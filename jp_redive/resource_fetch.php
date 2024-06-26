<?php
if (count(get_included_files()) == 1) define ('TEST_SUITE', __FILE__);

require_once 'UnityAsset.php';

$resourceToExport = [
  // 'all' => [
  //   // [ 'bundleNameMatch'=>'/^a\/all_battleunitprefab_\d+\.unity3d$/', 'customAssetProcessor'=> 'exportPrefab' ],
  // ],
  // 'atlasngui' => [
  //   [ 'bundleNameMatch'=>'/^a\/.*_atlascommon.unity3d$/', 'nameMatch'=>'/^(.*)$/i', 'exportTo'=>'minigame/$1'],
  //   [ 'bundleNameMatch'=>'/^a\/.*_atlasbattle.unity3d$/', 'nameMatch'=>'/^(.*)$/i', 'exportTo'=>'minigame/$1'],
  // ]
  'bg'=> [
    [ 'bundleNameMatch'=>'/^a\/bg_still_unit_\d+\.unity3d$/',       'nameMatch'=>'/^still_unit_(\d+)$/i',     'exportTo'=>'card/full/$1' ]
  ],
  'icon'=>[
    [ 'bundleNameMatch'=>'/^a\/icon_icon_skill_\d+\.unity3d$/',     'nameMatch'=>'/^icon_skill_(\d+)$/i',     'exportTo'=>'icon/skill/$1' ],
    [ 'bundleNameMatch'=>'/^a\/icon_icon_equipment_\d+\.unity3d$/', 'nameMatch'=>'/^icon_equipment_13(\d+)$/i', 'exportTo'=>'icon/equipment/icon_equipment_1$1' ],
    [ 'bundleNameMatch'=>'/^a\/icon_icon_equipment_\d+\.unity3d$/', 'nameMatch'=>'/^icon_equipment_(\d+)$/i', 'exportTo'=>'icon/equipment/$1' ],
    [ 'bundleNameMatch'=>'/^a\/icon_icon_item_\d+\.unity3d$/', 'nameMatch'=>'/^icon_item_(\d+)$/i', 'exportTo'=>'icon/item/icon_item_$1' ],
    [ 'bundleNameMatch'=>'/^a\/icon_icon_roomitem_.+\.unity3d$/', 'nameMatch'=>'/^icon_roomitem_(.+)$/i', 'exportTo'=>'icon/item/icon_roomitem_$1' ],
    // [ 'bundleNameMatch'=>'/^a\/icon_unit_plate_\d+\.unity3d$/',     'nameMatch'=>'/^unit_plate_(\d+)$/i',     'exportTo'=>'icon/plate/$1' ],
  ],
  'unit'=>[
    [ 'bundleNameMatch'=>'/^a\/unit_icon_unit_\d+\.unity3d$/',      'nameMatch'=>'/^icon_unit_(\d+)$/i',      'exportTo'=>'icon/unit/icon_unit_$1' ],
  //   // [ 'bundleNameMatch'=>'/^a\/unit_icon_shadow_\d+\.unity3d$/',    'nameMatch'=>'/^icon_shadow_(\d+)$/i',    'exportTo'=>'icon/unit_shadow/icon_shadow_$1' ],
  //   // [ 'bundleNameMatch'=>'/^a\/unit_thumb_actual_unit_profile_\d+\.unity3d$/',    'nameMatch'=>'/^thumb_actual_unit_profile_(\d+)$/i',    'exportTo'=>'card/actual_profile/$1', 'extraParam'=>'-s 1024x682' ],
  //   // [ 'bundleNameMatch'=>'/^a\/unit_thumb_unit_profile_\d+\.unity3d$/',           'nameMatch'=>'/^thumb_unit_profile_(\d+)$/i',           'exportTo'=>'card/profile/$1',        'extraParam'=>'-s 1024x682' ],
  ],
  'comic'=>[
    [ 'bundleNameMatch'=>'/^a\/comic_comic_l_\d+_\d+.unity3d$/',      'nameMatch'=>'/^comic_l_(\d+)_\d+$/i',      'exportTo'=>'comic/comic_$1', 'extraParam'=>'-s 682x512' ],
  ],
  'storydata'=>[
    [ 'bundleNameMatch'=>'/^a\/storydata_still_\d+.unity3d$/',      'nameMatch'=>'/^still_(\d+)$/i',      'exportTo'=>'card/story/$1', 'extraParamCb'=>function($item){return ($item->width!=$item->height)?'-s '.$item->width.'x'.($item->width/16*9):'';} ],
    // [ 'bundleNameMatch'=>'/^a\/storydata_\d+.unity3d$/',      'customAssetProcessor'=> 'exportStory' ],
    [ 'bundleNameMatch'=>'/^a\/storydata_spine_full_\d+.unity3d$/',      'customAssetProcessor'=> 'exportStoryStill' ],
    // [ 'bundleNameMatch'=>'/^a\/storydata_movie_\d+.unity3d$/',      'customAssetProcessor'=> 'exportSubtitle' ],
  ],
  'spine'=>[
    [ 'bundleNameMatch'=>'/^a\/spine_\d{6}_(chara_base|dear|no_weapon|posing|race|run_jump|smile|common_battle|battle)\.cysp\.unity3d$/', 'customAssetProcessor'=> 'exportSpine' ],
    [ 'bundleNameMatch'=>'/^a\/spine_\d\d_common_battle\.cysp\.unity3d$/', 'customAssetProcessor'=> 'exportSpine' ],
    [ 'bundleNameMatch'=>'/^a\/spine_sdnormal_\d{6}\.unity3d$/',        'customAssetProcessor'=> 'exportAtlas' ],
  ],
//   'wac'=>[    
//     [ 'bundleNameMatch'=>'/^a\/wac_wac\.unity3d$/',      'nameMatch'=>'/^(\d+)$/i',      'exportTo'=>'story/birthday/$1' ],
//   ],
//   'sound'=>[
//     [ 'bundleNameMatch'=>'/^v\/vo_cmn_(\d+)\.acb$/', 'exportTo'=> 'sound/unit_common/$1' ],
//     [ 'bundleNameMatch'=>'/^v\/vo_navi_(\d+)\.acb$/', 'exportTo'=> 'sound/unit_common/$1' ],
//     // [ 'bundleNameMatch'=>'/^v\/vo_enavi_(\d+)\.acb$/', 'exportTo'=> 'sound/unit_common/$1' ],
//     // [ 'bundleNameMatch'=>'/^v\/t\/vo_adv_(\d+)\.acb$/', 'exportTo'=> 'sound/story_vo/$1' ],
//     [ 'bundleNameMatch'=>'/^v\/vo_btl_(\d+)\.acb$/', 'exportTo'=> 'sound/unit_battle_voice/$1' ],
//     [ 'bundleNameMatch'=>'/^v\/vo_(ci|title|speciallogin)_(\d+)\.acb$/', 'exportTo'=> 'sound/vo_$1/$2' ],
//   ],
//   'movie'=>[
// //    [ 'bundleNameMatch'=>'/^m\/(t\/)?(.+?)_(\d[\d_]*)\.usm$/', 'exportTo'=> 'movie/$2/$3' ],
// //    [ 'bundleNameMatch'=>'/^m\/(t\/)?(.+)\.usm$/', 'exportTo'=> 'movie/$2' ],
//   ]
];

