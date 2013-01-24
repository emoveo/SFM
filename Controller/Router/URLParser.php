<?php
/**
 * Extensed parse_url functionality
 * Implements {@link http://php.net/spl SPL Iterator interface}.
 *
 * @author Alex Litvinenko
 * @package Generic
 */
class SFM_Controller_Router_URLParser
{
    /**
     * full Url string (with protocol)
     *
     * @var string
     */
    public $url;

    /**
     * scheme specified in request
     *
     * @var string
     */
    public $scheme;

    /**
     * scheme with "://"
     *
     * @var string
     */
    public $protocol;

    /**
     * Name of server 
     *
     * @var string
     */
    public $serverName;

    /**
     * 2nd level domain DOT 1st level domain names.
     *
     * @var string
     */
    public $hostnameBase;

    /**
     * server port specified in request
     *
     * @var string
     */
    public $port;

    /**
     * path specified in request
     *
     * @var string
     */
    public $path;


    /**
     * Create new URL object parsing REQUEST_URI.
     */
    protected function __construct($requestString) 
    {
        $this->url = $requestString;
        $parsed = parse_url($requestString);
        $this->scheme = @$parsed['scheme'];
        $this->protocol = strtolower($this->scheme) . '://';
        $this->serverName = strtolower(@$parsed['host']);
        $this->port = ''; // @$parsed['port']; // arty: this is temporary to allow nginx+apache on one host 
        $this->path = @$parsed['path'];
        $this->hostnameBase = Config_Application::getInstance()->SERVER_NAME;
        
//        $baseDomains = Config::getInstance()->base_domains;
//        
//        // help debugging common cryptic error
//        $matches = null;
//        if (!preg_match('/(' . str_replace('.','\.', join($baseDomains, '|')) . ')/', $this->serverName, $matches)
//            && php_sapi_name() != "cli") {
//            throw new ApplicationException(array('error_no_such_domain_in_config', $this->serverName));
//        }
//        
//        $this->hostnameBase = $matches[1];
    }

    /**
     * Shortcut to parse url and get base hostname for it
     */
    static public function getHostnameBaseStatic($url)
    {
        $urlParser = new SFM_Controller_Router_URLParser($url);
        return $urlParser->hostnameBase;
    }

    /**
     *   
     */
    public function getUrl()
    {
        return $this->url;
    }
}
