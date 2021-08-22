<?php declare(strict_types = 1);

/**
 * Router class to handle the controllers, functions, passing variables to get, getting current URLs etc.
 */
final class Router
{
    /**
     * Root directory where application is located.
     *
     * @var string
     */
    private $root_dir = '';

    /**
     * Directory where controllers are located.
     *
     * @var string
     */
    private $controller_path = '';

    /**
     * List of possible controllers that exists in controller directory.
     *
     * @var array
     */
    private $controllers = [];

    /**
     * Current controller object.
     *
     * @var object
     */
    private $controller;

    /**
     * Current function name the controller is using.
     *
     * @var string
     */
    private $function = '';

    /**
     * Directory where views are located.
     *
     * @var string
     */
    private $view_path = '';

    /**
     * Path to current view file that is being used to display data in browser.
     *
     * @var string
     */
    private $view = '';

    /**
     * Current query string which is appended at the end of the URL after first "?".
     *
     * @var string
     */
    private $query_string = '';

    /**
     * Data array for view.
     *
     * @var array
     */
    private $data = [];

    /**
     * Creates a router - sets the root directory, set the paths to controllers and views, loads list of possible
     * controllers, validates if called controller exists, validates function and sets the current values of controller.
     */
    public function __construct()
    {
        // Get root directory and set paths where controllers and views are located.
        $this->root_dir = App::getInstance()->getRootDir();
        $this->controller_path = $this->root_dir.'/app/classes/controllers';
        $this->view_path = $this->root_dir.'/app/views';

        /*
         * Reads the controller directory and files and creates a list of controllers with all their public functions
         * and variables.
         */
        $this->loadControllers();

        /*
         * Collect class names, public functions, variables, current view and query string from the current route.
         * For example of current URL is http://localhost/my_projects/tests/questions/1/2/?ref=abc the controller is
         * "tests", the function is "questions", variables is an array [1,2] and query string is "?ref=abc". The view
         * is determined by controller, view and path. In this case the view is full path
         * "/httpdocs/app/views/Tests/questions.php".
         */
        [$controller, $function, $variables, $view, $query_string] = $this->getRoute();

        // Check if the called controller actually exists in the controller list we created before.
        $this->validateController($controller);

        // If controller exists, initialize the controller class.
        $this->controller = new $controller();

        // Check if the called controller funcion is in the list of the current controller funtions.
        $this->validateFunction($controller, $function);

        // If everything was OK, set the other parameters of the current controller.
        $this->function = $function;
        $this->variables = $variables;
        $this->view = $view;
        $this->query_string = $query_string;
    }

    /**
     * Makes an URL depeding on current controller or given paremeters. For example if no controller is given, it will
     * use the current controller. If no function is given, use the current function (remain on same page).
     *
     * Example:
     * $router->getUrl('UsersController', 'create') will make an URL to "/users/create/" (depends also on where the
     * index.php is located)
     *
     * @param string     $controller    Set destination URL to a new controller.
     * @param string     $function      Set destiontion URL to a new function.
     * @param array|null $variables     Give new paremeters in URL.
     * @param string     $query_string  Append a query string at the end or variables.
     *
     * @return string                   The new destinal URL that contains controller, function, variables and query
     *                                  string.
     */
    public function getUrl(string $controller = '', string $function = '', ?array $variables = [],
        string $query_string = ''): string
    {
        // Locate the script location in case it is not in root http://localhost/ but some deeper folder.
        $url = $_SERVER['PHP_SELF'];
        $pos = strpos($url, 'index.php');
        if ($pos !== false) {
            $url = substr($url, 0, $pos);
        }

        // If controller is not given, use current controller or validate the new controller.
        if ($controller === '') {
            $controller = $this->controller;
        }
        else {
            $this->validateController($controller);
            $controller = new $controller();
        }

        // If controller exists, get it's name, to check what functions it contains.
        $controller_name = $controller->getControllerName();
        $controller_name = strtolower($controller_name);

        // If no function is given, use current function. Otherwise validate if function exists in controller.
        if ($function === '') {
            $function = $this->function;
        }
        else {
            $this->validateFunction(get_class($controller), $function);
        }

        // No need to validate variables. At least for now. To reset variables, set them to null.
        if ($variables !== null && !$variables) {
            $variables = $this->variables;
        }
        elseif ($variables === null) {
            $variables = [];
        }

        // Build the destination URL.
        if ($function === '') {
            return $url.$controller_name.$query_string;
        }
        else {
            return $url.$controller_name.'/'.$function.'/'.implode('/', $variables).$query_string;
        }
    }

