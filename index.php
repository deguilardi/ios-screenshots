<?php
ini_set('memory_limit', '4095M');

$app = $_REQUEST['app'];
$mockup = $_REQUEST['mockup'];
$versions = $_REQUEST['versions'];

function _log($msg){
  echo '<br />' . $msg;
}

function shouldIgnore($input){
  return ($input == '.' || $input == '..' || $input == '.DS_Store' || $input == '_masks' || $input == '_output' || $input == '.git' || $input == '.gitignore' || $input == 'index.php' || $input == '_support' );
}

if(!$app || !$mockup || !$versions){
?>

<form name="form" action="./index.php" method="get">
  1. <select name="app">
    <option value="">PICK AN APP</option>

<?
  $apps = scandir(".");
  foreach($apps as $app){
    if(shouldIgnore($app)){ continue; }
    echo '<option value="' . $app. '">' . $app. '</option>';
  }
?>

  </select>

  <br />2. Mockup?
  <label><input type="radio" name="mockup" value="false"> no</label>
  <label><input type="radio" name="mockup" value="true" selected="selected"> yes</label>

  <br />3. Versions
  <br /><label><input type="checkbox" name="versions[]" value="5.5" > 5.5 phone (2048x2732 android and ios)</label>
  <br /><label><input type="checkbox" name="versions[]" value="6.5" > 6.5 phone (2688x2732 ios)</label>
  <br /><label><input type="checkbox" name="versions[]" value="12.9"> 12.9 tablet (1232x2208 android and ios)</label>
  <br /><label><input type="checkbox" name="versions[]" value="amazon" > amazon generic (1080x1920)</label>

  <br />
  <input type="submit" value="generate" onclick="this.form.submit()" />

</form>

<?
  die();
}
?>

<br /><br />
<a href="index.php">VOLTAR</a>


<?php
$hasMockup = ($mockup == 'true') ? true : false;
set_time_limit(0);
$langsDir = $app.'/';
$masksDir = $langsDir.'_masks/';
$outputDir = $langsDir.'_output/';
$langs = scandir($langsDir);

$backgroundImage = @imagecreatefrompng($masksDir.'background.png');
$mockupImageIphone = @imagecreatefrompng('./_support/Mockup-iphone.png');
$mockupImageIphoneX = @imagecreatefrompng('./_support/Mockup-iphone-x.png');
$mockupImageIpad = @imagecreatefrompng('./_support/Mockup-ipad.png');
$mockupImagePixel = @imagecreatefrompng('./_support/Mockup-pixel.png');
$mockupImageTablet = @imagecreatefrompng('./_support/Mockup-android-tablet.png');
_log("verifying mockup files background:".$backgroundImage." iPhone:".$mockupImageIphone." pixel:".$mockupImagePixel);

 
foreach($langs as $lang){
  if(shouldIgnore($lang)){ continue; }

  $shoots = scandir($langsDir.'/'.$lang);
  $i = 1;
  foreach($shoots as $shoot){
    if(shouldIgnore($shoot)){ continue; }
    foreach ($versions as $version) {
        switch($version){
            case "5.5":
                makeIt('5.5', 1242, 2208, '16:9', $lang, $shoot, $i);
                break;
            case "6.5":
                makeIt('6.5', 1242, 2688, '19.5:9', $lang, $shoot, $i);
                break;
            case "12.9":
                makeIt('12.9', 2048, 2732, '4:3', $lang, $shoot, $i);
                break;
            case "amazon":
                makeIt('amazon', 1080, 1920, '16:9', $lang, $shoot, $i);
                break;
        }
    }
    // break; // run 1 image only
    $i++;
  }
  // break; // run 1 language only
}


