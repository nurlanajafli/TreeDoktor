<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

//require APPPATH . '/libraries/JWT.php';
use ElephantIO\Client as WSClient;
use ElephantIO\Engine\SocketIO\Version1X;

class Gps extends APP_Controller
{
    var $wsClient;

    function __construct()
    {
        parent::__construct();

        $this->load->model('mdl_users_tracking');
        $this->load->model('mdl_user');
    }

    /**
     * @return void
     */
    function save()
    {
        if (!is_array($_POST) || !isset($_POST[0]) || !isset($_POST[0]['lat']) || !isset($_POST[0]['lng'])) {
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Empty Data',
                'data' => json_encode($_POST)
            ), 200);
        }

        $coords = $this->input->post();
        $userId = isset($this->user) && isset($this->user->id) && $this->user->id ? $this->user->id : 0;
        $userColor = isset($this->user) && isset($this->user->color) && $this->user->color ? $this->user->color : '#ffffff';
        $data = $dateTimes = [];

        /***TODO: remove it before release***/
        if ($userId == 19) {
            $fp = fopen('/tmp/coords', 'a+');
            fwrite($fp, json_encode($coords, JSON_PRETTY_PRINT) . "\r\n");
            fclose($fp);
        }
        /***TODO: remove it before release***/

        foreach ($coords as $cord) {
            $lat = isset($cord['lat']) && $cord['lat'] ? $cord['lat'] : null;
            $lon = isset($cord['lng']) && $cord['lng'] ? $cord['lng'] : null;
            $dTime = isset($cord['time']) && $cord['time'] ? $cord['time'] : null;

            if (empty($lat) || empty($lon) || empty($dTime)) {
                continue;
            }

            $dateTime = false;

            if ((float)$dTime == $dTime) {
                $dateTime = new DateTime();
                $dateTime = $dateTime->setTimestamp((int)($dTime / 1000));
            } else {
                $dateTime = DateTime::createFromFormat('Y-m-d H:i:s O', $dTime);
            }

            if (!$dateTime) {
                $dateTime = new DateTime();
            }

            $dateTime = $dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('Y-m-d H:i:s');

            if (in_array($dateTime, $dateTimes)) {
                continue;
            }

            $dateTimes[] = $dateTime;

            $data[] = [
                'ut_user_id' => $userId,
                'ut_date' => $dateTime,
                'ut_lat' => $lat,
                'ut_lng' => $lon,
            ];
        }

        if ($data && count($data)) {
            $ins_id = $this->mdl_users_tracking->insertBatch($data);

            $sicketData = [
                'ut_user_id' => $userId,
                'ut_date' => $dateTime,
                'ut_lat' => $lat,
                'ut_lng' => $lon,
                'lastname' => $this->user->lastname,
                'firstname' => $this->user->lastname,
            ];
            $data['firstname'] = $this->user->firstname;
            $data['lastname'] = $this->user->lastname;
            $data['ut_id'] = $ins_id;
            $data['ut_user_id'] = $userId;
            $data['ut_lng'] = $lon;
            $data['ut_lat'] = $lat;
            $data['ut_date'] = $dateTime;
            $data['color'] = $userColor;
            $this->_socketWrite('newTrackingData', $data);
        }

        return $this->response([
            'status' => TRUE,
            'data' => []
        ], 200);
    }

    function create()
    {
        $location = $this->input->post("location");

        if (!is_array($location) || empty($location['coords'])) {
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Empty Data'
            ), 200);
        }

        $coords = $location['coords'];
        $userId = isset($this->user) && isset($this->user->id) && $this->user->id ? $this->user->id : 0;
        $userColor = isset($this->user) && isset($this->user->color) && $this->user->color ? $this->user->color : '#ffffff';
        $data = [];

        /***TODO: remove it before release***/
        if ($userId == 19) {
            $fp = fopen('/tmp/coords', 'a+');
            fwrite($fp, json_encode($coords, JSON_PRETTY_PRINT) . "\r\n");
            fclose($fp);
        }
        /***TODO: remove it before release***/
        $lat = $coords['latitude'] ?? null;
        $lon = $coords['longitude'] ?? null;
        $dTime = $location['timestamp'] ?? null;

        $dateTime = DateTime::createFromFormat('Y-m-d\TH:i:s.uP', $dTime);

        if (!$dateTime) {
            $dateTime = new DateTime();
        }

        $dateTime = $dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('Y-m-d H:i:s');

        $data[] = [
            'ut_user_id' => $userId,
            'ut_date' => $dateTime,
            'ut_lat' => $lat,
            'ut_lng' => $lon,
        ];


        if ($data && count($data)) {
            $ins_id = $this->mdl_users_tracking->insertBatch($data);

            $data['firstname'] = $this->user->firstname;
            $data['lastname'] = $this->user->lastname;
            $data['ut_id'] = $ins_id;
            $data['ut_user_id'] = $userId;
            $data['ut_lng'] = $lon;
            $data['ut_lat'] = $lat;
            $data['ut_date'] = $dateTime;
            $data['color'] = $userColor;
            $this->_socketWrite('newTrackingData', $data);
        }

        return $this->response([
            'status' => TRUE,
            'data' => []
        ], 200);
    }

    function log()
    {
        if (file_exists('/tmp/coords'))
            echo file_get_contents('/tmp/coords');
    }

    function clearLog()
    {
        if (file_exists('/tmp/coords'))
            file_put_contents('/tmp/coords', '');
    }

    function show($user_id = false)
    {
        if (!$user_id)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'User ID Required'
            ), 200);

        $records = $this->mdl_users_tracking->find_all(['ut_user_id' => $user_id], 'ut_date DESC');

        return $this->response(array(
            'status' => TRUE,
            'data' => $records
        ), 200);
    }

    function show_latest()
    {

        $records = $this->mdl_users_tracking->find_all_latest();

        return $this->response(array(
            'status' => TRUE,
            'data' => $records
        ), 200);
    }

    function clear($user_id = false)
    {
        if (!$user_id)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'User ID Required'
            ), 200);

        $this->mdl_users_tracking->delete_where(['ut_user_id' => $user_id]);

        return $this->response(array(
            'status' => TRUE,
            'data' => []
        ), 200);
    }

    private function _socketWrite($method, $params = [])
    {

        if ($this->config->item('wsClient')) {

            $wsClient = new WSClient(new Version1X($this->config->item('wsClient')));
            $wsClient->initialize();
            $wsClient->emit('room', ['chat']);
            $wsClient->emit('message', ['method' => $method, 'params' => $params]);
            $wsClient->close();

        }
    }
}
