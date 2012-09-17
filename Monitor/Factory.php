<?php
interface SFM_Monitor_Factory
{
    const TYPE_PINBA = 'Pinba';
    const TYPE_ZEND = 'Zend';
    const TYPE_DUMMY = 'Dummy';

    public static function create($type)
    {
        switch($type){
            case self::TYPE_PINBA:
                return extension_loaded('pinba') ? new SFM_Monitor_Pinba() : new SFM_Monitor_Dummy();
                break;
            case self::TYPE_ZEND:
                return new SFM_Monitor_Zend();
                break;
            case self::TYPE_DUMMY:
                return new SFM_Monitor_Dummy();
                break;
            default:
                throw new Exception('Undefined SFM_Monitor type '.$type);
        }
    }
}