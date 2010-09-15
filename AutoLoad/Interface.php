<?php
/**
 * Implement this interface to register own autoload rule 
 * 
 * @author Andry Ivannikov
 * @package SFM.Autoload
 */
interface SFM_Autoload_Interface 
{
	/**
	* @param string $className 
	* @return string
    */
	public function loadClass( $className );
}
