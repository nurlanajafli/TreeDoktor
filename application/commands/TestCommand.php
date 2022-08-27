<?php


namespace application\commands;

use application\core\Console\Command;
use ElephantIO\Client as WSClient;
use ElephantIO\Engine\SocketIO\Version1X;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:smile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test :)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $context = stream_context_create();
        stream_context_set_option($context, 'ssl', 'verify_peer', false);
        stream_context_set_option($context, 'ssl', 'verify_peer_name', false);
////
//        $client = new Client("wss://arbostar.loc:8895/",['context' => $context]);
//        $client->send("Hello WebSocket.org!");
//        $client->close();
        $CI = &get_instance();
        //echo $CI->config->item('wsClient');
        if ($CI->config->item('wsClient')) {
            $wsClient = new WsClient(new Version1X($CI->config->item('wsClient') . '?chat=1', [
//                'version' => 3,
//                'transport' => Version1X::TRANSPORT_WEBSOCKET,
                'context' => ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]
            ]));
            if ($wsClient) {
                $wsClient->initialize();
                $wsClient->emit('messaging', ['method' => 'test']);
                $wsClient->close();
            }
        }

        $this->output->text('                          oooo$$$$$$$$$$$$oooo');
        $this->output->text('                      oo$$$$$$$$$$$$$$$$$$$$$$$$o');
        $this->output->text('                   oo$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$o         o$   $$ o$');
        $this->output->text('   o $ oo        o$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$o       $$ $$ $$o$');
        $this->output->text('oo $ $ "$      o$$$$$$$$$    $$$$$$$$$$$$$    $$$$$$$$$o       $$$o$$o$');
        $this->output->text('"$$$$$$o$     o$$$$$$$$$      $$$$$$$$$$$      $$$$$$$$$$o    $$$$$$$$');
        $this->output->text('  $$$$$$$    $$$$$$$$$$$      $$$$$$$$$$$      $$$$$$$$$$$$$$$$$$$$$$$');
        $this->output->text('  $$$$$$$$$$$$$$$$$$$$$$$    $$$$$$$$$$$$$    $$$$$$$$$$$$$$  """$$$');
        $this->output->text('   "$$$""""$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$     "$$$');
        $this->output->text('    $$$   o$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$     "$$$o');
        $this->output->text('   o$$"   $$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$       $$$o');
        $this->output->text('   $$$    $$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$" "$$$$$$ooooo$$$$o');
        $this->output->text('  o$$$oooo$$$$$  $$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$   o$$$$$$$$$$$$$$$$$');
        $this->output->text('  $$$$$$$$"$$$$   $$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$     $$$$""""""""');
        $this->output->text(' """"       $$$$    "$$$$$$$$$$$$$$$$$$$$$$$$$$$$"      o$$$');
        $this->output->text('            "$$$o     """$$$$$$$$$$$$$$$$$$"$$"         $$$');
        $this->output->text('              $$$o          "$$""$$$$$$""""           o$$$');
        $this->output->text('               $$$$o                                o$$$"');
        $this->output->text('                "$$$$o      o$$$$$$o"$$$$o        o$$$$');
        $this->output->text('                  "$$$$$oo     ""$$$$o$$$$$o   o$$$$""');
        $this->output->text('                     ""$$$$$oooo  "$$$o$$$$$$$$$"""');
        $this->output->text('                        ""$$$$$$$oo $$$$$$$$$$');
        $this->output->text('                                """"$$$$$$$$$$$');
        $this->output->text('                                    $$$$$$$$$$$$');
        $this->output->text('                                     $$$$$$$$$$"');
        $this->output->text('                                      "$$$""  ');
    }
}