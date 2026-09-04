<?php

namespace App\Exceptions;

use Exception;

final class OfferUnavailableException extends Exception
{
    public function __construct()
    {
        parent::__construct('The offer is no longer available.');
    }
}
