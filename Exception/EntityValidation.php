<?php
class SFM_Exception_EntityValidation extends Exception
{
    protected $errors;

    public function __construct($message, $errors = array())
    {
        $this->errors = $errors;
        parent::__construct($message . ". Details: " . var_export($errors, true));
    }
}