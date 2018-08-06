<?php

namespace Swoft\Support\Contracts;

interface MessageProvider
{
    /**
     * Get the messages for the instance.
     *
     * @return MessageBag
     */
    public function getMessageBag();
}
