<?php
die('This file is used for development purposes only.');
/**
 * PhpStorm Code Completion to CodeIgniter + HMVC
 *
 * @package       CodeIgniter
 * @subpackage    PhpStorm
 * @category      Code Completion
 * @author        Natan Felles
 * @link          http://github.com/natanfelles/codeigniter-phpstorm
 */

/*
 * To enable code completion to your own libraries add a line above each class as follows:
 *
 * @property Library_name       $library_name                        Library description
 *
 */


namespace Illuminate\Support\Facades {

    /**
     *
     *
     * @see \Illuminate\Database\Schema\Builder
     */
    class Schema
    {

        /**
         * Determine if the given table exists.
         *
         * @param string $table
         * @return bool
         * @static
         */
        public static function hasTable($table)
        {
            /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
            return $instance->hasTable($table);
        }

        /**
         * Get the column listing for a given table.
         *
         * @param string $table
         * @return array
         * @static
         */
        public static function getColumnListing($table)
        {
            /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
            return $instance->getColumnListing($table);
        }

        /**
         * Drop all tables from the database.
         *
         * @return void
         * @static
         */
        public static function dropAllTables()
        {
            /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
            $instance->dropAllTables();
        }

        /**
         * Drop all views from the database.
         *
         * @return void
         * @static
         */
        public static function dropAllViews()
        {
            /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
            $instance->dropAllViews();
        }

        /**
         * Set the default string length for migrations.
         *
         * @param int $length
         * @return void
         * @static
         */
        public static function defaultStringLength($length)
        {
            //Method inherited from \Illuminate\Database\Schema\Builder
            \Illuminate\Database\Schema\MySqlBuilder::defaultStringLength($length);
        }

        /**
         * Determine if the given table has a given column.
         *
         * @param string $table
         * @param string $column
         * @return bool
         * @static
         */
        public static function hasColumn($table, $column)
        {
            //Method inherited from \Illuminate\Database\Schema\Builder
            /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
            return $instance->hasColumn($table, $column);
        }

        /**
         * Determine if the given table has given columns.
         *
         * @param string $table
         * @param array $columns
         * @return bool
         * @static
         */
        public static function hasColumns($table, $columns)
        {
            //Method inherited from \Illuminate\Database\Schema\Builder
            /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
            return $instance->hasColumns($table, $columns);
        }

        /**
         * Get the data type for the given column name.
         *
         * @param string $table
         * @param string $column
         * @return string
         * @static
         */
        public static function getColumnType($table, $column)
        {
            //Method inherited from \Illuminate\Database\Schema\Builder
            /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
            return $instance->getColumnType($table, $column);
        }

        /**
         * Modify a table on the schema.
         *
         * @param string $table
         * @param \Closure $callback
         * @return void
         * @static
         */
        public static function table($table, $callback)
        {
            //Method inherited from \Illuminate\Database\Schema\Builder
            /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
            $instance->table($table, $callback);
        }

        /**
         * Create a new table on the schema.
         *
         * @param string $table
         * @param \Closure $callback
         * @return void
         * @static
         */
        public static function create($table, $callback)
        {
            //Method inherited from \Illuminate\Database\Schema\Builder
            /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
            $instance->create($table, $callback);
        }

        /**
         * Drop a table from the schema.
         *
         * @param string $table
         * @return void
         * @static
         */
        public static function drop($table)
        {
            //Method inherited from \Illuminate\Database\Schema\Builder
            /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
            $instance->drop($table);
        }

        /**
         * Drop a table from the schema if it exists.
         *
         * @param string $table
         * @return void
         * @static
         */
        public static function dropIfExists($table)
        {
            //Method inherited from \Illuminate\Database\Schema\Builder
            /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
            $instance->dropIfExists($table);
        }

        /**
         * Drop all types from the database.
         *
         * @return void
         * @throws \LogicException
         * @static
         */
        public static function dropAllTypes()
        {
            //Method inherited from \Illuminate\Database\Schema\Builder
            /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
            $instance->dropAllTypes();
        }

        /**
         * Rename a table on the schema.
         *
         * @param string $from
         * @param string $to
         * @return void
         * @static
         */
        public static function rename($from, $to)
        {
            //Method inherited from \Illuminate\Database\Schema\Builder
            /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
            $instance->rename($from, $to);
        }

        /**
         * Enable foreign key constraints.
         *
         * @return bool
         * @static
         */
        public static function enableForeignKeyConstraints()
        {
            //Method inherited from \Illuminate\Database\Schema\Builder
            /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
            return $instance->enableForeignKeyConstraints();
        }

        /**
         * Disable foreign key constraints.
         *
         * @return bool
         * @static
         */
        public static function disableForeignKeyConstraints()
        {
            //Method inherited from \Illuminate\Database\Schema\Builder
            /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
            return $instance->disableForeignKeyConstraints();
        }

        /**
         * Register a custom Doctrine mapping type.
         *
         * @param string $class
         * @param string $name
         * @param string $type
         * @return void
         * @throws \Doctrine\DBAL\DBALException
         * @static
         */
        public static function registerCustomDoctrineType($class, $name, $type)
        {
            //Method inherited from \Illuminate\Database\Schema\Builder
            /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
            $instance->registerCustomDoctrineType($class, $name, $type);
        }

        /**
         * Get the database connection instance.
         *
         * @return \Illuminate\Database\Connection
         * @static
         */
        public static function getConnection()
        {
            //Method inherited from \Illuminate\Database\Schema\Builder
            /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
            return $instance->getConnection();
        }

        /**
         * Set the database connection instance.
         *
         * @param \Illuminate\Database\Connection $connection
         * @return \Illuminate\Database\Schema\MySqlBuilder
         * @static
         */
        public static function setConnection($connection)
        {
            //Method inherited from \Illuminate\Database\Schema\Builder
            /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
            return $instance->setConnection($connection);
        }

        /**
         * Set the Schema Blueprint resolver callback.
         *
         * @param \Closure $resolver
         * @return void
         * @static
         */
        public static function blueprintResolver($resolver)
        {
            //Method inherited from \Illuminate\Database\Schema\Builder
            /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
            $instance->blueprintResolver($resolver);
        }

    }

    /**
     *
     *
     * @see \Illuminate\Config\Repository
     */
    class Config
    {

        /**
         * Determine if the given configuration value exists.
         *
         * @param string $key
         * @return bool
         * @static
         */
        public static function has($key)
        {
            /** @var \Illuminate\Config\Repository $instance */
            return $instance->has($key);
        }

        /**
         * Get the specified configuration value.
         *
         * @param array|string $key
         * @param mixed $default
         * @return mixed
         * @static
         */
        public static function get($key, $default = null)
        {
            /** @var \Illuminate\Config\Repository $instance */
            return $instance->get($key, $default);
        }

        /**
         * Get many configuration values.
         *
         * @param array $keys
         * @return array
         * @static
         */
        public static function getMany($keys)
        {
            /** @var \Illuminate\Config\Repository $instance */
            return $instance->getMany($keys);
        }

        /**
         * Set a given configuration value.
         *
         * @param array|string $key
         * @param mixed $value
         * @return void
         * @static
         */
        public static function set($key, $value = null)
        {
            /** @var \Illuminate\Config\Repository $instance */
            $instance->set($key, $value);
        }

        /**
         * Prepend a value onto an array configuration value.
         *
         * @param string $key
         * @param mixed $value
         * @return void
         * @static
         */
        public static function prepend($key, $value)
        {
            /** @var \Illuminate\Config\Repository $instance */
            $instance->prepend($key, $value);
        }

        /**
         * Push a value onto an array configuration value.
         *
         * @param string $key
         * @param mixed $value
         * @return void
         * @static
         */
        public static function push($key, $value)
        {
            /** @var \Illuminate\Config\Repository $instance */
            $instance->push($key, $value);
        }

        /**
         * Get all of the configuration items for the application.
         *
         * @return array
         * @static
         */
        public static function all()
        {
            /** @var \Illuminate\Config\Repository $instance */
            return $instance->all();
        }

        /**
         * Determine if the given configuration option exists.
         *
         * @param string $key
         * @return bool
         * @static
         */
        public static function offsetExists($key)
        {
            /** @var \Illuminate\Config\Repository $instance */
            return $instance->offsetExists($key);
        }

        /**
         * Get a configuration option.
         *
         * @param string $key
         * @return mixed
         * @static
         */
        public static function offsetGet($key)
        {
            /** @var \Illuminate\Config\Repository $instance */
            return $instance->offsetGet($key);
        }

        /**
         * Set a configuration option.
         *
         * @param string $key
         * @param mixed $value
         * @return void
         * @static
         */
        public static function offsetSet($key, $value)
        {
            /** @var \Illuminate\Config\Repository $instance */
            $instance->offsetSet($key, $value);
        }

        /**
         * Unset a configuration option.
         *
         * @param string $key
         * @return void
         * @static
         */
        public static function offsetUnset($key)
        {
            /** @var \Illuminate\Config\Repository $instance */
            $instance->offsetUnset($key);
        }

    }

    /**
     *
     *
     * @see \Illuminate\Database\DatabaseManager
     * @see \Illuminate\Database\Connection
     */
    class DB
    {

        /**
         * Get a database connection instance.
         *
         * @param string|null $name
         * @return \Illuminate\Database\Connection
         * @static
         */
        public static function connection($name = null)
        {
            /** @var \Illuminate\Database\DatabaseManager $instance */
            return $instance->connection($name);
        }

        /**
         * Disconnect from the given database and remove from local cache.
         *
         * @param string|null $name
         * @return void
         * @static
         */
        public static function purge($name = null)
        {
            /** @var \Illuminate\Database\DatabaseManager $instance */
            $instance->purge($name);
        }

        /**
         * Disconnect from the given database.
         *
         * @param string|null $name
         * @return void
         * @static
         */
        public static function disconnect($name = null)
        {
            /** @var \Illuminate\Database\DatabaseManager $instance */
            $instance->disconnect($name);
        }

        /**
         * Reconnect to the given database.
         *
         * @param string|null $name
         * @return \Illuminate\Database\Connection
         * @static
         */
        public static function reconnect($name = null)
        {
            /** @var \Illuminate\Database\DatabaseManager $instance */
            return $instance->reconnect($name);
        }

        /**
         * Get the default connection name.
         *
         * @return string
         * @static
         */
        public static function getDefaultConnection()
        {
            /** @var \Illuminate\Database\DatabaseManager $instance */
            return $instance->getDefaultConnection();
        }

        /**
         * Set the default connection name.
         *
         * @param string $name
         * @return void
         * @static
         */
        public static function setDefaultConnection($name)
        {
            /** @var \Illuminate\Database\DatabaseManager $instance */
            $instance->setDefaultConnection($name);
        }

        /**
         * Get all of the support drivers.
         *
         * @return array
         * @static
         */
        public static function supportedDrivers()
        {
            /** @var \Illuminate\Database\DatabaseManager $instance */
            return $instance->supportedDrivers();
        }

        /**
         * Get all of the drivers that are actually available.
         *
         * @return array
         * @static
         */
        public static function availableDrivers()
        {
            /** @var \Illuminate\Database\DatabaseManager $instance */
            return $instance->availableDrivers();
        }

        /**
         * Register an extension connection resolver.
         *
         * @param string $name
         * @param callable $resolver
         * @return void
         * @static
         */
        public static function extend($name, $resolver)
        {
            /** @var \Illuminate\Database\DatabaseManager $instance */
            $instance->extend($name, $resolver);
        }

        /**
         * Return all of the created connections.
         *
         * @return array
         * @static
         */
        public static function getConnections()
        {
            /** @var \Illuminate\Database\DatabaseManager $instance */
            return $instance->getConnections();
        }

        /**
         * Set the database reconnector callback.
         *
         * @param callable $reconnector
         * @return void
         * @static
         */
        public static function setReconnector($reconnector)
        {
            /** @var \Illuminate\Database\DatabaseManager $instance */
            $instance->setReconnector($reconnector);
        }

        /**
         * Get a schema builder instance for the connection.
         *
         * @return \Illuminate\Database\Schema\MySqlBuilder
         * @static
         */
        public static function getSchemaBuilder()
        {
            //Method inherited from \Illuminate\Database\MySqlConnection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->getSchemaBuilder();
        }

        /**
         * Bind values to their parameters in the given statement.
         *
         * @param \PDOStatement $statement
         * @param array $bindings
         * @return void
         * @static
         */
        public static function bindValues($statement, $bindings)
        {
            //Method inherited from \Illuminate\Database\MySqlConnection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            $instance->bindValues($statement, $bindings);
        }

        /**
         * Set the query grammar to the default implementation.
         *
         * @return void
         * @static
         */
        public static function useDefaultQueryGrammar()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            $instance->useDefaultQueryGrammar();
        }

