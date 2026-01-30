<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class redirectAcessCentral
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $utmSource = 'central-ln-' . ltrim($request->path(), '/');

        $query = $request->query();
        $query['utm_source'] = $utmSource;

        $request->merge(['query' => $query]);

        if ($this->shouldRedirect($request->path())) {
            return Redirect::away("https://lnsistemas.com.br?" . http_build_query($query), 301);
        }

        return $next($request);
    }

    private function shouldRedirect($path)
    {
        $redirectPaths = [
            '/',
            '/login',
            '/register',
            '/registro',
            '/reset-password',
            '/recuperar-senha',
            '/home',
            '/404',
        ];

        return in_array('/' . ltrim($path, '/'), $redirectPaths);
    }
}
