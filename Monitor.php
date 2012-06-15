<?php
/**
 * Performance monitor
 */
class SFM_Monitor {

    /**
     * @var $monitor SFM_Monitor_Interface
     */
    private static $monitor;

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
     * create concrete SFM_Monitor_Interface implementation
     * @static
     * @return SFM_Monitor_Interface
     */
    private static function create() {
        $monitor = extension_loaded('pinba') ? new SFM_Monitor_Pinba() : new SFM_Monitor_Dummy();
        return $monitor;
    }

}