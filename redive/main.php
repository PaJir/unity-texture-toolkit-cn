<?php
chdir(__DIR__);
require_once 'UnityBundle.php';
require_once 'resource_fetch.php';
require_once 'diff_parse.php';
if (!file_exists('last_version')) {
  $last_version = array('TruthVersion'=>0,'hash'=>'','TimeStamp'=>'');
} else {
  $last_version = json_decode(file_get_contents('last_version'), true);
}
$logFile = fopen('redive.log', 'a');
function _log($s) {
  global $logFile;
  fwrite($logFile, date('[m/d H:i] ').$s."\n");
  echo $s."\n";
}
function execQuery($db, $query) {
  $returnVal = [];
  /*if ($stmt = $db->prepare($query)) {
    $result = $stmt->execute();
    if ($result->numColumns()) {
      $returnVal = $result->fetchArray(SQLITE3_ASSOC);
    }
  }*/
  if (!$db) {
    throw new Exception('Invalid db handle');
  }
  $result = $db->query($query);
  if ($result === false) {
    throw new Exception('Failed executing query: '. $query);
  }
  $returnVal = $result->fetchAll(PDO::FETCH_ASSOC);
  return $returnVal;
}

function encodeValue($value) {
  $arr = [];
  foreach ($value as $key=>$val) {
    $arr[] = '/*'.$key.'*/' . (is_numeric($val) ? $val : ('"'.str_replace('"','\\"',$val).'"'));
  }
  return implode(", ", $arr);
}

function main() {

global $last_version;
global $curl;
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_RETURNTRANSFER=>true,
  CURLOPT_HEADER=>0,
  CURLOPT_SSL_VERIFYPEER=>false
));
chdir(__DIR__);

$TruthVersion = $last_version['TruthVersion'];
$TimeStamp = $last_version["TimeStamp"];

// fetch bundle manifest
if (false) {
  $appver = $last_version["TruthVersion"];
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_HEADER=>0,
    CURLOPT_SSL_VERIFYPEER=>false
  ));
  curl_setopt($curl, CURLOPT_URL, "https://l1-prod-patch-gzlj.bilibiligame.net/client_ob_${appver}/Manifest/AssetBundles/iOS/${TimeStamp}/manifest/all_assetmanifest");
  $manifest = curl_exec($curl);
  file_put_contents('data/+manifest_bundle.txt', $manifest);

  // fetch all manifest & save
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL=>'https://l1-prod-patch-gzlj.bilibiligame.net/client_ob_'.$TruthVersion.'/Manifest/AssetBundles/iOS/'.$TimeStamp.'/manifest/manifest_assetmanifest',
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_HEADER=>0,
    CURLOPT_SSL_VERIFYPEER=>false
  ));
  $manifest = curl_exec($curl);
  file_put_contents('data/+manifest_manifest.txt', $manifest);
  foreach (explode("\n", trim($manifest)) as $line) {
    list($manifestName) = explode(',', $line);
    if ($manifestName == 'manifest/soundmanifest') {
      continue;
    } else {
      curl_setopt($curl, CURLOPT_URL, 'https://l1-prod-patch-gzlj.bilibiligame.net/client_ob_'.$TruthVersion.'/Manifest/AssetBundles/iOS/'.$TimeStamp.'/'.$manifestName);
      $manifest = curl_exec($curl);
      file_put_contents('data/+manifest_'.substr($manifestName, 9, -14).'.txt', $manifest);
    }
  }
}

