<?php
/** 
 * Performance timer
 * 
 * @author Alex Litvinenko
 * @package Generic
 */
class SFM_Performance_Timer
{
    protected $totalTime = 0;
    
    protected $startTime = 0;
    
    protected $count = 0;
    
    public function start()
    {
        if ($this->count === 0) {
            $this->startTime = microtime(true); 
        }
        $this->count++;
    }

    public function stop()
    {
        $this->count--;
        if ($this->count === 0) {
            $this->totalTime += microtime(true) - $this->startTime; 
        }
    }
    
    public function get()
    {
        return $this->totalTime; 
    }
}
