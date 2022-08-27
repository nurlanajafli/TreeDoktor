
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";



CREATE TABLE IF NOT EXISTS `administration` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `date_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `bonuses_types` (
  `bonus_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `bonus_type_name` varchar(255) DEFAULT NULL,
  `bonus_type_description` text,
  `bonus_type_amount` int(11) DEFAULT NULL,
  PRIMARY KEY (`bonus_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `chat` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `from` varchar(255) NOT NULL DEFAULT '',
  `to` varchar(255) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `sent` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `recd` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `from_name` varchar(100) NOT NULL,
  `to_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fromto` (`from`,`to`),
  KEY `from` (`from`),
  KEY `to` (`to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `clients` (
  `client_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_date_created` date NOT NULL,
  `client_maker` int(11) DEFAULT NULL,
  `client_date_modified` date default null null,
  `client_name` varchar(100) NOT NULL,
  `client_type` varchar(20) NOT NULL,
  `client_contact` varchar(50) NOT NULL,
  `client_main_intersection` varchar(50) NOT NULL,
  `client_address` varchar(50) NOT NULL,
  `client_city` varchar(50) NOT NULL,
  `client_state` varchar(50) NOT NULL,
  `client_zip` varchar(15) NOT NULL,
  `client_country` varchar(50) NOT NULL DEFAULT 'Canada',
  `client_phone` varchar(30) NOT NULL,
  `client_mobile` varchar(30) NOT NULL,
  `client_fax` varchar(30) NOT NULL,
  `client_email` varchar(100) NOT NULL,
  `client_email2` varchar(255) DEFAULT NULL,
  `client_web` varchar(100) NOT NULL,
  `client_status` int(1) NOT NULL DEFAULT '1',
  `client_intake_notes` longtext,
  `client_source` varchar(50) NOT NULL,
  `client_referred_by` varchar(255) DEFAULT NULL,
  `client_address_check` enum('0','1') DEFAULT '0',
  `client_address2` varchar(50) DEFAULT NULL,
  `client_main_intersection2` varchar(50) DEFAULT NULL,
  `client_city2` varchar(50) DEFAULT NULL,
  `client_state2` varchar(50) DEFAULT NULL,
  `client_zip2` varchar(50) DEFAULT NULL,
  `client_cc_name` varchar(255) DEFAULT NULL,
  `client_cc_type` enum('visa','mc') DEFAULT NULL,
  `client_cc_exp_month` tinyint(2) DEFAULT NULL,
  `client_cc_exp_year` int(4) DEFAULT NULL,
  `client_cc_cvv` varchar(10) DEFAULT NULL,
  `client_cc_number` longtext,
  `client_promo_code` varchar(20) DEFAULT NULL,
  `client_unsubsribed` tinyint(1) NOT NULL DEFAULT '0',
  `client_rating` int(11) NOT NULL DEFAULT '0',
  `client_email2_check` tinyint(4) DEFAULT NULL,
  `client_email_check` tinyint(4) DEFAULT NULL,
  `client_unsubscribe` tinyint(1) DEFAULT NULL,
  `client_is_refferal` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`client_id`),
  KEY `client_email` (`client_email`),
  KEY `client_status` (`client_status`),
  KEY `client_name` (`client_name`),
  KEY `clint_address` (`client_address`),
  KEY `gsearch` (`client_name`,`client_phone`,`client_contact`,`client_email`,`client_id`,`client_promo_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `clients_calls` (
  `call_id` int(11) NOT NULL AUTO_INCREMENT,
  `call_type` enum('taskrouter','dialer') NOT NULL,
  `call_disabled` tinyint(1) DEFAULT '0',
  `call_from` varchar(255) DEFAULT NULL,
  `call_to` varchar(255) DEFAULT NULL,
  `call_client_id` int(11) DEFAULT NULL,
  `call_user_id` int(11) DEFAULT NULL,
  `call_route` tinyint(1) NOT NULL DEFAULT '0',
  `call_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `call_duration` int(11) NOT NULL DEFAULT '0',
  `call_voice` varchar(255) DEFAULT NULL,
  `call_twilio_sid` varchar(50) DEFAULT NULL,
  `call_complete` tinyint(1) NOT NULL DEFAULT '0',
  `call_workspace_sid` enum('WS5107ffee9f1d1715ed06b8da32361790','WSd5ddf64bb22aa165abac6c6434764dec') DEFAULT NULL,
  `call_new_voicemail` tinyint(1) NOT NULL DEFAULT '0',
  `call_text` text,
  PRIMARY KEY (`call_id`),
  KEY `date` (`call_date`),
  KEY `wsdrv` (`call_workspace_sid`,`call_duration`,`call_route`,`call_voice`),
  KEY `uws` (`call_user_id`,`call_workspace_sid`),
  KEY `workspace` (`call_workspace_sid`),
  KEY `call_twilio_sid` (`call_twilio_sid`(34))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `clients_calls_reservations` (
  `res_id` int(11) NOT NULL AUTO_INCREMENT,
  `res_user_id` int(11) NOT NULL,
  `res_duration` int(11) DEFAULT NULL,
  `res_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `res_call_id` int(11) NOT NULL,
  `res_twilio_sid` varchar(50) DEFAULT NULL,
  `res_call_voice` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`res_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `clients_calls_tasks` (
  `twilio_calls` varchar(255) NOT NULL,
  `twilio_tasks` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `clients_contacts` (
  `cc_id` int(11) NOT NULL AUTO_INCREMENT,
  `cc_client_id` int(11) DEFAULT NULL,
  `cc_title` varchar(255) DEFAULT NULL,
  `cc_name` varchar(255) DEFAULT NULL,
  `cc_phone` varchar(255) DEFAULT NULL,
  `cc_phone_clean` varchar(255) DEFAULT NULL,
  `cc_email` varchar(255) DEFAULT NULL,
  `cc_email_check` tinyint(1) DEFAULT NULL,
  `cc_print` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`cc_id`),
  KEY `client_id` (`cc_client_id`),
  KEY `cc_phone` (`cc_phone`),
  KEY `cc_phone_10` (`cc_phone`(10)),
  KEY `cc_phone_clean` (`cc_phone_clean`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `client_calls_on_hold` (
  `ch_id` int(11) NOT NULL AUTO_INCREMENT,
  `ch_call_twilio_sid` varchar(50) DEFAULT NULL,
  `ch_call_number` varchar(255) DEFAULT NULL,
  `ch_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ch_client_id` int(11) DEFAULT NULL,
  `ch_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`ch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `client_notes` (
  `client_note_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `client_note_date` datetime NOT NULL,
  `client_note` longtext NOT NULL,
  `client_note_type` enum('info','system','contact','attachment','email') NOT NULL DEFAULT 'system',
  `author` varchar(50) NOT NULL,
  `robot` enum('yes','no') NOT NULL DEFAULT 'yes',
  `client_note_top` tinyint(1) DEFAULT NULL,
  `lead_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`client_note_id`),
  KEY `client_id` (`client_id`),
  KEY `user_id` (`author`),
  KEY `client_user` (`client_id`,`author`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `client_papers` (
  `cp_id` int(11) NOT NULL AUTO_INCREMENT,
  `cp_client_id` int(11) NOT NULL,
  `cp_user_id` int(11) NOT NULL,
  `cp_text` text NOT NULL,
  `cp_date` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`cp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `client_payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `estimate_id` int(11) NOT NULL,
  `payment_method` enum('cash','cc','check','dc','etransfer') NOT NULL,
  `payment_type` enum('deposit','invoice') NOT NULL,
  `payment_date` int(11) NOT NULL,
  `payment_amount` double NOT NULL,
  `payment_file` varchar(255) DEFAULT NULL,
  `payment_checked` tinyint(1) NOT NULL DEFAULT '0',
  `payment_author` int(11) DEFAULT NULL,
  `payment_account` tinyint(1) DEFAULT NULL,
  `payment_trans_id` int(11) DEFAULT NULL,
  `payment_alarm` int(11) DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `estimate_id` (`estimate_id`),
  KEY `payment_author` (`payment_author`),
  KEY `payment_account` (`payment_account`),
  KEY `author_account` (`payment_author`,`payment_account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `client_tasks` (
  `task_id` int(11) NOT NULL AUTO_INCREMENT,
  `task_author_id` int(11) DEFAULT NULL,
  `task_address` varchar(50) NOT NULL,
  `task_city` varchar(50) NOT NULL,
  `task_state` varchar(50) NOT NULL,
  `task_zip` varchar(20) NOT NULL,
  `task_category` int(11) DEFAULT NULL,
  `task_status` enum('new','canceled','done') NOT NULL DEFAULT 'new',
  `task_client_id` int(11) DEFAULT NULL,
  `task_date_created` date NOT NULL,
  `task_desc` text,
  `task_latitude` double DEFAULT NULL,
  `task_longitude` double DEFAULT NULL,
  `task_user_id_updated` int(11) DEFAULT NULL,
  `task_date_updated` date NOT NULL,
  `task_no_map` tinyint(1) NOT NULL DEFAULT '0',
  `task_assigned_user` int(11) DEFAULT NULL,
  `task_date` date DEFAULT NULL,
  `task_start` time DEFAULT NULL,
  `task_end` time DEFAULT NULL,
  PRIMARY KEY (`task_id`),
  KEY `task_category` (`task_category`),
  KEY `status` (`task_status`),
  KEY `status_user` (`task_status`,`task_assigned_user`),
  KEY `user_updated` (`task_user_id_updated`),
  KEY `date_updated` (`task_date_updated`),
  KEY `date` (`task_date_created`),
  KEY `client_id` (`task_client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `client_task_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) DEFAULT NULL,
  `category_color` varchar(10) DEFAULT NULL,
  `category_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `contacts` (
  `contact_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `number` varchar(255) NOT NULL,
  PRIMARY KEY (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `crews` (
  `crew_id` int(11) NOT NULL AUTO_INCREMENT,
  `crew_name` varchar(255) DEFAULT NULL,
  `crew_color` varchar(100) DEFAULT NULL,
  `crew_status` tinyint(1) NOT NULL DEFAULT '1',
  `crew_leader` int(11) DEFAULT NULL,
  `crew_weight` int(11) NOT NULL,
  `crew_full_name` varchar(255) DEFAULT NULL,
  `crew_rate` int(11) DEFAULT NULL,
  `crew_priority` int(11) DEFAULT NULL,
  `crew_return_priority` int(11) DEFAULT NULL,
  PRIMARY KEY (`crew_id`),
  KEY `leader` (`crew_leader`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `crews_equipment` (
  `eq_crew_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `eq_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `crews_members` (
  `crew_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `date` int(11) NOT NULL,
  UNIQUE KEY `uniq` (`crew_id`,`employee_id`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `crew_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `crew_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `discounts` (
  `discount_id` int(11) NOT NULL AUTO_INCREMENT,
  `estimate_id` int(11) NOT NULL,
  `discount_amount` float NOT NULL,
  `discount_date` int(11) NOT NULL,
  `discount_percents` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`discount_id`),
  KEY `est_id` (`estimate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `email_templates` (
    `email_template_id` int(11) NOT NULL AUTO_INCREMENT,
    `email_template_title` varchar(255) NOT NULL,
    `email_template_text` text NOT NULL,
    `email_system_template` tinyint(1) NOT NULL DEFAULT '0',
    `email_news_templates` int(1) DEFAULT NULL,
    `system_label` varchar(150) DEFAULT NULL,
    PRIMARY KEY (`email_template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `employees` (
  `employee_id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_name` varchar(300) DEFAULT NULL,
  `emp_email` varchar(150) NOT NULL,
  `emp_username` varchar(100) NOT NULL,
  `emp_pass` varchar(50) NOT NULL,
  `emp_position` varchar(100) NOT NULL,
  `emp_address1` varchar(450) DEFAULT NULL,
  `emp_address2` varchar(450) DEFAULT NULL,
  `emp_city` varchar(300) DEFAULT NULL,
  `emp_state` varchar(300) DEFAULT NULL,
  `emp_phone` varchar(48) DEFAULT NULL,
  `emp_sin` varchar(30) DEFAULT NULL,
  `emp_hourly_rate` float DEFAULT NULL,
  `emp_yearly_rate` float DEFAULT NULL,
  `emp_message_on_account` blob,
  `added_on` datetime DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `emp_feild_worker` enum('1','0') DEFAULT '0',
  `emp_driver` enum('1','0') DEFAULT '0',
  `emp_climber` enum('1','0') DEFAULT '0',
  `emp_ground` tinyint(1) NOT NULL DEFAULT '0',
  `emp_technique` tinyint(1) NOT NULL DEFAULT '0',
  `emp_status` enum('current','temporary','past','on_leave') NOT NULL,
  `emp_start_time` time NOT NULL DEFAULT '00:00:00',
  `emp_type` enum('employee','sub_ta','sub_ca') NOT NULL DEFAULT 'employee',
  `emp_date_hire` date DEFAULT NULL,
  `emp_sex` enum('male','female') DEFAULT NULL,
  `emp_birthday` date DEFAULT NULL,
  `emp_pay_frequency` enum('weekly','be-weekly','monthly') NOT NULL DEFAULT 'weekly',
  `emp_field_estimator` enum('0','1') NOT NULL DEFAULT '0',
  `deductions_state` tinyint(1) NOT NULL DEFAULT '0',
  `deductions_desc` text,
  `deductions_amount` float DEFAULT NULL,
  `emp_user_id` int(11) DEFAULT NULL,
  `emp_no_dayoff` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`employee_id`),
  KEY `emp_email` (`emp_email`),
  KEY `emp_username` (`emp_username`),
  KEY `emp_pass` (`emp_pass`),
  KEY `emp_hourly_rate` (`emp_hourly_rate`),
  KEY `status_id_fieldworker` (`emp_status`,`emp_feild_worker`) USING BTREE,
  KEY `userId` (`emp_user_id`),
  KEY `emp_phone` (`emp_phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `employee_login` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'auto id of employee login data',
  `employee_id` bigint(20) NOT NULL COMMENT 'employee id',
  `login_time` datetime NOT NULL COMMENT 'login time',
  `logout_time` datetime NOT NULL COMMENT 'logout time',
  `login_image` varchar(100) NOT NULL COMMENT 'login image',
  `logout_image` varchar(100) NOT NULL COMMENT 'logout image',
  `time_diff` time NOT NULL COMMENT 'time difference after logout',
  `last_logout` int(1) NOT NULL COMMENT 'flag to check last logout done',
  `employee_hourly_rate` float NOT NULL COMMENT 'employee hourly rate in dollars',
  `total_pay` float NOT NULL COMMENT 'total pay this day',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'record created date',
  `created_ip` varchar(20) NOT NULL COMMENT 'ip address of logout',
  `no_lunch` tinyint(1) NOT NULL DEFAULT '0',
  `login_lat` varchar(255) DEFAULT NULL,
  `login_lon` varchar(255) DEFAULT NULL,
  `logout_lat` varchar(255) DEFAULT NULL,
  `logout_lon` varchar(255) DEFAULT NULL,
  `login_late` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `login_time` (`login_time`),
  KEY `logout_time` (`logout_time`),
  KEY `time_diff` (`time_diff`),
  KEY `employee_hourly_rate` (`employee_hourly_rate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `employee_reports` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `report_emp_id` int(11) NOT NULL,
  `report_user_id` int(11) NOT NULL,
  `report_text` text,
  `report_date` datetime NOT NULL,
  `report_confirm` tinyint(1) DEFAULT '0',
  `est_appointments` int(11) DEFAULT NULL,
  `est_free_estimates` int(11) DEFAULT NULL,
  `est_no_go` int(11) DEFAULT NULL,
  `est_already_done` int(11) DEFAULT NULL,
  `task_construction_arb_report` int(11) DEFAULT NULL,
  `task_regular_arb_report` int(11) DEFAULT NULL,
  `task_exemption` int(11) DEFAULT NULL,
  `task_payment_follow_up` int(11) DEFAULT NULL,
  `task_assessment` int(11) DEFAULT NULL,
  `task_meeting_with_client` int(11) DEFAULT NULL,
  `task_secondary_visit` int(11) DEFAULT NULL,
  `report_comment` text,
  `task_quality_control` int(11) DEFAULT NULL,
  `report_login_id` int(11) DEFAULT NULL,
  `total_tasks` int(11) DEFAULT NULL,
  `total_estimates` int(11) DEFAULT NULL,
  PRIMARY KEY (`report_id`),
  KEY `emp_id` (`report_emp_id`),
  KEY `confirm` (`report_confirm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `employee_worked` (
  `worked_id` int(11) NOT NULL AUTO_INCREMENT,
  `worked_date` date NOT NULL,
  `worked_hours` float DEFAULT '0',
  `worked_lunch` float DEFAULT NULL,
  `worked_hourly_rate` float NOT NULL,
  `worked_bonuses` float NOT NULL DEFAULT '0',
  `worked_employee_id` int(11) NOT NULL,
  `worked_user_id` int(11) NOT NULL,
  `worked_late` tinyint(1) NOT NULL DEFAULT '0',
  `worked_payroll_id` int(11) NOT NULL,
  `worked_start` time DEFAULT NULL,
  `worked_end` time DEFAULT NULL,
  `worked_auto_logout` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`worked_id`),
  KEY `worked_id` (`worked_id`,`worked_employee_id`),
  KEY `payroll_worked_id` (`worked_payroll_id`),
  KEY `worked_employee_id` (`worked_employee_id`),
  KEY `worked_date` (`worked_date`),
  KEY `autologout` (`worked_auto_logout`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `employee_worked_likes` (
  `likes_id` int(11) NOT NULL AUTO_INCREMENT,
  `likes_user_id` int(11) NOT NULL,
  `likes_date` date NOT NULL,
  `likes_type` int(11) NOT NULL,
  PRIMARY KEY (`likes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `employee_worked_productivity` (
  `prod_id` int(11) NOT NULL AUTO_INCREMENT,
  `prod_worked_id` int(11) NOT NULL,
  `prod_per_mh` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`prod_id`),
  UNIQUE KEY `worked_id` (`prod_worked_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `emp_login` (
  `login_id` int(11) NOT NULL AUTO_INCREMENT,
  `login` time DEFAULT NULL,
  `logout` time DEFAULT NULL,
  `login_worked_id` int(11) NOT NULL,
  `login_employee_id` int(11) NOT NULL,
  `login_user_id` int(11) NOT NULL,
  `login_lat` varchar(10) DEFAULT NULL,
  `login_lon` varchar(10) DEFAULT NULL,
  `logout_lat` varchar(10) DEFAULT NULL,
  `logout_lon` varchar(10) DEFAULT NULL,
  `login_date` date NOT NULL,
  `login_image` varchar(50) DEFAULT NULL,
  `logout_image` varchar(50) DEFAULT NULL,
  `login_office` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`login_id`),
  KEY `login_worked_id` (`login_worked_id`),
  KEY `emp_id` (`login_employee_id`),
  KEY `date` (`login_date`),
  KEY `emp_date` (`login_employee_id`,`login_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `equipment_gps_tracker_distance` (
  `egtd_id` int(11) NOT NULL AUTO_INCREMENT,
  `egtd_item_id` int(11) NOT NULL,
  `egtd_date` date NOT NULL,
  `egtd_counter` double DEFAULT NULL,
  PRIMARY KEY (`egtd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `equipment_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `group_date_created` date DEFAULT NULL,
  `group_color` varchar(10) NOT NULL DEFAULT '#ffffff',
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `equipment_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `item_tracker_name` varchar(255) DEFAULT NULL,
  `item_code` varchar(255) DEFAULT NULL,
  `item_serial` varchar(255) DEFAULT NULL,
  `item_description` text,
  `item_date` int(11) DEFAULT NULL,
  `item_schedule` tinyint(1) NOT NULL DEFAULT '0',
  `item_repair` tinyint(1) NOT NULL DEFAULT '0',
  `counter_kilometers` varchar(255) DEFAULT NULL,
  `counter_hours` varchar(255) DEFAULT NULL,
  `item_gps_start_counter` float DEFAULT NULL,
  `item_gps_start_date` date NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `equipment_parts` (
  `part_id` int(11) NOT NULL AUTO_INCREMENT,
  `part_item_id` int(11) NOT NULL,
  `part_name` varchar(255) DEFAULT NULL,
  `part_date` int(11) NOT NULL,
  `part_seller` text,
  `part_price` float DEFAULT NULL,
  PRIMARY KEY (`part_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `equipment_repair_notes` (
  `equipment_note_id` int(11) NOT NULL AUTO_INCREMENT,
  `equipment_repair_id` int(11) NOT NULL,
  `equipment_note_text` text NOT NULL,
  `equipment_note_author` int(11) NOT NULL,
  `equipment_note_date` datetime NOT NULL,
  `equipment_note_item_id` int(11) NOT NULL,
  PRIMARY KEY (`equipment_note_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `equipment_services` (
  `service_id` int(11) NOT NULL AUTO_INCREMENT,
  `service_item_id` int(11) NOT NULL,
  `service_name` varchar(255) DEFAULT NULL,
  `service_date` int(11) DEFAULT NULL,
  `service_next` int(11) DEFAULT NULL,
  `service_periodicity` int(11) DEFAULT NULL,
  `service_description` text,
  `service_status` enum('new','complete') NOT NULL DEFAULT 'new',
  `service_comment` text,
  `service_hrs` float DEFAULT NULL,
  `service_type_id` int(11) DEFAULT NULL,
  `service_next_hrs` int(11) DEFAULT NULL,
  `service_current_hrs` int(11) DEFAULT NULL,
  PRIMARY KEY (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `equipment_services_reports` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `report_service_settings_id` int(11) NOT NULL,
  `report_item_counter_value` varchar(255) DEFAULT NULL,
  `report_date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `report_comment` text,
  `report_hours` varchar(255) DEFAULT NULL,
  `report_service_id` int(11) NOT NULL,
  `report_item_id` int(11) NOT NULL,
  `report_counter_hours_value` varchar(255) DEFAULT NULL,
  `report_counter_kilometers_value` varchar(255) DEFAULT NULL,
  `report_kind` int(11) DEFAULT NULL,
  `report_create_user` int(11) DEFAULT NULL,
  `report_cost` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `equipment_services_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `service_period_months` int(11) DEFAULT NULL,
  `service_period_hours` int(11) DEFAULT NULL,
  `service_period_kilometers` int(11) DEFAULT NULL,
  `service_type_id` int(11) NOT NULL,
  `service_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `service_postpone` timestamp NULL DEFAULT NULL,
  `service_last_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `service_start` timestamp NULL DEFAULT NULL,
  `service_postpone_on` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `equipment_service_types` (
  `equipment_service_id` int(11) NOT NULL AUTO_INCREMENT,
  `equipment_service_type` varchar(255) DEFAULT NULL,
  `equipment_service_desc` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`equipment_service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `equipment_tracker_data` (
  `eq_td_id` int(11) NOT NULL AUTO_INCREMENT,
  `eq_td_code` varchar(255) DEFAULT NULL,
  `eq_td_date` date NOT NULL,
  `eq_td_data` longtext NOT NULL,
  PRIMARY KEY (`eq_td_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `estimates` (
  `estimate_id` int(11) NOT NULL AUTO_INCREMENT,
  `estimate_no` varchar(20) DEFAULT NULL,
  `estimate_balance` decimal(10,2) DEFAULT NULL,
  `estimate_last_contact` int(11) DEFAULT NULL,
  `estimate_count_contact` int(11) DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `lead_id` int(11) NOT NULL,
  `date_created` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `estimate_hst_disabled` tinyint(1) NOT NULL DEFAULT '0',
  `estimate_item_team` text,
  `estimate_item_estimated_time` text,
  `estimate_item_equipment_setup` text,
  `estimate_item_note_crew` text,
  `estimate_item_note_estimate` text,
  `estimate_item_note_payment` text,
  `arborist` enum('yes','no') NOT NULL DEFAULT 'no',
  `bucket_truck_operator` enum('yes','no') NOT NULL DEFAULT 'no',
  `climber` enum('yes','no') NOT NULL DEFAULT 'no',
  `chipper_operator` enum('yes','no') NOT NULL DEFAULT 'no',
  `groundsmen` enum('yes','no') NOT NULL DEFAULT 'no',
  `bucket_truck` enum('yes','no') NOT NULL DEFAULT 'no',
  `wood_chipper` enum('yes','no') NOT NULL DEFAULT 'no',
  `dump_truck` enum('yes','no') NOT NULL DEFAULT 'no',
  `crane` enum('yes','no') NOT NULL DEFAULT 'no',
  `stump_grinder` enum('yes','no') NOT NULL DEFAULT 'no',
  `brush_disposal` enum('yes','no') NOT NULL DEFAULT 'no',
  `leave_wood` enum('yes','no') NOT NULL DEFAULT 'no',
  `full_cleanup` enum('yes','no') NOT NULL DEFAULT 'no',
  `stump_chips` enum('yes','no') NOT NULL DEFAULT 'no',
  `permit_required` enum('yes','no') NOT NULL DEFAULT 'no',
  `user_id` int(11) DEFAULT NULL,
  `estimate_scheme` longtext,
  `estimate_reason_decline` int(11) DEFAULT NULL,
  `estimate_provided_by` enum('meeting','schedule meeting','printed','phone','email') DEFAULT NULL,
  `estimate_pdf_files` text,
  `unsubscribe` tinyint(1) DEFAULT '0',
  `notification` tinyint(1) DEFAULT '1',
  `paid_by_cc` tinyint(1) NOT NULL DEFAULT '0',
  `estimate_review_date` date NOT NULL,
  `estimate_review_number` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`estimate_id`),
  UNIQUE KEY `no` (`estimate_no`),
  KEY `client_id` (`client_id`),
  KEY `lead` (`lead_id`),
  KEY `user_id` (`user_id`),
  KEY `date` (`date_created`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `estimates_calls` (
  `call_id` int(11) NOT NULL AUTO_INCREMENT,
  `call_estimate_id` int(11) NOT NULL,
  `call_user_id` int(11) NOT NULL,
  `call_time` int(11) NOT NULL,
  `call_message` text NOT NULL,
  UNIQUE KEY `call_id` (`call_id`),
  KEY `est_id` (`call_estimate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `estimates_crews` (
  `estimate_crew_id` int(11) NOT NULL AUTO_INCREMENT,
  `estimate_id` int(11) NOT NULL,
  `crew_id` int(11) NOT NULL,
  `estimate_crew_team` varchar(255) CHARACTER SET latin1 NOT NULL,
  `estimate_crew_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`estimate_crew_id`),
  KEY `est_id` (`estimate_id`),
  KEY `crew_id` (`crew_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `estimates_qa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estimate_id` int(11) NOT NULL,
  `qa_id` int(11) NOT NULL,
  `qa_message` text NOT NULL,
  `qa_user_id` int(11) NOT NULL,
  `qa_date` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `est_id` (`estimate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `estimates_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `estimate_id` int(11) NOT NULL,
  `service_description` text,
  `service_time` float DEFAULT NULL,
  `service_travel_time` float DEFAULT '0',
  `service_price` double DEFAULT NULL,
  `service_priority` tinyint(2) NOT NULL DEFAULT '0',
  `service_size` varchar(255) NOT NULL,
  `service_reason` varchar(255) NOT NULL,
  `service_species` varchar(255) NOT NULL,
  `service_permit` tinyint(1) DEFAULT NULL,
  `service_disposal_time` float NOT NULL,
  `service_wood_chips` int(11) DEFAULT NULL,
  `service_wood_trailers` int(11) DEFAULT NULL,
  `service_front_space` tinyint(1) DEFAULT NULL,
  `service_disposal_brush` tinyint(1) DEFAULT NULL,
  `service_disposal_wood` tinyint(4) DEFAULT NULL,
  `service_cleanup` tinyint(1) DEFAULT NULL,
  `service_access` varchar(255) NOT NULL,
  `service_client_home` tinyint(1) DEFAULT NULL,
  `service_scheme` text,
  `service_exemption` tinyint(1) DEFAULT NULL,
  `service_status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `estimate_id` (`estimate_id`),
  KEY `service_id` (`service_id`),
  KEY `status` (`service_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `estimates_services_crews` (
  `crew_id` int(11) NOT NULL AUTO_INCREMENT,
  `crew_service_id` int(11) NOT NULL,
  `crew_user_id` int(11) NOT NULL,
  `crew_estimate_id` int(11) NOT NULL,
  PRIMARY KEY (`crew_id`),
  KEY `user_id` (`crew_user_id`),
  KEY `service_id` (`crew_service_id`),
  KEY `estimate_id` (`crew_estimate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `estimates_services_equipments` (
  `equipment_id` int(11) NOT NULL AUTO_INCREMENT,
  `equipment_service_id` int(11) NOT NULL,
  `equipment_item_id` int(11) DEFAULT NULL,
  `equipment_estimate_id` int(11) NOT NULL,
  `equipment_attach_id` int(11) DEFAULT NULL,
  `equipment_item_option` text,
  `equipment_attach_option` text,
  `equipment_attach_tool` varchar(255) DEFAULT NULL,
  `equipment_tools_option` text,
  PRIMARY KEY (`equipment_id`),
  UNIQUE KEY `equipment_id` (`equipment_id`),
  KEY `service_id` (`equipment_service_id`),
  KEY `item_id` (`equipment_item_id`),
  KEY `est_id` (`equipment_estimate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `estimates_services_status` (
  `services_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `services_status_name` varchar(255) NOT NULL,
  PRIMARY KEY (`services_status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `estimate_equipment` (
  `eq_id` int(11) NOT NULL AUTO_INCREMENT,
  `eq_name` varchar(255) DEFAULT NULL,
  `eq_weight` int(11) NOT NULL,
  `eq_status` tinyint(1) NOT NULL DEFAULT '1',
  UNIQUE KEY `eq_id` (`eq_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `estimate_reason_status` (
  `reason_id` int(11) NOT NULL AUTO_INCREMENT,
  `reason_name` varchar(255) NOT NULL,
  `reason_est_status_id` int(11) NOT NULL,
  `reason_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`reason_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `estimate_statuses` (
  `est_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `est_status_name` varchar(255) NOT NULL,
  `est_status_active` tinyint(1) DEFAULT '1',
  `est_status_declined` tinyint(1) DEFAULT '0',
  `est_status_default` tinyint(1) DEFAULT '0',
  `est_status_confirmed` tinyint(4) NOT NULL DEFAULT '0',
  `est_status_sent` tinyint(1) NOT NULL DEFAULT '0',
  `est_status_priority` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`est_status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `expenses` (
  `expense_id` int(11) NOT NULL AUTO_INCREMENT,
  `expense_type_id` int(11) NOT NULL,
  `expense_item_id` int(11) DEFAULT NULL,
  `expense_employee_id` int(11) DEFAULT NULL,
  `expense_user_id` int(11) DEFAULT NULL,
  `expense_amount` decimal(10,2) DEFAULT '0.00',
  `expense_hst_amount` decimal(10,2) DEFAULT '0.00',
  `expense_date` int(11) NOT NULL,
  `expense_description` text,
  `expense_created_by` int(11) DEFAULT NULL,
  `expense_create_date` int(11) DEFAULT NULL,
  `expense_file` varchar(255) DEFAULT NULL,
  `expense_payment` enum('Cash','CC','Bank') NOT NULL DEFAULT 'Cash',
  PRIMARY KEY (`expense_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `expense_types` (
  `expense_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `expense_name` varchar(255) DEFAULT NULL,
  `expense_status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`expense_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `expense_types_groups` (
  `expense_type_id` int(11) NOT NULL,
  `expense_type_group_id` int(11) NOT NULL,
  PRIMARY KEY (`expense_type_id`,`expense_type_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `ext_numbers` (
  `extention_id` int(11) NOT NULL AUTO_INCREMENT,
  `extention_key` int(11) NOT NULL,
  `extention_number` varchar(255) NOT NULL,
  `extention_order` int(11) DEFAULT NULL,
  `extention_emergency` tinyint(1) NOT NULL DEFAULT '0',
  `extention_user_id` int(11) NOT NULL,
  PRIMARY KEY (`extention_id`),
  KEY `user_id` (`extention_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `followups` (
  `fu_id` int(11) NOT NULL AUTO_INCREMENT,
  `fu_fs_id` int(11) DEFAULT NULL,
  `fu_date` date DEFAULT NULL,
  `fu_time` time DEFAULT NULL,
  `fu_module_name` varchar(255) DEFAULT NULL,
  `fu_action_name` varchar(255) DEFAULT NULL,
  `fu_client_id` int(11) DEFAULT NULL,
  `fu_item_id` bigint(20) DEFAULT NULL,
  `fu_estimator_id` int(11) DEFAULT NULL,
  `fu_status` enum('new','completed','skipped','canceled','postponed') DEFAULT NULL,
  `fu_comment` text,
  `fu_author` int(11) DEFAULT NULL,
  `fu_variables` text,
  PRIMARY KEY (`fu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `followup_settings` (
  `fs_id` int(11) NOT NULL AUTO_INCREMENT,
  `fs_table` varchar(255) DEFAULT NULL,
  `fs_statuses` text,
  `fs_type` enum('call','email','sms','mail') DEFAULT NULL,
  `fs_client_types` varchar(255) DEFAULT NULL,
  `fs_periodicity` int(11) DEFAULT NULL,
  `fs_every` tinyint(1) NOT NULL DEFAULT '0',
  `fs_time` time DEFAULT NULL,
  `fs_template` text,
  `fs_subject` varchar(255) DEFAULT NULL,
  `fs_pdf` tinyint(1) NOT NULL DEFAULT '0',
  `fs_cron` tinyint(1) NOT NULL,
  `fs_disabled` tinyint(1) NOT NULL DEFAULT '0',
  `fs_table_number` int(1) NOT NULL DEFAULT '0',
  `fs_time_periodicity` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`fs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `geocoding` (
  `address` varchar(255) NOT NULL DEFAULT '',
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  KEY `latitude` (`latitude`),
  KEY `longitude` (`longitude`),
  KEY `address` (`address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(20) NOT NULL,
  `workorder_id` int(11) NOT NULL,
  `estimate_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `in_status` varchar(150) NOT NULL,
  `payment_mode` varchar(255) DEFAULT NULL,
  `payment_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `link_hash` varchar(30) DEFAULT NULL,
  `link_hash_valid_till` datetime DEFAULT NULL,
  `interest_rate` int(50) DEFAULT NULL,
  `interest_status` enum('Yes','No') DEFAULT 'No',
  `date_created` date NOT NULL,
  `overdue_date` date DEFAULT NULL,
  `in_finished_how` text,
  `in_extra_note_crew` text,
  `invoice_like` tinyint(1) DEFAULT NULL,
  `invoice_feedback` text,
  `invoice_pdf_files` text,
  `paid_by_cc` tinyint(4) NOT NULL DEFAULT '0',
  `invoice_notes` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_no` (`invoice_no`),
  UNIQUE KEY `workorder_id` (`workorder_id`),
  UNIQUE KEY `estimate_id` (`estimate_id`),
  KEY `client_id` (`client_id`),
  KEY `in_status` (`in_status`),
  KEY `link_hash` (`link_hash`),
  KEY `link_hash_valid_till` (`link_hash_valid_till`),
  KEY `date` (`date_created`),
  KEY `date_status` (`date_created`,`in_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `invoice_interest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `overdue_date` date DEFAULT NULL,
  `rate` float DEFAULT NULL,
  `nill_rate` enum('1','0') DEFAULT '0',
  `discount` float DEFAULT NULL,
  `interes_cost` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `leads` (
  `lead_id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_no` varchar(50) DEFAULT NULL,
  `lead_author_id` int(11) DEFAULT NULL,
  `lead_address` varchar(50) NOT NULL,
  `lead_city` varchar(50) NOT NULL,
  `lead_state` varchar(50) NOT NULL,
  `lead_neighborhood` int(11) DEFAULT NULL,
  `lead_zip` varchar(20) NOT NULL,
  `client_id` int(11) NOT NULL,
  `lead_body` longtext NOT NULL,
  `tree_removal` enum('yes','no') NOT NULL DEFAULT 'no',
  `tree_pruning` enum('yes','no') NOT NULL DEFAULT 'no',
  `stump_removal` enum('yes','no') NOT NULL DEFAULT 'no',
  `hedge_maintenance` enum('yes','no') NOT NULL DEFAULT 'no',
  `shrub_maintenance` enum('yes','no') NOT NULL DEFAULT 'no',
  `wood_disposal` enum('yes','no') NOT NULL DEFAULT 'no',
  `arborist_report` enum('yes','no') NOT NULL DEFAULT 'no',
  `development` enum('yes','no') NOT NULL DEFAULT 'no',
  `root_fertilizing` enum('yes','no') NOT NULL DEFAULT 'no',
  `tree_cabling` enum('yes','no') NOT NULL DEFAULT 'no',
  `emergency` enum('yes','no') NOT NULL DEFAULT 'no',
  `other` enum('yes','no') NOT NULL DEFAULT 'no',
  `spraying` enum('yes','no') NOT NULL DEFAULT 'no',
  `trunk_injection` enum('yes','no') NOT NULL DEFAULT 'no',
  `air_spading` enum('yes','no') NOT NULL DEFAULT 'no',
  `planting` enum('yes','no') NOT NULL DEFAULT 'no',
  `arborist_consultation` enum('yes','no') NOT NULL DEFAULT 'no',
  `construction_arborist_report` enum('yes','no') NOT NULL DEFAULT 'no',
  `tpz_installation` enum('yes','no') NOT NULL DEFAULT 'no',
  `lights_installation` enum('yes','no') NOT NULL DEFAULT 'no',
  `landscaping` enum('yes','no') NOT NULL DEFAULT 'no',
  `snow_removal` enum('yes','no') NOT NULL DEFAULT 'no',
  `lead_status` enum('New','Already Done','No Go','Estimated','For Approval') NOT NULL DEFAULT 'New',
  `lead_reason_status` enum('Don''t provide this service','Out of service area','Don''t want work done anymore','Already Done','Dublicate lead','Hydro','Dangerous tree no access','Spam') DEFAULT NULL,
  `timing` varchar(255) NOT NULL,
  `lead_estimator` varchar(100) DEFAULT 'none',
  `lead_created_by` varchar(30) NOT NULL,
  `lead_date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lead_priority` enum('Regular','Priority','Emergency') NOT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `lead_assigned_date` date DEFAULT NULL,
  `lead_postpone_date` date NOT NULL,
  `lead_scheduled` tinyint(1) NOT NULL DEFAULT '0',
  `lead_call` tinyint(1) NOT NULL DEFAULT '0',
  `lead_json_backup` text,
  `lead_reffered_client` int(11) DEFAULT NULL,
  `lead_reffered_user` int(11) DEFAULT NULL,
  `lead_reffered_by` varchar(255) DEFAULT NULL,
  `lead_comment_note` longtext,
  `lead_gclid` varchar(255) DEFAULT NULL,
  `lead_msclkid` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`lead_id`),
  KEY `lead_no` (`lead_no`),
  KEY `client_id` (`client_id`),
  KEY `lead_status` (`lead_status`),
  KEY `gsearch` (`lead_address`),
  KEY `date` (`lead_date_created`),
  KEY `neighborhood` (`lead_neighborhood`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `login_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_user_id` int(11) DEFAULT NULL,
  `log_time` int(11) DEFAULT NULL,
  `log_user_ip` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
  `log_data` text CHARACTER SET utf8,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `modules_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `module_desc` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `neighborhoods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coords` longtext NOT NULL,
  `offset_center` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `newsletters` (
  `nl_id` int(11) NOT NULL AUTO_INCREMENT,
  `nl_estimator` int(11) DEFAULT NULL,
  `nl_client` int(11) NOT NULL,
  `nl_subject` varchar(255) NOT NULL,
  `nl_from` varchar(255) NOT NULL,
  `nl_to` varchar(255) NOT NULL,
  `nl_text` text NOT NULL,
  `nl_status` int(1) DEFAULT NULL,
  `nl_mailgun_id` varchar(255) DEFAULT NULL,
  `nl_mailgun_status` varchar(255) DEFAULT NULL,
  `nl_date` datetime DEFAULT NULL,
  PRIMARY KEY (`nl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `notebook` (
  `nb_id` int(11) NOT NULL AUTO_INCREMENT,
  `nb_name` varchar(255) NOT NULL,
  `nb_number` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`nb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL COMMENT 'client id',
  `invoice_id` int(11) DEFAULT NULL COMMENT 'invoice id',
  `estimate_id` int(11) NOT NULL COMMENT 'estimate id',
  `amount_paid` float NOT NULL COMMENT 'amount paid',
  `transaction_approve` int(1) NOT NULL COMMENT 'transaction approve status',
  `transaction_order_no` varchar(20) NOT NULL COMMENT 'transaction order number',
  `transaction_id` int(11) NOT NULL COMMENT 'transaction id',
  `transaction_msg` tinytext NOT NULL COMMENT 'transaction message',
  `payment_file` varchar(100) DEFAULT NULL,
  `transaction_date` datetime NOT NULL COMMENT 'transaction date',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `estimate_id` (`estimate_id`),
  KEY `transaction_order_no` (`transaction_order_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `payment_account` (
  `payment_account_id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_account_name` varchar(255) NOT NULL,
  PRIMARY KEY (`payment_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `payment_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `payment_file` varchar(600) NOT NULL,
  `created_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `payroll` (
  `payroll_id` int(11) NOT NULL AUTO_INCREMENT,
  `payroll_start_date` date NOT NULL,
  `payroll_end_date` date NOT NULL,
  `payroll_day` date DEFAULT NULL,
  PRIMARY KEY (`payroll_id`),
  UNIQUE KEY `dates` (`payroll_start_date`,`payroll_end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `payroll_deductions` (
  `deduction_id` int(11) NOT NULL AUTO_INCREMENT,
  `deduction_employee_id` int(11) NOT NULL,
  `deduction_user_id` int(11) NOT NULL,
  `deduction_payroll_id` int(11) NOT NULL,
  `deduction_amount` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`deduction_id`),
  KEY `emp_id` (`deduction_employee_id`),
  KEY `payroll_id` (`deduction_payroll_id`),
  KEY `emp_payroll` (`deduction_employee_id`,`deduction_payroll_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `project` (
  `project_id` int(11) NOT NULL AUTO_INCREMENT,
  `estimate_id` int(11) DEFAULT NULL,
  `crew_id` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `crew_assigned` enum('1','0') DEFAULT '0',
  `crew_assigned_str` varchar(128) NOT NULL,
  `project_color` varchar(7) NOT NULL,
  `project_title` varchar(50) NOT NULL,
  `estimated_hrs` float DEFAULT NULL,
  PRIMARY KEY (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `qa` (
  `qa_id` int(11) NOT NULL AUTO_INCREMENT,
  `qa_name` varchar(255) DEFAULT NULL,
  `qa_description` text,
  `qa_type` enum('suggestion','complain','complement') NOT NULL DEFAULT 'complain',
  `qa_rate` int(11) NOT NULL,
  `qa_status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`qa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `reasons_absence` (
  `reason_id` int(11) NOT NULL AUTO_INCREMENT,
  `reason_name` varchar(255) DEFAULT NULL,
  `reason_limit` int(11) DEFAULT NULL,
  `reason_status` tinyint(1) NOT NULL DEFAULT '1',
  `reason_company` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`reason_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `repair_requests` (
  `repair_id` int(11) NOT NULL AUTO_INCREMENT,
  `repair_item_id` int(11) NOT NULL,
  `repair_status` enum('repaired','not_repaired','on_hold') NOT NULL,
  `repair_priority` tinyint(4) NOT NULL,
  `repair_solder_id` int(11) NOT NULL,
  `repair_author_id` int(11) NOT NULL,
  `repair_price` float NOT NULL,
  `repair_hours` float NOT NULL,
  `repair_counter` float NOT NULL DEFAULT '0',
  `repair_date` datetime NOT NULL,
  `repair_type` enum('damage','repair','maintenance') NOT NULL,
  `repair_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `repair_first_comment` text,
  `repair_finish_comment` text,
  PRIMARY KEY (`repair_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `sales` (
  `sale_id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_date` date DEFAULT NULL,
  `sale_amount` int(11) DEFAULT NULL,
  PRIMARY KEY (`sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `schedule` (
  `id` bigint(20) NOT NULL,
  `event_team_id` int(11) NOT NULL,
  `event_wo_id` int(11) NOT NULL,
  `event_start` int(11) NOT NULL,
  `event_end` int(11) NOT NULL,
  `event_note` text,
  `event_report` text,
  `event_report_confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `event_services` text,
  `event_damage` double NOT NULL DEFAULT '0',
  `event_complain` double NOT NULL DEFAULT '0',
  `event_compliment` varchar(255) DEFAULT NULL,
  `event_price` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `wo_id` (`event_wo_id`),
  KEY `crew_id` (`event_team_id`),
  KEY `date` (`event_start`,`event_end`),
  KEY `event_crew_id` (`event_team_id`,`event_wo_id`),
  KEY `evrep` (`event_report_confirmed`,`event_report`(10))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `schedule_absence` (
  `absence_employee_id` int(11) NOT NULL,
  `absence_reason_id` int(11) NOT NULL,
  `absence_date` int(11) NOT NULL,
  `absence_ymd` date NOT NULL,
  `absence_user_id` int(11) NOT NULL,
  PRIMARY KEY (`absence_user_id`,`absence_ymd`,`absence_reason_id`),
  KEY `emp_id` (`absence_employee_id`),
  KEY `date` (`absence_date`),
  KEY `reason` (`absence_reason_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `schedule_crews_stat` (
  `team_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `total` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `schedule_crews_stat_old` (
  `team_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `total` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `schedule_days_note` (
  `note_date` int(11) NOT NULL,
  `note_text` text,
  UNIQUE KEY `note_date` (`note_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `schedule_estimators_stat` (
  `team_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `schedule_estimators_stat_old` (
  `team_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `schedule_event_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`),
  KEY `event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `schedule_teams` (
  `team_id` int(11) NOT NULL AUTO_INCREMENT,
  `team_crew_id` int(11) DEFAULT NULL,
  `team_leader_id` int(11) DEFAULT NULL,
  `team_leader_user_id` int(11) DEFAULT NULL,
  `team_color` varchar(10) NOT NULL,
  `team_date` int(11) NOT NULL,
  `team_note` text,
  `team_hidden_note` text,
  `team_fail_equipment` text,
  `team_expenses` text,
  `team_amount` float DEFAULT '0',
  `team_man_hours` float NOT NULL DEFAULT '0',
  `team_closed` tinyint(4) NOT NULL DEFAULT '0',
  `team_rating` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`team_id`),
  KEY `team_date` (`team_date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `schedule_teams_bonuses` (
  `bonus_id` int(11) NOT NULL AUTO_INCREMENT,
  `bonus_type_id` int(11) NOT NULL,
  `bonus_team_id` int(11) NOT NULL,
  `bonus_amount` int(11) NOT NULL,
  `bonus_title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`bonus_id`),
  KEY `team_id` (`bonus_team_id`) USING BTREE,
  KEY `type_id` (`bonus_type_id`),
  KEY `team_type` (`bonus_type_id`,`bonus_team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `schedule_teams_equipment` (
  `equipment_team_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `equipment_driver_id` int(11) DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  PRIMARY KEY (`equipment_team_id`,`equipment_id`),
  KEY `eq_id` (`equipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `schedule_teams_members` (
  `employee_team_id` int(11) NOT NULL DEFAULT '0',
  `employee_id` int(11) NOT NULL DEFAULT '0',
  `employee_logout` time DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `weight` int(11) DEFAULT NULL,
  PRIMARY KEY (`employee_team_id`,`employee_id`,`user_id`),
  KEY `emp_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `schedule_teams_tools` (
  `stt_id` int(11) NOT NULL AUTO_INCREMENT,
  `stt_team_id` int(11) NOT NULL,
  `stt_item_id` int(11) NOT NULL,
  PRIMARY KEY (`stt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `schedule_updates` (
  `update_id` int(11) NOT NULL AUTO_INCREMENT,
  `update_time` int(11) NOT NULL,
  PRIMARY KEY (`update_id`),
  KEY `time` (`update_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `scripts` (
  `script_id` int(11) NOT NULL AUTO_INCREMENT,
  `script_name` varchar(255) NOT NULL,
  `script_text` text NOT NULL,
  PRIMARY KEY (`script_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `services` (
  `service_id` int(11) NOT NULL AUTO_INCREMENT,
  `service_name` varchar(255) CHARACTER SET utf16 DEFAULT NULL,
  `service_description` text,
  `service_cost` float DEFAULT NULL,
  `service_priority` int(11) NOT NULL DEFAULT '1',
  `service_status` tinyint(1) NOT NULL DEFAULT '1',
  `service_parent_id` int(11) DEFAULT NULL,
  `service_attachments` text,
  PRIMARY KEY (`service_id`),
  KEY `status` (`service_status`),
  KEY `parent` (`service_parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sms_messages` (
  `sms_id` int(11) NOT NULL AUTO_INCREMENT,
  `sms_sid` varchar(255) DEFAULT NULL,
  `sms_number` varchar(255) DEFAULT NULL,
  `sms_body` text,
  `sms_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `sms_support` tinyint(1) DEFAULT '0',
  `sms_readed` tinyint(1) DEFAULT '0',
  `sms_client_id` int(11) DEFAULT NULL,
  `sms_user_id` int(11) DEFAULT NULL,
  `sms_incoming` tinyint(4) DEFAULT '0',
  `sms_status` varchar(255) DEFAULT NULL,
  `sms_error` varchar(255) DEFAULT NULL,
  `sms_auto` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`sms_id`),
  KEY `sms_number` (`sms_number`),
  KEY `sms_date` (`sms_date`),
  KEY `client_id_number` (`sms_client_id`,`sms_number`),
  KEY `sms_client_id` (`sms_client_id`),
  KEY `sms_sid` (`sms_sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `sms_tpl` (
  `sms_id` int(11) NOT NULL AUTO_INCREMENT,
  `sms_name` varchar(255) NOT NULL,
  `sms_text` text NOT NULL,
  PRIMARY KEY (`sms_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `static_objects` (
  `object_id` int(11) NOT NULL AUTO_INCREMENT,
  `object_name` varchar(255) NOT NULL,
  `object_desc` text NOT NULL,
  `object_color` varchar(10) DEFAULT NULL,
  `object_latitude` double NOT NULL,
  `object_longitude` double NOT NULL,
  `object_street` varchar(30) NOT NULL,
  `object_city` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `status_log` (
  `status_id` int(11) NOT NULL AUTO_INCREMENT,
  `status_type` enum('lead','estimate','workorder','invoice') NOT NULL,
  `status_item_id` int(11) NOT NULL,
  `status_value` varchar(255) NOT NULL,
  `status_date` int(11) NOT NULL,
  `status_user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`status_id`),
  KEY `type_id` (`status_type`,`status_item_id`,`status_date`) USING BTREE,
  KEY `user_id` (`status_user_id`),
  KEY `typeDateVal` (`status_type`,`status_value`,`status_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `stumps` (
  `stump_id` int(11) NOT NULL AUTO_INCREMENT,
  `stump_address` varchar(50) NOT NULL,
  `stump_house_number` int(5) DEFAULT NULL,
  `stump_city` varchar(50) NOT NULL,
  `stump_state` varchar(50) NOT NULL,
  `stump_lat` double DEFAULT NULL,
  `stump_lon` double DEFAULT NULL,
  `stump_desc` varchar(255) DEFAULT NULL,
  `stump_client_id` int(11) NOT NULL,
  `stump_status` enum('new','grinded','cleaned_up','skipped','canceled','on_hold') NOT NULL DEFAULT 'new',
  `stump_assigned` int(11) DEFAULT NULL,
  `stump_clean_id` int(11) DEFAULT NULL,
  `stump_data` text,
  `stump_unique_id` varchar(50) DEFAULT NULL,
  `stump_map_grid` varchar(255) DEFAULT NULL,
  `stump_side` varchar(50) DEFAULT NULL,
  `stump_range` int(11) DEFAULT NULL,
  `stump_locates` varchar(20) DEFAULT NULL,
  `stump_status_work` int(11) NOT NULL DEFAULT '0',
  `stump_contractor_notes` varchar(255) DEFAULT NULL,
  `stump_removal` timestamp NULL DEFAULT NULL,
  `stump_clean` timestamp NULL DEFAULT NULL,
  `stump_archived` tinyint(1) DEFAULT NULL,
  `stump_last_status_changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`stump_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `stumps_client` (
  `cl_id` int(11) NOT NULL AUTO_INCREMENT,
  `cl_name` varchar(255) NOT NULL,
  `cl_lastname` varchar(255) NOT NULL,
  `cl_hidden` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tasks` (
  `task_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `task_urgency` enum('1','2') NOT NULL DEFAULT '1',
  `task_description` mediumtext,
  `task_status` int(11) NOT NULL DEFAULT '1',
  `task_date_created` datetime NOT NULL,
  `task_created_by` int(11) NOT NULL,
  PRIMARY KEY (`task_id`),
  KEY `user_id` (`user_id`),
  KEY `task_status` (`task_status`),
  KEY `created_by` (`task_created_by`),
  KEY `user_created_status` (`user_id`,`task_created_by`,`task_status`),
  KEY `created_user` (`user_id`,`task_created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tracking_parking` (
  `tracking_id` int(11) NOT NULL AUTO_INCREMENT,
  `tracking_device_name` varchar(255) DEFAULT NULL,
  `tracking_start_time` datetime DEFAULT NULL,
  `tracking_end_time` datetime DEFAULT NULL,
  `tracking_coords` varchar(255) DEFAULT NULL,
  `tracking_lat` double DEFAULT NULL,
  `tracking_lon` double DEFAULT NULL,
  `tracking_period` decimal(10,1) DEFAULT NULL,
  PRIMARY KEY (`tracking_id`),
  UNIQUE KEY `tracking` (`tracking_device_name`,`tracking_start_time`,`tracking_end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `trees` (
  `trees_id` int(11) NOT NULL AUTO_INCREMENT,
  `trees_name_eng` varchar(255) DEFAULT NULL,
  `trees_name_lat` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`trees_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `trees_info` (
  `tree_id` int(11) NOT NULL AUTO_INCREMENT,
  `tree_common_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `tree_scientific_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `tree_family_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `tree_data` text CHARACTER SET utf8,
  PRIMARY KEY (`tree_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `trees_pests` (
  `pest_id` int(11) NOT NULL AUTO_INCREMENT,
  `pest_tree_id` int(11) NOT NULL,
  `pest_eng_name` varchar(255) NOT NULL,
  `pest_lat_name` varchar(255) NOT NULL,
  `pest_description` text NOT NULL,
  `pest_notes` text NOT NULL,
  `pest_affecting` enum('diseases','insects','') DEFAULT NULL,
  PRIMARY KEY (`pest_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `trees_pests_products` (
  `tpp_id` int(11) NOT NULL AUTO_INCREMENT,
  `tpp_pest_id` int(11) NOT NULL,
  `tpp_name` varchar(255) NOT NULL,
  `tpp_rate` varchar(255) NOT NULL,
  `tpp_notes` text NOT NULL,
  PRIMARY KEY (`tpp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `tree_pest_relations` (
  `tpr_id` int(11) NOT NULL AUTO_INCREMENT,
  `tpr_tree_id` int(11) NOT NULL,
  `tpr_pest_id` int(11) NOT NULL,
  `tpr_notes` text NOT NULL,
  `tpr_description` text NOT NULL,
  PRIMARY KEY (`tpr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_type` enum('admin','user') NOT NULL DEFAULT 'user',
  `emailid` varchar(100) NOT NULL,
  `password` varchar(32) NOT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `added_on` datetime NOT NULL,
  `updated_on` datetime DEFAULT NULL,
  `active_status` enum('yes','no') NOT NULL DEFAULT 'no',
  `picture` varchar(100) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `rate` int(11) NOT NULL DEFAULT '100',
  `color` varchar(10) NOT NULL DEFAULT '#ffffff',
  `last_online` int(11) DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `twilio_worker_id` varchar(255) DEFAULT NULL,
  `twilio_workspace_id` varchar(255) DEFAULT NULL,
  `twilio_worker_agent` tinyint(1) DEFAULT NULL,
  `twilio_user_list` tinyint(1) DEFAULT NULL,
  `twilio_support` tinyint(1) NOT NULL DEFAULT '0',
  `twilio_level` int(11) DEFAULT NULL,
  `user_task` tinyint(1) DEFAULT NULL,
  `user_signature` text,
  `user_active_employee` tinyint(1) DEFAULT NULL,
  `user_emp_id` int(11) DEFAULT NULL,
  `system_user` tinyint(4) NOT NULL DEFAULT '0',
  `duty` tinyint(1) DEFAULT '0',
  `worker_type` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `twiliosid` (`twilio_worker_id`),
  KEY `fk_user_grps` (`user_type`),
  KEY `password` (`password`),
  KEY `emailid` (`emailid`),
  KEY `active_status` (`active_status`),
  KEY `status` (`active_status`),
  KEY `active_employee` (`user_active_employee`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `users_sms` (
  `us_id` int(11) NOT NULL AUTO_INCREMENT,
  `us_user_id` int(11) DEFAULT NULL,
  `us_recipient_user_id` int(11) DEFAULT NULL,
  `us_recipient` varchar(255) DEFAULT NULL,
  `us_body` text,
  `us_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`us_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `users_votes` (
  `vote_id` int(11) NOT NULL AUTO_INCREMENT,
  `vote_who` int(11) NOT NULL,
  `vote_user_id` int(11) NOT NULL,
  `vote_date` int(11) NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`vote_id`),
  KEY `userWho` (`vote_user_id`,`vote_who`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `user_history_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_user_id` int(11) DEFAULT NULL,
  `log_url` text,
  `log_postdata` longtext,
  `log_getdata` text,
  `log_date` datetime DEFAULT NULL,
  `log_user_ip` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`log_user_id`),
  KEY `log_date` (`log_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `user_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `address1` varchar(255) DEFAULT NULL,
  `address2` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `user_module` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `module_id` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `module_status` enum('0','1','2','3') CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `vehicles` (
  `vehicle_id` int(11) NOT NULL AUTO_INCREMENT,
  `vehicle_name` varchar(255) NOT NULL,
  `vehicle_trailer` tinyint(1) DEFAULT NULL,
  `vehicle_options` text,
  `vehicle_tool` tinyint(1) DEFAULT NULL,
  `vehicle_disabled` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`vehicle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `voice_tpl` (
  `voice_id` int(11) NOT NULL AUTO_INCREMENT,
  `voice_name` varchar(255) NOT NULL,
  `voice_resp` text NOT NULL,
  PRIMARY KEY (`voice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `workorders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workorder_no` varchar(50) DEFAULT NULL,
  `estimate_id` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `wo_confirm_how` varchar(255) DEFAULT NULL,
  `wo_deposit_taken_by` text,
  `wo_deposit_paid` decimal(10,2) NOT NULL,
  `wo_scheduling_preference` text,
  `wo_extra_not_crew` varchar(255) DEFAULT NULL,
  `in_time_left_office` varchar(255) DEFAULT NULL,
  `in_time_arrived_site` varchar(255) DEFAULT NULL,
  `in_time_left_site` varchar(255) DEFAULT NULL,
  `in_time_arrived_office` varchar(255) DEFAULT NULL,
  `in_job_completed` enum('Yes','No') NOT NULL DEFAULT 'No',
  `in_payment_received` enum('Yes','No') NOT NULL DEFAULT 'No',
  `in_left_todo` mediumtext,
  `in_any_damage` mediumtext,
  `in_eq_malfuntion` mediumtext,
  `in_note_completion` mediumtext,
  `wo_status` varchar(100) DEFAULT NULL,
  `wo_estimator` int(11) DEFAULT NULL,
  `wo_priority` enum('Regular','Priority','Emergency') NOT NULL DEFAULT 'Regular',
  `date_created` date DEFAULT NULL,
  `wo_pdf_files` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `workorder_no` (`workorder_no`),
  UNIQUE KEY `estimate_id` (`estimate_id`),
  KEY `client_id` (`client_id`),
  KEY `wo_status` (`wo_status`),
  KEY `date` (`date_created`),
  KEY `status_user` (`wo_status`,`wo_estimator`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `workorder_employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workorder_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `crew_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `workorder_status` (
  `wo_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `wo_status_name` varchar(255) NOT NULL,
  `wo_status_color` varchar(10) DEFAULT NULL,
  `wo_status_active` tinyint(1) NOT NULL DEFAULT '1',
  `wo_status_priority` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`wo_status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `ws_disconnects` (
  `wsd_id` int(11) NOT NULL AUTO_INCREMENT,
  `wsd_worker` varchar(255) NOT NULL,
  `wsd_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`wsd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
