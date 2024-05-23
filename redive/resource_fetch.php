<?php
if (count(get_included_files()) == 1) define ('TEST_SUITE', __FILE__);

require_once 'UnityAsset.php';

$resourceToExport = [
  'all' => [
    // [ 'bundleNameMatch'=>'/^a\/all_battleunitprefab_\d+\.unity3d$/', 'customAssetProcessor'=> 'exportPrefab' ],
    // [ 'bundleNameMatch'=>'/^a\/all_atlascommon.unity3d$/', 'nameMatch'=>'/^(.*)$/i', 'exportTo'=>'minigame/$1'],
    // [ 'bundleNameMatch'=>'/^a\/all_atlasbattle.unity3d$/', 'nameMatch'=>'/^(.*)$/i', 'exportTo'=>'minigame/$1'],
    // [ 'bundleNameMatch'=>'/^a\/all_atlasminigametaq.*.unity3d$/', 'nameMatch'=>'/^(.*)$/i', 'exportTo'=>'minigame/$1']
  ],
  // 'bg'=> [
  //   [ 'bundleNameMatch'=>'/^a\/bg_still_unit_\d+\.unity3d$/',       'nameMatch'=>'/^still_unit_(\d+)$/i',     'exportTo'=>'card/full/$1' ]
  // ],
  'icon'=>[
  //   [ 'bundleNameMatch'=>'/^a\/icon_icon_skill_\d+\.unity3d$/',     'nameMatch'=>'/^icon_skill_(\d+)$/i',     'exportTo'=>'icon/skill/$1' ],
  //   [ 'bundleNameMatch'=>'/^a\/icon_icon_equipment_\d+\.unity3d$/', 'nameMatch'=>'/^icon_equipment_(\d+)$/i', 'exportTo'=>'icon/equipment/$1' ],
    [ 'bundleNameMatch'=>'/^a\/icon_icon_extra_.*\.unity3d$/', 'nameMatch'=>'/^icon_extra_(.+)$/i', 'exportTo'=>'icon/equipment/icon_extra$1' ],
  //   [ 'bundleNameMatch'=>'/^a\/icon_icon_item_\d+\.unity3d$/', 'nameMatch'=>'/^icon_item_(\d+)$/i', 'exportTo'=>'icon/item/$1' ],
  //   [ 'bundleNameMatch'=>'/^a\/icon_unit_plate_\d+\.unity3d$/',     'nameMatch'=>'/^unit_plate_(\d+)$/i',     'exportTo'=>'icon/plate/$1' ],
    // [ 'bundleNameMatch'=>'/^a\/icon_icon_stamp_\d+\.unity3d$/',     'nameMatch'=>'/^icon_stamp_(\d+)$/i',     'exportTo'=>'icon/stamp/icon_stamp_$1' ],
    // [ 'bundleNameMatch'=>'/^a\/icon_thumb_chara_story_top_1\d+31\.unity3d$/',     'nameMatch'=>'/^thumb_chara_story_top_(\d+)$/i',     'exportTo'=>'icon/storytop/thumb_chara_story_top_$1', 'extraParam'=>'-s 240x135' ],
    // [ 'bundleNameMatch'=>'/^a\/icon_thumb_event_story_top_\d+\.unity3d$/',     'nameMatch'=>'/^thumb_event_story_top_(\d+)$/i',     'exportTo'=>'icon/storytop/thumb_event_story_top_$1', 'extraParam'=>'-s 240x135' ],
    // [ 'bundleNameMatch'=>'/^a\/icon_thumb_exstory_top_\d+\.unity3d$/',     'nameMatch'=>'/^thumb_exstory_top_(\d+)$/i',     'exportTo'=>'icon/storytop/thumb_exstory_top_$1', 'extraParam'=>'-s 240x135' ],
    // [ 'bundleNameMatch'=>'/^a\/icon_thumb_guild_story_top_\d+\.unity3d$/',     'nameMatch'=>'/^thumb_guild_story_top_(\d+)$/i',     'exportTo'=>'icon/storytop/thumb_guild_story_top_$1', 'extraParam'=>'-s 240x135' ],
    // [ 'bundleNameMatch'=>'/^a\/icon_thumb_tower_story_top_\d+\.unity3d$/',     'nameMatch'=>'/^thumb_tower_story_top_(\d+)$/i',     'exportTo'=>'icon/storytop/thumb_tower_story_top_$1', 'extraParam'=>'-s 240x135' ],
  ],
  'comic'=>[
    // [ 'bundleNameMatch'=>'/^a\/comic_comic_l_\d+_\d+.unity3d$/',      'nameMatch'=>'/^comic_l_(\d+)_\d+$/i',      'exportTo'=>'comic/comic_$1', 'extraParam'=>'-s 682x512' ],
  ],
  // 'storydata'=>[
  //   [ 'bundleNameMatch'=>'/^a\/storydata_still_\d+.unity3d$/',      'nameMatch'=>'/^still_(\d+)$/i',      'exportTo'=>'card/story/$1', 'extraParamCb'=>function($item){return ($item->width!=$item->height)?'-s '.$item->width.'x'.($item->width/16*9):'';} ],
  //   [ 'bundleNameMatch'=>'/^a\/storydata_\d+.unity3d$/',      'customAssetProcessor'=> 'exportStory' ],
  //   [ 'bundleNameMatch'=>'/^a\/storydata_spine_full_\d+.unity3d$/',      'customAssetProcessor'=> 'exportStoryStill' ],
  //   [ 'bundleNameMatch'=>'/^a\/storydata_movie_\d+.unity3d$/',      'customAssetProcessor'=> 'exportSubtitle' ],
  //   [ 'bundleNameMatch'=>'/^a\/storydata_icon_unit_\d+\.unity3d$/',      'nameMatch'=>'/^icon_unit_(\d+)$/i',      'exportTo'=>'story/icon/$1' ],
  // ],
  'minigame'=>[
    // [ 'bundleNameMatch'=>'/^a\/minigame_taq.*.unity3d$/', 'nameMatch'=>'/^(.*)$/i', 'exportTo'=>'minigame/$1']
  ],
];

