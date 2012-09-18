<?php
/**
 *  
 * Autoload management and __autoload function 
 * 
 * @author Andry Ivannikov
 * @package SFM
 */

require_once 'SFM/Interface/Singleton.php';
require_once 'SFM/AutoLoad/Rule/SFMFramework.php';
require_once 'SFM/AutoLoad/Rule/SFMProject.php';
require_once 'SFM/Exception/Autoload.php';; 


class SFM_AutoLoad implements SFM_Interface_Singleton
{
    /**
     * @var array
     */
    protected $rules = array();
    /**
     * @var SFM_AutoLoad
     */
    protected static $instance = false;
    
    /**
     *
     * @return SFM_Autoload
     */
    public static function getInstance() {
    	if( false === self::$instance ) {
    	    self::$instance = new SFM_AutoLoad();

    	    self::$instance->addRule(new SFM_AutoLoad_Rule_SFMFramework());
    	    self::$instance->addRule(new SFM_Autoload_Rule_SFMProject());
    	}
    	return self::$instance;
    }
    
    
    /**
     * Add rule. FIFO priority  
     *
     * @param SFM_Autoload_Interface $rule
     * @return void
     */
    public function addRule( SFM_Autoload_Interface $rule )
    {
        $this->rules[] = $rule;
    }
    
    public static function autoLoad( $class ) {
    	SFM_AutoLoad::getInstance()->loadClass( $class );
    	return $class;
    	
    }
    
    /**
    * @param string $className 
    * @return void
    * @throws SFM_Exception_Autoload
    */
    public function loadClass($className)
    {
        foreach ($this->rules as $rule) {
            $fileName = $rule->loadClass($className);
            if( $this->isReadable( $fileName) ) {
                require_once $fileName; 
                return;
            }  
        }
        
        // Create required class on the fly to prevent fatal error
        
        //commented out by A-25 - WTF? What for is that string?
        //eval("class {$className} extends stdClass {}");
        
        //throw new SFM_Exception_Autoload("File \"{$fileName}\" with class \"{$className}\" could not be found. Searched in dirs:<br />" . str_replace(PATH_SEPARATOR, "<br />", get_include_path()) . "");
         
    }
    
    /**
     * Register autoload function
     *
     */
    public function register() {
    	spl_autoload_register(array('SFM_AutoLoad', 'autoLoad'));
    }
    
    
    /**
     * @param string $filename
     * @return boolean
     */
    public function isReadable( $fileName ) 
    {
    	if (!$fh = @fopen($fileName, 'r', true)) {
            return false;
        }
        @fclose($fh);
        return true;
    }
    
}