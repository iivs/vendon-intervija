<?php declare(strict_types = 1);

/**
 * The general controller that handles the common features.
 */
class Controller
{

    /**
     * Allows to use the router in controller. Which allows to get and set URLs for redirecting the user.
     *
     * @var Router
     */
    private $router;

    /**
     * Default URL to redirect user back in case of an error.
     *
     * @var string
     */
    protected $default_back_url = '';

    /**
     * Sets a router to be used by controller.
     *
     * @param Router $router
     */
    public function setRouter(Router $router): void
    {
        $this->router = $router;
    }

    /**
     * Get the name of the current controller. Read the class name and remove the "Controller" part.
     * 
     * @return string   Returns the name of the current controller from where this function is called. For example if
     *                  controller name is "GeorgeController", the name returned is "george".
     */
    public function getControllerName(): string
    {
        $name = get_class($this);

        $pos = strrpos($name, 'Controller');
        if ($pos !== false) {
            $name = strtolower(substr($name, 0, $pos));
        }

        return $name;
    }

    /**
     * Retrieves the router object which is then use in the controller.
     *
     * @return Router
     */
    protected function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Checks the HTTP referer. If it exists, use it. If not, get the base location. Set a query string "ref" with the
     * referer or home URL. This will be URL can then be used for a "back" button to return to a valid URL in case user
     * gets completely lost.
     *
     * @return string
     */
    protected function getBackUrl(): string
    {
        // Use the referer if exists.
        if (array_key_exists('HTTP_REFERER', $_SERVER) && $_SERVER['HTTP_REFERER'] !== '') {
            $back_url = '?ref='.$_SERVER['HTTP_REFERER'];
        }
        else {
            /*
             * Otherwise build the URL based on script location. For example, if script is located in
             * http://localhost/my_projects/folder123/index.php, set the first part http://localhost and then add
             * everythig else that is before the /index.php making the URL http://localhost/my_projects/folder123/
             * Then append anything that is in the default back URL. Which is the controller name, function, variables
             * etc. Add that URL to "?ref=" parameter which will then be parsed in another function.
             */
            $back_url = '?ref='.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];

            $pos = strpos($_SERVER['SCRIPT_NAME'], '/index.php');
            if ($pos !== false) {
                $back_url .= substr($_SERVER['SCRIPT_NAME'], 0, $pos + 1);
            }

            $back_url .= $this->default_back_url;
        }

        return $back_url;
    }

    /**
     * Valiates the user input against the given rules.
     *
     * @param array $rules  List of rules.
     *
     * @return bool         Returns true if there are no errors or returns false if at least one field does not
     *                      validate.
     */
    protected function validateRequest(array $rules = []): bool
    {
        $errors = [];

        foreach ($rules as $field => $ruleset) {
            $name = $field;

            foreach ($ruleset as $type => $set) {
                switch ($type) {
                    // Set a custom name to a field instead of the one that is used by input tag.
                    case 'name':
                        $name = $set;
                        break;

                    case 'rules':
                        foreach ($set as $rule => $error_msg) {
                            $rule = trim($rule);

                            // Check if the input is valid. Apply custom error message, if given.
                            switch ($rule) {
                                case 'required':
                                    if ($error_msg === '') {
                                        $error_msg = __('Field "%1$s" is required.', $name);
                                    }

                                    if (!$this->hasInput($field)) {
                                        $errors[] = $error_msg;
                                    }
                                    break;

                                case 'string':
                                    if ($error_msg === '') {
                                        $error_msg = __('Field "%1$s" is not string.', $name);
                                    }

                                    if (!is_string($this->getInput($field))) {
                                        $errors[] = $error_msg;
                                    }
                                    break;

                                case 'int':
                                    if ($error_msg === '') {
                                        $error_msg = __('Field "%1$s" is not an integer.', $name);
                                    }

                                    if (!is_numeric($this->getInput($field))) {
                                        $errors[] = $error_msg;
                                    }
                                    break;

                                case 'bool':
                                    if ($error_msg === '') {
                                        $error_msg = __('Field "%1$s" is not boolean.', $name);
                                    }

                                    if (!is_bool($this->getInput($field))) {
                                        $errors[] = $error_msg;
                                    }
                                    break;

                                case 'array':
                                    if ($error_msg === '') {
                                        $error_msg = __('Field "%1$s" is not an array.', $name);
                                    }

                                    if (!is_array($this->getInput($field))) {
                                        $errors[] = $error_msg;
                                    }
                                    break;

                                case 'not_empty':
                                    if ($error_msg === '') {
                                        $error_msg = __('Field "%1$s" is cannot be empty.', $name);
                                    }

                                    if (trim($this->getInput($field, '')) === '') {
                                        $errors[] = $error_msg;
                                    }
                                    break;
                            }
                        }
                        break;
                }
            }
        }

        // Set errors to session.
        if ($errors) {
            foreach ($errors as $error) {
                Messages::addError($error);
            }

            return false;
        }

        return true;
    }

    /**
     * Allows to set addional errors to session from controller.
     *
     * @param string $error Error message.
     */
    protected function setError(string $error): void
    {
        Messages::addError($error);
    }

    /**
     * Get the specific inputs from $_REQUEST by given keys or get all of the $_REQUEST if no keys are specified.
     */
    protected function getInputs(array $keys = [])
    {
        if ($keys) {
            return array_filter($_REQUEST, function($key) use ($keys) {
                foreach ($keys as $k) {
                    return ($k === $key);
                }
            }, ARRAY_FILTER_USE_KEY);
        }
        else {
            return $_REQUEST;
        }
    }

    /**
     * Get one input from $_REQUEST by given key or return a default value if input does not exist.
     *
     * @param string $key       Array kef of the $_REQUEST.
     * @param string $default   Default value to return if input does not exist.
     *
     * @return mixed            Returns value from $_REQUEST which can be anything (string, bool, array etc) or
     *                          default value.
     */
    protected function getInput(string $key, string $default = null)
    {
        if ($this->hasInput($key)) {
            return $_REQUEST[$key];
        }
        else {
            return $default;
        }
    }

    /**
     * Check if the input in $_REQUEST exists.
     *
     * @param string $key   Array kef of the $_REQUEST.
     * 
     * @return bool         Returns true if input exists or returns false if input does not exist.
     */
    protected function hasInput(string $key): bool
    {
        return (array_key_exists($key, $_REQUEST)) ? true : false;
    }
}