function exportSubtitle($asset, $remoteTime) {
  foreach ($asset->preloadTable as $item) {
    if ($item->typeString == 'MonoBehaviour') {
      $stream = $asset->stream;
      $stream->position = $item->offset;
      if (isset($asset->ClassStructures[$item->type1])) {
        $deserializedStruct = ClassStructHelper::DeserializeStruct($stream, $asset->ClassStructures[$item->type1]['members']);
        $organizedStruct = ClassStructHelper::OrganizeStruct($deserializedStruct);
        $vttblocks = ['WEBVTT'];
        foreach ($organizedStruct['recordList'] as $cue) {
          $vttblocks[] = vtttime($cue['data']['startTime'])." --> ".vtttime($cue['data']['endTime'])."\n".$cue['data']['text'];
        }
        checkAndCreateFile(RESOURCE_PATH_PREFIX.'movie/vtts/'.substr($organizedStruct['m_Name'], 6).'.vtt', implode("\n\n", $vttblocks), $remoteTime);
      }
    }
  }
}
function vtttime($time) {
  $h = str_pad(floor($time / 3600), 2, '0', STR_PAD_LEFT);
  $time -= $h * 3600;
  $m = str_pad(floor($time / 60), 2, '0', STR_PAD_LEFT);
  $time -= $m * 60;
  $s = str_pad(number_format($time, 3), 6, '0', STR_PAD_LEFT);
  return implode(':', [$h, $m, $s]);
}

