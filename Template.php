<?php

class SFM_Template implements SFM_Interface_Singleton  
{
	
	const DEFAULT_TPL_NAME = 'default_tpl_name';
    /**
     * Page layout template file 
     *
     * @var string
     */
	private $layout;
	
	/**
    * Array of template names
    * @var array
    */
    private $tpls = array();
    
    /**
     * Type of content that should be returned to client
     */
    private $contentType;
    
    /**
     * Array of variables that should be processed to template
     * @var object
     */
    private $vars;
	
    private static $instance;
    
    protected function __construct()
    {
        $Config = Config_Application::getInstance();
        $this->template_dir = $Config->getTemplateDir();
        $this->template_layout_dir = $Config->getSmartyTemplateLayoutDir();
        $this->caching = false;
        $this->contentType = SFM_Controller_Router::getInstance()->getContentType();
        $this->vars = new stdClass();
    }
    
    /**
     * @return SFM_Template
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new SFM_Template();
        }
        
        return self::$instance;
    }
    
	/**
    * Sets template name
    * @param string $tpl  FileName of template to be used in content area
    * @param string $name Name of template to identificate it in the future
    */
    public function setTpl($tpl, $name=null)
    {
    	$name = is_null($name) ? self::DEFAULT_TPL_NAME : $name;
        $this->tpls[$name] = $tpl;
    }

    /**
     * Get template name
     * @param string $name
     * @return string
     */
    public function getTpl($name=null)
    {
    	$name = is_null($name) ? self::DEFAULT_TPL_NAME : $name;
    	return $this->template_dir . '/' . $this->tpls[$name];
    }
    
    /**
     * Sets page layout template
     * @param string $layout            Name of template to be used for layout (w/ or w/o ".tpl" suffix)
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * Get layout template path
     */
    public function getLayout()
    {
    	return $this->template_layout_dir . '/' . $this->layout;
    }
    
	/**
    * Displays the page with use of template specified as argument or already set
    * 
    * @param string $tpl            Name of template to be used in content area (w/ or w/o ".tpl" suffix)
    * @param string $layout         Name of template to be used for layout (w/ or w/o ".tpl" suffix)
    */
    public function display()
    {
    	switch ($this->contentType) {
    		case SFM_Config::CONTENT_TYPE_JSON:
    			$result = $this->php2js($this->vars);
    			break;
    		default:
    			$contentGenerator = new SFM_ContentGenerator($this->vars);
				$result = $contentGenerator->process($this->getLayout());
    			break;
    	}
    	
		echo $result;
    }
    
    public function getContentType()
    {
        return $this->contentType;
    }
    
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }
    
    public function assign($key, $value)
    {
    	$this->vars->$key = $value;
    }
    
    public function assignByRef($key, &$value)
    {
    	$this->vars->$key = &$value;
    }
    
	private function php2js($a=false)
	{
		if (is_null($a)) return 'null';
		if ($a === false) return 'false';
		if ($a === true) return 'true';
		if (is_scalar($a)) {
			if (is_float($a)) {
				// Always use "." for floats.
				$a = str_replace(",", ".", strval($a));
			}
	
			static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
			array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
			return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
		}
		
		$isList = true;
		for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
			if (key($a) !== $i) {
				$isList = false;
				break;
			}
		}
		$result = array();
		if ($isList) {
			foreach ($a as $v) {
				$result[] = $this->php2js($v);
			}
			return '[ ' . join(', ', $result) . ' ]';
		} else {
			foreach ($a as $k => $v) $result[] = $this->php2js($k).': '.$this->php2js($v);
			return '{ ' . join(', ', $result) . ' }';
		}
	}

}