        /**
         * Set the schema grammar to the default implementation.
         *
         * @return void
         * @static
         */
        public static function useDefaultSchemaGrammar()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            $instance->useDefaultSchemaGrammar();
        }

        /**
         * Set the query post processor to the default implementation.
         *
         * @return void
         * @static
         */
        public static function useDefaultPostProcessor()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            $instance->useDefaultPostProcessor();
        }

        /**
         * Begin a fluent query against a database table.
         *
         * @param string $table
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function table($table)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->table($table);
        }

        /**
         * Get a new query builder instance.
         *
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function query()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->query();
        }

        /**
         * Run a select statement and return a single result.
         *
         * @param string $query
         * @param array $bindings
         * @param bool $useReadPdo
         * @return mixed
         * @static
         */
        public static function selectOne($query, $bindings = array(), $useReadPdo = true)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->selectOne($query, $bindings, $useReadPdo);
        }

        /**
         * Run a select statement against the database.
         *
         * @param string $query
         * @param array $bindings
         * @return array
         * @static
         */
        public static function selectFromWriteConnection($query, $bindings = array())
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->selectFromWriteConnection($query, $bindings);
        }

        /**
         * Run a select statement against the database.
         *
         * @param string $query
         * @param array $bindings
         * @param bool $useReadPdo
         * @return array
         * @static
         */
        public static function select($query, $bindings = array(), $useReadPdo = true)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->select($query, $bindings, $useReadPdo);
        }

        /**
         * Run a select statement against the database and returns a generator.
         *
         * @param string $query
         * @param array $bindings
         * @param bool $useReadPdo
         * @return \Generator
         * @static
         */
        public static function cursor($query, $bindings = array(), $useReadPdo = true)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->cursor($query, $bindings, $useReadPdo);
        }

        /**
         * Run an insert statement against the database.
         *
         * @param string $query
         * @param array $bindings
         * @return bool
         * @static
         */
        public static function insert($query, $bindings = array())
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->insert($query, $bindings);
        }

        /**
         * Run an update statement against the database.
         *
         * @param string $query
         * @param array $bindings
         * @return int
         * @static
         */
        public static function update($query, $bindings = array())
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->update($query, $bindings);
        }

        /**
         * Run a delete statement against the database.
         *
         * @param string $query
         * @param array $bindings
         * @return int
         * @static
         */
        public static function delete($query, $bindings = array())
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->delete($query, $bindings);
        }

        /**
         * Execute an SQL statement and return the boolean result.
         *
         * @param string $query
         * @param array $bindings
         * @return bool
         * @static
         */
        public static function statement($query, $bindings = array())
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->statement($query, $bindings);
        }

        /**
         * Run an SQL statement and get the number of rows affected.
         *
         * @param string $query
         * @param array $bindings
         * @return int
         * @static
         */
        public static function affectingStatement($query, $bindings = array())
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->affectingStatement($query, $bindings);
        }

        /**
         * Run a raw, unprepared query against the PDO connection.
         *
         * @param string $query
         * @return bool
         * @static
         */
        public static function unprepared($query)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->unprepared($query);
        }

        /**
         * Execute the given callback in "dry run" mode.
         *
         * @param \Closure $callback
         * @return array
         * @static
         */
        public static function pretend($callback)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->pretend($callback);
        }

        /**
         * Prepare the query bindings for execution.
         *
         * @param array $bindings
         * @return array
         * @static
         */
        public static function prepareBindings($bindings)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->prepareBindings($bindings);
        }

        /**
         * Log a query in the connection's query log.
         *
         * @param string $query
         * @param array $bindings
         * @param float|null $time
         * @return void
         * @static
         */
        public static function logQuery($query, $bindings, $time = null)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            $instance->logQuery($query, $bindings, $time);
        }

        /**
         * Register a database query listener with the connection.
         *
         * @param \Closure $callback
         * @return void
         * @static
         */
        public static function listen($callback)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            $instance->listen($callback);
        }

        /**
         * Get a new raw query expression.
         *
         * @param mixed $value
         * @return \Illuminate\Database\Query\Expression
         * @static
         */
        public static function raw($value)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->raw($value);
        }

        /**
         * Indicate if any records have been modified.
         *
         * @param bool $value
         * @return void
         * @static
         */
        public static function recordsHaveBeenModified($value = true)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            $instance->recordsHaveBeenModified($value);
        }

        /**
         * Is Doctrine available?
         *
         * @return bool
         * @static
         */
        public static function isDoctrineAvailable()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->isDoctrineAvailable();
        }

        /**
         * Get a Doctrine Schema Column instance.
         *
         * @param string $table
         * @param string $column
         * @return \Doctrine\DBAL\Schema\Column
         * @static
         */
        public static function getDoctrineColumn($table, $column)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->getDoctrineColumn($table, $column);
        }

        /**
         * Get the Doctrine DBAL schema manager for the connection.
         *
         * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
         * @static
         */
        public static function getDoctrineSchemaManager()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->getDoctrineSchemaManager();
        }

        /**
         * Get the Doctrine DBAL database connection instance.
         *
         * @return \Doctrine\DBAL\Connection
         * @static
         */
        public static function getDoctrineConnection()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->getDoctrineConnection();
        }

        /**
         * Get the current PDO connection.
         *
         * @return \PDO
         * @static
         */
        public static function getPdo()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->getPdo();
        }

        /**
         * Get the current PDO connection used for reading.
         *
         * @return \PDO
         * @static
         */
        public static function getReadPdo()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->getReadPdo();
        }

        /**
         * Set the PDO connection.
         *
         * @param \PDO|\Closure|null $pdo
         * @return \Illuminate\Database\MySqlConnection
         * @static
         */
        public static function setPdo($pdo)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->setPdo($pdo);
        }

        /**
         * Set the PDO connection used for reading.
         *
         * @param \PDO|\Closure|null $pdo
         * @return \Illuminate\Database\MySqlConnection
         * @static
         */
        public static function setReadPdo($pdo)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->setReadPdo($pdo);
        }

        /**
         * Get the database connection name.
         *
         * @return string|null
         * @static
         */
        public static function getName()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->getName();
        }

        /**
         * Get an option from the configuration options.
         *
         * @param string|null $option
         * @return mixed
         * @static
         */
        public static function getConfig($option = null)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->getConfig($option);
        }

        /**
         * Get the PDO driver name.
         *
         * @return string
         * @static
         */
        public static function getDriverName()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->getDriverName();
        }

        /**
         * Get the query grammar used by the connection.
         *
         * @return \Illuminate\Database\Query\Grammars\Grammar
         * @static
         */
        public static function getQueryGrammar()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->getQueryGrammar();
        }

        /**
         * Set the query grammar used by the connection.
         *
         * @param \Illuminate\Database\Query\Grammars\Grammar $grammar
         * @return \Illuminate\Database\MySqlConnection
         * @static
         */
        public static function setQueryGrammar($grammar)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->setQueryGrammar($grammar);
        }

        /**
         * Get the schema grammar used by the connection.
         *
         * @return \Illuminate\Database\Schema\Grammars\Grammar
         * @static
         */
        public static function getSchemaGrammar()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->getSchemaGrammar();
        }

        /**
         * Set the schema grammar used by the connection.
         *
         * @param \Illuminate\Database\Schema\Grammars\Grammar $grammar
         * @return \Illuminate\Database\MySqlConnection
         * @static
         */
        public static function setSchemaGrammar($grammar)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->setSchemaGrammar($grammar);
        }

        /**
         * Get the query post processor used by the connection.
         *
         * @return \Illuminate\Database\Query\Processors\Processor
         * @static
         */
        public static function getPostProcessor()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->getPostProcessor();
        }

        /**
         * Set the query post processor used by the connection.
         *
         * @param \Illuminate\Database\Query\Processors\Processor $processor
         * @return \Illuminate\Database\MySqlConnection
         * @static
         */
        public static function setPostProcessor($processor)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->setPostProcessor($processor);
        }

        /**
         * Get the event dispatcher used by the connection.
         *
         * @return \Illuminate\Contracts\Events\Dispatcher
         * @static
         */
        public static function getEventDispatcher()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->getEventDispatcher();
        }

        /**
         * Set the event dispatcher instance on the connection.
         *
         * @param \Illuminate\Contracts\Events\Dispatcher $events
         * @return \Illuminate\Database\MySqlConnection
         * @static
         */
        public static function setEventDispatcher($events)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->setEventDispatcher($events);
        }

        /**
         * Unset the event dispatcher for this connection.
         *
         * @return void
         * @static
         */
        public static function unsetEventDispatcher()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            $instance->unsetEventDispatcher();
        }

        /**
         * Determine if the connection is in a "dry run".
         *
         * @return bool
         * @static
         */
        public static function pretending()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->pretending();
        }

        /**
         * Get the connection query log.
         *
         * @return array
         * @static
         */
        public static function getQueryLog()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->getQueryLog();
        }

        /**
         * Clear the query log.
         *
         * @return void
         * @static
         */
        public static function flushQueryLog()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            $instance->flushQueryLog();
        }

        /**
         * Enable the query log on the connection.
         *
         * @return void
         * @static
         */
        public static function enableQueryLog()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            $instance->enableQueryLog();
        }

        /**
         * Disable the query log on the connection.
         *
         * @return void
         * @static
         */
        public static function disableQueryLog()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            $instance->disableQueryLog();
        }

        /**
         * Determine whether we're logging queries.
         *
         * @return bool
         * @static
         */
        public static function logging()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->logging();
        }

        /**
         * Get the name of the connected database.
         *
         * @return string
         * @static
         */
        public static function getDatabaseName()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->getDatabaseName();
        }

        /**
         * Set the name of the connected database.
         *
         * @param string $database
         * @return \Illuminate\Database\MySqlConnection
         * @static
         */
        public static function setDatabaseName($database)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->setDatabaseName($database);
        }

        /**
         * Get the table prefix for the connection.
         *
         * @return string
         * @static
         */
        public static function getTablePrefix()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->getTablePrefix();
        }

        /**
         * Set the table prefix in use by the connection.
         *
         * @param string $prefix
         * @return \Illuminate\Database\MySqlConnection
         * @static
         */
        public static function setTablePrefix($prefix)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->setTablePrefix($prefix);
        }

        /**
         * Set the table prefix and return the grammar.
         *
         * @param \Illuminate\Database\Grammar $grammar
         * @return \Illuminate\Database\Grammar
         * @static
         */
        public static function withTablePrefix($grammar)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->withTablePrefix($grammar);
        }

        /**
         * Register a connection resolver.
         *
         * @param string $driver
         * @param \Closure $callback
         * @return void
         * @static
         */
        public static function resolverFor($driver, $callback)
        {
            //Method inherited from \Illuminate\Database\Connection
            \Illuminate\Database\MySqlConnection::resolverFor($driver, $callback);
        }

        /**
         * Get the connection resolver for the given driver.
         *
         * @param string $driver
         * @return mixed
         * @static
         */
        public static function getResolver($driver)
        {
            //Method inherited from \Illuminate\Database\Connection
            return \Illuminate\Database\MySqlConnection::getResolver($driver);
        }

        /**
         * Execute a Closure within a transaction.
         *
         * @param \Closure $callback
         * @param int $attempts
         * @return mixed
         * @throws \Exception|\Throwable
         * @static
         */
        public static function transaction($callback, $attempts = 1)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            return $instance->transaction($callback, $attempts);
        }

        /**
         * Start a new database transaction.
         *
         * @return void
         * @throws \Exception
         * @static
         */
        public static function beginTransaction()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \\Illuminate\Database\MySqlConnection $instance */
            $instance->beginTransaction();
        }

        /**
         * Commit the active database transaction.
         *
         * @return void
         * @static
         */
        public static function commit()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            $instance->commit();
        }

        /**
         * Rollback the active database transaction.
         *
         * @param int|null $toLevel
         * @return void
         * @throws \Exception
         * @static
         */
        public static function rollBack($toLevel = null)
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var \Illuminate\Database\MySqlConnection $instance */
            $instance->rollBack($toLevel);
        }

        /**
         * Get the number of active transactions.
         *
         * @return int
         * @static
         */
        public static function transactionLevel()
        {
            //Method inherited from \Illuminate\Database\Connection
            /** @var  \Illuminate\Database\MySqlConnection $instance */
            return $instance->transactionLevel();
        }

    }

    /**
     *
     *
     * @method static array getListeners(string $eventName)
     * @method static \Closure makeListener(\Closure|string $listener, bool $wildcard = false)
     * @method static \Closure createClassListener(string $listener, bool $wildcard = false)
     * @method static \Illuminate\Events\Dispatcher setQueueResolver(callable $resolver)
     * @see \Illuminate\Events\Dispatcher
     */
    class Event
    {

        /**
         * Set list of events that should be handled by transactional layer.
         *
         * @param array|null $transactional
         * @return void
         * @static
         */
        public static function setTransactionalEvents($transactional)
        {
            /** @var \Neves\Events\TransactionalDispatcher $instance */
            $instance->setTransactionalEvents($transactional);
        }

        /**
         * Set exceptions list.
         *
         * @param array $excluded
         * @return void
         * @static
         */
        public static function setExcludedEvents($excluded = array())
        {
            /** @var \Neves\Events\TransactionalDispatcher $instance */
            $instance->setExcludedEvents($excluded);
        }

        /**
         * Dispatch an event and call the listeners.
         *
         * @param string|object $event
         * @param mixed $payload
         * @param bool $halt
         * @return array|null
         * @static
         */
        public static function dispatch($event, $payload = array(), $halt = false)
        {
            /** @var \Neves\Events\TransactionalDispatcher $instance */
            return $instance->dispatch($event, $payload, $halt);
        }

        /**
         * Register an event listener with the dispatcher.
         *
         * @param string|array $events
         * @param mixed $listener
         * @return void
         * @static
         */
        public static function listen($events, $listener)
        {
            /** @var \Neves\Events\TransactionalDispatcher $instance */
            $instance->listen($events, $listener);
        }

        /**
         * Determine if a given event has listeners.
         *
         * @param string $eventName
         * @return bool
         * @static
         */
        public static function hasListeners($eventName)
        {
            /** @var \Neves\Events\TransactionalDispatcher $instance */
            return $instance->hasListeners($eventName);
        }

        /**
         * Register an event subscriber with the dispatcher.
         *
         * @param object|string $subscriber
         * @return void
         * @static
         */
        public static function subscribe($subscriber)
        {
            /** @var \Neves\Events\TransactionalDispatcher $instance */
            $instance->subscribe($subscriber);
        }

        /**
         * Dispatch an event until the first non-null response is returned.
         *
         * @param string|object $event
         * @param mixed $payload
         * @return array|null
         * @static
         */
        public static function until($event, $payload = array())
        {
            /** @var \Neves\Events\TransactionalDispatcher $instance */
            return $instance->until($event, $payload);
        }

        /**
         * Fire an event and call the listeners.
         *
         * @param string|object $event
         * @param mixed $payload
         * @param bool $halt
         * @return array|null
         * @static
         */
        public static function fire($event, $payload = array(), $halt = false)
        {
            /** @var \Neves\Events\TransactionalDispatcher $instance */
            return $instance->fire($event, $payload, $halt);
        }

        /**
         * Register an event and payload to be fired later.
         *
         * @param string $event
         * @param array $payload
         * @return void
         * @static
         */
        public static function push($event, $payload = array())
        {
            /** @var \Neves\Events\TransactionalDispatcher $instance */
            $instance->push($event, $payload);
        }

        /**
         * Flush a set of pushed events.
         *
         * @param string $event
         * @return void
         * @static
         */
        public static function flush($event)
        {
            /** @var \Neves\Events\TransactionalDispatcher $instance */
            $instance->flush($event);
        }

        /**
         * Remove a set of listeners from the dispatcher.
         *
         * @param string $event
         * @return void
         * @static
         */
        public static function forget($event)
        {
            /** @var \Neves\Events\TransactionalDispatcher $instance */
            $instance->forget($event);
        }

        /**
         * Forget all of the queued listeners.
         *
         * @return void
         * @static
         */
        public static function forgetPushed()
        {
            /** @var \Neves\Events\TransactionalDispatcher $instance */
            $instance->forgetPushed();
        }

        /**
         * Assert if an event was dispatched based on a truth-test callback.
         *
         * @param string $event
         * @param callable|int|null $callback
         * @return void
         * @static
         */
        public static function assertDispatched($event, $callback = null)
        {
            /** @var \Illuminate\Support\Testing\Fakes\EventFake $instance */
            $instance->assertDispatched($event, $callback);
        }

        /**
         * Assert if a event was dispatched a number of times.
         *
         * @param string $event
         * @param int $times
         * @return void
         * @static
         */
        public static function assertDispatchedTimes($event, $times = 1)
        {
            /** @var \Illuminate\Support\Testing\Fakes\EventFake $instance */
            $instance->assertDispatchedTimes($event, $times);
        }

        /**
         * Determine if an event was dispatched based on a truth-test callback.
         *
         * @param string $event
         * @param callable|null $callback
         * @return void
         * @static
         */
        public static function assertNotDispatched($event, $callback = null)
        {
            /** @var \Illuminate\Support\Testing\Fakes\EventFake $instance */
            $instance->assertNotDispatched($event, $callback);
        }

        /**
         * Get all of the events matching a truth-test callback.
         *
         * @param string $event
         * @param callable|null $callback
         * @return \Illuminate\Support\Collection
         * @static
         */
        public static function dispatched($event, $callback = null)
        {
            /** @var \Illuminate\Support\Testing\Fakes\EventFake $instance */
            return $instance->dispatched($event, $callback);
        }

        /**
         * Determine if the given event has been dispatched.
         *
         * @param string $event
         * @return bool
         * @static
         */
        public static function hasDispatched($event)
        {
            /** @var \Illuminate\Support\Testing\Fakes\EventFake $instance */
            return $instance->hasDispatched($event);
        }

    }

    /**
     *
     *
     * @see \Illuminate\Filesystem\Filesystem
     */
    class File
    {

        /**
         * Determine if a file or directory exists.
         *
         * @param string $path
         * @return bool
         * @static
         */
        public static function exists($path)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->exists($path);
        }

        /**
         * Get the contents of a file.
         *
         * @param string $path
         * @param bool $lock
         * @return string
         * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
         * @static
         */
        public static function get($path, $lock = false)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->get($path, $lock);
        }

        /**
         * Get contents of a file with shared access.
         *
         * @param string $path
         * @return string
         * @static
         */
        public static function sharedGet($path)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->sharedGet($path);
        }

        /**
         * Get the returned value of a file.
         *
         * @param string $path
         * @return mixed
         * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
         * @static
         */
        public static function getRequire($path)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->getRequire($path);
        }

        /**
         * Require the given file once.
         *
         * @param string $file
         * @return mixed
         * @static
         */
        public static function requireOnce($file)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->requireOnce($file);
        }

        /**
         * Get the MD5 hash of the file at the given path.
         *
         * @param string $path
         * @return string
         * @static
         */
        public static function hash($path)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->hash($path);
        }

        /**
         * Write the contents of a file.
         *
         * @param string $path
         * @param string $contents
         * @param bool $lock
         * @return int|bool
         * @static
         */
        public static function put($path, $contents, $lock = false)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->put($path, $contents, $lock);
        }

        /**
         * Write the contents of a file, replacing it atomically if it already exists.
         *
         * @param string $path
         * @param string $content
         * @return void
         * @static
         */
        public static function replace($path, $content)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            $instance->replace($path, $content);
        }

        /**
         * Prepend to a file.
         *
         * @param string $path
         * @param string $data
         * @return int
         * @static
         */
        public static function prepend($path, $data)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->prepend($path, $data);
        }

        /**
         * Append to a file.
         *
         * @param string $path
         * @param string $data
         * @return int
         * @static
         */
        public static function append($path, $data)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->append($path, $data);
        }

        /**
         * Get or set UNIX mode of a file or directory.
         *
         * @param string $path
         * @param int|null $mode
         * @return mixed
         * @static
         */
        public static function chmod($path, $mode = null)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->chmod($path, $mode);
        }

        /**
         * Delete the file at a given path.
         *
         * @param string|array $paths
         * @return bool
         * @static
         */
        public static function delete($paths)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->delete($paths);
        }

        /**
         * Move a file to a new location.
         *
         * @param string $path
         * @param string $target
         * @return bool
         * @static
         */
        public static function move($path, $target)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->move($path, $target);
        }

        /**
         * Copy a file to a new location.
         *
         * @param string $path
         * @param string $target
         * @return bool
         * @static
         */
        public static function copy($path, $target)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->copy($path, $target);
        }

        /**
         * Create a hard link to the target file or directory.
         *
         * @param string $target
         * @param string $link
         * @return void
         * @static
         */
        public static function link($target, $link)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            $instance->link($target, $link);
        }

        /**
         * Extract the file name from a file path.
         *
         * @param string $path
         * @return string
         * @static
         */
        public static function name($path)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->name($path);
        }

        /**
         * Extract the trailing name component from a file path.
         *
         * @param string $path
         * @return string
         * @static
         */
        public static function basename($path)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->basename($path);
        }

        /**
         * Extract the parent directory from a file path.
         *
         * @param string $path
         * @return string
         * @static
         */
        public static function dirname($path)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->dirname($path);
        }

        /**
         * Extract the file extension from a file path.
         *
         * @param string $path
         * @return string
         * @static
         */
        public static function extension($path)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->extension($path);
        }

        /**
         * Get the file type of a given file.
         *
         * @param string $path
         * @return string
         * @static
         */
        public static function type($path)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->type($path);
        }

        /**
         * Get the mime-type of a given file.
         *
         * @param string $path
         * @return string|false
         * @static
         */
        public static function mimeType($path)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->mimeType($path);
        }

        /**
         * Get the file size of a given file.
         *
         * @param string $path
         * @return int
         * @static
         */
        public static function size($path)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->size($path);
        }

        /**
         * Get the file's last modification time.
         *
         * @param string $path
         * @return int
         * @static
         */
        public static function lastModified($path)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->lastModified($path);
        }

        /**
         * Determine if the given path is a directory.
         *
         * @param string $directory
         * @return bool
         * @static
         */
        public static function isDirectory($directory)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->isDirectory($directory);
        }

        /**
         * Determine if the given path is readable.
         *
         * @param string $path
         * @return bool
         * @static
         */
        public static function isReadable($path)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->isReadable($path);
        }

        /**
         * Determine if the given path is writable.
         *
         * @param string $path
         * @return bool
         * @static
         */
        public static function isWritable($path)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->isWritable($path);
        }

        /**
         * Determine if the given path is a file.
         *
         * @param string $file
         * @return bool
         * @static
         */
        public static function isFile($file)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->isFile($file);
        }

        /**
         * Find path names matching a given pattern.
         *
         * @param string $pattern
         * @param int $flags
         * @return array
         * @static
         */
        public static function glob($pattern, $flags = 0)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->glob($pattern, $flags);
        }

        /**
         * Get an array of all files in a directory.
         *
         * @param string $directory
         * @param bool $hidden
         * @return \Symfony\Component\Finder\SplFileInfo[]
         * @static
         */
        public static function files($directory, $hidden = false)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->files($directory, $hidden);
        }

        /**
         * Get all of the files from the given directory (recursive).
         *
         * @param string $directory
         * @param bool $hidden
         * @return \Symfony\Component\Finder\SplFileInfo[]
         * @static
         */
        public static function allFiles($directory, $hidden = false)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->allFiles($directory, $hidden);
        }

        /**
         * Get all of the directories within a given directory.
         *
         * @param string $directory
         * @return array
         * @static
         */
        public static function directories($directory)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->directories($directory);
        }

        /**
         * Create a directory.
         *
         * @param string $path
         * @param int $mode
         * @param bool $recursive
         * @param bool $force
         * @return bool
         * @static
         */
        public static function makeDirectory($path, $mode = 493, $recursive = false, $force = false)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->makeDirectory($path, $mode, $recursive, $force);
        }

        /**
         * Move a directory.
         *
         * @param string $from
         * @param string $to
         * @param bool $overwrite
         * @return bool
         * @static
         */
        public static function moveDirectory($from, $to, $overwrite = false)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->moveDirectory($from, $to, $overwrite);
        }

        /**
         * Copy a directory from one location to another.
         *
         * @param string $directory
         * @param string $destination
         * @param int|null $options
         * @return bool
         * @static
         */
        public static function copyDirectory($directory, $destination, $options = null)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->copyDirectory($directory, $destination, $options);
        }

        /**
         * Recursively delete a directory.
         *
         * The directory itself may be optionally preserved.
         *
         * @param string $directory
         * @param bool $preserve
         * @return bool
         * @static
         */
        public static function deleteDirectory($directory, $preserve = false)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->deleteDirectory($directory, $preserve);
        }

        /**
         * Remove all of the directories within a given directory.
         *
         * @param string $directory
         * @return bool
         * @static
         */
        public static function deleteDirectories($directory)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->deleteDirectories($directory);
        }

        /**
         * Empty the specified directory of all files and folders.
         *
         * @param string $directory
         * @return bool
         * @static
         */
        public static function cleanDirectory($directory)
        {
            /** @var \Illuminate\Filesystem\Filesystem $instance */
            return $instance->cleanDirectory($directory);
        }

        /**
         * Register a custom macro.
         *
         * @param string $name
         * @param object|callable $macro
         * @return void
         * @static
         */
        public static function macro($name, $macro)
        {
            \Illuminate\Filesystem\Filesystem::macro($name, $macro);
        }

        /**
         * Mix another object into the class.
         *
         * @param object $mixin
         * @param bool $replace
         * @return void
         * @throws \ReflectionException
         * @static
         */
        public static function mixin($mixin, $replace = true)
        {
            \Illuminate\Filesystem\Filesystem::mixin($mixin, $replace);
        }

        /**
         * Checks if macro is registered.
         *
         * @param string $name
         * @return bool
         * @static
         */
        public static function hasMacro($name)
        {
            return \Illuminate\Filesystem\Filesystem::hasMacro($name);
        }

    }

    /**
     *
     *
     * @see \Illuminate\Http\Request
     */
    class Request
    {

        /**
         * Create a new Illuminate HTTP request from server variables.
         *
         * @return \Illuminate\Support\Facades\Request
         * @static
         */
        public static function capture()
        {
            return \Illuminate\Http\Request::capture();
        }

        /**
         * Return the Request instance.
         *
         * @return \Illuminate\Http\Request
         * @static
         */
        public static function instance()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->instance();
        }

        /**
         * Get the request method.
         *
         * @return string
         * @static
         */
        public static function method()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->method();
        }

        /**
         * Get the root URL for the application.
         *
         * @return string
         * @static
         */
        public static function root()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->root();
        }

        /**
         * Get the URL (no query string) for the request.
         *
         * @return string
         * @static
         */
        public static function url()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->url();
        }

        /**
         * Get the full URL for the request.
         *
         * @return string
         * @static
         */
        public static function fullUrl()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->fullUrl();
        }

        /**
         * Get the full URL for the request with the added query string parameters.
         *
         * @param array $query
         * @return string
         * @static
         */
        public static function fullUrlWithQuery($query)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->fullUrlWithQuery($query);
        }

        /**
         * Get the current path info for the request.
         *
         * @return string
         * @static
         */
        public static function path()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->path();
        }

        /**
         * Get the current decoded path info for the request.
         *
         * @return string
         * @static
         */
        public static function decodedPath()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->decodedPath();
        }

        /**
         * Get a segment from the URI (1 based index).
         *
         * @param int $index
         * @param string|null $default
         * @return string|null
         * @static
         */
        public static function segment($index, $default = null)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->segment($index, $default);
        }

        /**
         * Get all of the segments for the request path.
         *
         * @return array
         * @static
         */
        public static function segments()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->segments();
        }

        /**
         * Determine if the current request URI matches a pattern.
         *
         * @param mixed $patterns
         * @return bool
         * @static
         */
        public static function is($patterns = null)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->is($patterns);
        }

        /**
         * Determine if the route name matches a given pattern.
         *
         * @param mixed $patterns
         * @return bool
         * @static
         */
        public static function routeIs($patterns = null)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->routeIs($patterns);
        }

        /**
         * Determine if the current request URL and query string matches a pattern.
         *
         * @param mixed $patterns
         * @return bool
         * @static
         */
        public static function fullUrlIs($patterns = null)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->fullUrlIs($patterns);
        }

        /**
         * Determine if the request is the result of an AJAX call.
         *
         * @return bool
         * @static
         */
        public static function ajax()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->ajax();
        }

        /**
         * Determine if the request is the result of an PJAX call.
         *
         * @return bool
         * @static
         */
        public static function pjax()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->pjax();
        }

        /**
         * Determine if the request is the result of an prefetch call.
         *
         * @return bool
         * @static
         */
        public static function prefetch()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->prefetch();
        }

        /**
         * Determine if the request is over HTTPS.
         *
         * @return bool
         * @static
         */
        public static function secure()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->secure();
        }

        /**
         * Get the client IP address.
         *
         * @return string|null
         * @static
         */
        public static function ip()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->ip();
        }

        /**
         * Get the client IP addresses.
         *
         * @return array
         * @static
         */
        public static function ips()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->ips();
        }

        /**
         * Get the client user agent.
         *
         * @return string
         * @static
         */
        public static function userAgent()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->userAgent();
        }

        /**
         * Merge new input into the current request's input array.
         *
         * @param array $input
         * @return \Illuminate\Http\Request
         * @static
         */
        public static function merge($input)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->merge($input);
        }

        /**
         * Replace the input for the current request.
         *
         * @param array $input
         * @return \Illuminate\Http\Request
         * @static
         */
        public static function replace($input)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->replace($input);
        }

        /**
         * This method belongs to Symfony HttpFoundation and is not usually needed when using Laravel.
         *
         * Instead, you may use the "input" method.
         *
         * @param string $key
         * @param mixed $default
         * @return mixed
         * @static
         */
        public static function get($key, $default = null)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->get($key, $default);
        }

        /**
         * Get the JSON payload for the request.
         *
         * @param string|null $key
         * @param mixed $default
         * @return \Symfony\Component\HttpFoundation\ParameterBag|mixed
         * @static
         */
        public static function json($key = null, $default = null)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->json($key, $default);
        }

        /**
         * Create a new request instance from the given Laravel request.
         *
         * @param \Illuminate\Http\Request $from
         * @param \Illuminate\Http\Request|null $to
         * @return static
         * @static
         */
        public static function createFrom($from, $to = null)
        {
            return \Illuminate\Http\Request::createFrom($from, $to);
        }

        /**
         * Create an Illuminate request from a Symfony instance.
         *
         * @param \Symfony\Component\HttpFoundation\Request $request
         * @return static
         * @static
         */
        public static function createFromBase($request)
        {
            return \Illuminate\Http\Request::createFromBase($request);
        }

        /**
         * Clones a request and overrides some of its parameters.
         *
         * @param array $query The GET parameters
         * @param array $request The POST parameters
         * @param array $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
         * @param array $cookies The COOKIE parameters
         * @param array $files The FILES parameters
         * @param array $server The SERVER parameters
         * @return static
         * @static
         */
        public static function duplicate(
            $query = null,
            $request = null,
            $attributes = null,
            $cookies = null,
            $files = null,
            $server = null
        ) {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->duplicate($query, $request, $attributes, $cookies, $files, $server);
        }

        /**
         * Get the session associated with the request.
         *
         * @return \Illuminate\Session\Store
         * @throws \RuntimeException
         * @static
         */
        public static function session()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->session();
        }

        /**
         * Get the session associated with the request.
         *
         * @return \Illuminate\Session\Store|null
         * @static
         */
        public static function getSession()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getSession();
        }

        /**
         * Set the session instance on the request.
         *
         * @param \Illuminate\Contracts\Session\Session $session
         * @return void
         * @static
         */
        public static function setLaravelSession($session)
        {
            /** @var \Illuminate\Http\Request $instance */
            $instance->setLaravelSession($session);
        }

        /**
         * Get the user making the request.
         *
         * @param string|null $guard
         * @return mixed
         * @static
         */
        public static function user($guard = null)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->user($guard);
        }

        /**
         * Get the route handling the request.
         *
         * @param string|null $param
         * @param mixed $default
         * @return \Illuminate\Routing\Route|object|string
         * @static
         */
        public static function route($param = null, $default = null)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->route($param, $default);
        }

        /**
         * Get a unique fingerprint for the request / route / IP address.
         *
         * @return string
         * @throws \RuntimeException
         * @static
         */
        public static function fingerprint()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->fingerprint();
        }

        /**
         * Set the JSON payload for the request.
         *
         * @param \Symfony\Component\HttpFoundation\ParameterBag $json
         * @return \Illuminate\Http\Request
         * @static
         */
        public static function setJson($json)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->setJson($json);
        }

        /**
         * Get the user resolver callback.
         *
         * @return \Closure
         * @static
         */
        public static function getUserResolver()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getUserResolver();
        }

        /**
         * Set the user resolver callback.
         *
         * @param \Closure $callback
         * @return \Illuminate\Http\Request
         * @static
         */
        public static function setUserResolver($callback)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->setUserResolver($callback);
        }

        /**
         * Get the route resolver callback.
         *
         * @return \Closure
         * @static
         */
        public static function getRouteResolver()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getRouteResolver();
        }

        /**
         * Set the route resolver callback.
         *
         * @param \Closure $callback
         * @return \Illuminate\Http\Request
         * @static
         */
        public static function setRouteResolver($callback)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->setRouteResolver($callback);
        }

        /**
         * Get all of the input and files for the request.
         *
         * @return array
         * @static
         */
        public static function toArray()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->toArray();
        }

        /**
         * Determine if the given offset exists.
         *
         * @param string $offset
         * @return bool
         * @static
         */
        public static function offsetExists($offset)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->offsetExists($offset);
        }

        /**
         * Get the value at the given offset.
         *
         * @param string $offset
         * @return mixed
         * @static
         */
        public static function offsetGet($offset)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->offsetGet($offset);
        }

        /**
         * Set the value at the given offset.
         *
         * @param string $offset
         * @param mixed $value
         * @return void
         * @static
         */
        public static function offsetSet($offset, $value)
        {
            /** @var \Illuminate\Http\Request $instance */
            $instance->offsetSet($offset, $value);
        }

        /**
         * Remove the value at the given offset.
         *
         * @param string $offset
         * @return void
         * @static
         */
        public static function offsetUnset($offset)
        {
            /** @var \Illuminate\Http\Request $instance */
            $instance->offsetUnset($offset);
        }

        /**
         * Sets the parameters for this request.
         *
         * This method also re-initializes all properties.
         *
         * @param array $query The GET parameters
         * @param array $request The POST parameters
         * @param array $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
         * @param array $cookies The COOKIE parameters
         * @param array $files The FILES parameters
         * @param array $server The SERVER parameters
         * @param string|resource|null $content The raw body data
         * @static
         */
        public static function initialize(
            $query = array(),
            $request = array(),
            $attributes = array(),
            $cookies = array(),
            $files = array(),
            $server = array(),
            $content = null
        ) {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->initialize($query, $request, $attributes, $cookies, $files, $server, $content);
        }

        /**
         * Creates a new request with values from PHP's super globals.
         *
         * @return static
         * @static
         */
        public static function createFromGlobals()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            return \Illuminate\Http\Request::createFromGlobals();
        }

        /**
         * Creates a Request based on a given URI and configuration.
         *
         * The information contained in the URI always take precedence
         * over the other information (server and parameters).
         *
         * @param string $uri The URI
         * @param string $method The HTTP method
         * @param array $parameters The query (GET) or request (POST) parameters
         * @param array $cookies The request cookies ($_COOKIE)
         * @param array $files The request files ($_FILES)
         * @param array $server The server parameters ($_SERVER)
         * @param string|resource|null $content The raw body data
         * @return static
         * @static
         */
        public static function create(
            $uri,
            $method = 'GET',
            $parameters = array(),
            $cookies = array(),
            $files = array(),
            $server = array(),
            $content = null
        ) {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            return \Illuminate\Http\Request::create($uri, $method, $parameters, $cookies, $files, $server, $content);
        }

        /**
         * Sets a callable able to create a Request instance.
         *
         * This is mainly useful when you need to override the Request class
         * to keep BC with an existing system. It should not be used for any
         * other purpose.
         *
         * @param callable|null $callable A PHP callable
         * @static
         */
        public static function setFactory($callable)
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            return \Illuminate\Http\Request::setFactory($callable);
        }

        /**
         * Overrides the PHP global variables according to this request instance.
         *
         * It overrides $_GET, $_POST, $_REQUEST, $_SERVER, $_COOKIE.
         * $_FILES is never overridden, see rfc1867
         *
         * @static
         */
        public static function overrideGlobals()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->overrideGlobals();
        }

        /**
         * Sets a list of trusted proxies.
         *
         * You should only list the reverse proxies that you manage directly.
         *
         * @param array $proxies A list of trusted proxies
         * @param int $trustedHeaderSet A bit field of Request::HEADER_*, to set which headers to trust from your proxies
         * @throws \InvalidArgumentException When $trustedHeaderSet is invalid
         * @static
         */
        public static function setTrustedProxies($proxies, $trustedHeaderSet)
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            return \Illuminate\Http\Request::setTrustedProxies($proxies, $trustedHeaderSet);
        }

        /**
         * Gets the list of trusted proxies.
         *
         * @return array An array of trusted proxies
         * @static
         */
        public static function getTrustedProxies()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            return \Illuminate\Http\Request::getTrustedProxies();
        }

        /**
         * Gets the set of trusted headers from trusted proxies.
         *
         * @return int A bit field of Request::HEADER_* that defines which headers are trusted from your proxies
         * @static
         */
        public static function getTrustedHeaderSet()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            return \Illuminate\Http\Request::getTrustedHeaderSet();
        }

        /**
         * Sets a list of trusted host patterns.
         *
         * You should only list the hosts you manage using regexs.
         *
         * @param array $hostPatterns A list of trusted host patterns
         * @static
         */
        public static function setTrustedHosts($hostPatterns)
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            return \Illuminate\Http\Request::setTrustedHosts($hostPatterns);
        }

        /**
         * Gets the list of trusted host patterns.
         *
         * @return array An array of trusted host patterns
         * @static
         */
        public static function getTrustedHosts()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            return \Illuminate\Http\Request::getTrustedHosts();
        }

        /**
         * Normalizes a query string.
         *
         * It builds a normalized query string, where keys/value pairs are alphabetized,
         * have consistent escaping and unneeded delimiters are removed.
         *
         * @param string $qs Query string
         * @return string A normalized query string for the Request
         * @static
         */
        public static function normalizeQueryString($qs)
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            return \Illuminate\Http\Request::normalizeQueryString($qs);
        }

        /**
         * Enables support for the _method request parameter to determine the intended HTTP method.
         *
         * Be warned that enabling this feature might lead to CSRF issues in your code.
         * Check that you are using CSRF tokens when required.
         * If the HTTP method parameter override is enabled, an html-form with method "POST" can be altered
         * and used to send a "PUT" or "DELETE" request via the _method request parameter.
         * If these methods are not protected against CSRF, this presents a possible vulnerability.
         *
         * The HTTP method can only be overridden when the real HTTP method is POST.
         *
         * @static
         */
        public static function enableHttpMethodParameterOverride()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            return \Illuminate\Http\Request::enableHttpMethodParameterOverride();
        }

        /**
         * Checks whether support for the _method request parameter is enabled.
         *
         * @return bool True when the _method request parameter is enabled, false otherwise
         * @static
         */
        public static function getHttpMethodParameterOverride()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            return \Illuminate\Http\Request::getHttpMethodParameterOverride();
        }

        /**
         * Whether the request contains a Session which was started in one of the
         * previous requests.
         *
         * @return bool
         * @static
         */
        public static function hasPreviousSession()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->hasPreviousSession();
        }

        /**
         * Whether the request contains a Session object.
         *
         * This method does not give any information about the state of the session object,
         * like whether the session is started or not. It is just a way to check if this Request
         * is associated with a Session instance.
         *
         * @return bool true when the Request contains a Session object, false otherwise
         * @static
         */
        public static function hasSession()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->hasSession();
        }

        /**
         * Sets the Session.
         *
         * @param \Symfony\Component\HttpFoundation\SessionInterface $session The Session
         * @static
         */
        public static function setSession($session)
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->setSession($session);
        }

        /**
         *
         *
         * @internal
         * @static
         */
        public static function setSessionFactory($factory)
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->setSessionFactory($factory);
        }

        /**
         * Returns the client IP addresses.
         *
         * In the returned array the most trusted IP address is first, and the
         * least trusted one last. The "real" client IP address is the last one,
         * but this is also the least trusted one. Trusted proxies are stripped.
         *
         * Use this method carefully; you should use getClientIp() instead.
         *
         * @return array The client IP addresses
         * @see getClientIp()
         * @static
         */
        public static function getClientIps()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getClientIps();
        }

        /**
         * Returns the client IP address.
         *
         * This method can read the client IP address from the "X-Forwarded-For" header
         * when trusted proxies were set via "setTrustedProxies()". The "X-Forwarded-For"
         * header value is a comma+space separated list of IP addresses, the left-most
         * being the original client, and each successive proxy that passed the request
         * adding the IP address where it received the request from.
         *
         * If your reverse proxy uses a different header name than "X-Forwarded-For",
         * ("Client-Ip" for instance), configure it via the $trustedHeaderSet
         * argument of the Request::setTrustedProxies() method instead.
         *
         * @return string|null The client IP address
         * @see getClientIps()
         * @see https://wikipedia.org/wiki/X-Forwarded-For
         * @static
         */
        public static function getClientIp()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getClientIp();
        }

        /**
         * Returns current script name.
         *
         * @return string
         * @static
         */
        public static function getScriptName()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getScriptName();
        }

        /**
         * Returns the path being requested relative to the executed script.
         *
         * The path info always starts with a /.
         *
         * Suppose this request is instantiated from /mysite on localhost:
         *
         *  * http://localhost/mysite              returns an empty string
         *  * http://localhost/mysite/about        returns '/about'
         *  * http://localhost/mysite/enco%20ded   returns '/enco%20ded'
         *  * http://localhost/mysite/about?var=1  returns '/about'
         *
         * @return string The raw path (i.e. not urldecoded)
         * @static
         */
        public static function getPathInfo()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getPathInfo();
        }

        /**
         * Returns the root path from which this request is executed.
         *
         * Suppose that an index.php file instantiates this request object:
         *
         *  * http://localhost/index.php         returns an empty string
         *  * http://localhost/index.php/page    returns an empty string
         *  * http://localhost/web/index.php     returns '/web'
         *  * http://localhost/we%20b/index.php  returns '/we%20b'
         *
         * @return string The raw path (i.e. not urldecoded)
         * @static
         */
        public static function getBasePath()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getBasePath();
        }

        /**
         * Returns the root URL from which this request is executed.
         *
         * The base URL never ends with a /.
         *
         * This is similar to getBasePath(), except that it also includes the
         * script filename (e.g. index.php) if one exists.
         *
         * @return string The raw URL (i.e. not urldecoded)
         * @static
         */
        public static function getBaseUrl()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getBaseUrl();
        }

        /**
         * Gets the request's scheme.
         *
         * @return string
         * @static
         */
        public static function getScheme()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getScheme();
        }

        /**
         * Returns the port on which the request is made.
         *
         * This method can read the client port from the "X-Forwarded-Port" header
         * when trusted proxies were set via "setTrustedProxies()".
         *
         * The "X-Forwarded-Port" header must contain the client port.
         *
         * @return int|string can be a string if fetched from the server bag
         * @static
         */
        public static function getPort()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getPort();
        }

        /**
         * Returns the user.
         *
         * @return string|null
         * @static
         */
        public static function getUser()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getUser();
        }

        /**
         * Returns the password.
         *
         * @return string|null
         * @static
         */
        public static function getPassword()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getPassword();
        }

        /**
         * Gets the user info.
         *
         * @return string A user name and, optionally, scheme-specific information about how to gain authorization to access the server
         * @static
         */
        public static function getUserInfo()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getUserInfo();
        }

        /**
         * Returns the HTTP host being requested.
         *
         * The port name will be appended to the host if it's non-standard.
         *
         * @return string
         * @static
         */
        public static function getHttpHost()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getHttpHost();
        }

        /**
         * Returns the requested URI (path and query string).
         *
         * @return string The raw URI (i.e. not URI decoded)
         * @static
         */
        public static function getRequestUri()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getRequestUri();
        }

        /**
         * Gets the scheme and HTTP host.
         *
         * If the URL was called with basic authentication, the user
         * and the password are not added to the generated string.
         *
         * @return string The scheme and HTTP host
         * @static
         */
        public static function getSchemeAndHttpHost()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getSchemeAndHttpHost();
        }

        /**
         * Generates a normalized URI (URL) for the Request.
         *
         * @return string A normalized URI (URL) for the Request
         * @see getQueryString()
         * @static
         */
        public static function getUri()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getUri();
        }

        /**
         * Generates a normalized URI for the given path.
         *
         * @param string $path A path to use instead of the current one
         * @return string The normalized URI for the path
         * @static
         */
        public static function getUriForPath($path)
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getUriForPath($path);
        }

        /**
         * Returns the path as relative reference from the current Request path.
         *
         * Only the URIs path component (no schema, host etc.) is relevant and must be given.
         * Both paths must be absolute and not contain relative parts.
         * Relative URLs from one resource to another are useful when generating self-contained downloadable document archives.
         * Furthermore, they can be used to reduce the link size in documents.
         *
         * Example target paths, given a base path of "/a/b/c/d":
         * - "/a/b/c/d"     -> ""
         * - "/a/b/c/"      -> "./"
         * - "/a/b/"        -> "../"
         * - "/a/b/c/other" -> "other"
         * - "/a/x/y"       -> "../../x/y"
         *
         * @param string $path The target path
         * @return string The relative target path
         * @static
         */
        public static function getRelativeUriForPath($path)
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getRelativeUriForPath($path);
        }

        /**
         * Generates the normalized query string for the Request.
         *
         * It builds a normalized query string, where keys/value pairs are alphabetized
         * and have consistent escaping.
         *
         * @return string|null A normalized query string for the Request
         * @static
         */
        public static function getQueryString()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getQueryString();
        }

        /**
         * Checks whether the request is secure or not.
         *
         * This method can read the client protocol from the "X-Forwarded-Proto" header
         * when trusted proxies were set via "setTrustedProxies()".
         *
         * The "X-Forwarded-Proto" header must contain the protocol: "https" or "http".
         *
         * @return bool
         * @static
         */
        public static function isSecure()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->isSecure();
        }

        /**
         * Returns the host name.
         *
         * This method can read the client host name from the "X-Forwarded-Host" header
         * when trusted proxies were set via "setTrustedProxies()".
         *
         * The "X-Forwarded-Host" header must contain the client host name.
         *
         * @return string
         * @throws SuspiciousOperationException when the host name is invalid or not trusted
         * @static
         */
        public static function getHost()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getHost();
        }

        /**
         * Sets the request method.
         *
         * @param string $method
         * @static
         */
        public static function setMethod($method)
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->setMethod($method);
        }

        /**
         * Gets the request "intended" method.
         *
         * If the X-HTTP-Method-Override header is set, and if the method is a POST,
         * then it is used to determine the "real" intended HTTP method.
         *
         * The _method request parameter can also be used to determine the HTTP method,
         * but only if enableHttpMethodParameterOverride() has been called.
         *
         * The method is always an uppercased string.
         *
         * @return string The request method
         * @see getRealMethod()
         * @static
         */
        public static function getMethod()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getMethod();
        }

        /**
         * Gets the "real" request method.
         *
         * @return string The request method
         * @see getMethod()
         * @static
         */
        public static function getRealMethod()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getRealMethod();
        }

        /**
         * Gets the mime type associated with the format.
         *
         * @param string $format The format
         * @return string|null The associated mime type (null if not found)
         * @static
         */
        public static function getMimeType($format)
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getMimeType($format);
        }

        /**
         * Gets the mime types associated with the format.
         *
         * @param string $format The format
         * @return array The associated mime types
         * @static
         */
        public static function getMimeTypes($format)
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            return \Illuminate\Http\Request::getMimeTypes($format);
        }

        /**
         * Gets the format associated with the mime type.
         *
         * @param string $mimeType The associated mime type
         * @return string|null The format (null if not found)
         * @static
         */
        public static function getFormat($mimeType)
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getFormat($mimeType);
        }

        /**
         * Associates a format with mime types.
         *
         * @param string $format The format
         * @param string|array $mimeTypes The associated mime types (the preferred one must be the first as it will be used as the content type)
         * @static
         */
        public static function setFormat($format, $mimeTypes)
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->setFormat($format, $mimeTypes);
        }

        /**
         * Gets the request format.
         *
         * Here is the process to determine the format:
         *
         *  * format defined by the user (with setRequestFormat())
         *  * _format request attribute
         *  * $default
         *
         * @param string|null $default The default format
         * @return string|null The request format
         * @static
         */
        public static function getRequestFormat($default = 'html')
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getRequestFormat($default);
        }

        /**
         * Sets the request format.
         *
         * @param string $format The request format
         * @static
         */
        public static function setRequestFormat($format)
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->setRequestFormat($format);
        }

        /**
         * Gets the format associated with the request.
         *
         * @return string|null The format (null if no content type is present)
         * @static
         */
        public static function getContentType()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getContentType();
        }

        /**
         * Sets the default locale.
         *
         * @param string $locale
         * @static
         */
        public static function setDefaultLocale($locale)
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->setDefaultLocale($locale);
        }

        /**
         * Get the default locale.
         *
         * @return string
         * @static
         */
        public static function getDefaultLocale()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getDefaultLocale();
        }

        /**
         * Sets the locale.
         *
         * @param string $locale
         * @static
         */
        public static function setLocale($locale)
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->setLocale($locale);
        }

        /**
         * Get the locale.
         *
         * @return string
         * @static
         */
        public static function getLocale()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getLocale();
        }

        /**
         * Checks if the request method is of specified type.
         *
         * @param string $method Uppercase request method (GET, POST etc)
         * @return bool
         * @static
         */
        public static function isMethod($method)
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->isMethod($method);
        }

        /**
         * Checks whether or not the method is safe.
         *
         * @see https://tools.ietf.org/html/rfc7231#section-4.2.1
         * @return bool
         * @static
         */
        public static function isMethodSafe()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->isMethodSafe();
        }

        /**
         * Checks whether or not the method is idempotent.
         *
         * @return bool
         * @static
         */
        public static function isMethodIdempotent()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->isMethodIdempotent();
        }

        /**
         * Checks whether the method is cacheable or not.
         *
         * @see https://tools.ietf.org/html/rfc7231#section-4.2.3
         * @return bool True for GET and HEAD, false otherwise
         * @static
         */
        public static function isMethodCacheable()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->isMethodCacheable();
        }

        /**
         * Returns the protocol version.
         *
         * If the application is behind a proxy, the protocol version used in the
         * requests between the client and the proxy and between the proxy and the
         * server might be different. This returns the former (from the "Via" header)
         * if the proxy is trusted (see "setTrustedProxies()"), otherwise it returns
         * the latter (from the "SERVER_PROTOCOL" server parameter).
         *
         * @return string
         * @static
         */
        public static function getProtocolVersion()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getProtocolVersion();
        }

        /**
         * Returns the request body content.
         *
         * @param bool $asResource If true, a resource will be returned
         * @return string|resource The request body content or a resource to read the body stream
         * @throws \LogicException
         * @static
         */
        public static function getContent($asResource = false)
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getContent($asResource);
        }

        /**
         * Gets the Etags.
         *
         * @return array The entity tags
         * @static
         */
        public static function getETags()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getETags();
        }

        /**
         *
         *
         * @return bool
         * @static
         */
        public static function isNoCache()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->isNoCache();
        }

        /**
         * Returns the preferred language.
         *
         * @param array $locales An array of ordered available locales
         * @return string|null The preferred locale
         * @static
         */
        public static function getPreferredLanguage($locales = null)
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getPreferredLanguage($locales);
        }

        /**
         * Gets a list of languages acceptable by the client browser.
         *
         * @return array Languages ordered in the user browser preferences
         * @static
         */
        public static function getLanguages()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getLanguages();
        }

        /**
         * Gets a list of charsets acceptable by the client browser.
         *
         * @return array List of charsets in preferable order
         * @static
         */
        public static function getCharsets()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getCharsets();
        }

        /**
         * Gets a list of encodings acceptable by the client browser.
         *
         * @return array List of encodings in preferable order
         * @static
         */
        public static function getEncodings()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getEncodings();
        }

        /**
         * Gets a list of content types acceptable by the client browser.
         *
         * @return array List of content types in preferable order
         * @static
         */
        public static function getAcceptableContentTypes()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->getAcceptableContentTypes();
        }

        /**
         * Returns true if the request is a XMLHttpRequest.
         *
         * It works if your JavaScript library sets an X-Requested-With HTTP header.
         * It is known to work with common JavaScript frameworks:
         *
         * @see https://wikipedia.org/wiki/List_of_Ajax_frameworks#JavaScript
         * @return bool true if the request is an XMLHttpRequest, false otherwise
         * @static
         */
        public static function isXmlHttpRequest()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->isXmlHttpRequest();
        }

        /**
         * Indicates whether this request originated from a trusted proxy.
         *
         * This can be useful to determine whether or not to trust the
         * contents of a proxy-specific header.
         *
         * @return bool true if the request came from a trusted proxy, false otherwise
         * @static
         */
        public static function isFromTrustedProxy()
        {
            //Method inherited from \Symfony\Component\HttpFoundation\Request
            /** @var \Illuminate\Http\Request $instance */
            return $instance->isFromTrustedProxy();
        }

        /**
         * Determine if the given content types match.
         *
         * @param string $actual
         * @param string $type
         * @return bool
         * @static
         */
        public static function matchesType($actual, $type)
        {
            return \Illuminate\Http\Request::matchesType($actual, $type);
        }

        /**
         * Determine if the request is sending JSON.
         *
         * @return bool
         * @static
         */
        public static function isJson()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->isJson();
        }

        /**
         * Determine if the current request probably expects a JSON response.
         *
         * @return bool
         * @static
         */
        public static function expectsJson()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->expectsJson();
        }

        /**
         * Determine if the current request is asking for JSON.
         *
         * @return bool
         * @static
         */
        public static function wantsJson()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->wantsJson();
        }

        /**
         * Determines whether the current requests accepts a given content type.
         *
         * @param string|array $contentTypes
         * @return bool
         * @static
         */
        public static function accepts($contentTypes)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->accepts($contentTypes);
        }

        /**
         * Return the most suitable content type from the given array based on content negotiation.
         *
         * @param string|array $contentTypes
         * @return string|null
         * @static
         */
        public static function prefers($contentTypes)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->prefers($contentTypes);
        }

        /**
         * Determine if the current request accepts any content type.
         *
         * @return bool
         * @static
         */
        public static function acceptsAnyContentType()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->acceptsAnyContentType();
        }

        /**
         * Determines whether a request accepts JSON.
         *
         * @return bool
         * @static
         */
        public static function acceptsJson()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->acceptsJson();
        }

        /**
         * Determines whether a request accepts HTML.
         *
         * @return bool
         * @static
         */
        public static function acceptsHtml()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->acceptsHtml();
        }

        /**
         * Get the data format expected in the response.
         *
         * @param string $default
         * @return string
         * @static
         */
        public static function format($default = 'html')
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->format($default);
        }

        /**
         * Retrieve an old input item.
         *
         * @param string|null $key
         * @param string|array|null $default
         * @return string|array
         * @static
         */
        public static function old($key = null, $default = null)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->old($key, $default);
        }

        /**
         * Flash the input for the current request to the session.
         *
         * @return void
         * @static
         */
        public static function flash()
        {
            /** @var \Illuminate\Http\Request $instance */
            $instance->flash();
        }

        /**
         * Flash only some of the input to the session.
         *
         * @param array|mixed $keys
         * @return void
         * @static
         */
        public static function flashOnly($keys)
        {
            /** @var \Illuminate\Http\Request $instance */
            $instance->flashOnly($keys);
        }

        /**
         * Flash only some of the input to the session.
         *
         * @param array|mixed $keys
         * @return void
         * @static
         */
        public static function flashExcept($keys)
        {
            /** @var \Illuminate\Http\Request $instance */
            $instance->flashExcept($keys);
        }

        /**
         * Flush all of the old input from the session.
         *
         * @return void
         * @static
         */
        public static function flush()
        {
            /** @var \Illuminate\Http\Request $instance */
            $instance->flush();
        }

        /**
         * Retrieve a server variable from the request.
         *
         * @param string|null $key
         * @param string|array|null $default
         * @return string|array|null
         * @static
         */
        public static function server($key = null, $default = null)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->server($key, $default);
        }

        /**
         * Determine if a header is set on the request.
         *
         * @param string $key
         * @return bool
         * @static
         */
        public static function hasHeader($key)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->hasHeader($key);
        }

        /**
         * Retrieve a header from the request.
         *
         * @param string|null $key
         * @param string|array|null $default
         * @return string|array|null
         * @static
         */
        public static function header($key = null, $default = null)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->header($key, $default);
        }

        /**
         * Get the bearer token from the request headers.
         *
         * @return string|null
         * @static
         */
        public static function bearerToken()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->bearerToken();
        }

        /**
         * Determine if the request contains a given input item key.
         *
         * @param string|array $key
         * @return bool
         * @static
         */
        public static function exists($key)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->exists($key);
        }

        /**
         * Determine if the request contains a given input item key.
         *
         * @param string|array $key
         * @return bool
         * @static
         */
        public static function has($key)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->has($key);
        }

        /**
         * Determine if the request contains any of the given inputs.
         *
         * @param string|array $keys
         * @return bool
         * @static
         */
        public static function hasAny($keys)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->hasAny($keys);
        }

        /**
         * Determine if the request contains a non-empty value for an input item.
         *
         * @param string|array $key
         * @return bool
         * @static
         */
        public static function filled($key)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->filled($key);
        }

        /**
         * Determine if the request contains a non-empty value for any of the given inputs.
         *
         * @param string|array $keys
         * @return bool
         * @static
         */
        public static function anyFilled($keys)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->anyFilled($keys);
        }

        /**
         * Get the keys for all of the input and files.
         *
         * @return array
         * @static
         */
        public static function keys()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->keys();
        }

        /**
         * Get all of the input and files for the request.
         *
         * @param array|mixed|null $keys
         * @return array
         * @static
         */
        public static function all($keys = null)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->all($keys);
        }

        /**
         * Retrieve an input item from the request.
         *
         * @param string|null $key
         * @param string|array|null $default
         * @return string|array|null
         * @static
         */
        public static function input($key = null, $default = null)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->input($key, $default);
        }

        /**
         * Get a subset containing the provided keys with values from the input data.
         *
         * @param array|mixed $keys
         * @return array
         * @static
         */
        public static function only($keys)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->only($keys);
        }

        /**
         * Get all of the input except for a specified array of items.
         *
         * @param array|mixed $keys
         * @return array
         * @static
         */
        public static function except($keys)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->except($keys);
        }

        /**
         * Retrieve a query string item from the request.
         *
         * @param string|null $key
         * @param string|array|null $default
         * @return string|array|null
         * @static
         */
        public static function query($key = null, $default = null)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->query($key, $default);
        }

        /**
         * Retrieve a request payload item from the request.
         *
         * @param string|null $key
         * @param string|array|null $default
         * @return string|array|null
         * @static
         */
        public static function post($key = null, $default = null)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->post($key, $default);
        }

        /**
         * Determine if a cookie is set on the request.
         *
         * @param string $key
         * @return bool
         * @static
         */
        public static function hasCookie($key)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->hasCookie($key);
        }

        /**
         * Retrieve a cookie from the request.
         *
         * @param string|null $key
         * @param string|array|null $default
         * @return string|array|null
         * @static
         */
        public static function cookie($key = null, $default = null)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->cookie($key, $default);
        }

        /**
         * Get an array of all of the files on the request.
         *
         * @return array
         * @static
         */
        public static function allFiles()
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->allFiles();
        }

        /**
         * Determine if the uploaded data contains a file.
         *
         * @param string $key
         * @return bool
         * @static
         */
        public static function hasFile($key)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->hasFile($key);
        }

        /**
         * Retrieve a file from the request.
         *
         * @param string|null $key
         * @param mixed $default
         * @return \Illuminate\Http\UploadedFile|\Illuminate\Http\UploadedFile[]|array|null
         * @static
         */
        public static function file($key = null, $default = null)
        {
            /** @var \Illuminate\Http\Request $instance */
            return $instance->file($key, $default);
        }

        /**
         * Register a custom macro.
         *
         * @param string $name
         * @param object|callable $macro
         * @return void
         * @static
         */
        public static function macro($name, $macro)
        {
            \Illuminate\Http\Request::macro($name, $macro);
        }

        /**
         * Mix another object into the class.
         *
         * @param object $mixin
         * @param bool $replace
         * @return void
         * @throws \ReflectionException
         * @static
         */
        public static function mixin($mixin, $replace = true)
        {
            \Illuminate\Http\Request::mixin($mixin, $replace);
        }

        /**
         * Checks if macro is registered.
         *
         * @param string $name
         * @return bool
         * @static
         */
        public static function hasMacro($name)
        {
            return \Illuminate\Http\Request::hasMacro($name);
        }

        /**
         *
         *
         * @static
         */
        public static function validate($rules, $params = null)
        {
            return \Illuminate\Http\Request::validate($rules, $params);
        }

        /**
         *
         *
         * @static
         */
        public static function hasValidSignature($absolute = true)
        {
            return \Illuminate\Http\Request::hasValidSignature($absolute);
        }

    }

    /**
     *
     *
     * @see \Illuminate\Contracts\Foundation\Application
     */
    class App
    {

        /**
         * Get the version number of the application.
         *
         * @return string
         * @static
         */
        public static function version()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->version();
        }

        /**
         * Run the given array of bootstrap classes.
         *
         * @param string[] $bootstrappers
         * @return void
         * @static
         */
        public static function bootstrapWith($bootstrappers)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->bootstrapWith($bootstrappers);
        }

        /**
         * Register a callback to run after loading the environment.
         *
         * @param \Closure $callback
         * @return void
         * @static
         */
        public static function afterLoadingEnvironment($callback)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->afterLoadingEnvironment($callback);
        }

        /**
         * Register a callback to run before a bootstrapper.
         *
         * @param string $bootstrapper
         * @param \Closure $callback
         * @return void
         * @static
         */
        public static function beforeBootstrapping($bootstrapper, $callback)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->beforeBootstrapping($bootstrapper, $callback);
        }

        /**
         * Register a callback to run after a bootstrapper.
         *
         * @param string $bootstrapper
         * @param \Closure $callback
         * @return void
         * @static
         */
        public static function afterBootstrapping($bootstrapper, $callback)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->afterBootstrapping($bootstrapper, $callback);
        }

        /**
         * Determine if the application has been bootstrapped before.
         *
         * @return bool
         * @static
         */
        public static function hasBeenBootstrapped()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->hasBeenBootstrapped();
        }

        /**
         * Set the base path for the application.
         *
         * @param string $basePath
         * @return \Illuminate\Foundation\Application
         * @static
         */
        public static function setBasePath($basePath)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->setBasePath($basePath);
        }

        /**
         * Get the path to the application "app" directory.
         *
         * @param string $path
         * @return string
         * @static
         */
        public static function path($path = '')
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->path($path);
        }

        /**
         * Set the application directory.
         *
         * @param string $path
         * @return \Illuminate\Foundation\Application
         * @static
         */
        public static function useAppPath($path)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->useAppPath($path);
        }

        /**
         * Get the base path of the Laravel installation.
         *
         * @param string $path Optionally, a path to append to the base path
         * @return string
         * @static
         */
        public static function basePath($path = '')
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->basePath($path);
        }

        /**
         * Get the path to the bootstrap directory.
         *
         * @param string $path Optionally, a path to append to the bootstrap path
         * @return string
         * @static
         */
        public static function bootstrapPath($path = '')
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->bootstrapPath($path);
        }

        /**
         * Get the path to the application configuration files.
         *
         * @param string $path Optionally, a path to append to the config path
         * @return string
         * @static
         */
        public static function configPath($path = '')
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->configPath($path);
        }

        /**
         * Get the path to the database directory.
         *
         * @param string $path Optionally, a path to append to the database path
         * @return string
         * @static
         */
        public static function databasePath($path = '')
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->databasePath($path);
        }

        /**
         * Set the database directory.
         *
         * @param string $path
         * @return \Illuminate\Foundation\Application
         * @static
         */
        public static function useDatabasePath($path)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->useDatabasePath($path);
        }

        /**
         * Get the path to the language files.
         *
         * @return string
         * @static
         */
        public static function langPath()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->langPath();
        }

        /**
         * Get the path to the public / web directory.
         *
         * @return string
         * @static
         */
        public static function publicPath()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->publicPath();
        }

        /**
         * Get the path to the storage directory.
         *
         * @return string
         * @static
         */
        public static function storagePath()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->storagePath();
        }

        /**
         * Set the storage directory.
         *
         * @param string $path
         * @return \Illuminate\Foundation\Application
         * @static
         */
        public static function useStoragePath($path)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->useStoragePath($path);
        }

        /**
         * Get the path to the resources directory.
         *
         * @param string $path
         * @return string
         * @static
         */
        public static function resourcePath($path = '')
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->resourcePath($path);
        }

        /**
         * Get the path to the environment file directory.
         *
         * @return string
         * @static
         */
        public static function environmentPath()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->environmentPath();
        }

        /**
         * Set the directory for the environment file.
         *
         * @param string $path
         * @return \Illuminate\Foundation\Application
         * @static
         */
        public static function useEnvironmentPath($path)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->useEnvironmentPath($path);
        }

        /**
         * Set the environment file to be loaded during bootstrapping.
         *
         * @param string $file
         * @return \Illuminate\Foundation\Application
         * @static
         */
        public static function loadEnvironmentFrom($file)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->loadEnvironmentFrom($file);
        }

        /**
         * Get the environment file the application is using.
         *
         * @return string
         * @static
         */
        public static function environmentFile()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->environmentFile();
        }

        /**
         * Get the fully qualified path to the environment file.
         *
         * @return string
         * @static
         */
        public static function environmentFilePath()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->environmentFilePath();
        }

        /**
         * Get or check the current application environment.
         *
         * @param string|array $environments
         * @return string|bool
         * @static
         */
        public static function environment($environments = null)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->environment($environments);
        }

        /**
         * Determine if application is in local environment.
         *
         * @return bool
         * @static
         */
        public static function isLocal()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->isLocal();
        }

        /**
         * Determine if application is in production environment.
         *
         * @return bool
         * @static
         */
        public static function isProduction()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->isProduction();
        }

        /**
         * Detect the application's current environment.
         *
         * @param \Closure $callback
         * @return string
         * @static
         */
        public static function detectEnvironment($callback)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->detectEnvironment($callback);
        }

        /**
         * Determine if the application is running in the console.
         *
         * @return bool
         * @static
         */
        public static function runningInConsole()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->runningInConsole();
        }

        /**
         * Determine if the application is running unit tests.
         *
         * @return bool
         * @static
         */
        public static function runningUnitTests()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->runningUnitTests();
        }

        /**
         * Register all of the configured providers.
         *
         * @return void
         * @static
         */
        public static function registerConfiguredProviders()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->registerConfiguredProviders();
        }

        /**
         * Register a service provider with the application.
         *
         * @param \Illuminate\Support\ServiceProvider|string $provider
         * @param bool $force
         * @return \Illuminate\Support\ServiceProvider
         * @static
         */
        public static function register($provider, $force = false)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->register($provider, $force);
        }

        /**
         * Get the registered service provider instance if it exists.
         *
         * @param \Illuminate\Support\ServiceProvider|string $provider
         * @return \Illuminate\Support\ServiceProvider|null
         * @static
         */
        public static function getProvider($provider)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->getProvider($provider);
        }

        /**
         * Get the registered service provider instances if any exist.
         *
         * @param \Illuminate\Support\ServiceProvider|string $provider
         * @return array
         * @static
         */
        public static function getProviders($provider)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->getProviders($provider);
        }

        /**
         * Resolve a service provider instance from the class name.
         *
         * @param string $provider
         * @return \Illuminate\Support\ServiceProvider
         * @static
         */
        public static function resolveProvider($provider)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->resolveProvider($provider);
        }

        /**
         * Load and boot all of the remaining deferred providers.
         *
         * @return void
         * @static
         */
        public static function loadDeferredProviders()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->loadDeferredProviders();
        }

        /**
         * Load the provider for a deferred service.
         *
         * @param string $service
         * @return void
         * @static
         */
        public static function loadDeferredProvider($service)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->loadDeferredProvider($service);
        }

        /**
         * Register a deferred provider and service.
         *
         * @param string $provider
         * @param string|null $service
         * @return void
         * @static
         */
        public static function registerDeferredProvider($provider, $service = null)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->registerDeferredProvider($provider, $service);
        }

        /**
         * Resolve the given type from the container.
         *
         * (Overriding Container::make)
         *
         * @param string $abstract
         * @param array $parameters
         * @return mixed
         * @static
         */
        public static function make($abstract, $parameters = array())
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->make($abstract, $parameters);
        }

        /**
         * Determine if the given abstract type has been bound.
         *
         * (Overriding Container::bound)
         *
         * @param string $abstract
         * @return bool
         * @static
         */
        public static function bound($abstract)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->bound($abstract);
        }

        /**
         * Determine if the application has booted.
         *
         * @return bool
         * @static
         */
        public static function isBooted()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->isBooted();
        }

        /**
         * Boot the application's service providers.
         *
         * @return void
         * @static
         */
        public static function boot()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->boot();
        }

        /**
         * Register a new boot listener.
         *
         * @param callable $callback
         * @return void
         * @static
         */
        public static function booting($callback)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->booting($callback);
        }

        /**
         * Register a new "booted" listener.
         *
         * @param callable $callback
         * @return void
         * @static
         */
        public static function booted($callback)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->booted($callback);
        }

        /**
         * {@inheritdoc}
         *
         * @static
         */
        public static function handle($request, $type = 1, $catch = true)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->handle($request, $type, $catch);
        }

        /**
         * Determine if middleware has been disabled for the application.
         *
         * @return bool
         * @static
         */
        public static function shouldSkipMiddleware()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->shouldSkipMiddleware();
        }

        /**
         * Get the path to the cached services.php file.
         *
         * @return string
         * @static
         */
        public static function getCachedServicesPath()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->getCachedServicesPath();
        }

        /**
         * Get the path to the cached packages.php file.
         *
         * @return string
         * @static
         */
        public static function getCachedPackagesPath()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->getCachedPackagesPath();
        }

        /**
         * Determine if the application configuration is cached.
         *
         * @return bool
         * @static
         */
        public static function configurationIsCached()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->configurationIsCached();
        }

        /**
         * Get the path to the configuration cache file.
         *
         * @return string
         * @static
         */
        public static function getCachedConfigPath()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->getCachedConfigPath();
        }

        /**
         * Determine if the application routes are cached.
         *
         * @return bool
         * @static
         */
        public static function routesAreCached()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->routesAreCached();
        }

        /**
         * Get the path to the routes cache file.
         *
         * @return string
         * @static
         */
        public static function getCachedRoutesPath()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->getCachedRoutesPath();
        }

        /**
         * Determine if the application events are cached.
         *
         * @return bool
         * @static
         */
        public static function eventsAreCached()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->eventsAreCached();
        }

        /**
         * Get the path to the events cache file.
         *
         * @return string
         * @static
         */
        public static function getCachedEventsPath()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->getCachedEventsPath();
        }

        /**
         * Determine if the application is currently down for maintenance.
         *
         * @return bool
         * @static
         */
        public static function isDownForMaintenance()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->isDownForMaintenance();
        }

        /**
         * Throw an HttpException with the given data.
         *
         * @param int $code
         * @param string $message
         * @param array $headers
         * @return void
         * @throws \Symfony\Component\HttpKernel\Exception\HttpException
         * @static
         */
        public static function abort($code, $message = '', $headers = array())
        {
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->abort($code, $message, $headers);
        }

        /**
         * Register a terminating callback with the application.
         *
         * @param callable|string $callback
         * @return \Illuminate\Foundation\Application
         * @static
         */
        public static function terminating($callback)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->terminating($callback);
        }

        /**
         * Terminate the application.
         *
         * @return void
         * @static
         */
        public static function terminate()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->terminate();
        }

        /**
         * Get the service providers that have been loaded.
         *
         * @return array
         * @static
         */
        public static function getLoadedProviders()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->getLoadedProviders();
        }

        /**
         * Get the application's deferred services.
         *
         * @return array
         * @static
         */
        public static function getDeferredServices()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->getDeferredServices();
        }

        /**
         * Set the application's deferred services.
         *
         * @param array $services
         * @return void
         * @static
         */
        public static function setDeferredServices($services)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->setDeferredServices($services);
        }

        /**
         * Add an array of services to the application's deferred services.
         *
         * @param array $services
         * @return void
         * @static
         */
        public static function addDeferredServices($services)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->addDeferredServices($services);
        }

        /**
         * Determine if the given service is a deferred service.
         *
         * @param string $service
         * @return bool
         * @static
         */
        public static function isDeferredService($service)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->isDeferredService($service);
        }

        /**
         * Configure the real-time facade namespace.
         *
         * @param string $namespace
         * @return void
         * @static
         */
        public static function provideFacades($namespace)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->provideFacades($namespace);
        }

        /**
         * Get the current application locale.
         *
         * @return string
         * @static
         */
        public static function getLocale()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->getLocale();
        }

        /**
         * Set the current application locale.
         *
         * @param string $locale
         * @return void
         * @static
         */
        public static function setLocale($locale)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->setLocale($locale);
        }

        /**
         * Determine if application locale is the given locale.
         *
         * @param string $locale
         * @return bool
         * @static
         */
        public static function isLocale($locale)
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->isLocale($locale);
        }

        /**
         * Register the core class aliases in the container.
         *
         * @return void
         * @static
         */
        public static function registerCoreContainerAliases()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->registerCoreContainerAliases();
        }

        /**
         * Flush the container of all bindings and resolved instances.
         *
         * @return void
         * @static
         */
        public static function flush()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->flush();
        }

        /**
         * Get the application namespace.
         *
         * @return string
         * @throws \RuntimeException
         * @static
         */
        public static function getNamespace()
        {
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->getNamespace();
        }

        /**
         * Define a contextual binding.
         *
         * @param array|string $concrete
         * @return \Illuminate\Contracts\Container\ContextualBindingBuilder
         * @static
         */
        public static function when($concrete)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->when($concrete);
        }

        /**
         * Returns true if the container can return an entry for the given identifier.
         *
         * Returns false otherwise.
         *
         * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
         * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
         *
         * @param string $id Identifier of the entry to look for.
         * @return bool
         * @static
         */
        public static function has($id)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->has($id);
        }

        /**
         * Determine if the given abstract type has been resolved.
         *
         * @param string $abstract
         * @return bool
         * @static
         */
        public static function resolved($abstract)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->resolved($abstract);
        }

        /**
         * Determine if a given type is shared.
         *
         * @param string $abstract
         * @return bool
         * @static
         */
        public static function isShared($abstract)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->isShared($abstract);
        }

        /**
         * Determine if a given string is an alias.
         *
         * @param string $name
         * @return bool
         * @static
         */
        public static function isAlias($name)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->isAlias($name);
        }

        /**
         * Register a binding with the container.
         *
         * @param string $abstract
         * @param \Closure|string|null $concrete
         * @param bool $shared
         * @return void
         * @static
         */
        public static function bind($abstract, $concrete = null, $shared = false)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->bind($abstract, $concrete, $shared);
        }

        /**
         * Determine if the container has a method binding.
         *
         * @param string $method
         * @return bool
         * @static
         */
        public static function hasMethodBinding($method)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->hasMethodBinding($method);
        }

        /**
         * Bind a callback to resolve with Container::call.
         *
         * @param array|string $method
         * @param \Closure $callback
         * @return void
         * @static
         */
        public static function bindMethod($method, $callback)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->bindMethod($method, $callback);
        }

        /**
         * Get the method binding for the given method.
         *
         * @param string $method
         * @param mixed $instance
         * @return mixed
         * @static
         */
        public static function callMethodBinding($method, $instance)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->callMethodBinding($method, $instance);
        }

        /**
         * Add a contextual binding to the container.
         *
         * @param string $concrete
         * @param string $abstract
         * @param \Closure|string $implementation
         * @return void
         * @static
         */
        public static function addContextualBinding($concrete, $abstract, $implementation)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->addContextualBinding($concrete, $abstract, $implementation);
        }

        /**
         * Register a binding if it hasn't already been registered.
         *
         * @param string $abstract
         * @param \Closure|string|null $concrete
         * @param bool $shared
         * @return void
         * @static
         */
        public static function bindIf($abstract, $concrete = null, $shared = false)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->bindIf($abstract, $concrete, $shared);
        }

        /**
         * Register a shared binding in the container.
         *
         * @param string $abstract
         * @param \Closure|string|null $concrete
         * @return void
         * @static
         */
        public static function singleton($abstract, $concrete = null)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->singleton($abstract, $concrete);
        }

        /**
         * "Extend" an abstract type in the container.
         *
         * @param string $abstract
         * @param \Closure $closure
         * @return void
         * @throws \InvalidArgumentException
         * @static
         */
        public static function extend($abstract, $closure)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->extend($abstract, $closure);
        }

        /**
         * Register an existing instance as shared in the container.
         *
         * @param string $abstract
         * @param mixed $instance
         * @return mixed
         * @static
         */
        public static function instance($abstract, $instance)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->instance($abstract, $instance);
        }

        /**
         * Assign a set of tags to a given binding.
         *
         * @param array|string $abstracts
         * @param array|mixed $tags
         * @return void
         * @static
         */
        public static function tag($abstracts, $tags)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->tag($abstracts, $tags);
        }

        /**
         * Resolve all of the bindings for a given tag.
         *
         * @param string $tag
         * @return \Illuminate\Container\iterable
         * @static
         */
        public static function tagged($tag)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->tagged($tag);
        }

        /**
         * Alias a type to a different name.
         *
         * @param string $abstract
         * @param string $alias
         * @return void
         * @throws \LogicException
         * @static
         */
        public static function alias($abstract, $alias)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->alias($abstract, $alias);
        }

        /**
         * Bind a new callback to an abstract's rebind event.
         *
         * @param string $abstract
         * @param \Closure $callback
         * @return mixed
         * @static
         */
        public static function rebinding($abstract, $callback)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->rebinding($abstract, $callback);
        }

        /**
         * Refresh an instance on the given target and method.
         *
         * @param string $abstract
         * @param mixed $target
         * @param string $method
         * @return mixed
         * @static
         */
        public static function refresh($abstract, $target, $method)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->refresh($abstract, $target, $method);
        }

        /**
         * Wrap the given closure such that its dependencies will be injected when executed.
         *
         * @param \Closure $callback
         * @param array $parameters
         * @return \Closure
         * @static
         */
        public static function wrap($callback, $parameters = array())
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->wrap($callback, $parameters);
        }

        /**
         * Call the given Closure / class@method and inject its dependencies.
         *
         * @param callable|string $callback
         * @param array $parameters
         * @param string|null $defaultMethod
         * @return mixed
         * @static
         */
        public static function call($callback, $parameters = array(), $defaultMethod = null)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->call($callback, $parameters, $defaultMethod);
        }

        /**
         * Get a closure to resolve the given type from the container.
         *
         * @param string $abstract
         * @return \Closure
         * @static
         */
        public static function factory($abstract)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->factory($abstract);
        }

        /**
         * An alias function name for make().
         *
         * @param string $abstract
         * @param array $parameters
         * @return mixed
         * @static
         */
        public static function makeWith($abstract, $parameters = array())
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->makeWith($abstract, $parameters);
        }

        /**
         * Finds an entry of the container by its identifier and returns it.
         *
         * @param string $id Identifier of the entry to look for.
         * @return mixed Entry.
         * @static
         * @throws ContainerExceptionInterface Error while retrieving the entry.
         * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
         */
        public static function get($id)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->get($id);
        }

        /**
         * Instantiate a concrete instance of the given type.
         *
         * @param string $concrete
         * @return mixed
         * @throws \Illuminate\Contracts\Container\BindingResolutionException
         * @static
         */
        public static function build($concrete)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->build($concrete);
        }

        /**
         * Register a new resolving callback.
         *
         * @param \Closure|string $abstract
         * @param \Closure|null $callback
         * @return void
         * @static
         */
        public static function resolving($abstract, $callback = null)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->resolving($abstract, $callback);
        }

        /**
         * Register a new after resolving callback for all types.
         *
         * @param \Closure|string $abstract
         * @param \Closure|null $callback
         * @return void
         * @static
         */
        public static function afterResolving($abstract, $callback = null)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->afterResolving($abstract, $callback);
        }

        /**
         * Get the container's bindings.
         *
         * @return array
         * @static
         */
        public static function getBindings()
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->getBindings();
        }

        /**
         * Get the alias for an abstract if available.
         *
         * @param string $abstract
         * @return string
         * @static
         */
        public static function getAlias($abstract)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->getAlias($abstract);
        }

        /**
         * Remove all of the extender callbacks for a given type.
         *
         * @param string $abstract
         * @return void
         * @static
         */
        public static function forgetExtenders($abstract)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->forgetExtenders($abstract);
        }

        /**
         * Remove a resolved instance from the instance cache.
         *
         * @param string $abstract
         * @return void
         * @static
         */
        public static function forgetInstance($abstract)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->forgetInstance($abstract);
        }

        /**
         * Clear all of the instances from the container.
         *
         * @return void
         * @static
         */
        public static function forgetInstances()
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->forgetInstances();
        }

        /**
         * Get the globally available instance of the container.
         *
         * @return \Illuminate\Support\Facades\App
         * @static
         */
        public static function getInstance()
        {
            //Method inherited from \Illuminate\Container\Container
            return \Illuminate\Foundation\Application::getInstance();
        }

        /**
         * Set the shared instance of the container.
         *
         * @param \Illuminate\Contracts\Container\Container|null $container
         * @return \Illuminate\Contracts\Container\Container|static
         * @static
         */
        public static function setInstance($container = null)
        {
            //Method inherited from \Illuminate\Container\Container
            return \Illuminate\Foundation\Application::setInstance($container);
        }

        /**
         * Determine if a given offset exists.
         *
         * @param string $key
         * @return bool
         * @static
         */
        public static function offsetExists($key)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->offsetExists($key);
        }

        /**
         * Get the value at a given offset.
         *
         * @param string $key
         * @return mixed
         * @static
         */
        public static function offsetGet($key)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            return $instance->offsetGet($key);
        }

        /**
         * Set the value at a given offset.
         *
         * @param string $key
         * @param mixed $value
         * @return void
         * @static
         */
        public static function offsetSet($key, $value)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->offsetSet($key, $value);
        }

        /**
         * Unset the value at a given offset.
         *
         * @param string $key
         * @return void
         * @static
         */
        public static function offsetUnset($key)
        {
            //Method inherited from \Illuminate\Container\Container
            /** @var \Illuminate\Foundation\Application $instance */
            $instance->offsetUnset($key);
        }

    }
}

