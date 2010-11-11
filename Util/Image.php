<?php

/**
 * Image functions
 *
 * @author Greg Ryzhov
 * @package Generic
 */
class SFM_Util_Image
{

    static public function scaleToSideSize($filename, $newSideSize)
    {
        if (!SFM_Util_File::isImage($filename))
            return false;


        $type = SFM_Util_File::getTypeExt($filename);
        $createfun = 'imagecreatefrom' . $type;
        $outputfun = 'image' . $type;
        list($widthOrig, $heightOrig) = getimagesize($filename);

        // Get new dimensions
        $scaleRatio = floatval($newSideSize) / (($widthOrig > $heightOrig) ? $widthOrig : $heightOrig);
        $widthNew = intval($widthOrig * $scaleRatio);
        $heightNew = intval($heightOrig * $scaleRatio);

        // Load
        $outImage = imagecreatetruecolor($widthNew, $heightNew);
        $sourceImage = $createfun($filename);

        // Resize
        imagecopyresized($outImage, $sourceImage, 0, 0, 0, 0, $widthNew, $heightNew, $widthOrig, $heightOrig);
        // Output
        $outputfun($outImage, $filename, 100);
        imagedestroy($outImage);
        imagedestroy($sourceImage);
        return true;
    }

    static public function scaleToWidth($filename, $newWidth)
    {
        if (!SFM_Util_File::isImage($filename))
            return false;

        $type = SFM_Util_File::getTypeExt($filename);
        $createfun = 'imagecreatefrom' . $type;
        $outputfun = 'image' . $type;
        list($widthOrig, $heightOrig) = getimagesize($filename);
        if ($widthOrig <= $newWidth) {
            return true;
        }
        // Get new dimensions
        $scaleRatio = floatval($newWidth) / $widthOrig;
        $newHeight = intval($heightOrig * $scaleRatio);

        // Load   
        $outImage = imagecreatetruecolor($newWidth, $newHeight);
        $sourceImage = $createfun($filename);
        // Resize
        imagecopyresized($outImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $widthOrig, $heightOrig);
        // Output
        $outputfun($outImage, $filename, 100);
        imagedestroy($outImage);
        imagedestroy($sourceImage);
        return true;
    }

    static public function imScaleToWidth($filename, $newWidth)
    {
        list($width, $height) = getimagesize($filename);
        if ($width <= $newWidth)
            return;

        $image = new Imagick($filename);

        // If 0 is provided as a width or height parameter,
        // aspect ratio is maintained
        $image->thumbnailImage($newWidth, 0);

        $image->writeImages($filename, true);
    }

    static public function imScaleToSideWithBorders($filename, $newSideSize)
    {
        list($width, $height) = getimagesize($filename);
        // if ($width <= $newWidth) return;

        $image = new Imagick($filename);

        $fitbyWidth = (($newSideSize / $width) < ($newSideSize / $height)) ? true : false;

        if ($fitbyWidth) {
            if ($width <= $newSideSize)
                return;
            $image->thumbnailImage($newSideSize, 0, false);
        } else {
            if ($height <= $newSideSize)
                return;
            $image->thumbnailImage(0, $newSideSize, false);
        }
        $newDimentions = $image->getImageGeometry();

        /* The overlay x and y coordinates */
        $x = ( $newSideSize - $newDimentions['width'] ) / 2;
        $y = ( $newSideSize - $newDimentions['height'] ) / 2;
        $image->borderImage('white', $x, $y);

        $image->writeImages($filename, true);
    }

}