function exportSpine($asset, $remoteTime) {
  foreach ($asset->preloadTable as $item) {
    if ($item->typeString == 'TextAsset') {
      $item = new TextAsset($item, true);

      if (substr($item->name, -5, 5) == '.skel') {
        $item->name = 'Skel '.substr($item->name, 0, -5).'.odg';
      } else if (substr($item->name, -5, 5) == '.cysp') {
        $item->name = substr($item->name, 0, -5).'.odg';
      } else if (substr($item->name, -6, 6) == '.atlas') {
        $item->name = 'Spineq '.substr($item->name, 0, -6).'.odp';
      }
      // base chara skeleton
      // class type animation
      // character skill animation
      checkAndCreateFile(RESOURCE_PATH_PREFIX.'spine/unitq/'.$item->name, $item->data, $remoteTime);
    }
  }
}
function exportAtlas($asset, $remoteTime) {
  foreach ($asset->preloadTable as $item) {
    if ($item->typeString == 'TextAsset') {
      $item = new TextAsset($item, true);
      if (substr($item->name, -5, 5) == '.skel') {
        $item->name = 'Skel '.substr($item->name, 0, -5).'.odg';
      } else if (substr($item->name, -5, 5) == '.cysp') {
        $item->name = substr($item->name, 0, -5).'.odg';
      } else if (substr($item->name, -6, 6) == '.atlas') {
        $item->name = 'Spineq '.substr($item->name, 0, -6).'.odp';
      }
      checkAndCreateFile(RESOURCE_PATH_PREFIX.'spine/unitq/'.$item->name, $item->data, $remoteTime);
    } else if ($item->typeString == 'Texture2D') {
      $item = new Texture2D($item, true);
      $item->name = 'Unitq '.$item->name;
      $saveTo = RESOURCE_PATH_PREFIX.'spine/unitq/'.$item->name;
      $item->exportTo($saveTo, 'png');
      if (filemtime($saveTo.'.png') > $remoteTime)
      touch($saveTo.'.png', $remoteTime);
    }
  }
}
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

      $storyStillName = json_decode(file_get_contents(RESOURCE_PATH_PREFIX.'spine/still_name.json'), true);
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
      file_put_contents(RESOURCE_PATH_PREFIX.'spine/still_name.json', json_encode($storyStillName));
    }
  }
}
function exportStoryStill($asset, $remoteTime) {
  foreach ($asset->preloadTable as $item) {
    if ($item->typeString == 'TextAsset') {
      $item = new TextAsset($item, true);
      if (substr($item->name, -5, 5) == '.skel') {
        $item->name = 'Skel '.substr($item->name, 0, -5).'.odg';
      } else if (substr($item->name, -5, 5) == '.cysp') {
        $item->name = substr($item->name, 0, -5).'.odg';
      } else if (substr($item->name, -6, 6) == '.atlas') {
        $item->name = 'Spine '.substr($item->name, 0, -6).'.odp';
      }
      checkAndCreateFile(RESOURCE_PATH_PREFIX.'spine/unit/'.$item->name, $item->data, $remoteTime);
    } else if ($item->typeString == 'Texture2D') {
      $item = new Texture2D($item, true);
      $item->name = 'Unit '.$item->name;
      $saveTo = RESOURCE_PATH_PREFIX.'spine/unit/'.$item->name;
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
    list($name, $hash, $hash2, $stage, $size) = explode(',', $line);
    $list[$name] = [
      'hash' =>$hash,
      'hash2' =>$hash2,
      'size' =>$size
    ];
  }
  unset($manifest);
  return $list;
}
$cacheHashDb = new PDO('sqlite:'.__DIR__.'/cacheHash.db');
// _log('delete cache');
// $cacheHashDb->exec('DROP TABLE cacheHash;');
// $cacheHashDb->exec('DROP TABLE textureHash;');
// $cacheHashDb->exec('DELETE FROM cacheHash WHERE res like \'%1307%\';');
// $cacheHashDb->exec('DELETE FROM textureHash WHERE res like \'%1307%\';');
$cacheHashDb->exec('CREATE TABLE IF NOT EXISTS cacheHash (res TEXT, hash TEXT);');
$cacheHashDb->exec('CREATE TABLE IF NOT EXISTS textureHash (res TEXT, hash TEXT);');
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
  _log('update texture hash '.$name.' '.$item->imageDataHash);
  $setTextureHashStmt->execute([$name, $item->imageDataHash]);
}

