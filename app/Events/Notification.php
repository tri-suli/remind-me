<?php

namespace App\Events;

interface Notification
{
    /**
     * Get notification message
     *
     * @return string
     */
    public function message(): string;
}
