<?php
abstract class SFM_Exception_Abstract extends Exception implements SFM_Exception_Interface
{
    protected $context = array();

    /**
     * @param string $message
     * @param array $context
     * @param array $context
     * @param Exception $previousException
     */
    public function __construct($message = '', $context = array(), $previousException = null)
    {
        $this->context = $context;
        parent::__construct($message, 0, $previousException);
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }
}