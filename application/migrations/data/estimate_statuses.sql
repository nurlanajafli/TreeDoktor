
INSERT INTO `estimate_statuses` (`est_status_id`, `est_status_name`, `est_status_active`, `est_status_declined`, `est_status_default`, `est_status_confirmed`, `est_status_sent`, `est_status_priority`) VALUES
('1', 'New', 1, 0, 1, 0, 0, 2),
('2', 'Sent for approval', 1, 0, 0, 0, 1, 3),
('3', 'Pending approval', 1, 0, 0, 0, 0, 4),
('4', 'Declined', 1, 1, 0, 0, 0, 7),
('5', 'Decline - No follow up', 0, 1, 0, 0, 0, 8),
('6', 'Confirmed', 1, 0, 0, 1, 0, 5),
('7', 'Contact the client', 1, 0, 0, 0, 0, 1),
('8', 'Thinking- No Follow Up Needed', 1, 0, 0, 0, 0, 6),
('9', 'Expired', 1, 0, 0, 0, 0, 9),
('10', 'Credit', 1, 0, 0, 0, 0, 0);
