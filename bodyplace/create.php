<?php
$BannerColor = [0,0,0];

set_time_limit(0);
$langsDir = './';
$masksDir = $langsDir.'_masks/';
$outputDir = $langsDir.'_output/';
$langs = scandir($langsDir);

function shouldIgnore($input){
  return ($input == '.' || $input == '..' || $input == '.DS_Store' || $input == '_masks' || $input == '_output' || $input == 'create.php');
}

function makeIt($pressetName, $width, $height, $lang, $shoot, $x, $y, $shootWidth ){
    global $masksDir, $langsDir, $outputDir, $BannerColor;
    $shootHeight = $shootWidth/0.56;
    $out = imagecreatetruecolor($width, $height);
    $mask = imagecreatefrompng($masksDir.$lang.'/'.$pressetName.'/'.$shoot);
    $screen = imagecreatefrompng($langsDir.'/'.$lang.'/'.$shoot);

    imagecopyresampled($out, $mask, 0, 0, 0, 0, $width, $height, $width, $height);
    imagecopyresampled($out, $screen, $x, $y, 0, 0, $shootWidth, $shootHeight, 1490, 2665);
    
    imagepng($out, $outputDir.$pressetName.'-'.$lang.'-'.$shoot);
    //header("Content-Type:image/png"); imagepng($out); die();
}

foreach($langs as $lang){
  if(shouldIgnore($lang)){ continue; }

  $shoots = scandir($langsDir.'/'.$lang);
  foreach($shoots as $shoot){
    if(shouldIgnore($shoot)){ continue; }
    
    makeIt('ipad-pro', 2048, 2732, $lang, $shoot, 280, 740, 1480);
    makeIt('ipad', 1536, 2048, $lang, $shoot, 200, 545, 1110);
    makeIt('5.5', 1242, 2208, $lang, $shoot, 170, 595, 910);
    makeIt('4.7', 750, 1334, $lang, $shoot, 100, 365, 545);
    makeIt('4.0', 640, 1136, $lang, $shoot, 95, 335, 455);
    makeIt('3.5', 640, 960, $lang, $shoot, 95, 335, 455);
  }
}