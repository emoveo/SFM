<?php
namespace SFM\Silex;

use SFM\Manager;

trait SfmAppTrait
{
    /**
     * @return Manager
     */
    public function getSFM()
    {
        return $this['sfm'];
    }
} 