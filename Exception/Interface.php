<?php
interface SFM_Exception_Interface
{
    /**
     * @param string $message
     * @param array $context
     */
    public function __construct($message = '', $context = array());

    /**
     * @return array
     */
    public function getContext();
}