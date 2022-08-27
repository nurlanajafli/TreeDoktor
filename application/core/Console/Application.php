<?php


namespace application\core\Console;


use Illuminate\Console\Events\ArtisanStarting;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Exception\NamespaceNotFoundException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends \Illuminate\Console\Application
{

    /**
     * Create a new Artisan console application.
     *
     * @param \Illuminate\Contracts\Container\Container $laravel
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     * @param string $version
     * @return void
     */
    public function __construct(Container $laravel, Dispatcher $events, $version)
    {
        SymfonyApplication::__construct('CI Application', $version);

        $this->laravel = $laravel;
        $this->events = $events;
        $this->setAutoExit(false);
        $this->setCatchExceptions(false);

        $this->events->dispatch(new ArtisanStarting($this));

        $this->bootstrap();
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        // rewrite input Input :)
        $input = $input ?: new ArgvInput;
        try {
            parent::run($input, $output);
        } catch (\Exception $e){
            if (null === $output) {
                $console = new ConsoleOutput();
                $this->configureIO($input, $console);
                $out = new OutputStyle($input, $console);
            }
            if($e instanceof NamespaceNotFoundException){
                $errs = explode("\n",$e->getMessage());
                $out->warning(array_shift($errs));
                $out->block($errs);
            } else {
                $_error =& load_class('Exceptions', 'core');
                $_error->show_exception($e);
            }

        }
    }
}