function makeIt($pressetName, $width, $height, $aspect, $lang, $shoot, $i){
    _log("<br />making ".$i.": ".$pressetName."  ".$width."x".$height." (".$aspect.")"." ".$lang." shoot:".$shoot);
    global $masksDir, $langsDir, $outputDir, $bannerColor, $hasMockup, $backgroundImage, $mockupImageIphone, $mockupImageIphoneX, $mockupImagePixel, $mockupImageIpad, $mockupImageTablet;
    $out = imagecreatetruecolor($width, $height);
    $mask = imagecreatefrompng($masksDir.$lang.'/'.$shoot);
    $screen = imagecreatefrompng($langsDir.'/'.$lang.'/'.$shoot);
    _log("opening files mask:" . $mask . " screen:".$screen);

    // tablet
    if($aspect == '4:3'){
      $newX = 2732/16*9*0.17;
      $newWidth = 2732/16*9;
      $newHeight = 2732*0.93;
      $backgroundW = imagesx($backgroundImage);

      // correct mask fit
      // old versions are phone size, new versions are tablet size
      $maskW = imagesx($mask);
      if($maskW == 1242){
        $maskHMultiplier = 1.2;
        $maskSrcX = 0;
      }
      else{
        $maskHMultiplier = 1.0;
        $maskSrcX = 0;
      }
      $maskDestH = $height * $maskHMultiplier;

      $backgroundWillFit = (2048 * $i <= $backgroundW);
      echo " - " . $backgroundW . " - ". (2048 * $i);

      if(!$hasMockup || !$backgroundWillFit){
        imagecopyresampled($out, $screen, $dstX=0, $dstY=0, $srcX=$width*0.03, $srcY=0, $dstW=$width, $dstH=$height, $srcW=2048*0.95, $srcH=$height*0.91);
        if( $pressetName != "amazon" ){
          imagecopyresampled($out, $mask, $dstX=0, $dstY=0, $srcX=$maskSrcX, $srcY=0, $dstW=$width, $dstH=$maskDestH, $srcW=$maskW, $srcH=2208);  
        }
        imagepng($out, $outputDir.$pressetName.'-'.$lang.'-'.$shoot);
        imagedestroy($out); 
      }
      elseif($backgroundImage && $mockupImageIphone && $mockupImageTablet){
        $bgOriginX = ($width * $i - $width) * (-1);
        imagecopyresampled($out, $backgroundImage, $dstX=$bgOriginX, $dstY=0  , $srcX=0  , $srcY=0, $dstW=$width*8, $dstH=$height, $srcW=10240, $srcH=$height);
        imagecopyresampled($out, $screen         , $dstX=180       , $dstY=460, $srcX=100, $srcY=0, $dstW=1670    , $dstH=2260   , $srcW=1880 , $srcH=$newHeight);

        // ipad
        $outIpad = imagecreatetruecolor($width, $height);
        imagecopy($outIpad, $out, 0, 0, 0, 0, $width, $height);
        imagecopyresampled($outIpad, $mockupImageIpad, $dstX=0  , $dstY=0  , $srcX=0  , $srcY=0, $dstW=$width, $dstH=$height   , $srcW=$width, $srcH=$height);
        imagecopyresampled($outIpad, $mask           , $dstX=0  , $dstY=0  , $srcX=0  , $srcY=0, $dstW=$width, $dstH=$maskDestH, $srcW=$maskW, $srcH=2208);
        $saved = imagepng($outIpad, $outputDir.$pressetName.'-'.$lang.'-ipad-'.$shoot);
        _log("saving ipad:".$saved);
        imagedestroy($outIpad);

        // pixel
        $outPixel = imagecreatetruecolor($width, $height);
        imagecopy($outPixel, $out, 0, 0, 0, 0, $width, $height);
        imagecopyresampled($outPixel, $mockupImageTablet, $dstX=0  , $dstY=0  , $srcX=0  , $srcY=0, $dstW=$width, $dstH=$height   , $srcW=$width, $srcH=$height);
        imagecopyresampled($outPixel, $mask             , $dstX=0  , $dstY=0  , $srcX=0  , $srcY=0, $dstW=$width, $dstH=$maskDestH, $srcW=$maskW, $srcH=2208);
        $saved = imagepng($outPixel, $outputDir.$pressetName.'-'.$lang.'-pixel-'.$shoot);
        _log("saving tablet:".$saved);
        imagedestroy($outPixel);
      }
    }

    // phone
    else{
      // $newX = 2732/16*9*0.165;
      // $newWidth = 2732/16*9;
      $newX = 2732/$height*$width*0.165;
      $newWidth = 2732/$height*$width;
      $newHeight = 2732;
      $backgroundW = imagesx($backgroundImage);

      // correct mask fit
      // old versions are phone size, new versions are tablet size
      $maskW = imagesx($mask);
      if($maskW == 1242){
        $maskSrcX = 0;
      }
      else{
        $maskSrcX = 200;
      }

      $backgroundWillFit = (2048 * $i <= $backgroundW);

      if(!$hasMockup || $pressetName == "amazon" || !$backgroundWillFit){
        _log("creating without mockup [reason:$hasMockup:$pressetName:$backgroundWillFit]");
        imagecopyresampled($out, $screen, $dstX=0, $dstY=0, $srcX=$newX, $srcY=0, $dstW=$width, $dstH=$height, $srcW=$newWidth, $srcH=$newHeight);
        if( $pressetName != "amazon" ){
          imagecopyresampled($out, $mask, $dstX=0, $dstY=0, $srcX=$maskSrcX, $srcY=0, $dstW=$width, $dstH=$height, $srcW=1242, $srcH=2208);  
        }
        imagepng($out, $outputDir.$pressetName.'-'.$lang.'-'.$platform.$shoot);
        imagedestroy($out); 
      }
      elseif($backgroundImage && $mockupImageIphone && $mockupImagePixel){
        _log("creating with mockup");
        $bgOriginX = ($width * $i - $width) * (-1);
        imagecopyresampled(  $out      , $backgroundImage   , $dstX=$bgOriginX, $dstY=0  , $srcX=0        , $srcY=0, $dstW=$width*8   , $dstH=$height     , $srcW=$backgroundW , $srcH=$height);

        // iphone x
        if($aspect == '19.5:9'){
          imagecopyresampled($out      , $screen            , $dstX=103       , $dstY=560, $srcX=$newX+90 , $srcY=0, $dstW=$width*0.83, $dstH=$height*0.80, $srcW=$newWidth+200, $srcH=$newHeight);
          $outIphone = imagecreatetruecolor($width, $height);
          imagecopy($outIphone, $out, 0, 0, 0, 0, $width, $height);
          imagecopyresampled($outIphone, $mockupImageIphoneX, $dstX=0         , $dstY=0  , $srcX=$newX+200, $srcY=0, $dstW=$width     , $dstH=$height     , $srcW=$newWidth    , $srcH=$newHeight);
          imagecopyresampled($outIphone, $mask              , $dstX=0         , $dstY=0  , $srcX=$maskSrcX, $srcY=0, $dstW=$width     , $dstH=$height*0.88, $srcW=1242         , $srcH=2208);
          $saved = imagepng($outIphone, $outputDir.$pressetName.'-'.$lang.'-iphone-'.$shoot);
          _log("saving iphone X:".$saved);
          imagedestroy($outIphone);
        }
        else{

          // iphone
          imagecopyresampled($out      , $screen            , $dstX=145       , $dstY=495, $srcX=$newX    , $srcY=0, $dstW=$width*0.78, $dstH=$height*0.78, $srcW=$newWidth    , $srcH=$newHeight);
          $outIphone = imagecreatetruecolor($width, $height);
          imagecopy($outIphone, $out, 0, 0, 0, 0, $width, $height);
          imagecopyresampled($outIphone, $mockupImageIphone , $dstX=0         , $dstY=0  , $srcX=$newX    , $srcY=0, $dstW=$width     , $dstH=$height     , $srcW=$newWidth    , $srcH=$newHeight);
          imagecopyresampled($outIphone, $mask              , $dstX=0         , $dstY=0  , $srcX=$maskSrcX, $srcY=0, $dstW=$width     , $dstH=$height     , $srcW=1242         , $srcH=2208);
          $saved = imagepng($outIphone, $outputDir.$pressetName.'-'.$lang.'-iphone-'.$shoot);
          _log("saving iphone:".$saved);
          imagedestroy($outIphone);

          // pixel
          // $outPixel = imagecreatetruecolor($width, $height);
          // imagecopy($outPixel, $out, 0, 0, 0, 0, $width, $height);
          // imagecopyresampled($outPixel, $mockupImagePixel, $dstX=0, $dstY=0, $srcX=$newX    , $srcY=0, $dstW=$width, $dstH=$height, $srcW=$newWidth, $srcH=$newHeight);
          // imagecopyresampled($outPixel, $mask            , $dstX=0, $dstY=0, $srcX=$maskSrcX, $srcY=0, $dstW=$width, $dstH=$height, $srcW=1242     , $srcH=2208);
          // $saved = imagepng($outPixel, $outputDir.$pressetName.'-'.$lang.'-pixel-'.$shoot);
          // _log("saving pixel:".$saved);
          // imagedestroy($outPixel);
        }
      } 
    }

    imagedestroy($out); 
}
?>
