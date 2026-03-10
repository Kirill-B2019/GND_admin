<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * @param  string  ...$roles  Разрешённые роли (например 'admin').
     */
    public function __construct(
        protected array $roles = []
    ) {}

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $allowed = ! empty($roles) ? $roles : $this->roles;
        if (! $request->user() || ! in_array($request->user()->role, $allowed, true)) {
            abort(403, 'Доступ запрещён.');
        }

        return $next($request);
    }
}
