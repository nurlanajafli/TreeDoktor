<?php

class EventActions
{
    protected $CI;
    protected $event;
    protected $eventId;

    function __construct($eventId = NULL) {
        $this->CI =& get_instance();

        $this->CI->load->model('mdl_events_orm');
        $this->CI->load->model('mdl_events_reports');
        $this->CI->load->model('mdl_workorders');
        $this->CI->load->model('mdl_estimates');

        $this->CI->load->model('mdl_schedule');
        
        $this->CI->load->library('mpdf');

        $this->eventId = $eventId;
        if($eventId) {
            $this->event = $this->CI->mdl_events_orm->get_by(['ev_event_id'=>$eventId]);
        }
    }

    public function setEventId($eventId, $date = false) {
        if(!$date)
            $date = date("Y-m-d");
        $this->event = $this->CI->mdl_events_orm->get_by(['ev_event_id'=>$eventId, 'ev_date' => $date]);

        if(!$this->event)
            return FALSE;

        $this->eventId = $eventId;
        return TRUE;
    }

    public function getEvent() {
        if($this->event)
            return $this->event;

        return FALSE;
    }

    /*-- args: ev_event_id,  ev_team_id,  ev_estimate_id --*/
    public function start_trevel($input) {
        
        if(!isset($input['ev_estimate_id']) && isset($input['wo_id']))
        {
            $workorder = $this->CI->mdl_workorders->wo_find_by_id($input['wo_id'], false);
            $input['ev_estimate_id'] = $workorder->estimate_id;
        }

        if(!$this->valid_event($input))
            return FALSE;

        $input['ev_start_travel'] = $input['ev_start_travel']??(($input["ev_date"]??date("Y-m-d")).' '.date("H:i:s"));
        $input['ev_start_time'] = $input['ev_start_time']??(($input['ev_start_time']??date("Y-m-d")).' '.date("H:i:s"));
        $data = [
            "ev_event_id"     => $input["ev_event_id"],
            "ev_team_id"      => $input["ev_team_id"],
            "ev_date"         => $input["ev_date"],
            "ev_estimate_id"  => $input["ev_estimate_id"],
            "ev_start_travel" => $input['ev_start_travel'],
            "ev_start_time"   => $input['ev_start_time'],
        ];
        
        $this->CI->mdl_events_orm->save($data);
        $this->CI->mdl_schedule->update($input['ev_event_id'], ['event_state' => 1]);
        return TRUE;
    }

    public function start_work($input){
        if(!isset($input['ev_estimate_id']) && isset($input['wo_id']))
        {
            $workorder = $this->CI->mdl_workorders->wo_find_by_id($input['wo_id'], false);
            $input['ev_estimate_id'] = $workorder->estimate_id;
        }
        
        if(!$this->valid_event($input))
            return FALSE;

        $this->setEventId($input["ev_event_id"]);
        
        $ev_travel_time = 0;
        if($this->event && $this->event->ev_start_travel)
            $ev_travel_time = time()-strtotime($this->event->ev_start_travel);

        $data = [
            "ev_event_id"     => $input["ev_event_id"],
            "ev_team_id"      => $input["ev_team_id"],
            "ev_estimate_id"  => $input["ev_estimate_id"],
            "ev_start_time"   => $input["ev_start_time"] ?? date("Y-m-d H:i:s"),
            "ev_start_work"   => $input["ev_start_work"] ?? date("Y-m-d H:i:s"),
            "ev_tailgate_safety_form"=>json_encode($input),
            "ev_travel_time"  => $ev_travel_time,
            "ev_date"   => $input['date']??date("Y-m-d")
        ];

        $results = $this->CI->mdl_events_orm->save($data);
        $this->setEventId($input["ev_event_id"]);

        if(!$results)
            return FALSE;


        //$this->CI->mdl_schedule->update($input['ev_event_id'], ['event_state' => 2]);
        return $this->event->ev_id;
    }

