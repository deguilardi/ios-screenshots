<?php
$app = $_REQUEST['app'];
$iphone4Mask = $_REQUEST['iphone4-mask'] ? true : false;

set_time_limit(0);
$langsDir = $app.'/';
$masksDir = $langsDir.'_masks/';
$outputDir = $langsDir.'_output/';
$langs = scandir($langsDir);

function shouldIgnore($input){
  return ($input == '.' || $input == '..' || $input == '.DS_Store' || $input == '_masks' || $input == '_output');
}

function makeIt($pressetName, $width, $height, $aspect, $lang, $shoot){
    global $masksDir, $langsDir, $outputDir, $bannerColor, $iphone4Mask;
    $out = imagecreatetruecolor($width, $height);
    $mask = imagecreatefrompng($masksDir.$lang.'/'.$shoot);
    $screen = imagecreatefrompng($langsDir.'/'.$lang.'/'.$shoot);

    if($pressetName == '3.5'){
      if($iphone4Mask){
        imagecopyresampled($out, $screen, 66, 58, 0, 0, 510, 908, 1242, 2208);
      }
      else{
        imagecopyresampled($out, $screen, 0, 0, 0, 0, 640, 1050, 1242, 2208);
      }
      $frame = imagecreatefrompng($masksDir.'_3.5.png');
      if($iphone4Mask){
        imagecopyresampled($out, $frame, 0, 0, 0, 0, $width, $height, $width, $height);
      }
      imagecopyresampled($out, $mask, 0, 0, 0, 0, $width, $height+50, 1242, 2208);
    }
    else if($aspect == '4:3'){
      imagecopyresampled($out, $screen, 0, 0, $width*0.03, 0, $width, $height, 2048*0.95, 2732*0.91); 
      
    }
    else{
      imagecopyresampled($out, $screen, 0, 0, 2732/16*9*0.17, 0, $width, $height, 2732/16*9, 2732*0.93);
      imagecopyresampled($out, $mask, 0, 0, 0, 0, $width, $height, 1242, 2208);     
    }
    
    imagepng($out, $outputDir.$pressetName.'-'.$lang.'-'.$shoot);
}

foreach($langs as $lang){
  if(shouldIgnore($lang)){ continue; }

  $shoots = scandir($langsDir.'/'.$lang);
  foreach($shoots as $shoot){
    if(shouldIgnore($shoot)){ continue; }

    makeIt('12.9', 2048, 2732, '4:3', $lang, $shoot);
    makeIt('5.5', 1242, 2208, '16:9', $lang, $shoot);
    //makeIt('4.7', 750, 1334, '16:9', $lang, $shoot);
    //makeIt('4.0', 640, 1136, '16:9', $lang, $shoot);
    //makeIt('3.5', 640, 960, '3:2', $lang, $shoot);
  }
}