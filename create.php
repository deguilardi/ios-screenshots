<?php
ini_set('memory_limit', '4095M');

$app = $_REQUEST['app'];
$mockup = $_REQUEST['mockup'];

function _log($msg){
  echo '<br />' . $msg;
}

function shouldIgnore($input){
  return ($input == '.' || $input == '..' || $input == '.DS_Store' || $input == '_masks' || $input == '_output' || $input == '.git' || $input == '.gitiginore' || $input == 'create.php');
}

if(!$app || !$mockup){
?>

<form name="form" action="./create.php" method="get">
  <select name="app">
    <option value="">SELECIONE</option>

<?
  $apps = scandir(".");
  foreach($apps as $app){
    if(shouldIgnore($app)){ continue; }
    echo '<option value="' . $app. '">' . $app. '</option>';
  }
?>

  </select>

  <br />Mockup?
  <label><input type="radio" name="mockup" value="false" onclick="this.form.submit()"> não</label>
  <label><input type="radio" name="mockup" value="true" onclick="this.form.submit()"> sim</label>

</form>

<?
  die();
}
?>

<br /><br />
<a href="create.php">VOLTAR</a>


<?php
$hasMockup = ($mockup == 'true') ? true : false;
set_time_limit(0);
$langsDir = $app.'/';
$masksDir = $langsDir.'_masks/';
$outputDir = $langsDir.'_output/';
$langs = scandir($langsDir);

$backgroundImage = @imagecreatefrompng($masksDir.'background.png');
$mockupImageIphone = @imagecreatefrompng($masksDir.'Mockup-iphone.png');
$mockupImagePixel = @imagecreatefrompng($masksDir.'Mockup-pixel.png');
_log("verifying mockup files background:".$backgroundImage." iPhone:".$mockupImageIphone." pixel:".$mockupImagePixel);

 
foreach($langs as $lang){
  if(shouldIgnore($lang)){ continue; }

  $shoots = scandir($langsDir.'/'.$lang);
  $i = 1;
  foreach($shoots as $shoot){
    if(shouldIgnore($shoot)){ continue; }

    makeIt('12.9', 2048, 2732, '4:3', $lang, $shoot, $i);
    makeIt('5.5', 1242, 2208, '16:9', $lang, $shoot, $i);
    //makeIt('4.7', 750, 1334, '16:9', $lang, $shoot, $i);
    //makeIt('4.0', 640, 1136, '16:9', $lang, $shoot, $i);
    //makeIt('3.5', 640, 960, '3:2', $lang, $shoot, $i);
    $i++;
  }
}


function makeIt($pressetName, $width, $height, $aspect, $lang, $shoot, $i){
    _log("making ".$pressetName);
    global $masksDir, $langsDir, $outputDir, $bannerColor, $hasMockup, $backgroundImage, $mockupImageIphone, $mockupImagePixel;
    $out = imagecreatetruecolor($width, $height);
    $mask = imagecreatefrompng($masksDir.$lang.'/'.$shoot);
    $screen = imagecreatefrompng($langsDir.'/'.$lang.'/'.$shoot);
    _log("opening files mask:" . $mask . " screen:".$screen);

    // tablet
    if($aspect == '4:3'){
      $newX = 2732/16*9*0.17;
      $newWidth = 2732/16*9;
      $newHeight = 2732*0.93;
      if(!$hasMockup){
        imagecopyresampled($out, $screen, 0, 0, $width*0.03, 0, $width, $height, 2048*0.95, $height*0.91);
        imagepng($out, $outputDir.$pressetName.'-'.$lang.'-'.$shoot);
        imagedestroy($out); 
      }
      elseif($backgroundImage && $mockupImageIphone && $mockupImagePixel){

        // background
        $backgroundOriginX = ($width * $i - $width) * (-1);
        imagecopyresampled($out, $backgroundImage, $backgroundOriginX, 0, 0, 0, $width*4, $height, 8192, $height);

        // screen
        imagecopyresampled($out, $screen, 510, 910, $newX, 0, $width * 0.50, $height * 0.65, $newWidth, $newHeight);

        // iphone
        $outIphone = imagecreatetruecolor($width, $height);
        imagecopy($outIphone, $out, 0, 0, 0, 0, $width, $height);
        imagecopyresampled($outIphone, $mockupImageIphone, 0, 0, 0, 0, $width, $height, $width, $height);
        imagecopyresampled($outIphone, $mask, 0, 0, 0, 0, $width, $height*1.2, 1242, 2208);
        imagepng($outIphone, $outputDir.$pressetName.'-'.$lang.'-iphone-'.$shoot);
        imagedestroy($outIphone);

        // pixel
        $outPixel = imagecreatetruecolor($width, $height);
        imagecopy($outPixel, $out, 0, 0, 0, 0, $width, $height);
        imagecopyresampled($outPixel, $mockupImagePixel, 0, 0, 0, 0, $width, $height, $width, $height);
        imagecopyresampled($outPixel, $mask, 0, 0, 0, 0, $width, $height*1.2, 1242, 2208);
        $saved = imagepng($outPixel, $outputDir.$pressetName.'-'.$lang.'-pixel-'.$shoot);
        _log("saving pixel:".$saved);
        imagedestroy($outPixel);

        imagedestroy($out); 
      }
    }

    // phone
    else{
      $newX = 2732/16*9*0.17;
      $newWidth = 2732/16*9;
      $newHeight = 2732*0.93;
      if(!$hasMockup){
        _log("creating without mockup");
        imagecopyresampled($out, $screen, 0, 0, $newX, 0, $width, $height, $newWidth, $newHeight);
        imagecopyresampled($out, $mask, 0, 0, 0, 0, $width, $height, $width, 2208);  
        imagepng($out, $outputDir.$pressetName.'-'.$lang.'-'.$platform.$shoot);
        imagedestroy($out); 
      }
      elseif($backgroundImage && $mockupImageIphone && $mockupImagePixel){
        _log("creating with mockup");
        // background
        $backgroundOriginX = ($width * $i - $width) * (-1);
        imagecopyresampled($out, $backgroundImage, $backgroundOriginX, 0, 0, 0, $width*4, $height, 8192, $height);

        // screen
        imagecopyresampled($out, $screen, 180, 640, $newX, 0, $width * 0.7, $height * 0.7, $newWidth, $newHeight);

        // iphone
        $outIphone = imagecreatetruecolor($width, $height);
        imagecopy($outIphone, $out, 0, 0, 0, 0, $width, $height);
        imagecopyresampled($outIphone, $mockupImageIphone, 0, 0, $newX, 170, $width, $height, $newWidth, $newHeight);
        imagecopyresampled($outIphone, $mask, 0, 0, 0, 0, $width, $height, 1242, 2208);
        imagepng($outIphone, $outputDir.$pressetName.'-'.$lang.'-iphone-'.$shoot);
        imagedestroy($outIphone);

        // pixel
        $outPixel = imagecreatetruecolor($width, $height);
        imagecopy($outPixel, $out, 0, 0, 0, 0, $width, $height);
        imagecopyresampled($outPixel, $mockupImagePixel, 0, 0, $newX, 170, $width, $height, $newWidth, $newHeight);
        imagecopyresampled($outPixel, $mask, 0, 0, 0, 0, $width, $height, 1242, 2208);
        $saved = imagepng($outPixel, $outputDir.$pressetName.'-'.$lang.'-pixel-'.$shoot);
        _log("saving pixel:".$saved);
        imagedestroy($outPixel);

        imagedestroy($out); 
      } 
    }
}
?>