define('RESOURCE_PATH_PREFIX', '/mnt/d/Extra/pcr/_redive/');

function checkSubResource($manifest, $rules) {
  global $curl;
  foreach ($manifest as $name => $info) {
    if (($rule = findRule($name, $rules)) !== false && shouldUpdate($name, $info['hash'])) {
      _log('download1 '. $name.' '.$info['hash'].' '.$info['hash2']);
      curl_setopt_array($curl, array(
        CURLOPT_URL=>'http://prd-priconne-redive.akamaized.net/dl/pool/AssetBundles/'.substr($info['hash'],0,2).'/'.$info['hash'],
      ));
      $bundleData = curl_exec($curl);
      $remoteTime = curl_getinfo($curl, CURLINFO_FILETIME);
      $remoteTime = time();
      if (md5($bundleData) != $info['hash']) {
        _log('retry download '. $name.' '.$info['hash2']);
        curl_setopt_array($curl, array(
          CURLOPT_URL=>'http://prd-priconne-redive.akamaized.net/dl/pool/AssetBundles/'.substr($info['hash2'],0,2).'/'.$info['hash2'],
        ));
        $bundleData = curl_exec($curl);
          if (md5($bundleData) != $info['hash']) {
          _log('download failed  '.$name.' '.md5($bundleData));
          continue;
        }
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
              if (filemtime($saveTo. '.png') > $remoteTime)
              touch($saveTo. '.png', $remoteTime);
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

function checkSoundResource($manifest, $rules) {
  global $curl;
  foreach ($manifest as $name => &$info) {
    $info['hasAwb'] = false;
    if (substr($name, -4, 4) === '.awb') {
      $manifest[ substr($name, 0, -4) .'.acb' ]['hasAwb'] = true;
      $manifest[ substr($name, 0, -4) .'.acb' ]['awbName'] = $name;
      $manifest[ substr($name, 0, -4) .'.acb' ]['awbInfo'] = $info;
    }
  }
  foreach ($manifest as $name => $info) {
    if (($rule = findRule($name, $rules)) !== false && shouldUpdate($name, $info['hash'])) {
      _log('download2 '. $name.' '.$info['hash']);
      curl_setopt_array($curl, array(
        CURLOPT_URL=>'http://prd-priconne-redive.akamaized.net/dl/pool/Sound/'.substr($info['hash'],0,2).'/'.$info['hash'],
      ));
      $acbData = curl_exec($curl);
      $remoteTime = curl_getinfo($curl, CURLINFO_FILETIME);
      $remoteTime = time();
      if (md5($acbData) != $info['hash']) {
        _log('download failed  '.$name);
        continue;
      }
      $acbFileName = pathinfo($name, PATHINFO_BASENAME);

      // has streaming awb, download it
      if ($info['hasAwb']) {
        $awbName = $info['awbName'];
        $awbInfo = $info['awbInfo'];
        _log('download3 '. $awbName.' '.$awbInfo['hash']);
        curl_setopt_array($curl, array(
          CURLOPT_URL=>'http://prd-priconne-redive.akamaized.net/dl/pool/Sound/'.substr($awbInfo['hash'],0,2).'/'.$awbInfo['hash'],
        ));
        $awbData = curl_exec($curl);
        if (md5($awbData) != $awbInfo['hash']) {
          _log('download failed  '.$awbName);
          continue;
        }
        $awbFileName = pathinfo($awbName, PATHINFO_BASENAME);
        file_put_contents($awbFileName, $awbData);
      }
      file_put_contents($acbFileName, $acbData);

      // call acb2wavs
      $nullptr = NULL;
      // https://github.com/esterTion/libcgss/blob/master/src/apps/acb2wavs/acb2wavs.cpp
      exec('acb2wavs '.$acbFileName.' -b 00000000 -a 0030D9E8 -n', $nullptr);
      $acbUnpackDir = '_acb_'.$acbFileName;
      $saveTo = RESOURCE_PATH_PREFIX. preg_replace($rule['bundleNameMatch'], $rule['exportTo'], $name);
      foreach (['internal', 'external'] as $awbFolder)
        if (file_exists($acbUnpackDir .'/'. $awbFolder)) {
          foreach (glob($acbUnpackDir .'/'. $awbFolder.'/*.wav') as $waveFile) {
            $m4aFile = substr($waveFile, 0, -3).'m4a';
            $finalPath = $saveTo.'/'.pathinfo($m4aFile, PATHINFO_BASENAME);
            exec('ffmpeg -hide_banner -loglevel quiet -y -i '.$waveFile.' -vbr 5 -movflags faststart '.$m4aFile, $nullptr);
            checkAndMoveFile($m4aFile, $finalPath, $remoteTime);
            if (filemtime($finalPath) > $remoteTime)
            touch($finalPath, $remoteTime);
          }
        }
      delTree($acbUnpackDir);
      unlink($acbFileName);
      $info['hasAwb'] && unlink($awbFileName);
      if (isset($rule['print'])) exit;
      setHashCached($name, $info['hash']);
    }
  }
}
function checkMovieResource($manifest, $rules) {
  global $curl;
  $curl_movie = curl_copy_handle($curl);
  mkdir('usm_temp', 0777, true);
  foreach ($manifest as $name => $info) {
    if (($rule = findRule($name, $rules)) !== false && shouldUpdate($name, $info['hash'])) {
      _log('download4 '. $name.' '.$info['hash']);
      $usmFileName = pathinfo($name, PATHINFO_BASENAME);
      $usmFilePath = 'usm_temp/'.$usmFileName;
      $fh = fopen($usmFilePath, 'w');
      curl_setopt_array($curl, array(
        CURLOPT_URL=>'http://prd-priconne-redive.akamaized.net/dl/pool/Movie/'.substr($info['hash'],0,2).'/'.$info['hash'],
        CURLOPT_RETURNTRANSFER=>false,
        CURLOPT_FILE => $fh
      ));
      curl_exec($curl);
      $remoteTime = curl_getinfo($curl, CURLINFO_FILETIME);
      $remoteTime = time();
      if (md5_file($usmFilePath) != $info['hash']) {
        _log('download failed  '.$name);
        unlink($usmFilePath);
        continue;
      }

      // call UsmDemuxer
      // https://github.com/esterTion/UsmDemuxer
      $nullptr = NULL;
      exec('mono UsmDemuxer.exe '.$usmFilePath, $nullptr);
      unlink($usmFilePath);
      $streams = glob(substr($usmFilePath, 0, -4).'_*');
      $videoFile = '';
      $audioFiles = [];
      foreach ($streams as $stream) {
        $ext = pathinfo($stream, PATHINFO_EXTENSION);
        if ($ext == 'hca' || $ext == 'bin') {
          $waveFile = substr($stream, 0, -3). 'wav';
          exec('hca2wav '.$stream.' '.$waveFile.' 0030D9E8 00000000', $nullptr);
          $audioFiles[] = $waveFile;
        } else if ($ext == 'adx') {
          $audioFiles[] = $stream;
        } else if ($ext == 'm2v') {
          $videoFile = $stream;
        } else {
          _log('---unknown stream '.$stream);
        }
      }
      if (empty($videoFile)) {
        _log('---no video stream found');
        setHashCached($name, $info['hash']);
        continue;
      }
      $saveTo = RESOURCE_PATH_PREFIX. preg_replace($rule['bundleNameMatch'], $rule['exportTo'], $name);
      $code=0;
      exec('ffmpeg -hide_banner -loglevel quiet -y -i '.$videoFile.' '.implode(' ', array_map(function($i){return '-i '.$i;}, $audioFiles)).' '.(empty($audioFiles)?'':'-filter_complex amix=inputs='.count($audioFiles).':duration=longest').' -c:v copy '.(empty($audioFiles)?'':'-c:a aac -vbr 5').' -movflags faststart out.mp4', $nullptr, $code);
      if ($code !==0 || !file_exists('out.mp4')) {
        _log('encode failed, code '.$code);
        _log('ffmpeg -hide_banner -loglevel quiet -y -i '.$videoFile.' '.implode(' ', array_map(function($i){return '-i '.$i;}, $audioFiles)).' '.(empty($audioFiles)?'':'-filter_complex amix=inputs='.count($audioFiles).':duration=longest').' -c:v copy '.(empty($audioFiles)?'':'-c:a aac -vbr 5').' -movflags faststart out.mp4');
        delTree('usm_temp');
        mkdir('usm_temp');
        continue;
      }

      $saveToFull = $saveTo .'.mp4';

      // avc chk
      $shouldReencode = false;
      if (filesize('out.mp4') > 10*1024*1024) $shouldReencode = true;
      if (!$shouldReencode) {
        $mp4 = new FileStream('out.mp4');
        $mp4->littleEndian = false;
        $ftypLen = $mp4->ulong;
        $mp4->position = $ftypLen;
        $moovLen = $mp4->ulong;
        $moov = $mp4->readData($moovLen - 4);
        unset($mp4);
        $shouldReencode = strpos($moov, 'avcC') === false;
      }
      if ($shouldReencode) {
        // > 10M / not avc, reencode
        _log('reencoding to avc');
        rename('out.mp4', 'out_ori.mp4');
        exec('ffmpeg -hide_banner -loglevel quiet -y -i out_ori.mp4 -c copy -c:v h264 -crf 20 -movflags faststart out.mp4', $nullptr);
        //checkAndMoveFile('out_ori.mp4', $saveTo.'_ori.mp4', $remoteTime);
        unlink('out_ori.mp4');
      }

      checkAndMoveFile('out.mp4', $saveToFull, $remoteTime);

      unlink($videoFile);
      array_map('unlink', $audioFiles);
      if (isset($rule['print'])) exit;
      setHashCached($name, $info['hash']);
    }
  }
  delTree('usm_temp');
}
function delTree($dir) {
  $files = array_diff(scandir($dir), array('.','..'));
  foreach ($files as $file) {
    (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
  }
  return rmdir($dir);
}

function checkAndUpdateResource($TruthVersion) {
  global $resourceToExport;
  global $curl;
  chdir(__DIR__);
  _log('TruthVersion: '.$TruthVersion);
  curl_setopt_array($curl, array(
    CURLOPT_URL=>'http://prd-priconne-redive.akamaized.net/dl/Resources/'.$TruthVersion.'/Jpn/AssetBundles/iOS/manifest/manifest_assetmanifest',
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
    $name = "manifest/${name}2_assetmanifest";
    if (isset($manifest[$name]) && shouldUpdate($name, $manifest[$name]['hash'])) {
    // if (isset($manifest[$name])) {
      _log('update '.$name);
      curl_setopt_array($curl, array(
        CURLOPT_URL=>'http://prd-priconne-redive.akamaized.net/dl/Resources/'.$TruthVersion.'/Jpn/AssetBundles/iOS/'.$name,
      ));
      $submanifest = curl_exec($curl);
      if (md5($submanifest) != $manifest[$name]['hash']) {
        _log('download failed  '.$name);
        continue;
      }
      $submanifest = parseManifest($submanifest);
      checkSubResource($submanifest, $rules);
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
  return;
  // sound res check
  do {
    curl_setopt_array($curl, array(
      CURLOPT_URL=>'http://prd-priconne-redive.akamaized.net/dl/Resources/'.$TruthVersion.'/Jpn/Sound/manifest/sound2manifest',
    ));
    $submanifest = curl_exec($curl);
    $submanifest = parseManifest($submanifest);
    checkSoundResource($submanifest, $resourceToExport['sound']);
  } while(0);

  // movie res check
  do {
    $name = "manifest/moviemanifest";
    curl_setopt_array($curl, array(
      CURLOPT_URL=>'http://prd-priconne-redive.akamaized.net/dl/Resources/'.$TruthVersion.'/Jpn/Movie/SP/High/'.$name,
    ));
    $submanifest = curl_exec($curl);
    $submanifest = parseManifest($submanifest);
    checkMovieResource($submanifest, $resourceToExport['movie']);
  } while(0);
}
if (defined('TEST_SUITE') && TEST_SUITE == __FILE__) {
  chdir(__DIR__);
  $curl = curl_init();
  function _log($s) {echo "$s\n";}
  if (!file_exists('data/!TruthVersion.txt')) exit;
  $ver = trim(file_get_contents('data/!TruthVersion.txt'));
  var_dump($ver);
  checkAndUpdateResource($ver);
  /*$assets = extractBundle(new FileStream('bundle/spine_000000_chara_base.cysp.unity3d'));
  $asset = new AssetFile($assets[0]);
  foreach ($asset->preloadTable as $item) {
    if ($item->typeString == 'TextAsset') {
      $item = new TextAsset($item, true);
      print_r($item);
    }
  }*/
}
//print_r($asset);

