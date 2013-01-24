<?php
/**
 * Pinba timer implementation
 */
class SFM_Monitor_Pinba implements SFM_Monitor_Interface
{

    /**
     * @param array $tags
     * @return SFM_Monitor_TimerInterface|SFM_Monitor_Pinba_Timer
     */
    public function createTimer($tags) {
        $timer = new SFM_Monitor_Pinba_Timer($tags);
        return $timer;
    }

}