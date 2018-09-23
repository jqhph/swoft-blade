<?php

namespace Swoft\Blade\Bootstrap\Listeners;

use Swoft\Bean\Annotation\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;
use Swoft\Event\AppEvent;

/**
 * Class ReleaseResource
 * @package Swoft\Blade\Bootstrap\Listeners
 * @Listener(AppEvent::RESOURCE_RELEASE)
 */
class ReleaseResource implements EventHandlerInterface
{
    /**
     * @param \Swoft\Event\EventInterface $event
     */
    public function handle(EventInterface $event)
    {
        blade_factory()->release();
    }

}
