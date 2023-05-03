<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HasRoutePermissions
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return JsonResponse
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        if ($this->hasPermission()) return $next($request);
        return response()->json(['message' => 'You are not allowed to access this resource.'], 403);
    }

    private function hasPermission(): bool
    {
        if (!isset(request()->user()->{'role'})) return false;

        return request()->user()->{'role'}->permissions->contains(function (Permission $permission) {
            return $permission->{'endpoint'} == request()->route()->uri && $permission->{'method'} == request()->method();
        });
    }
}
