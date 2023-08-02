<?php

namespace Framework\View;

use Closure;
use Exception;
use Framework\View\Engine\Engine;
use Framework\View\View;

/**
 * The Manager class is responsible for managing the view templates of a web application.
 * It allows adding paths and engines for rendering templates, resolving templates to their corresponding files,
 * and adding and using macros for more complex functionality.
 */
class Manager
{
    /**
     * @var array $paths An array of paths to search for templates.
     */
    protected array $paths = [];

    /**
     * @var array $engines An array of engines for rendering templates with their corresponding extensions.
     */
    protected array $engines = [];

    /**
     * @var array $macros A collection of macros with their names and closures.
     */
    protected array $macros = [];

    /**
     * Adds a path to the list of paths to search for templates.
     *
     * @param string $path The path to add.
     * @return static
     */
    public function addPath(string $path): static
    {
        array_push($this->paths, $path);
        return $this;
    }

    /**
     * Adds an engine for rendering templates with a given extension.
     *
     * @param string $extension The extension of the templates to render.
     * @param Engine $engine The engine to use for rendering.
     * @return static
     */
    public function addEngine(string $extension, Engine $engine): static
    {
        $this->engines[$extension] = $engine;
        $this->engines[$extension]->setManager($this);
        return $this;
    }

    /**
     * Resolves a template to its corresponding file and returns a new 'View' object with the engine, file path, and data.
     *
     * @param string $template The name of the template to resolve.
     * @param array $data The data to pass to the template.
     * @return View The resolved view object.
     * @throws Exception If the template cannot be resolved.
     */
    public function resolve(string $template, array $data = []): View
    {
        foreach ($this->engines as $extension => $engine) {
            foreach ($this->paths as $path) {
                $file = "{$path}/{$template}.{$extension}";

                if (is_file($file)) {
                    return new View($engine, realpath($file), $data);
                }
            }
        }

        throw new Exception("Could not resolve '{$template}'");
    }

    /**
     * Adds a macro to the collection.
     *
     * @param string $name The name of the macro.
     * @param Closure $closure The closure of the macro.
     * @return static
     */
    public function addMacro(string $name, Closure $closure): static
    {
        $this->macros[$name] = $closure;
        return $this;
    }

    /**
     * Uses a macro with the given name and values.
     *
     * @param string $name The name of the macro to use.
     * @param mixed ...$values The values to pass to the macro.
     * @return mixed The result of the macro.
     * @throws Exception If the macro is not defined.
     */
    public function useMacro(string $name, ...$values)
    {
        if (isset($this->macros[$name])) {
            $bound = $this->macros[$name]->bindTo($this);
            return $bound(...$values);
        }

        throw new Exception("Macro isn't defined: '{$name}'");
    }
}
