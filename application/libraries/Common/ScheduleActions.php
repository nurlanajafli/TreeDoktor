<?php

use application\modules\schedule\models\ScheduleTeamsEquipment;
use application\modules\schedule\models\ScheduleTeamsMembers;
use application\modules\schedule\models\ScheduleUpdates;
use application\modules\schedule\models\ScheduleEvent;
use application\modules\schedule\models\ScheduleTeams;

class ScheduleActions
{

    /**
     * @param $equipments
     * @param $teamId
     * @param $members
     * @param $date
     */
    /*
    public function teamChangeOrder($equipments, $teamId, $members, $date)
    {
        if ($equipments) {
            ScheduleTeamsEquipment::updateEquipmentsOrder($teamId, $equipments);
        }
        if ($members) {
            ScheduleTeamsMembers::updateMembersOrder($teamId, $members);
        }

        ScheduleUpdates::insertUpdate(['update_time' => $date]);
    }
    */
    /**
     * @param $team_id
     * @param $field
     * @param $note
     * @param $date
     * @return bool
     * @throws Exception
     */
    /*
    public function teamSaveNote($team_id, $field, $note, $date)
    {
        return ScheduleTeams::saveData($team_id, [$field => $note], $date, true);
    }
    */
    /**
     * @param $team_id
     * @param $team_amount
     * @param $date
     * @return bool
     * @throws Exception
     */
    /*
    public function teamSaveAmount($team_id, $team_amount, $date)
    {
        return ScheduleTeams::saveData($team_id, ['team_amount' => $team_amount], $date);
    }
    */
    /**
     * @param $team_id
     * @param $leader_id
     * @param $date
     * @return bool
     * @throws Exception
     */
    /*
    public function teamChangeLeader($team_id, $leader_id, $date)
    {
        return ScheduleTeams::saveData($team_id, ['team_leader_user_id' => $leader_id], $date);
    }
    */
    /**
     * @param $team_id
     * @param $team_color
     * @param $date
     * @return bool
     * @throws Exception
     */
    /*
    public function teamChangeColor($team_id, $team_color, $date)
    {
        return ScheduleTeams::saveData($team_id, ['team_color' => $team_color], $date);
    }
    */
    /**
     * @param $event_team_id
     * @return string
     */
    /*
    public function getCalculatedTeamAmount($event_team_id)
    {
        $team = ScheduleTeams::getTeams(['team_id' => $event_team_id], 1);
        $dmg = ScheduleEvent::getSumDamageComplain(['team_id' => $event_team_id]);
        $mhrs = 0;

        if($team->team_man_hours) {
            $mhrs = round(($team->team_amount - $dmg->event_damage) / $team->team_man_hours, 2);
        }

        return money($mhrs);
    }
    */
    /**
     * @param $id
     * @param array $data
     */
    /*
    public function updateScheduleData($id, array $data)
    {
        ScheduleEvent::where(['id' => $id])->update($data);
    }
    */
    /**
     * @param $data
     * @param $id
     * @return mixed
     */
    /*
    public function updateSchedule($data, $id)
    {
        //deprecated mdl_safety_pdf_signs - should be create as Eloquent
        $this->CI =& get_instance();
        $this->CI->load->model('mdl_safety_pdf_signs');
        $this->CI->load->model('mdl_schedule');
        $this->CI->load->model('mdl_estimates_orm');
        $this->CI->load->model('mdl_vehicles');
        $this->CI->load->model('mdl_crews_orm');

        $service_ids = [];
        // data for safety PDF sign //
        $spsData = [
            'event_id' => $id,
            'team_id' => $data['event_team_id'],
        ];
        if (!in_array(null, $spsData, true) && !in_array('', $spsData, true)) {
            $this->CI->mdl_safety_pdf_signs->createEmptyTeamSlots($spsData);
        }
        // data for safety PDF sign //
        if (isset($data['event_services'])) {
            $service_ids = json_decode($data['event_services']);
            unset($data['event_services']);
        }

        $oldTeamId = null;
        $event = $this->CI->mdl_schedule->get_events(['schedule.id' => $data['id']], 1);
        $change_workorder_id = (isset($event['event_wo_id']) && $event['event_wo_id'] != $data['event_wo_id']);
        $this->CI->mdl_schedule->delete_event_services(['event_id' => $data['id']]);
        $this->CI->mdl_schedule->save_event($data, true);
        $this->generateScheduleFollowUp($data['id'], true);

        if($event['event_team_id'] != $data['event_team_id']) {
            $amount = $this->CI->mdl_schedule->get_events_amount(['event_team_id' => $event['event_team_id']]);
            $this->CI->mdl_schedule->update_team($event['event_team_id'], ['team_amount' => $amount['event_price']]);
            $team = $this->CI->mdl_schedule->get_teams(['team_id' => $event['event_team_id']], 1);
        }

        foreach ($service_ids as $key => $val) {
            $this->CI->mdl_schedule->insert_event_services(['event_id' => $data['id'], 'service_id' => $val]);
        }

        $eventServices = $this->CI->mdl_schedule->get_event_services(['event_id' => $data['id']]);
        $eventData['total_for_services'] = !$change_workorder_id ? $event['event_price'] : 0;
        $eventData['total_event_time'] = 0;
        $eventData['total_service_time'] = 0;
        $eventData['event_crew'] = null;
        $eventData['event_equipment'] = null;

        foreach ($eventServices as $key => $val) {
            $serv = $this->CI->mdl_estimates_orm->get_full_service_data($val['event_service_id']);

            if (!isset($serv->id)) {
                continue;
            }
            if ($change_workorder_id) {
                $eventData['total_for_services'] += $serv->service_price;
            }
            if (isset($serv->service_time) && $serv->service_time) {
                $eventData['total_event_time'] += $serv->service_time;
                $eventData['total_service_time'] += $serv->service_time * count($serv->crew);
            }
            if (isset($serv->service_travel_time) && $serv->service_travel_time) {
                $eventData['total_event_time'] += $serv->service_travel_time;
                $eventData['total_service_time'] += $serv->service_travel_time * count($serv->crew);
            }
            if (isset($serv->service_disposal_time) && $serv->service_disposal_time) {
                $eventData['total_event_time'] += $serv->service_disposal_time;
                $eventData['total_service_time'] += $serv->service_disposal_time * count($serv->crew);
            }

            $servequipment = $this->CI->mdl_vehicles->get_service_equipment(['equipment_service_id' => $serv->id]);
            $crew = $this->CI->mdl_crews_orm->get_service_crew_in_string(['crew_service_id' => $serv->id]);
            foreach ($servequipment as $jkey => $jvalue) {
                if ($jvalue['item_name']) {
                    $eventData['event_equipment'] .= $jvalue['item_name'];
                    $options = $jvalue['equipment_item_option'] ? implode(' OR ',
                        json_decode($jvalue['equipment_item_option'])) : 'Any';
                    $eventData['event_equipment'] .= ' (' . $options . '), ';
                }

                if ($jvalue['attach_name']) {
                    $eventData['event_equipment'] .= $jvalue['attach_name'];
                    $options = $jvalue['equipment_attach_option'] ? implode(' OR ',
                        json_decode($jvalue['equipment_attach_option'])) : 'Any';
                    $eventData['event_equipment'] .= ' (' . $options . '), ';
                }
            }

            if (isset($crew['crews_names'])) {
                $eventData['event_crew'] .= $crew['crews_names'] . ', ';
            }
            $eventData['event_services'][$serv->id] = $serv->id;
            $eventData['event_services'] = json_encode($eventData['event_services']);
        }
        $eventData['event_equipment'] = rtrim($eventData['event_equipment'], ', ');
        $eventData['event_crew'] = rtrim($eventData['event_crew'], ', ');

        $this->CI->mdl_schedule->save_event(['id' => $data['id'], 'event_price' => $eventData['total_for_services']], true);
        $amount = $this->CI->mdl_schedule->get_events_amount(['event_team_id' => $data['event_team_id']]);
        $this->CI->mdl_schedule->update_team($data['event_team_id'], ['team_amount' => $amount['event_price']]);
        $uid = $this->CI->mdl_schedule->insert_update(['update_time' => $data['event_start']]);

        return $uid;
    }*/

