<?php
/**
 * Class managing configuration options.
 * 
 * @author Alex Litvinenko 
 * @package Generic
 */
class SFM_Config implements SFM_Interface_Singleton, ArrayAccess
{
    const ENVIROMENT_NAME = 'environment';
    const ENVIROMENT_DEVELOPMENT = 'development';
    const ENVIROMENT_PRODUCTION = 'production';
    const ENVIROMENT_TEST = 'test';
    
    const CONTENT_TYPE_HTML = "html";
    const CONTENT_TYPE_XML = "xml";
    const CONTENT_TYPE_JSON = "json";
    const CONTENT_TYPE_NONE = "none";
    /**
     * @var SFM_Config
     */
    protected static $instance = null;
    
    /**
     * @var Zend_Config
     */
    protected $data;
    /**
     * @var string
     */
    protected $enviroment;
    
    /**
     * Config constructor
     */
    protected function __construct(Zend_Config $config, $enviroment=null )
    {
        // Assign self::$instance NOW
        // We need it because in config.php may be used references to Config instance.  
        self::$instance = $this;
        
        //set current enviroment
        $this->enviroment = $enviroment;
        
        // Put $_SERVER into config area
        $this->data = new Zend_Config($_SERVER, true);
        
        if( !is_null($enviroment) ) {
            $this->data->merge( $config->{$enviroment} );
        } else {
            $this->data->merge( $config );
        }        
        
//        print_r($this->data->toArray());
    }

    /**
     * @param Zend_Config $config
     * @param string $enviroment
     * @return SFM_Config
     */
    public static function createInstance(Zend_Config $config, $enviroment=null)
    {
        if (self::$instance !== null) {
            throw new SFM_Exception_Config('error_config_already_created');
        }
        return self::$instance = new self($config, $enviroment); 
    }
    
    /**
     *
     * @return SFM_Config
     */
    public static function getInstance()
    {
        return self::$instance; 
    }
    
    /**
     * Add Config, it's merge with previous configs
     *
     * @param Zend_Config $config
     */
    public function addConfig( Zend_Config $config ) {
        $this->data->merge( $config );
    }
    
    public function getEnviroment()
    {
        return $this->enviroment;
    }
    
    /**
     * First return explicit property, then from data 
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        
        if ( property_exists( get_class($this), $name) ) {
            return $this->{$name};
        } else {
            return $this->data->get($name);
        }
    }
    
    /**
     * The following methods are used to allow access to Config
     * in array-style (e.g. used in Smarty templates).
     */
    
    public function offsetExists($key)
    {
        return property_exists($this, $key);
    }
    

    public function offsetGet($key)
    {
        return $this->$key;
    }


    public function offsetSet($key, $value)
    {
        return $this->$key = $value;
    }


    public function offsetUnset($key)
    {
        unset($this->$key);
    }    
    
    protected function createDirIfNotExists( $dir ) {
        if( !file_exists( $dir ) ) {
            mkdir( $dir, 0770, true );
        }
    }
    
} 