<?php
/**
 * Pinba timer implementation
 */
class SFM_Monitor_Zend implements SFM_Monitor_Interface
{

    /**
     * @param array $tags
     * @return SFM_Monitor_TimerInterface|SFM_Monitor_Pinba_Zend
     */
    public function createTimer($tags) {
        $timer = new SFM_Monitor_Zend_Timer($tags);
        return $timer;
    }

}