function exportStory($asset, $remoteTime) {
  foreach ($asset->preloadTable as $item) {
    if ($item->typeString == 'TextAsset') {
      $item = new TextAsset($item, true);
      require_once 'RediveStoryDeserializer.php';
      $parser = new RediveStoryDeserializer($item->data);
      $name = substr($item->name, 10);
      checkAndCreateFile(RESOURCE_PATH_PREFIX.'story/data/'.$name.'.json', json_encode($parser->commandList), $remoteTime);
      checkAndCreateFile(RESOURCE_PATH_PREFIX.'story/data/'.$name.'.htm', $parser->data, $remoteTime);

      $storyStillName = json_decode(file_get_contents(RESOURCE_PATH_PREFIX.'spine/still/still_name.json'), true);
      $nextId = NULL;
      foreach($parser->commandList as $cmd) {
        if ($cmd['name'] == 'face') {
          $nextId = str_pad(
            substr($cmd['args'][0], 0, -1) . 1
            , 6, '0', STR_PAD_LEFT);
        } else if ($cmd['name'] == 'print' && $nextId) {
          $storyStillName[$nextId] = $cmd['args'][0];
          $nextId = NULL;
        } else if ($cmd['name'] == 'touch') {
          $nextId = NULL;
        }
      }
      file_put_contents(RESOURCE_PATH_PREFIX.'spine/still/still_name.json', json_encode($storyStillName));
    }
  }
}
function exportStoryStill($asset, $remoteTime) {
  foreach ($asset->preloadTable as $item) {
    if ($item->typeString == 'TextAsset') {
      $item = new TextAsset($item, true);
      checkAndCreateFile(RESOURCE_PATH_PREFIX.'spine/still/unit/'.$item->name, $item->data, $remoteTime);
    } else if ($item->typeString == 'Texture2D') {
      $item = new Texture2D($item, true);
      $saveTo = RESOURCE_PATH_PREFIX.'spine/still/unit/'.$item->name;
      $item->exportTo($saveTo, 'png');
      if (filemtime($saveTo.'.png') > $remoteTime)
      touch($saveTo.'.png', $remoteTime);
    }
  }
}

$prefabUpdated = false;
function exportPrefab($asset, $remoteTime) {
  global $prefabUpdated;
  $prefabUpdated = true;
  foreach ($asset->preloadTable as $item) {
    if ($item->typeString == 'MonoBehaviour') {
      $stream = $asset->stream;
      $stream->position = $item->offset;
      if (isset($asset->ClassStructures[$item->type1])) {
        $deserializedStruct = ClassStructHelper::DeserializeStruct($stream, $asset->ClassStructures[$item->type1]['members']);
        $organizedStruct = ClassStructHelper::OrganizeStruct($deserializedStruct);
        if (isset($organizedStruct['Attack'])) {
          $gameObjectPath = $organizedStruct['m_GameObject']['m_PathID'];
          $gameObject = $asset->preloadTable[$gameObjectPath];
          $stream->position = $gameObject->offset;
          $unitId = ClassStructHelper::OrganizeStruct(ClassStructHelper::DeserializeStruct($stream, $asset->ClassStructures[$gameObject->type1]['members']))['m_Name'];
          file_put_contents('prefabs/'.$unitId.'.json', json_encode($organizedStruct));
          break;
        }
      }
    }
  }
}

function shouldExportFile($name, $rule) {
  return preg_match($rule['nameMatch'], $name) != 0;
}

