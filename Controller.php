<?php
/**
 * Abstract class, All controllers should extend this class
 * 
 * @author Alex Litvinenko
 * @package Generic
 */
abstract class SFM_Controller
{
	protected $params = array();
	
	/**
	 * View object
	 *
	 * @var SFM_Template
	 */
	protected $view;
	
	public function __construct(array $params)
	{
		$this->params = $params;
		$this->view = SFM_Template::getInstance();
	}
	
	public function __call($method, $arguments)
    {
        $method = substr($method, 1);
        
        $this->init();
        
        if (method_exists($this, $method)) {
        	call_user_func_array(array($this, $method), $arguments);
        } else {
        	throw new SFM_Exception_Controller("Method $method not exist in  " . get_class($this) ); 
        }
    }
	
    /**
     * Get internal params
     * 
     * @param string $name
     * @return mixed
     */
    public function getParam( $name )
    {
        return $this->params[$name];
    }
    
    /**
     * Some initial params initialization
     *
     */
    protected function init()
    {
        
    }
    	
}

?>