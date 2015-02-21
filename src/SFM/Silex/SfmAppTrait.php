<?php
namespace SFM\Silex;

trait SfmAppTrait
{
    /**
     * @return \SFM_Manager
     */
    public function getSFM()
    {
        return $this['sfm'];
    }
} 