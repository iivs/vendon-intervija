<?php declare(strict_types = 1);

/**
 * A class to parse the URL and split it into controller, function, variables and query string.
 */
final class UrlParser
{

    /**
     * Base variable that query string is going into. This is set in ".htaccess" as "index.php?url=$1".
     *
     * @var string
     */
    private $base = 'url=';

    /**
     * Controller full name.
     *
     * @var string
     */
    private $controller = '';

    /**
     * Controller short name with first capital letter.
     *
     * @var string
     */
    private $controller_name = '';

    /**
     * Function name.
     *
     * @var string
     */
    private $function = '';

    /**
     * List of function variables.
     *
     * @var array
     */
    private $variables = [];

    /**
     * Query string at the end of URL.
     *
     * @var string
     */
    private $query_string = '';

    /**
     * Parses the given URL string and collects the controller name, function name, variables and query string.
     *
     * @param string $url_string    The URL to parse.
     * 
     * @throws Exception if URL cannot be parsed.
     *
     * @return bool Returns true if string is parsed successfully.
     */
    public function parse(string $url_string): bool
    {
        // Parse URL in lower case. But in case of error return the orignal URL state.
        $query_string = strtolower($url_string);

        $pos = strpos($query_string, $this->base);
        if ($pos === false) {
            throw new Exception(__('Error: Invalid URL "%1$s".', $url_string));
        }

        // Remove the first "url=" part. If actual "url=" parameter exists, it will be untouched.
        $query_string = substr($query_string, strlen($this->base));

        // This can actually be the "?" or real "&", which means everything after will go to $_REQUEST.
        $parts = explode('&', $query_string);

        if (array_key_exists(1, $parts)) {
            $this->query_string = $parts[1];
        }

        // Everything else, that is not $parts[0], is going to $_REQUEST.
        $parts = explode('/', $parts[0]);

        // Remove the last slash.
        $parts = array_filter($parts, function($val) {
            return ($val !== '');
        });

        // The controller name starts with capital letter and must have the word "Controller" at the end.
        $this->controller_name = ucfirst(array_shift($parts));
        $this->controller = $this->controller_name.'Controller';

        // Check if there are more parts like function and variables after the controller.
        if ($parts) {
            $this->function = array_shift($parts);
        }

        $this->variables = $parts;

        return true;
    }

    /**
     * Return the controller short name from the URL that was parsed.
     *
     * @return string
     */
    public function getControllerName(): string
    {
        return $this->controller_name;
    }

    /**
     * Return the controller full name from the URL that was parsed.
     *
     * @return string
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * Return the controller function name from the URL that was parsed.
     *
     * @return string
     */
    public function getFunction(): string
    {
        return $this->function;
    }

    /**
     * Return the function variables from the URL that was parsed.
     *
     * @return string
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * Return the query string from the URL that was parsed.
     *
     * @return string
     */
    public function getQueryString(): string
    {
        return $this->query_string;
    }
}
