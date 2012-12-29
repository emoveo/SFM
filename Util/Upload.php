<?php
/**
 * Uploader
 *
 * @author Greg Ryzhov
 * @package Generic
 */
class SFM_Util_Upload
{
    const SUCCESS      = 1;  // File is uploaded and moved to destination dir
    const ERROR_UPLOAD = 2;  // File not uploaded to server temp dir
    const ERROR_MOVE   = 3;  // File not moved to destination dir

    static public function upload($file, $destination)
    {
      if($file['tmp_name'])
        {//File is uploaded to temp dir
          return move_uploaded_file($file['tmp_name'], $destination)? self::SUCCESS : self::ERROR_MOVE;
        }
      return self::ERROR_UPLOAD;
    }

    static public function isImage($file, $mime_type=false)
    {
        $filename = trim($file["name"], ". \r\n\t");
        $arr = explode(".", $filename);
        $ext = strtoupper($arr[count($arr)-1]);
        if(strlen($ext)>0)
        {
            if(in_array($ext, explode(",", "JPG,BMP,JPEG,JPE,GIF,PNG")))
                if(strpos($file["type"], "image/")!==false || $mime_type===false) return true;
        }
        return false;
    }

    static public function isVideo($file, $mime_type=false)
    {
        $filename = trim($file["name"], ". \r\n\t");
        $arr = explode(".", $filename);
        $ext = strtoupper($arr[count($arr)-1]);
        if(strlen($ext)>0)
        {
            if(in_array($ext, explode(",", "FLV,MPG,MP4,MOV,AVI,WMV")))
                if(strpos($file["type"], "video/")!==false || $mime_type===false) return true;
        }
        
        return false;
    }

    static public function getType($file)
    {
        $mime = mime_content_type($file["tmp_name"]);
        $arr = explode("/", $mime);
        return $arr[0];
    }

    static public function getExt($file)
    {
        $arr = explode(".", trim($file["name"], ". \r\n\t"));
        return strtolower($arr[count($arr)-1]);
    }
}
