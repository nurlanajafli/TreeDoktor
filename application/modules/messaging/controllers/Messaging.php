<?php

use application\modules\clients\models\ClientsContact;
use application\modules\employees\models\Employee;
use application\modules\messaging\models\Messages;
use application\modules\messaging\models\SmsCounter;
use Illuminate\Http\Request;

class Messaging extends MX_Controller
{

//*******************************************************************************************************************
//*************
//*************
//*************											Messaging Controller
//*************
//*************
//*******************************************************************************************************************

    protected $phoneCleanLength;

    protected $phoneCountryCode;

    protected $smsMessagesShowLimit;

    protected $smsChatsShowLimit;

    private $request;

    private $requestError;

    function __construct()
    {
        parent::__construct();
        $this->load->driver('messages');

        $this->phoneCleanLength = (int)config_item('phone_clean_length');
        $this->phoneCountryCode = config_item('phone_country_code');
        $this->smsMessagesShowLimit = (int)config_item('sms_messages_show_limit');
        $this->smsChatsShowLimit = (int)config_item('sms_chats_show_limit');

        try {
            $this->request = app(Request::class);
        } catch (Exception $e) {
            $this->request = null;
            $this->requestError = $e->getMessage();
        }
    }

    public function _remap($method, $params = array()) {
        if (!$this->request) {
            $data = [
                'status' => 'error',
                'error' => 'Unexpected error',
                'debug' => $this->requestError ?? null
            ];

            return $this->response($data);
        }

        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $params);
        }

        show_404();
    }

    public function callback($driver, $method = 'callback')
    {
        $driver = str_replace('_', '/', $driver);
        if (!$this->messages->validateAdapter($driver)) {
            show_404();
        }
        $this->messages->setAdapter($driver);
        return $this->messages->{$method}($this->input->post());
    }

    public function ajax_open()
    {
        $params = $this->request->get('params');
        $mode = $params['mode'] ?? 'all';
        $offset = (int)$params['offset'] ?? 0;

        try {
            $result = $this->_getSmsChatBoxes($mode, $offset);
        }
        catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->successResponse([
            'result' => [
                'mode' => $mode,
                'offset' => $offset,
                'rows' => $result
            ]
        ]);
    }

    public function ajax_get_count_unread()
    {
        $this->load->helper('message');
        $count = get_count_unreaded_sms();

        return $this->successResponse(['result' => $count]);
    }

    public function ajax_get_user_sms_limit()
    {
        $this->load->helper('message');
        $limit = get_user_sms_limit();

        return $this->successResponse(['result' => $limit]);
    }

    public function ajax_get_users()
    {
        $employees = Employee::select([
                DB::raw('SUBSTR(emp_phone, 1, ' . $this->phoneCleanLength . ') as id'),
                DB::raw('CONCAT(firstname, " ", lastname) as text')
            ])
            ->leftJoin('users', 'users.id', '=', 'employees.emp_user_id')
            ->whereNotNull('emp_phone')
            ->where('emp_phone', '<>', '')
            ->where(DB::raw('LENGTH(emp_phone)'), '>=', $this->phoneCleanLength)
            ->where('active_status', '=', 'yes')
            ->groupBy(['emp_phone', DB::raw('CONCAT(firstname, " ", lastname)')])
            ->orderBy('text')
            ->get();

        return $this->successResponse(['result' => ['rows' => $employees]]);
    }

    public function ajax_get_history()
    {
        $params = $this->request->get('params');

        if (!array_key_exists('number', $params)) {
            return $this->errorResponse('No number');
        }

        $number = (int)$params['number'];

        // strlen($number) > 16 - according to E.164 standard max 15 digits, plus (+) symbol
        if (strlen($number) < $this->phoneCleanLength || strlen($number) > 16) {
            return $this->errorResponse('Invalid number');
        }
        elseif (strlen($number) - strlen((int)$this->phoneCountryCode) == $this->phoneCleanLength) {
            $number = ltrim($number, (int)$this->phoneCountryCode);
        }

        $offset = (int)$params['offset'] ?? 0;
        $suffix = $params['suffix'] ?? '';
        $lastId = $params['lastId'] ?? null;
        $result = $this->_getHistory($number, $offset, $lastId);

        return $this->successResponse([
            'result' => [
                'number' => $number,
                'offset' => $offset,
                'suffix' => $suffix,
                'rows' => $result,
            ]
        ]);
    }

    public function ajax_get_contacts()
    {
        $params = $this->request->get('params');
        $mode = $params['mode'] ?? 'all';
        $search = $params['search'] ?? '';

        // remove phone country code if exists for clients search
        $search = ltrim($search, $this->phoneCountryCode);

        if (in_array($mode, ['clients', 'supportchat', 'all', ''])) {
            $sub = ClientsContact::select([
                DB::raw('cc_phone_clean as id'),
                DB::raw('cc_name as text'),
                DB::raw('CONCAT(clients.client_address, ", ", clients.client_city) as address'),
                DB::raw('"client" as type'),
            ])
                ->leftJoin('clients', 'clients.client_id', '=', 'clients_contacts.cc_client_id')
                ->whereNotNull('cc_phone_clean')
                ->where('cc_phone_clean', '<>', '')
                ->where(DB::raw('LENGTH(cc_phone_clean)'), '>=', $this->phoneCleanLength)
                ->whereNotNull('cc_name')
                ->whereRaw('LENGTH(cc_name) > 1')
                ->where('cc_name', '<>', '')
                ->whereRaw("(cc_name LIKE '%" . $search . "%' OR cc_phone_clean LIKE '" . $search . "%') ")
                ->groupBy(['cc_phone_clean', 'cc_name']);
            //->orderBy('text','ASC');
        }

        if (in_array($mode, ['users', 'all', ''])) {
            $sub2 = Employee::select([
                DB::raw('SUBSTRING(emp_phone, 1, ' . $this->phoneCleanLength . ') as id'),
                DB::raw('CONCAT(firstname, \' \', lastname) as text'),
                DB::raw('"" as address'),
                DB::raw('"user" as type')
            ])
                ->leftJoin('users', 'users.id', '=', 'employees.emp_user_id')
                ->whereNotNull(DB::raw('CONCAT(firstname, \' \', lastname)'))
                ->whereRaw('LENGTH(CONCAT(firstname, \' \', lastname)) > 1')
                ->whereNotNull('emp_phone')
                ->where('emp_phone', '<>', '')
                ->where(DB::raw('LENGTH(emp_phone)'), '>=', $this->phoneCleanLength)
                ->whereRaw("(CONCAT(firstname, ' ', lastname) LIKE '%" . $search . "%' OR emp_phone LIKE '" . $search . "%') ")
                ->groupBy(['emp_phone', DB::raw('CONCAT(firstname, \' \', lastname)')]);
            //->orderBy('text','ASC');
        }

        if ($mode === 'all' || $mode === '') {
            $query = $sub->union($sub2);
        } else {
            $query = $sub ?? $sub2;
        }

        $result = $query->get();

        return $this->successResponse([
            'result' => [
                'search' => $search,
                'rows' => $result,
            ]
        ]);
    }

    public function ajax_refresh_chatboxes() {
        $this->load->helper('message');
        $user_sms_limit = get_user_sms_limit();
        $count_unreaded = get_count_unreaded_sms();

        return $this->successResponse([
            'result' => [
                'user_sms_limit' => $user_sms_limit,
                'count_unreaded' => $count_unreaded
            ]
        ]);
    }

    public function ajax_set_read()
    {
        $params = $this->request->get('params');

        if (!array_key_exists('number', $params)) {
            return $this->errorResponse('No number');
        }

        $number = (int)$params['number'];

        // strlen($number) > 16 - according to E.164 standard max 15 digits, plus (+) symbol
        if (strlen($number) < $this->phoneCleanLength || strlen($number) > 16) {
            return $this->errorResponse('Invalid number');
        }
        elseif (strlen($number) - strlen((int)$this->phoneCountryCode) == $this->phoneCleanLength) {
            $number = ltrim($number, (int)$this->phoneCountryCode);
        }

        $update = Messages::where('sms_number', '=', $number)->update(['sms_readed' => 1]);

        if ($update) {
            $this->load->helper('message');
            $count_unreaded = get_count_unreaded_sms();
            $this->messages->socketNotification([
                'method' => 'refreshChatboxes',
                'params' => [
                    'count_unreaded' => $count_unreaded,
                    'count_unreaded_only' => true
                ]
            ]);
        }

        return $this->successResponse([
            'result' => $number
        ]);
    }

    /**
     * Set unreaded last incoming SMS message by number
     */
    public function ajax_set_sms_unread()
    {
        $params = $this->request->get('params');

        if (!array_key_exists('number', $params)) {
            return $this->errorResponse('No data');
        }

        $number = $params['number'];

        $update = Messages::setUnread($number);

        if ($update) {
            $this->load->helper('message');
            $count_unreaded = get_count_unreaded_sms();
            $this->messages->socketNotification([
                'method' => 'refreshChatboxes',
                'params' => [
                    'count_unreaded' => $count_unreaded
                ]
            ]);

            return $this->successResponse([
                'result' => [
                    'number' => $number
                ]
            ]);
        }

        return $this->errorResponse('Not updated');
    }

    /**
     * send sms message
     */
    public function ajax_send()
    {
        $number = $this->request->get('number');
        $message = $this->request->get('message');

        $params = $this->request->get('params');
        $type = null;

        if ($params) {
            $type = $params['type'] ?? null;
            $number = $params['number'] ?? null;
            $message = $params['message'] ?? null;
        }

        if (!$number) {
            return $this->errorResponse('No phone number');
        }
        if (!$message) {
            return $this->errorResponse('No message');
        }

        try {
            $result = $this->messages->send($number, $message);
        }
        catch (MessagesException $e) {
            return $this->errorResponse('Message not sent. Unexpected error');
        }

        $errors = [];
        $debug = [];

        if (sizeof($result) === 1) {
            if (array_key_exists('error', $result[0])) {
                $data = [
                    'status' => 'error',
                    'error' => $result[0]['error'],
                    'debug' => $result[0]['debug'] ?? null
                ];

                return $this->response($data);
            }
        }
        elseif (sizeof($result) > 1) {
            foreach ($result as $res) {
                if (array_key_exists('error', $res)) {
                    $errors[] = $res['error'];
                }
                if (array_key_exists('debug', $res)) {
                    $debug[] = $res['debug'];
                }
            }

            if (sizeof($result) === sizeof($errors)) {
                $data = [
                    'status' => 'error',
                    'errors' => $errors
                ];

                if (sizeof($debug)) {
                    $data['debug'] = $debug;
                }

                return $this->response($data);
            }
        } else {
            return $this->errorResponse('Message not sent. No result');
        }

        $responseResult = [
            'result' => $result
        ];

        if ($params && $type === 'chat_box') {
            $responseResult['result'] = [
                'number' => $number
            ];
        }

        if (sizeof($errors)) {
            $responseResult['errors'] = $errors;

            if (sizeof($debug)) {
                $responseResult['debug'] = $debug;
            }
        }

        return $this->successResponse($responseResult);
    }

    /**
     * SMS search
     */
    public function ajax_search_sms() {
        $params = $this->request->get('params');

        if (!array_key_exists('search', $params)) {
            return $this->successResponse([
                'result' => [
                    'rows' => [],
                    'offset' => 0
                ]
            ]);
        }

        $search = $params['search'];
        $offset = $params['offset'] ?? 0;

        $sub = Messages::select([
            DB::raw('DISTINCT (sms_number)'),
            DB::raw('MAX(sms_id) as sms_id'),
        ])
            ->leftJoin('clients_contacts', 'clients_contacts.cc_phone_clean', '=',
                DB::raw("SUBSTRING(sms_messages.sms_number, 1, " . $this->phoneCleanLength . ")"))
            ->leftJoin('clients', 'clients_contacts.cc_client_id', '=', 'clients.client_id')
            ->leftJoin('employees', 'employees.emp_phone', '=',
                DB::raw("SUBSTRING(sms_messages.sms_number, 1, " . $this->phoneCleanLength . ")"))
            ->leftJoin('users', 'users.id', '=', 'employees.emp_user_id')
            ->where(DB::raw('LENGTH(sms_messages.sms_number)'), '<=', 15)
            ->where(DB::raw('LENGTH(sms_messages.sms_number)'), '>=', $this->phoneCleanLength)
            ->whereRaw("(cc_name LIKE '%" . $search . "%' 
                OR sms_body LIKE '%" . $search . "%'
                OR sms_number LIKE '%" . $search . "%'
                OR client_name LIKE '%" . $search . "%'
                /*OR CONCAT(firstname, ' ', lastname) LIKE '%" . $search . "%'*/)")
            ->groupBy('sms_number')
            ->orderBy('sms_date', 'DESC')
            ->limit(100);

        $query = Messages::query()
            ->select([
                'sms_messages.sms_id',
                'sms_messages.sms_number',
                'sms_messages.sms_body',
                'sms_messages.sms_date',
                'sms_messages.sms_status',
                'sms_messages.sms_readed',
                DB::raw("CONCAT('[', GROUP_CONCAT(JSON_OBJECT('cc_client_id', cc_client_id, 'cc_name', cc_name)), ']') as cc_data"),
                'users.firstname',
                'users.lastname',
                DB::raw('SUBSTR(employees.emp_phone, 1, ' . $this->phoneCleanLength . ') as emp_phone'),
                'client_name',
                DB::raw("'search' as block")
            ])
            ->leftJoin('clients_contacts', 'clients_contacts.cc_phone_clean', '=',
                DB::raw("SUBSTRING(sms_messages.sms_number, 1, " . $this->phoneCleanLength . ")"))
            ->leftJoin('clients', 'clients_contacts.cc_client_id', '=', 'clients.client_id')
            ->leftJoin('employees', 'employees.emp_phone', '=',
                DB::raw("SUBSTRING(sms_messages.sms_number, 1, " . $this->phoneCleanLength . ")"))
            ->leftJoin('users', 'users.id', '=', 'employees.emp_user_id')
            ->rightJoinSub($sub, 'last_message', 'last_message.sms_id', '=', 'sms_messages.sms_id');

        $queryResult = $query->groupBy('sms_messages.sms_number')
            ->orderBy('sms_date', 'DESC')
            ->limit(100)
            ->offset($offset)
            ->get();

        return $this->successResponse([
            'result' => [
                'rows' => $this->_decodeCCData($queryResult->toArray()),
                'offset' => $offset
            ]
        ]);
    }

    /**
     * Get SMS history
     *
     * @param $number
     * @param int $offset
     * @param int|null $lastId
     * @return mixed
     */
    private function _getHistory($number, $offset = 0, $lastId = null)
    {
        return Messages::select([
            'sms_id',
            'sms_number',
            'sms_body',
            'sms_date',
            'sms_support',
            'sms_readed',
            'sms_user_id',
            'sms_incoming',
            'sms_status',
            'sms_error',
            DB::raw('CONCAT(users.firstname, " ", users.lastname) as sender_name')
        ])
            ->leftJoin('users', function ($join) {
                $join->on('users.id', '=', 'sms_messages.sms_user_id')->where('sms_messages.sms_user_id', '>', 1);
            })
            ->where('sms_number', '=', $number)
            ->fromSmsId($lastId)
            ->orderBy('sms_date', 'DESC')
            ->limit($this->smsMessagesShowLimit)
            ->offset($offset)
            ->get();
    }

    /**
     * Get SMS chatboxes
     *
     * @param string $mode
     * @param int $offset
     * @param null $number
     * @return array
     */
    private function _getSmsChatBoxes($mode = '', $offset = 0, $number = null)
    {
        if ($number) {
            // strlen($number) > 16 - according to E.164 standard max 15 digits, plus (+) symbol
            if (strlen($number) < $this->phoneCleanLength || strlen($number) > 16) {
                $number = null;
            }
            elseif (strlen($number) - strlen((int)$this->phoneCountryCode) == $this->phoneCleanLength) {
                $number = ltrim($number, (int)$this->phoneCountryCode);
            }
        }

        $sub = Messages::select([
            DB::raw('DISTINCT (sms_number)'),
            DB::raw('MAX(sms_id) as sms_id'),
            DB::raw('SUM(sms_auto) as sum_auto'),
            DB::raw('count(sms_id) as count_all'),
            DB::raw('MIN(sms_readed) as readed_all'),
            DB::raw('MAX(sms_incoming) as incoming_present'),
        ]);
        if ($number) {
            $sub->where('sms_number', '=', $number);
        }
        $sub->groupBy('sms_number')
            ->orderBy('sms_date', 'DESC');

        $sub2 = Messages::select([
            DB::raw('MAX(sms_id) as last_incoming_message_id'),
            DB::raw('sms_number as last_incoming_sms_number'),
        ])
            ->where('sms_incoming', '=', 1)
            ->groupBy('sms_number')
            ->orderBy('sms_date', 'DESC');
        $query = Messages::query()
            ->select([
                'sms_messages.sms_id',
                'sms_messages.sms_number',
                'sms_messages.sms_body',
                'sms_messages.sms_date',
                'sms_messages.sms_status',
                'sms_messages.sms_readed',
                'sms_messages.sms_incoming',
                DB::raw("CONCAT('[', GROUP_CONCAT(JSON_OBJECT('cc_client_id', cc_client_id, 'cc_name', cc_name)), ']') as cc_data"),
                'cc_client_id',
                'users.firstname',
                'users.lastname',
                DB::raw('SUBSTR(employees.emp_phone, 1, ' . $this->phoneCleanLength . ') as emp_phone'),
                'client_name',
                DB::raw('last_incoming_message.sms_support as last_incoming_sms_support'),
                'readed_all',
                'incoming_present'
            ])
            ->rightJoinSub($sub, 'last_message', 'last_message.sms_id', '=', 'sms_messages.sms_id')
            ->leftJoinSub($sub2, 'last_incoming_message_max_id',
                'last_incoming_message_max_id.last_incoming_sms_number', '=', 'sms_messages.sms_number')
            ->leftJoin(DB::raw('sms_messages last_incoming_message'),
                'last_incoming_message_max_id.last_incoming_message_id', '=', 'last_incoming_message.sms_id')
            ->leftJoin('clients_contacts', 'clients_contacts.cc_phone_clean', '=',
                DB::raw("SUBSTRING(sms_messages.sms_number, 1, " . $this->phoneCleanLength . ")"))
            ->leftJoin('clients', 'clients_contacts.cc_client_id', '=', 'clients.client_id')
            ->leftJoin('employees', 'employees.emp_phone', '=',
                DB::raw("SUBSTRING(sms_messages.sms_number, 1, " . $this->phoneCleanLength . ")"))
            ->leftJoin('users', 'users.id', '=', 'employees.emp_user_id')
            ->whereRaw('(count_all - sum_auto) > 0');
        switch ($mode) {
            case 'users':
                $query->whereNotNull('employee_id');
                break;
            case 'clients':
                $query->whereNull('employee_id');
                break;
            case 'supportchat':
                $query->where('last_incoming_message.sms_support', '=', 1);
                break;
        }
        if ($number) {
            $query->where('sms_messages.sms_number', '=', $number);
        }

        // <= 15 - according to E.164 standard
        $query->where(DB::raw('LENGTH(sms_messages.sms_number)'), '<=', 15);
        $query->where(DB::raw('LENGTH(sms_messages.sms_number)'), '>=', $this->phoneCleanLength);

        $queryResult = $query->groupBy('sms_messages.sms_number')
            ->orderBy('sms_date', 'DESC')
            ->limit($this->smsChatsShowLimit)
            ->offset($offset)
            ->get();

        return $this->_decodeCCData($queryResult->toArray());
    }

    private function _decodeCCData($data) {
        return array_map(
            function ($v) {
                $v['cc_data'] = json_decode($v['cc_data'], true);

                if ($v['cc_data'] && sizeof($v['cc_data'])) {
                    // clean empty data
                    foreach ($v['cc_data'] as $key => $val) {
                        if (empty($val['cc_name']) || empty($val['cc_client_id'])) {
                            unset($v['cc_data'][$key]);
                        }
                    }
                }

                $v['cc_data'] = $v['cc_data'] && sizeof($v['cc_data']) ? array_values($v['cc_data']) : null;

                return $v;
            },
            $data
        );
    }
}
