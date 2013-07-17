<?php
/**
 * File functions
 *
 * @author Greg Ryzhov
 * @package Generic
 */
class SFM_Util_File
{
    static public function isImage($filename, $mime_type=false)
    {
        $filename = trim($filename, ". \r\n\t");
        $arr = explode(".", $filename);
        $ext = strtoupper($arr[count($arr)-1]);
        if(strlen($ext)>0)
        {
            if(in_array($ext, explode(",", "JPG,BMP,JPEG,JPE,GIF,PNG")))
                if(strpos(self::getType($filename), "image/")!==false || $mime_type===false) return true;
        }
        return false;
    }

    static public function isVideo($filename, $mime_type=false)
    {
        $arr = explode(".", $filename);
        $ext = strtoupper($arr[count($arr)-1]);
        if(strlen($ext)>0)
        {
            if(in_array($ext, explode(",", "FLV,MPG,MP4,MOV,AVI,WMV")))
                if(strpos($file["type"], "video/")!==false || $mime_type===false) return true;
        }

        return false;
    }

    static public function getType($filename)
    {
        $arr = explode("/", mime_content_type($filename));
        return $arr[0];
    }

    static public function getTypeExt($filename)
    {
        $arr = explode("/", mime_content_type($filename));
        return $arr[1];
    }

    static public function getExt($filename)
    {
        $path_parts = pathinfo($filename);
        return isset($path_parts['extension']) ? $path_parts['extension'] : false;
    }

    static public function getFileWithExtension($filename, $newExtension)
    {
        $ext = static::getExt($filename);
        if($ext){
            $newFileName = str_replace('.'.$ext,'.'.$newExtension,$filename);
        } else {
            $newFileName = $filename.'.'.$newExtension;
        }
        return $newFileName;
    }
}