namespace {

    use application\models\Relations\HasManySyncable;

    /**
     * @property CI_Benchmark $benchmark                           This class enables you to mark points and calculate the time difference between them. Memory consumption can also be displayed.
     * @property CI_Calendar $calendar                            This class enables the creation of calendars
     * @property CI_Cache $cache                               Caching Class
     * @property CI_Cart $cart                                Shopping Cart Class
     * @property CI_Config $config                              This class contains functions that enable config files to be managed
     * @property CI_Controller $controller                          This class object is the super class that every library in CodeIgniter will be assigned to
     * @property CI_DB_forge $dbforge                             Database Forge Class
     * @property CI_DB_mysql_driver|CI_DB_query_builder $db                                  This is the platform-independent base Query Builder implementation class
     * @property CI_DB_utility $dbutil                              Database Utility Class
     * @property CI_Driver_Library $driver                              Driver Library Class
     * @property CI_Email $email                               Permits email to be sent using Mail, Sendmail, or SMTP
     * @property CI_Encrypt $encrypt                             Provides two-way keyed encoding using Mcrypt
     * @property CI_Encryption $encryption                          Provides two-way keyed encryption via PHP's MCrypt and/or OpenSSL extensions
     * @property CI_Exceptions $exceptions                          Exceptions Class
     * @property CI_Form_validation $form_validation                     Form Validation Class
     * @property CI_FTP $ftp                                 FTP Class
     * @property CI_Hooks $hooks                               Provides a mechanism to extend the base system without hacking
     * @property CI_Image_lib $image_lib                           Image Manipulation class
     * @property CI_Input $input                               Pre-processes global input data for security
     * @property CI_Javascript $javascript                          Javascript Class
     * @property CI_Jquery $jquery                              Jquery Class
     * @property CI_Lang $lang                                Language Class
     * @property CI_Loader $load                                Loads framework components
     * @property CI_Log $log                                 Logging Class
     * @property CI_Migration $migration                           All migrations should implement this, forces up() and down() and gives access to the CI super-global
     * @property CI_Model $model                               CodeIgniter Model Class
     * @property CI_Output $output                              Responsible for sending final output to the browser
     * @property CI_Pagination $pagination                          Pagination Class
     * @property CI_Parser $parser                              Parser Class
     * @property CI_Profiler $profiler                            This class enables you to display benchmark, query, and other data in order to help with debugging and optimization.
     * @property CI_Router $router                              Parses URIs and determines routing
     * @property CI_Security $security                            Security Class
     * @property CI_Session $session                             Session Class
     * @property CI_Table $table                               Lets you create tables manually or from database result objects, or arrays
     * @property CI_Trackback $trackback                           Trackback Sending/Receiving Class
     * @property CI_Typography $typography                          Typography Class
     * @property CI_Unit_test $unit                                Simple testing class
     * @property CI_Upload $upload                              File Uploading Class
     * @property CI_URI $uri                                 Parses URIs and determines routing
     * @property CI_User_agent $agent                               Identifies the platform, browser, robot, or mobile device of the browsing agent
     * @property CI_Xmlrpc $xmlrpc                              XML-RPC request handler class
     * @property CI_Xmlrpcs $xmlrpcs                             XML-RPC server class
     * @property CI_Zip $zip                                 Zip Compression Class
     * @property CI_Utf8 $utf8                                Provides support for UTF-8 environments
     * @property \application\core\Http\Request $request      Implementation of Laravel Request
     * @property Illuminate\Container\Container $container    Implementation of Laravel Container
     */
    class CI_Controller
    {
        public function __construct()
        {
        }
    }