    public function end_work($input){
        if(!isset($this->event) || empty($this->event) || !$this->event)
            return [];
        $fields = $this->CI->mdl_events_reports->getFields();
        $prefix = $this->CI->mdl_events_reports->getPrefix();

        $insert = [];
        foreach ($fields as $key => $value)
            $insert[$value] = element(str_replace($prefix, '', $value), $input, '');

        $insert['er_event_id'] = $this->event->ev_event_id;
        $insert['er_team_id'] = $this->event->ev_team_id;
        $insert['er_estimate_id'] = $this->event->ev_estimate_id;
        
        $insert['er_event_date'] = $input['er_event_date'] ?? date("Y-m-d");

        if(isset($this->event->ev_start_work) && $this->event->ev_start_work)
            $insert['er_event_date'] = date("Y-m-d", strtotime($this->event->ev_start_work));

        if(!isset($input['event_start_work']) || !$input['event_start_work']) {
            $insert['er_event_start_work'] = date("H:i", strtotime($this->event->ev_start_work));
        } else {
            $insert['er_event_start_work'] = date("H:i", strtotime($input['event_start_work']));
        }

        if(!isset($input['event_finish_work']) || !$input['event_finish_work']) {
            $insert['er_event_finish_work'] = date("H:i");
        } else {
            $insert['er_event_finish_work'] = date("H:i", strtotime($input['event_finish_work']));
        }

        if(!isset($input['event_start_travel']) || !$input['event_start_travel']){
            if($this->event->ev_start_travel)
                $insert['er_event_start_travel'] = date("H:i", strtotime($this->event->ev_start_travel));
            else
                $insert['er_event_start_travel'] = date("H:i", strtotime($this->event->ev_start_work));
        } else {
            $insert['er_event_start_travel'] = date("H:i", strtotime($input['event_start_travel']));
        }


        $insert['er_event_status_work']    = $input['status'];
        $insert['er_team_fail_equipment']  = element('malfunctions_description', $input, '');
        
        if(isset($input['payment_amount']))
            $insert['er_event_payment_amount'] = money($input['payment_amount']);

        $insert['er_report_date'] = $input['date']??date("Y-m-d");

        $input['ev_on_site_time'] = time()-strtotime($this->event->ev_start_work);
        $input['ev_end_work'] = $input['ev_end_work'] ?? date("Y-m-d H:i:s");

        $input['ev_start_work'] = $this->event->ev_start_work;
        if(!$this->event->ev_start_work){
            $input['ev_start_work'] = $input['ev_end_work'];
        }
        
        if(!$this->event->ev_start_travel){
            $input['ev_start_travel'] = element('ev_start_work', $input, $input['ev_end_work']);
        }

        if(isset($input['ev_start_work'])){
            $input['ev_on_site_time'] = strtotime($input['ev_end_work'])-strtotime($input['ev_start_work']);
        }

        if(isset($input['ev_start_travel'])){
            $input['ev_travel_time'] = strtotime(element('ev_start_work', $input, $input['ev_end_work']))-strtotime($input['ev_start_travel']);
        }
        
        $this->CI->mdl_events_orm->save($input, $this->event->ev_id);
        $this->CI->mdl_events_reports->save($insert);
        //$this->CI->mdl_schedule->update($this->event->ev_event_id, ['event_report' => json_encode($insert), 'event_state' => 3]);
        
        $finishedByFieldStatus = $this->CI->mdl_workorders->getFinishedByField();
        if($finishedByFieldStatus!==FALSE && $input['status'] === 'finished')
            $this->CI->mdl_workorders->update_workorder(['wo_status'=>$finishedByFieldStatus], ['id'=>$input['wo_id']]);

        // make notes
        $leader_name = $this->CI->mdl_schedule->get_teams(['team_id' => $this->event->ev_team_id]);
        $workorder = $this->CI->mdl_workorders->wo_find_by_id($input['wo_id'], false);
        
        $update_msg = $leader_name[0]->emp_name . ' filled report for <a href="' . base_url($workorder->workorder_no) . '#eventInfo-' . $this->event->ev_event_id . '" data-toggle="modal">' . $workorder->workorder_no . '</a>';
        make_notes($workorder->client_id, $update_msg, 'system', intval($workorder->workorder_no));
        // end make notes
        
        $update_team = [];
        if(isset($input['malfunctions_description']))
            $update_team['team_fail_equipment'] = $input['malfunctions_description'];
        
        if(isset($input['expenses_description']))
            $update_team['team_expenses'] = $input['expenses_description'];
        
        if(count($update_team))
            $this->CI->mdl_schedule->update_team($this->event->ev_team_id, $update_team);

        $sig_event_id = $this->event->ev_event_id.'_'.$workorder->client_id;
        
        if(isset($input['client_signature_image']) && $input['client_signature_image'])
            save_signature(['ev_estimate_id'=>$this->event->ev_estimate_id, 'ev_event_id'=>$sig_event_id, 'signature_image'=>$input['client_signature_image']]);

        return TRUE;
    }

