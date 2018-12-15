<?php

namespace Codegrapple\Doctrine\MongoDB\Facades;

use Illuminate\Support\Facades\Facade;

class DocumentManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'DocumentManager';
    }
}