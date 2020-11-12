<?php

namespace LaraCaptcha\Facades;

use Illuminate\Support\Facades\Facade;

class LaraCaptcha extends Facade
{
    /**
     * Get the name of the class registered in the Application container.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'laracaptcha';
    }
}
