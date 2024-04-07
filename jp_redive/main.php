<?php
chdir(__DIR__);
require_once 'UnityBundle.php';
require_once 'resource_fetch.php';
require_once 'diff_parse.php';
if (!file_exists('last_version')) {
  $last_version = array('TruthVersion'=>0,'hash'=>'');
} else {
  $last_version = json_decode(file_get_contents('last_version'), true);
}
$logFile = fopen('redive.log', 'a');
function _log($s) {
  global $logFile;
  fwrite($logFile, date('[m/d H:i] ').$s."\n");
  echo $s."\n";
}
class PrcnDbQueryProcessor {
  static $nameMap;
  static function load() {
    if (static::$nameMap === NULL) {
      static::$nameMap = json_decode(file_get_contents('nameMap.json'), true);
    }
  }
  static function processQuery($query) {
    static::load();
    $nameMap = static::$nameMap[$query[1]];
    if (empty($nameMap)) {
      throw new Exception('Table not found: '.$query[1]);
    }
    $finalQuery = str_replace('{tbl}', $nameMap['table'], $query[0]);
    $finalQuery = str_replace('{col}', implode(',', array_map(function ($i) use ($nameMap) {
      return '`'. $nameMap['column'][$i] . '` AS '. $i;
    }, $query[2])), $finalQuery);
    return $finalQuery;
  }
}

function execQuery($db, $query) {
  $returnVal = [];
  /*if ($stmt = $db->prepare($query)) {
    $result = $stmt->execute();
    if ($result->numColumns()) {
      $returnVal = $result->fetchArray(SQLITE3_ASSOC);
    }
  }*/
  if (is_array($query)) {
    $query = PrcnDbQueryProcessor::processQuery($query);
  }
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
function autoProxy() {
  $curl = curl_init();
  curl_setopt_array($curl, [
    CURLOPT_URL=>'https://free-proxy-list.net/',
    CURLOPT_HTTPHEADER=>[
      //'Host: www.us-proxy.org',
      'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:51.0) Gecko/20100101 Firefox/60.0.1',
      'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
      'Accept-Language: zh-CN,zh-TW;q=0.7,en-US;q=0.3',
      'Accept-Encoding: gzip, deflate, br',
      'Connection: keep-alive',
      'Upgrade-Insecure-Requests: 1'
    ],
    CURLOPT_CONNECTTIMEOUT=>3,
    CURLOPT_HEADER=>0,
    CURLOPT_RETURNTRANSFER=>1,
    CURLOPT_SSL_VERIFYPEER=>false
  ]);
  $proxylist = brotli_uncompress(curl_exec($curl));
  preg_match_all('/<tr><td>(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})<\/td><td>(\d+)<\/td>/', $proxylist, $matches);
  //print_r($matches);

  curl_setopt($curl, CURLOPT_URL, 'https://app.priconne-redive.jp/');
  $oldproxy = file_get_contents('currentproxy.txt');
  for ($i=0; $i<count($matches[1]); $i++) {
    $proxy = $matches[1][$i].':'.$matches[2][$i];
    if ($proxy == $oldproxy) continue;
    curl_setopt($curl, CURLOPT_PROXY, $proxy);
    curl_exec($curl);
    //_log($proxy.' '.curl_getinfo($curl, CURLINFO_HTTP_CODE));
    if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == 404) {
      /*
      $oldproxy = file_get_contents('currentproxy.txt');
      list($ip, $port) = explode(':', $oldproxy);
      $search  = [' -d '.$ip.' --dport '.$port.' ', ' --to-destination '.$ip.':'.$port];
      
      list($ip, $port) = explode(':', $proxy);
      $replace = [' -d '.$ip.' --dport '.$port.' ', ' --to-destination '.$ip.':'.$port];
      file_put_contents('/etc/firewalld/direct.xml', str_replace($search, $replace, file_get_contents('/etc/firewalld/direct.xml')));
      exec('/usr/bin/firewall-cmd --reload >/dev/null');
      */
      _log('Found new proxy: '. $proxy);
      file_put_contents('currentproxy.txt', $proxy);
      return true;
    };
  }
  return false;
}

