<?php

class Model_railCaptcha {
    var $debug = false;

    public function __construct() {
        $this->debug = false;
        
    }

    public function __destruct() {
        
    }

    /*
      $letterlist = array(
      array('id' => '1', 'letter' => '+', 'hash' => '0000000000000000100000000000000000000000000011101000000000000000000000000000100000000000000000000000000000100000000000000000000000000000100000000000001'),
      array('id' => '2', 'letter' => '=', 'hash' => '0000000000000010010000000000000000000000000010010000000000000000000000000010010000000000000000000000000010010000000000000000000000000010010000000000000000000000000010010000000000000000000000000010010000000000001'),
      array('id' => '3', 'letter' => '?', 'hash' => '0000000000010000000000000000000000000000100000000000000000000000000000100001101100000000000000000000100011101100000000000000000000111110000000000000000000000000011100000000000000001'),
      array('id' => '4', 'letter' => '0', 'hash' => '0000000000001111110000000000000000000000011111111000000000000000000000110000001000000000000000000000100000000000000000000000000000110000001000000000000000000000011111111000000000000000000000001111110000000000001'),
      array('id' => '5', 'letter' => '1', 'hash' => '0000000000010000000100000000000000000000010000000100000000000000000000111111111100000000000000000000111111111100000000000000000000000000000100000000001'),
      array('id' => '6', 'letter' => '2', 'hash' => '0000000000010000001000000000000000000000100000011000000000000000000000100000110000000000000000000000100001100000000000000000000000111111000000000000000000000000011110000000000000001'),
      array('id' => '7', 'letter' => '3', 'hash' => '0000000000010000001000000000000000000000100010000000000000000000000000100010000000000000000000000000100010000000000000000000000000100111001000000000000000000000111101111000000000000000000000011000110000000000001'),
      array('id' => '8', 'letter' => '4', 'hash' => '0000000000000010100000000000000000000000000100100000000000000000000000011000100000000000000000000000111111111100000000000000000000111111101000000000001'),
      array('id' => '9', 'letter' => '5', 'hash' => '0000000000100100000100000000000000000000100100000100000000000000000000100100000000000000000000000000100110001100000000000000000000100011010000000000000000000000100001100000000000001'),
      array('id' => '10', 'letter' => '6', 'hash' => '0000000000000111110000000000000000000000011111111000000000000000000000110001001000000000000000000000100010000000000000000000000000100010000000000000000000000000100011111000000000000000000000010001111000000000001'),
      array('id' => '11', 'letter' => '7', 'hash' => '0000000000100000000000000000000000000000100000001000000000000000000000100000111000000000000000000000100011110000000000000000000000101111000000000000000000000000111100000000000000000000000000110000000000000000001'),
      array('id' => '12', 'letter' => '8', 'hash' => '0000000000011100111000000000000000000000111111111000000000000000000000100111000000000000000000000000100011000000000000000000000000100111100000000000000000000000111101111000000000000000000000011000111000000000001'),
      array('id' => '13', 'letter' => '9', 'hash' => '0000000000011110001000000000000000000000111111000000000000000000000000100001000000000000000000000000100001000000000000000000000000110010001000000000000000000000011111111000000000000000000000001111110000000000001'),
      );
     */

