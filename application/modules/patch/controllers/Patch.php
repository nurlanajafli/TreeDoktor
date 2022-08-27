<?php ini_set('memory_limit', '3072M');?>
<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
define("COOKIE_FILE", "uploads/cookie.txt");


use application\modules\payroll\requests\CreatePayrollRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use application\modules\user\models\User;
use Twilio\Rest\Client;
use QuickBooksOnline\API\DataService\DataService;

use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Contracts\Database\ModelIdentifier;
use application\notifications\UserPush;


class Patch extends MX_Controller
{////
    private $onlineActivitySid = 'WAfc57c478bd7cea19883d8908c85b0f6b';
    private $busyActivitySid = 'WAc34bf63afbabe02fa3aba16bcec1bde4';
    private $reservedActivitySid = 'WAcee551aab46ac59139a9c9b06059465c';
    private $wrapUpActivitySid = 'WA8c2811c172735411234161750938690a';
    private $offlineActivitySid = 'WAb59015c8ffd0c47bb1f812a8c06c38d6';

    private $accountSid = 'ACba9a5eeb8b45a12e3973bd16e6ae83f2';
    private $authToken = '59cd6e4b9e26182c4fc30956b950bc5f';
    private $workspaceSid = 'WSd5ddf64bb22aa165abac6c6434764dec';
    private $taskQueueSid = 'WQ4634bbda790aa3a5e589ac482f7229aa';
    private $appSid = 'AP1b1806bca60981e676ba7c77a9636772';
    private $workflowSid = 'WWade680760e2f16066950ea0f956a99c9';
    private $myNumber = '14162018000';
    private $twilioNumber = '14162018000';

    private $tablesStructure = [
        'clients' => [
            'columns' => ['client_id', 'client_brand_id', 'client_date_created', 'client_maker', 'client_date_modified', 'client_name', 'client_type', 'client_contact', 'client_main_intersection', 'client_address', 'client_city', 'client_state', 'client_zip', 'client_country', 'client_lng', 'client_lat', 'client_phone', 'client_mobile', 'client_fax', 'client_email', 'client_email2', 'client_web', 'client_status', 'client_intake_notes', 'client_source', 'client_referred_by', 'client_address_check', 'client_address2', 'client_main_intersection2', 'client_city2', 'client_state2', 'client_zip2', 'client_promo_code', 'client_unsubsribed', 'client_rating', 'client_email2_check', 'client_email_check', 'client_unsubscribe', 'client_is_refferal', 'client_qb_id', 'client_payment_profile_id', 'client_payment_driver', 'client_last_qb_time_log', 'client_last_qb_sync_result', 'client_tax_name', 'client_tax_rate', 'client_tax_value'],
            'relations' => [
                'clients_contacts' => 'cc_client_id',
                'client_notes' => 'client_id',
                'leads' => 'client_id',
                'estimates' => 'client_id',
                'workorders' => 'client_id',
                'invoices' => 'client_id',
                'payment_transactions' => 'client_id',
                'client_tasks' => 'task_client_id',
            ],
        ],
        'clients_contacts' => [
            'columns' => ['cc_id', 'cc_client_id', 'cc_title', 'cc_name', 'cc_phone', 'cc_phone_clean', 'cc_email', 'cc_email_check', 'cc_email_manual_approve', 'cc_print'],
            'relations' => [],
        ],
        'leads' => [
            'columns' => ['lead_id', 'lead_no', 'lead_author_id', 'lead_address', 'lead_city', 'lead_state', 'lead_neighborhood', 'lead_zip', 'lead_country', 'client_id', 'lead_body', 'tree_removal', 'tree_pruning', 'stump_removal', 'hedge_maintenance', 'shrub_maintenance', 'wood_disposal', 'arborist_report', 'development', 'root_fertilizing', 'tree_cabling', 'emergency', 'other', 'spraying', 'trunk_injection', 'air_spading', 'planting', 'arborist_consultation', 'construction_arborist_report', 'tpz_installation', 'lights_installation', 'landscaping', 'snow_removal', 'lead_status', 'lead_reason_status', 'timing', 'lead_estimator', 'lead_created_by', 'lead_date_created', 'lead_priority', 'latitude', 'longitude', 'lead_assigned_date', 'lead_postpone_date', 'lead_scheduled', 'lead_call', 'lead_json_backup', 'lead_reffered_client', 'lead_reffered_user', 'lead_reffered_by', 'lead_comment_note', 'lead_gclid', 'lead_msclkid', 'preliminary_estimate', 'lead_status_id', 'lead_reason_status_id', 'lead_tax_name', 'lead_tax_rate', 'lead_tax_value', 'lead_estimate_draft', 'lead_add_info'],
            'relations' => [
                'client_notes' => 'lead_id',
                'client_tasks' => 'task_lead_id',
                'estimates' => 'lead_id',
            ],
        ],
        'client_notes' => [
            'columns' => ['client_note_id', 'client_id', 'client_note_date', 'client_note', 'client_note_type', 'author', 'robot', 'client_note_top', 'lead_id'],
            'relations' => [],
        ],
        'client_tasks' => [
            'columns' => ['task_id', 'task_author_id', 'task_address', 'task_city', 'task_state', 'task_zip', 'task_country', 'task_category', 'task_status', 'task_client_id', 'task_date_created', 'task_desc', 'task_latitude', 'task_longitude', 'task_user_id_updated', 'task_date_updated', 'task_no_map', 'task_assigned_user', 'task_date', 'task_start', 'task_end', 'task_lead_id', 'is_anytime'],
            'relations' => [],
        ],

        'estimates' => [
            'columns' => ['estimate_id', 'estimate_no', 'estimate_balance', 'estimate_last_contact', 'estimate_count_contact', 'client_id', 'estimate_brand_id', 'lead_id', 'date_created', 'status', 'estimate_hst_disabled', 'estimate_item_team', 'estimate_item_estimated_time', 'estimate_item_equipment_setup', 'estimate_item_note_crew', 'estimate_crew_notes', 'estimate_item_note_estimate', 'estimate_item_note_payment', 'arborist', 'bucket_truck_operator', 'climber', 'chipper_operator', 'groundsmen', 'bucket_truck', 'wood_chipper', 'dump_truck', 'crane', 'stump_grinder', 'brush_disposal', 'leave_wood', 'full_cleanup', 'stump_chips', 'permit_required', 'user_id', 'estimate_scheme', 'estimate_reason_decline', 'estimate_provided_by', 'estimate_pdf_files', 'unsubscribe', 'notification', 'paid_by_cc', 'estimate_review_date', 'estimate_review_number', 'estimate_planned_company_cost', 'estimate_planned_crews_cost', 'estimate_planned_equipments_cost', 'estimate_planned_extra_expenses', 'estimate_planned_overheads_cost', 'estimate_planned_profit', 'estimate_planned_profit_percents', 'estimate_planned_tax', 'estimate_planned_total', 'estimate_planned_total_for_services', 'estimate_qb_id', 'estimate_tax_name', 'estimate_tax_rate', 'estimate_tax_value', 'tree_inventory_pdf'],
            'relations' => [
                'estimates_services' => 'estimate_id',
                'workorders' => 'estimate_id',
                'invoices' => 'estimate_id',
                'client_payments' => 'estimate_id',
                'payment_transactions' => 'estimate_id',
            ],
        ],

        'payment_transactions' => [
            'columns' => ['payment_transaction_id','payment_transaction_status','client_id','estimate_id','invoice_id','payment_driver','payment_transaction_remote_id','payment_transaction_amount','payment_transaction_approved','payment_transaction_risk','payment_transaction_order_no','payment_transaction_card','payment_transaction_card_num','payment_transaction_date','payment_transaction_message','payment_transaction_log','payment_transaction_auth_code','payment_transaction_remote_reason_code','payment_transaction_remote_reason_description','payment_transaction_remote_status','payment_transaction_settled_amount','payment_transaction_ref_id','payment_transaction_type'],
            'relations' => [
                'client_payments' => 'payment_trans_id',
            ],
        ],

        'client_payments' => [
            'columns' => ['payment_id','estimate_id','payment_type','payment_date','payment_amount','payment_fee','payment_fee_percent','payment_tips','payment_file','payment_checked','payment_author','payment_account','payment_trans_id','payment_alarm','payment_qb_id','payment_method_int','payment_last_qb_time_log','payment_last_qb_sync_result'],
            'relations' => [],
        ],

        'estimates_services' => [
            'columns' => ['id', 'service_id', 'estimate_id', 'service_description', 'service_time', 'service_travel_time', 'service_price', 'service_priority', 'service_size', 'service_reason', 'service_species', 'service_permit', 'service_disposal_time', 'service_wood_chips', 'service_wood_trailers', 'service_front_space', 'service_disposal_brush', 'service_disposal_wood', 'service_cleanup', 'service_access', 'service_client_home', 'service_scheme', 'service_exemption', 'service_status', 'service_overhead_rate', 'service_markup_rate', 'quantity', 'cost', 'non_taxable', 'is_view_in_pdf', 'estimate_service_qb_id', 'estimate_service_category_id', 'estimate_service_class_id', 'estimate_service_ti_title'],
            'relations' => [],
        ],

        'workorders'=>[
            'columns' => ['id', 'workorder_no', 'estimate_id', 'client_id', 'wo_confirm_how', 'wo_deposit_taken_by', 'wo_deposit_paid', 'wo_scheduling_preference', 'wo_office_notes', 'wo_extra_not_crew', 'in_time_left_office', 'in_time_arrived_site', 'in_time_left_site', 'in_time_arrived_office', 'in_job_completed', 'in_payment_received', 'in_left_todo', 'in_any_damage', 'in_eq_malfuntion', 'in_note_completion', 'wo_status', 'wo_estimator', 'wo_priority', 'date_created', 'wo_pdf_files'],
            'relations' => [
                'schedule' => 'event_wo_id',
                'workorder_employees' => 'workorder_id',
                'invoices' => 'workorder_id'
            ],
        ],

        'invoices' => [
            'columns' => ['id', 'invoice_no', 'workorder_id', 'estimate_id', 'client_id', 'in_status', 'payment_mode', 'payment_amount', 'link_hash', 'link_hash_valid_till', 'interest_rate', 'interest_status', 'date_created', 'overdue_date', 'in_finished_how', 'in_extra_note_crew', 'invoice_like', 'invoice_feedback', 'invoice_pdf_files', 'paid_by_cc', 'invoice_notes', 'overpaid', 'invoice_qb_id', 'qb_invoice_no', 'invoice_last_qb_time_log', 'invoice_last_qb_sync_result', 'invoice_qb_link'],
            'relations' => [
                'payment_transactions' => 'invoice_id'
            ],
        ],

        'schedule'=>[
            'columns' => ['id', 'event_team_id', 'event_wo_id', 'event_start', 'event_end', 'event_note', 'event_report', 'event_report_confirmed', 'event_services', 'event_damage', 'event_complain', 'event_compliment', 'event_price', 'event_expenses', 'event_state'],
            'relations' => [
                'schedule_event_services' => 'event_id',
            ],
        ],
        'schedule_teams'=>[
            'columns' => ['team_id','team_crew_id','team_leader_id','team_leader_user_id','team_color','team_date','team_date_start','team_date_end','team_note','team_hidden_note','team_fail_equipment','team_expenses','team_amount','team_man_hours','team_closed','team_rating'],
            'relations' => [
                'schedule'=>'event_team_id',
                'schedule_teams_equipment'=>'equipment_team_id',
                'schedule_teams_members'=>'employee_team_id',
                'schedule_teams_tools'=>'stt_team_id',
            ]
        ],
    ];

    function __construct()
    {
        parent::__construct();

        //load all common models and libraries here;
        $this->load->model('mdl_administration', 'mdl_administration');
        $this->load->model('mdl_invoices', 'mdl_invoices');
        $this->load->model('mdl_workorders', 'mdl_workorders');
        $this->load->model('mdl_user');
    }

    function db_notification($param1 = null, $param2 = null) {
        try {
            $data = [
                'param1' => $param1 ?: rand(),
                'param2' => $param2 ?: rand(),
            ];

            request()->user()->notify(
                new application\notifications\DatabaseNotification($data)
            );
        }
        catch (Exception $e) {
            die($e->getMessage());
        }

        die();
    }

