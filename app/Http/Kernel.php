<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $routeMiddleware = [
    // ... other middleware
        'custom.auth' => \App\Http\Middleware\AuthMiddleware::class,
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ];


}