function parseManifest($manifest) {
  $manifest = new MemoryStream($manifest);
  $list=[];
  while (!empty($line = $manifest->line)) {
    list($name, $hash, $stage, $size) = explode(',', $line);
    $list[$name] = [
      'hash' =>$hash,
      'size' =>$size
    ];
  }
  unset($manifest);
  return $list;
}
$cacheHashDb = new PDO('sqlite:'.__DIR__.'/cacheHash.db');
// $cacheHashDb->exec('DROP TABLE cacheHash;');
// $cacheHashDb->exec('DROP TABLE textureHash;');
// $cacheHashDb->exec('DELETE FROM cacheHash WHERE res like \'%extra%\';');
// $cacheHashDb->exec('DELETE FROM textureHash WHERE res like \'%extra%\';');
// $cacheHashDb->exec('CREATE TABLE IF NOT EXISTS cacheHash (res TEXT, hash TEXT);');
// $cacheHashDb->exec('CREATE TABLE IF NOT EXISTS textureHash (res TEXT, hash TEXT);');
$chkHashStmt = $cacheHashDb->prepare('SELECT hash FROM cacheHash WHERE res=?');
$chkHashStmt = $cacheHashDb->prepare('SELECT hash FROM cacheHash WHERE res=?');
function shouldUpdate($name, $hash) {
  global $chkHashStmt;
  $chkHashStmt->execute([$name]);
  $row = $chkHashStmt->fetch();
  return !(!empty($row) && $row['hash'] == $hash);
}
$setHashStmt = $cacheHashDb->prepare('REPLACE INTO cacheHash (res,hash) VALUES (?,?)');
function setHashCached($name, $hash) {
  global $setHashStmt;
  $setHashStmt->execute([$name, $hash]);
}

function findRule($name, $rules) {
  //var_dump($name, $rules);
  foreach ($rules as $rule) {
    if (preg_match($rule['bundleNameMatch'], $name) != 0) return $rule;
  }
  return false;
}

$chkTextureHashStmt = $cacheHashDb->prepare('SELECT hash FROM textureHash WHERE res=?');
function textureHasUpdated($name, Texture2D &$item) {
  global $chkTextureHashStmt;
  $hash = crc32($item->imageData);
  $item->imageDataHash = $hash;
  $chkTextureHashStmt->execute([$name]);
  $row = $chkTextureHashStmt->fetch();
  return !(!empty($row) && $row['hash'] == $hash);
}
$setTextureHashStmt = $cacheHashDb->prepare('REPLACE INTO textureHash (res,hash) VALUES (?,?)');
function updateTextureHash($name, Texture2D &$item) {
  global $setTextureHashStmt;
  $setTextureHashStmt->execute([$name, $item->imageDataHash]);
}

define('RESOURCE_PATH_PREFIX', '/mnt/d/Extra/pcr/_redive_cn/');

function checkSubResource($manifest, $rules, $TruthVersion) {
  global $curl;
  foreach ($manifest as $name => $info) {
    // if (($rule = findRule($name, $rules)) !== false && shouldUpdate($name, $info['hash'])) {
    if (($rule = findRule($name, $rules)) !== false) {
      _log('download '. $name.' '.$info['hash']);
      curl_setopt_array($curl, array(
        CURLOPT_URL=>'https://l1-prod-patch-gzlj.bilibiligame.net/client_ob_'.$TruthVersion.'/pool/AssetBundles/iOS/'.substr($info['hash'],0,2).'/'.$info['hash'],
      ));
      $bundleData = curl_exec($curl);
      $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      if ($code != 200) {
        curl_setopt_array($curl, array(
          CURLOPT_URL=>'https://l3-prod-patch-gzlj.bilibiligame.net/client_ob_'.$TruthVersion.'/pool/AssetBundles/iOS/'.substr($info['hash'],0,2).'/'.$info['hash'],
        ));
        $bundleData = curl_exec($curl);
      }
      $remoteTime = curl_getinfo($curl, CURLINFO_FILETIME);
      $remoteTime = time();
      if (md5($bundleData) != $info['hash']) {
        _log('download failed  '.$name);
        continue;
      }
      $bundleData = new MemoryStream($bundleData);
      $assets = extractBundle($bundleData);
      foreach ($assets as $asset) {
        if (substr($asset, -5,5) == '.resS') continue;
        $asset = new AssetFile($asset);
    
        if (isset($rule['customAssetProcessor'])) {
          call_user_func($rule['customAssetProcessor'], $asset, $remoteTime);
        } else
        foreach ($asset->preloadTable as &$item) {
          if ($item->typeString == 'Texture2D') {
            $item = new Texture2D($item, true);
            if (isset($rule['print'])) {
              var_dump($item->name);
              continue;
            }
            $itemname = $item->name;
            if (isset($rule['namePrefix'])) {
              $itemname = preg_replace($rule['bundleNameMatch'], $rule['namePrefix'], $name).$itemname;
            }
            if (isset($rule['namePrefixCb'])) {
              $itemname = preg_replace_callback($rule['bundleNameMatch'], $rule['namePrefixCb'], $name).$itemname;
            }
            if (shouldExportFile($itemname, $rule) && textureHasUpdated("$name:$itemname", $item)) {
              $saveTo = RESOURCE_PATH_PREFIX. preg_replace($rule['nameMatch'], $rule['exportTo'], $itemname);
              $param = '-lossless 1';
              if (isset($rule['extraParam'])) $param .= ' '.$rule['extraParam'];
              if (isset($rule['extraParamCb'])) $param .= ' '.call_user_func($rule['extraParamCb'], $item);
              $item->exportTo($saveTo, 'png', $param);
              if (filemtime($saveTo.'.png') > $remoteTime)
              touch($saveTo.'.png', $remoteTime);
              updateTextureHash("$name:$itemname", $item);
            }
            unset($item);
          }
        }
        $asset->__desctruct();
        unset($asset);
        gc_collect_cycles();
      }
      foreach ($assets as $asset) {
        unlink($asset);
      }
      unset($bundleData);
      if (isset($rule['print'])) exit;
      setHashCached($name, $info['hash']);
    }
  }
}

