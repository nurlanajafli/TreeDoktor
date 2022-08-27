<?php


namespace application\core\Console;

use Illuminate\Console\Command as IlluminateCommand;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends IlluminateCommand
{

    /**
     * Run the console command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->output = new OutputStyle($input, $output);

        return parent::run(
            $this->input = $input, $this->output
        );
    }

    /**
     * Execute the console command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $return = $this->handle();
        if ($return === false) {
            $this->output->error('Error');
        } //@todo complete output all errors!
        return 1;
    }

}