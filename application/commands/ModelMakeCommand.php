<?php

namespace application\commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Console\Input\InputOption;

class ModelMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Eloquent model class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    protected $ns = 'models';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->option('module')) {
            if (!file_exists(APPPATH . 'modules' . DIRECTORY_SEPARATOR . $this->option('module'))) {
                throw new RuntimeException('Unable to detect application namespace.');
            }
            $this->ns = 'modules\\' . $this->option('module') . '\\models';
        }

        if (parent::handle() === false && !$this->option('force')) {
            return false;
        }

        if ($this->option('migration')) {
            $this->createMigration();
        }

    }

    /**
     * Create a migration file for the model.
     *
     * @return void
     */
    protected function createMigration()
    {
        $table = $this->getTable();

        $this->call('make:migration', [
            'name' => "create_{$table}_table",
            '--create' => $table,
        ]);
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)->replaceTable($stub,$name)->replaceClass($stub, $name);
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceTable(&$stub, $name)
    {
        $table = $this->getTable();

        $stub = str_replace(['{{ table }}', '{{table}}'], $table, $stub);
        return $this;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('pivot')) {
            return __DIR__ . '/stubs/pivot.model.stub';
        }

        return __DIR__ . '/stubs/model.stub';
    }

    protected function getTable()
    {
        $table = Str::snake(Str::pluralStudly(class_basename($this->argument('name'))));
        if ($this->option('pivot')) {
            return Str::singular($table);
        }

        return Str::plural($table);
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\' . $this->ns;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['module', 'c', InputOption::VALUE_OPTIONAL, 'Create model in module'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
            ['migration', 'm', InputOption::VALUE_NONE, 'Create a new migration file for the model'],
            [
                'pivot',
                'p',
                InputOption::VALUE_NONE,
                'Indicates if the generated model should be a custom intermediate table model'
            ],
        ];
    }

}
