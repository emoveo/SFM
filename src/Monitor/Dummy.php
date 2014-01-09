<?php
/**
 * Dummy timer implementation
 */
class SFM_Monitor_Dummy implements SFM_Monitor_Interface
{

    /**
     * @param array $tags
     * @return SFM_Monitor_TimerInterface|SFM_Monitor_Dummy_Timer
     */
    public function createTimer($tags) {
        $timer = new SFM_Monitor_Dummy_Timer($tags);
        return $timer;
    }

}