    function getAllHashCodes() {
        $letterlist = array();
        $folder = dirname(__FILE__) . '/../resources/captcha';
        $cdir = scandir($folder);
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", "..")) && $value[0] != '.') {
                $hash = $this->getHashCode("{$folder}/$value");
                $name = @reset(explode('v', @reset(explode('.', $value))));
                $name = ($name == 'question') ? '?' : $name;
                $name = ($name == 'plus') ? '+' : $name;
                $name = ($name == 'equal') ? '=' : $name;
                $name = ($name == 'minus') ? '-' : $name;
                $letterlist[] = array('id' => 1, 'letter' => $name, 'hash' => $hash);
            }
        }
        return $letterlist;
    }

    function getHashCode($img, $obj = false) {
        if ($obj) {
            $src = $img;
        } else {
            $src = imagecreatefromjpeg($img) or die('Problem with source ' . $img);
        }
        $out = ImageCreateTrueColor(imagesx($src), imagesy($src)) or die('Problem In Creating image');
        $string = '';
// scan image pixels
        for ($x = 0; $x < imagesx($src); $x++) {
            for ($y = 0; $y < imagesy($src); $y++) {
                $src_pix = imagecolorat($src, $x, $y);
                $src_pix_array = $this->rgb_to_array($src_pix);


                if ($src_pix_array[0] > 100 && $src_pix_array[1] > 100 && $src_pix_array[2] > 100) {
                    $string .='0';
                } else {
                    $string .='1';
                }
            }
        }
        $string .='1';
        return $string;
    }

    function getCaptchaCode($imageString) {
        $time = time();
        $letterlist = $this->getAllHashCodes();
        $cc = new canvasCrop();
        //$src = imagecreatefromjpeg($imagePath) or die('Problem with source');
        $src = imagecreatefromstring($imageString) or die('Problem with source');
        imagefilter($src, IMG_FILTER_NEGATE);
        if($this->debug){
            imagejpeg($src, LOG_PATH.'/out-orig.jpeg', 150) or die('Problem saving output image');
        }
        

        $out = ImageCreateTrueColor(imagesx($src), imagesy($src)) or die('Problem In Creating image');
        
        

        // scan image pixels
        for ($x = 0; $x < imagesx($src); $x++) {
            for ($y = 0; $y < imagesy($src); $y++) {
                $src_pix = imagecolorat($src, $x, $y);
                $src_pix_array = $this->rgb_to_array($src_pix);

                if ($src_pix_array[0] > 50 && $src_pix_array[1] > 50 && $src_pix_array[2] > 50) {
                    $src_pix_array[0] = 0;
                    $src_pix_array[1] = 0;
                    $src_pix_array[2] = 0;
                } else {
                    $src_pix_array[0] = 255;
                    $src_pix_array[1] = 255;
                    $src_pix_array[2] = 255;
                    
                }

                imagesetpixel($out, $x, $y, imagecolorallocate($out, $src_pix_array[0], $src_pix_array[1], $src_pix_array[2]));
            }
        }
        /*
        for ($fx = 0; $fx < 60; $fx++) {
            imagesetpixel($out, $fx, 0, imagecolorallocate($out, 255, 255, 255));
        }
        for ($fy = 0; $fy < 30; $fy++) {
            imagesetpixel($out, 59, $fy, imagecolorallocate($out, 255, 255, 255));
        }
        for ($fx = 0; $fx < 60; $fx++) {
            imagesetpixel($out, $fx, 29, imagecolorallocate($out, 255, 255, 255));
        }
        for ($fy = 0; $fy < 30; $fy++) {
            imagesetpixel($out, 0, $fy, imagecolorallocate($out, 255, 255, 255));
        }
         * 
         */



// write $out to disc
        if($this->debug){
            imagejpeg($out, LOG_PATH.'/out.jpeg', 150) or die('Problem saving output image');
        }
        //imagedestroy($out);
        $src = $out; //imagecreatefromjpeg($outputFilePath) or die('Problem with source');
        $out = ImageCreateTrueColor(imagesx($src), imagesy($src)) or die('Problem In Creating image');
// scan image pixels
        $ino = 0;
        $started = 0;$allBinaryCodes = array();
        for ($x = 0; $x < imagesx($src); $x++) {
            for ($y = 0; $y < imagesy($src); $y++) {
                $src_pix = imagecolorat($src, $x, $y);
                $src_pix_array = $this->rgb_to_array($src_pix);
                //p($src_pix_array);

                if ($src_pix_array[0] < 100 && $src_pix_array[1] < 100 && $src_pix_array[2] < 100 && $started == 0) {
                    $csx = $x;
                    $csy = 0;
                    $started = 1;
                    $white = 0;
                }
                if ($src_pix_array[0] < 100 && $src_pix_array[1] < 100 && $src_pix_array[2] < 100) {
                    $white = 0;
                }
                if ($started == 1 && $y == 19 && $white == 1 && ($x-$csx)>3) {
                    $cey = 30;
                    $cex = $x;
                    $cc->loadImage($src, true);
                    $cc->cropToDimensions($csx, $csy, $cex, $cey);
                    $allBinaryCodes[] = $this->getHashCode($cc->getImgObj(), true);
                    if($this->debug){
                        $cc->saveImage(LOG_PATH."/ltr_{$ino}.jpg");
                    }
                    $cc->clear();
                    $ino++;
                    $started = 0;
                    //echo $csx . "," . $csy . "," . $cex . "," . $cey ."<br />";
                }
            }
            $white = 1;
        }
        $srr = array();

        foreach ($allBinaryCodes as $bcode) {
            for ($i = 0; $i < count($letterlist); $i++) {
                if (strcmp($letterlist[$i]['hash'], $bcode) == 0) {
                    $srr[] = $letterlist[$i]['letter'];
                    break;
                }
            }
        }
        $first = $second = '';
        $result = -1;
        $operator = '#';
        $isFirst = true;
        foreach ($srr as $nu) {
            if ($nu == '=') {
                break;
            }
            if ($nu == '+' || $nu == '-') {
                $isFirst = false;
                $operator = $nu; 
                continue;
            }

            if ($isFirst) {
                $first .= $nu;
            } else {
                $second .= $nu;
            }
        }
        if($operator=='+'){
            $result = ($first + $second);
        }elseif($operator=='-'){
            $result = ($first - $second);
        }
        unset($out);
        unset($src);
        unset($cc);
        if($this->debug){
            print_r($srr);
            echo "{$first}{$operator}{$second} = $result";
        }
        //$result = -1;
        return $result;
    }

// split rgb to components
    function rgb_to_array($rgb) {
        $a[0] = ($rgb >> 16) & 0xFF;
        $a[1] = ($rgb >> 8) & 0xFF;
        $a[2] = $rgb & 0xFF;

        return $a;
    }

    function hashToText($hash) {
        for ($i = 0; $i < count($letterlist); $i++)
            if ($letterlist[$i]['hash'] === $hash)
                return $letterlist[$i]['letter'];
        return 'X';
    }

}

?>