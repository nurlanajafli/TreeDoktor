<?php


use application\core\Application;
use application\core\Console\Application as ConsoleApplication;
use Barryvdh\Debugbar\Support\Clockwork\Converter;
use DebugBar\OpenHandler;

/**
 * Class Console
 * @property Application $container
 * @property ConsoleApplication $app
 */
class Debugbar extends MX_Controller
{

    protected $debugbar;

    public function __construct()
    {
        parent::__construct();
        $this->debugbar = app('debugbar');
        $this->debugbar->boot();
    }

    public function handle()
    {
        $openHandler = new OpenHandler($this->debugbar);
        $data = $openHandler->handle($_GET, false, false);
        $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output($data);

    }

    public function clockwork($id)
    {
        $request = [
            'op' => 'get',
            'id' => $id,
        ];

        $openHandler = new OpenHandler($this->debugbar);
        $data = $openHandler->handle($request, false, false);

        // Convert to Clockwork
        $converter = new Converter();
        $output = $converter->convert(json_decode($data, true));

        return $this->response($output, 200);
    }

    /**
     * Return the javascript for the Debugbar
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function js()
    {
        $renderer = $this->debugbar->getJavascriptRenderer();

        $content = $renderer->dumpAssetsToString('js');
        die($this->output
            ->set_content_type('text/javascript')
            ->set_status_header(200)
            ->set_output($content)
        ->_display());
    }

    /**
     * Return the stylesheets for the Debugbar
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function css()
    {
        $renderer = $this->debugbar->getJavascriptRenderer();

        $content = $renderer->dumpAssetsToString('css');

        die($this->output
            ->set_content_type('text/css')
            ->set_status_header(200)
            ->set_output($content)
            ->_display());
    }
    /**
     * Forget a cache key
     *
     */
    public function delete($key, $tags = '')
    {
        $cache = app('cache');

        if (!empty($tags)) {
            $tags = json_decode($tags, true);
            $cache = $cache->tags($tags);
        } else {
            unset($tags);
        }

        $success = $cache->forget($key);

        return json_out(['success' => $success]);
    }

//    public function show($uuid)
//    {
//
//        $entry = app()->find($uuid);
//        $result = $storage->get('request', (new EntryQueryOptions())->batchId($entry->batchId))->first();
//
//        return redirect(config('telescope.path') . '/requests/' . $result->id);
//    }
}
