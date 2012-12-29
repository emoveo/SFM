<?php
/**
 * SFM autoload rule 
 * 
 * @author Andry Ivannikov
 * @package SFM.Autoload.Rule
 */
require_once 'SFM/AutoLoad/Interface.php'; 

class SFM_Autoload_Rule_SFMFramework implements SFM_AutoLoad_Interface
{
    /**
     * Example SFM_Controller_Front => SFM/Controller/Front.php
     * 
     * @param string $className
     * @return string 
     */
    public function loadClass( $className )
    {
        return (str_replace('_', '/', $className) . '.php');
    }
}