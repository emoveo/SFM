<?php
/**
 * Dummy timer implementation
 */
class SFM_Monitor_Dummy_Timer implements SFM_Monitor_TimerInterface
{

    /**
     * @param array $tags
     */
    public function __construct($tags) {}

    /**
     * @param array $tags
     * @return bool
     */
    private function start($tags) {
        return true;
    }

    /**
     * @return bool
     */
    public function stop() {
        return true;
    }

    /**
     * @return bool
     */
    public function remove() {
        return true;
    }

}