    /**
     * @property Mdl_clients $mdl_clients
     */
    class CI_Model extends CI_Controller
    {
    }

    class MY_Controller extends CI_Controller
    {
    }

    class MX_Controller extends CI_Controller
    {
    }

    class App extends \Illuminate\Support\Facades\App {}
    class Config extends \Illuminate\Support\Facades\Config {}
    class DB extends \Illuminate\Support\Facades\DB {}
    class Eloquent extends \Illuminate\Database\Eloquent\Model {
        /**
         * Create and return an un-saved model instance.
         *
         * @param array $attributes
         * @return \Illuminate\Database\Eloquent\Model
         * @static
         */
        public static function make($attributes = array())
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->make($attributes);
        }

        /**
         * Register a new global scope.
         *
         * @param string $identifier
         * @param \Illuminate\Database\Eloquent\Scope|\Closure $scope
         * @return \Illuminate\Database\Eloquent\Builder
         * @static
         */
        public static function withGlobalScope($identifier, $scope)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->withGlobalScope($identifier, $scope);
        }

        /**
         * Remove a registered global scope.
         *
         * @param \Illuminate\Database\Eloquent\Scope|string $scope
         * @return \Illuminate\Database\Eloquent\Builder
         * @static
         */
        public static function withoutGlobalScope($scope)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->withoutGlobalScope($scope);
        }

        /**
         * Remove all or passed registered global scopes.
         *
         * @param array|null $scopes
         * @return \Illuminate\Database\Eloquent\Builder
         * @static
         */
        public static function withoutGlobalScopes($scopes = null)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->withoutGlobalScopes($scopes);
        }

        /**
         * Get an array of global scopes that were removed from the query.
         *
         * @return array
         * @static
         */
        public static function removedScopes()
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->removedScopes();
        }

        /**
         * Add a where clause on the primary key to the query.
         *
         * @param mixed $id
         * @return \Illuminate\Database\Eloquent\Builder
         * @static
         */
        public static function whereKey($id)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->whereKey($id);
        }

        /**
         * Add a where clause on the primary key to the query.
         *
         * @param mixed $id
         * @return \Illuminate\Database\Eloquent\Builder
         * @static
         */
        public static function whereKeyNot($id)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->whereKeyNot($id);
        }

        /**
         * Add a basic where clause to the query.
         *
         * @param string|array|\Closure $column
         * @param mixed $operator
         * @param mixed $value
         * @param string $boolean
         * @return \Illuminate\Database\Eloquent\Builder
         * @static
         */
        public static function where($column, $operator = null, $value = null, $boolean = 'and')
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->where($column, $operator, $value, $boolean);
        }

        /**
         * Add an "or where" clause to the query.
         *
         * @param \Closure|array|string $column
         * @param mixed $operator
         * @param mixed $value
         * @return \Illuminate\Database\Eloquent\Builder|static
         * @static
         */
        public static function orWhere($column, $operator = null, $value = null)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->orWhere($column, $operator, $value);
        }

        /**
         * Add an "order by" clause for a timestamp to the query.
         *
         * @param string $column
         * @return \Illuminate\Database\Eloquent\Builder
         * @static
         */
        public static function latest($column = null)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->latest($column);
        }

        /**
         * Add an "order by" clause for a timestamp to the query.
         *
         * @param string $column
         * @return \Illuminate\Database\Eloquent\Builder
         * @static
         */
        public static function oldest($column = null)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->oldest($column);
        }

        /**
         * Create a collection of models from plain arrays.
         *
         * @param array $items
         * @return \Illuminate\Database\Eloquent\Collection
         * @static
         */
        public static function hydrate($items)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->hydrate($items);
        }

        /**
         * Create a collection of models from a raw query.
         *
         * @param string $query
         * @param array $bindings
         * @return \Illuminate\Database\Eloquent\Collection
         * @static
         */
        public static function fromQuery($query, $bindings = array())
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->fromQuery($query, $bindings);
        }

        /**
         * Find a model by its primary key.
         *
         * @param mixed $id
         * @param array $columns
         * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
         * @static
         */
        public static function find($id, $columns = array())
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->find($id, $columns);
        }

        /**
         * Find multiple models by their primary keys.
         *
         * @param \Illuminate\Contracts\Support\Arrayable|array $ids
         * @param array $columns
         * @return \Illuminate\Database\Eloquent\Collection
         * @static
         */
        public static function findMany($ids, $columns = array())
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->findMany($ids, $columns);
        }

        /**
         * Find a model by its primary key or throw an exception.
         *
         * @param mixed $id
         * @param array $columns
         * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static|static[]
         * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
         * @static
         */
        public static function findOrFail($id, $columns = array())
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->findOrFail($id, $columns);
        }

        /**
         * Find a model by its primary key or return fresh model instance.
         *
         * @param mixed $id
         * @param array $columns
         * @return \Illuminate\Database\Eloquent\Model|static
         * @static
         */
        public static function findOrNew($id, $columns = array())
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->findOrNew($id, $columns);
        }

        /**
         * Get the first record matching the attributes or instantiate it.
         *
         * @param array $attributes
         * @param array $values
         * @return \Illuminate\Database\Eloquent\Model|static
         * @static
         */
        public static function firstOrNew($attributes, $values = array())
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->firstOrNew($attributes, $values);
        }

        /**
         * Get the first record matching the attributes or create it.
         *
         * @param array $attributes
         * @param array $values
         * @return \Illuminate\Database\Eloquent\Model|static
         * @static
         */
        public static function firstOrCreate($attributes, $values = array())
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->firstOrCreate($attributes, $values);
        }

        /**
         * Create or update a record matching the attributes, and fill it with values.
         *
         * @param array $attributes
         * @param array $values
         * @return \Illuminate\Database\Eloquent\Model|static
         * @static
         */
        public static function updateOrCreate($attributes, $values = array())
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->updateOrCreate($attributes, $values);
        }

        /**
         * Execute the query and get the first result or throw an exception.
         *
         * @param array $columns
         * @return \Illuminate\Database\Eloquent\Model|static
         * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
         * @static
         */
        public static function firstOrFail($columns = array())
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->firstOrFail($columns);
        }

        /**
         * Execute the query and get the first result or call a callback.
         *
         * @param \Closure|array $columns
         * @param \Closure|null $callback
         * @return \Illuminate\Database\Eloquent\Model|static|mixed
         * @static
         */
        public static function firstOr($columns = array(), $callback = null)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->firstOr($columns, $callback);
        }

        /**
         * Get a single column's value from the first result of a query.
         *
         * @param string $column
         * @return mixed
         * @static
         */
        public static function value($column)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->value($column);
        }

        /**
         * Execute the query as a "select" statement.
         *
         * @param array $columns
         * @return \Illuminate\Database\Eloquent\Collection|static[]
         * @static
         */
        public static function get($columns = array())
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->get($columns);
        }

        /**
         * Get the hydrated models without eager loading.
         *
         * @param array $columns
         * @return \Illuminate\Database\Eloquent\Model[]|static[]
         * @static
         */
        public static function getModels($columns = array())
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->getModels($columns);
        }

        /**
         * Eager load the relationships for the models.
         *
         * @param array $models
         * @return array
         * @static
         */
        public static function eagerLoadRelations($models)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->eagerLoadRelations($models);
        }

        /**
         * Get a generator for the given query.
         *
         * @return \Generator
         * @static
         */
        public static function cursor()
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->cursor();
        }

        /**
         * Chunk the results of a query by comparing numeric IDs.
         *
         * @param int $count
         * @param callable $callback
         * @param string|null $column
         * @param string|null $alias
         * @return bool
         * @static
         */
        public static function chunkById($count, $callback, $column = null, $alias = null)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->chunkById($count, $callback, $column, $alias);
        }

        /**
         * Get an array with the values of a given column.
         *
         * @param string $column
         * @param string|null $key
         * @return \Illuminate\Support\Collection
         * @static
         */
        public static function pluck($column, $key = null)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->pluck($column, $key);
        }

        /**
         * Paginate the given query.
         *
         * @param int $perPage
         * @param array $columns
         * @param string $pageName
         * @param int|null $page
         * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
         * @throws \InvalidArgumentException
         * @static
         */
        public static function paginate($perPage = null, $columns = array(), $pageName = 'page', $page = null)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->paginate($perPage, $columns, $pageName, $page);
        }

        /**
         * Paginate the given query into a simple paginator.
         *
         * @param int $perPage
         * @param array $columns
         * @param string $pageName
         * @param int|null $page
         * @return \Illuminate\Contracts\Pagination\Paginator
         * @static
         */
        public static function simplePaginate($perPage = null, $columns = array(), $pageName = 'page', $page = null)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->simplePaginate($perPage, $columns, $pageName, $page);
        }

        /**
         * Save a new model and return the instance.
         *
         * @param array $attributes
         * @return \Illuminate\Database\Eloquent\Model|$this
         * @static
         */
        public static function create($attributes = array())
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->create($attributes);
        }

        /**
         * Save a new model and return the instance. Allow mass-assignment.
         *
         * @param array $attributes
         * @return \Illuminate\Database\Eloquent\Model|$this
         * @static
         */
        public static function forceCreate($attributes)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->forceCreate($attributes);
        }

        /**
         * Register a replacement for the default delete function.
         *
         * @param \Closure $callback
         * @return void
         * @static
         */
        public static function onDelete($callback)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            $instance->onDelete($callback);
        }

        /**
         * Call the given local model scopes.
         *
         * @param array $scopes
         * @return static|mixed
         * @static
         */
        public static function scopes($scopes)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->scopes($scopes);
        }

        /**
         * Apply the scopes to the Eloquent builder instance and return it.
         *
         * @return static
         * @static
         */
        public static function applyScopes()
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->applyScopes();
        }

        /**
         * Prevent the specified relations from being eager loaded.
         *
         * @param mixed $relations
         * @return \Illuminate\Database\Eloquent\Builder
         * @static
         */
        public static function without($relations)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->without($relations);
        }

        /**
         * Create a new instance of the model being queried.
         *
         * @param array $attributes
         * @return \Illuminate\Database\Eloquent\Model|static
         * @static
         */
        public static function newModelInstance($attributes = array())
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->newModelInstance($attributes);
        }

        /**
         * Get the underlying query builder instance.
         *
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function getQuery()
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->getQuery();
        }

        /**
         * Set the underlying query builder instance.
         *
         * @param \Illuminate\Database\Query\Builder $query
         * @return \Illuminate\Database\Eloquent\Builder
         * @static
         */
        public static function setQuery($query)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->setQuery($query);
        }

        /**
         * Get a base query builder instance.
         *
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function toBase()
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->toBase();
        }

        /**
         * Get the relationships being eagerly loaded.
         *
         * @return array
         * @static
         */
        public static function getEagerLoads()
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->getEagerLoads();
        }

        /**
         * Set the relationships being eagerly loaded.
         *
         * @param array $eagerLoad
         * @return \Illuminate\Database\Eloquent\Builder
         * @static
         */
        public static function setEagerLoads($eagerLoad)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->setEagerLoads($eagerLoad);
        }

        /**
         * Get the model instance being queried.
         *
         * @return \Illuminate\Database\Eloquent\Model|static
         * @static
         */
        public static function getModel()
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->getModel();
        }

        /**
         * Set a model instance for the model being queried.
         *
         * @param \Illuminate\Database\Eloquent\Model $model
         * @return \Illuminate\Database\Eloquent\Builder
         * @static
         */
        public static function setModel($model)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->setModel($model);
        }

        /**
         * Get the given macro by name.
         *
         * @param string $name
         * @return \Closure
         * @static
         */
        public static function getMacro($name)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->getMacro($name);
        }

        /**
         * Chunk the results of the query.
         *
         * @param int $count
         * @param callable $callback
         * @return bool
         * @static
         */
        public static function chunk($count, $callback)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->chunk($count, $callback);
        }

        /**
         * Execute a callback over each item while chunking.
         *
         * @param callable $callback
         * @param int $count
         * @return bool
         * @static
         */
        public static function each($callback, $count = 1000)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->each($callback, $count);
        }

        /**
         * Execute the query and get the first result.
         *
         * @param array $columns
         * @return \Illuminate\Database\Eloquent\Model|object|static|null
         * @static
         */
        public static function first($columns = array())
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->first($columns);
        }

        /**
         * Apply the callback's query changes if the given "value" is true.
         *
         * @param mixed $value
         * @param callable $callback
         * @param callable|null $default
         * @return mixed|$this
         * @static
         */
        public static function when($value, $callback, $default = null)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->when($value, $callback, $default);
        }

        /**
         * Pass the query to a given callback.
         *
         * @param callable $callback
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function tap($callback)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->tap($callback);
        }

        /**
         * Apply the callback's query changes if the given "value" is false.
         *
         * @param mixed $value
         * @param callable $callback
         * @param callable|null $default
         * @return mixed|$this
         * @static
         */
        public static function unless($value, $callback, $default = null)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->unless($value, $callback, $default);
        }

        /**
         * Add a relationship count / exists condition to the query.
         *
         * @param string|\Illuminate\Database\Eloquent\Relations\Relation $relation
         * @param string $operator
         * @param int $count
         * @param string $boolean
         * @param \Closure|null $callback
         * @return \Illuminate\Database\Eloquent\Builder|static
         * @static
         */
        public static function has($relation, $operator = '>=', $count = 1, $boolean = 'and', $callback = null)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->has($relation, $operator, $count, $boolean, $callback);
        }

        /**
         * Add a relationship count / exists condition to the query with an "or".
         *
         * @param string $relation
         * @param string $operator
         * @param int $count
         * @return \Illuminate\Database\Eloquent\Builder|static
         * @static
         */
        public static function orHas($relation, $operator = '>=', $count = 1)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->orHas($relation, $operator, $count);
        }

        /**
         * Add a relationship count / exists condition to the query.
         *
         * @param string $relation
         * @param string $boolean
         * @param \Closure|null $callback
         * @return \Illuminate\Database\Eloquent\Builder|static
         * @static
         */
        public static function doesntHave($relation, $boolean = 'and', $callback = null)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->doesntHave($relation, $boolean, $callback);
        }

        /**
         * Add a relationship count / exists condition to the query with an "or".
         *
         * @param string $relation
         * @return \Illuminate\Database\Eloquent\Builder|static
         * @static
         */
        public static function orDoesntHave($relation)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->orDoesntHave($relation);
        }

        /**
         * Add a relationship count / exists condition to the query with where clauses.
         *
         * @param string $relation
         * @param \Closure|null $callback
         * @param string $operator
         * @param int $count
         * @return \Illuminate\Database\Eloquent\Builder|static
         * @static
         */
        public static function whereHas($relation, $callback = null, $operator = '>=', $count = 1)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->whereHas($relation, $callback, $operator, $count);
        }

        /**
         * Add a relationship count / exists condition to the query with where clauses and an "or".
         *
         * @param string $relation
         * @param \Closure $callback
         * @param string $operator
         * @param int $count
         * @return \Illuminate\Database\Eloquent\Builder|static
         * @static
         */
        public static function orWhereHas($relation, $callback = null, $operator = '>=', $count = 1)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->orWhereHas($relation, $callback, $operator, $count);
        }

        /**
         * Add a relationship count / exists condition to the query with where clauses.
         *
         * @param string $relation
         * @param \Closure|null $callback
         * @return \Illuminate\Database\Eloquent\Builder|static
         * @static
         */
        public static function whereDoesntHave($relation, $callback = null)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->whereDoesntHave($relation, $callback);
        }

        /**
         * Add a relationship count / exists condition to the query with where clauses and an "or".
         *
         * @param string $relation
         * @param \Closure $callback
         * @return \Illuminate\Database\Eloquent\Builder|static
         * @static
         */
        public static function orWhereDoesntHave($relation, $callback = null)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->orWhereDoesntHave($relation, $callback);
        }

        /**
         * Add a polymorphic relationship count / exists condition to the query.
         *
         * @param string $relation
         * @param string|array $types
         * @param string $operator
         * @param int $count
         * @param string $boolean
         * @param \Closure|null $callback
         * @return \Illuminate\Database\Eloquent\Builder|static
         * @static
         */
        public static function hasMorph($relation, $types, $operator = '>=', $count = 1, $boolean = 'and', $callback = null)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->hasMorph($relation, $types, $operator, $count, $boolean, $callback);
        }

        /**
         * Add a polymorphic relationship count / exists condition to the query with an "or".
         *
         * @param string $relation
         * @param string|array $types
         * @param string $operator
         * @param int $count
         * @return \Illuminate\Database\Eloquent\Builder|static
         * @static
         */
        public static function orHasMorph($relation, $types, $operator = '>=', $count = 1)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->orHasMorph($relation, $types, $operator, $count);
        }

        /**
         * Add a polymorphic relationship count / exists condition to the query.
         *
         * @param string $relation
         * @param string|array $types
         * @param string $boolean
         * @param \Closure|null $callback
         * @return \Illuminate\Database\Eloquent\Builder|static
         * @static
         */
        public static function doesntHaveMorph($relation, $types, $boolean = 'and', $callback = null)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->doesntHaveMorph($relation, $types, $boolean, $callback);
        }

        /**
         * Add a polymorphic relationship count / exists condition to the query with an "or".
         *
         * @param string $relation
         * @param string|array $types
         * @return \Illuminate\Database\Eloquent\Builder|static
         * @static
         */
        public static function orDoesntHaveMorph($relation, $types)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->orDoesntHaveMorph($relation, $types);
        }

        /**
         * Add a polymorphic relationship count / exists condition to the query with where clauses.
         *
         * @param string $relation
         * @param string|array $types
         * @param \Closure|null $callback
         * @param string $operator
         * @param int $count
         * @return \Illuminate\Database\Eloquent\Builder|static
         * @static
         */
        public static function whereHasMorph($relation, $types, $callback = null, $operator = '>=', $count = 1)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->whereHasMorph($relation, $types, $callback, $operator, $count);
        }

        /**
         * Add a polymorphic relationship count / exists condition to the query with where clauses and an "or".
         *
         * @param string $relation
         * @param string|array $types
         * @param \Closure $callback
         * @param string $operator
         * @param int $count
         * @return \Illuminate\Database\Eloquent\Builder|static
         * @static
         */
        public static function orWhereHasMorph($relation, $types, $callback = null, $operator = '>=', $count = 1)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->orWhereHasMorph($relation, $types, $callback, $operator, $count);
        }

        /**
         * Add a polymorphic relationship count / exists condition to the query with where clauses.
         *
         * @param string $relation
         * @param string|array $types
         * @param \Closure|null $callback
         * @return \Illuminate\Database\Eloquent\Builder|static
         * @static
         */
        public static function whereDoesntHaveMorph($relation, $types, $callback = null)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->whereDoesntHaveMorph($relation, $types, $callback);
        }

        /**
         * Add a polymorphic relationship count / exists condition to the query with where clauses and an "or".
         *
         * @param string $relation
         * @param string|array $types
         * @param \Closure $callback
         * @return \Illuminate\Database\Eloquent\Builder|static
         * @static
         */
        public static function orWhereDoesntHaveMorph($relation, $types, $callback = null)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->orWhereDoesntHaveMorph($relation, $types, $callback);
        }

        /**
         * Add subselect queries to count the relations.
         *
         * @param mixed $relations
         * @return \Illuminate\Database\Eloquent\Builder
         * @static
         */
        public static function withCount($relations)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->withCount($relations);
        }

        /**
         * Merge the where constraints from another query to the current query.
         *
         * @param \Illuminate\Database\Eloquent\Builder $from
         * @return \Illuminate\Database\Eloquent\Builder|static
         * @static
         */
        public static function mergeConstraintsFrom($from)
        {
            /** @var \Illuminate\Database\Eloquent\Builder $instance */
            return $instance->mergeConstraintsFrom($from);
        }

        /**
         * Set the columns to be selected.
         *
         * @param array|mixed $columns
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function select($columns = array())
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->select($columns);
        }

        /**
         * Add a subselect expression to the query.
         *
         * @param \Closure|\Illuminate\Database\Query\Builder|string $query
         * @param string $as
         * @return \Illuminate\Database\Query\Builder|static
         * @throws \InvalidArgumentException
         * @static
         */
        public static function selectSub($query, $as)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->selectSub($query, $as);
        }

        /**
         * Add a new "raw" select expression to the query.
         *
         * @param string $expression
         * @param array $bindings
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function selectRaw($expression, $bindings = array())
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->selectRaw($expression, $bindings);
        }

        /**
         * Makes "from" fetch from a subquery.
         *
         * @param \Closure|\Illuminate\Database\Query\Builder|string $query
         * @param string $as
         * @return \Illuminate\Database\Query\Builder|static
         * @throws \InvalidArgumentException
         * @static
         */
        public static function fromSub($query, $as)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->fromSub($query, $as);
        }

        /**
         * Add a raw from clause to the query.
         *
         * @param string $expression
         * @param mixed $bindings
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function fromRaw($expression, $bindings = array())
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->fromRaw($expression, $bindings);
        }

        /**
         * Add a new select column to the query.
         *
         * @param array|mixed $column
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function addSelect($column)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->addSelect($column);
        }

        /**
         * Force the query to only return distinct results.
         *
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function distinct()
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->distinct();
        }

        /**
         * Set the table which the query is targeting.
         *
         * @param string $table
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function from($table)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->from($table);
        }

        /**
         * Add a join clause to the query.
         *
         * @param string $table
         * @param \Closure|string $first
         * @param string|null $operator
         * @param string|null $second
         * @param string $type
         * @param bool $where
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->join($table, $first, $operator, $second, $type, $where);
        }

        /**
         * Add a "join where" clause to the query.
         *
         * @param string $table
         * @param \Closure|string $first
         * @param string $operator
         * @param string $second
         * @param string $type
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function joinWhere($table, $first, $operator, $second, $type = 'inner')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->joinWhere($table, $first, $operator, $second, $type);
        }

        /**
         * Add a subquery join clause to the query.
         *
         * @param \Closure|\Illuminate\Database\Query\Builder|string $query
         * @param string $as
         * @param \Closure|string $first
         * @param string|null $operator
         * @param string|null $second
         * @param string $type
         * @param bool $where
         * @return \Illuminate\Database\Query\Builder|static
         * @throws \InvalidArgumentException
         * @static
         */
        public static function joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->joinSub($query, $as, $first, $operator, $second, $type, $where);
        }

        /**
         * Add a left join to the query.
         *
         * @param string $table
         * @param \Closure|string $first
         * @param string|null $operator
         * @param string|null $second
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function leftJoin($table, $first, $operator = null, $second = null)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->leftJoin($table, $first, $operator, $second);
        }

        /**
         * Add a "join where" clause to the query.
         *
         * @param string $table
         * @param \Closure|string $first
         * @param string $operator
         * @param string $second
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function leftJoinWhere($table, $first, $operator, $second)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->leftJoinWhere($table, $first, $operator, $second);
        }

        /**
         * Add a subquery left join to the query.
         *
         * @param \Closure|\Illuminate\Database\Query\Builder|string $query
         * @param string $as
         * @param \Closure|string $first
         * @param string|null $operator
         * @param string|null $second
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function leftJoinSub($query, $as, $first, $operator = null, $second = null)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->leftJoinSub($query, $as, $first, $operator, $second);
        }

        /**
         * Add a right join to the query.
         *
         * @param string $table
         * @param \Closure|string $first
         * @param string|null $operator
         * @param string|null $second
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function rightJoin($table, $first, $operator = null, $second = null)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->rightJoin($table, $first, $operator, $second);
        }

        /**
         * Add a "right join where" clause to the query.
         *
         * @param string $table
         * @param \Closure|string $first
         * @param string $operator
         * @param string $second
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function rightJoinWhere($table, $first, $operator, $second)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->rightJoinWhere($table, $first, $operator, $second);
        }

        /**
         * Add a subquery right join to the query.
         *
         * @param \Closure|\Illuminate\Database\Query\Builder|string $query
         * @param string $as
         * @param \Closure|string $first
         * @param string|null $operator
         * @param string|null $second
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function rightJoinSub($query, $as, $first, $operator = null, $second = null)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->rightJoinSub($query, $as, $first, $operator, $second);
        }

        /**
         * Add a "cross join" clause to the query.
         *
         * @param string $table
         * @param \Closure|string|null $first
         * @param string|null $operator
         * @param string|null $second
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function crossJoin($table, $first = null, $operator = null, $second = null)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->crossJoin($table, $first, $operator, $second);
        }

        /**
         * Merge an array of where clauses and bindings.
         *
         * @param array $wheres
         * @param array $bindings
         * @return void
         * @static
         */
        public static function mergeWheres($wheres, $bindings)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            $instance->mergeWheres($wheres, $bindings);
        }

        /**
         * Prepare the value and operator for a where clause.
         *
         * @param string $value
         * @param string $operator
         * @param bool $useDefault
         * @return array
         * @throws \InvalidArgumentException
         * @static
         */
        public static function prepareValueAndOperator($value, $operator, $useDefault = false)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->prepareValueAndOperator($value, $operator, $useDefault);
        }

        /**
         * Add a "where" clause comparing two columns to the query.
         *
         * @param string|array $first
         * @param string|null $operator
         * @param string|null $second
         * @param string|null $boolean
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function whereColumn($first, $operator = null, $second = null, $boolean = 'and')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereColumn($first, $operator, $second, $boolean);
        }

        /**
         * Add an "or where" clause comparing two columns to the query.
         *
         * @param string|array $first
         * @param string|null $operator
         * @param string|null $second
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function orWhereColumn($first, $operator = null, $second = null)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orWhereColumn($first, $operator, $second);
        }

        /**
         * Add a raw where clause to the query.
         *
         * @param string $sql
         * @param mixed $bindings
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function whereRaw($sql, $bindings = array(), $boolean = 'and')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereRaw($sql, $bindings, $boolean);
        }

        /**
         * Add a raw or where clause to the query.
         *
         * @param string $sql
         * @param mixed $bindings
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function orWhereRaw($sql, $bindings = array())
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orWhereRaw($sql, $bindings);
        }

        /**
         * Add a "where in" clause to the query.
         *
         * @param string $column
         * @param mixed $values
         * @param string $boolean
         * @param bool $not
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function whereIn($column, $values, $boolean = 'and', $not = false)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereIn($column, $values, $boolean, $not);
        }

        /**
         * Add an "or where in" clause to the query.
         *
         * @param string $column
         * @param mixed $values
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function orWhereIn($column, $values)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orWhereIn($column, $values);
        }

        /**
         * Add a "where not in" clause to the query.
         *
         * @param string $column
         * @param mixed $values
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function whereNotIn($column, $values, $boolean = 'and')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereNotIn($column, $values, $boolean);
        }

        /**
         * Add an "or where not in" clause to the query.
         *
         * @param string $column
         * @param mixed $values
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function orWhereNotIn($column, $values)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orWhereNotIn($column, $values);
        }

        /**
         * Add a "where in raw" clause for integer values to the query.
         *
         * @param string $column
         * @param \Illuminate\Contracts\Support\Arrayable|array $values
         * @param string $boolean
         * @param bool $not
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function whereIntegerInRaw($column, $values, $boolean = 'and', $not = false)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereIntegerInRaw($column, $values, $boolean, $not);
        }

        /**
         * Add a "where not in raw" clause for integer values to the query.
         *
         * @param string $column
         * @param \Illuminate\Contracts\Support\Arrayable|array $values
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function whereIntegerNotInRaw($column, $values, $boolean = 'and')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereIntegerNotInRaw($column, $values, $boolean);
        }

        /**
         * Add a "where null" clause to the query.
         *
         * @param string|array $columns
         * @param string $boolean
         * @param bool $not
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function whereNull($columns, $boolean = 'and', $not = false)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereNull($columns, $boolean, $not);
        }

        /**
         * Add an "or where null" clause to the query.
         *
         * @param string $column
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function orWhereNull($column)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orWhereNull($column);
        }

        /**
         * Add a "where not null" clause to the query.
         *
         * @param string $column
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function whereNotNull($column, $boolean = 'and')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereNotNull($column, $boolean);
        }

        /**
         * Add a where between statement to the query.
         *
         * @param string $column
         * @param array $values
         * @param string $boolean
         * @param bool $not
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function whereBetween($column, $values, $boolean = 'and', $not = false)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereBetween($column, $values, $boolean, $not);
        }

        /**
         * Add an or where between statement to the query.
         *
         * @param string $column
         * @param array $values
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function orWhereBetween($column, $values)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orWhereBetween($column, $values);
        }

        /**
         * Add a where not between statement to the query.
         *
         * @param string $column
         * @param array $values
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function whereNotBetween($column, $values, $boolean = 'and')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereNotBetween($column, $values, $boolean);
        }

        /**
         * Add an or where not between statement to the query.
         *
         * @param string $column
         * @param array $values
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function orWhereNotBetween($column, $values)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orWhereNotBetween($column, $values);
        }

        /**
         * Add an "or where not null" clause to the query.
         *
         * @param string $column
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function orWhereNotNull($column)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orWhereNotNull($column);
        }

        /**
         * Add a "where date" statement to the query.
         *
         * @param string $column
         * @param string $operator
         * @param \DateTimeInterface|string|null $value
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function whereDate($column, $operator, $value = null, $boolean = 'and')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereDate($column, $operator, $value, $boolean);
        }

        /**
         * Add an "or where date" statement to the query.
         *
         * @param string $column
         * @param string $operator
         * @param \DateTimeInterface|string|null $value
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function orWhereDate($column, $operator, $value = null)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orWhereDate($column, $operator, $value);
        }

        /**
         * Add a "where time" statement to the query.
         *
         * @param string $column
         * @param string $operator
         * @param \DateTimeInterface|string|null $value
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function whereTime($column, $operator, $value = null, $boolean = 'and')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereTime($column, $operator, $value, $boolean);
        }

        /**
         * Add an "or where time" statement to the query.
         *
         * @param string $column
         * @param string $operator
         * @param \DateTimeInterface|string|null $value
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function orWhereTime($column, $operator, $value = null)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orWhereTime($column, $operator, $value);
        }

        /**
         * Add a "where day" statement to the query.
         *
         * @param string $column
         * @param string $operator
         * @param \DateTimeInterface|string|null $value
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function whereDay($column, $operator, $value = null, $boolean = 'and')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereDay($column, $operator, $value, $boolean);
        }

        /**
         * Add an "or where day" statement to the query.
         *
         * @param string $column
         * @param string $operator
         * @param \DateTimeInterface|string|null $value
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function orWhereDay($column, $operator, $value = null)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orWhereDay($column, $operator, $value);
        }

        /**
         * Add a "where month" statement to the query.
         *
         * @param string $column
         * @param string $operator
         * @param \DateTimeInterface|string|null $value
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function whereMonth($column, $operator, $value = null, $boolean = 'and')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereMonth($column, $operator, $value, $boolean);
        }

        /**
         * Add an "or where month" statement to the query.
         *
         * @param string $column
         * @param string $operator
         * @param \DateTimeInterface|string|null $value
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function orWhereMonth($column, $operator, $value = null)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orWhereMonth($column, $operator, $value);
        }

        /**
         * Add a "where year" statement to the query.
         *
         * @param string $column
         * @param string $operator
         * @param \DateTimeInterface|string|int|null $value
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function whereYear($column, $operator, $value = null, $boolean = 'and')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereYear($column, $operator, $value, $boolean);
        }

        /**
         * Add an "or where year" statement to the query.
         *
         * @param string $column
         * @param string $operator
         * @param \DateTimeInterface|string|int|null $value
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function orWhereYear($column, $operator, $value = null)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orWhereYear($column, $operator, $value);
        }

        /**
         * Add a nested where statement to the query.
         *
         * @param \Closure $callback
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function whereNested($callback, $boolean = 'and')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereNested($callback, $boolean);
        }

        /**
         * Create a new query instance for nested where condition.
         *
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function forNestedWhere()
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->forNestedWhere();
        }

        /**
         * Add another query builder as a nested where to the query builder.
         *
         * @param \Illuminate\Database\Query\Builder|static $query
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function addNestedWhereQuery($query, $boolean = 'and')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->addNestedWhereQuery($query, $boolean);
        }

        /**
         * Add an exists clause to the query.
         *
         * @param \Closure $callback
         * @param string $boolean
         * @param bool $not
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function whereExists($callback, $boolean = 'and', $not = false)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereExists($callback, $boolean, $not);
        }

        /**
         * Add an or exists clause to the query.
         *
         * @param \Closure $callback
         * @param bool $not
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function orWhereExists($callback, $not = false)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orWhereExists($callback, $not);
        }

        /**
         * Add a where not exists clause to the query.
         *
         * @param \Closure $callback
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function whereNotExists($callback, $boolean = 'and')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereNotExists($callback, $boolean);
        }

        /**
         * Add a where not exists clause to the query.
         *
         * @param \Closure $callback
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function orWhereNotExists($callback)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orWhereNotExists($callback);
        }

        /**
         * Add an exists clause to the query.
         *
         * @param \Illuminate\Database\Query\Builder $query
         * @param string $boolean
         * @param bool $not
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function addWhereExistsQuery($query, $boolean = 'and', $not = false)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->addWhereExistsQuery($query, $boolean, $not);
        }

        /**
         * Adds a where condition using row values.
         *
         * @param array $columns
         * @param string $operator
         * @param array $values
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function whereRowValues($columns, $operator, $values, $boolean = 'and')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereRowValues($columns, $operator, $values, $boolean);
        }

        /**
         * Adds a or where condition using row values.
         *
         * @param array $columns
         * @param string $operator
         * @param array $values
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function orWhereRowValues($columns, $operator, $values)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orWhereRowValues($columns, $operator, $values);
        }

        /**
         * Add a "where JSON contains" clause to the query.
         *
         * @param string $column
         * @param mixed $value
         * @param string $boolean
         * @param bool $not
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function whereJsonContains($column, $value, $boolean = 'and', $not = false)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereJsonContains($column, $value, $boolean, $not);
        }

        /**
         * Add a "or where JSON contains" clause to the query.
         *
         * @param string $column
         * @param mixed $value
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function orWhereJsonContains($column, $value)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orWhereJsonContains($column, $value);
        }

        /**
         * Add a "where JSON not contains" clause to the query.
         *
         * @param string $column
         * @param mixed $value
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function whereJsonDoesntContain($column, $value, $boolean = 'and')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereJsonDoesntContain($column, $value, $boolean);
        }

        /**
         * Add a "or where JSON not contains" clause to the query.
         *
         * @param string $column
         * @param mixed $value
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function orWhereJsonDoesntContain($column, $value)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orWhereJsonDoesntContain($column, $value);
        }

        /**
         * Add a "where JSON length" clause to the query.
         *
         * @param string $column
         * @param mixed $operator
         * @param mixed $value
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function whereJsonLength($column, $operator, $value = null, $boolean = 'and')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->whereJsonLength($column, $operator, $value, $boolean);
        }

        /**
         * Add a "or where JSON length" clause to the query.
         *
         * @param string $column
         * @param mixed $operator
         * @param mixed $value
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function orWhereJsonLength($column, $operator, $value = null)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orWhereJsonLength($column, $operator, $value);
        }

        /**
         * Handles dynamic "where" clauses to the query.
         *
         * @param string $method
         * @param array $parameters
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function dynamicWhere($method, $parameters)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->dynamicWhere($method, $parameters);
        }

        /**
         * Add a "group by" clause to the query.
         *
         * @param array $groups
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function groupBy($groups = null)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->groupBy($groups);
        }

        /**
         * Add a "having" clause to the query.
         *
         * @param string $column
         * @param string|null $operator
         * @param string|null $value
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function having($column, $operator = null, $value = null, $boolean = 'and')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->having($column, $operator, $value, $boolean);
        }

        /**
         * Add a "or having" clause to the query.
         *
         * @param string $column
         * @param string|null $operator
         * @param string|null $value
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function orHaving($column, $operator = null, $value = null)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orHaving($column, $operator, $value);
        }

        /**
         * Add a "having between " clause to the query.
         *
         * @param string $column
         * @param array $values
         * @param string $boolean
         * @param bool $not
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function havingBetween($column, $values, $boolean = 'and', $not = false)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->havingBetween($column, $values, $boolean, $not);
        }

        /**
         * Add a raw having clause to the query.
         *
         * @param string $sql
         * @param array $bindings
         * @param string $boolean
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function havingRaw($sql, $bindings = array(), $boolean = 'and')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->havingRaw($sql, $bindings, $boolean);
        }

        /**
         * Add a raw or having clause to the query.
         *
         * @param string $sql
         * @param array $bindings
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function orHavingRaw($sql, $bindings = array())
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orHavingRaw($sql, $bindings);
        }

        /**
         * Add an "order by" clause to the query.
         *
         * @param string $column
         * @param string $direction
         * @return \Illuminate\Database\Query\Builder
         * @throws \InvalidArgumentException
         * @static
         */
        public static function orderBy($column, $direction = 'asc')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orderBy($column, $direction);
        }

        /**
         * Add a descending "order by" clause to the query.
         *
         * @param string $column
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function orderByDesc($column)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orderByDesc($column);
        }

        /**
         * Put the query's results in random order.
         *
         * @param string $seed
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function inRandomOrder($seed = '')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->inRandomOrder($seed);
        }

        /**
         * Add a raw "order by" clause to the query.
         *
         * @param string $sql
         * @param array $bindings
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function orderByRaw($sql, $bindings = array())
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->orderByRaw($sql, $bindings);
        }

        /**
         * Alias to set the "offset" value of the query.
         *
         * @param int $value
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function skip($value)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->skip($value);
        }

        /**
         * Set the "offset" value of the query.
         *
         * @param int $value
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function offset($value)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->offset($value);
        }

        /**
         * Alias to set the "limit" value of the query.
         *
         * @param int $value
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function take($value)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->take($value);
        }

        /**
         * Set the "limit" value of the query.
         *
         * @param int $value
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function limit($value)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->limit($value);
        }

        /**
         * Set the limit and offset for a given page.
         *
         * @param int $page
         * @param int $perPage
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function forPage($page, $perPage = 15)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->forPage($page, $perPage);
        }

        /**
         * Constrain the query to the previous "page" of results before a given ID.
         *
         * @param int $perPage
         * @param int|null $lastId
         * @param string $column
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function forPageBeforeId($perPage = 15, $lastId = 0, $column = 'id')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->forPageBeforeId($perPage, $lastId, $column);
        }

        /**
         * Constrain the query to the next "page" of results after a given ID.
         *
         * @param int $perPage
         * @param int|null $lastId
         * @param string $column
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function forPageAfterId($perPage = 15, $lastId = 0, $column = 'id')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->forPageAfterId($perPage, $lastId, $column);
        }

        /**
         * Add a union statement to the query.
         *
         * @param \Illuminate\Database\Query\Builder|\Closure $query
         * @param bool $all
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function union($query, $all = false)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->union($query, $all);
        }

        /**
         * Add a union all statement to the query.
         *
         * @param \Illuminate\Database\Query\Builder|\Closure $query
         * @return \Illuminate\Database\Query\Builder|static
         * @static
         */
        public static function unionAll($query)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->unionAll($query);
        }

        /**
         * Lock the selected rows in the table.
         *
         * @param string|bool $value
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function lock($value = true)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->lock($value);
        }

        /**
         * Lock the selected rows in the table for updating.
         *
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function lockForUpdate()
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->lockForUpdate();
        }

        /**
         * Share lock the selected rows in the table.
         *
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function sharedLock()
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->sharedLock();
        }

        /**
         * Get the SQL representation of the query.
         *
         * @return string
         * @static
         */
        public static function toSql()
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->toSql();
        }

        /**
         * Get the count of the total records for the paginator.
         *
         * @param array $columns
         * @return int
         * @static
         */
        public static function getCountForPagination($columns = array())
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->getCountForPagination($columns);
        }

        /**
         * Concatenate values of a given column as a string.
         *
         * @param string $column
         * @param string $glue
         * @return string
         * @static
         */
        public static function implode($column, $glue = '')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->implode($column, $glue);
        }

        /**
         * Determine if any rows exist for the current query.
         *
         * @return bool
         * @static
         */
        public static function exists()
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->exists();
        }

        /**
         * Determine if no rows exist for the current query.
         *
         * @return bool
         * @static
         */
        public static function doesntExist()
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->doesntExist();
        }

        /**
         * Retrieve the "count" result of the query.
         *
         * @param string $columns
         * @return int
         * @static
         */
        public static function count($columns = '*')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->count($columns);
        }

        /**
         * Retrieve the minimum value of a given column.
         *
         * @param string $column
         * @return mixed
         * @static
         */
        public static function min($column)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->min($column);
        }

        /**
         * Retrieve the maximum value of a given column.
         *
         * @param string $column
         * @return mixed
         * @static
         */
        public static function max($column)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->max($column);
        }

        /**
         * Retrieve the sum of the values of a given column.
         *
         * @param string $column
         * @return mixed
         * @static
         */
        public static function sum($column)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->sum($column);
        }

        /**
         * Retrieve the average of the values of a given column.
         *
         * @param string $column
         * @return mixed
         * @static
         */
        public static function avg($column)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->avg($column);
        }

        /**
         * Alias for the "avg" method.
         *
         * @param string $column
         * @return mixed
         * @static
         */
        public static function average($column)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->average($column);
        }

        /**
         * Execute an aggregate function on the database.
         *
         * @param string $function
         * @param array $columns
         * @return mixed
         * @static
         */
        public static function aggregate($function, $columns = array())
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->aggregate($function, $columns);
        }

        /**
         * Execute a numeric aggregate function on the database.
         *
         * @param string $function
         * @param array $columns
         * @return float|int
         * @static
         */
        public static function numericAggregate($function, $columns = array())
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->numericAggregate($function, $columns);
        }

        /**
         * Insert a new record into the database.
         *
         * @param array $values
         * @return bool
         * @static
         */
        public static function insert($values)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->insert($values);
        }

        /**
         * Insert a new record into the database while ignoring errors.
         *
         * @param array $values
         * @return int
         * @static
         */
        public static function insertOrIgnore($values)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->insertOrIgnore($values);
        }

        /**
         * Insert a new record and get the value of the primary key.
         *
         * @param array $values
         * @param string|null $sequence
         * @return int
         * @static
         */
        public static function insertGetId($values, $sequence = null)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->insertGetId($values, $sequence);
        }

        /**
         * Insert new records into the table using a subquery.
         *
         * @param array $columns
         * @param \Closure|\Illuminate\Database\Query\Builder|string $query
         * @return bool
         * @static
         */
        public static function insertUsing($columns, $query)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->insertUsing($columns, $query);
        }

        /**
         * Insert or update a record matching the attributes, and fill it with values.
         *
         * @param array $attributes
         * @param array $values
         * @return bool
         * @static
         */
        public static function updateOrInsert($attributes, $values = array())
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->updateOrInsert($attributes, $values);
        }

        /**
         * Run a truncate statement on the table.
         *
         * @return void
         * @static
         */
        public static function truncate()
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            $instance->truncate();
        }

        /**
         * Create a raw database expression.
         *
         * @param mixed $value
         * @return \Illuminate\Database\Query\Expression
         * @static
         */
        public static function raw($value)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->raw($value);
        }

        /**
         * Get the current query value bindings in a flattened array.
         *
         * @return array
         * @static
         */
        public static function getBindings()
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->getBindings();
        }

        /**
         * Get the raw array of bindings.
         *
         * @return array
         * @static
         */
        public static function getRawBindings()
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->getRawBindings();
        }

        /**
         * Set the bindings on the query builder.
         *
         * @param array $bindings
         * @param string $type
         * @return \Illuminate\Database\Query\Builder
         * @throws \InvalidArgumentException
         * @static
         */
        public static function setBindings($bindings, $type = 'where')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->setBindings($bindings, $type);
        }

        /**
         * Add a binding to the query.
         *
         * @param mixed $value
         * @param string $type
         * @return \Illuminate\Database\Query\Builder
         * @throws \InvalidArgumentException
         * @static
         */
        public static function addBinding($value, $type = 'where')
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->addBinding($value, $type);
        }

        /**
         * Merge an array of bindings into our bindings.
         *
         * @param \Illuminate\Database\Query\Builder $query
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function mergeBindings($query)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->mergeBindings($query);
        }

        /**
         * Get the database query processor instance.
         *
         * @return \Illuminate\Database\Query\Processors\Processor
         * @static
         */
        public static function getProcessor()
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->getProcessor();
        }

        /**
         * Get the query grammar instance.
         *
         * @return \Illuminate\Database\Query\Grammars\Grammar
         * @static
         */
        public static function getGrammar()
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->getGrammar();
        }

        /**
         * Use the write pdo for query.
         *
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function useWritePdo()
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->useWritePdo();
        }

        /**
         * Clone the query without the given properties.
         *
         * @param array $properties
         * @return static
         * @static
         */
        public static function cloneWithout($properties)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->cloneWithout($properties);
        }

        /**
         * Clone the query without the given bindings.
         *
         * @param array $except
         * @return static
         * @static
         */
        public static function cloneWithoutBindings($except)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->cloneWithoutBindings($except);
        }

        /**
         * Dump the current SQL and bindings.
         *
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function dump()
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->dump();
        }

        /**
         * Die and dump the current SQL and bindings.
         *
         * @return void
         * @static
         */
        public static function dd()
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            $instance->dd();
        }

        /**
         * Register a custom macro.
         *
         * @param string $name
         * @param object|callable $macro
         * @return void
         * @static
         */
        public static function macro($name, $macro)
        {
            \Illuminate\Database\Query\Builder::macro($name, $macro);
        }

        /**
         * Mix another object into the class.
         *
         * @param object $mixin
         * @param bool $replace
         * @return void
         * @throws \ReflectionException
         * @static
         */
        public static function mixin($mixin, $replace = true)
        {
            \Illuminate\Database\Query\Builder::mixin($mixin, $replace);
        }

        /**
         * Checks if macro is registered.
         *
         * @param string $name
         * @return bool
         * @static
         */
        public static function hasMacro($name)
        {
            return \Illuminate\Database\Query\Builder::hasMacro($name);
        }

        /**
         * Dynamically handle calls to the class.
         *
         * @param string $method
         * @param array $parameters
         * @return mixed
         * @throws \BadMethodCallException
         * @static
         */
        public static function macroCall($method, $parameters)
        {
            /** @var \Illuminate\Database\Query\Builder $instance */
            return $instance->macroCall($method, $parameters);
        }

        /**
         * Define a one-to-many relationship.
         *
         * @param string $related
         * @param string|null $foreignKey
         * @param string|null $localKey
         * @return HasManySyncable
         */
        public static function hasMany($related, $foreignKey = null, $localKey = null)
        {
//            /** @var EloquentModel $instance */
//            return $instance->newHasMany();
        }
    }
    class Event extends \Illuminate\Support\Facades\Event {}

    class File extends \Illuminate\Support\Facades\File {}
    class Request extends \Illuminate\Support\Facades\Request {}
    class Schema extends \Illuminate\Support\Facades\Schema {}
}

