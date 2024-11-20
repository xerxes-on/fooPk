<?php

namespace App\Listeners;

/**
 * Base class for event Listeners
 *
 * @package App\Listeners
 */
abstract class EventBase
{
    public function __construct(protected ?int $userId = null)
    {
        if (is_null($this->userId)) {
            $this->setUserId((int)\Auth::id());
        }
    }

    /**
     * Optional user setter
     *
     * @param int $userId
     */
    final public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }
}
