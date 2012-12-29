<?php
/**
 * Class for routing requests
 *
 * @author Alex Litvinenko
 * @package Generic
 */
class SFM_Controller_Router extends SFM_Controller_Router_URLParser implements SFM_Interface_Singleton
{
    /**
     * Instance
     *
     * @var SFM_Router
     */
    private static $instance = null;
    
    /**
     * Information about controller and method
     *
     * @var array
     */
    private $controller = null;
    
    /**
     * Additional params, that were got from GET
     *
     * @var array
     */
    private $params = array();
    
    /**
     * Templates for request
     *
     * @var array
     */
    private $templates = array();
    
    /**
     * Layout for request
     *
     * @var string
     */
    private $layout;
    
    /**
     * Type of content to be returned
     *
     * @var string
     */
    private $contentType;
    
    protected function __construct()
    {
        //Set html as a default content-type
        $this->contentType = Config_Application::CONTENT_TYPE_HTML;
        $this->layout = Config_Application::SMARTY_TEMPLATE_LAYOUT_DEFAULT;
        parent::__construct(Config_Application::getInstance()->getCurrentUrl());
    }
    
    /**
     * Singleton pattern
     *
     * @return SFM_Controller_Router
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Parses current url
     * @throws SFM_Exception_PageNotFound
     *
     */
    public function parseCurrentUrl()
    {
        $siteMap = Config_Application::getInstance()->getSiteMap();
        $parts = explode('/', trim($this->path, '/'));
        
        $urlKey = $parts[0];
        
        if (!isset($siteMap[$urlKey])) {
            throw new SFM_Exception_PageNotFound($urlKey);
        }
        
        $route = $siteMap[$urlKey];
        
        $controller = $route['Controller'];
        $action = isset($parts[1]) ? $parts[1] : Config_Application::MVC_DEFAULT_ACTION;
        $params = isset($route['Params']) ? explode('/', $route['Params']) : array();
        
        if (isset($route['Layout'])) {
            $this->layout = $route['Layout'];
        }

        $this->parseTemplates($route);
        
        if (isset($route['ContentType'])) {
            $this->contentType = $route['ContentType'];
        }
        
        if (isset($route['Action'])) {
            $action = $route['Action'];
        }
        
        //Fill $params with data from url
        $urlParams = array();
        foreach ($params as $key => $value) {
            $urlParams[$value] = $parts[$key + 2];
        }
        
        if (isset($route['Actions'])) {
            $predefinedActions = (array) $route['Actions'];
            if (isset($predefinedActions[$action])) {
                if (isset($predefinedActions[$action]['Layout'])) {
                    $this->layout = $predefinedActions[$action]['Layout'];
                }
                
                $this->parseTemplates($predefinedActions[$action]);

                if (isset($predefinedActions[$action]['Params'])) {
                    $params = explode('/', $predefinedActions[$action]['Params']);
                    //FIXME refactor this crap!
                    foreach ($params as $key => $value) {
                       $urlParams[$value] = $parts[$key + 2];
                    }
                }
                
                if (isset($predefinedActions[$action]['ContentType'])) {
                    $this->contentType = $predefinedActions[$action]['ContentType'];
                }
                
                if (isset($predefinedActions[$action]['AddParams'])) {
                    $urlParams = array_merge($urlParams, $predefinedActions[$action]['AddParams']);
                }
            }
        }
        
        
        $this->controller[]= 'Controller_'.$controller;
        $this->controller[]= '_'.$action.'Action';
        $this->params = $urlParams;
        
    }
    
    /**
     * Returns name of controller and name of method to run
     *
     * @return array
     */
    public function getScript()
    {
        if ($this->controller === null) {
            $this->parseCurrentUrl();
        }
        
        return $this->controller;
    }
    
    /**
     * Returns template for this request
     *
     * @return string
     */
    public function getTemplates()
    {
        return $this->templates;
    }
    
    /**
     * Returns params from URL
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
    
    public function getContentType()
    {
        return $this->contentType;
    }
    
    public function getLayout()
    {
        return $this->layout;
    }
    
    private function parseTemplates($arr)
    {
        if (array_key_exists('Template', $arr)) {
            $this->templates[SFM_Template::DEFAULT_TPL_NAME] = $arr['Template'];
        }
        
        if (array_key_exists('Templates', $arr) && is_array($arr['Templates'])) {
            $tpls = $arr['Templates'];
            foreach ($tpls as $key => $value) {
                $this->templates[$key]= $value;
            }
        } 
    }

}