namespace Illuminate\Support {
    /**
     * Methods commonly used in migrations
     *
     * @method Fluent after(string $column) Add the after modifier
     * @method Fluent charset(string $charset) Add the character set modifier
     * @method Fluent collation(string $collation) Add the collation modifier
     * @method Fluent comment(string $comment) Add comment
     * @method Fluent default($value) Add the default modifier
     * @method Fluent first() Select first row
     * @method Fluent index(string $name = null) Add the in dex clause
     * @method Fluent on(string $table) `on` of a foreign key
     * @method Fluent onDelete(string $action) `on delete` of a foreign key
     * @method Fluent onUpdate(string $action) `on update` of a foreign key
     * @method Fluent primary() Add the primary key modifier
     * @method Fluent references(string $column) `references` of a foreign key
     * @method Fluent nullable(bool $value = true) Add the nullable modifier
     * @method Fluent unique(string $name = null) Add unique index clause
     * @method Fluent unsigned() Add the unsigned modifier
     * @method Fluent useCurrent() Add the default timestamp value
     * @method Fluent change() Add the change modifier
     */
    class Fluent {}
}

namespace Illuminate\Database\Schema {

    use Illuminate\Database\Query\Expression;

    /**
     * @method after(string $column) Place the column "after" another column (MySQL)
     * @method always() Used as a modifier for generatedAs() (PostgreSQL)
     * @method static autoIncrement() Set INTEGER columns as auto-increment (primary key)
     * @method static change() Change the column
     * @method static charset(string $charset) Specify a character set for the column (MySQL)
     * @method static collation(string $collation) Specify a collation for the column (MySQL/PostgreSQL/SQL Server)
     * @method static comment(string $comment) Add a comment to the column (MySQL)
     * @method static default(mixed $value) Specify a "default" value for the column
     * @method static first() Place the column "first" in the table (MySQL)
     * @method static generatedAs(string|Expression $expression = null) Create a SQL compliant identity column (PostgreSQL)
     * @method static index(string $indexName = null) Add an index
     * @method nullable(bool $value = true) Allow NULL values to be inserted into the column
     * @method static persisted() Mark the computed generated column as persistent (SQL Server)
     * @method static primary() Add a primary index
     * @method static spatialIndex() Add a spatial index
     * @method static storedAs(string $expression) Create a stored generated column (MySQL)
     * @method static unique(string $indexName = null) Add a unique index
     * @method static unsigned() Set the INTEGER column as UNSIGNED (MySQL)
     * @method static useCurrent() Set the TIMESTAMP column to use CURRENT_TIMESTAMP as default value
     * @method  virtualAs(string $expression) Create a virtual generated column (MySQL)
     */
    class ColumnDefinition extends Fluent
    {
        //
    }
}