    /**
     * @param $data
     * @param $id
     * @return mixed
     */
    /*
    public function createSchedule($data, $id)
    {
        //deprecated mdl_safety_pdf_signs - should be create as Eloquent
        $this->CI =& get_instance();
        $this->CI->load->model('mdl_safety_pdf_signs');
        $this->CI->load->model('mdl_schedule');
        $this->CI->load->model('mdl_estimates_orm');
        $this->CI->load->model('mdl_vehicles');
        $this->CI->load->model('mdl_crews_orm');

        $service_ids = [];
        // data for safety PDF sign //
        $spsData = [
            'event_id' => $id,
            'team_id' => $data['event_team_id'],
        ];
        if (!in_array(null, $spsData, true) && !in_array('', $spsData, true)) {
            $this->CI->mdl_safety_pdf_signs->createEmptyTeamSlots($spsData);
        }
        // data for safety PDF sign //
        if (isset($data['event_services'])) {
            $service_ids = json_decode($data['event_services']);
            unset($data['event_services']);
        }

        $oldTeamId = null;
        $event = $this->CI->mdl_schedule->get_events(['schedule.id' => $data['id']], 1);
        $change_workorder_id = (isset($event['event_wo_id']) && $event['event_wo_id'] != $data['event_wo_id']);
        $this->CI->mdl_schedule->delete_event_services(['event_id' => $data['id']]);
        $this->CI->mdl_schedule->save_event($data, false);
        $this->generateScheduleFollowUp($data['id'], true);

        foreach ($service_ids as $key => $val) {
            $this->CI->mdl_schedule->insert_event_services(['event_id' => $data['id'], 'service_id' => $val]);
        }

        $eventServices = $this->CI->mdl_schedule->get_event_services(['event_id' => $data['id']]);
        $eventData['total_for_services'] = 0;
        $eventData['total_event_time'] = 0;
        $eventData['total_service_time'] = 0;
        $eventData['event_crew'] = null;
        $eventData['event_equipment'] = null;

        foreach ($eventServices as $key => $val) {
            $serv = $this->CI->mdl_estimates_orm->get_full_service_data($val['event_service_id']);

            if (!isset($serv->id)) {
                continue;
            }
            if ($change_workorder_id) {
                $eventData['total_for_services'] += $serv->service_price;
            }
            if (isset($serv->service_time) && $serv->service_time) {
                $eventData['total_event_time'] += $serv->service_time;
                $eventData['total_service_time'] += $serv->service_time * count($serv->crew);
            }
            if (isset($serv->service_travel_time) && $serv->service_travel_time) {
                $eventData['total_event_time'] += $serv->service_travel_time;
                $eventData['total_service_time'] += $serv->service_travel_time * count($serv->crew);
            }
            if (isset($serv->service_disposal_time) && $serv->service_disposal_time) {
                $eventData['total_event_time'] += $serv->service_disposal_time;
                $eventData['total_service_time'] += $serv->service_disposal_time * count($serv->crew);
            }

            $servequipment = $this->CI->mdl_vehicles->get_service_equipment(['equipment_service_id' => $serv->id]);
            $crew = $this->CI->mdl_crews_orm->get_service_crew_in_string(['crew_service_id' => $serv->id]);
            foreach ($servequipment as $jkey => $jvalue) {
                if ($jvalue['item_name']) {
                    $eventData['event_equipment'] .= $jvalue['item_name'];
                    $options = $jvalue['equipment_item_option'] ? implode(' OR ',
                        json_decode($jvalue['equipment_item_option'])) : 'Any';
                    $eventData['event_equipment'] .= ' (' . $options . '), ';
                }

                if ($jvalue['attach_name']) {
                    $eventData['event_equipment'] .= $jvalue['attach_name'];
                    $options = $jvalue['equipment_attach_option'] ? implode(' OR ',
                        json_decode($jvalue['equipment_attach_option'])) : 'Any';
                    $eventData['event_equipment'] .= ' (' . $options . '), ';
                }
            }

            if (isset($crew['crews_names'])) {
                $eventData['event_crew'] .= $crew['crews_names'] . ', ';
            }
            $eventData['event_services'][$serv->id] = $serv->id;
            $eventData['event_services'] = json_encode($eventData['event_services']);
        }
        $eventData['event_equipment'] = rtrim($eventData['event_equipment'], ', ');
        $eventData['event_crew'] = rtrim($eventData['event_crew'], ', ');

        $this->CI->mdl_schedule->save_event(['id' => $data['id'], 'event_price' => $eventData['total_for_services']], true);
        $amount = $this->CI->mdl_schedule->get_events_amount(['event_team_id' => $data['event_team_id']]);
        $this->CI->mdl_schedule->update_team($data['event_team_id'], ['team_amount' => $amount['event_price']]);
        $uid = $this->CI->mdl_schedule->insert_update(['update_time' => $data['event_start']]);

        return $uid;
    }*/