    public function teams_new_events_reports($team_id = FALSE){
        
        $where = [/*'schedule.event_report_confirmed' => 0,*/ 'events_reports.er_report_confirmed' => 0];
        if($team_id)
            $where['schedule.event_team_id'] = $team_id;
                
        
        $result = $this->report_events($where);
        return $result;
    }

    function get_reports($event_id = FALSE, $once = FALSE){
        
        $where = [];
        if($event_id)
            $where = ['schedule.id' => $event_id];
                
        $result = [];
        if($once){
            $result = array_values($this->report_events($where));
            if(isset($result[0]) && isset($result[0][0]))
                return $result[0][0];
        }
        else
            $result = $this->report_events($where);

        return $result;
    }

    public function report_events($where=[])
    {   
        $events = $this->CI->mdl_events_reports->report_events($where);
        if(!count($events))
            return [];

        $result = [];
        foreach ($events as $key => $event) {
            $result[$event['event_team_id']][] = $event;
        }
        
        foreach($result as $tkey=> $team_events)
        {
            $estimate_services_data = [];
            foreach ($team_events as $key => $val) {
                if(isset($val['estimate_id']) && !isset($estimate_services_data[$val['estimate_id']])){
                    $estimate_services_data[$val['estimate_id']] = $this->CI->mdl_estimates->find_estimate_services($val['estimate_id']);
                }
                if(isset($val['estimate_id']) && isset($estimate_services_data[$val['estimate_id']])){
                    $result[$tkey][$key]['estimate_services_data'] = $estimate_services_data[$val['estimate_id']];
                }
            }
        }
        return $result;
    }

    public function workorder_events(){

    }

    public function get_report($er_id){
        return $this->CI->mdl_events_reports->get($er_id);
    }

    public function create_report($input){
        $fields = $this->CI->mdl_events_reports->getFields();
        $prefix = $this->CI->mdl_events_reports->getPrefix();

        $insert = [];
        foreach ($fields as $key => $value)
            $insert[$value] = element(str_replace($prefix, '', $value), $input, null);
        
        if(!isset($insert['er_payment_amount']))
            $insert['er_payment_amount'] = 0;
        
        $id = $this->CI->mdl_events_reports->insert($insert);
        return $id;
    }

    public function set_report($er_id, $data){

        $fields = $this->CI->mdl_events_reports->getFields();
        
        $update = [];
        foreach ($data as $key => $value) {
            if(array_search($key, $fields)!==FALSE)
            {
                $update[$key] = $value;
            }
        }
        
        $this->CI->mdl_events_reports->update((int)$er_id, $update);
        return TRUE;
    }

    function report_edit_fields(){
        return $this->CI->mdl_events_reports->get_edit_fields();
    }

    function create($input) {
        if(!isset($input['ev_estimate_id']) && isset($input['wo_id']))
        {
            $workorder = $this->CI->mdl_workorders->wo_find_by_id((int)$input['wo_id'], false);
            $input['ev_estimate_id'] = (isset($workorder->estimate_id))?$workorder->estimate_id:0;
        }

        if(!$this->valid_event($input))
            return FALSE;

        $this->CI->mdl_events_orm->save($input);
    }

    function update() {

    }

    function deposit() {

    }

    function valid_event($input)
    {
        if(!element("ev_event_id", $input) || !element("ev_team_id", $input) || !element("ev_estimate_id", $input))
            return FALSE;

        return TRUE;
    }

    function report_form_data($event)
    {
        
    }

    function create_pdf($file, $html){
        $this->CI->mpdf->WriteHTML($html);
        $this->CI->mpdf->_setPageSize('Letter', $this->CI->mpdf->DefOrientation);
        $this->CI->mpdf->SetHtmlFooter('');

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        header('Access-Control-Max-Age: 1000');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        $this->CI->mpdf->Output($file, 'I');
    }
}
