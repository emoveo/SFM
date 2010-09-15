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
        if (!SFM_Util_File::isImage($filename)) return false;

        $type = SFM_Util_File::getTypeExt($filename);
        $createfun = 'imagecreatefrom'.$type;
        $outputfun = 'image'.$type;
        list($widthOrig, $heightOrig) = getimagesize($filename);

        // Get new dimensions
        $scaleRatio = floatval($newSideSize)/(($widthOrig > $heightOrig)?$widthOrig:$heightOrig);
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
}
