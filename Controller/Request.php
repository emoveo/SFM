<?php
/**
 * Class works with Request params
 *
 * @author Alex Litvinenko
 * @package Generic
 */
class SFM_Controller_Request
{
    /**
     * Returns either it is post request
     *
     * @return bool
     */
    static public function isPost()
    {
        return strtoupper($_SERVER['REQUEST_METHOD']) == 'POST';
    }
    
    /**
    * Get any GET variable
    * @param string $var            Variable name
    * @param mixed $default         Default value
    * @return mixed                 Either value of desired variable or default value
    */
    static public function getGet($var = "", $default = "")
    {
        if (empty($var)) {
            return $_GET;
        }
        if (@key_exists($var, $_GET)) {
            return $_GET[$var];
        } else {
            return $default;
        }       
    }
    
    /**
    * Get any POST variable
    * @param string $var            Variable name
    * @param mixed $default         Default value
    * @return mixed                 Either value of desired variable or default value
    */
    static public function getPost($var = "", $default = "")
    {
        if (empty($var)) {
            return $_POST;
        }
        
        if (@key_exists($var, $_POST)) {
            return $_POST[$var];
        } else {
            return $default;
        }       
    }
    
    /**
     * Checks Cross-site request forgery
     *
     * @see http://en.wikipedia.org/wiki/Cross-site_request_forgery
     * @throws Exception_Csrf
     */
    static public function checkCsrf()
    {
        assert("Request::isPost()");
    }
    
/**
    * Get any COOKIE variable
    * @param string $var            Variable name
    * @param mixed $default         Default value
    * @return mixed                 Either value of desired variable or default value
    */
    static public function getCookie($var = "", $default = "")
    {
        if (empty($var)) {
            return $_COOKIE;
        }
        if (@key_exists($var, $_COOKIE)) {
            return $_COOKIE[$var];
        } else {
            return $default;
        }
    }
    
    /**
    * Get any SESSION variable (PHP Sessions)
    * @param string $var            Variable name
    * @param mixed $default         Default value
    * @return mixed                 Either value of desired variable or default value
    */
    static public function getSession($var = "", $default = "")
    {
        if (empty($var)) {
            return $_SESSION;
        }
        if (@key_exists($var, $_SESSION)) {
            return $_SESSION[$var];
        } else {
            return $default;
        }
    }

    /**
    * Get any GET/POST/COOKIE/SESSION variable (in this order of precedence)
    * @param string $var            Variable name
    * @param mixed $default         Default value
    * @return mixed                 Either value of desired variable or default value
    */
    static public function getAny($var, $default = "")
    {
        switch (true) {
            case key_exists($var, $_GET):
                return $_GET[$var];
                break;
            case key_exists($var, $_POST):
                return $_POST[$var];
                break;
            case key_exists($var, $_COOKIE):
                return $_COOKIE[$var];
                break;
            case key_exists($var, $_SESSION):
                return $_SESSION[$var];
                break;
            default:
                return null;
        }
    }

}