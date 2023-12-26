<?php

namespace App\Exceptions;

use Exception;

class CredentialMismatchException extends Exception
{
    /**
     * Create a new exception instance
     */
    public function __construct()
    {
        parent::__construct(__('passwords.user'));
    }
}
