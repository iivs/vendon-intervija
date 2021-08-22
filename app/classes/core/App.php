<?php declare(strict_types = 1);

/**
 * The main application class.
 */
final class App
{

    /**
     * An instance of the current App object.
     *
     * @var App
     */
    private static $instance;

    /**
     * The absolute path to the root directory.
     *
     * @var string
     */
    private $root_dir;

    /**
     * Returns the current instance of class.
     *
     * @static
     *
     * @return App
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new static;
        }

        return self::$instance;
    }

    /**
     * Returns the path to the root directory.
     *
     * @return string
     */
    public function getRootDir(): string
    {
        return realpath(__DIR__.'/../../..');
    }

    /**
     * Runs the application - get controller, function, variables and show the corresponding view.
     */
    public function run(): void
    {
        // Check the prequisites, load classes etc.
        $this->init();

        // If everything is ok so far, start the user session.
        Session::start();

        /*
         * Set up a new router, cet controller list and current controller, get curret function and variables. Also
         * acquires the default controller if not is asked in URL.
         */
        $router = new Router();
        $controllers = $router->getControllerList();
        $controller = $router->getController();
        $function = $router->getFunction();
        $variables = $router->getVariables();

        // Set router to view, so it can be used there are well. In case a view needs to get a link.
        $view = new View(['router' => $router]);

        // Set router to controller, so it can be used there like create or get a link.
        $controller->setRouter($router);

        if ($controllers[get_class($controller)]['functions'][$function]['variables'] && $variables) {
            // If there are variables, call the controller by passing the arguments.
            $data = call_user_func_array([$controller, $function], $variables);
        }
        else {
            // Function has no variables, so we can call the controller funtion this way.
            $data = $controller->$function();
        }

        // Set "data" variable to view. $this->data will contain information from controller.
        $view->setData($data);

        // Set the view file of current controller and function.
        $view->setView();
    }

    /**
     * Initialize the application, get the root directory, load classes and check if DB is installed.
     */
    private function init(): void
    {
        $this->root_dir = $this->getRootDir();

        $paths = [
            $this->root_dir.'/app/classes/core',
            $this->root_dir.'/app/classes/controllers'
        ];

        // Autoload the core and controller classes.
        spl_autoload_register(function (string $name) use ($paths) {
            foreach ($paths as $path) {
                if (is_file($path.'/'.$name.'.php')) {
                    require $path.'/'.$name.'.php';
                }
            }
        });

        // Add other include files.
        require_once $this->root_dir.'/app/includes/config.inc.php';
        require_once $this->root_dir.'/app/includes/schema.inc.php';
        require_once $this->root_dir.'/app/includes/func.inc.php';
        require_once $this->root_dir.'/app/includes/debug.inc.php';

        $this->checkDB();
    }

    /**
     * Check if MySQL PDO extension exists and database is properly set up.
     * 
     * @throws Exception if DB is not properly set up.
     */
    private function checkDB(): void
    {
        // Terminate application if PHP extension is not loaded.
        if (!extension_loaded('pdo_mysql')) {
            throw new Exception(__('MySQL PDO extension is not enabled.'));
        }

        // Get list of tables and check if any of the required tables are missing. Fail at first missing table.
        $tables = DB::query('SHOW TABLES')->fetchAll();
        $tables = (array_map(function($array) {
            return reset($array);
        }, $tables));

        $diff = array_diff(SCHEMA_REQUIRED, $tables);
        if ($diff) {
            throw new Exception(__('Error in database. Missing table "%1$s".', reset($diff)));
        }
    }
}