    /**
     * Get the current controller, function, variables and query string from URL. Also handles a backup home controller
     * if none is given. 
     *
     * @throws Exception if directories or views are not found.
     *
     * @return array    Returns the list in this order: controller, function, variables, view, query string.
     */
    public function getRoute(): array
    {
        /*
         * Set default controller 'TestController' as fall back if controller in URL is not found as we are sure this is
         * what users will see as default when visiting the page. Ideally it would be some kind of HomeController, but
         * that is not implemented. For now TestsController is the only one.
         */
        $controller = 'TestsController';

        // The name of the default controller. This is going to be used by view. Must match the folder name.
        $controller_name = 'Tests';

        // Default function to execute if none is given in URL.
        $function = 'index';

        // Other default parameters if not given in URL.
        $variables = [];
        $view = '';
        $query_string = '';

        // Since we are using mod rewrite, we are almost always redirecting.
        if (array_key_exists('REDIRECT_QUERY_STRING', $_SERVER)) {
            // Parse the current URL and get the controller name, function, variables and query string.
            $url_parser = new UrlParser();
            $url_parser->parse($_SERVER['REDIRECT_QUERY_STRING']);

            // If URL is, for example, http://localhost/path_to_proj/johny/bravo, the controller is "JohnyController".
            if ($url_parser->getController() !== '') {
                $controller = $url_parser->getController();
            }

            // If URL is, for example, http://localhost/path_to_proj/johny/bravo, the function name is "bravo".
            if ($url_parser->getFunction() !== '') {
                $function = $url_parser->getFunction();
            }

            // If URL is, for example, http://localhost/path_to_proj/johny/bravo/1/2/3, the variables are array [1,2,3].
            if ($url_parser->getVariables()) {
                $variables = $url_parser->getVariables();
            }

            // If URL is, for example, http://localhost/path_to_proj/johny/bravo, the controller name is "Johny".
            if ($url_parser->getControllerName() !== '') {
                $controller_name = $url_parser->getControllerName();
            }

            // If URL is, for example, http://localhost/path_to_proj/johny/?ref=bravo, the query string is "?ref=bravo".
            $query_string = $url_parser->getQueryString();
        }
        // else we are explicitly using http://localhost/path_to_proj/index.php file in URL so we are not redirecting.

        // Check if the view folder exists. It is determined by the controller name and path.
        if (is_dir($this->view_path.'/'.$controller_name)) {
            if ($handle = opendir($this->view_path.'/'.$controller_name)) {
                if (is_file($this->view_path.'/'.$controller_name.'/'.$function.'.php')) {
                    // Set the full path to view if file is found.
                    $view = $this->view_path.'/'.$controller_name.'/'.$function.'.php';
                    closedir($handle);
                }
                else {
                    closedir($handle);
                    throw new Exception(__('Error: View "%1$s" not found in "%2$s".', $function,
                        $this->view_path.'/'.$controller_name
                    ));
                }
            }
            else {
                throw new Exception(__('Error: Cannot read directory "%1$s".', $this->view_path.'/'.$controller_name));
            }
        }
        else {
            throw new Exception(__('Error: Directory "%1$s" not found.', $this->view_path.'/'.$controller_name));
        }
        /*
         * otherwise the user is browsing the main page (index.php), so this is the the defaut controller and function
         * becomes irrelevant.
         */

        return [$controller, $function, $variables, $view, $query_string];
    }

    /**
     * Returns the current query string.
     *
     * @return string
     */
    public function getQueryString(): string
    {
        return $this->query_string;
    }

    /**
     * Returns the current controller list.
     *
     * @return array
     */
    public function getControllerList(): array
    {
        return $this->controllers;
    }

    /**
     * Returns the current controller.
     *
     * @return object
     */
    public function getController(): object
    {
        return $this->controller;
    }

    /**
     * Returns the current function.
     *
     * @return string
     */
    public function getFunction(): string
    {
        return $this->function;
    }

    /**
     * Returns the current list of variables.
     *
     * @return array
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * Returns the current path to view.
     *
     * @return string
     */
    public function getView(): string
    {
        return $this->view;
    }

    /**
     * Load the controllers from the directory with all the public functions and variables.
     */
    private function loadControllers(): void
    {
        if (is_dir($this->controller_path)) {
            if ($handle = opendir($this->controller_path)) {
                while (($file = readdir($handle)) !== false) {
                    // Skip directories and read only files.
                    if ($file !== '.' && $file !== '..' && is_file($this->controller_path.'/'.$file)) {
                        // Get the file contents and parse the file. Get class name, functions and variables in result.
                        $contents = file_get_contents($this->controller_path.'/'.$file);
                        $parser = new FileParser();
                        $result = $parser->parse($contents, 0);

                        $class = key($result);

                        // Assume the file name ends with .php, remove that and compare with the class name returned.
                        if (substr($file, 0, strlen($file) - 4) !== $class) {
                            throw new Exception(__('Error: Invalid class name "%1$s" for file "%2$s".', $class, $file));
                        }

                        // In case previous check fails, but should not, check if class already exists.
                        if (array_key_exists($class, $this->controllers)) {
                            throw new Exception(__('Error: Cannot redeclare class "%1$s".', $class));
                        }
                        else {
                            $this->controllers += $result;
                        }
                    }
                }
                closedir($handle);
            }
        }
        else {
            throw new Exception(__('Error: Invalid controller directory specified.'));
        }
    }

    /**
     * Check if the controller called exists in the list.
     *
     * @param string $controller    Controller name.
     *
     * @throws Exception if controller does not exist.
     */
    private function validateController(string $controller): void
    {
        if (!array_key_exists($controller, $this->controllers)) {
            throw new Exception(__('Error: Cannot find controller "%1$s".', $controller));
        }
    }

    /**
     * Check if the function called in a controller exists in the list. It could be that function doesn't exist or
     * actually is a private or protected function.
     *
     * @param string $controller    Controller name.
     * @param string $function      Function name.
     *
     * @throws Exception if controller does not exist.
     */
    private function validateFunction(string $controller, string $function): void
    {
        if (!array_key_exists($function, $this->controllers[$controller]['functions'])) {
            throw new Exception(__('Error: Cannot find function "%1$s" in controller "%2$s".', $function, $controller));
        }
    }
}
