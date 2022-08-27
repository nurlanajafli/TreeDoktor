
INSERT INTO `workorder_status` (`wo_status_id`, `wo_status_name`, `wo_status_color`, `wo_status_active`, `wo_status_priority`) VALUES
(0, 'Finished', '#397e49', 1, 12),
(1, 'Confirmed online', '#456bdf', 1, 1),
(2, 'Confirmed', '#4599df', 1, 2),
(3, 'Scheduled - Confirmed', '', 1, 3),
(4, 'Scheduled - Pending', '#969696', 1, 4),
(5, 'Stump Grinding', '#edc14b', 1, 5),
(6, 'Firewood delivery', '#edc14b', 1, 6),
(7, 'Finished by field worker', '#5db37e', 1, 7),
(8, 'Unfinished', '#D88177', 1, 8),
(9, 'Complains', '#c3291c', 1, 9),
(10, 'On hold', '#e25d33', 1, 10),
(11, 'Repair', '#d88177', 1, 11);

UPDATE `workorder_status` SET `wo_status_id` = 0 WHERE `wo_status_name` = 'Finished';