    // /user_push/1,2,3
    function user_push(string $ids = null) {
        try {
            $data = [
                'action' => 'Test/action',
                'params' => ['param1' => 2],
                'title' => 'Test title 😀😂🤣🤪',
                'body' => 'Test body for user - ' . $ids . ', with some dummy text and another dummy text -' .time(),
//                'tag' => 'New',
//                'image_url' => ''
            ];

            $when = now()->addMinutes(0);

            if ($ids) {
                $ids = explode(',', $ids);

                $notification = new application\notifications\UserPush($data);
                $via = method_exists($notification, 'viaAdditional') && sizeof($notification->viaAdditional())
                    ? array_merge($notification->via(), $notification->viaAdditional())
                    : $notification->via();

                \Illuminate\Support\Facades\Notification::sendNow(
                    User::whereIn('id', $ids)->get(),
                    $notification,
                    $via
                );
            } else {
                $data['body'] = 'Test body for user - ' . request()->user()->id . ', with some dummy text and another dummy text -' .time();

                request()->user()->notify(
                    new application\notifications\UserPush($data)
                );
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }

        die();
    }

    // /user_push_job/1,2,3
    function user_push_job(string $ids = null) {
        try {
            $data = [
                'action' => 'Test/job',
                'params' => ['param1' => 2],
                'title' => 'Test title for push from job',
                'body' => 'Test body with some dummy text and another dummy text, text -' .time(),
//                'tag' => 'New',
//                'image_url' => ''
            ];

            $when = now()->addMinutes(1);

            if ($ids) {
                $ids = explode(',', $ids);

                \Illuminate\Support\Facades\Notification::send(
                    User::whereIn('id', $ids)->get(),
                    (new application\notifications\UserPush($data))->delay($when)
                );
            } else {
                $data['body'] = 'Test body for user - ' . request()->user()->id . ', with some dummy text and another dummy text -' .time();

                request()->user()->notify(
                    new application\notifications\UserPush($data)
                );
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }

        die();
    }

    // /task_push/3/2022-02-17 07:22/categoryId/<address 1/0>
    function task_push($userId, $startDateTime, $categoryId = null, $address = null) {
        $startDateTime = str_replace('%20' ,' ', $startDateTime);
        $startDateTime = str_replace('_' ,' ', $startDateTime);
        $startDateTime = str_replace('+' ,' ', $startDateTime);

        $date = date('Y-m-d', strtotime($startDateTime));
        $startTime = date('H:i:s', strtotime($startDateTime));
        $endTime = date('H:i:s', strtotime($startDateTime) + (45 * 60));

        $this->load->model('mdl_client_tasks');
        $data = [
            'task_client_id' => 39393,
            'task_desc' => null,
            'task_author_id' => 6,
            'task_category' => $categoryId,
            'task_lead_id' => null,
            'task_status' => 'new',
            'task_date_created' => now(),
            'task_date' => $date,
            'task_start' => $startTime,
            'task_end' => $endTime,
            'task_address' => $address ? '762 North Federal Boulevard' : null,
            'task_city' => 'Denver',
            'task_state' => 'CO',
            'task_zip' => '80204',
            'task_country' => 'United States',
            'task_latitude' => '39.7332028',
            'task_longitude' => '-105.0247956',
            'task_assigned_user' => $userId
        ];

        $taskId = $this->mdl_client_tasks->insert($data);

        echo "<pre>"; print_r('now: ' . date('Y-m-d H:i:s')); echo "</pre>";
        echo "<pre>"; print_r('taskId: ' . $taskId); echo "</pre>"; die("TEST END");
    }

    // /socket_notification/1,2,3
    function socket_notification(string $ids = null) {
        try {
            $data = [
                'rooms' => ['sms'],
                'method' => 'updateSmsStatus',
                'params' => [
                    'sms_id' => 294,
                    'sms_status' => 'delivered',
                    'sms_error' => null
                ],
                'sender_id' => request()->user()->id
            ];

            if (strpos(base_url(), 'localhost') !== false) {
                $data['options'] = [
                    'context' => [
                        'ssl' => [
                            'verify_peer' => false,
                            "verify_peer_name"=>false,
                            'allow_self_signed'=> false
                        ]
                    ]
                ];
            }

            $when = now()->addMinutes(0);

            if ($ids) {
                $ids = explode(',', $ids);

                \Illuminate\Support\Facades\Notification::send(
                    User::whereIn('id', $ids)->get(),
                    (new application\notifications\Socket($data))->delay($when)
                );
            } else {
                request()->user()->notify(
                    new application\notifications\Socket($data)
                );
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }

        die();
    }

    // replace '--' in $email to '@'
    // /email_notification/qwe--gmail.com
    function email_notification($email = null) {
        try {
            $testData = [
                'payment' => true,
                'amount' => 50,
                'entity_description' => 'Test description',
                'entity_item_name' => 'Test name',
                'message' => 'Test message',
                'card' => '1234xxxx1234',
                'card_icon' => [
                    'img' => 'visa.png',
                    'title' => 'VISA'
                ],
                'date' => date('Y-m-d H:i:s'),
                'auth_code' => 'XXXXXX'
            ];
            $tmpl = $this->load->view('patch/test_tmpl', $testData, true);

            if ($email) {
                $email = str_replace('--', '@', $email);

                \Illuminate\Support\Facades\Notification::route('mail', $email)
                    ->notify(new application\notifications\Email([
//                        'template_id' => 9,
                        'template_html' => $tmpl
                    ]));
            } else {
                request()->user()->notify(
                    new application\notifications\Email([
//                    'template_id' => 9,
                        'template_html' => $tmpl
                    ])
                );
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }

        die();
    }

    function ha($delete = false) {
        $json = file_get_contents('uploads/data.json');
        $data = json_decode($json,true);
        $insertedAsRel = [];

        foreach ($data as $table => $rows) {
            foreach ($rows as $row) {
                $firstKey = array_key_first($row);
                if(isset($insertedAsRel[$table]) && in_array($row[$firstKey], $insertedAsRel[$table])) {
                    continue;
                }
                if($delete) {
                    $this->db->delete($table, [$firstKey => $row[$firstKey]]);
                } else {
                    $originalColumnValue = $row[$firstKey];
                    unset($row[$firstKey]);
                    if(isset($row['payment_transaction_log'])) {
                        $row['payment_transaction_log'] = json_encode(json_decode(str_replace(['\\x0cé', '\x0cï', '\\x0c', '\\u00e9', '\\x04', '\\x0a'], '', $row['payment_transaction_log']), true));
                    }

                    $this->db->insert($table, $row);
                    $id = $this->db->insert_id();
                    foreach ($this->tablesStructure[$table]['relations'] as $relT => $relCol) {
                        if (!isset($data[$relT])) {
                            continue;
                        }
                        $foreignKeyRecordIndexes = array_keys(array_column($data[$relT], $relCol), $originalColumnValue);
                        foreach ($foreignKeyRecordIndexes as $index) {
                            $data[$relT][$index][$relCol] = $id;
                            $firstKey = array_key_first($data[$relT][$index]);
                            if(isset($insertedAsRel[$relT]) && in_array($data[$relT][$index][$firstKey], $insertedAsRel[$relT])) {
                                continue;
                            }
                            $backupTmpId = $data[$relT][$index][$firstKey];
                            unset($data[$relT][$index][$firstKey]);
                            if($data[$relT][$index]) {
                                $insertedAsRel[$relT][] = $backupTmpId;
                                $this->db->insert($relT, $data[$relT][$index]);
                            }

                        }
                    }
                }
            }
        }


        die;
        $this->load->library('Common/InvoiceActions');
        $this->invoiceactions->setInvoiceId(23997);


        echo($this->invoiceactions->compileSmsTemplate(['[EMAIL]' => '1@ca.com, test@test.ca']));

        die;
        try {
            request()->user()->notify(
                new application\notifications\UserPush()
            );

            /*\Illuminate\Support\Facades\Notification::send(
                User::whereIn('id', [31,6])->get(), new application\notifications\UserPush()
            );*/
        } catch (Exception $e) {
            var_dump($e);
            //die($e->getMessage());
        }
        die;
        $this->load->library('Common/InvoiceActions');
        $this->invoiceactions->setInvoiceId(23986);
        var_dump($this->invoiceactions->getPDFTemplate());
    }

    function rename_users()
    {
        $users = $this->mdl_user->find_all(['id <>' => 0]);
        $faker = Faker\Factory::create();
        //var_dump($users);
        foreach ($users as $user) {
            $name = $user->firstname;
            $lastname = $user->lastname;

            $newFirstName = $faker->firstName;
            $newLastName = $faker->lastName;

            $newUser = [
                'user_signature' => str_replace([
                    $name, $lastname, '416.201.8000', '+14162018000', '647.435.3439', '+16474353439', 'treedoctors.ca', 'TreeDoctors.ca', 'Tree Doctors Inc', 'Tree Doctors', '425 Kipling Ave. Toronto M8Z 5C7', '425+Kipling+Ave.+Toronto+M8Z+5C7',
                    '229000977160808', 'td.onlineoffice.io', 'treedoctorsca', 'tree_doctors/', '</i></b></a><b><i>'
                ], [
                    $newFirstName, $newLastName, '1-877-444-8733 (TREE)', '+18774448733', '877.444.8733', '+18774448733', 'arborcare.com', 'ArborCare.com', 'Arbor Care LTD', 'Arbor Care', '10100 114 Ave SE Calgary, AB T3S 0A5', '10100+114+Ave+SE+Calgary+AB+T3S+0A5',
                    '', 'arborcare.arbostar.com', '', '', '</i></b></a> <b><i>'
                ], $user->user_signature),
                'firstname' => $newFirstName,
                'lastname' => $newLastName,
                'emailid' => strtoupper(substr($newFirstName, 0, 1)) . strtoupper(substr($newLastName, 0, 1)),
                'password' => md5(strtoupper(substr($newFirstName, 0, 1)) . strtoupper(substr($newLastName, 0, 1))),
                'picture' => NULL,
                'user_email' => $newFirstName . '.' . $newLastName . '@arborcare.arbostar.com',
            ];

            $this->mdl_user->update($user->id, $newUser);
        }

        //echo $faker->firstname;
    }

    function alter()
    {
        $this->db->query("ALTER TABLE `schedule` CHANGE `expenses` `event_expenses` DECIMAL(10,2) NOT NULL DEFAULT '0.00';");
    }

    function vaughan_xlsx_parser($test = FALSE)
    {

        //die;
        $this->load->model('mdl_stumps');
        $file = 'uploads/2020_3.xlsx';
        $a = $this->mdl_stumps->find_by_id(1);
        $result = get_vaughan_stumps_data($file);
        //echo "<pre>";var_dump($result);die;
        unset($result[0]);

        //unset($result[1]);
        //unset($result[2]);
        //unset($result[3]);
        //
        //unset($result[count($result)]);
        //
        $nextId = 9140;
        $ids = '';

        foreach ($result as $key => $value) {

            $val = $value[0];
           // if ($val[0] == null)
               // break;
            //if($val[7] == 'NO STUMP' || !$val[7])
            //continue;

            /*$row = $this->db->like('stump_data', $val[0])->get('stumps')->row();
            //echo "<pre>";var_dump($row);die;
            if($row) {
                //$ids  .= ', ' . $row->stump_id;
                //$ids[$key]['aaa'] = $val[7];
                //$ids[$key]['uid'] = $val[0];
                //$ids[$key]['stump_unique_id'] = $val;
                $this->mdl_stumps->update($row->stump_id, ['stump_status' => 'grinded']);
            }

            else {*/
            /* LIST 1
                $data = [];
                $data['stump_address'] = intval($val[3]) . ' ' . $val[4];
                $data['stump_city'] = 'Vaughan';
                $data['stump_state'] = 'ON';
                $data['stump_data'] = json_encode($val);
                $data['stump_unique_id'] = $val[0];
                $data['stump_map_grid']  = $val[2];
                $data['stump_range'] = floatval($val[9]);
                $data['stump_desc'] = 'List #1  ';
                $data['stump_desc'] .= $val[5] ? 'Park Name: ' . $val[5] . ' ' . $val[8] . '' : ' ' . $val[8] . '';
                if($val[11] && $val[11] == 'yes')
                    $data['stump_desc'] .= ' Priority "yes"';

                $data['stump_status'] = 'new';
                $data['stump_client_id'] = 8;
                $data['stump_status_work'] = 0;

                $data['stump_side'] = trim($val[7]); // strpos($val[5], '/') ? $val[5] . ' ' . trim($val[7]) : trim($val[7]);

                $data['stump_locates'] = $val[13]; //(string)$val[10];
                $data['stump_removal'] = NULL;
                $data['stump_clean'] = NULL;
                $data['stump_contractor_notes'] = NULL;
                */
            // LIST 2
            $data = [];
           /* $data['stump_address'] = intval($val[3]) . ' ' . $val[4];
            $data['stump_city'] = 'Vaughan';
            $data['stump_state'] = 'ON';
            $data['stump_data'] = json_encode($val);
            $data['stump_unique_id'] = $val[0];
            $data['stump_map_grid'] = $val[0];
            $data['stump_range'] = floatval($val[7]);
            $data['stump_desc'] = $val[6];
            * */
            //$data['stump_desc'] .= $val[5] ? 'Park Name: ' . $val[5] . ' ' . $val[9] . '' : ' ' . $val[9] . '';

            //if($val[13] && $val[13] != '')
            //$data['stump_desc'] .= ' T# ' . $val[13];

            $data['stump_status'] = 'new';
            $data['stump_client_id'] = 12;
            $data['stump_status_work'] = 0;

            $data['stump_side'] = NULL;//trim($val[7]); // strpos($val[5], '/') ? $val[5] . ' ' . trim($val[7]) : trim($val[7]);

            //$data['stump_locates'] = $val[11]; //(string)$val[10];
            $data['stump_removal'] = NULL;
            $data['stump_clean'] = NULL;
            $data['stump_contractor_notes'] = NULL;
            //echo "<pre>";var_dump($data);die;
            //echo "<pre>";var_dump($data, $data);die;
            //$val[6] ? "Tree #" . $val[6] . "\n" : '';
            //$data['stump_desc'] .= $val[3] ? "Route #" . $val[3] : '';

            $data['stump_map_grid'] = $val[0];
            $data['stump_desc'] = $val[1] ? ' Zone: ' . $val[1] : ' ';
            $data['stump_desc'] .= $val[8] ? ' Comments: ' . $val[8]  : ' ';
            $data['stump_desc'] .= $val[7] ? ' WO#: ' . $val[7]  : ' ';
            $data['stump_house_number'] = $val[2];
            $data['stump_address'] = $val[3];
            $data['stump_address'] .= $val[4] ? ' ' . $val[4]  : '';
            $data['stump_side'] = $val[5];
            $data['stump_range'] = $val[6];
            $data['stump_city'] = 'Vaughan';
            $data['stump_state'] = 'ON';
            $data['stump_data'] = json_encode($val);

             //echo "<pre>";var_dump($data);die;
            if (!$test)
                $this->mdl_stumps->insert_stumps($data);
            $nextId++;
            //}

        }
        echo "<pre>";
        var_dump($ids);
        die;
    }

    function setCorrdsToStumps()
    {
        $this->load->model('mdl_stumps');
        //$rows = $this->db->query("SELECT * FROM `stumps` WHERE stump_lat IS NULL AND stump_client_id = 12 GROUP BY stump_address ORDER BY ABS(`stump_unique_id`) ASC")->result_array();
		$rows = $this->db->query("SELECT * FROM `stumps` WHERE stump_lat IS NULL AND stump_client_id = 17 GROUP BY CONCAT(stump_house_number, ' ', stump_address) ORDER BY `stumps`.`stump_id` ASC")->result_array();
        //$rows = $this->db->query("SELECT * FROM `stumps` WHERE stump_id = 2")->result_array();
		//AND stump_state != 'Ontario'
        foreach ($rows as $row) {
            $g_address = str_replace(' ', '+', $row['stump_house_number'] . ' ' . trim($row['stump_address'])) . ",ON," . config_item('office_country');

            $g_addr_str = $g_address;
            $url = "https://maps.google.com/maps/api/geocode/json?address=$g_addr_str&key=AIzaSyAuTaDHsRcoSJgMb9o-Mfu459D4Uk0MU-g";// . $this->config->item('gmaps_key');//&key=AIzaSyBDUPTmUYuYnv9r8d6-TXanSefGZclfKTw

            $data = [];

            $jsonData = file_get_contents($url);
            $client_geo_address = json_decode($jsonData);
            $streetName = NULL;
            $streetNumber = 0;
            echo "<pre>";var_dump($client_geo_address);die;
            if (!empty($client_geo_address->{'results'}) && isset($client_geo_address->{'results'}[0]->{'address_components'})) {
                foreach ($client_geo_address->{'results'}[0]->{'address_components'} as $addresComp) {
                    if ($addresComp->types[0] == 'administrative_area_level_1')
                        $data['stump_state'] = $addresComp->long_name;

                    if ($addresComp->types[0] == 'street_number')
                        $streetNumber = $addresComp->long_name;

                    if ($addresComp->types[0] == 'route')
                        $streetName = $addresComp->short_name;
                    if ($addresComp->types[0] == 'locality')
                       $data['stump_city'] = $addresComp->long_name;
                }

                //$data['stump_address'] = $streetNumber . ' ' . $streetName;

                $data['stump_lat'] = $client_geo_address->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
                $data['stump_lon'] = $client_geo_address->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
                //echo "<pre>";var_dump($data);die;
                //$this->mdl_stumps->update($row['stump_id'], $data);
                //echo '<pre>'; var_dump($data); die;
                $wdata = ['stump_address' => $row['stump_address'], 'stump_house_number' => $row['stump_house_number']];
                $this->mdl_stumps->update_stumps($data, $wdata);
            }
        }
    }

    function update_stumps_for_claster()
    {

        $this->load->model('mdl_stumps');
        $coords = $this->db->query('SELECT count(stump_id) as count, stump_lat, stump_lon, stump_unique_id FROM `stumps` WHERE stump_client_id = 17 GROUP BY stump_lat, stump_lon HAVING COUNT( stump_id ) >1 LIMIT 1000')->result_array();

        //174
        $lat = 00.0000150;
        $lon = -00.0000202;
        foreach ($coords as $key => $val) {
            $sqrt = ceil(sqrt($val['count']));
            $start = $i = $j = ceil($sqrt / 2);

            $stumps = $this->mdl_stumps->get_all('', array('stump_lat' => $val['stump_lat'], 'stump_lon' => $val['stump_lon']));

            foreach ($stumps as $k => $v) {
                $this->mdl_stumps->update_stumps(array('stump_lat' => $v['stump_lat'] + ($i * $lat), 'stump_lon' => $v['stump_lon'] + ($j * $lon)), array('stump_id' => $v['stump_id']));
                $i--;
                if ($i == -($start - 1)) {
                    $i = $start;
                    $j--;
                }
            }

        }

        // LAT - 150 LON - 202

    }

    function vaughan_router()
    {
        $this->load->model('mdl_stumps');
        $file = 'uploads/routes_list.xlsx';
        $result = get_vaughan_stumps_data($file);
        unset($result[0]);
        //unset($result[1]);
        //unset($result[2]);
        //echo "<pre>";var_dump($result);die;
        //unset($result[count($result)]);
        $startUniqId = 1;
        foreach ($result as $key => $value) {
            $stumpRow = $this->mdl_stumps->find_stump(['stump_data' => $value[0][0]], ['stump_client_id' => 7]);
            if (!$stumpRow) {
                echo $value[0][0] . ', ';
                continue;
            }

            $data['stump_unique_id'] = $startUniqId;
            $startUniqId++;
            $this->mdl_stumps->update_stumps($data, ['stump_id' => $stumpRow['stump_id']]);
        }
    }

    function patch_event_price()
    {
        $this->load->model('mdl_schedule');
        $rows = $this->db->query("SELECT COUNT(schedule.id) as count, schedule.id, team_amount, event_price, team_id, FROM_UNIXTIME(event_start) as start_date FROM `schedule_teams` JOIN schedule WHERE schedule_teams.team_id = schedule.event_team_id GROUP BY team_id HAVING count = 1 AND ABS(team_amount) + 0.01 < ABS(event_price) AND start_date >= '2017-01-01' AND start_date <= '2017-12-31'")->result();
        foreach ($rows as $row) {
            $this->mdl_schedule->update($row->id, ['event_price' => $row->team_amount]);
        }
    }

    function delete_twilio_recordings()
    {
        $this->load->model('mdl_calls');
        $twilio = new Client($this->accountSid, $this->authToken);

        $recordings = $twilio->recordings->read([/*"dateCreatedBefore" => new \DateTime('2019-01-21'), */ "dateCreatedAfter" => new \DateTime('2019-01-21')]);
        //$twilio->recordings("RE67e568fd9f40b43cbc04cdf5754082a8")->fetch();
        //
        $recs = $rws = 0;

        foreach ($recordings as $record) {
            $url = 'https://api.twilio.com' . str_replace('.json', '.mp3', $record->uri);
            $rawData = file_get_contents($url);
            $path = 'uploads/recordings/' . $record->dateCreated->format('Y-m') . '/';
            $fileName = $path . $record->sid . '.mp3';
            if (is_file($fileName))
                continue;

            if (!is_dir($path)) {
                mkdir($path);
                chmod($path, 0777);
            }
            file_put_contents('./' . $fileName, $rawData, 0777);
            $recs++;
            $this->mdl_calls->update_by(['call_twilio_sid' => $record->callSid], ['call_voice' => base_url($fileName) . '?']);

            $twilio->recordings($record->sid)->delete();
        }
        /*$calls = $this->db->query("SELECT * FROM `clients_calls` WHERE call_voice LIKE '%api.twilio%' AND call_date >= '2019-01-21 00:00:00' AND call_date <= '2019-03-06 23:59:59' ORDER BY call_date")->result();
        foreach ($calls as $call) {
            $url = $call->call_voice;
            $rawData = file_get_contents($url);
            $path = 'uploads/recordings/' . date('Y-m', strtotime($call->call_date)) . '/';
            $fileName = $path . basename($call->call_voice) . '.mp3';
            if(is_file($fileName))
                continue;

            if(!is_dir($path)) {
                mkdir($path);
                chmod($path, 0777);
            }
            file_put_contents('./' . $fileName, $rawData, 0777);
            $rws++;
            $this->mdl_calls->update($call->call_id, ['call_voice' => base_url($fileName) . '?']);
            //$twilio->recordings($record->sid)->delete();
        }
        echo $recs . '<br>' . $rws . '<br>'; */
        echo "ok";
    }

    function get_calls()
    {
        $twilio = new Client($this->accountSid, $this->authToken);

        $calls = $twilio->calls->read(array(
                "startTimeBefore" => new \DateTime('2018-5-31'),
                "startTimeAfter" => new \DateTime('2018-5-1'),
                "status" => "completed"
            )
        );
        $newCalls = [];
        $parents = [];
        foreach ($calls as $record) {
            if ($record->parentCallSid)
                $parents[$record->parentCallSid] = $record->parentCallSid;
        }
        foreach ($calls as $record) {
            if (isset($parents[$record->sid])) {
                $parents[$record->sid] = [
                    'parent' => $record->parentCallSid,
                    'sid' => $record->sid,
                    'from' => $record->from,
                    'to' => $record->to,
                    'price' => $record->price,
                    //'price_unit' => $record->price_unit,
                    'date' => $record->dateCreated->format("Y-m-d H:i:s"),
                ];
                continue;
            } else {
                $newCalls[] = [
                    'parent' => $record->parentCallSid,
                    'sid' => $record->sid,
                    'from' => $record->from,
                    'to' => $record->to,
                    'price' => $record->price,
                    //'price_unit' => $record->price_unit,
                    'date' => $record->dateCreated->format("Y-m-d H:i:s"),
                ];
            }

        }

        $this->load->view('calls_report', ['newCalls' => $newCalls, 'parents' => $parents]);
    }

    function update_twilio_number()
    {
        $myNumber = '14162018000';
        $this->load->model('mdl_calls');
        $this->db->select('*');
        $this->db->where("call_from = 16475600393");
        $calls = $this->db->get('clients_calls')->result_array();

        foreach ($calls as $k => $v)
            $this->mdl_calls->update($v['call_id'], array('call_from' => $myNumber));
        $this->db->select('*');
        $this->db->where("call_to = 16475600393");
        $calls = $this->db->get('clients_calls')->result_array();
        foreach ($calls as $k => $v)
            $this->mdl_calls->update($v['call_id'], array('call_to' => $myNumber));
        die('ok');

    }

    /* This method not actual */
    function patch_invoices()
    {
        die;
        $this->db->join('clients', 'clients.client_id = invoices.client_id');
        $invoices = $this->db->get('invoices')->result_array();
        foreach ($invoices as $key => $invoice) {
            $update = array();
            //\application\modules\invoices\models\Invoice::getInvoiceTerm($client_data->client_type ?? null)
            $timestamp = strtotime($invoice['date_created'] . '+' . 1 . ' days');
            if ($invoice['client_type'] > 1)
                $timestamp = strtotime($invoice['date_created'] . '+' . 2 . ' days');
            $update['overdue_date'] = date('Y-m-d', $timestamp);
            if (($invoice['in_status'] != 'Paid') && (date('Y-m-d') > $update['overdue_date']))
                $update['in_status'] = 'Overdue';
            $this->db->where('id', $invoice['id']);
            $this->db->update('invoices', $update);
        }
    }

    public function cron_services()
    {
        die;
        $this->load->model('mdl_equipments', 'mdl_equipments');
        $wdata['service_date >='] = strtotime(date('Y-m-01')); //timestamp first day of current month
        $wdata['service_date <'] = strtotime(date("Y-m-t", strtotime(date('Y-m-01')))); //timestamp last day of current month
        $wdata['service_status'] = 'complete'; //status
        $services = $this->mdl_equipments->get_services($wdata, TRUE, 'service_status ASC');

        foreach ($services as $service) {
            $insert['service_item_id'] = $service['service_item_id'];
            $insert['service_name'] = $service['service_name'];
            $insert['service_date'] = $service['service_next'];

            if ($service['service_periodicity']) {
                $ndate = date('Y-m-d', $service['service_next']);
                $insert['service_next'] = strtotime($ndate . '+' . $service['service_periodicity'] . ' months');
            }
            $insert['service_periodicity'] = $service['service_periodicity'];

            $id = $this->mdl_equipments->insert_service($insert);
        }
    }

    function patch_estimates_balance()
    {
        die;
        $this->load->model('mdl_estimates');
        $estimates = $this->mdl_estimates->find_all();
        foreach ($estimates as $estimate) {
            $this->mdl_estimates->update_estimate_balance($estimate->estimate_id);
        }
        echo 'ok';
    }

    function patch_invoices_balance()
    {
        die;
        $this->load->model('mdl_invoices');
        $invoices = $this->mdl_invoices->find_all();
        foreach ($invoices as $invoice) {
            $this->mdl_invoices->update_invoice_balance($invoice->id);
        }
        echo 'ok';
    }

    public function payments_patch()
    {
        die;
        $this->load->model('mdl_clients', 'mdl_clients');
        $this->db->where('wo_deposit_paid <>', '0');
        $this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id');
        $workorders = $this->db->get('workorders')->result_array();
        foreach ($workorders as $key => $row) {
            $insert = array();
            $insert['payment_method'] = 'cash';
            $insert['estimate_id'] = $row['estimate_id'];
            $insert['payment_date'] = strtotime($row['date_created']);
            $insert['payment_type'] = 'deposit';
            $insert['payment_amount'] = $row['wo_deposit_paid'];
            $files = array();
            $this->load->helper('file');
            $path = FCPATH . 'uploads/workorder_files/' . $row['client_id'] . '/' . $row['workorder_no'] . '/';
            $newPath = FCPATH . 'uploads/payment_files/' . $row['client_id'] . '/' . $row['estimate_no'] . '/';
            $files = bucketScanDir($path);
            if (!$files)
                $files = array();
            sort($files, SORT_NATURAL);
            $num = 1;
            $filename = '';
            foreach ($files as $key => $file) {
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                /*if (!is_dir(FCPATH . 'uploads/payment_files/'))
                    mkdir(FCPATH . 'uploads/payment_files/');
                if (!is_dir(FCPATH . 'uploads/payment_files/' . $row['client_id']))
                    mkdir(FCPATH . 'uploads/payment_files/' . $row['client_id']);
                if (!is_dir($newPath))
                    mkdir($newPath);*/
                if (is_file($path . $file)) {
                    if ($num != 1)
                        make_notes($row['client_id'], 'Payment File From: <a target="_blank" href="' . base_url('uploads/payment_files/' . $row['client_id'] . '/' . $row['estimate_no'] . '/' . $filename) . '">' . $filename . '</a> To: <a target="_blank" href="' . base_url('uploads/payment_files/' . $row['client_id'] . '/' . $row['estimate_no'] . '/payment_' . $num . '.' . $ext) . '">' . 'payment_' . $num . '.' . $ext . '</a>', 'system', intval($row['estimate_no']));
                    $filename = 'payment_' . $num . '.' . $ext;
                    copy($path . $file, $newPath . $filename);
                    $num++;
                }
            }
            $insert['payment_file'] = $filename;
            $this->db->insert('client_payments', $insert);
        }
    }

    public function discounts_patch()
    {
        die;
        $this->db->where('interest_rate IS NOT NULL', NULL, false);
        $invoices = $this->db->get('invoices')->result_array();
        foreach ($invoices as $invoice) {
            $insert = array();
            $insert['discount_amount'] = $invoice['interest_rate'];
            $insert['estimate_id'] = $invoice['estimate_id'];
            $insert['discount_date'] = strtotime($invoice['date_created']);
            $this->db->insert('discounts', $insert);
        }
    }

    function patch_client_notes()
    {
        die;
        $this->db->select('*');
        $this->db->from('estimates_calls');
        $this->db->join('estimates', 'estimates.estimate_id = estimates_calls.call_estimate_id');
        $estimates_calls = $this->db->get()->result_array();

        $client_notes = array();

        foreach ($estimates_calls as $estimate_call) {
            $date = date('Y-m-d H', $estimate_call['call_time']);
            $this->db->where('client_id', $estimate_call['client_id']);
            $this->db->where('client_note', $estimate_call['call_message']);
            $this->db->like('client_note_date', $date, 'after');
            $note = $this->db->get('client_notes')->result_array();
            if (!$note) {
                $insert['client_id'] = $estimate_call['client_id'];
                $insert['client_note_date'] = date('Y-m-d H:i:s', $estimate_call['call_time']);
                $insert['client_note'] = $estimate_call['call_message'];
                $insert['author'] = $estimate_call['call_user_id'] ? $estimate_call['call_user_id'] : 1;
                $insert['robot'] = 'yes';
                $this->db->insert('client_notes', $insert);
            }
        }
    }

    function patch_estimate_count()
    {
        die;
        $this->load->model('mdl_estimates');
        $this->db->select('call_estimate_id');
        $this->db->group_by("call_estimate_id", 'call_time');

        $estimates_ids = $this->db->get('estimates_calls')->result_array();
        $estimate_array = array();
        foreach ($estimates_ids as $estimate_id) {
            //echo $estimate_id['call_estimate_id'];
            $this->db->select('call_time');
            $this->db->where(array('call_estimate_id' => $estimate_id['call_estimate_id']));
            $this->db->order_by("call_time", "desc");
            $date = $this->db->get('estimates_calls')->result_array();


            //var_dump($date[0]['call_time']); die;
            $update = $this->mdl_estimates->update_estimates(array('estimate_last_contact' => $date[0]['call_time'], 'estimate_count_contact' => count($date)), array('estimate_id' => $estimate_id['call_estimate_id']));
        }
    }

    function patch_invoices_payments()
    {
        die;
        //SELECT * FROM `invoices` WHERE date_created < '2014-09-01' ORDER BY `invoices`.`date_created` ASC
        $this->db->select('invoices.*, estimates.*, estimates.date_created as estimate_date_created');
        $this->db->where('invoices.date_created < ', '2014-09-01');
        $this->db->where('invoices.in_status', 'Paid');
        $this->db->join('estimates', 'invoices.estimate_id = estimates.estimate_id');
        $invoices = $this->db->get('invoices')->result_array();
        foreach ($invoices as $inv) {
            $insert = array();

            $sum = 0;
            $sum += $inv['arborist_report_price'] + $inv['trimming_service_price'] + $inv['tree_removal_price'] + $inv['stump_grinding_price'] + $inv['wood_removal_price'] + $inv['site_cleanup_price'] + $inv['extra_option_price'];
            $sum = $sum + ($sum * ($inv->estimate_tax_value / 100));

            $insert['estimate_id'] = $inv['estimate_id'];
            $insert['payment_method'] = 'cash';
            $insert['payment_type'] = 'invoice';
            $insert['payment_date'] = strtotime($inv['estimate_date_created']) + (86400 * 7);
            $insert['payment_amount'] = $sum;
            $insert['payment_file'] = '';

            $this->db->insert('client_payments', $insert);
        }
        echo "Done";
    }

    function patch_new_estimates()
    {
        die;
        $this->load->helper('estimates_helper');
        $this->load->model('mdl_estimates', 'mdl_estimates');
        $cols = array('arborist_report', 'trimming_service', 'tree_removal', 'stump_grinding', 'wood_removal', 'site_cleanup', 'extra_option');
        $path = 'uploads/clients_files/';
        $estimates = $this->db->get('estimates')->result_array();
        foreach ($estimates as $num => $estimate) {
            $client_dir = $path;
            $client_dir .= $estimate['client_id'] . '/estimates/' . $estimate['estimate_no'] . '/';
            foreach ($cols as $key => $col) {
                if ($estimate[$col] || ($estimate[$col . '_price'] && $estimate[$col . '_price'] != '0.00') || $estimate[$col . '_time']) {
                    /**FIND AND CREATE SERVICES**/
                    $insert = array('service_id' => ($key + 1), 'estimate_id' => $estimate['estimate_id'], 'service_description' => $estimate[$col], 'service_time' => $estimate[$col . '_time'], 'service_price' => $estimate[$col . '_price']);
                    $servId = $this->mdl_estimates->insert_estimate_service($insert);

                    /**FIND AND COPY FILES TO NEW DIR**/
                    if ($estimate[$col . '_photos'] && json_decode($estimate[$col . '_photos'])) {
                        $service_dir = $client_dir;
                        $service_dir .= $servId . '/';
                        $oldFiles = bucket_get_filenames('uploads/estimate_photos/' . ceil($estimate['estimate_no'] / 1000) . '/' . $estimate['estimate_no'] . '/');
                        if (!empty($oldFiles) && $oldFiles) {
                            makedir($service_dir);
                            $estimate[$col . '_photos'] = json_decode($estimate[$col . '_photos']);
                            foreach ($estimate[$col . '_photos'] as $photoNum => $photo) {
                                $oldFile = 'uploads/estimate_photos/' . ceil($estimate['estimate_no'] / 1000) . '/' . $estimate['estimate_no'] . '/' . $photo;
                                $newFile = $service_dir . 'estimate_' . ($photoNum + 1) . '.jpg';
                                @copy($oldFile, $newFile);
                                @chmod($oldFile, 0777);
                                //unlink($oldFile);
                            }
                        }
                    }
                }
                unset($estimate[$col], $estimate[$col . '_photos'], $estimate[$col . '_price'], $estimate[$col . '_price'], $estimate[$col . '_time']);
                unset($estimate['crew_size']);
            }
            $estimate['date_created'] = strtotime($estimate['date_created']);
            $this->db->insert('estimates_new', $estimate);
        }
        die;
    }

    function client_patch_author()
    {
        die;
        //SELECT clients.client_id, client_notes.author FROM `clients` LEFT JOIN client_notes ON clients.client_id = client_notes.client_id GROUP BY client_notes.client_id ORDER BY client_note_date ASC
        $this->db->select('clients.client_id, client_notes.author');
        $this->db->join('client_notes', 'clients.client_id = client_notes.client_id', 'left');
        $this->db->group_by('client_notes.client_id');
        $this->db->order_by('client_note_date', 'ASC');
        $clients = $this->db->get('clients')->result_array();

        foreach ($clients as $client) {
            $data = array('client_maker' => $client['author']);
            $this->db->where('client_id', $client['client_id']);
            $this->db->update('clients', $data);
        }

    }

    function lead_patch_authors()
    {
        die;
        //SELECT users.id, leads.lead_id FROM `leads` LEFT JOIN users ON leads.lead_created_by = CONCAT(users.firstname,' ',users.lastname)
        $this->db->select('users.id, leads.lead_id');
        $this->db->join("users", "leads.lead_created_by = CONCAT(users.firstname,' ',users.lastname)", "left");
        $users = $this->db->get('leads')->result_array();

        foreach ($users as $user) {

            $data = array('lead_author_id' => $user['id']);
            $this->db->where('lead_id', $user['lead_id']);
            $this->db->update('leads', $data);
        }
    }

    function get_tree_patch()
    {
        die;
        $this->load->helper('estimates_helper');
        echo "<pre>";
        $page = file_get_contents('http://www.arborday.org/treeguide/browsetrees.cfm');
        preg_match_all('/<tr>.*?<td><a href="TreeDetail\.cfm\?ID=([0-9]{1,})">(.*?)<\/a><\/td>.*?<td>(.*?)<\/td>.*?<td>(.*?)<\/td>.*?<\/tr>/is', $page, $data);
        $ids = $data[1];
        $commonNames = $data[2];
        $scientificNames = $data[3];
        $familyNames = $data[4];
        foreach ($ids as $key => $id) {
            $page = file_get_contents('http://www.arborday.org/treeguide/TreeDetail.cfm?ID=' . $id);
            $insert = array();
            $info = array();
            $insert['tree_common_name'] = $commonNames[$key];
            $insert['tree_scientific_name'] = $scientificNames[$key];
            $insert['tree_family_name'] = $familyNames[$key];
            preg_match('/<div class="media-object-big bigger-image">(.*?)<\/div>/is', $page, $images);
            /****$links[1] urls to images****/
            preg_match_all('/<a href="(.*?)" class=".*?mfp-image" title=".*?">/is', $images[1], $links);
            /**************$descr[1] description************/
            //preg_match('/<p>(.*?)<\/p>/is', $images[1], $descr);
            preg_match('/<div class="clearfix more detail">.*?<\/div>/is', $page, $details);
            preg_match_all('/<h6>(.*?)<\/h6>.*?<p>(.*?)<\/p>/is', $details[0], $itemDetails);
            preg_match_all('/<div class="info.*?" id=".*?">.*?<div class="detail">.*?<h6>(.*?)<\/h6>(.*?)<\/div>.*?<\/div>/is', $page, $itemDetails1);
            foreach ($itemDetails[1] as $nkey => $val)
                $info[$val] = trim($itemDetails[2][$nkey]);
            foreach ($itemDetails1[1] as $nkey => $val) {
                if (strpos($val, 'Hardiness Zones') === FALSE)
                    $info[$val] = trim($itemDetails1[2][$nkey]);
            }
            $insert['tree_data'] = json_encode($info);
            $this->db->insert('trees_info', $insert);
            $id = $this->db->insert_id();
            $path = 'uploads/trees_files/' . $id . '/';
            makedir($path);
            foreach ($links[1] as $nkey => $val) {
                file_put_contents($path . ($nkey + 1) . '.jpg', file_get_contents('http://www.arborday.org/' . $val));
            }
        }
        //var_dump($ids, $commonNames, $scientificNames, $familyNames);
    }

    function remove_tree_links()
    {
        die;
        $trees = $this->db->get('trees_info')->result_array();
        foreach ($trees as $val) {
            $newDetails = array();
            $details = json_decode($val['tree_data']);
            foreach ($details as $key => $detail) {
                $newDetails[$key] = preg_replace('/<a.*?>.*?<\/a>/is', '', $detail);
            }
            $this->db->where('tree_id', $val['tree_id']);
            $this->db->update('trees_info', array('tree_data' => json_encode($newDetails)));
        }
    }

    function promoEmailSend()
    {
        die;
        set_time_limit(0);
        $result = $this->db->query("SELECT * FROM  `invoices`  JOIN estimates ON estimates.estimate_id = invoices.estimate_id JOIN leads ON estimates.lead_id = leads.lead_id JOIN clients ON estimates.client_id = clients.client_id WHERE invoices.in_status =  'Paid' AND invoices.date_created >=  '2014-01-01' AND invoices.date_created <  '2015-01-01' AND client_type =1 AND client_promo_code IS NULL GROUP BY clients.client_id ORDER BY invoices.`estimate_id` ASC ")->result_array();
        $this->load->library('mandrill', 'ufG01biYhHFqS3wD5I4hbQ');
        $this->load->model('mdl_clients');
        $countMessages = 0;
        $errorInvalidEmail = array();
        $errorFailed = array();

        foreach ($result as $key => $val) {
            /*if($key == 1)
                die('ok');
            /*if($countMessages == 1)*/
            die('ok');
            $to = array();
            //$val['client_email'] = 'isq200820082008@gmail.com';
            $emails = array();
            if (strpos($val['client_email'], '/') !== FALSE)
                $emails = explode('/', $val['client_email']);
            elseif (strpos($val['client_email'], ',') !== FALSE)
                $emails = explode(',', $val['client_email']);
            else
                $emails[] = $val['client_email'];

            $data['name'] = ucwords(strtolower($val['client_name']));
            $data['client_id'] = $val['client_id'];
            $data['address'] = ucwords(strtolower($val['client_address'])) . ' ' . ucwords(strtolower($val['client_city']));
            $letters = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 3);
            $code = rand(11111, 99999) . $letters;
            $data['code'] = $code;

            foreach ($emails as $email) {
                if (strpos($val['client_email'], '@') === FALSE) {
                    $errorInvalidEmail[] = $val['client_id'];
                    $this->mdl_clients->update_client(array('client_promo_code' => 'Invalid Email'), array('client_id' => $val['client_id']));
                    continue;
                }
                $to[] = array(
                    'email' => trim(strtolower($email)),
                    'name' => $data['name'],
                    'type' => 'to'
                );
            }

            if (empty($to))
                continue;

            $text = $this->load->view('promo_invoices_letter', $data, TRUE);

//			echo $text;die;

            $message = array(
                'html' => $text,
                'subject' => "Thank You for being out client. Please enjoy this special offer.",
                'from_email' => 'promotions@treedoctors.ca',
                'from_name' => 'Tree Doctors Promotions',
                'to' => $to,
                'headers' => array('Reply-To' => 'info@treedoctors.ca')
            );
            $async = false;
            $ip_pool = 'Main Pool';
            $send_at = date('Y-m-d H:i:s');
            $result = $this->mandrill->messages->send($message, $async, $ip_pool, $send_at);

            if (isset($result[0]['status']) && $result[0]['status'] == 'sent') {
                $this->mdl_clients->update_client(array('client_promo_code' => $code . ' - $100'), array('client_id' => $val['client_id']));
                $countMessages++;
            } else {
                $errorFailed[] = $val['client_id'];
                $this->mdl_clients->update_client(array('client_promo_code' => 'Failed Email'), array('client_id' => $val['client_id']));
            }
        }
        echo $countMessages;
    }

    function expenses_hst_patch()
    {
        die('stop');
        $this->db->where('expense_hst_amount !=', '0.00');
        $expenses = $this->db->get('expenses')->result_array();
        $defaultTax = getDefaultTax();
        foreach ($expenses as $exp) {
            $summ = $exp['expense_amount'] + $exp['expense_hst_amount'];
            $update['expense_amount'] = $summ / ($defaultTax['value'] / 100 +1);
            $update['expense_hst_amount'] = $update['expense_amount'] * ($defaultTax['value'] / 100);
            $this->db->where('expense_id', $exp['expense_id']);
            $this->db->update('expenses', $update);
        }
        echo "ok";
    }

    function createPayroll()
    {
        die('1');
        $startDate = '2012-12-30';
        $endDate = '2030-12-31';

        $this->load->model('mdl_payroll');

        $this->mdl_payroll->truncate();

        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);

        if (date('N', $startTime) != 1) {
            $startDate = date('Y-m-d', strtotime("Sunday this week", $startTime));
            $startTime = strtotime($startDate);
        }
        if (!(round($startTime / 604800) % 2)) {
            $startDate = date('Y-m-d', ($startTime - 604800));
            $startTime = $startTime - 604800;
        }

        if (date('N', $endTime) != 1) {
            $endDate = date('Y-m-d', strtotime("Monday this week", $endTime));
            $endTime = strtotime($endDate);
        }
        if (round($endTime / 604800) % 2) {
            $endDate = date('Y-m-d', ($endTime + 604800));
            $endTime = $endTime + 604800;
        }

        for ($i = $startTime; $i <= $endTime; $i += (604800 * 2)) {
            $startPayroll = date('Y-m-d', $i);
            $endPayroll = date('Y-m-d', ($i + (604800 * 2) - 86400));
            $payroll['payroll_day'] = date('Y-m-d', strtotime("Friday this week", strtotime($endPayroll)));
            $payroll['payroll_start_date'] = $startPayroll;
            $payroll['payroll_end_date'] = $endPayroll;
            $this->mdl_payroll->insert($payroll);
        }

        $this->fixEmployeePayrollIds();
//        $this->testEmployeePayrollIdsAccuracy();
        die('ok');
    }

    function createOneWeekPayroll()
    {
        $startDate = '2022-01-03';
        $endDate = '2040-12-31';
        $secsInWeek = 604800;
        $secsInDay = 86400;

        $this->load->model('mdl_payroll');
        $this->mdl_payroll->truncate();

        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);

        if (date('N', $startTime) != 1) {
            $startDate = date('Y-m-d', strtotime("Monday this week", $startTime));
            $startTime = strtotime($startDate);
        }

        if (!(round($startTime / $secsInWeek) % 2)) {
            $startDate = date('Y-m-d', ($startTime - $secsInWeek));
            $startTime = $startTime - $secsInWeek;
        }

        if (date('N', $endTime) != 1) {
            $endDate = date('Y-m-d', strtotime("Sunday this week", $endTime));
            $endTime = strtotime($endDate);
        }
        if (round($endTime / $secsInWeek) % 2) {
            $endDate = date('Y-m-d', ($endTime + $secsInWeek));
            $endTime = $endTime + $secsInWeek;
        }

        for ($i = $startTime; $i <= $endTime; $i += $secsInWeek) {
            $startPayroll = date('Y-m-d', $i);
            $endPayroll = date('Y-m-d', ($i + $secsInWeek - $secsInDay));
            $payroll['payroll_day'] = date('Y-m-d', strtotime("Friday next week", strtotime($endPayroll)));
            $payroll['payroll_start_date'] = $startPayroll;
            $payroll['payroll_end_date'] = $endPayroll;
            $this->mdl_payroll->insert($payroll);
        }

        $this->fixEmployeePayrollIds();
//        $this->testEmployeePayrollIdsAccuracy();

        echo 'done';
    }

    public function fixEmployeePayrollIds()
    {
        $sql = <<<SQL
            UPDATE employee_worked
                CROSS  JOIN payroll
            SET
                employee_worked.worked_payroll_id = payroll.payroll_id
            
            WHERE
                employee_worked.worked_date >= payroll.payroll_start_date  AND employee_worked.worked_date <= payroll.payroll_end_date;          
SQL;
        try {
            $this->db->query($sql);
            die('done');
        } catch (Throwable $e) {
            die($e->getMessage());
        }
    }

    public function testEmployeePayrollIdsAccuracy()
    {
        $sql = <<<SQL
            SELECT * FROM employee_worked
            JOIN payroll ON payroll_id = employee_worked.worked_payroll_id
            WHERE
            employee_worked.worked_date < payroll.payroll_start_date  OR employee_worked.worked_date > payroll.payroll_end_date;
SQL;

        if ($this->db->query($sql)->num_rows() !== 0) {
            trigger_error("Warning  {$this->db->query($sql)->num_rows()}  employee_worked  worked_payroll_id not matching payroll table actual date range", E_USER_WARNING);
        }

        die('Ok');
    }

    function patch_payroll_day()
    {
        die;
        $this->load->model('mdl_payroll');
        $payrolls = $this->mdl_payroll->get_all();
        foreach ($payrolls as $payroll) {
            $update['payroll_day'] = date('Y-m-d', strtotime("Friday this week", strtotime($payroll->payroll_end_date)));
            $this->mdl_payroll->update($payroll->payroll_id, $update);
        }
    }

    function patch_emp_login()
    {
        die;
        $this->load->model('mdl_emp_login');
        $this->load->model('mdl_employee');

        $emps = $this->mdl_employee->get()->result_array();

        foreach ($emps as $key => $emp) {
            $data['login'] = date('H:i', strtotime($emp['login_time']));
            $data['logout'] = date('H:i', strtotime($emp['logout_time']));
            $data['login_employee_id'] = $emp['employee_id'];
            $data['login_lat'] = $emp['login_lat'];
            $data['login_lon'] = $emp['login_lon'];
            $data['logout_lat'] = $emp['logout_lat'];
            $data['logout_lon'] = $emp['logout_lon'];
            $data['login_date'] = date('Y-m-d', strtotime($emp['login_time']));
            $data['login_image'] = $emp['login_image'];
            $data['logout_image'] = $emp['logout_image'];
            $data['worked_hourly_rate'] = $emp['employee_hourly_rate'];
            $data['worked_lunch'] = 0.5;
            if ($emp['no_lunch'])
                $data['worked_lunch'] = 0;
            $this->mdl_emp_login->insert($data);
        }
    }

    function ClientMapPin()
    {
        die;
        $this->load->model('mdl_clients');
        $coords = $this->mdl_clients->get_clients_coords();
        file_put_contents('uploads/coords.txt', json_encode($coords));
    }

    function equipment_last_update_patch()
    {
        die('Please Start');
        $this->load->model('mdl_equipments');

        $data['months_services'] = $this->mdl_equipments->get_service_settings(array('item_id' => 22));

        //var_dump($data['months_services'] );die;
        foreach ($data['months_services'] as $key => $val) {
            $report = $this->mdl_equipments->get_services_reports(array('equipment_services_reports.report_service_settings_id' => $val['id']), 1, 0);
            if (isset($report[0]['report_date_created'])) {
                $date['service_last_update'] = $report[0]['report_date_created'];
                if ($report[0]['report_kind'])
                    $date['service_postpone_on'] = $report[0]['report_kind'];
                else
                    $date['service_postpone_on'] = 0;
                $this->mdl_equipments->update_services_setting($val['id'], $date);
            } else
                $this->mdl_equipments->update_services_setting($val['id'], array('service_last_update' => $val['service_created'], 'service_postpone_on' => 0));
        }
    }

    function testXML($test = FALSE)
    {
        die('kk');
        $this->load->model('mdl_stumps');
        $this->load->model('mdl_user');
        $this->load->model('mdl_employees');
        $file = 'uploads/test23.xlsx';
        $res = get_stumps_data($file);

        $client_id = 3;

        unset($res[0]);

        foreach ($res as $key => $val) {
            $data = [];
            $g_address = str_replace(' ', '+', trim($val[2] . ' ' . $val[3])) . ",";

            $g_addr_str = $g_address . 'Markham,ON,' . config_item('office_country');
            $url = "https://maps.google.com/maps/api/geocode/json?address=$g_addr_str&key=AIzaSyBDUPTmUYuYnv9r8d6-TXanSefGZclfKTw";
            //key=AIzaSyBDUPTmUYuYnv9r8d6-TXanSefGZclfKTw&

            $data['stump_client_id'] = $client_id;

            if (!$test) {
                $jsonData = file_get_contents($url);
                $client_geo_address = json_decode($jsonData);

                $data['stump_address'] = NULL;

                foreach ($client_geo_address->{'results'}[0]->{'address_components'} as $key => $value) {

                    if ($value->types[0] == 'street_number')
                        $data['stump_address'] = $value->short_name . ' ';

                    if ($value->types[0] == 'route')
                        $data['stump_address'] .= $value->long_name;

                    if ($value->types[0] == 'locality')
                        $data['stump_city'] = $value->long_name;

                    if ($value->types[0] == 'administrative_area_level_1')
                        $data['stump_state'] = $value->short_name;

                }

                $data['stump_lat'] = $client_geo_address->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
                $data['stump_lon'] = $client_geo_address->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
            }

            $data['stump_assigned'] = NULL;
            $data['stump_clean_id'] = NULL;
            trim($val[6]);
            if ($val[6] != '' && $val[6] != ' ')
                $data['stump_desc'] = $val[6];

            if ($val[12] && $val[12] != '' && $val[12] != ' ') {
                //Change user id, when new xls file
                $name = explode('+', $val[12])[0];
                $name = trim(explode('?', $name)[0]);

                if ($name == 'Viacheslav Herman H.')
                    $name = 'Viacheslav Herman';
                if ($name == 'Orest Kertavykh')
                    $name = 'Orest Kartavykh';
                if ($name == 'Sebastian De Leon')
                    $name = 'Sabastian De Leon';

                $employee = $this->mdl_employees->get_employee('*', array('emp_name' => $name));
                //echo $name . "<br>";
                $employee_row = $this->mdl_employees->find_employee($employee->row_array()['employee_id']);
                $names = explode(' ', $name);
                $user = $this->mdl_user->get_user('id', array('firstname' => $names[0], 'lastname' => $names[1]));

                if (!$user) {
                    $user = $this->newUser($employee->row_array()['employee_id']);
                    $data['stump_assigned'] = $user['id'];
                } else
                    $data['stump_assigned'] = intval($user->row_array()['id']);

            }
            $user = array();
            if ($val[15] && $val[15] != '' && $val[15] != ' ') {
                //Change user id, when new xls file
                $name = explode('+', $val[15])[0];
                $name = trim(explode('?', $name)[0]);

                if ($name == 'Viacheslav Herman H.')
                    $name = 'Viacheslav Herman';
                if ($name == 'Orest Kertavykh')
                    $name = 'Orest Kartavykh';
                if ($name == 'Sebastian De Leon')
                    $name = 'Sabastian De Leon';

                $employee = $this->mdl_employees->get_employee('*', array('emp_name' => $name));
                if (!$employee)
                    die('Employee "' . $name . '" is wrong!!!');
                $employee_row = $this->mdl_employees->find_employee($employee->row_array()['employee_id']);
                $names = explode(' ', $name);
                $user = $this->mdl_user->get_user('id', array('firstname' => $names[0], 'lastname' => $names[1]));

                if (!$user) {
                    $user = $this->newUser($employee->row_array()['employee_id']);
                    $data['stump_clean_id'] = $user['id'];
                } else
                    $data['stump_clean_id'] = intval($user->row_array()['id']);

            }
            $data['stump_status'] = 'new';
            $data['stump_data'] = json_encode($val);
            $data['stump_unique_id'] = $val[0];
            $data['stump_map_grid'] = $val[1];
            $data['stump_side'] = $val[5];
            $data['stump_range'] = $val[7];
            $data['stump_locates'] = $val[8];
            $data['stump_removal'] = NULL;
            $data['stump_clean'] = NULL;


            $data['stump_status_work'] = 0;
            if ($val['color'] == '#95B3D7')
                $data['stump_status_work'] = 1;
            if ($val['color'] == '#C3D69B')
                $data['stump_status_work'] = 2;
            $data['stump_contractor_notes'] = $val[9];

            if ($val[10] && $val[10] != '' && $val[10] != ' ')
                $data['stump_removal'] = date('Y-m-d', strtotime('2016/' . $val[10]));
            if ($data['stump_removal'] && isset($data['stump_assigned']) && $data['stump_assigned'])
                $data['stump_status'] = 'grinded';

            if ($val[11] && $val[11] != '' && $val[11] != ' ')
                $data['stump_clean'] = date('Y-m-d', strtotime('2016/' . $val[11]));
            if (isset($data['stump_clean']) && $data['stump_clean'] && isset($data['stump_clean_id']) && $data['stump_clean_id'])
                $data['stump_status'] = 'cleaned_up';

            if (!$test) {
                $this->mdl_stumps->insert_stumps($data);
            }

        }
        die('Done');
    }


    function gwillimbury_xlsx_parser($test = FALSE)
    {
        $this->load->model('mdl_stumps');
        $file = 'uploads/test2.xlsx';
        $res = get_gwillimbury_stumps_data($file);
        //echo "<pre>";
        //unset($res[0]);
        foreach ($res as $key => $val) {
            $g_address = str_replace(' ', '+', trim($val[0] . ' ' . $val[1])) . ",";

            $g_addr_str = $g_address . str_replace(' ', '+', trim($val['grid'])) . ',ON,' . config_item('office_country');
            $url = "https://maps.google.com/maps/api/geocode/json?address=$g_addr_str&key=AIzaSyBDUPTmUYuYnv9r8d6-TXanSefGZclfKTw";

            $data = [];

            if (!$test) {
                $jsonData = file_get_contents($url);
                $client_geo_address = json_decode($jsonData);

                foreach ($client_geo_address->{'results'}[0]->{'address_components'} as $addresComp) {
                    if ($addresComp->types[0] == 'administrative_area_level_1')
                        $data['stump_state'] = $addresComp->long_name;
                }

                $data['stump_address'] = trim($val[0] . ' ' . $val[1]);
                $data['stump_city'] = $client_geo_address->{'results'}[0]->{'address_components'}[3]->long_name;
                $data['stump_lat'] = $client_geo_address->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
                $data['stump_lon'] = $client_geo_address->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
                //$data['adderess'] = $client_geo_address;
            }

            $data['stump_status'] = 'new';
            $data['stump_client_id'] = 2;
            $data['stump_data'] = json_encode($val);
            $data['stump_unique_id'] = NULL;
            $data['stump_map_grid'] = $val['grid'];
            $data['stump_side'] = NULL;
            $data['stump_range'] = $val[4];
            $data['stump_locates'] = (string)$val[9];
            $data['stump_removal'] = NULL;
            $data['stump_clean'] = NULL;
            $data['stump_desc'] = $val[2];

            $data['stump_status_work'] = 0;
            if ($val['color'] == '#95B3D7')
                $data['stump_status_work'] = 1;
            if ($val['color'] == '#C3D69B')
                $data['stump_status_work'] = 2;

            if (!$test) {
                $this->mdl_stumps->insert_stumps($data);
            }
        }
    }

    function patch_egtd()
    {
        die;
        set_time_limit(0);


        $this->load->model('mdl_equipments');
        $this->load->model('mdl_tracker');

        //trackingLogin();
        $this->load->driver('gps');
        if (!$this->gps->enabled()) {
            //show_error('GPS driver is disabled!');
            die;
        }

        $tracks = json_decode($this->gps->tracks());

        $data = [];
        $resultLeft = [];
        $counter = 0;


        for ($i = strtotime('2017-11-22'); $i <= strtotime(date('Y-m-d')); $i = $i + 86400) {
            $date = date('Y-m-d', $i);
            foreach ($tracks as $key => $value) {
//				$query = [
//					'op'=>'detailreport' ,
//					'reportType'=>'distance' ,
//					'unitList'=> $value->SN_IMEI_ID ,
//					'sdate'=>  date('d/m/Y 00:00', strtotime($date)),
//					'edate'=> date('d/m/Y 23:59', strtotime($date)),
//					'startSpeed'=>5,
//					'parkingMin'=>5
//				];
//				$distanceResponse = json_decode(sendPost('http://login.genuinetrackingsolutions.com/GTS/report', $query));
                $distanceResponse = json_decode($this->gps->distance($value->SN_IMEI_ID, $date));

                if (isset($distanceResponse->data) && !empty($distanceResponse->data)) {
                    $item = $this->mdl_equipments->get_item(array('item_tracker_name' => $value->SN_IMEI_ID));

                    $resultLeft[$counter]['kms'] = 0;
                    foreach ($distanceResponse->data as $a => $b)
                        $resultLeft[$counter]['kms'] += $b[6];
                    $this->mdl_equipments->insert_gps_distance(array('egtd_item_id' => $item->item_id, 'egtd_date' => $date, 'egtd_counter' => round($resultLeft[$counter]['kms'] / 1000, 2)));
                    $counter++;
                }
            }
        }
    }

    function company_letters()
    {
        set_time_limit(0);
        $companies = [

        ];

        $emails = [];
        $uniQemails = [];
        $names = [];

        foreach ($companies as $key => $value) {

            $explode = explode('|', $value);
            if (isset($explode[1]) && $explode[1]) {

                $parsedEmail = strtolower(str_replace([';', ',', ' ', '"', "'"], '', $explode[1]));
                $parsedName = $explode[0];

                if (!isset($uniQemails[$parsedEmail]) && filter_var($parsedEmail, FILTER_VALIDATE_EMAIL)) {
                    echo '"' . $companies[$key] . '",<br>';

                    $uniQemails[$parsedEmail] = true;
                    /*$data['company'] = $parsedName;
                    $user = $this->mdl_user->find_by_id(34);
                    $data['signature'] = $user->user_signature;

                    $text = $this->load->view('promo_letter_companies', $data, TRUE);

                    $this->load->library('email');
                    $config['mailtype'] = 'html';
                    $this->email->initialize($config);

                    //
                    $to = $parsedEmail;//'isq200820082008@gmail.com, yl@treedoctors.ca, yl6877@gmail.com';
                    $this->email->to($to);
                    $this->email->from('info@treedoctors.ca', 'Dave Gilbert | Tree Doctors');
                    $this->email->subject('Tree Services for Cemeteries');

                    $this->email->message($text);*/
                    //echo $text;die;


                    /*if(!$this->email->send_mailgun()) {
                        echo $to . ' - SEND ERROR<br>';
                    }*/


                }
            }
        }

        echo 'DONE !!!';

    }

    function _getCompaniesFromFile($filename)
    {
        $companies = [];
        $lines = file($filename);
        $f = fopen($filename, 'w+');

        foreach ($lines as $key => $value) {
            if ($key <= 99)
                $companies[] = rtrim(ltrim($value, '"'), '",');
            else {
                fwrite($f, $value);
            }
        }
        fclose($f);

        return $companies;
    }

    function company_letters_schools()
    {
        set_time_limit(0);
        $filename = 'uploads/letters/schools';

        $companies = $this->_getCompaniesFromFile($filename);

        $emails = [];
        $uniQemails = [];
        $names = [];
        $wasSent = 0;

        foreach ($companies as $key => $value) {

            $explode = explode('|', $value);
            if (isset($explode[1]) && $explode[1]) {

                $parsedEmail = trim(strtolower(str_replace([';', ',', ' ', '"', "'"], '', $explode[1])));
                $parsedName = $explode[0];

                if (!isset($uniQemails[$parsedEmail]) && filter_var($parsedEmail, FILTER_VALIDATE_EMAIL)) {
                    /*echo '"' . $companies[$key] . '",<br>';*/

                    $uniQemails[$parsedEmail] = true;
                    $data['company'] = $parsedName;
                    $user = $this->mdl_user->find_by_id(149);
                    $data['signature'] = $user->user_signature;

                    $text = $this->load->view('promo_letter_companies_schools', $data, TRUE);

                    $this->load->library('email');
                    $config['mailtype'] = 'html';
                    $this->email->initialize($config);

                    //
                    $to = $parsedEmail;//'isq200820082008@gmail.com, yl@treedoctors.ca, yl6877@gmail.com';
                    $this->email->to($to);
                    $this->email->from('derek.lebow@treedoctors.ca', 'Derek Lebow | Tree Doctors');
                    $this->email->subject('Tree Maintenance');

                    $this->email->message($text);

                    //echo $text;die;


                    if (!$this->email->send_mailgun()) {
                        echo $to . ' - SEND ERROR<br>';
                    } else {
                        $wasSent++;
                    }


                }
            }
        }

        echo $wasSent . ' - DONE !!!';

    }


    function company_letters_landscape()
    {
        set_time_limit(0);
        $filename = 'uploads/letters/landscapes';

        $companies = $this->_getCompaniesFromFile($filename);

        $emails = [];
        $uniQemails = [];
        $names = [];
        $wasSent = 0;

        foreach ($companies as $key => $value) {

            $explode = explode('|', $value);
            if (isset($explode[1]) && $explode[1]) {

                $parsedEmail = trim(strtolower(str_replace([';', ',', ' ', '"', "'"], '', $explode[1])));
                $parsedName = $explode[0];

                if (!isset($uniQemails[$parsedEmail]) && filter_var($parsedEmail, FILTER_VALIDATE_EMAIL)) {
                    /*echo '"' . $companies[$key] . '",<br>';*/

                    $uniQemails[$parsedEmail] = true;
                    $data['company'] = $parsedName;
                    $user = $this->mdl_user->find_by_id(149);
                    $data['signature'] = $user->user_signature;

                    $text = $this->load->view('promo_letter_companies_landscape', $data, TRUE);

                    $this->load->library('email');
                    $config['mailtype'] = 'html';
                    $this->email->initialize($config);

                    //
                    $to = $parsedEmail;//'isq200820082008@gmail.com, yl@treedoctors.ca, yl6877@gmail.com';
                    $this->email->to($to);
                    $this->email->from('derek.lebow@treedoctors.ca', 'Derek Lebow | Tree Doctors');
                    $this->email->subject('Referrals for landscape worked');

                    $this->email->message($text);

                    //echo $text;die;


                    if (!$this->email->send_mailgun()) {
                        echo $to . ' - SEND ERROR<br>';
                    } else {
                        $wasSent++;
                    }


                }
            }
        }

        echo $wasSent . ' - DONE !!!';

    }


    function company_letters_churches()
    {
        set_time_limit(0);
        $filename = 'uploads/letters/churches';

        $companies = $this->_getCompaniesFromFile($filename);

        $emails = [];
        $uniQemails = [];
        $names = [];
        $wasSent = 0;

        foreach ($companies as $key => $value) {

            $explode = explode('|', $value);
            if (isset($explode[1]) && $explode[1]) {

                $parsedEmail = trim(strtolower(str_replace([';', ',', ' ', '"', "'"], '', $explode[1])));
                $parsedName = $explode[0];

                if (!isset($uniQemails[$parsedEmail]) && filter_var($parsedEmail, FILTER_VALIDATE_EMAIL)) {
                    /*echo '"' . $companies[$key] . '",<br>';*/

                    $uniQemails[$parsedEmail] = true;
                    $data['company'] = $parsedName;
                    $user = $this->mdl_user->find_by_id(28);
                    $data['signature'] = $user->user_signature;

                    $text = $this->load->view('promo_letter_companies_churches', $data, TRUE);

                    $this->load->library('email');
                    $config['mailtype'] = 'html';
                    $this->email->initialize($config);

                    //
                    $to = $parsedEmail;//'isq200820082008@gmail.com, yl@treedoctors.ca, yl6877@gmail.com';
                    $this->email->to($to);
                    $this->email->from('info@treedoctors.ca', 'Allan Williams | Tree Doctors');
                    $this->email->subject('Protect your member\'s safety and improve the visual appearance of your facility');

                    $this->email->message($text);

                    //echo $text;die;


                    if (!$this->email->send_mailgun()) {
                        echo $to . ' - SEND ERROR<br>';
                    } else {
                        $wasSent++;
                    }

                }
            }
        }

        echo $wasSent . ' - DONE !!!';
    }


    function company_letters_roads()
    {
        set_time_limit(0);
        $filename = 'uploads/letters/roads';

        $companies = $this->_getCompaniesFromFile($filename);

        $emails = [];
        $uniQemails = [];
        $names = [];
        $wasSent = 0;

        foreach ($companies as $key => $value) {

            $explode = explode('|', $value);
            if (isset($explode[1]) && $explode[1]) {

                $parsedEmail = trim(strtolower(str_replace([';', ',', ' ', '"', "'"], '', $explode[1])));
                $parsedName = $explode[0];

                if (!isset($uniQemails[$parsedEmail]) && filter_var($parsedEmail, FILTER_VALIDATE_EMAIL)) {
                    /*echo '"' . $companies[$key] . '",<br>';*/

                    $uniQemails[$parsedEmail] = true;
                    $data['company'] = $parsedName;
                    $user = $this->mdl_user->find_by_id(28);
                    $data['signature'] = $user->user_signature;

                    $text = $this->load->view('promo_letter_companies_roads', $data, TRUE);

                    $this->load->library('email');
                    $config['mailtype'] = 'html';
                    $this->email->initialize($config);

                    //
                    $to = $parsedEmail;//'isq200820082008@gmail.com, yl@treedoctors.ca, yl6877@gmail.com';
                    $this->email->to($to);
                    $this->email->from('info@treedoctors.ca', 'Allan Williams | Tree Doctors');
                    $this->email->subject('Tree Doctors can help improve Safety and Efficiency for your crews');

                    $this->email->message($text);

                    //echo $text;die;


                    if (!$this->email->send_mailgun()) {
                        echo $to . ' - SEND ERROR<br>';
                    } else {
                        $wasSent++;
                    }

                }
            }
        }

        echo $wasSent . ' - DONE !!!';
    }

    function company_letters_real()
    {
        set_time_limit(0);
        $filename = 'uploads/letters/real';

        $companies = $this->_getCompaniesFromFile($filename);

        $emails = [];
        $uniQemails = [];
        $names = [];

        $wasSent = 0;

        foreach ($companies as $key => $value) {

            $explode = explode('|', $value);
            if (isset($explode[1]) && $explode[1]) {

                $parsedEmail = trim(strtolower(str_replace([';', ',', ' ', '"', "'"], '', $explode[1])));
                $parsedName = $explode[0];

                if (!isset($uniQemails[$parsedEmail]) && filter_var($parsedEmail, FILTER_VALIDATE_EMAIL)) {
                    /*echo '"' . $companies[$key] . '",<br>';*/

                    $uniQemails[$parsedEmail] = true;
                    $data['company'] = $parsedName;
                    $user = $this->mdl_user->find_by_id(28);
                    $data['signature'] = $user->user_signature;

                    $text = $this->load->view('promo_letter_companies_real', $data, TRUE);

                    $this->load->library('email');
                    $config['mailtype'] = 'html';
                    $this->email->initialize($config);

                    //
                    $to = $parsedEmail;//'isq200820082008@gmail.com, yl@treedoctors.ca, yl6877@gmail.com';
                    $this->email->to($to);
                    $this->email->from('info@treedoctors.ca', 'Allan Williams | Tree Doctors');
                    $this->email->subject('Tree Doctors can help increase the value of your client\'s property');

                    $this->email->message($text);

                    //echo $text;die;


                    if (!$this->email->send_mailgun()) {
                        echo $to . ' - SEND ERROR<br>';
                    } else {
                        $wasSent++;
                    }

                }
            }
        }

        echo $wasSent . ' - DONE !!!';
    }

    function company_letters_property()
    {
        set_time_limit(0);
        $filename = 'uploads/letters/property';

        $companies = $this->_getCompaniesFromFile($filename);

        $emails = [];
        $uniQemails = [];
        $names = [];

        $wasSent = 0;

        foreach ($companies as $key => $value) {

            $explode = explode('|', $value);
            if (isset($explode[1]) && $explode[1]) {

                $parsedEmail = trim(strtolower(str_replace([';', ',', ' ', '"', "'"], '', $explode[1])));
                $parsedName = $explode[0];

                if (!isset($uniQemails[$parsedEmail]) && filter_var($parsedEmail, FILTER_VALIDATE_EMAIL)) {
                    /*echo '"' . $companies[$key] . '",<br>';*/

                    $uniQemails[$parsedEmail] = true;
                    $data['company'] = $parsedName;
                    $user = $this->mdl_user->find_by_id(149);
                    $data['signature'] = $user->user_signature;

                    $text = $this->load->view('promo_letter_companies_property', $data, TRUE);

                    $this->load->library('email');
                    $config['mailtype'] = 'html';
                    $this->email->initialize($config);

                    //
                    $to = $parsedEmail;//'isq200820082008@gmail.com, yl@treedoctors.ca, yl6877@gmail.com';
                    $this->email->to($to);
                    $this->email->from('derek.lebow@treedoctors.ca', 'Derek Lebow | Tree Doctors');
                    $this->email->subject('Site Safety Inspection');

                    $this->email->message($text);

                    //echo $text;die;


                    if (!$this->email->send_mailgun()) {
                        echo $to . ' - SEND ERROR<br>';
                    } else {
                        $wasSent++;
                    }

                }
            }
        }

        echo $wasSent . ' - DONE !!!';
    }

    function get_old_estimates()
    {
        die(11);
        $this->load->model('mdl_estimates');
        $today = date('Y-m-d');
        $wdata = array();
        //$wdata['status'] = 1;
        //$wdata['date_created'] = 'date_created - ';
        $newEst = $this->mdl_estimates->get_three_days_estimates($today, array());

        foreach ($newEst as $k => $v) {
            $data['task_client_id'] = $v['client_id'];
            $data['task_desc'] = 'please call the client to make sure they received the quote and check if they have any questions about it';
            $data['task_assigned_user'] = $data['task_author_id'] = $v['user_id'];
            $data['task_category'] = 9;
            $data['task_status'] = 'new';
            $data['task_date_created'] = $today;
            $data['task_address'] = $v['lead_address'];
            $data['task_city'] = $v['lead_city'];
            $data['task_state'] = $v['lead_state'];
            $data['task_zip'] = $v['lead_zip'];
            $data['task_latitude'] = $v['latitude'];
            $data['task_longitude'] = $v['longitude'];
            $this->mdl_client_tasks->insert($data);

        }
        //SELECT * FROM `estimates` LEFT JOIN users ON estimates.user_id=users.id WHERE DATEDIFF("2018-01-29", FROM_UNIXTIME(date_created, '%Y-%m-%d')) >= 3 AND (status = 1 OR status = 2)

    }

    function patch_duplicate_clients()
    {
        $deleted = ["13615", "33308"];
        $mainClient = 13615;

        $this->load->model('mdl_clients');
        $this->load->model('mdl_leads');
        $this->load->model('mdl_estimates');
        $this->load->model('mdl_workorders');
        $this->load->model('mdl_invoices');
        $this->load->model('mdl_calls');
        $this->load->model('mdl_client_tasks');
        $this->load->model('mdl_contacts');
        $this->load->model('mdl_payments');
        $this->load->model('mdl_stumps');


        foreach ($deleted as $k => $v) {
            if ($v == $mainClient)
                continue;

            $estimates = $this->mdl_estimates->find_all(['estimates.client_id' => $v]);

            foreach ($estimates as $key => $value) {
                $this->mdl_leads->update_leads(array('client_id' => $mainClient), array('lead_id' => $value->lead_id));
                $this->mdl_estimates->update_estimates(array('client_id' => $mainClient), array('estimate_id' => $value->estimate_id));
                $this->mdl_workorders->update_workorder(array('client_id' => $mainClient), array('estimate_id' => $value->estimate_id));
                $this->mdl_invoices->update_invoice(array('client_id' => $mainClient), array('estimate_id' => $value->estimate_id));
            }
            $this->mdl_calls->update_by(array('call_client_id' => $v), array('call_client_id' => $mainClient));
            $this->mdl_clients->update_note_by(array('client_id' => $v), array('client_id' => $mainClient));
            $this->mdl_client_tasks->update_by(array('task_client_id' => $mainClient), array('task_client_id' => $v));

            //DELETE
            //clients, cc_contact
            $this->mdl_clients->complete_client_removal($v);
            $this->db->_reset_select();

        }

        /*
        $this->mdl_clients->add_client_contact([
            'cc_client_id' => $mainClient,
            'cc_title' => 'DUPLICATE COPY',
            'cc_name' => '',
            'cc_phone' => '',
            'cc_email' => '',
            'cc_email_check' => NULL,
            'cc_print' => 0,
        ]);
        */

    }

    function patch_notes()
    {
        $this->load->model('mdl_clients');

        //$clients = $this->mdl_clients->get_clients()->result_array();
        //$notes = $this->mdl_clients->get_notes(17084);
        //$all = [4316, 24059, 6611, 70146, 4393, 14478, 15005, 14770, 5488,  13223, 4964, 2846, 1582, 2286, 5822, 6695, 3582];
        //var_dump($all); die;
        //NEED TO CHECK
        //SELECT * FROM `client_notes` WHERE lead_id IS NULL ORDER BY client_id
        // client_id = -22660 client_id = 0 ???  4449 rows  ?????
        // client_note = '' ??? 5843 rows ????

        /**************CHANGE 4 rows***************/
        //SELECT * FROM `client_notes` WHERE lead_id IS NULL AND client_note LIKE '%Priority for workorder%' ORDER BY `client_notes`.`client_note` ASC
        /**************CHANGE 4 rows***************/


        /***********CALLS TO CLIENTS***********/
        /* FIRST
        $notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL', ['client_note' => 'call-info'])->result_array();

        foreach($notes as $k=>$v)
        {
            $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), ['lead_id' => 0]);
        }
        echo '<pre>'; var_dump($this->db->last_query()); die;
        */
        /***********CALLS TO CLIENTS***********/
        /*********************Estimate created***********************/
        //SELECT * FROM (`client_notes`) WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%Estimate%create%' ORDER BY `client_notes`.`client_note` ASC
        /* SECOND => 24059
        $notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0 AND client_note LIKE "%Estimate%create%"', [])->result_array();

        foreach($notes as $k=>$v)
        {
            $res = preg_match("/([0-9]{5}\-[L,I,W,E])/", $v['client_note'], $matches, PREG_OFFSET_CAPTURE);
            //echo '<pre>'; var_dump($res, $matches); die;
            if($res)
            {
                $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), array('lead_id' => intval($matches[1][0])));
            }
        }*/

        //echo '<pre>'; var_dump($this->db->last_query()); die;
        /*********************Estimate created ***********************/
        /*********************Estimate update***********************/
        //SELECT * FROM (`client_notes`) WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%Estimate%update%' ORDER BY `client_notes`.`client_note` ASC
        /* THIRD
        $notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0 AND client_note LIKE "%Estimate%update%"', [])->result_array();

        foreach($notes as $k=>$v)
        {
            $res = preg_match("/([0-9]{5}\-[L,I,W,E])/", $v['client_note'], $matches, PREG_OFFSET_CAPTURE);
            //echo '<pre>'; var_dump($res, $matches); die;
            if($res)
            {
                $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), array('lead_id' => intval($matches[1][0])));
            }
        }
        */
        //echo '<pre>'; var_dump($this->db->last_query()); die;
        /*********************Estimate update ***********************/
        /*********************Status for XXXXXXX was modified 6273 rows***********************/
        /* FORTH
        $notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_note LIKE "%Status for%was modified%"', [])->result_array();

        foreach($notes as $k=>$v)
        {
            $res = preg_match("/([0-9]{5}\-[L,I,W,E])/", $v['client_note'], $matches, PREG_OFFSET_CAPTURE);
            if($res)
            {
                $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), array('lead_id' => intval($matches[1][0])));
            }
        }
        * */
        //echo '<pre>'; var_dump($this->db->last_query()); die;
        //SELECT * FROM `client_notes` WHERE lead_id IS NULL AND client_note LIKE '%Status for%was modified%' ORDER BY `client_notes`.`client_note` ASC
        /*********************Status for XXXXXXX was modified***********************/

        /***********CALLS TO CLIENTS***********/
        /*FIFTH
        $notes = $this->mdl_clients->get_insert_notes('client_id > 0', ['client_note' => 'twilio.com'])->result_array();

        foreach($notes as $k=>$v)
        {
            $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), ['lead_id' => 0]);
        }
        */
        /***********CALLS TO CLIENTS***********/
        //echo '<pre>'; var_dump($notes); die;

        /***********UPDATE LEAD ID CLIENT NOTE***********/
        //SIXTH
        //$this->mdl_clients->update_note_by(array('client_note' => 'Hey, I just created a new client.'), ['lead_id' => 0]);
        /***********UPDATE LEAD ID CLIENT NOTE***********/
        /***********WHERE ISSET LEAD ID IN NOTE***********/
        /*
        foreach($clients as $k=>$v)
        {
            $notes = $this->mdl_clients->get_notes($v['client_id']);
            if(count($notes))
            {
                foreach($notes as $key=>$val)
                {
                    $res = preg_match("/([0-9]{5}\-[L,I,W,E])/", $val['client_note'], $matches, PREG_OFFSET_CAPTURE);
                    if($res)
                    {
                        $a++;
                        $this->mdl_clients->update_note_by(array('client_note_id' => $val['client_note_id']), array('lead_id' => intval($matches[1][0])));
                    }
                }
            }
        }
        */
        //var_dump(count($notes), $a); die;
        /***********WHERE ISSET LEAD ID IN NOTE***********/


        /***********CREATED SERVICES ONLY ONE ESTIMATE***********/
        // SEVENTH
        //NOT PLUS SECONDS ON LIVE SERVER
        /*$notes = $this->mdl_clients->get_insert_notes('estimates.date_created >= (UNIX_TIMESTAMP(client_notes.client_note_date) + 25198)
                  AND estimates.date_created <= (UNIX_TIMESTAMP(client_notes.client_note_date) + 25202)',
                  ['client_note' => 'Insert services:'], TRUE)->result_array();

        foreach($notes as $k=>$v)
        {
            if(!$v['lead_id'] && $v['est_lead_id'])
                $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), ['lead_id' => $v['est_lead_id']]);

        }
        /***********CREATED SERVICES ONLY ONEW ESTIMATE***********/


        /***********UPDATE SERVICES ONLY ONEW ESTIMATE***********/
        //WHERE ONLY ONE ESTIMATE
        /*$a = 0;
        $notes = $this->mdl_clients->get_insert_notes('', ['client_note' => 'Update services:'])->result_array();
        $this->load->model('mdl_estimates');
        foreach($notes as $k=>$v)
        {
            $estimates = $this->mdl_estimates->get_estimates('', '', '', '', "estimates.estimate_id", "DESC", ['estimates.client_id' => $v['client_id']]);

            if($estimates && $estimates->num_rows() == 1)
            {
                $a++;
                $estimates = $estimates->row_array();
                $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), ['lead_id' => $estimates['lead_id']]);
            }

        }
        var_dump($a); die;*/
        /***********UPDATE SERVICES ONLY ONEW ESTIMATE***********/


        /***********CHANGE STATUS ONLY ONE ESTIMATE***********/
        /* nineth
        $notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL', ['client_note' => 'Change status from'])->result_array();
        $a = 0;
        $this->load->model('mdl_estimates');
        foreach($notes as $k=>$v)
        {
            $estimates = $this->mdl_estimates->get_estimates('', '', '', '', "estimates.estimate_id", "DESC", ['estimates.client_id' => $v['client_id']]);

            if($estimates && $estimates->num_rows() == 1)
            {
                $a++;
                $estimates = $estimates->row_array();
                $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), ['lead_id' => $estimates['lead_id']]);
            }

        }
        var_dump(count($notes)); die;*/
        /***********CHANGE STATUS ONLY ONE ESTIMATE***********/

        /**************UPDATE CLIENT DATA*********/
        //SELECT * FROM `client_notes` WHERE lead_id IS NULL AND client_id > 0 AND client_note LIKE '%Client%was modified%' AND client_note NOT LIKE '%Update services:%' ORDER BY `client_notes`.`client_note` ASC
        /* tenth 13223
         $notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0 AND client_note LIKE "%Client%was modified%" AND client_note NOT LIKE "%Update services:%"', [])->result_array();
        //echo '<pre>'; var_dump($this->db->last_query()); die;

        foreach($notes as $k=>$v)
        {
            $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), ['lead_id' => 0]);
        }
        */
        /**************UPDATE CLIENT DATA*********/
        //SELECT * FROM `client_notes` WHERE lead_id IS NULL AND client_id > 0 AND client_note LIKE '%I just created a new lead%' ORDER BY `client_notes`.`client_note_id` ASC

        /**************CREATE NEW LEAD***********/
        /* ELEVENTH
        $notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0', ['client_note' => 'I just created a new lead'])->result_array();

        foreach($notes as $k=>$v)
        {
            $res = preg_match("/([0-9]{5}\-[L,I,W,E])/", $v['client_note'], $matches, PREG_OFFSET_CAPTURE);

            if($res)
            {
                $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), array('lead_id' => intval($matches[1][0])));
            }
        }
        */
        /**************CREATE NEW LEAD***********/
        /**************CREATE NEW TASK***********/
        //SELECT * FROM (`client_notes`) WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%I just created a new task%' ORDER BY `client_note_id` ASC
        /* twelfth 2846
        $notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0', ['client_note' => 'I just created a new task'])->result_array();

        foreach($notes as $k=>$v)
        {
            $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), ['lead_id' => 0]);
        }
        */
        /**************CREATE NEW TASK***********/

        //SELECT * FROM (`client_notes`) WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%No answer%'
        /***********CALLS TO CLIENTS***********/
        /*thirteen 1577
        $notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0', ['client_note' => 'No answer'])->result_array();

        foreach($notes as $k=>$v)
        {
            $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), ['lead_id' => 0]);
        }
        echo '<pre>'; var_dump($this->db->last_query()); die;
        */
        /***********CALLS TO CLIENTS***********/

        //SELECT * FROM (`client_notes`) WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%Client Task%'
        /***********CLIENT TASKS***********/
        /*fourteen 2286
        $notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0', ['client_note' => 'Client Task'])->result_array();

        foreach($notes as $k=>$v)
        {
            $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), ['lead_id' => 0]);
        }
        echo '<pre>'; var_dump($this->db->last_query()); die;
        */
        /***********CLIENT TASKS***********/
        //SELECT * FROM (`client_notes`) WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%emailed%'
        /***********CLIENT EMAIL***********/
        /*fifthteen 1138
        $notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0 AND client_note = "emailed"', [])->result_array();

        foreach($notes as $k=>$v)
        {
            $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), ['lead_id' => 0]);
        }
        //4540
        $notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0', ['client_note' => 'emailed'])->result_array();

        foreach($notes as $k=>$v)
        {
            $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), ['lead_id' => 0]);
        }
        echo '<pre>'; var_dump($this->db->last_query()); die;
        */

        /***********CLIENT EMAIL***********/
        /***********CLIENT CALLS***********/
        //SELECT * FROM (`client_notes`) WHERE `lead_id` IS NULL AND client_note LIKE '%Client called%' ORDER BY `client_notes`.`client_note` ASC
        /*sixteen
        $notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0', ['client_note' => 'Client called'])->result_array();

        foreach($notes as $k=>$v)
        {
            $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), ['lead_id' => 0]);
        }

        echo '<pre>'; var_dump($this->db->last_query()); die;
        */
        /***********CLIENT CALLS***********/
        /***********CLIENT CALLS***********/
        //SELECT * FROM (`client_notes`) WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%called%' ORDER BY `client_notes`.`client_note` ASC
        /* 3991
        $notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0', ['client_note' => 'called'])->result_array();

        foreach($notes as $k=>$v)
        {
            $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), ['lead_id' => 0]);
        }

        echo '<pre>'; var_dump($this->db->last_query()); die;
        */
        /***********CLIENT CALLS***********/
        /***********CLIENT EMAIL***********/
        //SELECT * FROM (`client_notes`) WHERE `lead_id` IS NULL AND client_note LIKE '%client email%' ORDER BY `client_notes`.`client_note` ASC
        /*seventeen 13
        $notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0', ['client_note' => 'client email'])->result_array();

        foreach($notes as $k=>$v)
        {
            $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), ['lead_id' => 0]);
        }

        echo '<pre>'; var_dump($this->db->last_query()); die;
        */
        /***********CLIENT EMAIL***********/
        //109 UPDATE `client_notes` SET `lead_id`= 0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%last call%'
        //2495 UPDATE `client_notes` SET `lead_id`=0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%left message%'
        //3126 UPDATE `client_notes` SET `lead_id`=0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%left vm%'
        //1980 UPDATE `client_notes` SET `lead_id`=0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%forward%'
        //69 UPDATE `client_notes` SET `lead_id`=0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%fwd to YL.%'
        //963 UPDATE `client_notes` SET `lead_id`=0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%Good afternoon%'
        //667 UPDATE `client_notes` SET `lead_id`=0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%Good morning%'
        //459 UPDATE `client_notes` SET `lead_id`=0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%happening%'
        //3342 UPDATE `client_notes` SET `lead_id`=0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%stump %'
        //641 UPDATE `client_notes` SET `lead_id`=0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%he said%'
        //127 UPDATE `client_notes` SET `lead_id`=0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%explained%'
        //92 UPDATE `client_notes` SET `lead_id`= 0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%from client%'
        //886 UPDATE `client_notes` SET `lead_id`=0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%@%'
        //389 UPDATE `client_notes` SET `lead_id`=0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%fwd to%'
        //SELECT * FROM (`client_notes`) WHERE `lead_id` IS NULL AND client_id > 0 AND client_note REGEXP "[0-9][5]-[E,I,W,L]" AND client_note NOT LIKE '%Change status from%' AND client_note NOT LIKE '%Update services:%' ORDER BY `client_notes`.`client_note` ASC
        /*$notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0 AND client_note REGEXP "[0-9][5]-[E,I,W,L]"', [])->result_array();
        0
        foreach($notes as $k=>$v)
        {
            $res = preg_match("/([0-9]{5}\-[L,I,W,E])/", $v['client_note'], $matches, PREG_OFFSET_CAPTURE);
            //echo '<pre>'; var_dump($res, $matches); die;
            if($res)
            {
                $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), array('lead_id' => intval($matches[1][0])));
            }
        }*/
        //SELECT * FROM (`client_notes`) WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%Hello%' ORDER BY `client_notes`.`client_note` ASC
        /*$notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0', ['client_note' => 'Hello'])->result_array();
        foreach($notes as $k=>$v)
        {
            $res = preg_match("/([0-9]{5}\-[L,I,W,E])/", $v['client_note'], $matches, PREG_OFFSET_CAPTURE);
            //echo '<pre>'; var_dump($res, $matches); die;
            if($res)
            {
                $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), array('lead_id' => intval($matches[1][0])));
            }
            else //593
                $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), array('lead_id' => 0));
        }*/
        //SELECT * FROM (`client_notes`) WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%contact%' ORDER BY `client_notes`.`client_note` ASC
        /*$notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0', ['client_note' => 'contact'])->result_array();

        foreach($notes as $k=>$v)
        {
            $res = preg_match("/([0-9]{5}\-[L,I,W,E])/", $v['client_note'], $matches, PREG_OFFSET_CAPTURE);
            //echo '<pre>'; var_dump($res, $matches); die;
            if($res)
            {
                //0
                $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), array('lead_id' => intval($matches[1][0])));
            }
            else {
                //757
                $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), array('lead_id' => 0));
            }
        }
        var_dump(count($notes), $a, $b); die; */
        //755 UPDATE `client_notes` SET `lead_id`=0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%he will%'
        /*$notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0 AND client_note != ""', [])->result_array();

        foreach($notes as $k=>$v)
        {
            $res = preg_match("/([0-9]{5}\-[L,I,W,E])/", $v['client_note'], $matches, PREG_OFFSET_CAPTURE);
            //echo '<pre>'; var_dump($res, $matches); die;
            if($res)
            {
                0
                $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), array('lead_id' => intval($matches[1][0])));
            }
        }
        var_dump(count($notes), $a); die;*/
        //234 UPDATE `client_notes` SET `lead_id`=0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%Hi Olga%'
        /**************UPDATE USERS LISTS**********/
        /*$this->load->model('mdl_user');
        $users = $this->mdl_user->get_user(null, 'firstname != ""')->result_array();

        foreach($users as $key=>$val)
        {
            $notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0 AND client_note != ""', ['client_note' => 'Hi ' . $val['firstname']])->result_array();
            if(count($notes))
            {
                foreach($notes as $k=>$v)
                {
                    $res = preg_match("/([0-9]{5}\-[L,I,W,E])/", $v['client_note'], $matches, PREG_OFFSET_CAPTURE);
                    //echo '<pre>'; var_dump($res, $matches); die;
                    if($res) {
                        $a++;
                        $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), array('lead_id' => intval($matches[1][0])));
                    }
                    else { //562
                        $b++;
                        $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), array('lead_id' => 0));
                    }
                }
            }
        }
        var_dump(count($notes), $a, $b); die;
        */
        /**************UPDATE USERS LISTS**********/
        //SELECT * FROM `client_notes` WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%Hi,%' ORDER BY `client_note` ASC

        /*
        $notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0 AND client_note != ""', ['client_note' => 'Hi,'])->result_array();
        $a=$b=0;
        //echo '<pre>'; var_dump($this->db->last_query()); die;
        foreach($notes as $k=>$v)
        {
            $res = preg_match("/([0-9]{5}\-[L,I,W,E])/", $v['client_note'], $matches, PREG_OFFSET_CAPTURE);
            //echo '<pre>'; var_dump($res, $matches); die;
            if($res) {
                $a++;
                $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), array('lead_id' => intval($matches[1][0])));
            }
            else { //387
                $b++;
                $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), array('lead_id' => 0));
            }
        }
        var_dump(count($notes), $a, $b); die; */
        /*
        $notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL',
                  ['client_note' => 'Insert services:'])->result_array();
        $a=0;
        foreach($notes as $k=>$v)
        {
            $this->load->model('mdl_estimates');


            $where['estimates.client_id'] = $v['client_id'];
            $estimates = $this->mdl_estimates->get_estimates('', '', '', '', 'estimates.estimate_id', 'desc', $where);

            if($estimates && $estimates->num_rows() == 1)
            {
                //890
                $a++;
                $estimates = $estimates->row_array();
                $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), ['lead_id' => $estimates['lead_id']]);
            }
        }
        var_dump(count($notes), $a); die;
        */

        //WHERE client_notes.`lead_id` IS NULL AND client_notes.client_id > 0 AND client_note LIKE '%New lead%was created%'
        /*$notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_notes.client_id > 0 AND client_note LIKE "%New lead%was created%"',
                  [])->result_array();
        $a=0;
        //2190
        foreach($notes as $k=>$v)
        {
            $this->load->model('mdl_leads');

            //leads.lead_date_created = DATE_FORMAT(client_notes.client_note_date, "%Y-%m-%d") AND leads.client_id = client_notes.client_id
            $where['lead_date_created'] = date('Y-m-d', strtotime($v['client_note_date']));
            $where['leads.client_id'] = $v['client_id'];
            $leads = $this->mdl_leads->get_leads($where, '');
            //echo '<pre>'; var_dump($leads->num_rows()); die;
            if($leads && $leads->num_rows() == 1)
            {
                $a++;
                $leads = $leads->row_array();
                $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), ['lead_id' => $leads['lead_id']]);
            }
        }
        var_dump($a); die;
        */

        //SELECT * FROM (`client_notes`) WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%New crews:%' ORDER BY `client_notes`.`client_note` ASC
        /*
        $notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0',
                  ['client_note' => 'New crews:'])->result_array();
        //echo '<pre>'; var_dump($notes); die;
        //52
        $a = 0;
        foreach($notes as $k=>$v)
        {
            $this->load->model('mdl_leads');

            //leads.lead_date_created = DATE_FORMAT(client_notes.client_note_date, "%Y-%m-%d") AND leads.client_id = client_notes.client_id
            //$where['lead_date_created'] = date('Y-m-d', strtotime($v['client_note_date']));
            $where['leads.client_id'] = $v['client_id'];
            $leads = $this->mdl_leads->get_leads($where, '');
            //;
            if($leads && $leads->num_rows() == 1)
            {
                $a++;
                $leads = $leads->row_array();
                $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), ['lead_id' => $leads['lead_id']]);
            }
        }
        var_dump($a); die;
        */
        //175
        //UPDATE `client_notes` SET `lead_id`=0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%No VM%'
        //
        /*******TROOOOOOOOUBLEEEEESSSSSS*******/
        //2883
        //SELECT * FROM `client_payments` JOIN estimates ON client_payments.estimate_id = estimates.estimate_id LEFT JOIN client_notes ON estimates.client_id = client_notes.client_id AND DATE_FORMAT(FROM_UNIXTIME(payment_date), '%Y-%m-%d') = DATE_FORMAT(client_note_date, '%Y-%m-%d') WHERE client_notes.lead_id IS NULL AND client_notes.client_id > 0 AND client_note LIKE '%Payment by%'
        /*
        $this->db->select('client_notes.*, estimates.lead_id as est_lead_id');
        $this->db->join('estimates', 'client_payments.estimate_id = estimates.estimate_id');
        $this->db->join('client_notes', "estimates.client_id = client_notes.client_id AND DATE_FORMAT(FROM_UNIXTIME(payment_date), '%Y-%m-%d') = DATE_FORMAT(client_note_date, '%Y-%m-%d')", 'left');
        $this->db->where("client_notes.lead_id IS NULL AND client_notes.client_id > 0 AND client_note LIKE '%Payment by%'");
        $notes = $this->db->get('client_payments')->result_array();
        //echo '<pre>'; var_dump($notes); die;

        foreach($notes as $k=>$v)
        {
            if($v['lead_id'] == NULL && $v['est_lead_id'])
                $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), ['lead_id' => $v['est_lead_id']]);
        }
        die('ok');
        */
        /*******TROOOOOOOOUBLEEEEESSSSSS*******/
        // 678 UPDATE `client_notes` SET `lead_id`=0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%phone%'
        //3883 UPDATE `client_notes` SET `lead_id`=0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%price%'
        //373 UPDATE `client_notes` SET `lead_id`=0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%resent%'
        // 370 UPDATE `client_notes` SET `lead_id`=0 WHERE `lead_id` IS NULL AND client_id > 0 AND (client_note LIKE 'sent email%' OR client_note LIKE 'sent exemption%')
        //SELECT * FROM (`client_notes`) WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%Update%services:%' ORDER BY `client_notes`.`client_note` ASC

        //~692
        /*
        $coount = 0;
        $notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0 AND client_note LIKE "%Update%services:%"',
                  [])->result_array();
        $this->load->model('mdl_services');
        $this->load->model('mdl_estimates');
        //echo '<pre>'; var_dump($notes); die;
        $services = $this->mdl_services->get_all_services();
        $fields = [
                'service_description',
                'service_time',
                'service_travel_time',
                'service_price',
                'service_priority',
                'service_size',
                'service_reason',
                'service_species',
                'service_permit',
                'service_disposal_time',
                'service_wood_chips',
                'service_wood_trailers',
                'service_front_space',
                'service_disposal_brush',
                'service_disposal_wood',
                'service_cleanup',
                'service_access',
                'service_client_home',
                'service_exemption',
                'service_status'
                    ];

        //SELECT * FROM (`client_notes`) WHERE `lead_id` IS NULL AND client_id > 0 AND client_note != ''

        foreach($notes as $k=>$v)
        {
            $where = $matches = [];
            $res = preg_match_all("/<li>(.*?<ul>.*?<\/ul>.*?)<\/li>/is", $v['client_note'], $matches);

            if(count($matches) && count($matches[1]))
            {
                foreach($matches[1] as $key=>$val)
                {
                    foreach($services as $jkey=>$jval)
                    {
                        //echo '<pre>'; var_dump(strpos($val, $jval->service_name));
                        if(strpos($val, $jval->service_name) !== FALSE)
                        {
                            //var_dump($jkey); die();
                            $where['service_id'] = $jval->service_id;
                            $where['estimates.client_id'] = $v['client_id'];
                            $field = '';
                            preg_match_all('/<ul><li>.*?To: "(.*?)"<\/li><\/ul>/is', $val, $serviceVal);
                            preg_match_all('/<ul><li>(.*?):/is', $val, $serviceField);
                            if(isset($serviceField[1][0]))
                                $field = 'service_' . str_replace(' ', '_', strtolower($serviceField[1][0]));

                            if(isset($serviceField[1][0]) && strpos($serviceField[1][0], 'service description was modified from'))
                                break;
                            if(isset($serviceField[1][0]) && isset($serviceVal[1][0]) && array_search($field , $fields) !== FALSE)
                            {
                                if(strpos($serviceField[1][0], 'Time') !== FALSE)
                                {
                                    preg_match("/(\d*(?:\.\d+)?)/", $serviceVal[1][0], $int, PREG_OFFSET_CAPTURE);
                                    $where['service_' . str_replace(' ', '_', strtolower($serviceField[1][0]))] = $int[0][0];
                                }
                                elseif(strpos($serviceField[1][0], 'Description') !== FALSE)
                                {
                                    preg_match_all('/<small>(.*?)<\/small>/is', $serviceVal[1][0], $desc);
                                    $where['service_' . str_replace(' ', '_', strtolower($serviceField[1][0]))] = $desc[1][0];
                                }
                                else
                                    $where['service_' . str_replace(' ', '_', strtolower($serviceField[1][0]))] = $serviceVal[1][0];
                            }
                        }
                        if(count($where) == 3)
                        {
                            $estData = $this->mdl_estimates->get_estimates_by_service($where);
                            //echo '<pre>'; var_dump($estData); die;
                            if($estData && count($estData) == 1)
                            {
                                $coount++;
                                $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), ['lead_id' => $estData[0]['lead_id']]);
                            }
                        }

                    }
                    //die;

                }

            }



        }
        var_dump($coount); die;
        ****/
        //795 UPDATE `client_notes` SET lead_id = 0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note != '' AND client_note LIKE '%asked%' ORDER BY `client_note` ASC
        //UPDATE `client_notes` JOIN estimates ON estimates.client_id = client_notes.client_id  JOIN client_payments ON estimates.estimate_id = client_payments.estimate_id AND client_payments.payment_author = client_notes.author SET client_notes.lead_id = estimates.lead_id
        //156 UPDATE `client_notes` SET lead_id = 0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%confirmed for%' ORDER BY `client_note` ASC
        //120 UPDATE `client_notes` SET lead_id = 0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%consult%' ORDER BY `client_note` ASC
        //200 UPDATE `client_notes` SET lead_id = 0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%credit card%' ORDER BY `client_note` ASC
        //665 UPDATE  `client_notes` SET lead_id = 0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%cust%' ORDER BY `client_note` ASC
        //143 UPDATE `client_notes` SET lead_id = 0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%decided%' ORDER BY `client_note` ASC
        /*495
        $notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0 AND client_note LIKE "%deposit%"',
                  [])->result_array();
        $this->load->model('mdl_estimates');
        $count = 0;
        foreach($notes as $k=>$v)
        {
            $estData = $this->mdl_estimates->get_estimates('', '', '', '', 'estimates.estimate_id', 'desc', ['estimates.client_id' => $v['client_id']])->result_array();

            if(count($estData) == 1)
            {
                $this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), ['lead_id' => $estData[0]['lead_id']]);
                $count++;
            }
        }
        echo '<pre>'; var_dump($count); die;
        */
        /*$notes = $this->mdl_clients->get_insert_notes('lead_id IS NULL AND client_id > 0',
                  [])->result_array();
        //echo '<pre>'; var_dump($notes); die;
        $this->load->model('mdl_estimates');
        $count = 0;
        foreach($notes as $k=>$v)
        {
            $estData = $this->mdl_estimates->get_estimates('', '', '', '', 'estimates.estimate_id', 'desc', ['estimates.client_id' => $v['client_id']]);

            if($estData && $estData->num_rows() == 1)
            {
                //$this->mdl_clients->update_note_by(array('client_note_id' => $v['client_note_id']), ['lead_id' => $estData[0]['lead_id']]);
                $count++;
            }
        }
        echo '<pre>'; var_dump($count); die;*/
        //682 UPDATE `client_notes` SET lead_id = 0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%done by%' AND client_note NOT LIKE '%update%service%' ORDER BY `client_notes`.`client_note_id` ASC
        //2366 UPDATE `client_notes` SET lead_id = 0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%email%' ORDER BY `client_notes`.`client_note_id` ASC
        // 12818 UPDATE `client_notes` SET lead_id = 0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%new client.%' ORDER BY `client_notes`.`client_note_id` ASC
        //464 UPDATE `client_notes` SET lead_id = 0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%message%'
        //889 UPDATE `client_notes` SET lead_id = 0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%left%vm%'
        //590 UPDATE `client_notes` SET lead_id = 0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%left%voice%mail%'
        //532 UPDATE `client_notes` SET lead_id = 0 WHERE `lead_id` IS NULL AND client_id > 0 AND client_note LIKE '%Lvm%'
        /*
        SELECT *
        FROM (`client_notes`)
        WHERE `lead_id` IS NULL
        AND client_id > 0
        AND client_note != ''
        AND client_note NOT LIKE '%Change status from%'
        AND client_note NOT LIKE '%Update%services:%'
        ORDER BY `client_notes`.`client_note` ASC
        */
        var_dump('ok');
        die;

    }

    function equipmenMainCommentPatch()
    {
        $this->load->model('mdl_repairs');
        $this->load->model('mdl_repairs_notes');
        $this->load->model('mdl_equipments');
        $repairs = $this->mdl_repairs->with('repair_notes')->get_all_data();

        foreach ($repairs as $k => $repair) {

            //echo '<pre>'; var_dump(count($repair->repair_notes), $repair->repair_notes[count($repair->repair_notes)-1]->equipment_note_text); die;
            if (!empty($repair->repair_notes) && $repair->repair_notes[count($repair->repair_notes) - 1]->equipment_note_text) {
                //$this->mdl_equipments->update_items(array('item_main_comment' => $repair->repair_notes[count($repair->repair_notes)-1]->equipment_note_text), $repair->item_id);
                $this->mdl_repairs->update($repair->repair_id, array('repair_first_comment' => $repair->repair_notes[count($repair->repair_notes) - 1]->equipment_note_text));

                $this->mdl_repairs_notes->delete($repair->repair_notes[count($repair->repair_notes) - 1]->equipment_note_id);

            }

            //update_items
            //item_main_comment
        }
        $repairs = $this->mdl_repairs->with('repair_notes')->get_all_data();
        echo '<pre>';
        var_dump($repairs);
        die;
    }

    function removeDuplicatesXLSX()
    {
        $file = 'uploads/stumps_report_2018-05-28 12_00_01.xlsx';
        $this->load->library('excel');

        $objReader = PHPExcel_IOFactory::load($file);
        $objWriter = PHPExcel_IOFactory::createWriter($objReader, 'Excel2007');
        $grindedSheet = $objReader->getSheet(1);

        $highestRow = $grindedSheet->getHighestRow();
        $highestColumn = 'I';

        $grindedData = [];
        $grindedFullData = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = $grindedSheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);

            if (!empty($rowData)) {
                $grindedFullData[] = $rowData[0];
                unset($rowData[0][6]);
                unset($rowData[0][0]);
                $grindedData[] = array_values($rowData[0]);
            }
        }


