<?php
/** 
 * Performance monitor
 * 
 * @author Alex Litvinenko
 * @package Generic
 */
class SFM_Performance_Monitor
{
    static private $monitors = array();

    static private $counters = array();
    
    static public function register($name)
    {
        self::$monitors[$name] = new SFM_Performance_Timer();
    }
    
    static public function start($name)
    {
        self::$monitors[$name]->start();
    }

    static public function stop($name)
    {
        self::$monitors[$name]->stop();
    }
    
    static public function get($name)
    {
        return self::$monitors[$name]->get(); 
    }

    static public function registerCounter($name)
    {
        self::$counters[$name] = 0;
    }
    
    static public function increaseCounter($name)
    {
         self::$counters[$name] += 1; 
    }
    
    static public function getCounter($name)
    {
        return self::$counters[$name]; 
    }
}
