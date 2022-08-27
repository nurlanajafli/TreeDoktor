<?php


namespace application\core\Console;


use Symfony\Component\Console\Input\ArgvInput as SymfontArgvInput;
use Symfony\Component\Console\Input\InputDefinition;

class ArgvInput extends SymfontArgvInput
{
    public function __construct(array $argv = null, InputDefinition $definition = null)
    {
        if (null === $argv) {
            $argv = $_SERVER['argv'];
        }

        // strip the index.php name
        array_shift($argv);

        parent::__construct($argv, $definition);
    }
}