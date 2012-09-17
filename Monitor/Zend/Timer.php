<?php
/**
 * Pinba timer implementation
 */
class SFM_Monitor_Zend_Timer implements SFM_Monitor_TimerInterface
{

    protected $_currentRunningProfiler = null;
    protected $_currentRunningProfilerId = null;
    protected $_profilers = array();

    /**
     * @param array $tags
     */
    public function __construct($tags)
    {
        $label = $this->createLabel($tags);
        if(!isset($this->_profilers)){
            $this->_profilers[$label] = new Zend_Db_Profiler_Firebug($label);
            $this->_profilers[$label]->setEnabled(true);
        }
        $this->start($this->_profilers[$label],$label);
    }
    
    protected function createLabel(array $tags)
    {
        $labelParts = array();
        foreach($tags as $key => $value){
            $labelParts[] = $key.':'.$value;
        }
        return implode(',',$labelParts);
    }

    /**
     * @param Zend_Db_Profiler $profiler
     * @return bool
     */
    private function start(Zend_Db_Profiler $profiler,$label)
    {
        $this->_currentRunningProfiler = $profiler;
        $this->_currentRunningProfilerId =  $profiler->queryStart($label);
        return true;
    }

    /**
     * @return bool
     */
    public function stop()
    {
        $this->_currentRunningProfiler->queryEnd($this->_currentRunningProfilerId);
        return true;
    }

    /**
     * @return bool
     */
    public function remove()
    {
        
    }

}