function delTree($dir) {
  $files = array_diff(scandir($dir), array('.','..'));
  foreach ($files as $file) {
    (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
  }
  return rmdir($dir);
}

function checkAndUpdateResource($TruthVersion, $TimeStamp) {
  _log($TruthVersion.' '.$TimeStamp);
  global $resourceToExport;
  global $curl;
  chdir(__DIR__);
  curl_setopt_array($curl, array(
    CURLOPT_URL=>'https://l1-prod-patch-gzlj.bilibiligame.net/client_ob_'.$TruthVersion.'/Manifest/AssetBundles/iOS/'.$TimeStamp.'/manifest/manifest_assetmanifest',
    CURLOPT_CONNECTTIMEOUT=>5,
    CURLOPT_ENCODING=>'gzip',
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_HEADER=>0,
    CURLOPT_FILETIME=>true,
    CURLOPT_SSL_VERIFYPEER=>false
  ));
  $manifest = curl_exec($curl);

  $manifest = parseManifest($manifest);
  foreach ($resourceToExport as $name=>$rules) {
    $name = "manifest/${name}_assetmanifest";
    // if (isset($manifest[$name]) && shouldUpdate($name, $manifest[$name]['hash'])) {
    if (isset($manifest[$name])) {
      _log($name);
      curl_setopt_array($curl, array(
        CURLOPT_URL=>'https://l1-prod-patch-gzlj.bilibiligame.net/client_ob_'.$TruthVersion.'/Manifest/AssetBundles/iOS/'.$TimeStamp.'/'.$name,
      ));
      $submanifest = curl_exec($curl);
      if (md5($submanifest) != $manifest[$name]['hash']) {
        _log('download failed 2 '.$name);
        continue;
      }
      $submanifest = parseManifest($submanifest);
      checkSubResource($submanifest, $rules, $TruthVersion);
      setHashCached($name, $manifest[$name]['hash']);
    }
  }

  global $prefabUpdated;
  if ($prefabUpdated) {
    chdir('prefabs');
    exec('7za a -mx=9 ../prefabs.zip *');
    chdir('..');
    $lastVer = json_decode(file_get_contents('last_version'), true);
    $lastVer['PrefabVer'] = $TruthVersion;
    file_put_contents('last_version', json_encode($lastVer));
  }
}

