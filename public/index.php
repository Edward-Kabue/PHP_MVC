
<?php
/**
 * This code snippet creates a router object, defines routes using the routes.php file,
 * and dispatches the appropriate route based on the current URL.
 *
 * Inputs:
 * - The vendor/autoload.php file is required to load the necessary classes and dependencies.
 * - The app/routes.php file is required to define the routes.
 *
 * Flow:
 * 1. The autoload.php file is included to load the necessary classes and dependencies.
 * 2. A new router object is created.
 * 3. The routes.php file is required and its contents are executed, passing the router object as a parameter.
 * 4. The dispatch() method of the router object is called to determine the appropriate route based on the current URL.
 * 5. The result of the dispatch() method is printed to the screen.
 *
 * Outputs:
 * - The appropriate route based on the current URL is dispatched and printed to the screen.
 *
 * Additional aspects:
 * - The Framework\Routing\Router class is used to handle routing.
 * - The routes.php file should define the routes using the $router object passed as a parameter.
 * - The dispatch() method of the router object returns the appropriate route based on the current URL.
 */

require_once __DIR__ . '/../vendor/autoload.php';

$router = new Framework\Routing\Router();

$routes = require_once __DIR__ . '/../app/routes.php';
$routes($router);

print $router->dispatch();