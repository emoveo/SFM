<?php
namespace SFM\Event;

use Symfony\Component\EventDispatcher\Event;

class EntityUpdatedEvent extends Event
{
    const NAME = "EntityUpdated";

    protected $new;
    protected $old;

    /**
     * @param \SFM_Entity $new
     * @param \SFM_Entity $old
     */
    public function __construct(\SFM_Entity $new, \SFM_Entity $old)
    {
        $this->new = $new;
        $this->old = $old;
    }

    /**
     * @return \SFM_Entity
     */
    public function getOldEntity()
    {
        return $this->old;
    }

    /**
     * @return \SFM_Entity
     */
    public function getNewEntity()
    {
        return $this->new;
    }
}