        $oldSheet = $objReader->getSheet(4);

        $highestRow = $oldSheet->getHighestRow();
        $highestColumn = 'G';

        $oldData = [];

        for ($row = 1; $row <= $highestRow; $row++) {
            $rowData = $oldSheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);

            if (!empty($rowData)) {
                $oldData[] = array_values($rowData[0]);
            }
        }


        foreach ($oldData as $key => $value) {
            $rowNumber = array_search($value, $grindedData);


            if ($rowNumber !== FALSE) {
                //var_dump($grindedFullData[$rowNumber][0]);die;
                $objReader->setActiveSheetIndex(4);
                $oldSheet->setCellValueByColumnAndRow(7, ($key + 1), $grindedFullData[$rowNumber][0]);
                $oldSheet->getStyle('H' . ($key + 1))->getAlignment()->setWrapText(true);
                $oldSheet->getStyle('H' . ($key + 1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
                $oldSheet->getStyle('H' . ($key + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                unset($grindedData[$rowNumber]);
                unset($grindedFullData[$rowNumber]);
                //$grindedSheet->removeRow($rowNumber + 2);
                //echo ($rowNumber + 2) . "<br>";
                //die;
            }
        }

        //var_dump($grindedFullData);die;

        $sheet = $objReader->createSheet(5);
        $objReader->setActiveSheetIndex(5);
        // Подписываем лист
        $sheet->setTitle('New Grinded');

        $sheet->getRowDimension(1)->setRowHeight(50);
        $sheet->freezePane('A2');// Закрепляем 1 строку


        $columns = [
            'A' => ['name' => '#', 'width' => '3.5'],
            'B' => ['name' => 'Block', 'width' => '1.6'],
            'C' => ['name' => 'Address', 'width' => '6'],
            'D' => ['name' => 'Loc. Info', 'width' => '6'],
            'E' => ['name' => 'Dist.', 'width' => '2.25'],
            'F' => ['name' => 'Size', 'width' => '2.25'],
            'G' => ['name' => 'Locates', 'width' => '4'],
            'H' => ['name' => 'Notes', 'width' => '4'],
            'I' => ['name' => 'Grinded', 'width' => '7'],
            'J' => ['name' => 'Cleaned', 'width' => '7'],
        ];

        foreach ($columns as $col => $val) {
            $sheet->setCellValue($col . "1", $val['name']);
            $sheet->getStyle($col . '1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $sheet->getStyle($col . '1')->getFont()->setSize(12);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($col . '1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $sheet->getStyle($col . '1')->getAlignment()->setWrapText(true);
            $sheet->getStyle($col . '1')->getBorders()->getBottom()->applyFromArray(array('style' => PHPExcel_Style_Border::BORDER_MEDIUM));
            $sheet->getStyle($col . '1')->getBorders()->getRight()->applyFromArray(array('style' => PHPExcel_Style_Border::BORDER_THIN));
            $sheet->getColumnDimension($col)->setWidth(floatval($val['width']) * 4.054);
        }

        $grid = NULL;
        $gridStart = 1;
        $row = 2;


        foreach ($grindedFullData as $key => $stump) {

            $sheet->setCellValueByColumnAndRow(0, $row, $stump[0]);
            $sheet->getStyle('A' . $row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('A' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValueByColumnAndRow(1, $row, $stump[1]);
            $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('B' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValueByColumnAndRow(2, $row, $stump[2]);
            $sheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('C' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);

            $sheet->setCellValueByColumnAndRow(3, $row, $stump[3]);
            $sheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('D' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValueByColumnAndRow(4, $row, $stump[4]);
            $sheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('E' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValueByColumnAndRow(5, $row, $stump[5]);
            $sheet->getStyle('F' . $row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('F' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValueByColumnAndRow(6, $row, $stump[6]);
            $sheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('G' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValueByColumnAndRow(7, $row, $stump[7]);
            $sheet->getStyle('H' . $row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('H' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);

            $sheet->setCellValueByColumnAndRow(8, $row, $stump[8]);
            $sheet->getStyle('I' . $row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('I' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
            $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            /*$sheet->setCellValueByColumnAndRow(9, $row, $stump[9]);
            $sheet->getStyle('J' . $row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('J' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
            $sheet->getStyle('J' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);*/


            $row++;
        }

        foreach ($sheet->getRowDimensions() as $rd) {
            $rd->setRowHeight(-1);
        }
        $objWriter->save($file);
        //echo "<pre>";var_dump($grindedData);die;
        return $grindedData;
    }

    function patch_estimate_equipmets()
    {
        $relations = [
            '6' => ['id' => '5', 'option' => NULL, 'type' => 'vehicle'],//pick up truck
            '2' => ['id' => '6', 'option' => 'large', 'type' => 'vehicle'],//bucket truck large
            '16' => ['id' => '6', 'option' => 'small', 'type' => 'vehicle'],//bucket truck small
            '8' => ['id' => '8', 'option' => 'Large', 'type' => 'vehicle'],//chiper truck large
            '9' => ['id' => '8', 'option' => 'Small', 'type' => 'vehicle'],//chiper truck Small
            '22' => ['id' => '8', 'option' => 'Milk Truck', 'type' => 'vehicle'],//chiper truck Milk Truck
            '4' => ['id' => '9', 'option' => 'Large Hiab', 'type' => 'vehicle'],//Crane Truck Large Hiab
            '1' => ['id' => '9', 'option' => 'Small Crane', 'type' => 'vehicle'],//Crane Truck Small Crane


            '11' => ['id' => '3', 'option' => '15"', 'type' => 'attachment'],//Wood Chipper Medium 15"
            '10' => ['id' => '3', 'option' => '18"', 'type' => 'attachment'],//Wood Chipper Large 18"
            '15' => ['id' => '4', 'option' => 'Rayco L', 'type' => 'attachment'],//Stump Grinder Rayco L
            '14' => ['id' => '4', 'option' => 'Rayco Junior', 'type' => 'attachment'],//Stump Grinder Rayco Junior
            '7' => ['id' => '7', 'option' => 'Medium', 'type' => 'attachment'],//Trailer Medium
            '21' => ['id' => '7', 'option' => 'Large', 'type' => 'attachment'],//Trailer Large


            '20' => ['id' => '13', 'option' => 'BlueBird', 'type' => 'tool'],//Big Equipment BlueBird
            '12' => ['id' => '13', 'option' => 'Bobcat', 'type' => 'tool'],//Big Equipment Bobcat
            '13' => ['id' => '13', 'option' => 'Miniloader', 'type' => 'tool'],//Big Equipment Miniloader
            '17' => ['id' => '13', 'option' => 'Spider Lift', 'type' => 'tool'],//Big Equipment Spider Lift
            '18' => ['id' => '20', 'option' => 'XL Saw', 'type' => 'tool'],//Hand Tools XL Saw
            '5' => ['id' => '20', 'option' => 'Hedge Trimmer', 'type' => 'tool'],//Hand Tools Hedge Trimmer
            '19' => ['id' => '21', 'option' => 'Orchard Ladder', 'type' => 'tool'],//Other Tools Orchard Ladder
            '3' => ['id' => '21', 'option' => 'Ladder', 'type' => 'tool'],//Other Tools Ladder
        ];
        $this->db->order_by('equipment_estimate_id');
        $equipment = $this->db/*->where('equipment_estimate_id = 22171')*/ ->get('estimates_services_equipments')->result();
        $eqByEst = [];
        $oldId = 0;
        foreach ($equipment as $key => $value) {
            $eqByEst[$value->equipment_estimate_id][$value->equipment_service_id][] = $value;
        }
        foreach ($eqByEst as $estId => $estEq) {
            foreach ($estEq as $servId => $servEq) {
                $servSetups = ['vehicle' => [], 'attachment' => [], 'tool' => []];
                foreach ($servEq as $key => $value) {

                    if ($relations[$value->equipment_item_id]['type'] != 'tool') {
                        if (!empty($servSetups[$relations[$value->equipment_item_id]['type']])) {
                            $found = false;
                            foreach ($servSetups[$relations[$value->equipment_item_id]['type']] as $k => $v) {
                                if ($relations[$value->equipment_item_id]['id'] == $v['id']) {
                                    $servSetups[$relations[$value->equipment_item_id]['type']][$k]['option'] .= '|' . $relations[$value->equipment_item_id]['option'];
                                    $found = true;
                                    break;
                                }
                            }
                            if (!$found) {
                                $servSetups[$relations[$value->equipment_item_id]['type']][] = $relations[$value->equipment_item_id];
                            }

                        } else {
                            $servSetups[$relations[$value->equipment_item_id]['type']][] = $relations[$value->equipment_item_id];
                        }

                    } else
                        $servSetups[$relations[$value->equipment_item_id]['type']][] = $relations[$value->equipment_item_id];
                }

                $insert = [];
                $tools = ['id' => [], 'options' => []];
                foreach ($servSetups['tool'] as $k => $v) {
                    $tools['id'][] = $v['id'];
                    $tools['options'][$v['id']][] = $v['option'];
                }

                foreach ($servSetups['vehicle'] as $k => $v) {
                    $attachmentId = NULL;
                    $attachmentOptions = NULL;

                    if (isset($servSetups['attachment'][$k])) {
                        $attachmentId = $servSetups['attachment'][$k]['id'];
                        $attachmentOptions = json_encode(explode('|', $servSetups['attachment'][$k]['option']));
                        unset($servSetups['attachment'][$k]);
                    }

                    $insert[] = [
                        'equipment_service_id' => $value->equipment_service_id,
                        'equipment_item_id' => $v['id'],
                        'equipment_estimate_id' => $estId,
                        'equipment_attach_id' => $attachmentId,
                        'equipment_item_option' => $v['option'] ? json_encode(explode('|', $v['option'])) : NULL,
                        'equipment_attach_option' => $attachmentOptions,
                        'equipment_attach_tool' => !empty($tools['id']) ? json_encode($tools['id']) : NULL,
                        'equipment_tools_option' => !empty($tools['options']) ? json_encode($tools['options']) : NULL,
                    ];
                    $tools = ['id' => [], 'options' => []];
                }

                foreach ($servSetups['attachment'] as $k => $v) {
                    $insert[] = [
                        'equipment_service_id' => $value->equipment_service_id,
                        'equipment_item_id' => NULL,
                        'equipment_estimate_id' => $estId,
                        'equipment_attach_id' => $v['id'],
                        'equipment_item_option' => NULL,
                        'equipment_attach_option' => $v['option'] ? json_encode(explode('|', $v['option'])) : NULL,
                        'equipment_attach_tool' => !empty($tools['id']) ? json_encode($tools['id']) : NULL,
                        'equipment_tools_option' => !empty($tools['options']) ? json_encode($tools['options']) : NULL,
                    ];
                    $tools = ['id' => [], 'options' => []];
                }

                if (empty($insert)) {
                    $insert[] = [
                        'equipment_service_id' => $value->equipment_service_id,
                        'equipment_item_id' => NULL,
                        'equipment_estimate_id' => $estId,
                        'equipment_attach_id' => NULL,
                        'equipment_item_option' => NULL,
                        'equipment_attach_option' => NULL,
                        'equipment_attach_tool' => !empty($tools['id']) ? json_encode($tools['id']) : NULL,
                        'equipment_tools_option' => !empty($tools['options']) ? json_encode($tools['options']) : NULL,
                    ];
                    $tools = ['id' => [], 'options' => []];
                }

                $this->db->delete('estimates_services_equipments', ['equipment_estimate_id' => $estId, 'equipment_service_id' => $value->equipment_service_id]);
                $this->db->insert_batch('estimates_services_equipments', $insert);
            }
        }
    }

    function patch_notes_without_client()
    {
        die;
        $this->load->model('mdl_clients');
        $count = 0;
        $clientsIds = [];
        $notes = $this->mdl_clients->get_insert_notes(['client_id' => 0], ['client_note' => 'Sent to client'])->result();
        foreach ($notes as $k => $v) {
            $dir = 'uploads/notes_files/' . $v->client_note_id . '/';

            if (is_dir($dir) && $v->client_note_id) {
                $filename = scandir($dir);
                if ($filename) {
                    if (!empty($filename) && isset($filename[2])) {
                        $fileUrl = $dir . $filename[2];
                        $file = file_get_contents($fileUrl);
                        //echo '<pre>'; var_dump($file); die;
                        preg_match('|To: </strong>(.*)<br>|Uis', $file, $email);
                        if ($email && isset($email[1])) {
                            $client = $this->mdl_clients->get_client_contacts(['cc_email' => trim($email[1])]);
                            if (!empty($client) && is_countable($client) && count($client) == 1) {

                                $to = 'uploads/notes_files/' . $client[0]['cc_client_id'] . '/' . $v->client_note_id . '/';
                                $this->mdl_clients->update_note_by(['client_note_id' => $v->client_note_id], ['client_id' => $client[0]['cc_client_id']]);
                                makedir($to);
                                copy($fileUrl, $to . $filename[2]);
                                recursive_rm_files($dir);
                                $count++;
                                $clientsIds[] = $client[0]['cc_client_id'];
                            }
                        }
                    }

                }
            }
            //

        }
        echo '<pre>';
        var_export($clientsIds);
        die;
    }

    function patch_notes_sent_to_client()
    {
        die('Need start');
        $this->load->model('mdl_clients');
        $count = 0;
        $clientsIds = [];
        $notes = $this->mdl_clients->get_insert_notes(['client_id != 0' => NULL], ['client_note' => 'Sent to client'])->result();

        foreach ($notes as $k => $v) {
            $dir = 'uploads/notes_files/' . $v->client_id . '/' . $v->client_note_id . '/';

            if (is_dir($dir) && $v->client_note_id && $v->client_id) {

                $filename = scandir($dir);

                if ($filename) {
                    if (!empty($filename) && isset($filename[2])) {
                        $fileUrl = $dir . $filename[2];
                        $file = file_get_contents($fileUrl);

                        preg_match('|Subject: </strong>(.*)<br>|Uis', $file, $email);

                        if ($email && isset($email[1])) {
                            $this->mdl_clients->update_note_by(['client_note_id' => $v->client_note_id], ['client_note' => 'Sent email "' . $email[1] . '"', 'client_note_type' => 'email']);
                            $count++;
                            $clientsIds[] = $v->client_id;
                        }
                    }
                }
            }
        }
        echo '<pre>';
        var_export($clientsIds);
        die;
    }

    function patch_note_sentPdf()
    {
        $this->load->model('mdl_clients');

        $notes = $this->mdl_clients->get_insert_notes(['client_note_type !=' => 'email'], ['client_note' => 'Sent PDF of'])->result();
        //echo '<pre>'; var_dump($notes);  die;
        foreach ($notes as $k => $v) {
            $this->mdl_clients->update_note_by(['client_note_id' => $v->client_note_id], ['client_note_type' => 'email']);
        }
    }

    function paymentsChecker()
    {
        die;
        $fileData = file_get_contents('uploads/payments');
        //$fPayments = explode("\n", $fileData);
        $this->load->model('mdl_clients');
        $this->db->query("SET time_zone = '-04:00'");
        $payments = $this->db->query("SELECT estimates.client_id, estimates.estimate_no, client_payments.*, FROM_UNIXTIME(payment_date, '%Y-%m-%d %H:%i:%s') as formated_date FROM `client_payments` JOIN estimates ON client_payments.estimate_id = estimates.estimate_id WHERE payment_method = 'cc' AND payment_trans_id IS NULL AND payment_amount > 0 AND client_id != 22660 AND FROM_UNIXTIME(payment_date, '%Y-%m-%d') >= '2018-06-01'")->result();
        echo "<pre>";
        var_dump($this->db->last_query());
        die;

        $number = 0;
        $sum = 0;
        $estimatesIds = [];
        $dates = [];
        foreach ($payments as $key => $value) {
            $update = FALSE;
            $amount = number_format($value->payment_amount, 2, '.', '');
            $date = substr($value->formated_date, 0, 16);
            $client_id = $value->client_id;
            $featDate = date('Y-m-d H:i', $payments[0]->payment_date + 60);
            $pastDate = date('Y-m-d H:i', $payments[0]->payment_date - 60);
            $estimateNo = $value->estimate_no;
            $pattern = '/1000(.*?\|' . $date . '\|' . $amount . '\|' . $client_id . '.*?)\n/';
            preg_match_all($pattern, $fileData, $matches);
            //echo "<pre>"; var_dump($pattern); die;
            /*if(!isset($matches[1][0])) {
                continue;
                //echo $date . ' - ' . $amount . ' - ' . $value->estimate_id . "<br>";
            }*/

            if (isset($matches[0][0]) && !empty($matches[0][0])) {
                $update = TRUE;
            }

            $pattern2 = '/1000(.*?\|' . $date . '\|' . $amount . '\|' . $estimateNo . '.*?)\n/';
            preg_match_all($pattern2, $fileData, $matches2);
            if (isset($matches2[0][0]) && !empty($matches2[0][0])) {
                $update = TRUE;
                $matches = $matches2;
            }
            $pattern3 = '/1000(.*?\|' . $featDate . '\|' . $amount . '\|' . $client_id . '.*?)\n/';
            preg_match_all($pattern3, $fileData, $matches3);
            if (isset($matches3[0][0]) && !empty($matches3[0][0])) {
                $update = TRUE;
                $matches = $matches3;
            }
            $pattern4 = '/1000(.*?\|' . $featDate . '\|' . $amount . '\|' . $estimateNo . '.*?)\n/';
            preg_match_all($pattern4, $fileData, $matches4);
            if (isset($matches4[0][0]) && !empty($matches4[0][0])) {
                $update = TRUE;
                $matches = $matches4;
            }
            $pattern5 = '/1000(.*?\|' . $pastDate . '\|' . $amount . '\|' . $client_id . '.*?)\n/';
            preg_match_all($pattern5, $fileData, $matches5);
            if (isset($matches5[0][0]) && !empty($matches5[0][0])) {
                $update = TRUE;
                $matches = $matches5;
            }
            $pattern6 = '/1000(.*?\|' . $pastDate . '\|' . $amount . '\|' . $estimateNo . '.*?)\n/';
            preg_match_all($pattern6, $fileData, $matches6);
            if (isset($matches6[0][0]) && !empty($matches6[0][0])) {
                $update = TRUE;
                $matches = $matches6;
            }

            if ($update) {
                preg_match("~^.+?[|]~ui", $matches[0][0], $m);
                //$sum += $amount;
                $number++;
                $paymentData[] = [
                    'id' => $value->payment_id,
                    'payment_trans_id' => intval($m[0]),
                    'amount' => $amount,
                    'client' => $client_id,
                ];
            }
        }
        //echo '<pre>'; var_dump($paymentData); die;
        if (isset($paymentData)) {
            foreach ($paymentData as $key => $value) {
                $this->mdl_clients->update_payment($value['id'], ['payment_trans_id' => $value['payment_trans_id']]);
            }
        }
        echo $number;
        //ALTER TABLE `client_payments` ADD `payment_trans_id` INT(11) NULL DEFAULT NULL ;
        //var_dump($sum, $number, $estimatesIds, $dates);
    }

    function testTranasactionsReport()
    {
        $this->load->model('mdl_estimates');
        $this->load->model('mdl_invoices');
        $beanstream = new \Beanstream\Gateway(_FIRST_DATA_MERCHANT_ID, _FIRST_DATA_API_KEY, 'api', 'v1');

        $datai["transaction_approve"] = 1;
        $estimate_id = 35415;
        $estimate_data = $this->mdl_estimates->find_by_id($estimate_id);
        $icons = [
            'vi' => ['img' => 'visa.png', 'title' => 'VISA'],
            'mc' => ['img' => 'mc.png', 'title' => 'MasterCard'],
            'am' => ['img' => 'amex.png', 'title' => 'AMEX'],
            'nn' => ['img' => 'discover.png', 'title' => 'Discover'],
        ];
        $to = 'isq200820082008@gmail.com';
        $letterData['client_id'] = 34177;
        $letterData['amount'] = 58.47;
        $letterData['id'] = 10007922;
        $letterData['date'] = '9/6/2018 4:32:19 PM';
        $letterData['payment'] = 1;
        $transData = [];
        try {
            $transData = $beanstream->reporting()->getTransaction($letterData['id']);
        } catch (Exception $e) {

        }
        //echo "<pre>";var_dump($icons[strtolower($transData['card']['card_type'])]);die;
        $letterData['message'] = $transData ? $transData['message'] : 'Incorrect Billing Information';
        $letterData['card'] = $transData ? $transData['card']['last_four'] : '****';
        $letterData['auth_code'] = $transData && isset($transData['auth_code']) ? $transData['auth_code'] : NULL;
        $letterData['card_icon'] = $transData && isset($icons[strtolower($transData['card']['card_type'])]) ? $icons[strtolower($transData['card']['card_type'])] : ['img' => 'default.png', 'title' => 'CreditCard'];

        $subject = 'Credit Card payment for ';

        $wasPay = 0;
        if ($datai["transaction_approve"] == 1)
            $wasPay = $letterData['amount'];
        if (isset($invoice)) {
            $letterData['invoice'] = $invoice->invoice_no;

            $invoiceObj = $this->mdl_invoices->invoices(['invoices.estimate_id' => $estimate_id]);
            $data['total'] = (isset($invoiceObj[0])) ? $invoiceObj[0]->total_with_hst : 0;
            $data['due'] = (isset($invoiceObj[0])) ? round($invoiceObj[0]->due - $wasPay, 2) : 0;

            $subject .= 'Invoice ' . $this->session->userdata("_INVOICE_NO");
        } else {
            $estData = $this->mdl_estimates->estimate_sum_and_hst($estimate_id);
            if ($estData) {
                $letterData['total'] = $estData['total'] + $estData['hst'];
                $paymentsSum = isset($estData['payments']) ? $estData['payments'] : 0;
                $letterData['due'] = $letterData['total'] - $paymentsSum - $wasPay;
            }
            //echo '<pre>'; var_dump($estData); die;
            $letterData['estimate'] = $estimate_data->estimate_no;
            $subject .= 'Estimate ' . $estimate_data->estimate_no;
        }

        if ($datai["transaction_approve"] == 1)
            $subject .= ' is Approved';
        else
            $subject .= ' is Declined';
        //echo '<pre>'; var_dump($letterData); die;
        $text = $this->load->view('payments/payment_check', $letterData, TRUE);

        echo $text;
    }

    function patchEstimateBalance()
    {
        $this->load->model('mdl_estimates');
        $count = 0;
        $estimates = $this->mdl_estimates->find_all();
        foreach ($estimates as $k => $v) {
            $bal = $this->mdl_estimates->get_total_estimate_balance($v->estimate_id);
            if ($bal > 0) {
                $this->mdl_estimates->update_estimates(['estimate_balance' => $bal], ['estimate_id' => $v->estimate_id]);
                $count++;
            }
        }
        $data = $this->mdl_estimates->get_total_estimate_balance(20094);
        echo '<pre>';
        var_dump($data, $count);
        die;

    }

    function patch_gps_tracker()
    {

        $date = strtotime('2019-04-01');
        $start = date('H:i:s');
        $count = 1;
        for ($i = $date; $i <= strtotime(date('Y-m-t', $date)); $i = $i + 86400) {
            $pdf = Modules::run('cron/cron/equipment_gps_tracker', date('Y-m-d', $i));
        }
        $end = date('H:i:s');
        echo $start . ' - ' . $end;
        die;
        //$pdf = Modules::run('cron/cron/equipment_gps_tracker', '2018-01-03');
        //echo '<pre>'; var_dump($pdf); die;
    }

    function patch_stump_house_number()
    {
        $this->load->model('mdl_stumps');
        $rows = $this->db->query("SELECT * FROM `stumps` WHERE stump_client_id = 10")->result_array();

        foreach ($rows as $k => $v) {
            $area = substr($v['stump_address'], 0, strspn($v['stump_address'], "0123456789"));
            $address = str_replace($area, '', $v['stump_address']);
            $this->mdl_stumps->update_stumps(array('stump_house_number' => $area, 'stump_address' => $address), array('stump_id' => $v['stump_id']));
        }
        echo '<pre>';
        var_dump(substr($rows[1]['stump_address'], 0, strspn($rows[1]['stump_address'], "0123456789")));
        die;
    }

    function patch_invoices_status()
    {
        die;
        $this->load->model('mdl_invoices');
        $this->load->model('mdl_invoice_status');
        $data['invoices_statuses'] = $this->mdl_invoice_status->get_all();

        $i = 0;

        foreach ($data['invoices_statuses'] as $k => $v) {
            $i += $this->mdl_invoices->update_invoice(['in_status' => $v->invoice_status_id], ['in_status' => $v->invoice_status_name]);
        }
        echo '<pre>';
        var_dump($i);
        die;
    }

    function patch_status_log_table()
    {
        die;
        $this->load->model('mdl_invoice_status');
        $data['invoices_statuses'] = $this->mdl_invoice_status->get_all();
        $i = 0;

        foreach ($data['invoices_statuses'] as $k => $v) {
            $this->db->where(['status_type' => 'invoice', 'status_value' => $v->invoice_status_name]);
            $update = $this->db->update('status_log', ['status_value' => $v->invoice_status_id]);
        }
        echo '<pre>';
        var_dump($i);
        die;
    }

    function patch_invoices_interest()
    {
        $this->load->model('mdl_invoices');

        $interests = $this->mdl_invoices->get_invoice_interes();

        foreach ($interests as $key => $interest) {
            $this->mdl_invoices->update_all_invoice_interes($interest->estimate_id);
        }

        echo "success";
        die;
    }

    function patch_invoice_overpaid()
    {
        $this->load->model('mdl_invoices');
        $query = "SELECT inv.id, inv.invoice_no, inv.invoice_status_name, inv.in_status, SUM(inv.total) as sum_total, SUM(inv.due) as sum_due, SUM(inv.service_sum) as sum_service, SUM(inv.discount) as sum_discount, SUM(inv.hst) as sum_hst, SUM(inv.payments) as sum_payments FROM (
					SELECT invoices.id, invoices.invoice_no, invoices.in_status, invoice_statuses.invoice_status_name,
						
    					ROUND((ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2)
						- IF(discounts.discount_percents, ROUND(IF(estimate_hst_disabled = 2, ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2) / estimate_tax_rate,	ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2))	*  IF(estimate_hst_disabled <> 2, discounts.discount_amount, 0) / 100, 2), IF(estimate_hst_disabled != 2, IFNULL(discounts.discount_amount, 0), 0))
						 + ROUND(IF(invoices.interest_status = 'No' , SUM(IFNULL(invoice_interest.interes_cost, 0))/ IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1)  / IF(COUNT(DISTINCT(estimates_services.id)), COUNT(DISTINCT(estimates_services.id)), 1), 0), 2))
						* IF(estimate_hst_disabled = 0, estimate_tax_rate, 1), 2)
                         as total,
                        
                         ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2) as service_sum,
                        
                         ROUND(IF(discounts.discount_percents, ROUND(IF(estimate_hst_disabled = 2, ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2) / estimate_tax_rate,	ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2))	*  IF(estimate_hst_disabled <> 2, discounts.discount_amount, 0) / 100, 2), IF(estimate_hst_disabled != 2, IFNULL(discounts.discount_amount, 0), 0)), 2) as discount,
                         
                         ROUND((ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2)
						- IF(discounts.discount_percents, ROUND(IF(estimate_hst_disabled = 2, ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2) / estimate_tax_rate,	ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2))	*  IF(estimate_hst_disabled <> 2, discounts.discount_amount, 0) / 100, 2), IF(estimate_hst_disabled != 2, IFNULL(discounts.discount_amount, 0), 0))
						 + ROUND(IF(invoices.interest_status = 'No' , SUM(IFNULL(invoice_interest.interes_cost, 0))/ IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1)  / IF(COUNT(DISTINCT(estimates_services.id)), COUNT(DISTINCT(estimates_services.id)), 1), 0), 2))
						* IF(estimate_hst_disabled = 0, estimate_tax_rate, 1) - (ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2)
						- IF(discounts.discount_percents, ROUND(IF(estimate_hst_disabled = 2, ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2) / estimate_tax_rate,	ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2))	*  IF(estimate_hst_disabled <> 2, discounts.discount_amount, 0) / 100, 2), IF(estimate_hst_disabled != 2, IFNULL(discounts.discount_amount, 0), 0))
						 + ROUND(IF(invoices.interest_status = 'No' , SUM(IFNULL(invoice_interest.interes_cost, 0))/ IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1)  / IF(COUNT(DISTINCT(estimates_services.id)), COUNT(DISTINCT(estimates_services.id)), 1), 0), 2)), 2) as hst,
						 
						 
						 ROUND(SUM(IF(client_payments.payment_amount, client_payments.payment_amount, 0)) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) / IF(COUNT(DISTINCT(estimates_services.id)), COUNT(DISTINCT(estimates_services.id)), 1), 2) as payments,
    
						ROUND((ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2)
						- IF(discounts.discount_percents, ROUND(IF(estimate_hst_disabled = 2, ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2) / estimate_tax_rate,	ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2))	*  IF(estimate_hst_disabled <> 2, discounts.discount_amount, 0) / 100, 2), IF(estimate_hst_disabled != 2, IFNULL(discounts.discount_amount, 0), 0))
						 + ROUND(IF(invoices.interest_status = 'No' , SUM(IFNULL(invoice_interest.interes_cost, 0))/ IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1)  / IF(COUNT(DISTINCT(estimates_services.id)), COUNT(DISTINCT(estimates_services.id)), 1), 0), 2))
						* IF(estimate_hst_disabled = 0, estimate_tax_rate, 1) - (SUM(IF(client_payments.payment_amount, client_payments.payment_amount, 0)) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) / IF(COUNT(DISTINCT(estimates_services.id)), COUNT(DISTINCT(estimates_services.id)), 1)), 2)
                         as due
						FROM 	invoices  JOIN invoice_statuses ON invoices.in_status = invoice_statuses.invoice_status_id
						
						INNER JOIN 	estimates
									
						ON 			invoices.estimate_id= estimates.estimate_id
						
						LEFT JOIN invoice_interest
						
						ON 			invoices.id= invoice_interest.invoice_id
    					
    					
						
						LEFT JOIN 	estimates_services
						
						ON			estimates_services.estimate_id=invoices.estimate_id 
						
						LEFT JOIN 	discounts
						
						ON			discounts.estimate_id=invoices.estimate_id
						
						LEFT JOIN client_payments
						
						ON			client_payments.estimate_id=invoices.estimate_id WHERE invoice_statuses.completed = 1 GROUP BY invoices.id ) as inv	GROUP BY inv.id ";
        $overpaid = $this->db->query($query . ' HAVING sum_due < 0')->result_array();
        foreach ($overpaid as $k => $v)
            $this->mdl_invoices->update_invoice(['overpaid' => 1], ['id' => $v['id']]);
        $nopaid = $this->db->query($query . ' HAVING sum_due > 0')->result_array();
        foreach ($nopaid as $k => $v) {
            $status = $this->mdl_invoices->get_last_invoice_status($v['id'], TRUE);
            if ($status && !empty($status) && isset($status['status_value']) && $status['status_value'])
                $this->mdl_invoices->update_invoice(['overpaid' => NULL, 'in_status' => $status['status_value']], ['id' => $v['id']]);
        }

    }

    function test()
    {
        echo date(getTimeFormat(true), strtotime('18:15:00')) . '<br>' . getDateTimeWithDate('2020-09-15', 'Y-m-d');
        /*$this->load->model('mdl_invoices');
        $this->mdl_invoices->get_invoice(['invoice_no' => '46554-I']);
        echo $this->db->last_query();*/
        die;
        $subject = 'Test Message';
        $this->load->model('mdl_clients');
        $this->load->library('email');
        $clients = $this->db->query("SELECT cc_email, client_id FROM clients JOIN clients_contacts ON cc_client_id = clients.client_id AND cc_print = 1 WHERE client_id = 22660 OR client_id = 22661/*client_unsubscribe = 0 AND cc_email IS NOT NULL AND cc_email <> '' AND cc_email REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,63}$' GROUP BY cc_email*/")->result();
        foreach ($clients as $client) {
            $body = '<!DOCTYPE html><html><head></head><body><div style="word-spacing:1px;border-color:rgb(49,49,49);color:rgb(49,49,49)" dir="auto"><table width="100%" cellpadding="0" border="0" cellspacing="0" bgcolor="#ffffff" id="m_-7744115767154907768m_3920056035944105378m_-3349327101640134282gmail-ko_imageBlock_6" style="font-family:&quot;Times New Roman&quot;;font-size:medium;min-width:0px;zoom:1;border-color:rgb(128,128,128);color:rgb(145,145,145)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td align="center" valign="top" style="padding-left:9px;padding-right:9px;font-size:0px;min-width:0px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><div style="margin:0px auto;max-width:570px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff" width="570" style="width:570px;border-spacing:0px;max-width:570px;zoom:1;font-family:&quot;Times New Roman&quot;;border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td width="100%" valign="top" align="center" style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><img border="0" hspace="0" align="center" vspace="0" width="570" src="https://ci6.googleusercontent.com/proxy/1qn56h_CHKnmSJJrbA93YWQsOpDk3dElQx4drNd8i6B6wzdfQYUj8udklD31NsQivTa-1_tx2rzDry6cdZm2xONlaZNkqJDy3TSKx3dQmeSkWewmmxChWP_bpXndjSO6QATADoXCvPcorxtHQuvJ_Df4cDNCVJwyYySCfyOe19ncPBOZYwWLK7HELAAdKql3Qm-QUZ1i-4rp2ArlR0VjUF4=s0-d-e1-ft#https://mosaico.io/srv/f-pdo2fw3/img?src=https%3A%2F%2Fmosaico.io%2Ffiles%2Fpdo2fw3%2F777%2520%25282%2529.png&amp;method=resize&amp;params=570%2Cnull" style="border:0px rgb(63,63,63);display:block;outline:none;vertical-align:top;margin:0px auto;font-size:15px;font-family:Arial,Helvetica,sans-serif;width:570px;max-width:570px;height:auto;background-color:rgba(0,0,0,0);color:rgb(63,63,63)" class="CToWUd a6T" tabindex="0"><div class="a6S" dir="ltr" style="opacity: 0.01; left: 1081px; top: 222px;"><div id=":1hv" class="T-I J-J5-Ji aQv T-I-ax7 L3 a5q" role="button" tabindex="0" aria-label="Скачать файл " data-tooltip-class="a1V" data-tooltip="Скачать"><div class="aSK J-J5-Ji aYr"></div></div></div></td></tr></tbody></table></div></td></tr></tbody></table><table width="100%" cellpadding="0" border="0" cellspacing="0" bgcolor="#ffffff" id="m_-7744115767154907768m_3920056035944105378m_-3349327101640134282gmail-ko_sideArticleBlock_9" style="font-family:&quot;Times New Roman&quot;;font-size:medium;min-width:0px;zoom:1;border-color:rgb(128,128,128);color:rgb(145,145,145)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td align="center" valign="top" style="padding-left:9px;padding-right:9px;font-size:0px;min-width:0px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><div style="margin:0px auto;max-width:570px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table border="0" cellpadding="0" cellspacing="9" bgcolor="#ffffff" width="570" style="width:570px;border-spacing:9px;max-width:570px;zoom:1;font-family:&quot;Times New Roman&quot;;border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td align="center" valign="top" style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><div style="width:552px;max-width:552px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><div style="display:inline-block;vertical-align:top;min-width:50%;max-width:100%;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table border="0" cellspacing="9" cellpadding="0" width="276" align="left" style="width:276px;border-spacing:9px;zoom:1;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td width="100%" valign="top" align="center" style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><img border="0" hspace="0" align="center" vspace="0" width="258" src="https://ci3.googleusercontent.com/proxy/8x476Pgfv2rVgNp-7o15DqIspDw-ly0Y8LqGe8bzuzJGJkqkVxvUZeXfiDw4WHmyQxwSi0CTYt-kcSDjxbV6p33mthXwiwlhfq7gOoWdv57vYMhaC7Jtg9laOnGjp3hzfvbdZdK5LSJOmuRxaCfOSnG81hhe9ZhNANopSRe34zfmuQn_KrYmrs2z-Leqtgtf9FEsOQyPthOM6xeRKDZZRw=s0-d-e1-ft#https://mosaico.io/srv/f-pdo2fw3/img?src=https%3A%2F%2Fmosaico.io%2Ffiles%2Fpdo2fw3%2F78%2520%25281%2529.png&amp;method=resize&amp;params=258%2Cnull" style="border:0px rgb(63,63,63);display:block;outline:none;vertical-align:top;margin:0px auto;font-size:15px;font-family:Arial,Helvetica,sans-serif;width:258px;max-width:258px;height:auto;background-color:rgba(0,0,0,0);color:rgb(63,63,63)" class="CToWUd"></td></tr></tbody></table></div><div style="display:inline-block;vertical-align:top;min-width:50%;max-width:100%;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table border="0" cellspacing="9" cellpadding="0" width="276" align="left" style="width:276px;border-spacing:9px;zoom:1;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td width="100%" valign="top" align="left" style="font-size:28px;font-family:&quot;Arial Black&quot;,&quot;Arial Black&quot;,Gadget,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(103,153,92);color:rgb(103,153,92)"><strong style="font-size:1.75rem;font-family:&quot;Arial Black&quot;,&quot;Arial Black&quot;,Gadget,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(103,153,92)">PRUNING</strong></td></tr><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td width="100%" valign="top" align="left" style="font-size:15px;font-family:Arial,Helvetica,sans-serif;line-height:normal;background-color:rgba(0,0,0,0);border-color:rgb(63,63,63);color:rgb(63,63,63)"><p style="margin:0px;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(63,63,63)"><strong style="font-size:0.9375rem;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(63,63,63)">It is essential to prune dead, dying, or dangerous limbs in order to avoid insect and fungal problems as they will spread into live and healthy wood and also to protect your property and passerby from any damage. Pruning allows an arborist to closely inspect the tree for safety in a way that just can\'t be done from the ground.</strong></p></td></tr><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td valign="top" align="left" style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table cellpadding="6" border="0" align="left" cellspacing="0" style="border-spacing:0px;padding-top:4px;zoom:1;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td width="auto" valign="middle" align="left" bgcolor="#67995c" style="text-align:center;padding:6px 18px;font-size:18px;font-family:Arial,Helvetica,sans-serif;border-top-left-radius:18px;border-top-right-radius:18px;border-bottom-right-radius:18px;border-bottom-left-radius:18px;background-color:rgb(103,153,92);border-color:rgb(238,236,225);color:rgb(238,236,225)"><a href="https://treedoctors.ca/tree-trimming/" style="text-decoration-line:none;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(238,236,225);color:rgb(238,236,225)" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://treedoctors.ca/tree-trimming/&amp;source=gmail&amp;ust=1589560272818000&amp;usg=AFQjCNFg59XGzd7Cd_hj-9MDCx1cAnsg3g"><strong style="font-size:1.125rem;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(238,236,225)">LEARN MORE</strong></a></td></tr></tbody></table></td></tr></tbody></table></div></div></td></tr></tbody></table></div></td></tr></tbody></table><table width="100%" cellpadding="0" border="0" cellspacing="0" bgcolor="#ffffff" id="m_-7744115767154907768m_3920056035944105378m_-3349327101640134282gmail-ko_sideArticleBlock_8" style="font-family:&quot;Times New Roman&quot;;font-size:medium;min-width:0px;zoom:1;border-color:rgb(128,128,128);color:rgb(145,145,145)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td align="center" valign="top" style="padding-left:9px;padding-right:9px;font-size:0px;min-width:0px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><div style="margin:0px auto;max-width:570px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table border="0" cellpadding="0" cellspacing="9" bgcolor="#ffffff" width="570" style="width:570px;border-spacing:9px;max-width:570px;zoom:1;font-family:&quot;Times New Roman&quot;;border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td align="center" valign="top" style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><div style="width:552px;max-width:552px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><div style="display:inline-block;vertical-align:top;min-width:50%;max-width:100%;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table border="0" cellspacing="9" cellpadding="0" width="276" align="left" style="width:276px;border-spacing:9px;zoom:1;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td width="100%" valign="top" align="left" style="font-size:1.75rem;font-family:&quot;Arial Black&quot;,&quot;Arial Black&quot;,Gadget,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(103,153,92);color:rgb(103,153,92)">REMOVAL</td></tr><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td width="100%" valign="top" align="left" style="font-size:15px;font-family:Arial,Helvetica,sans-serif;line-height:normal;background-color:rgba(0,0,0,0);border-color:rgb(63,63,63);color:rgb(63,63,63)"><p style="margin:0px 0px 1em;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(63,63,63)"><strong style="font-size:0.9375rem;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(63,63,63)">Hazardous trees posing&nbsp;</strong><strong style="font-size:0.9375rem;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(63,63,63)">structural defects that could potentially cause injury to people or damage property need immediate attention. Removing the tree during this season is recommended because trees are lighter during their dormant season.</strong><br><strong style="font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(63,63,63)"></strong></p><p style="margin:1em 0px 0px;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(63,63,63)"><strong style="font-size:0.9375rem;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(63,63,63)">Our experienced team is ready and willing to discuss the options that are best suited for your yard. If a tree cannot be taken down safely with a crew member in the tree, we have the equipment and technology necessary to take it down mechanically.</strong></p></td></tr><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td valign="top" align="left" style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table cellpadding="6" border="0" align="left" cellspacing="0" style="border-spacing:0px;padding-top:4px;zoom:1;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td width="auto" valign="middle" align="left" bgcolor="#67995c" style="text-align:center;padding:6px 18px;font-size:18px;font-family:Arial,Helvetica,sans-serif;border-top-left-radius:18px;border-top-right-radius:18px;border-bottom-right-radius:18px;border-bottom-left-radius:18px;background-color:rgb(103,153,92);border-color:rgb(238,236,225);color:rgb(238,236,225)"><a href="https://treedoctors.ca/tree-removal/" style="text-decoration-line:none;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(238,236,225);color:rgb(238,236,225)" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://treedoctors.ca/tree-removal/&amp;source=gmail&amp;ust=1589560272818000&amp;usg=AFQjCNHx3TY6qJIT4NZXIZoXBVOY-pfNjA"><strong style="font-size:1.125rem;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(238,236,225)">LEARN MORE</strong></a></td></tr></tbody></table></td></tr></tbody></table></div><div style="display:inline-block;vertical-align:top;min-width:50%;max-width:100%;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table border="0" cellspacing="9" cellpadding="0" width="276" align="left" style="width:276px;border-spacing:9px;zoom:1;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td width="100%" valign="top" align="center" style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><img border="0" hspace="0" align="center" vspace="0" width="258" src="https://ci4.googleusercontent.com/proxy/KVv0rmA5b0YeazV_7F9w7cx-li5q-R5J0091nHkh-UTrrhAeWQGWXKNq5rXeu6E7hU3penK7Rjgz_1R7quac3GO5YhJ557yo0lYOdzDAP20cfqhX2dyxokMbTMsOhQZ_8Q8UH_NrYIXYRnDP5QhzHvUj8dkEQHqEtgw8uk6wa14HbvLAEeQR_vXWhMwd-HErnh0xAzVfFtJIvBQLAk2hqg=s0-d-e1-ft#https://mosaico.io/srv/f-pdo2fw3/img?src=https%3A%2F%2Fmosaico.io%2Ffiles%2Fpdo2fw3%2F79%2520%25281%2529.png&amp;method=resize&amp;params=258%2Cnull" style="border:0px rgb(63,63,63);display:block;outline:none;vertical-align:top;margin:0px auto;font-size:15px;font-family:Arial,Helvetica,sans-serif;width:258px;max-width:258px;height:auto;background-color:rgba(0,0,0,0);color:rgb(63,63,63)" class="CToWUd"></td></tr></tbody></table></div></div></td></tr></tbody></table></div></td></tr></tbody></table><table width="100%" cellpadding="0" border="0" cellspacing="0" bgcolor="#ffffff" id="m_-7744115767154907768m_3920056035944105378m_-3349327101640134282gmail-ko_sideArticleBlock_7" style="font-family:&quot;Times New Roman&quot;;font-size:medium;min-width:0px;zoom:1;border-color:rgb(128,128,128);color:rgb(145,145,145)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td align="center" valign="top" style="padding-left:9px;padding-right:9px;font-size:0px;min-width:0px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><div style="margin:0px auto;max-width:570px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table border="0" cellpadding="0" cellspacing="9" bgcolor="#ffffff" width="570" style="width:570px;border-spacing:9px;max-width:570px;zoom:1;font-family:&quot;Times New Roman&quot;;border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td align="center" valign="top" style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><div style="width:552px;max-width:552px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><div style="display:inline-block;vertical-align:top;min-width:50%;max-width:100%;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table border="0" cellspacing="9" cellpadding="0" width="276" align="left" style="width:276px;border-spacing:9px;zoom:1;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td width="100%" valign="top" align="center" style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><img border="0" hspace="0" align="center" vspace="0" width="258" src="https://ci4.googleusercontent.com/proxy/r9lFKHu86GeQGc3er1hsIOgLuwa9k68uZw7HxKdAdxAH7ify91iZ4u6yOqlLfWJBfbsaOVy_xXo24yp4LS93ODvB10laVuppQCxjlGBaaWxUgyVcPdPM6tKAsi94hiJDKijpSBJyN9R3O9nZiK_z0qCZ6FIiY3xAP_iPnOTNQ530dDy7tZQ8ZC-ZBa260is=s0-d-e1-ft#https://mosaico.io/srv/f-pdo2fw3/img?src=https%3A%2F%2Fmosaico.io%2Ffiles%2Fpdo2fw3%2F80.png&amp;method=resize&amp;params=258%2Cnull" style="border:0px rgb(63,63,63);display:block;outline:none;vertical-align:top;margin:0px auto;font-size:15px;font-family:Arial,Helvetica,sans-serif;width:258px;max-width:258px;height:auto;background-color:rgba(0,0,0,0);color:rgb(63,63,63)" class="CToWUd"></td></tr></tbody></table></div><div style="display:inline-block;vertical-align:top;min-width:50%;max-width:100%;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table border="0" cellspacing="9" cellpadding="0" width="276" align="left" style="width:276px;border-spacing:9px;zoom:1;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td width="100%" valign="top" align="left" style="font-size:28px;font-family:&quot;Arial Black&quot;,&quot;Arial Black&quot;,Gadget,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(103,153,92);color:rgb(103,153,92)"><strong style="font-size:1.75rem;font-family:&quot;Arial Black&quot;,&quot;Arial Black&quot;,Gadget,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(103,153,92)">FERTILIZING</strong><br></td></tr><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td width="100%" valign="top" align="left" style="font-size:15px;font-family:Arial,Helvetica,sans-serif;line-height:normal;background-color:rgba(0,0,0,0);border-color:rgb(63,63,63);color:rgb(63,63,63)"><p style="margin:0px;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(63,63,63)"><strong style="font-size:0.9375rem;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(63,63,63)">A tree will show signs that there is an issue with a missing nutrient in the soil. Trees with leaves that are a few shades more yellow than they should be can be suffering from one of several different nutrient deficiencies. A certified arborist will know how to spot a tree with this issue and how to narrow down the problem based on the species of tree and local soil conditions.</strong></p></td></tr><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td valign="top" align="left" style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table cellpadding="6" border="0" align="left" cellspacing="0" style="border-spacing:0px;padding-top:4px;zoom:1;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td width="auto" valign="middle" align="left" bgcolor="#67995c" style="text-align:center;padding:6px 18px;font-size:18px;font-family:Arial,Helvetica,sans-serif;border-top-left-radius:18px;border-top-right-radius:18px;border-bottom-right-radius:18px;border-bottom-left-radius:18px;background-color:rgb(103,153,92);border-color:rgb(238,236,225);color:rgb(238,236,225)"><a href="https://treedoctors.ca/tree-fertilization/" style="text-decoration-line:none;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(238,236,225);color:rgb(238,236,225)" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://treedoctors.ca/tree-fertilization/&amp;source=gmail&amp;ust=1589560272819000&amp;usg=AFQjCNHZZB2JIrrvVXasM7k0ciqtYmD6pg"><strong style="font-size:1.125rem;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(238,236,225)">LEARN MORE</strong></a></td></tr></tbody></table></td></tr></tbody></table></div></div></td></tr></tbody></table></div></td></tr></tbody></table><table width="100%" cellpadding="0" border="0" cellspacing="0" bgcolor="#ffffff" id="m_-7744115767154907768m_3920056035944105378m_-3349327101640134282gmail-ko_imageBlock_14" style="font-family:&quot;Times New Roman&quot;;font-size:medium;min-width:0px;zoom:1;border-color:rgb(128,128,128);color:rgb(145,145,145)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td align="center" valign="top" style="padding-left:9px;padding-right:9px;font-size:0px;min-width:0px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><div style="margin:0px auto;max-width:570px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table border="0" cellpadding="0" cellspacing="18" bgcolor="#ffffff" width="570" style="width:570px;border-spacing:18px;max-width:570px;zoom:1;font-family:&quot;Times New Roman&quot;;border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td width="100%" valign="top" align="center" style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><img border="0" hspace="0" align="center" vspace="0" width="534" src="https://ci5.googleusercontent.com/proxy/4AUaujWMdcyylkFJifVpqygA7dCNcLKCZwUNZbY-SkKkSfujRkykcVCUyNAS0nbk7qhAoNzx8YgZjW92WS3pflMyUqxr1z7acluZb7czR9kVGluY8ZfmEh9LeO2cLGjVMTjMAzPUiXMyVF8FYPM2VriDW4RjWuNYBEryUQp6J6p6YTt3pYFiegCBv4Q2N4WwgtGsdA=s0-d-e1-ft#https://mosaico.io/srv/f-pdo2fw3/img?src=https%3A%2F%2Fmosaico.io%2Ffiles%2Fpdo2fw3%2Ffooter.png&amp;method=resize&amp;params=534%2Cnull" style="border:0px rgb(63,63,63);display:block;outline:none;vertical-align:top;margin:0px auto;font-size:15px;font-family:Arial,Helvetica,sans-serif;width:534px;max-width:534px;height:auto;background-color:rgba(0,0,0,0);color:rgb(63,63,63)" class="CToWUd a6T" tabindex="0"><div class="a6S" dir="ltr" style="opacity: 0.01; left: 1063px; top: 1505px;"><div id=":1hw" class="T-I J-J5-Ji aQv T-I-ax7 L3 a5q" role="button" tabindex="0" aria-label="Скачать файл " data-tooltip-class="a1V" data-tooltip="Скачать"><div class="aSK J-J5-Ji aYr"></div></div></div></td></tr></tbody></table></div></td></tr></tbody></table><table width="100%" cellpadding="0" border="0" cellspacing="0" bgcolor="#ffffff" id="m_-7744115767154907768m_3920056035944105378m_-3349327101640134282gmail-ko_hrBlock_5" style="font-family:&quot;Times New Roman&quot;;font-size:medium;min-width:0px;zoom:1;border-color:rgb(128,128,128);color:rgb(145,145,145)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td align="center" valign="top" style="padding-left:9px;padding-right:9px;font-size:0px;min-width:0px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><div style="margin:0px auto;max-width:570px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff" width="570" style="width:570px;border-spacing:0px;max-width:570px;zoom:1;font-family:&quot;Times New Roman&quot;;border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td align="center" valign="top" style="padding:0px 9px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><div style="vertical-align:top;width:552px;max-width:552px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table border="0" cellspacing="9" cellpadding="0" width="552" style="width:552px;border-spacing:9px;zoom:1;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td valign="top" align="center" style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table width="100%" cellspacing="0" cellpadding="0" border="0" style="border-spacing:0px;width:534px;zoom:1;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td width="100%" height="1" style="padding:0px;font-size:1px;width:534px;line-height:normal;max-height:1px;overflow:hidden;font-family:&quot;Times New Roman&quot;;background-color:rgb(63,63,63);border-color:rgb(145,145,145)">&nbsp;</td></tr></tbody></table></td></tr></tbody></table></div></td></tr></tbody></table></div></td></tr></tbody></table><table width="100%" cellpadding="0" border="0" cellspacing="0" bgcolor="#ffffff" id="m_-7744115767154907768m_3920056035944105378m_-3349327101640134282gmail-ko_sideArticleBlock_2" style="font-family:&quot;Times New Roman&quot;;font-size:medium;min-width:0px;zoom:1;border-color:rgb(128,128,128);color:rgb(145,145,145)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td align="center" valign="top" style="padding-left:9px;padding-right:9px;font-size:0px;min-width:0px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><div style="margin:0px auto;max-width:570px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table border="0" cellpadding="0" cellspacing="9" bgcolor="#ffffff" width="570" style="width:570px;border-spacing:9px;max-width:570px;zoom:1;font-family:&quot;Times New Roman&quot;;border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td align="center" valign="top" style="height:auto;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><div style="width:552px;max-width:552px;height:auto;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><div style="display:inline-block;vertical-align:top;min-width:50%;max-width:100%;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table border="0" cellspacing="9" cellpadding="0" width="276" align="left" style="width:276px;border-spacing:9px;zoom:1;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td width="100%" valign="top" align="center" style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><img border="0" hspace="0" align="center" vspace="0" width="258" src="https://ci4.googleusercontent.com/proxy/A7SHMOg3oCQBKTQrZkYoNsQq7UoNNRP2aXmp7lhNQb-eRIJ2ipYRVRrmUcO7SgaVoLfkQvP63W_osZIXxe3YZEGL27Ye689atP6CQNAjVZ_K3-DFywtTwCNJ5he-2UjE2x3wafUGzxTXAPjwN4CzpB9WEmiI-JitfDEHafEYhG53S9CSh_1da-AeZ6PpQFrnxQ=s0-d-e1-ft#https://mosaico.io/srv/f-pdo2fw3/img?src=https%3A%2F%2Fmosaico.io%2Ffiles%2Fpdo2fw3%2Flogo.png&amp;method=resize&amp;params=258%2Cnull" style="border:0px rgb(63,63,63);display:block;outline:none;vertical-align:top;margin:0px auto;font-size:15px;font-family:Arial,Helvetica,sans-serif;width:258px;max-width:258px;height:auto;background-color:rgba(0,0,0,0);color:rgb(63,63,63)" class="CToWUd"></td></tr></tbody></table></div><div style="display:inline-block;vertical-align:top;min-width:50%;max-width:100%;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table border="0" cellspacing="9" cellpadding="0" width="276" align="left" style="width:276px;border-spacing:9px;zoom:1;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td width="100%" valign="top" align="left" style="font-size:1.75rem;font-family:&quot;Arial Black&quot;,&quot;Arial Black&quot;,Gadget,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(103,153,92);color:rgb(103,153,92)">CALL US NOW!</td></tr><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td width="100%" valign="top" align="left" style="font-size:15px;font-family:Arial,Helvetica,sans-serif;line-height:normal;background-color:rgba(0,0,0,0);border-color:rgb(63,63,63);color:rgb(63,63,63)"><h3 style="font-size:1.0968749523162842rem;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(63,63,63)">Phone:&nbsp;<span style="font-size:1.0968749523162842rem;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(103,153,92);color:rgb(103,153,92)">(416) 201-8000</span><br>Email:&nbsp;<span style="font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(103,153,92);color:rgb(103,153,92)"><a href="mailto:info@treedoctors.ca" style="font-size:1.0968749523162842rem;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(66,133,244)" target="_blank">info@treedoctors.ca</a></span><br>Website:&nbsp;<span style="font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(103,153,92);color:rgb(103,153,92)"><a href="http://www.treedoctors.ca/" style="font-size:1.0968749523162842rem;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(103,153,92);color:rgb(103,153,92)" target="_blank" data-saferedirecturl="https://www.google.com/url?q=http://www.treedoctors.ca/&amp;source=gmail&amp;ust=1589560272819000&amp;usg=AFQjCNHCyAT3qTEyOu7ea8sYJ8r2pVCAUw">treedoctors.ca</a></span></h3><h3 style="font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(63,63,63)"><span style="font-size:1.0968749523162842rem;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(103,153,92);color:rgb(103,153,92)">or fill-up our online form</span>&nbsp;<a href="https://treedoctors.ca/contact-us" style="font-size:1.0968749523162842rem;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(63,63,63);color:rgb(63,63,63)" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://treedoctors.ca/contact-us&amp;source=gmail&amp;ust=1589560272819000&amp;usg=AFQjCNF7o2Aq4khW--1nvTigUZAMotNT5A">here</a></h3></td></tr></tbody></table></div></div></td></tr></tbody></table></div></td></tr></tbody></table><table width="100%" cellpadding="0" border="0" cellspacing="0" bgcolor="#ffffff" id="m_-7744115767154907768m_3920056035944105378m_-3349327101640134282gmail-ko_textBlock_22" style="font-family:&quot;Times New Roman&quot;;font-size:medium;min-width:0px;zoom:1;border-color:rgb(128,128,128);color:rgb(145,145,145)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td align="center" valign="top" style="padding-left:9px;padding-right:9px;font-size:0px;min-width:0px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><div style="margin:0px auto;max-width:570px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table border="0" cellpadding="0" cellspacing="18" bgcolor="#ffffff" width="570" style="width:570px;border-spacing:18px;max-width:570px;zoom:1;font-family:&quot;Times New Roman&quot;;border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td width="100%" valign="top" align="left" style="font-size:15px;font-family:Arial,Helvetica,sans-serif;line-height:normal;background-color:rgba(0,0,0,0);border-color:rgb(63,63,63);color:rgb(63,63,63)"><p style="margin:0px;text-align:center;font-size:0.9375rem;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(63,63,63)">Tree Doctors Inc.<br><a href="https://www.google.com/maps/search/425+Kipling+Ave+Etobicoke,+Ontario,+M8Z+5C8,+Canada?entry=gmail&amp;source=g" style="font-size:0.9375rem;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(66,133,244)" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://www.google.com/maps/search/425%2BKipling%2BAve%2BEtobicoke,%2BOntario,%2BM8Z%2B5C8,%2BCanada?entry%3Dgmail%26source%3Dg&amp;source=gmail&amp;ust=1589560272819000&amp;usg=AFQjCNFzLJVMAKBgZMIxkOZQgRk2bZczHQ">425 Kipling Ave</a><br><a href="https://www.google.com/maps/search/425+Kipling+Ave+Etobicoke,+Ontario,+M8Z+5C8,+Canada?entry=gmail&amp;source=g" style="font-size:0.9375rem;font-family:Arial,Helvetica,sans-serif;background-color:rgba(0,0,0,0);border-color:rgb(66,133,244)" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://www.google.com/maps/search/425%2BKipling%2BAve%2BEtobicoke,%2BOntario,%2BM8Z%2B5C8,%2BCanada?entry%3Dgmail%26source%3Dg&amp;source=gmail&amp;ust=1589560272819000&amp;usg=AFQjCNFzLJVMAKBgZMIxkOZQgRk2bZczHQ">Etobicoke, Ontario, M8Z 5C8, Canada</a></p></td></tr></tbody></table></div></td></tr></tbody></table><table width="100%" cellpadding="0" border="0" cellspacing="0" bgcolor="#ffffff" id="m_-7744115767154907768m_3920056035944105378m_-3349327101640134282gmail-ko_bigSocialBlock_21" style="font-family:&quot;Times New Roman&quot;;font-size:medium;min-width:0px;zoom:1;border-color:rgb(128,128,128);color:rgb(145,145,145)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td align="center" valign="top" style="padding-left:9px;padding-right:9px;font-size:0px;min-width:0px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><div style="margin:0px auto;max-width:570px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)"><table border="0" cellpadding="0" cellspacing="18" bgcolor="#ffffff" width="570" style="font-size:6px;width:570px;border-spacing:18px;max-width:570px;zoom:1;font-family:&quot;Times New Roman&quot;;border-color:rgb(128,128,128)"><tbody style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(128,128,128)"><td width="100%" valign="top" align="center" style="text-align:center;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(145,145,145)">&nbsp;<a href="https://www.facebook.com/TreeDoctorsInc/" style="display:inline-block;border-top-left-radius:50px;border-top-right-radius:50px;border-bottom-right-radius:50px;border-bottom-left-radius:50px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(66,133,244)" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://www.facebook.com/TreeDoctorsInc/&amp;source=gmail&amp;ust=1589560272819000&amp;usg=AFQjCNGSuhwpE0S1_4pLUAeKaB3v3l55Qw"><img border="0" src="https://ci4.googleusercontent.com/proxy/OnPc4qIwK2d-cG8IAGoGS6v7BAiQGtsriOuK7nzLk8KGO01p_PjhGMuG4RCtGK0HkF8vUiHv8SsDMJeyrtPcE1i2EE2ElswLjx_I1vyhpkiLt-ZF=s0-d-e1-ft#https://mosaico.io/templates/versafix-1/img/icons/fb-rdcol-96.png" width="48" height="48" alt="Facebook" style="border:0px rgb(66,133,244);display:inline-block;outline:none;text-decoration-line:none;vertical-align:top;padding-bottom:0px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0)" class="CToWUd"></a>&nbsp;&nbsp;<a href="https://twitter.com/treedoctorsca" style="display:inline-block;border-top-left-radius:50px;border-top-right-radius:50px;border-bottom-right-radius:50px;border-bottom-left-radius:50px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(66,133,244)" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://twitter.com/treedoctorsca&amp;source=gmail&amp;ust=1589560272819000&amp;usg=AFQjCNHXQw9Csl465QdYGilvw4NoP8E4Rw"><img border="0" src="https://ci5.googleusercontent.com/proxy/TKwcZwn9ebDRZ_DHVLkLAIR8R0DcBzBsDos2GH_kWwooCY8YfxJhKwJKRdaXHAK4_dlP-3SYfCXUxKB6A2BNB-aHtv0fu9XsBsdUqAjvqbNVyzHQ=s0-d-e1-ft#https://mosaico.io/templates/versafix-1/img/icons/tw-rdcol-96.png" width="48" height="48" alt="Twitter" style="border:0px rgb(66,133,244);display:inline-block;outline:none;text-decoration-line:none;vertical-align:top;padding-bottom:0px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0)" class="CToWUd"></a>&nbsp;&nbsp;<a href="https://treedoctors.ca/" style="display:inline-block;border-top-left-radius:50px;border-top-right-radius:50px;border-bottom-right-radius:50px;border-bottom-left-radius:50px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(66,133,244)" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://treedoctors.ca/&amp;source=gmail&amp;ust=1589560272819000&amp;usg=AFQjCNFRXZtL0AxlSH61gUvcNvDdVxT1bg"><img border="0" src="https://ci4.googleusercontent.com/proxy/gQqrY4_Em7Oy65-Cd_zSrbjmmMPadOCG8xESHH5DEaRcq1LXtoQChKrOTQ6d0WCCqOu_0eIszMddDOj2CLNHU4iGZ8jgyxp8H29PwZ02DlRnyNkcLg=s0-d-e1-ft#https://mosaico.io/templates/versafix-1/img/icons/web-rdcol-96.png" width="48" height="48" alt="Web" style="border:0px rgb(66,133,244);display:inline-block;outline:none;text-decoration-line:none;vertical-align:top;padding-bottom:0px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0)" class="CToWUd"></a>&nbsp;&nbsp;<a href="https://www.instagram.com/tree_doctors/" style="display:inline-block;border-top-left-radius:50px;border-top-right-radius:50px;border-bottom-right-radius:50px;border-bottom-left-radius:50px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(66,133,244)" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://www.instagram.com/tree_doctors/&amp;source=gmail&amp;ust=1589560272819000&amp;usg=AFQjCNFNzc-g96RNwZYpQrcQAjQZFCl9fg"><img border="0" src="https://ci6.googleusercontent.com/proxy/Pocm_OQcHT4EFdw2MY39kensCQ38-gCuoL86_k6BmGquNH8-AeUi_hiPEH10Y_3JvndyqZnCY586REYie4YQu4-iDIY1xWOlSgO4rh_UxYbjVXbbb6Q=s0-d-e1-ft#https://mosaico.io/templates/versafix-1/img/icons/inst-rdcol-96.png" width="48" height="48" alt="Instagram" style="border:0px rgb(66,133,244);display:inline-block;outline:none;text-decoration-line:none;vertical-align:top;padding-bottom:0px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0)" class="CToWUd"></a>&nbsp;&nbsp;<a href="https://www.youtube.com/channel/UCnVj_AbPQ5GTtuGZ5iME2Yw" style="display:inline-block;border-top-left-radius:50px;border-top-right-radius:50px;border-bottom-right-radius:50px;border-bottom-left-radius:50px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0);border-color:rgb(66,133,244)" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://www.youtube.com/channel/UCnVj_AbPQ5GTtuGZ5iME2Yw&amp;source=gmail&amp;ust=1589560272819000&amp;usg=AFQjCNHd_B9hfEeKk_CN996apW1GEvxsCg"><img border="0" src="https://ci3.googleusercontent.com/proxy/qCxZop1BE6ryLWNemM6Id3pJe8C_tpodVgILbUQCbfn2JpOh61EalSzfiWqH_D1LWyEeGjBRepEDA_mO4LSjLqjBqFaL1EfJzlYJdMJvE2yCcZQPiA=s0-d-e1-ft#https://mosaico.io/templates/versafix-1/img/icons/you-rdcol-96.png" width="48" height="48" alt="Youtube" style="border:0px rgb(66,133,244);display:inline-block;outline:none;text-decoration-line:none;vertical-align:top;padding-bottom:0px;font-family:&quot;Times New Roman&quot;;background-color:rgba(0,0,0,0)" class="CToWUd"></a></td></tr></tbody></table></div></td></tr></tbody></table><div style="border-color:rgb(49,49,49)"><div dir="ltr" data-smartmail="gmail_signature" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><div dir="ltr" style="border-color:rgb(49,49,49)"><table width="100%" style="font-size:12.8px;font-family:&quot;Times New Roman&quot;;zoom:1;border-color:rgb(128,128,128);color:rgb(71,74,93)"><tbody style="font-family:&quot;Times New Roman&quot;;border-color:rgb(128,128,128)"><tr style="font-family:&quot;Times New Roman&quot;;border-color:rgb(128,128,128)"><td style="padding:0px;font-family:&quot;Times New Roman&quot;;border-color:rgb(71,74,93)"><p style="font-size:12px;font-family:&quot;Times New Roman&quot;;border-color:rgb(71,74,93)"><br></p></td></tr></tbody></table></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div>';
            $body .= '<br><div style="text-align:center; font-size: 10px; color: rgb(71, 74, 93);"> If you no longer wish to receive these emails you may ' .
                '<a style="color: rgb(71, 74, 93);" href="http://unsubscribe.treedoctors.ca/unsubscribe/unsubscribeAll/' . md5($client->client_id) . '">unsubscribe</a> at any time.</div></body></html>';

            $config['mailtype'] = 'html';
            $this->email->clear(TRUE);
            $this->email->initialize($config);

            $this->email->to($client->cc_email);
            $this->email->from('info@treedoctors.ca', 'Tree Doctors');
            $this->email->subject($subject);
            $this->email->message($body);
            $this->email->send();
        }
    }

    function migrate_cc()
    {
        die;
        $this->load->model('mdl_clients');
        $this->load->driver('payment');
        $rows = $this->db->query("SELECT * FROM `clients` WHERE client_cc_number IS NOT NULL AND client_payment_profile_id IS NULL")->result();

        foreach ($rows as $k => &$client) {
            $cards = json_decode($client->client_cc_number);
            if (json_last_error() === JSON_ERROR_NONE) {
                foreach ($cards as $card) {
                    if ($card->client_cc_number) {
                        $jkey = md5($this->config->item('encryption_key') . $client->client_id);
                        $client_cc_number = decrypt_data($jkey, $card->client_cc_number);
                        $client_cc_number = preg_replace('/[^0-9]/iu', '', $client_cc_number);
                        $client_cc_exp_year = strlen($card->client_cc_exp_year) != 2 ? substr($card->client_cc_exp_year, -2) : $card->client_cc_exp_year;
                        $client_cc_exp_month = strlen($card->client_cc_exp_month) == 1 ? '0' . $card->client_cc_exp_month : $card->client_cc_exp_month;
                        $client_cc_cvv = /* $card->client_cc_cvv == "" ? "123" :*/
                            $card->client_cc_cvv;
                        $today = \Carbon\Carbon::now();
                        $mm = strlen($today->month) == 1 ? '0' . $today->month : $today->month;
                        $year = strlen($today->year) != 2 ? substr($today->year, -2) : $today->year;
                        if ((int)$client_cc_exp_year < (int)$year) {
                            continue;
                        } elseif ($client_cc_exp_year == $year) {
                            if ((int)$client_cc_exp_month <= (int)$mm) {
                                continue;
                            }
                        }
                        if ($token = $this->payment->tokenize($client_cc_number, $card->client_cc_exp_month, $client_cc_exp_year, $client_cc_cvv)) {
                            if ($client->client_payment_profile_id) {
                                try {
                                    $this->payment->profileAddCard($client, $token, $card->client_cc_name);
                                    echo $client->client_id . "\t" . $client_cc_number . "\tAddCard\r\n";
                                } catch (Exception $e) {
                                    echo $client->client_id . "\t" . $client_cc_number . "\t" . $e->getMessage() . "\r\n";
                                    continue;
                                }
                            } else {
                                try {
                                    $profile_id = $this->payment->createProfile($client, $token, $card->client_cc_name);
                                    echo $client->client_id . "\t" . $client_cc_number . "\tcreateProfile\r\n";
                                } catch (PaymentException $e) {
                                    echo $client->client_id . "\t" . $client_cc_number . "\t" . $e->getMessage() . "\r\n";
                                    continue;
                                }

                                $upd = [
                                    'client_payment_profile_id' => $profile_id,
                                    'client_payment_driver' => $this->payment->getAdapter(),
                                    //'client_cc_number' => null
                                ];
                                $this->mdl_clients->update_client($upd, ['client_id' => $client->client_id]);

                                $client->client_payment_profile_id = $profile_id;
                                $client->client_payment_driver = $this->payment->getAdapter();
                            }
                        }
                    }
                }
            }
            /** TODO: COMPLETE! */
        }
    }

    function payment_bambora_delete_profiles()
    {
        $this->load->model('mdl_clients');
        $this->load->driver('payment');
        $rows = $this->db->query("SELECT * FROM `clients` WHERE client_payment_profile_id IS NOT NULL LIMIT 10")->result();

        foreach ($rows as $k => &$client) {
            try {
                $this->payment->deleteProfile($client->client_payment_profile_id);
                echo $client->client_id . "\tdelete profile\r\n";
            } catch (Exception $e) {
                echo $client->client_id . $e->getMessage() . "\r\n";
                continue;
            }
        }
    }

    function get_transaction_info($transId, $driver = false){
        if(!$driver)
            $driver = config_item('payment_default');
        if(!$transId)
            return FALSE;
        $CI = & get_instance();
        $CI->load->driver('payment');
        $CI->payment->setAdapter($driver);
        try {
            $result = $CI->payment->checkTransaction($transId);
        } catch (Exception $e) {
            die($e->getMessage());
        }
        echo json_encode($result);
    }

    function void_transaction($transId, $driver = false){
        if(!$driver)
            $driver = config_item('payment_default');
        if(!$transId)
            return FALSE;
        $CI = & get_instance();
        $CI->load->driver('payment');
        $CI->payment->setAdapter($driver);
        try {
            $result = $CI->payment->void($transId);
        } catch (Exception $e) {
            die($e->getMessage());
        }
        echo json_encode($result);
    }

    function payment_authorize_profiles()
    {
        $this->load->model('mdl_clients');
        $this->load->driver('payment');
        try {
            $ids = $this->payment->getProfiles();
            echo "success get " . count($ids) . ' IDs';
        } catch (Exception $e) {
            echo $e->getMessage() . "\r\n";
            die();
        }

        foreach ($ids as $id) {
            try {
                $profile = $this->payment->getProfile($id);
                echo json_encode($profile, JSON_PRETTY_PRINT) . "\r\n";
            } catch (Exception $e) {
                echo $e->getMessage() . "\r\n";
                continue;
            }
        }
    }

    function payment_authorize_delete_profile($id)
    {
        $this->load->model('mdl_clients');
        $this->load->driver('payment');
        try {
            $ids = $this->payment->deleteProfile($id);
            echo "success";
        } catch (Exception $e) {
            echo $e->getMessage() . "\r\n";
            die();
        }
    }

    public function eloquent_test()
    {
        //$this->output->enable_profiler = true;
        /** @var PaymentTransaction $model */
        //$model = PaymentTransaction::find(1);
        $model = User::withMeta()->find(1);
        $renderer = app('debugbar')->getJavascriptRenderer();
        $renderer->setOpenHandlerUrl('_debugbar/handle');
        $this->load->view('errors/html/error_404',['heading'=> 'test','message' => var_export($model,true),'renderer' => $renderer]);
    }

	function test_cc_bcc() {
		$subject = 'Test Message';
        $this->load->library('email');
		$config['mailtype'] = 'html';
		$this->email->clear(TRUE);
		$this->email->initialize($config);

		$this->email->to('magicpurplecow@gmail.com');
		$this->email->cc('magicpurplecow+cc@gmail.com');
		$this->email->bcc('freakerror13+bcc@gmail.com');
		$this->email->from('info@treedoctors.ca', 'Tree Doctors');
		$this->email->subject($subject);
		$this->email->message('Bark');
		$this->email->send();
	}

	function importJobber() {

        set_time_limit(0);

        $ignoreRows = [
            'Contact',
            'Report totals:'
        ];

        $content = @file_get_contents(base_url('uploads/jobber.csv'));
        if ($content === false) {
            die('jobber.csv Not Found');
        }

        $data = [];

        $fp = tmpfile();
        fwrite($fp, $content);
        rewind($fp);

        while (($row = fgetcsv($fp, 0)) !== FALSE) {
            if(in_array($row[0], $ignoreRows, true)) {
                continue;
            }

            $row[2] = preg_replace('/[^0-9]/', '', $row[2]);
            $row[4] = str_replace('-', '', $row[4]);
            $row[7] = '';

            $replaceInAddress = [
                "\n",
                "\r"
            ];

            if(preg_match('/.*?(\d{7,}).*?/', $row[4], $matches)) {
                $row[($row[2] === '' || $row[2] === $matches[0]) ? 2 : 7] = $matches[1] ?? null;
                $replaceInAddress[] = $matches[1];
            }
            if(preg_match('/.*?[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,3}).*?/', $row[4], $matches)) {
                $row[3] = $matches[0] ?? null;
                $replaceInAddress[] = $matches[0];
            }

            $row[4] = str_replace($replaceInAddress, ',', $row[4]);
            $row[4] = rtrim(str_replace([',,,', ',,', ', , '], ', ', $row[4]), ', ');

            $data[] = $row;
        }

        usort($data, fn ($a, $b) => strtotime($a[5]) - strtotime($b[5]));

        foreach ($data as $key => $row) {
            $clientName = $contactName = $row[1];
            $clientType = 1;

            if (($row[0] === $row[1] || $row[1] === '') && ($row[0] !== '')) {
                $clientName = $contactName = $row[0];
            } elseif ($row[0] !== $row[1] && $row[0] !== '' && $row[1] !== '') {
                $clientName = $row[0];
                $contactName = $row[1];
                $clientType = 2;
            }

            if(stripos($clientName, 'city') !== false &&
                stripos($clientName, 'government') !== false
            ) {
                $clientType = 3;
            }

            $address = $row[4];
            $city = null;
            $state = null;
            $zip = null;
            $lat = null;
            $lon = null;
            $country = config_item('office_country');

            $addressData = get_lat_lon($row[4]);

            if (isset($addressData['address']) && isset($addressData['city']) && $addressData['address'] && $addressData['city']) {
                $address = $addressData['address'];
                $city = $addressData['city'];
                $state = $addressData['state'];
                $lat = $addressData['lat'];
                $lon = $addressData['lon'];
                $zip = $addressData['zip'];
                $country = $addressData['country'];
            }

            $client = new \application\modules\clients\models\Client();
            $client->client_name = $clientName;
            $client->client_type = $clientType;
            $client->client_country = $country ?: '';
            $client->client_state = $state ?: '';
            $client->client_address = $address ?: '';
            $client->client_city = $city ?: '';
            $client->client_zip = $zip ?: '';
            $client->client_lat = $lat ?: '';
            $client->client_lng = $lon ?: '';
            $client->client_date_created = $row[5];
            $client->save();

            $client->contacts()->create([
                'cc_title' => 'Contact #1',
                'cc_name' => $contactName,
                'cc_phone' => numberFrom($row[2]),
                'cc_phone_clean' => numberFrom($row[2]),
                'cc_email' => trim($row[3]),
                'cc_email_check' => null,
                'cc_email_manual_approve' => 1,
                'cc_print' => 1,
            ]);

            if(isset($row[7]) && $row[7] !== '' && numberFrom($row[7]) !== numberFrom($row[2])) {
                $client->contacts()->create([
                    'cc_title' => 'Contact #2',
                    'cc_name' => $contactName,
                    'cc_phone' => numberFrom($row[7]),
                    'cc_phone_clean' => numberFrom($row[7]),
                    'cc_email' => null,
                    'cc_email_check' => null,
                    'cc_email_manual_approve' => 0,
                    'cc_print' => 0,
                ]);
            }
        }

        echo "Finished";
    }

	function importLewis() {
        $rows = $this->db/*->limit(100)*/->where('Main_Phone IS NOT NULL')->or_where('Main_Email IS NOT NULL')->get('table_name')->result();
        foreach ($rows as $row) {
            $data = [
                'client_name' => $row->Customer ?? $row->Company,
                'client_type' => $row->Company ? 2 : 1,
                'client_date_created' => date('Y-m-d'),
                'client_country' => 'USA',
                'client_state' => 'California',
                'client_address' => '',
                'client_city' => '',
                'client_zip' => ''
            ];
            $keys = ['Bill_to_1', 'Bill_to_2', 'Bill_to_3', 'Bill_to_4', 'Bill_to_5', 'Ship_to_1', 'Ship_to_2', 'Ship_to_3', 'Ship_to_4', 'Ship_to_5'];
            $extraNotes = [];
            foreach ($keys as $key) {
                if(!$row->$key)
                    continue;

                $states = 'CA|Ca|Ca.|California|DE|Oregon|NV|Texas|IL|AZ|TX|Va|Co|PA|NE|WI|FL|WA|NY|OK|Ga|HI|MD|ID|TN|MT';

                if(!$data['client_address'] && preg_match('/^[0-9\-]{1,7}[A-Z]{0,2} .*$/is', $row->$key)) {
                    $data['client_address'] = trim($row->$key);
                    unset($row->$key);
                }
                //
                elseif(!$data['client_city'] && preg_match("/^([a-zA-Z- ]{3,50})(.|,|. |, |,  | |  )($states)([ 0-9\-]{5,13}$|$)$/is", $row->$key, $matches)) {
                    $data['client_city'] = trim($matches[1]);
                    $data['client_zip'] = trim($matches[4]);
                    if(strtolower($matches[3]) == 'nv')
                        $data['client_state'] = 'Nevada';
                    elseif(strtolower($matches[3]) == 'texas' || strtolower($matches[3]) == 'tx')
                        $data['client_state'] = 'Texas';
                    elseif(strtolower($matches[3]) == 'il')
                        $data['client_state'] = 'Illinois';
                    elseif(strtolower($matches[3]) == 'az')
                        $data['client_state'] = 'Arizona';
                    elseif(strtolower($matches[3]) == 'oregon')
                        $data['client_state'] = 'Oregon';
                    elseif(strtolower($matches[3]) == 'de')
                        $data['client_state'] = 'Delaware';
                    elseif(strtolower($matches[3]) == 'va')
                        $data['client_state'] = 'Virginia';
                    elseif(strtolower($matches[3]) == 'ga')
                        $data['client_state'] = 'Georgia';
                    elseif(strtolower($matches[3]) == 'co')
                        $data['client_state'] = 'Colorado';
                    elseif(strtolower($matches[3]) == 'pa')
                        $data['client_state'] = 'Pennsylvania';
                    elseif(strtolower($matches[3]) == 'ne')
                        $data['client_state'] = 'Nebraska';
                    elseif(strtolower($matches[3]) == 'wi')
                        $data['client_state'] = 'Wisconsin';
                    elseif(strtolower($matches[3]) == 'fl')
                        $data['client_state'] = 'Florida';
                    elseif(strtolower($matches[3]) == 'wa')
                        $data['client_state'] = 'Washington';
                    elseif(strtolower($matches[3]) == 'ny')
                        $data['client_state'] = 'New York';
                    elseif(strtolower($matches[3]) == 'ok')
                        $data['client_state'] = 'Oklahoma';
                    elseif(strtolower($matches[3]) == 'hi')
                        $data['client_state'] = 'Hawaii';
                    elseif(strtolower($matches[3]) == 'md')
                        $data['client_state'] = 'Maryland';
                    elseif(strtolower($matches[3]) == 'id')
                        $data['client_state'] = 'Idaho';
                    elseif(strtolower($matches[3]) == 'tn')
                        $data['client_state'] = 'Tennessee';
                    elseif(strtolower($matches[3]) == 'mt')
                        $data['client_state'] = 'Montana';
                    unset($row->$key);
                }
            }

            foreach ($keys as $key) {
                if(!isset($row->$key) || !$row->$key)
                    continue;

                if(!$data['client_city'] && preg_match('/^([a-zA-Z\- ]{3,50})(.|,|. |, |,  | |  )([ 0-9\-]{5,13})$/is', $row->$key, $matches)) {
                    $data['client_city'] = trim($matches[1]);
                    $data['client_zip'] = trim($matches[3]);
                    unset($row->$key);
                }

                elseif(!$data['client_city'] && preg_match("/^([a-zA-Z\., ]{3,50})(, )($states)([ 0-9\-]{5,13})(\.){0,1}$/is", $row->$key, $matches)) {
                    $data['client_city'] = trim($matches[1]);
                    $data['client_zip'] = trim($matches[4]);
                    unset($row->$key);
                }

                elseif(!$data['client_city'] && preg_match("/^([a-zA-Z\., ]{3,50})(, )($states)([ 0-9\-\.]{5,13})(\.){0,1}$/is", $row->$key, $matches)) {
                    $data['client_city'] = trim($matches[1]);
                    $data['client_zip'] = trim($matches[4]);
                    unset($row->$key);
                }

                elseif (!$data['client_city'] && $row->$key == 'Santa Cruz') {
                    $data['client_city'] = 'Santa Cruz';
                    unset($row->$key);
                }

                elseif (!$data['client_city'] && $row->$key == 'San Jose') {
                    $data['client_city'] = 'San Jose';
                    unset($row->$key);
                }

                else {
                    if($data['client_address'] != $row->$key && $data['client_city'] != $row->$key && ($data['client_city'] && (strpos($row->$key, $data['client_city']) === FALSE || strpos($row->$key, $data['client_zip']) === FALSE)))
                        $extraNotes[] = $key . ': ' . $row->$key;
                }
            }

            $this->db->insert('clients', $data);
            $clientId = $this->db->insert_id();

            if(is_array($extraNotes) && !empty($extraNotes)) {
                foreach ($extraNotes as $note) {
                    $this->db->insert('client_papers', [
                        'cp_client_id' => $clientId,
                        'cp_user_id' => 0,
                        'cp_text' => $note,
                        'cp_date' => date('Y-m-d 00:00:00')
                    ]);
                }
            }

            $phone = preg_replace('/\D/', '', $row->Main_Phone);
            $email = $row->Main_Email;
            $name = $row->Primary_Contact ? $row->Primary_Contact : $row->First_Name . ' ' . $row->Last_Name;
            if(!$name)
                $name = $row->Customer;
            if(!$name)
                $name = $row->Company;

            $contact = [
                'cc_title' => 'Primary',
                'cc_print' => 1,
                'cc_client_id' => $clientId,
                'cc_name' => $name,
                'cc_email' => $email,
                'cc_phone' => $phone,
                'cc_phone_clean' => $phone,
            ];
            $this->db->insert('clients_contacts', $contact);

            $fax = preg_replace('/\D/', '', $row->Fax);

            if($fax) {
                $contact['cc_title'] = 'Fax';
                $contact['cc_email'] = NULL;
                $contact['cc_name'] = NULL;
                $contact['cc_phone'] = $fax;
                $contact['cc_phone_clean'] = $fax;
                $contact['cc_print'] = 0;
                $this->db->insert('clients_contacts', $contact);
            }

            $alt = preg_replace('/\D/', '', $row->Alt_Phone);

            if($alt) {
                $contact['cc_title'] = 'Alt';
                $contact['cc_email'] = NULL;
                $contact['cc_name'] = NULL;
                $contact['cc_phone'] = $alt;
                $contact['cc_phone_clean'] = $alt;
                $contact['cc_print'] = 0;
                $this->db->insert('clients_contacts', $contact);
            }

            $sec = preg_replace('/\D/', '', $row->Secondary_Contact);

            if($sec) {
                $contact['cc_title'] = 'Secondary';
                $contact['cc_email'] = NULL;
                $contact['cc_name'] = NULL;
                $contact['cc_phone'] = $sec;
                $contact['cc_phone_clean'] = $sec;
                $contact['cc_print'] = 0;
                $this->db->insert('clients_contacts', $contact);
            }
        }
    }

    function importCustom() {
        set_time_limit(0);

        $ignoreRows = [
            'id',
            '4377684',
            '4427900',
            'Report totals:'
        ];

        $content = @file_get_contents(base_url('uploads/custom.csv'));
        if ($content === false) {
            die('custom.csv Not Found');
        }

        $data = [];

        $fp = tmpfile();
        fwrite($fp, $content);
        rewind($fp);

        while (($row = fgetcsv($fp, 0)) !== FALSE) {
            if(in_array($row[0], $ignoreRows, true)) {
                continue;
            }

            $row[5] = preg_replace('/[^0-9]/', '', $row[5]);
            $row[15] = preg_replace('/[^0-9]/', '', $row[15]);
            $data[] = $row;
        }

        foreach ($data as $key => $row) {
            $clientName = $contactName = ($row[17] !== '' && $row[19] !== '') ? $row[17] . ' ' . $row[19] : $row[1];
            $clientType = 1;

            if($row[20] !== '') {
                $clientName = $row[20];
                $clientType = 2;
            }
            /*if (($row[1] === $row[6] || $row[6] === '') && ($row[1] !== '')) {
                $clientName = $contactName = $row[1];
            } elseif ($row[1] !== $row[6] && $row[1] !== '' && $row[6] !== '') {
                $clientName = $row[1];
                $contactName = $row[6];
                $clientType = 2;
            }*/

            if(stripos($clientName, 'city') !== false &&
                stripos($clientName, 'government') !== false
            ) {
                $clientType = 3;
            }

            $address = $row[10];
            $city = $row[2];
            $state = $row[3];
            $zip = $row[13];
            $lat = null;
            $lon = null;
            $country = config_item('office_country');

            $addressData = get_lat_lon($address, $city, $state, $zip);

            if (isset($addressData['address']) && isset($addressData['city']) && $addressData['address'] && $addressData['city']) {
                $address = $addressData['address'];
                $city = $addressData['city'];
                $state = $addressData['state'];
                $lat = $addressData['lat'];
                $lon = $addressData['lon'];
                $zip = $addressData['zip'];
                $country = $addressData['country'];
            }

            $client = new \application\modules\clients\models\Client();
            $client->client_name = $clientName;
            $client->client_type = $clientType;
            $client->client_country = $country ?: '';
            $client->client_state = $state ?: '';
            $client->client_address = $address ?: '';
            $client->client_city = $city ?: '';
            $client->client_zip = $zip ?: '';
            $client->client_lat = $lat ?: '';
            $client->client_lng = $lon ?: '';
            $client->client_date_created = date('Y-m-d');
            $client->save();

            $client->contacts()->create([
                'cc_title' => 'Contact #1',
                'cc_name' => $contactName,
                'cc_phone' => numberFrom($row[5]),
                'cc_phone_clean' => numberFrom($row[5]),
                'cc_email' => $row[4] && $row[4] !== 'NULL' ? trim($row[4]) : null,
                'cc_email_check' => null,
                'cc_email_manual_approve' => 1,
                'cc_print' => 1,
            ]);

            if($row[15] !== '') {
                $client->contacts()->create([
                    'cc_title' => 'Mobile',
                    'cc_name' => $contactName,
                    'cc_phone' => numberFrom($row[15]),
                    'cc_phone_clean' => numberFrom($row[15]),
                    'cc_email' => null,
                    'cc_email_check' => null,
                    'cc_email_manual_approve' => 1,
                    'cc_print' => 0,
                ]);
            }

            if($row[21] !== '') {
                $client->papers()->create([
                    'cp_user_id' => 0,
                    'cp_text' => $row[21],
                    'cp_date' => date('Y-m-d')
                ]);
            }
        }

        echo "Finished";
    }

    function update_stump_desc()
    {

        $this->load->model('mdl_stumps');
        $stumps = $this->db->query('SELECT stump_id, stump_desc, stump_data FROM `stumps` WHERE stump_client_id = 17')->result_array();
        /*echo '<pre>'; var_dump($stumps); die;*/
        foreach ($stumps as $k => $v) {
            $val = (array)json_decode($v['stump_data']);

            $newData['stump_desc'] = $val['Site'] && $val['Site'] != '' ? 'Site: ' . $val['Site'] : '';
            $newData['stump_desc'] .= $val['Asset Location Details'] ? '<br>Asset Location Details: ' . $val['Asset Location Details'] : '';
            $newData['stump_desc'] .= $val['Inspector Notes'] ? '<br>Inspector Notes: ' . $val['Inspector Notes'] : ' ';
            $this->mdl_stumps->update_stumps(array('stump_desc' => trim(trim($newData['stump_desc']), '<br>')), array('stump_id' => $v['stump_id']));
            $querys[$k] = $this->db->last_query() . ';';
        }
        echo '<pre>';
        var_dump($querys);
    }

    function patchStumpList(){
        $this->load->model('mdl_stumps');
        $file = 'uploads/04_08_21.csv';
        $csv = array_map('str_getcsv', file($file));
        //
        array_walk($csv, function(&$a) use ($csv) {
            $a = array_combine($csv[0], $a);
        });
        array_shift($csv);

        foreach($csv as $key=>$val) {
            $res = strripos($val['Activity'], 'FILL AND SEED');
            if ($res !== false) {
                continue;
            }

            $data = [];
            $data['stump_status'] = 'new';
            $data['stump_client_id'] = 17;
            $data['stump_status_work'] = 0;

            $data['stump_side'] = NULL;//trim($val[7]); // strpos($val[5], '/') ? $val[5] . ' ' . trim($val[7]) : trim($val[7]);

            //$data['stump_locates'] = $val[11]; //(string)$val[10];
            $data['stump_removal'] = NULL;
            $data['stump_clean'] = NULL;
            $data['stump_contractor_notes'] = NULL;
            //echo "<pre>";var_dump($data);die;
            //echo "<pre>";var_dump($data, $data);die;
            //$val[6] ? "Tree #" . $val[6] . "\n" : '';
            //$data['stump_desc'] .= $val[3] ? "Route #" . $val[3] : '';

            $data['stump_map_grid'] = NULL;
            $data['stump_desc'] = $val['Wards'] ? 'Wards: ' . $val['Wards'] : ' ';
            $data['stump_desc'] .= $val['Date Package Issued'] ? '<br>Date Package Issued: ' . $val['Date Package Issued']  : ' ';
            $data['stump_desc'] .= $val['Package Due Date'] ? '<br>Package Due Date: ' . $val['Package Due Date']  : ' ';
            $data['stump_desc'] .= $val['Sub-Grid'] ? '<br>Sub-Grid: ' . $val['Sub-Grid']  : ' ';
            $data['stump_desc'] .= $val['Work Order #'] ? '<br>Work Order #: ' . $val['Work Order #']  : ' ';
            $data['stump_desc'] .= $val['Activity'] ? '<br>Activity: ' . $val['Activity']  : ' ';
            $data['stump_desc'] .= $val['Region'] ? '<br>Region: ' . $val['Region']  : ' ';
            $data['stump_desc'] .= $val['Ward'] ? '<br>Ward: ' . $val['Ward']  : ' ';
            $data['stump_desc'] .= $val['Site'] ? '<br>Site: ' . $val['Site']  : ' ';
            $data['stump_desc'] .= $val['Tree Species'] ? '<br>Tree Species: ' . $val['Tree Species']  : ' ';
            $data['stump_desc'] .= $val['Asset Location Details'] ? '<br>Asset Location Details: ' . $val['Asset Location Details']  : ' ';
            $data['stump_desc'] .= $val['Inspector Notes'] ? '<br>Inspector Notes: ' . $val['Inspector Notes']  : ' ';
            $data['stump_house_number'] = intval($val['Address']);
            $data['stump_address'] = trim(preg_replace('/[0-9]+/', '', $val['Address']));
            $data['stump_unique_id'] = $val['Tree ID'] ? $val['Tree ID'] : NULL;
            $data['stump_range'] = $val['Tree Size'];
            $data['stump_city'] = 'Toronto';
            $data['stump_state'] = 'ON';
            $data['stump_data'] = json_encode($val);
            //echo '<pre>' ; var_dump($data); die;
            $this->mdl_stumps->insert_stumps($data);
        }
    }


    function update_desc_stump(){
        $this->load->model('mdl_stumps');
        $file = 'uploads/11_08_4.csv';

        $csv = array_map('str_getcsv', file($file));
        //
        array_walk($csv, function(&$a) use ($csv) {
            $a = array_combine($csv[0], $a);
        });
        array_shift($csv);

        $ids = [];
        foreach($csv as $key=>$val) {
            if($val['Geo Tagged Photos Required'] == 'Yes' && array_search($val['Tree ID'], $ids) === false)
                $ids[] = trim($val['Tree ID']);
        }
        $id = '(';
        foreach($ids as $k=>$v) {
            $id .= "'" . $v . "', ";
        }
        $id .= ')';
        die($id);
    }

    function add_ward_to_stump_desc(){
        $this->load->model('mdl_stumps');
        $stumps = $this->db->query('SELECT stump_id, stump_desc, stump_data FROM `stumps` WHERE stump_client_id = 17')->result_array();
        $a = 0;
        foreach($stumps as $k=>$v){
            $data = (array) json_decode($v['stump_data']);
            $new_desc['stump_desc'] = $v['stump_desc'];
            if(isset($data['Work Package Number ']))
                $new_desc['stump_desc'] .= '<br> Work Package Number: ' . $data['Work Package Number '];
            if(isset($data['Ward']))
                $new_desc['stump_desc'] .= '<br> Ward: ' . $data['Ward'];
            /*echo '<pre>'; var_dump($v['stump_id'], $new_desc); die;*/
            $this->mdl_stumps->update_stumps($new_desc, ['stump_id' => $v['stump_id']]);
            /*$this->db->query('UPDATE stumps SET stump_desc =  ' . $new_desc . '  WHERE stump_id = ' . $v["stump_id"]);*/
            $a++;

        }
        echo '<pre>'; var_dump($a); die;
    }
}
