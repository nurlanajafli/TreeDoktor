<?php

class Mdl_safety_pdf_signs extends JR_Model
{
	protected $_table = 'safety_pdf_signs';
	protected $primary_key = 'id';

	public function __construct() {
		parent::__construct();
	}

    /**
     * @param $data
     */
    public function createEmptyTeamSlots($data)
    {
        $teamlead = $this->db
            ->select('team_leader_user_id as id')
            ->where('team_id', $data['team_id'])
            ->get('schedule_teams')
            ->row_array();
        $newMemebers = [];
        foreach ($this->db->select('user_id')->where('employee_team_id', $data['team_id'])->get('schedule_teams_members')->result_array() as $member) {
            $newMemebers[] = [
                'event_id'      => $data['event_id'],
                'team_id'       => $data['team_id'],
                'user_id'       => $member['user_id'],
                'is_teamlead'   => $member['user_id'] == $teamlead['id'],
            ];
        }

        if(count($newMemebers))
            $this->insert_many($newMemebers);
    }

    /**
     * @param $event_id
     * @param $user_id
     * @return array|null
     */
    public function isSigned($event_id, $user_id): ?array
    {
        return $this->db
            ->select('id')
            ->where('event_id', $event_id)
            ->where('user_id', $user_id)
            ->get($this->_table)
            ->row_array();
    }

    /**
     * @param $event_id
     * @return array
     */
    public function getMembersWithSigns($event_id, $date): array
    {
        if(!$date)
            $date = date('Y-m-d');

        return $this->db
            ->select('schedule_teams_members.user_id, safety_pdf_signs.signed')
            ->join('schedule_teams', 'schedule.event_team_id = schedule_teams.team_id')
            ->join('schedule_teams_members', 'schedule_teams.team_id = schedule_teams_members.employee_team_id', 'left')
            ->join('safety_pdf_signs', "safety_pdf_signs.team_id = schedule_teams_members.employee_team_id AND 
                safety_pdf_signs.user_id = schedule_teams_members.user_id AND safety_pdf_signs.event_id = schedule.id AND safety_pdf_signs.date = '".$date."'",
                'left')
            ->join('schedule_teams tleader', 'tleader.team_leader_user_id = schedule_teams_members.user_id AND schedule.event_team_id = tleader.team_id', 'left')
            ->where('schedule.id', $event_id)
            ->order_by('tleader.team_leader_user_id', 'DESC')
            ->get('schedule')
            ->result_array();
    }

    /**
     * @param null $event_id
     * @return bool
     */
    public function isTeamLeadSigned($event_id = null): bool
    {
        return (bool) $this->db
            ->select('id')
            ->where('event_id', $event_id)
            ->where('is_teamlead', 1)
            ->where('safety_pdf_sign <>', '')
            ->get($this->_table)
            ->row();
    }

    /**
     * @param $event_id
     * @return array|null
     */
    public function getLeadSignature($event_id): ?array
    {
        return $this->db
            ->select('user_id, safety_pdf_sign')
            ->where('event_id', $event_id)
            ->where('is_teamlead', 1)
            ->get($this->_table)
            ->row_array();
    }

    /**
     * @param $event_id
     * @return array|null
     */
    public function getTeamSignatures($event_id): ?array
    {
        return $this->db
            ->select('user_id, safety_pdf_sign')
            ->where('event_id', $event_id)
            ->where('is_teamlead', 0)
            ->get($this->_table)
            ->result_array();
    }
}
