<?php

namespace Framework\Routing;

class Route
{
    protected string $method;
    protected string $path;
    protected $handler;
    protected array $parameters = [];
    protected ?string $name = null;

    /**
     * Constructs a new instance of the class.
     *
     * @param string $method The HTTP method.
     * @param string $path The path of the route.
     * @param callable $handler The callback function that handles the route.
     */
    public function __construct(string $method, string $path, callable $handler)
    {
        $this->method = $method;
        $this->path = $path;
        $this->handler = $handler;
    }

    /**
     * Retrieves the method.
     *
     * @param string $method The method to retrieve.
     * @return string The retrieved method.
     */
    public function method(string $method): string
    {
        return $this->method;
    }

    /**
     * Get the path of the object.
     *
     * @return string The path of the object.
     */
    public function path()
    {
        return $this->path;
    }

    /**
     * Returns the parameters of the object.
     *
     * @return array The parameters of the object.
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * Sets or retrieves the name of the object.
     *
     * @param string $name The name to set for the object. If null, the function will retrieve the current name.
     * @return string|null The name of the object if $name is null, otherwise returns null.
     */
    public function name(string $name = null)
    {
        if ($name) {
            $this->name = $name;
            return $this;
        }

        return $this->name;
    }

    /**
     * Checks if the given method and path match the current object's method and path.
     *
     * @param string $method The method to compare.
     * @param string $path The path to compare.
     * @return bool Returns true if the method and path match, false otherwise.
     */
    public function matches(string $method, string $path): bool
    {
        if (
            $this->method === $method
            && $this->path === $path
        ) {
            return true;
        }

        $parameterNames = [];

        $pattern = $this->normalisePath($this->path);

        $pattern = preg_replace_callback('#{([^}]+)}/#', function (array $found) use (&$parameterNames) {
            array_push($parameterNames, rtrim($found[1], '?'));

            if (str_ends_with($found[1], '?')) {
                return '([^/]*)(?:/?)';    
            }

            return '([^/]+)/';
        }, $pattern);

        if (
            !str_contains($pattern, '+')
            && !str_contains($pattern, '*')
        ) {
            return false;
        }

        preg_match_all("#{$pattern}#", $this->normalisePath($path), $matches);

        $parameterValues = [];

        if (count($matches[1]) > 0) {
            foreach ($matches[1] as $value) {
                if ($value) {
                    array_push($parameterValues, $value);
                    continue;
                }

                array_push($parameterValues, null);
            }

            $emptyValues = array_fill(0, count($parameterNames), false);
            $parameterValues += $emptyValues;

            $this->parameters = array_combine($parameterNames, $parameterValues);

            return true;
        }

        return false;
    }

    /**
     * Normalizes the given path by removing leading and trailing slashes,
     * adding a leading slash, and removing consecutive slashes.
     *
     * @param string $path The path to be normalized.
     * @return string The normalized path.
     */
    private function normalisePath(string $path): string
    {
        $path = trim($path, '/');
        $path = "/{$path}/";
        $path = preg_replace('/[\/]{2,}/', '/', $path);

        return $path;
    }

    /**
     * Dispatches the function.
     *
     * @throws Some_Exception_Class description of exception
     * @return Some_Return_Value
     */
  
    public function dispatch()
    {
        return call_user_func($this->handler);
    }
}
