<?php

//start payroll week from sunday
if (!function_exists("week_sunday_monday")) {
    function week_sunday_monday($what = null)
    {        
        if($what == 'sun'){
            $check_true = 0;
            $day_shift = "-1";            
        } else if($what == 'mon') {
            $check_true = 1;
            $day_shift = "+1"; 
        } else {
            return;
        }
        
        $ci = &get_instance();
        
        $ci->db->select("payroll_start_date");
		$ci->db->from('payroll');
		$ci->db->limit(1);		
		$query = $ci->db->get();
        
        if($query->num_rows() > 0) {
			$db_date = $query->row()->payroll_start_date;
            $week_day = date("w", strtotime($db_date));
            if($week_day == $check_true){
               echo 'Already starts from this day';
               return;
            } else {                
                $end = false;
                $start = 0;
                $limit = 50;
                while(!$end){
                    $ci->db->select("*");
                    $ci->db->from('payroll');
                    $ci->db->limit($limit, $start);
                    $query = $ci->db->get();
                                        
                    if($query->num_rows() > 0){
                        $start = $start + $limit;
                        
                        $dates = $query->result();                        
                        $update_arr = [];
                        
                        foreach($dates as $date){
                            
                            $subarray = [
                                'payroll_id' =>  $date->payroll_id,
                                'payroll_start_date' => date('Y-m-d', strtotime($day_shift.' days', strtotime($date->payroll_start_date))),
                                'payroll_end_date' => date('Y-m-d', strtotime($day_shift.' days', strtotime($date->payroll_end_date)))
                            ];
                            $update_arr[] = $subarray;                            
                        }
                        $ci->db->update_batch('payroll', $update_arr, 'payroll_id');
                        
                    } else {
                        $end = true;
                        echo 'Dates Shifted <br>';
                        //update all rows for employee_worked where it's connected by date of payroll is not corresponding to the right payroll id                              
                        
                        $sql = "UPDATE employee_worked ew " .
                        "JOIN payroll pl ON ew.worked_date >= pl.payroll_start_date AND ew.worked_date <= pl.payroll_end_date " .
                        "SET ew.worked_payroll_id = pl.payroll_id WHERE ew.worked_date >= pl.payroll_start_date AND ew.worked_date <= pl.payroll_end_date;";
                        $res = $ci->db->query($sql);
                        
                        echo 'Employee worked - Updated';
                        return;
                    }
                }
                
            }
		} else {
            echo 'The table is empty';
			return;
		}
        
    }
}