// download & extract db
if (false) {
  $manifest = file_get_contents('data/+manifest_masterdata.txt');
  $manifest = array_map(function ($i){ return explode(',', $i); }, explode("\n", $manifest));
  foreach ($manifest as $entry) {
    if ($entry[0] === 'a/masterdata_master.unity3d') { $manifest = $entry; break; }
  }
  if ($manifest[0] !== 'a/masterdata_master.unity3d') {
    _log('masterdata_master.unity3d not found');
    return;
  }
  $bundleHash = $manifest[1];
  $bundleSize = $manifest[3]|0;
  //download bundle
  _log("downloading cdb for TruthVersion ${TruthVersion}, hash: ${bundleHash}, size: ${bundleSize}");
  $bundleFileName = "master_${TruthVersion}.unity3d";
  curl_setopt_array($curl, array(
    CURLOPT_URL=>'https://l1-prod-patch-gzlj.bilibiligame.net/client_ob_'.$TruthVersion.'/pool/AssetBundles/iOS/'.substr($bundleHash,0,2).'/'.$bundleHash,
    CURLOPT_RETURNTRANSFER=>true
  ));
  $bundle = curl_exec($curl);
  //curl_close($curl);
  $downloadedSize = strlen($bundle);
  $downloadedHash = md5($bundle);
  if ($downloadedSize != $bundleSize || $downloadedHash != $bundleHash) {
    _log("download failed, received hash: ${downloadedHash}, received size: ${downloadedSize}");
    return;
  }

  //extract db
  _log('dumping db');
  file_put_contents('master.unity3d', $bundle);
  $bundle = new MemoryStream($bundle);
  $assets = extractBundle($bundle);
  unset($bundle);
  foreach ($assets as $asset) {
    $asset = new AssetFile($asset);
    foreach ($asset->preloadTable as &$item) {
      if ($item->typeString == 'TextAsset') {
        $item = new TextAsset($item, true);
        checkAndCreateFile($item->name.'.db', $item->data);
        unset($item);
      }
    }
    $asset->__desctruct();
    unset($asset);
  }
  foreach ($assets as $asset) {
    unlink($asset);
  }
  if (!file_exists('master.db')) {
    _log('Dump master.db failed');
    return;
  }
  unlink('master.unity3d');
  rename('master.db', 'redive.db');
  copy('redive.db', '/mnt/d/Data/github/bwiki/pcr/redive_cnx.db');
}

//dump sql
if (false) {
  _log('dumping sql');
  $db = new PDO('sqlite:redive.db');

  $tables = execQuery($db, 'SELECT * FROM sqlite_master');

  $name = [];
  foreach(execQuery($db, 'SELECT unit_id,unit_name FROM unit_data WHERE unit_id > 100000 AND unit_id < 200000') as $row) {
    $name[$row['unit_id']+30] = $row['unit_name'];
  }
  file_put_contents(RESOURCE_PATH_PREFIX.'card/full/index.json', json_encode($name, JSON_UNESCAPED_SLASHES));
  $storyStillName = [];
  foreach(execQuery($db, 'SELECT story_group_id,title FROM story_data') as $row) {
    $storyStillName[$row['story_group_id']] = $row['title'];
  }
  foreach(execQuery($db, 'SELECT story_group_id,title FROM event_story_data') as $row) {
    $storyStillName[$row['story_group_id']] = $row['title'];
  }
  foreach(execQuery($db, 'SELECT story_group_id,title FROM tower_story_data') as $row) {
    $storyStillName[$row['story_group_id']] = $row['title'];
  }
  file_put_contents(RESOURCE_PATH_PREFIX.'card/story/index.json', json_encode($storyStillName, JSON_UNESCAPED_SLASHES));
  $info = [];
  foreach (execQuery($db, 'SELECT unit_id,motion_type,unit_name FROM unit_data WHERE unit_id > 100000 AND unit_id < 200000') as $row) {
    $info[$row['unit_id']] = [
      'name' => $row['unit_name'],
      'type'=>$row['motion_type'],
      'hasRarity6' => false
    ];
  }
  foreach (execQuery($db, 'SELECT unit_id FROM unit_rarity WHERE rarity=6') as $row) {
    $info[$row['unit_id']]['hasRarity6'] = true;
  }
  $spineManifest = file_get_contents('data/+manifest_spine.txt');
  foreach ($info as $id => &$item) {
    if (strpos($spineManifest, "a/spine_${id}_chara_base.cysp.unity3d") !== false) {
      $item['hasSpecialBase'] = true;
    }
  }
  file_put_contents(RESOURCE_PATH_PREFIX.'spine/classMap.json', json_encode($info));

  unset($name);
  unset($db);
}

if (true) {
  checkAndUpdateResource($TruthVersion, $TimeStamp);

  file_put_contents(RESOURCE_PATH_PREFIX.'spine/still/index.json', json_encode(
    array_map(function ($i){
      return substr($i, -10, -4);
    },
    glob(RESOURCE_PATH_PREFIX.'spine/still/unit/*.png'))
  ));
}

}

main();
