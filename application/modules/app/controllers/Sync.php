<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Sync extends APP_Controller
{

	function __construct() {
		parent::__construct();
	}

	function index() {
        $accepted = $notAccepted = [];
	    $requests = $this->input->post('requests');
	    $requests = is_array($requests) ? $requests : [];
	    foreach ($requests as $request) {
	        if(!isset($request['url']) || strpos($request['url'], base_url('app')) === FALSE) {
                $notAccepted[$request['id']] = 'Incorrect Request URL';
                continue;
            }
            if(!isset($request['id']) || !$request['id']) {
                $notAccepted[] = 'Request ID is required';
                continue;
            }
			$request['url'] = rtrim($request['url'],"/");

	        $route = $this->_parse_routes(explode('/', ltrim(str_replace(base_url(), '', $request['url']), '/')));

            if(!$route) {
                $notAccepted[$request['id']] = 'Invalid Route';
                continue;
            }
            $uriArgs = $route['uri_args'] ?? [];
            $requestDate = date('Y-m-d H:i:s', strtotime($request['created_at'] . ' ' . $this->input->post('gmt')));
            $requestBody = $request['obj'] ?? [];

	        $driverClassName = $route['controller'] . '_' . $route['method'];
	        $jobDriverPath = $route['module'] . '/offline/' . $driverClassName;

            $jobId = pushJob($jobDriverPath, [
                'id' => $request['id'],
                'user_id' => $this->user->id,
                'date' => $requestDate,
                'route' => $route,
                'body' => $requestBody,
            ]);
            if($jobId)
                $accepted[] = $request['id'];
            else
                $notAccepted[$request['id']] = 'Sync Driver Not Found';
        }

        return $this->response([
            'status' => TRUE,
            'data' => [
                'accepted' => $accepted,
                'not_accepted' => $notAccepted,
            ],
        ]);
	}

	private function _parse_routes($uri)
    {
        // Turn the segment array into a URI string
        $uri = implode('/', $uri);

        // Is there a literal match?  If so we're done
        if (isset(CI::$APP->router->routes[$uri]))
        {
            list($module, $controller, $method) = explode('/', CI::$APP->router->routes[$uri]);
            return [
                'module' => $module,
                'controller' => $controller,
                'method' => $method,
                'uri' => $uri
            ];
        }

        // Loop through the route array looking for wild-cards
        foreach (CI::$APP->router->routes as $key => $val)
        {
            // Convert wild-cards to RegEx
            $key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $key));

            // Does the RegEx match?
            if (preg_match('#^'.$key.'$#', $uri))
            {
                list($module, $controller, $method) = explode('/', $val);
                return [
                    'module' => $module,
                    'controller' => $controller,
                    'method' => $method,
                    'uri' => rtrim(str_replace('(.+)', '', str_replace('([0-9]+)', '', $key)), '/'),
                    'uri_args' => explode('/', str_replace($module . '/' . $controller . '/' . $method . '/', '', preg_replace('#^'.$key.'$#', $val, $uri)))
                ];
            }
        }

        return FALSE;
    }
    /*
    function get() {
        $path = 'uploads/appsync.txt';
        $data = file_get_contents($path);
        $obj = [];
        if($data) {
            $obj = json_decode($data);
        }

        return $this->response(array(
            'status' => TRUE,
            'data' => $obj
        ), 200);
    }

    function clear() {
        $path = 'uploads/appsync.txt';
        $data = file_put_contents($path, '');
        return $this->response(array(
            'status' => TRUE,
            'data' => []
        ), 200);
    }
    */
}