function encodeValue($value) {
  $arr = [];
  foreach ($value as $key=>$val) {
    //$arr[] = '/*'.$key.'*/' . (is_numeric($val) ? $val : ('"'.str_replace('"','\\"',$val).'"'));
    $arr[] = (is_numeric($val) ? $val : ('"'.str_replace('"','\\"',$val).'"'));
  }
  return implode(", ", $arr);
}
function do_commit($TruthVersion, $db = NULL, $extraMsg = '') {
  exec('git commit -m "'.$TruthVersion.'"');
  // exec('git push origin master');
  return;
}

function main() {

global $last_version;
chdir(__DIR__);

//check app ver at 00:00
$appver = file_exists('appver') ? file_get_contents('appver') : '1.1.4';
$itunesid = 1134429300;
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL=>'https://itunes.apple.com/lookup?id='.$itunesid.'&lang=ja_jp&country=jp&rnd='.rand(10000000,99999999),
  CURLOPT_HEADER=>0,
  CURLOPT_RETURNTRANSFER=>1,
  CURLOPT_SSL_VERIFYPEER=>false
));
$appinfo = curl_exec($curl);
curl_close($curl);
if ($appinfo !== false) {
  $appinfo = json_decode($appinfo, true);
  if (!empty($appinfo['results'][0]['version'])) {
    $prevappver = $appver;
    $appver = $appinfo['results'][0]['version'];

    if (version_compare($prevappver,$appver, '<')) {
      file_put_contents('appver', $appver);
      _log('new game version: '. $appver);

      // fetch bundle manifest
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_HEADER=>0,
        CURLOPT_SSL_VERIFYPEER=>false
      ));
      curl_setopt($curl, CURLOPT_URL, "http://prd-priconne-redive.akamaized.net/dl/Bundles/${appver}/Jpn/AssetBundles/iOS/manifest/bdl_assetmanifest");
      $manifest = curl_exec($curl);
      file_put_contents('data/+manifest_bundle.txt', $manifest);
      chdir('data');
      exec('git add +manifest_bundle.txt');
      exec('git commit -m "bundle manifest v'.$appver.'"');
      chdir(__DIR__);
    }
  }
}

$isWin = DIRECTORY_SEPARATOR === '\\';
// $cmdPrepend = $isWin ? '' : 'wine ';
$cmdPrepend = $isWin ? '' : './';
$cmdAppend = $isWin ? '' : ' >/dev/null 2>&1';

// guess latest res_ver
global $curl;
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_RETURNTRANSFER=>true,
  CURLOPT_HEADER=>0,
  CURLOPT_SSL_VERIFYPEER=>false
));
$TruthVersion = $last_version['TruthVersion'];
// checkAndUpdateResource($TruthVersion);
// return;
$current_ver = $TruthVersion|0;

for ($i=1; $i<=20; $i++) {
  $guess = $current_ver + $i * 10;
  curl_setopt($curl, CURLOPT_URL, 'http://prd-priconne-redive.akamaized.net/dl/Resources/'.$guess.'/Jpn/AssetBundles/iOS/manifest/manifest_assetmanifest');
  curl_exec($curl);
  $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  if ($code == 200) {
    $TruthVersion = $guess.'';
    break;
  }
}
curl_close($curl);
if ($TruthVersion == $last_version['TruthVersion']) {
  _log('no update found');
  return;
}//
$last_version['TruthVersion'] = $TruthVersion;
_log("TruthVersion: ${TruthVersion}");
file_put_contents('data/!TruthVersion.txt', $TruthVersion."\n");

