<?php

class Migration_patch_event_reports extends CI_Migration {

    public function up() {
        
        lock_arbostar();
        $this->load->library('Common/EventActions');
        
        $this->db->select('schedule.id, schedule.event_report, schedule.event_wo_id, schedule.event_team_id, events_reports.er_id, workorders.estimate_id', FALSE);
        $this->db->from('schedule');
        $this->db->join('events_reports', 'schedule.id = events_reports.er_event_id', 'left');
        $this->db->join('workorders', 'schedule.event_wo_id = workorders.id', 'left');
        
        $this->db->where('schedule.event_report IS NOT NULL AND events_reports.er_id IS NULL');
        $query = $this->db->get();

        $result = [];
        if($query->num_rows())
            $result = $query->result_array();

        if(!count($result))
            return TRUE;

        foreach ($result as $key => $item) {
            $this->eventactions->setEventId($item["id"]);
            $event = $this->eventactions->getEvent();
            if($event==FALSE){
                $this->eventactions->create([
                    "ev_event_id" => $item["id"], 
                    "ev_team_id" => $item["event_team_id"], 
                    "ev_estimate_id" => $item["estimate_id"]
                ]);
            }
            $report = json_decode($item['event_report'], TRUE);
            $this->eventactions->create_report([
                "event_id" => $item["id"], 
                "team_id" => $item["event_team_id"], 
                "wo_id" => $item["event_wo_id"],
                'estimate_id'=>$item['estimate_id'],
                "payment_amount"=>isset($report['event_payment_amount'])?(float)$report['event_payment_amount']:0,
            ]+$report);
        }
        unlock_arbostar();
    }

    public function down() {
        //$this->dbforge->drop_table('patch_event_reports');
    }

}