<?php

namespace ZerosDev\NikReader\Laravel;

use ZerosDev\NikReader\Reader;
use Illuminate\Support\Facades\Facade as LaravelFacade;

class Facade extends LaravelFacade
{
    protected static function getFacadeAccessor()
    {
        return Reader::class;
    }
}
