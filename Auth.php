<?php
class SFM_Auth implements SFM_Interface_Singleton
{
    /**
     * General Failure
     */
    const FAILURE                        =  0;

    /**
     * Failure due to identity not being found.
     */
    const FAILURE_IDENTITY_NOT_FOUND     = -1;

    /**
     * Failure due to identity being ambiguous.
     */
    const FAILURE_IDENTITY_AMBIGUOUS     = -2;

    /**
     * Failure due to invalid credential being supplied.
     */
    const FAILURE_CREDENTIAL_INVALID     = -3;

    /**
     * Failure due to uncategorized reasons.
     */
    const FAILURE_UNCATEGORIZED          = -4;

    /**
     * Authentication success.
     */
    const SUCCESS                        =  1;

    /**
     * Authentication result code
     *
     * @var int
     */
    protected $_code;

    /**
     * The identity used in the authentication attempt
     *
     * @var mixed
     */
    protected $_identity;

    /**
     * An array of string reasons why the authentication attempt was unsuccessful
     *
     * If authentication was successful, this should be an empty array.
     *
     * @var array
     */
    protected $_messages;
    
    
    /**
     * Singleton instance
     *
     * @var SFM_Auth
     */
    protected static $_instance = null;

    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    protected function __construct()
    {}

    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    protected function __clone()
    {}

    /**
     * Returns an instance of SFM_Auth
     *
     * Singleton pattern implementation
     *
     * @return SFM_Auth Provides a fluent interface
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Authenticates against the supplied adapter
     *
     * @param  string $adapter
     * @param  string $pass
     * @return int authentication code 
     */
    public function authenticate($email, $pass)
    {
        //Select User by Email
        $user = Mapper_User::getInstance()->getUserByMailAndPasswd($email, $pass);
        if($user === null) {
            $this->_code = self::FAILURE_IDENTITY_NOT_FOUND;
            return;
        }

        //Check password (Here may be different logic)
        if($user->getProto("pass") === sha1($pass)){
          $this->_code = self::SUCCESS;
          $_SESSION["logged_user"] = $user;
        } else {
          $this->_code = self::FAILURE_CREDENTIAL_INVALID;
        }  
        
        //Return result code
        return $this->_code;
    }

    /**
     * Returns true if and only if an identity is available from session
     *
     * @return boolean
     */
    public function hasIdentity()
    {
        if (isset($_SESSION["logged_user"]) && $_SESSION["logged_user"] != '') 
          return true;
        else return false;
    }

    /**
     * Returns the identity from session or null if no identity is available
     *
     * @return Entity_User
     */
    public function getIdentity()
    {
        return (isset($_SESSION["logged_user"]) && $_SESSION["logged_user"] != '') ? $_SESSION["logged_user"] : null; 
    }

    /**
     * Clears the identity from session
     *
     * @return void
     */
    public function clearIdentity()
    {
        unset($_SESSION["logged_user"]);
    }
    
    /**
     * getCode() - Get the result code for this authentication attempt
     *
     * @return int authentication code
     */
    public function getCode()
    {
        return $this->_code;
    }

    public static function check()
    {
        if( !self::$_instance->hasIdentity() ) {
            throw new Zend_Amf_Server_Exception("Access deny");
        }
    }    
}
