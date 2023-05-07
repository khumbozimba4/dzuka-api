<?php

namespace App\Http\Middleware;

use App\Models\Footprint;
use Closure;
use Illuminate\Http\Request;

class Footprints
{
    private $hidden = ['password', 'pin', 'new_pin'];

    public function handle(Request $request, Closure $next)
    {
        if (!defined('LARAVEL_START')) define('LARAVEL_START', microtime(true));
        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        Footprint::create([
            'user_id' => auth()->id() ?? null,
            'endpoint' => $request->route() ? $request->route()->uri : '',
            'uri' => $request->getRequestUri(),
            'method' => $request->method(),
            'request' => json_encode($request->except($this->hidden)),
            'response' => method_exists($response, 'content') ? $response->content() : null,
            'status' => method_exists($response, 'status') ? $response->status() : null,
            'success' => $response->isSuccessful(),
        ]);
    }
}
