<?php


namespace application\core\Database;


use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Connectors\ConnectionFactory;

class CodeigniterConnectionFactory extends ConnectionFactory
{
    var $driver;
    var $connection;
    var $database;
    var $prefix;

    /**
     * Create a new connection factory instance.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @return void
     */
    public function __construct(Container $container, $driver, $connection, $database, $prefix = '')
    {
        parent::__construct($container);
        $this->driver = $driver;
        $this->connection = $connection;
        $this->database = $database;
        $this->prefix = $prefix;
    }

    /**
     * Establish a PDO connection based on the configuration.
     *
     * @param array $config
     * @param string|null $name
     * @return \Illuminate\Database\Connection
     */
    public function make(array $config, $name = null)
    {
        return $this->createConnection($this->driver, $this->connection, $this->database, $this->prefix);
    }

}