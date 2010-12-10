<?php
/**
 * Projects based on SFM autoload rule 
 * 
 * @author Andry Ivannikov
 * @package SFM.Autoload.Rule
 */
class SFM_Autoload_Rule_SFMProjectExtra implements SFM_AutoLoad_Interface
{
    /**
     * Example Entity_User => Entity/User.php
     * 
     * @param string $className
     * @return string 
     */
	public function loadClass( $className )
	{
        return (str_replace('_', '/', $className) . '.php');
	}
}