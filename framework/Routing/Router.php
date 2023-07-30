<?php

namespace Framework\Routing;

use Exception;
use Throwable;

/**
 * The Router class is responsible for routing HTTP requests to the appropriate handler function.
 * It allows for adding routes with specific HTTP methods and paths, as well as error handlers for different HTTP error codes.
 * The class provides methods for dispatching the current request, redirecting to a different path, and generating a path for a named route with optional parameters.
 *
 * Methods:
 * - add(): adds a new route with a specified HTTP method, path, and handler function
 * - errorHandler(): sets a callable function to handle a specific HTTP error code
 * - dispatch(): matches the current request to a route and dispatches it, or returns an error if no matching route is found
 * - paths(): returns an array of paths for all added routes
 * - current(): returns the current matched route, if any
 * - match(): matches a specified HTTP method and path to a route, or returns null if no match is found
 * - dispatchNotAllowed(): returns an error message for a 400 HTTP error code
 * - dispatchNotFound(): returns an error message for a 404 HTTP error code
 * - dispatchError(): returns an error message for a 500 HTTP error code
 * - redirect(): redirects to a specified path with a 301 HTTP status code
 * - route(): generates a path for a named route with optional parameters
 *
 * Fields:
 * - routes: an array of Route objects representing all added routes
 * - errorHandlers: an array of callable functions to handle different HTTP error codes
 * - current: the currently matched Route object, if any
 */
class Router
{
    protected array $routes = [];
    protected array $errorHandlers = [];
    protected Route $current;

    public function add(string $method, string $path, callable $handler): Route
    {
        $route = $this->routes[] = new Route($method, $path, $handler);
        return $route;
    }

    public function errorHandler(int $code, callable $handler)
    {
        $this->errorHandlers[$code] = $handler;
    }

    public function dispatch()
    {
        $paths = $this->paths();

        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestPath = $_SERVER['REQUEST_URI'] ?? '/';

        $matching = $this->match($requestMethod, $requestPath);

        if ($matching) {
            $this->current = $matching;

            try {
                return $matching->dispatch();
            }
            catch (Throwable $e) {
                return $this->dispatchError();
            }
        }
        
        if (in_array($requestPath, $paths)) {
            return $this->dispatchNotAllowed();
        }
        
        return $this->dispatchNotFound();
    }

    /**
     * Returns an array of paths by iterating over the routes and retrieving the path for each route.
     *
     * @return array The array of paths.
     */
    private function paths(): array
    {
        $paths = [];

        foreach ($this->routes as $route) {
            $paths[] = $route->path();
        }

        return $paths;
    }

    public function current(): ?Route
    {
        return $this->current;
    }

    private function match(string $method, string $path): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->matches($method, $path)) {
                return $route;
            }
        }

        return null;
    }

    public function dispatchNotAllowed()
    {
        $this->errorHandlers[400] ??= fn() => 'not allowed';
        return $this->errorHandlers[400]();
    }

    public function dispatchNotFound()
    {
        $this->errorHandlers[404] ??= fn() => 'not found';
        return $this->errorHandlers[404]();
    }

    public function dispatchError()
    {
        $this->errorHandlers[500] ??= fn() => 'server error';
        return $this->errorHandlers[500]();
    }

    public function redirect($path)
    {
        header("Location: {$path}", $replace = true, $code = 301);
        exit;
    }

    public function route(string $name, array $parameters = []): string
    {
        foreach ($this->routes as $route) {
            if ($route->name() === $name) {
                $finds = [];
                $replaces = [];

                foreach ($parameters as $key => $value) {
                    array_push($finds, "{{$key}}");
                    array_push($replaces, $value);
                    array_push($finds, "{{$key}?}");
                    array_push($replaces, $value);
                }

                $path = $route->path();
                $path = str_replace($finds, $replaces, $path);
                $path = preg_replace('#{[^}]+}#', '', $path);

                return $path;
            }
        }

        throw new Exception('no route with that name');
    }
}
