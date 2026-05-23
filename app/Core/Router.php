<?php

declare(strict_types=1);

namespace App\Core;

use App\Security\Auth;
use App\Security\Csrf;

final class Router
{
    /** @var array<int, array<string, mixed>> */
    private array $routes = [];

    /** @var array<string, string> */
    private static array $namedRoutes = [];

    private static ?string $currentName = null;

    /** @param callable|array{0: class-string, 1: string} $handler */
    public function get(string $name, string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('GET', $name, $path, $handler, $middleware);
    }

    /** @param callable|array{0: class-string, 1: string} $handler */
    public function post(string $name, string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('POST', $name, $path, $handler, $middleware);
    }

    /** @param callable|array{0: class-string, 1: string} $handler */
    public function delete(string $name, string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('DELETE', $name, $path, $handler, $middleware);
    }

    /** @param callable|array{0: class-string, 1: string} $handler */
    private function add(string $method, string $name, string $path, callable|array $handler, array $middleware): void
    {
        $this->routes[] = compact('method', 'name', 'path', 'handler', 'middleware');
        self::$namedRoutes[$name] = $path;
    }

    public function dispatch(Request $request): mixed
    {
        foreach ($this->routes as $route) {
            $params = $this->match($route, $request);

            if ($params === null) {
                continue;
            }

            self::$currentName = (string) $route['name'];
            $this->runMiddleware($route['middleware'], $request);

            return $this->call($route['handler'], $request, $params);
        }

        abort_response(404, 'Halaman tidak ditemukan.');
    }

    public static function url(string $name, array|string|int $params = []): string
    {
        if (! isset(self::$namedRoutes[$name])) {
            throw new \InvalidArgumentException("Route {$name} tidak terdaftar.");
        }

        $path = self::$namedRoutes[$name];
        $params = is_array($params) ? $params : [$params];

        foreach ($params as $key => $value) {
            if (is_int($key)) {
                $path = preg_replace('/\{[A-Za-z_][A-Za-z0-9_]*\}/', rawurlencode((string) $value), $path, 1);
                continue;
            }

            $path = str_replace('{' . $key . '}', rawurlencode((string) $value), $path);
        }

        return $path;
    }

    public static function currentName(): ?string
    {
        return self::$currentName;
    }

    private function match(array $route, Request $request): ?array
    {
        if ($route['method'] !== $request->method()) {
            return null;
        }

        $pattern = preg_replace('/\{([A-Za-z_][A-Za-z0-9_]*)\}/', '(?P<$1>[^/]+)', $route['path']);
        $pattern = '#^' . $pattern . '$#';

        if (! preg_match($pattern, $request->path(), $matches)) {
            return null;
        }

        return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    private function runMiddleware(array $middleware, Request $request): void
    {
        if (in_array('csrf', $middleware, true)) {
            Csrf::validate($request);
        }

        if (in_array('auth', $middleware, true) && ! Auth::check()) {
            flash('error', 'Silakan login Google terlebih dahulu.');
            redirect(route('home'));
        }

        if (in_array('guest', $middleware, true) && Auth::check()) {
            redirect(route('dashboard'));
        }
    }

    private function call(callable|array $handler, Request $request, array $params): mixed
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $handler = [new $class(), $method];
        }

        return $handler($request, ...array_values($params));
    }
}
