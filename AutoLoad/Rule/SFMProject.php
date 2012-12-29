<?php
/**
 * Projects based on SFM autoload rule 
 * 
 * @author Andry Ivannikov
 * @package SFM.Autoload.Rule
 */
class SFM_Autoload_Rule_SFMProject implements SFM_AutoLoad_Interface
{
    /**
     * Example Entity_User => Entity/User.Entity.php
     * 
     * @param string $className
     * @return string 
     */
    public function loadClass( $className )
    {
        $dirs = explode("_", $className);
        return (str_replace('_', '/', $className) . "." . $dirs[0] . '.php');
    }
}