//$TruthVersion = '10000000';
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL=>'http://prd-priconne-redive.akamaized.net/dl/Resources/'.$TruthVersion.'/Jpn/AssetBundles/iOS/manifest/manifest_assetmanifest',
  CURLOPT_RETURNTRANSFER=>true,
  CURLOPT_HEADER=>0,
  CURLOPT_SSL_VERIFYPEER=>false
));
//$manifest = file_get_contents('history/'.$TruthVersion);
// fetch all manifest & save
$manifest = curl_exec($curl);
file_put_contents('data/+manifest_manifest.txt', $manifest);
foreach (explode("\n", trim($manifest)) as $line) {
  list($manifestName) = explode(',', $line);
  if ($manifestName == 'manifest/soundmanifest') {
    continue;
  } else {
    curl_setopt($curl, CURLOPT_URL, 'http://prd-priconne-redive.akamaized.net/dl/Resources/'.$TruthVersion.'/Jpn/AssetBundles/iOS/'.$manifestName);
    $manifest = curl_exec($curl);
    file_put_contents('data/+manifest_'.substr($manifestName, 9, -14).'.txt', $manifest);
  }
}
curl_setopt($curl, CURLOPT_URL, 'http://prd-priconne-redive.akamaized.net/dl/Resources/'.$TruthVersion.'/Jpn/Sound/manifest/sound2manifest');
$manifest = curl_exec($curl);
file_put_contents('data/+manifest_sound.txt', $manifest);
curl_setopt($curl, CURLOPT_URL, 'http://prd-priconne-redive.akamaized.net/dl/Resources/'.$TruthVersion.'/Jpn/Movie/SP/High/manifest/moviemanifest');
$manifest = curl_exec($curl);
file_put_contents('data/+manifest_movie.txt', $manifest);
curl_setopt($curl, CURLOPT_URL, 'http://prd-priconne-redive.akamaized.net/dl/Resources/'.$TruthVersion.'/Jpn/Movie/SP/Low/manifest/moviemanifest');
$manifest = curl_exec($curl);
file_put_contents('data/+manifest_movie_low.txt', $manifest);

