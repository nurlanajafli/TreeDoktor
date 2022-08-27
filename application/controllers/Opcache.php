<?php

use Appstract\Opcache\OpcacheFacade;

class Opcache extends \MX_Controller
{

    /**
     * Clear the OPcache.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear()
    {
        //return response()->json(['result' => OPcache::clear()]);
        $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode(['result' => OpcacheFacade::clear()]));
    }

    /**
     * Get config values.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function config()
    {
        //return response()->json(['result' => OPcache::getConfig()]);
        $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode(['result' => OpcacheFacade::getConfig()]));
    }

    /**
     * Get status info.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function status()
    {
        //return response()->json(['result' => OPcache::getStatus()]);
        $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode(['result' => OpcacheFacade::getStatus()]));
    }

    /**
     * Compile.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function compile()
    {
        $request = request();
        //return response()->json(['result' => OPcache::compile($request->get('force'))]);
        $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode(['result' => OpcacheFacade::compile($request->get('force'))]));
    }
}