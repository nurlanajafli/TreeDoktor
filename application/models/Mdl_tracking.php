<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Mdl_tracking extends MY_Model
{

	function __construct()
	{
		parent::__construct();

		$this->table = 'tracking';
		$this->primary_key = "id";

	}

	function get_tracking($wdata, $event)
	{
		$this->db->where($wdata);
		$parkings = $this->db->get('tracking_parking')->result_array();
		$stops = array();
		$num = 0;
		foreach($parkings as $key => $parking)
		{
			if(isset($stops[$num - 1]))
			{
				$startTime = strtotime($parking['tracking_start_time']);
				$prevStartTime = strtotime($stops[$num - 1]['end_date']);
				if($startTime - $prevStartTime < 300)
				{
					$stops[$num - 1]['end_date'] = $parking['tracking_end_time'];
					$stops[$num - 1]['stop_time'] += $parking['tracking_period'];
					continue;
				}
			}
			$stops[$num]['vehicle'] = $parking['tracking_device_name'];
			$stops[$num]['start_date'] = $parking['tracking_start_time'];
			$stops[$num]['end_date'] = $parking['tracking_end_time'];
			$stops[$num]['stop_time'] = $parking['tracking_period'];
			$num++;
		}
		foreach($stops as $key => $stop)
		{
			$prevEvent = $this->mdl_schedule->get_events(array('event_team_id' => $event['team_id'], 'event_start < ' => $event['event_start']), 1);

			$newwdata['tracking_device_name'] = $stop['vehicle'];
			$newwdata['tracking_start_time >='] = date('Y-m-d 00:00:00', $event['event_start']);
			$newwdata['tracking_start_time <='] = date('Y-m-d 23:59:59', $event['event_start']);

			if($prevEvent && !empty($prevEvent))
			{
				$data['estimate_data'] = $this->mdl_estimates->find_by_id($prevEvent['estimate_id']);
				$data['lead_data'] = $this->mdl_leads->find_by_id($data['estimate_data']->lead_id);
				$range = 0.4; //Radius in kilometers; ~60-100 meters diameter. ///0.019 CHANGED BY GLEBA RUSLAN
				$lat_range = $range / 69.172;
				$lon_range = abs($range / (cos($data['lead_data']->latitude) * 69.172));
				$data['min_lat'] = number_format($data['lead_data']->latitude - $lat_range, "6", ".", "");
				$data['max_lat'] = number_format($data['lead_data']->latitude + $lat_range, "6", ".", "");
				$data['min_lon'] = number_format($data['lead_data']->longitude - $lon_range, "6", ".", "");
				$data['max_lon'] = number_format($data['lead_data']->longitude + $lon_range, "6", ".", "");

				$newwdata['tracking_lat >='] = $data['min_lat'];
				$newwdata['tracking_lon >='] = $data['min_lon'];
				$newwdata['tracking_lat <='] = $data['max_lat'];
				$newwdata['tracking_lon <='] = $data['max_lon'];
				$this->db->where($newwdata);
				$newParkings = $this->db->get('tracking_parking')->result_array();
				if($newParkings && !empty($newParkings))
					$stops[$key]['leaving_date'] = $newParkings[count($newParkings) - 1]['tracking_end_time'];//countOk
				if(isset($stops[$key - 1]) && ($stops[$key]['leaving_date'] == $stops[$key - 1]['leaving_date'] || $stops[$key]['leaving_date'] < $stops[$key - 1]['end_date']))
					$stops[$key]['leaving_date'] = $stops[$key - 1]['end_date'];
			}
			else
			{
				$this->db->where($newwdata);
				$newParkings = $this->db->get('tracking_parking')->result_array();
				if($newParkings && !empty($newParkings))
					$stops[$key]['leaving_date'] = $newParkings[0]['tracking_end_time'];
				if(isset($stops[$key - 1]) && ($stops[$key]['leaving_date'] == $stops[$key - 1]['leaving_date'] || $stops[$key]['leaving_date'] < $stops[$key - 1]['end_date']))
					$stops[$key]['leaving_date'] = $stops[$key - 1]['end_date'];
			}


			$leavingTime = strtotime($stops[$key]['leaving_date']);
			$startTime = strtotime($stops[$key]['start_date']);
			$stops[$key]['way_time'] = intval(($startTime - $leavingTime) / 60 / 60) . ':' . str_pad((($startTime - $leavingTime) / 60 % 60), 2, '0', STR_PAD_LEFT);
			$stops[$key]['stop_time'] = intval($stop['stop_time'] / 60) . ':' . str_pad(($stop['stop_time'] % 60), 2, '0', STR_PAD_LEFT);
		}
		return $stops;
	}

	/*function get_tracking($wdata = array(), $groupBy = FALSE, $limit = FALSE)
	{
		if (count($wdata))
			$this->db->where($wdata);
		if (count($orWdata))
			$this->db->or_where($orWdata);
		if ($groupBy)
			$this->db->group_by($groupBy);
		if ($limit)
			$this->db->limit($limit);
		$this->db->order_by('GPS_time ASC');
		$query = $this->db->get('tracking');

		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return false;
		}
	}*/

	function client_tracking_records($estimate_id)
	{
		$this->load->model('estimates/mdl_estimates', 'mdl_estimates');
		$this->load->model('invoices/mdl_invoices', 'mdl_invoices');
		$this->load->model('leads/mdl_leads', 'mdl_leads');
		$estimate = $this->mdl_estimates->find_by_id($estimate_id);
		$invoice = $this->mdl_invoices->getEstimatedData($estimate_id);

		$lead = $this->mdl_leads->find_by_id($estimate->lead_id);
		$wdata['GPS_time >'] = $lead->lead_date_created . ' 00:00:00';

		$results = FALSE;

		$range = 0.3;
		$lat_range = $range / 69.172;
		$lon_range = abs($range / (cos($lead->latitude) * 69.172));
		$min_lat = number_format($lead->latitude - $lat_range, "6", ".", "");
		$max_lat = number_format($lead->latitude + $lat_range, "6", ".", "");
		$min_lon = number_format($lead->longitude - $lon_range, "6", ".", "");
		$max_lon = number_format($lead->longitude + $lon_range, "6", ".", "");

		$wdata['tracking.latitude <='] = $max_lat;
		$wdata['tracking.latitude >='] = $min_lat;
		$wdata['tracking.longitude <='] = $max_lon;
		$wdata['tracking.longitude >='] = $min_lon;

		$where = NULL;

		foreach ($wdata as $key => $val)
			$where .= $key . " '" . $val . "' AND ";
		$where = rtrim($where, ' AND ');


		$cars = $this->get_tracking($where, 'vehicle');

		if (!$cars)
			return FALSE;

		$maxTime = NULL;

		foreach ($cars as $car) {
			$gpsTime = $lead->lead_date_created . ' 00:00:00';
			if ($invoice)
				$maxTime = $invoice->date_created . ' 00:00:00';
			$in = TRUE;
			$minStopMins = 15;
			if ($car['vehicle'] == 'MC Smart')
				$minStopMins = 5;
			while ($in) {
				$wdata = array();
				$where = NULL;

				$wdata['vehicle ='] = $car['vehicle'];
				$wdata['tracking.latitude <='] = $max_lat;
				$wdata['tracking.latitude >='] = $min_lat;
				$wdata['tracking.longitude <='] = $max_lon;
				$wdata['tracking.longitude >='] = $min_lon;
				$wdata['GPS_time >'] = $gpsTime;
				if ($maxTime)
					$wdata['GPS_time <'] = $maxTime;

				foreach ($wdata as $key => $val)
					$where .= $key . " '" . $val . "' AND ";
				$where = rtrim($where, ' AND ');

				$in = $this->get_tracking($where, FALSE, 1);

				if (!$in)
					break;
				$wdata = array();
				$where = NULL;

				$wdata['tracking.latitude >'] = $max_lat;
				$wdata['tracking.latitude <'] = $min_lat;
				$wdata['tracking.longitude >'] = $max_lon;
				$wdata['tracking.longitude <'] = $min_lon;

				$gpsTime = $in[0]['GPS_time'];

				$where .= "GPS_time > '" . $gpsTime . "' AND vehicle = '" . $car['vehicle'] . "' AND (";
				foreach ($wdata as $key => $val) {
					$where .= $key . " '" . $val . "' OR ";
				}
				$where = rtrim($where, ' OR ') . ")";

				$out = $this->get_tracking($where, FALSE, 1);

				$gpsTime = $out[0]['GPS_time'];

				if ((strtotime($out[0]['GPS_time']) - strtotime($in[0]['GPS_time'])) >= ($minStopMins * 60)) {
					$leaving_date = NULL;
					$way_time = NULL;
					/*$this->db->where('vehicle', $car['vehicle']);
					$this->db->where('GPS_time <', $in[0]['GPS_time']);
					$this->db->where('GPS_time >', date('Y-m-d 00:00:00', strtotime($in[0]['GPS_time'])));
					$this->db->join('leads', 'tracking.longitude = tracking.longitude AND tracking.longitude <= ' . $max_lon . ' AND tracking.longitude >= ' . $min_lon . ' AND tracking.latitude <= ' . $max_lat . ' AND tracking.latitude >=' . $min_lat);
					//$this->db->order_by('GPS_time DESC'); FOR OPTIMIZE QUERY USE array_reverse

					$points = $this->db->get('tracking')->result_array();
					$points = array_reverse($points);

					$stop = 0;
					$speedSum = 0;
					$stopTime = NULL;
					$leaving_date = FALSE;
					foreach($points as $number => $point)
					{
						if($point['speed'] < 5)
						{
							if(!$stop)
								$stopTime = strtotime($point['GPS_time']);
							$stop++;
							$speedSum += $point['speed'];
							if($speedSum < 5 && (($stopTime - strtotime($point['GPS_time'])) > ($minStopMins * 60)))
							{
								$leaving_date = date('Y-m-d H:i:s', $stopTime);
								$way_time = str_pad(intval((strtotime($in[0]['GPS_time']) - $stopTime) / 3600), 2, '0', STR_PAD_LEFT);
								$way_time .= date(':i:s', strtotime($in[0]['GPS_time']) - $stopTime);
								break;
							}
						}
						else
						{
							$stop = 0;
							$speedSum = 0;
						}
					}
					if(!$leaving_date)
					{
						$this->db->where('vehicle', $car['vehicle']);
						$this->db->where('GPS_time >', date('Y-m-d 00:00:00', strtotime($in[0]['GPS_time'])));
						$this->db->where('speed >', 10);
						$this->db->limit(1);
						$fromOffice = $this->db->get('tracking')->row_array();
						$stopTime = strtotime($fromOffice['GPS_time']);
						$leaving_date = date('Y-m-d H:i:s', $stopTime);
						$way_time = str_pad(intval((strtotime($in[0]['GPS_time']) - $stopTime) / 3600), 2, '0', STR_PAD_LEFT);
						$way_time .= date(':i:s', strtotime($in[0]['GPS_time']) - $stopTime);
					}*/
					$results[] = array(
						'start_date' => $in[0]['GPS_time'],
						'end_date' => $out[0]['GPS_time'],
						'vehicle' => $car['vehicle'],
						'stop_time' => str_pad(intval((strtotime($out[0]['GPS_time']) - strtotime($in[0]['GPS_time'])) / 3600), 2, '0', STR_PAD_LEFT) . date(':i:s', strtotime($out[0]['GPS_time']) - strtotime($in[0]['GPS_time'])),
						'leaving_date' => $leaving_date,
						'way_time' => $way_time
					);
				}

				/*echo "<table>";
				foreach($in as $rec)
				{
					echo '<tr><td>'.$rec['GPS_time'].'</td><td></td></tr>';
				}
				foreach($out as $rec)
				{
					echo '<tr><td>'.$rec['GPS_time'].'</td><td></td></tr>';
				}
				echo "</table>";
				echo $car['vehicle'];
				echo "<br><br>";*/
			}
		}
		//echo "<pre>";
		//var_dump($results);
		//die;


		//echo $this->db->last_query();
		//var_dump($wdata);


		/*		if($tracking_records)
				{
					$date = NULL;
					$vehicle = NULL;
					foreach($tracking_records as $key => $rec)
					{
						$time_key = NULL;
						if($date != date('Ymd', strtotime($rec['GPS_time'])))
						{
							$date = date('Ymd', strtotime($rec['GPS_time']));
							$time_key = 'start_date';
						}

						if($vehicle != $rec['vehicle'])
						{
							$vehicle = $rec['vehicle'];
							$time_key = 'start_date';
						}

						$minStopMins = 15;
						if($vehicle == 'MC Smart')
							$minStopMins = 5;

						if($time_key && !isset($results[$vehicle . $date]['start_date']))
						{
							$results[$vehicle . $date][$time_key] = $rec['GPS_time'];
							$results[$vehicle . $date]['vehicle'] = $vehicle;


							$this->db->where('vehicle', $vehicle);
							$this->db->where('GPS_time <', $results[$vehicle . $date][$time_key]);
							$this->db->where('GPS_time >', date('Y-m-d 00:00:00', strtotime($results[$vehicle . $date][$time_key])));
							$this->db->join('leads', 'max_lon = max_lon AND tracking.longitude <= max_lon AND tracking.longitude >= min_lon AND tracking.latitude <= max_lat AND tracking.latitude >= min_lat');
							//$this->db->order_by('GPS_time DESC'); FOR OPTIMIZE QUERY USE array_reverse

							$points = $this->db->get('tracking')->result_array();
							$points = array_reverse($points);

							$stop = 0;
							$speedSum = 0;
							$stopTime = NULL;
							foreach($points as $number => $point)
							{
								if($point['speed'] < 5)
								{
									if(!$stop)
										$stopTime = strtotime($point['GPS_time']);
									$stop++;
									$speedSum += $point['speed'];
									if($speedSum < 5 && (($stopTime - strtotime($point['GPS_time'])) > ($minStopMins * 60)))
									{
										$results[$vehicle . $date]['leaving_date'] = date('Y-m-d H:i:s', $stopTime);
										$results[$vehicle . $date]['way_time'] = str_pad(intval((strtotime($results[$vehicle . $date][$time_key]) - $stopTime) / 3600), 2, '0', STR_PAD_LEFT);
										$results[$vehicle . $date]['way_time'] .= date(':i:s', strtotime($results[$vehicle . $date][$time_key]) - $stopTime);
										break;
									}
								}
								else
								{
									$stop = 0;
									$speedSum = 0;
								}
							}
							if(empty($results[$vehicle . $date]['leaving_date']))
							{
								$this->db->where('vehicle', $vehicle);
								$this->db->where('GPS_time >', date('Y-m-d 00:00:00', strtotime($results[$vehicle . $date][$time_key])));
								$this->db->where('speed >', 10);
								$this->db->limit(1);
								$fromOffice = $this->db->get('tracking')->row_array();
								$stopTime = strtotime($fromOffice['GPS_time']);
								$results[$vehicle . $date]['leaving_date'] = date('Y-m-d H:i:s', $stopTime);
								$results[$vehicle . $date]['way_time'] = str_pad(intval((strtotime($results[$vehicle . $date][$time_key]) - $stopTime) / 3600), 2, '0', STR_PAD_LEFT);
								$results[$vehicle . $date]['way_time'] .= date(':i:s', strtotime($results[$vehicle . $date][$time_key]) - $stopTime);
							}
						}
						if((isset($tracking_records[$key + 1]) && $tracking_records[$key + 1]['vehicle'] != $vehicle)
							|| (isset($tracking_records[$key + 1]) && date('Ymd', strtotime($tracking_records[$key + 1]['GPS_time'])) != $date)
							|| !isset($tracking_records[$key + 1])
							|| isset($results[$vehicle . $date]['start_date'])
						)
							$time_key = 'end_date';

						if($time_key)
						{
							$results[$vehicle . $date][$time_key] = $rec['GPS_time'];

							if($time_key == 'end_date')
							{
								$results[$vehicle . $date]['stop_time'] = str_pad(intval((strtotime($results[$vehicle . $date]['end_date']) - strtotime($results[$vehicle . $date]['start_date'])) / 3600), 2, '0', STR_PAD_LEFT);
								$results[$vehicle . $date]['stop_time'] .= date(':i:s', strtotime($results[$vehicle . $date]['end_date']) - strtotime($results[$vehicle . $date]['start_date']));
							}
						}
					}
				}
		*/
		return $results;
	}
}