    /**
     * @param $event_id
     * @param $event_team_id
     */
    /*
    public function deleteSchedule($event_id, $event_team_id)
    {
        $this->CI =& get_instance();
        $this->CI->load->model('mdl_schedule');

        $this->CI->mdl_schedule->delete_event($event_id);
        $this->CI->mdl_schedule->delete_event_services(['event_id' => $event_id]);
        $amount = $this->CI->mdl_schedule->get_events_amount(['event_team_id' => $event_team_id]);
        $this->CI->mdl_schedule->update_team($event_team_id, array('team_amount' => $amount['event_price']));

        $this->generateScheduleFollowUp($event_id, FALSE);
    }
    */
    /**
     * @param $eventId
     * @param $action
     */
    function generateScheduleFollowUp($eventId, $action)
    {
        $this->CI =& get_instance();
        $this->CI->load->model('mdl_followup_settings');
        $this->CI->load->model('mdl_followups');
        $this->CI->load->model('mdl_user');
        $fsSettings = $this->CI->mdl_followup_settings->get_many_by(['fs_disabled' => '0', 'fs_table' => 'schedule']);
        $this->CI->mdl_followups->delete_by(['fu_item_id' => $eventId]);

        $fsConfig = $this->CI->config->item('followup_modules')['schedule'];
        if($action && $fsSettings)
        {
            foreach ($fsSettings as $key => $value) {
                $statuses = json_decode($value->fs_statuses);
                $data = $this->CI->mdl_schedule->get_followup(['schedule.id' => $eventId], $statuses);

                if(!empty($data))
                {
                    $variables = $this->CI->mdl_schedule->get_followup_variables($eventId);
                    $fuData = [
                        'fu_fs_id' => $value->fs_id,
                        'fu_date' => date('Y-m-d', ($data[0]['this_status_date'] - $value->fs_time_periodicity*3600)),
                        'fu_module_name' => $value->fs_table,
                        'fu_action_name' => $fsConfig['action_name'],
                        'fu_client_id' => $data[0]['client_id'],
                        'fu_item_id' => $data[0][$fsConfig['id_field_name']],
                        'fu_estimator_id' => $data[0]['estimator_id'],
                        'fu_status' => 'new',
                        'fu_time' => date('H:i:s', ($data[0]['this_status_date'] - $value->fs_time_periodicity*3600)),
                        'fu_variables' => json_encode($variables)
                    ];
                    $this->CI->mdl_followups->insert($fuData);
                    $variables = $this->CI->mdl_user->get_followup_variables($eventId);

                    $fuData = [
                        'fu_fs_id' => $value->fs_id,
                        'fu_date' => date('Y-m-d', ($data[0]['this_status_date'] - $value->fs_time_periodicity*3600)),
                        'fu_module_name' => 'users',
                        'fu_action_name' => $fsConfig['action_name'],
                        'fu_client_id' => $data[0]['client_id'],
                        'fu_item_id' => $data[0][$fsConfig['id_field_name']],
                        'fu_estimator_id' => $data[0]['estimator_id'],
                        'fu_status' => 'new',
                        'fu_time' => date('H:i:s', ($data[0]['this_status_date'] - $value->fs_time_periodicity*3600)),
                        'fu_variables' => json_encode($variables)
                    ];
                    $this->CI->mdl_followups->insert($fuData);
                }
            }
        }
    }
}