$manifest = file_get_contents('data/+manifest_masterdata.txt');
$manifest = array_map(function ($i){ return explode(',', $i); }, explode("\n", $manifest));
foreach ($manifest as $entry) {
  if ($entry[0] === 'a/masterdata_master_0003.cdb') { $manifest = $entry; break; }
}
if ($manifest[0] !== 'a/masterdata_master_0003.cdb') {
  _log('masterdata_master_0003.cdb not found');
  //file_put_contents('stop_cron', '');
  file_put_contents('last_version', json_encode($last_version));
  chdir('data');
  exec('git add !TruthVersion.txt +manifest_*.txt');
  do_commit($TruthVersion, NULL, ' (no master db)');
  checkAndUpdateResource($TruthVersion);
  return;
}
$bundleHash = $manifest[1];
$bundleSize = $manifest[3]|0;
if ($last_version['hash'] == $bundleHash) {
  _log("Same hash as last version ${bundleHash}");
  file_put_contents('last_version', json_encode($last_version));
  chdir('data');
  exec('git add !TruthVersion.txt +manifest_*.txt');
  do_commit($TruthVersion);
  return;
}
$last_version['hash'] = $bundleHash;
//download bundle
_log("downloading cdb for TruthVersion ${TruthVersion}, hash: ${bundleHash}, size: ${bundleSize}");
$bundleFileName = "master_${TruthVersion}.unity3d";
curl_setopt_array($curl, array(
  CURLOPT_URL=>'http://prd-priconne-redive.akamaized.net/dl/pool/AssetBundles/'.substr($bundleHash,0,2).'/'.$bundleHash,
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
_log('dumping cdb');
file_put_contents('master.cdb', $bundle);
unset($bundle);
system($cmdPrepend.'Coneshell_call.exe -cdb master.cdb master.mdb'.$cmdAppend);
if (!file_exists('master.mdb')) {
  _log('Dump master.mdb failed');
  return;
}
unlink('master.cdb');
rename('master.mdb', 'redive.db');
$dbData = file_get_contents('redive.db');
// file_put_contents('redive.db.br', brotli_compress($dbData, 9));

// dump sql
_log('dumping sql');
$db = new PDO('sqlite:redive.db');

$tables = execQuery($db, 'SELECT * FROM sqlite_master');

chdir('data');
exec('git rm *.sql');
chdir('..');
foreach (glob('data/*.sql') as $file) {unlink($file);}

foreach ($tables as $entry) {
  if ($entry['name'] == 'sqlite_stat1') continue;
  if ($entry['type'] == 'table') {
    $tblName = $entry['name'];
    $f = fopen("data/${tblName}.sql", 'w');
    fwrite($f, $entry['sql'].";\n");
    $values = execQuery($db, "SELECT * FROM ${tblName}");
    foreach($values as $value) {
      fwrite($f, "INSERT INTO `${tblName}` VALUES (".encodeValue($value).");\n");
    }
    fclose($f);
  } else if ($entry['type'] == 'index' && !empty($entry['sql'])) {
    $tblName = $entry['tbl_name'];
    file_put_contents("data/${tblName}.sql", $entry['sql'].";\n", FILE_APPEND);
  }
}
$name = [];
foreach(execQuery($db, ['SELECT {col} FROM {tbl} WHERE unit_id > 100000 AND unit_id < 200000', 'unit_data', ['unit_id','unit_name']]) as $row) {
  $name[$row['unit_id']+30] = $row['unit_name'];
}
file_put_contents(RESOURCE_PATH_PREFIX.'card/full/index.json', json_encode($name, JSON_UNESCAPED_UNICODE));
$storyStillName = [];
foreach(execQuery($db, ['SELECT {col} FROM {tbl}', 'story_data', ['story_group_id','title']]) as $row) {
  $storyStillName[$row['story_group_id']] = $row['title'];
}
foreach(execQuery($db, ['SELECT {col} FROM {tbl}', 'event_story_data', ['story_group_id','title']]) as $row) {
  $storyStillName[$row['story_group_id']] = $row['title'];
}
foreach(execQuery($db, ['SELECT {col} FROM {tbl}', 'tower_story_data', ['story_group_id','title']]) as $row) {
  $storyStillName[$row['story_group_id']] = $row['title'];
}
file_put_contents(RESOURCE_PATH_PREFIX.'card/story/index.json', json_encode($storyStillName, JSON_UNESCAPED_UNICODE));
$info = [];
foreach (execQuery($db, ['SELECT {col} FROM {tbl} WHERE unit_id > 100000 AND unit_id < 200000', 'unit_data', ['unit_id','motion_type','unit_name']]) as $row) {
  $info[$row['unit_id']] = [
    'name' => $row['unit_name'],
    'type'=>$row['motion_type'],
    'hasRarity6' => false
  ];
}
foreach (execQuery($db, ['SELECT {col} FROM {tbl} WHERE rarity=6', 'unit_rarity', ['unit_id', 'rarity']]) as $row) {
  $info[$row['unit_id']]['hasRarity6'] = true;
}
$spineManifest = file_get_contents('data/+manifest_spine2.txt');
foreach ($info as $id => &$item) {
  if (strpos($spineManifest, "a/spine_${id}_chara_base.cysp.unity3d") !== false) {
    $item['hasSpecialBase'] = true;
  }
}
file_put_contents(RESOURCE_PATH_PREFIX.'spine/classMap.json', json_encode($info, JSON_UNESCAPED_UNICODE));

unset($name);
file_put_contents('last_version', json_encode($last_version));

chdir('data');
exec('git add *.sql !TruthVersion.txt +manifest_*.txt');
do_commit($TruthVersion, $db);
unset($db);

if (file_exists(__DIR__.'/action_after_update.php')) require_once __DIR__.'/action_after_update.php';

checkAndUpdateResource($TruthVersion);

#file_put_contents(RESOURCE_PATH_PREFIX.'spine/still/index.json', json_encode(
#  array_map(function ($i){
#    return substr($i, -10, -4);
#  },
#  glob(RESOURCE_PATH_PREFIX.'spine/still/unit/*.png'))
#));

}

/*foreach(glob('history/100*') as $ver) {
  main(substr($ver, 8));
}*/
main();
