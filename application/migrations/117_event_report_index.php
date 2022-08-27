<?php

class Migration_event_report_index extends CI_Migration {

    public function up() {
        
        $sql = "CREATE INDEX er_event_id ON events_reports(er_event_id)";
        $this->db->query($sql);
        $sql = "CREATE INDEX er_estimate_id ON events_reports(er_estimate_id)";
        $this->db->query($sql);
        $sql = "CREATE INDEX er_team_id ON events_reports(er_team_id)";
        $this->db->query($sql);
        $sql = "CREATE INDEX er_wo_id ON events_reports(er_wo_id)";
        $this->db->query($sql);
        $sql = "CREATE INDEX er_event_date ON events_reports(er_event_date)";
        $this->db->query($sql);
        
    }

    public function down() {
        $this->db->query('ALTER TABLE events_reports DROP INDEX er_event_id;');
        $this->db->query('ALTER TABLE events_reports DROP INDEX er_estimate_id;');
        $this->db->query('ALTER TABLE events_reports DROP INDEX er_team_id;');
        $this->db->query('ALTER TABLE events_reports DROP INDEX er_wo_id;');
        $this->db->query('ALTER TABLE events_reports DROP INDEX er_event_date;');

    }

}