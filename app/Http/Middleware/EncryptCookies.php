<?php

namespace App\Http\Middleware;

use App\Services\SeenStories;
use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Which stories a guest was already counted for (nothing secret; kept small, see SeenStories)
        SeenStories::COOKIE,
    ];
}
