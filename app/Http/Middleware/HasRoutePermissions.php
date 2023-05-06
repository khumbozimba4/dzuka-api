<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class HasRoutePermissions
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->hasPermission()) return $next($request);
        return \response([], ResponseAlias::HTTP_FORBIDDEN);
    }

    private function hasPermission(): bool
    {
        if (!isset(request()->user()->{'role'})) return false;

        return request()->user()->{'role'}->permissions->contains(function (Permission $permission) {
            return $permission->{'endpoint'} == request()->route()->uri && $permission->{'method'} == request()->method();
        });
    }
}
