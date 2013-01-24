<?php
/**
 * Performance monitor
 */
class SFM_Monitor {

    /**
     * @var $monitor SFM_Monitor_Interface
     */
    private static $monitor;
    
    private static $monitorType;

    /**
     * get concrete SFM_Monitor_Interface implementation
     * @static
     * @return SFM_Monitor_Interface
     */
    public static function get() {

        if (is_null(self::$monitor)) {
            self::$monitor = self::create();
        }

        return self::$monitor;
    }

    /**
    * @param string $monitorType
    * */    
    public static function setMonitorType($monitorType)
    {
        self::$monitorType = $monitorType;
    }

    /**
     * create concrete SFM_Monitor_Interface implementation
     * @static
     * @return SFM_Monitor_Interface
     */
    private static function create() {
        if(self::$monitorType === null){
            self::$monitorType = SFM_Monitor_Factory::TYPE_PINBA;
        }
            
        $monitor = SFM_Monitor_Factory::create(self::$monitorType);
        return $monitor;
    }

}