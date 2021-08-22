<?php declare(strict_types = 1);

/**
 * A class to handle the views. Views operate by getting data from controller and set to $this->data variable which is
 * used by view file.
 */
final class View
{

    /**
     * Data which is used in view.
     *
     * @var array
     */
    public $data;

    /**
     * The router which will retrieve the full path to view file.
     *
     * @var Router
     */
    private $router;

    /**
     * Initialize the view with options.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (array_key_exists('router', $options)) {
            $this->router = $options['router'];
        }
    }

    /**
     * Include the view file.
     */
    public function setView(): void
    {
        include_once $this->router->getView();
    }

    /**
     * Set the data to view.
     *
     * @param array $data
     */
    public function setData(array $data = []): void
    {
        $this->data = $data;
    }
}
