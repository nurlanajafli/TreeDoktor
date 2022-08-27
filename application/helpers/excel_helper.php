<?php

function get_column_color($sheet, $column) {
	$CI = & get_instance();

	$color = $sheet->getStyle($column)->getFill()->getStartColor()->getARGB();

	$color = '#' . substr($color, 2);

	/**********BLUE***********/
	if($color == '#B9CDE5' || $color == '#8EB4E3' || $color == '#00B0F0')
		$color = '#95B3D7';

	/**********GREEN***********/
	if($color == '#C2D69B' || $color == '#92D050')
		$color = '#C3D69B';

	/**********WHITE***********/
	if($color == '#FF0000' || $color == '#C00000')
		$color = '#FFFFFF';

	return $color;
}

function get_stumps_data($file) {
	$CI = & get_instance();
	$CI->load->library('excel');
	$objReader = PHPExcel_IOFactory::load($file);
	$sheet = $objReader->getSheet(0);
	$highestRow = $sheet->getHighestRow();
	$highestColumn = 'S';//$sheet->getHighestColumn();

	$data = [];
	$key = 0;
	for ($row = 1; $row <= $highestRow; $row++) {
		$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
		if(isset($rowData[0]) && $rowData[0][0]) {
			$rowData[0]['color'] = get_column_color($sheet, 'I' . $row);
			$data[$key] = $rowData[0];
			$data[$key]['grind_formula'] = NULL;
			$data[$key]['clean_formula'] = NULL;

			if(preg_match('/SUM\((.*?)\)/is', $sheet->getCell('N' . $row)->getValue(), $matches))
				$data[$key]['grind_formula'] = explode(',', trim($matches[1], ','));

			if(preg_match('/SUM\((.*?)\)/is', $sheet->getCell('Q' . $row)->getValue(), $matches))
				$data[$key]['clean_formula'] = explode(',', trim($matches[1], ','));

			$key++;
		}
	}
	unset($data[0]);
	$result = $data;
	foreach ($data as $num => $item) {
		if(!$num || !isset($item[0]) || !$item[0])
		{
			unset($data[$num], $result[$num]);
			continue;
		}
		if($item['grind_formula'])
		{
			foreach ($item['grind_formula'] as $range)
			{
				if(!$range)
					continue;
				$range = str_replace('H', 'A', $range);
				$ids = $sheet->rangeToArray($range);
				foreach ($ids as $id)
				{
					$row = & array_filter($data, function($row) use($id) {
						return $row[0] == $id[0];
					});
					$key = key($row);
					$result[$key][10] = $item[10];
					$result[$key][12] = $item[12];
				}
			}
		}

		if($item['clean_formula'])
		{
			foreach ($item['clean_formula'] as $range)
			{
				if(!$range)
					continue;
				$range = str_replace('H', 'A', $range);
				$ids = $sheet->rangeToArray($range);
				foreach ($ids as $id)
				{
					$row = & array_filter($data, function($row) use($id) {
						return $row[0] == $id[0];
					});
					$key = key($row);
					$result[$key][11] = $item[11];
					$result[$key][15] = $item[15];
				}
			}
		}
		//unset($data[$num]['clean_formula'], $data[$num]['grind_formula']);
		//unset($result[$num]['clean_formula'], $result[$num]['grind_formula']);
	}
	return $result;
}

function vaughan_stumps_report($client_id = NULL)
{
	$CI = & get_instance();
	$CI->load->library('excel');
	$CI->load->library('user_agent');
	$xls = new PHPExcel();
	
	$where = [];
	if ($CI->agent->is_referral())
	{
		$refer =  $CI->agent->referrer();
		if(strpos($refer, 'my_stumps'))
			$where = "(stump_assigned = " . $CI->session->userdata['user_id'] . " OR stump_clean_id = " . $CI->session->userdata['user_id'] . ")";
	}
	if($client_id)
	{
		if($where && count($where))
			$where .= ' AND stump_client_id = ' . $client_id;
		else
			$where = 'stump_client_id = ' . $client_id;
	}
	$statuses = [
		'new' => 'New',
		'grinded' => 'Grinded',
		'cleaned_up' => 'Cleaned Up',
		'skipped' => 'Skipped',
	];

	$listNumber = 0;
	
	foreach ($statuses as $statusName => $statusTitle) {		
		$sheet = $xls->createSheet($listNumber);
		$xls->setActiveSheetIndex($listNumber);
		// Подписываем лист
		$sheet->setTitle($statusTitle);

		$sheet->getRowDimension(1)->setRowHeight(50);
		$sheet->freezePane('A2');// Закрепляем 1 строку


		$columns = [
			'A' => ['name' => '#', 'width' => '3.5'],
			'B' => ['name' => 'Block', 'width' => '1.6'],
			'C' => ['name' => 'Street #', 'width' => '1.6'],
			'D' => ['name' => 'Address', 'width' => '6'],
			'E' => ['name' => 'Loc. Info', 'width' => '6'],
			'F' => ['name' => 'Dist.', 'width' => '2.25'],
			'G' => ['name' => 'Size', 'width' => '2.25'],
			'H' => ['name' => 'Locates', 'width' => '4'],
			'I' => ['name' => 'Notes', 'width' => '4'],
			'J' => ['name' => 'Grinded', 'width' => '7'],
			'K' => ['name' => 'Cleaned', 'width' => '7'],
		];

		foreach ($columns as $col => $val) {
			$sheet->setCellValue($col . "1", $val['name']);
			$sheet->getStyle($col . '1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$sheet->getStyle($col . '1')->getFont()->setSize(12);
			$sheet->getStyle($col . '1')->getFont()->setBold(true);
			$sheet->getStyle($col . '1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$sheet->getStyle($col . '1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$sheet->getStyle($col . '1')->getAlignment()->setWrapText(true);
			$sheet->getStyle($col . '1')->getBorders()->getBottom()->applyFromArray(array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('rgb' => '000000')));
			$sheet->getStyle($col . '1')->getBorders()->getRight()->applyFromArray(array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')));
			$sheet->getColumnDimension($col)->setWidth(floatval($val['width']) * 4.054);
		}

		$grid = NULL;
		$gridStart = 1;
		$row = 2;

		$data = $CI->mdl_stumps->get_xlsx_data($statusName, NULL, $where);
		//echo $CI->db->last_query();die;
		foreach ($data as $key => $stump) {

			$jsonData = json_decode($stump['stump_data']);

            $UniqueID = $stump['stump_unique_id'];
			/*if(isset($jsonData[0]) || isset($jsonData[1]))
			    $UniqueID = strpos($jsonData[1], '-PV-') ? $jsonData[1] : $jsonData[0];*/
			$sheet->setCellValueByColumnAndRow(0, $row, $UniqueID);
			$sheet->getStyle('A' . $row)->getAlignment()->setWrapText(true);
			$sheet->getStyle('A' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
			$sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

			$sheet->setCellValueByColumnAndRow(1, $row, $stump['stump_map_grid']);
			$sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
			$sheet->getStyle('B' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
			$sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			
			$sheet->setCellValueByColumnAndRow(2, $row, $stump['stump_house_number']);
			$sheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
			$sheet->getStyle('C' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
			$sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

			$sheet->setCellValueByColumnAndRow(3, $row, $stump['stump_address']);
			$sheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
			$sheet->getStyle('D' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);

			$sheet->setCellValueByColumnAndRow(4, $row, $stump['stump_side']);
			$sheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
			$sheet->getStyle('E' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
			$sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

			$sheet->setCellValueByColumnAndRow(5, $row, $stump['stump_desc']);
			$sheet->getStyle('F' . $row)->getAlignment()->setWrapText(true);
			$sheet->getStyle('F' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
			$sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

			$sheet->setCellValueByColumnAndRow(6, $row, $stump['stump_range']);
			$sheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);
			$sheet->getStyle('G' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
			$sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

			$sheet->setCellValueByColumnAndRow(7, $row, $stump['stump_locates']);
			$sheet->getStyle('H' . $row)->getAlignment()->setWrapText(true);
			$sheet->getStyle('H' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
			$sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

			$sheet->setCellValueByColumnAndRow(8, $row, $stump['stump_contractor_notes']);
			$sheet->getStyle('I' . $row)->getAlignment()->setWrapText(true);
			$sheet->getStyle('I' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);

			$date = NULL;
			if($stump['stump_status'] == 'grinded') {
				$date = $stump['stump_removal'] ? date('m/d H:i', strtotime($stump['stump_removal'])) : date('m/d H:i', strtotime($stump['stump_last_status_changed']));
			}
			$sheet->setCellValueByColumnAndRow(9, $row, $stump['stump_status'] == 'grinded' ? $stump['gfirstname'] . ' ' . $stump['glastname'] . ' (' . $date . ')' : '');
			$sheet->getStyle('J' . $row)->getAlignment()->setWrapText(true);
			$sheet->getStyle('J' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
			$sheet->getStyle('J' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

			$sheet->setCellValueByColumnAndRow(10, $row, $stump['stump_clean'] ? $stump['cfirstname'] . ' ' . $stump['clastname'] . ' (' . date('m/d H:i', strtotime($stump['stump_clean'])) . ')' : '');
			$sheet->getStyle('K' . $row)->getAlignment()->setWrapText(true);
			$sheet->getStyle('K' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
			$sheet->getStyle('K' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

			
			$row++;
		}

		foreach($sheet->getRowDimensions() as $rd) {
			$rd->setRowHeight(-1);
		}

		$listNumber++;
	}
	// Выводим HTTP-заголовки
	header ( "Expires: Mon, 1 Apr 1974 05:00:00 GMT" );
	header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
	header ( "Cache-Control: no-cache, must-revalidate" );
	header ( "Pragma: no-cache" );
	header ( "Content-type: application/vnd.ms-excel" );
	header ( "Content-Disposition: attachment; filename=stumps_report_".date('Y-m-d H:i:s').".xlsx" );

	// Выводим содержимое файла
	$objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
	$objWriter->setPreCalculateFormulas(TRUE);
	$objWriter->save('php://output');


}

function markham_stumps_report($data)
{
	$CI = & get_instance();
	$CI->load->library('excel');
	$xls = new PHPExcel();
	// Устанавливаем индекс активного листа
	$xls->setActiveSheetIndex(0);
	// Получаем активный лист
	$sheet = $xls->getActiveSheet();
	// Подписываем лист
	$sheet->setTitle('Stumps Report');

	$sheet->getRowDimension(1)->setRowHeight(32);
	$sheet->freezePane('A2');// Закрепляем 1 строку

	$columns = [
		'A' => ['name' => 'UniqueID', 'width' => '5.45'],
		'B' => ['name' => 'Map Grid', 'width' => '1.6'],
		'C' => ['name' => '#', 'width' => '1.36'],
		'D' => ['name' => 'Street', 'width' => '5.98'],
		'E' => ['name' => 'On Street', 'width' => '5.98'],
		'F' => ['name' => 'Side', 'width' => '2.25'],
		'G' => ['name' => 'Location Notes', 'width' => '13'],
		'H' => ['name' => 'Stump Range', 'width' => '1.85'],
		'I' => ['name' => 'Locates', 'width' => '3.33'],
		'J' => ['name' => 'Contractor Notes', 'width' => '5.67'],
		'K' => ['name' => 'Stump Removal Date(MM/DD)', 'width' => '4.15'],
		'L' => ['name' => 'Stump Cleanup Date(MM/DD)', 'width' => '4'],
		'M' => ['name' => 'GRIND CREW', 'width' => '2.42'],
		'N' => ['name' => 'CMS', 'width' => '1.9'],
		'O' => ['name' => 'STPS', 'width' => '2.15'],
		'P' => ['name' => 'CLEAN CREW', 'width' => '2.88'],
		'Q' => ['name' => 'CMS', 'width' => '1.95'],
		'R' => ['name' => 'STPS', 'width' => '2.15'],
		'S' => ['name' => 'Stump Status', 'width' => '3.33'],
	];

	// Вставляем текст в ячейки
	foreach ($columns as $col => $val) {
		$sheet->setCellValue($col . "1", $val['name']);
		$sheet->getStyle($col . '1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$sheet->getStyle($col . '1')->getFont()->setSize(12);
		$sheet->getStyle($col . '1')->getFont()->setBold(true);
		$sheet->getStyle($col . '1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle($col . '1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$sheet->getStyle($col . '1')->getAlignment()->setWrapText(true);
		$sheet->getStyle($col . '1')->getBorders()->getBottom()->applyFromArray(array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('rgb' => '000000')));
		$sheet->getStyle($col . '1')->getBorders()->getRight()->applyFromArray(array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')));
		$sheet->getColumnDimension($col)->setWidth(floatval($val['width']) * 4.054);
	}

	$grid = NULL;
	$gridStart = 1;
	$row = 2;

	foreach ($data as $key => $stump) {
		$stump_first = json_decode($stump->stump_data);

		if($grid && $grid != $stump->stump_map_grid) {
			$sheet->setCellValueByColumnAndRow(1, $row, $grid);
			$sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
			$sheet->getStyle('B' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
			$sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

			$sheet->setCellValue('H' . $row, '=SUM(H' . $gridStart . ':H' . ($row - 1) . ')');
			$sheet->getStyle('H' . $row)->getAlignment()->setWrapText(true);
			$sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$sheet->getStyle('H' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);

			$gridStart = $row + 1;
			$row++;
		}

		if($stump->stump_unique_id)
			$sheet->setCellValueByColumnAndRow(0, $row, $stump->stump_unique_id);
		else
			$sheet->setCellValueByColumnAndRow(0, $row, $stump->stump_id);

		$sheet->getStyle('A' . $row)->getAlignment()->setWrapText(true);
		$sheet->getStyle('A' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);

		$sheet->setCellValueByColumnAndRow(1, $row, $stump->stump_map_grid);
		$sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
		$sheet->getStyle('B' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
		$sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

		if($stump_first && isset($stump_first->{2}) && is_numeric($stump_first->{2}))
			$value = $stump_first->{2};
		else {
			$parts = explode(' ', $stump->stump_address);
			$value = isset($parts[0]) ? $parts[0] : '';
		}

		$sheet->setCellValueByColumnAndRow(2, $row, $value);
		$sheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
		$sheet->getStyle('C' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);

		if($stump_first && isset($stump_first->{3}) && !is_numeric($stump_first->{3}))
			$value = $stump_first->{3};
		else {
			$parts = explode(' ', $stump->stump_address);
			unset($parts[0]);
			$street = '';
			foreach ($parts as $part)
				$street .= $part . ' ';
			$value = trim($street);
		}
		$sheet->setCellValueByColumnAndRow(3, $row, $value);
		$sheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
		$sheet->getStyle('D' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);

		if($stump_first  && isset($stump_first->{4}) && !is_numeric($stump_first->{4}))
			$value = $stump_first->{4};
		else {
			$parts = explode(' ', $stump->stump_address);
			unset($parts[0]);
			$street = '';
			foreach ($parts as $part)
				$street .= $part . ' ';
			$value = trim($street);
		}
		$sheet->setCellValueByColumnAndRow(4, $row, $value);
		$sheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
		$sheet->getStyle('E' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);

		$sheet->setCellValueByColumnAndRow(5, $row, $stump->stump_side);
		$sheet->getStyle('F' . $row)->getAlignment()->setWrapText(true);
		$sheet->getStyle('F' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);

		$sheet->setCellValueByColumnAndRow(6, $row, $stump->stump_desc);
		$sheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);
		$sheet->getStyle('G' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);

		$sheet->setCellValueByColumnAndRow(7, $row, intval($stump->stump_range));
		$sheet->getStyle('H' . $row)->getAlignment()->setWrapText(true);
		$sheet->getStyle('H' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
		$sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

		$sheet->setCellValueByColumnAndRow(8, $row, $stump->stump_locates);
		$sheet->getStyle('I' . $row)->getAlignment()->setWrapText(true);
		$sheet->getStyle('I' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
		$sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

		$sheet->setCellValueByColumnAndRow(9, $row, $stump->stump_contractor_notes);
		$sheet->getStyle('J' . $row)->getAlignment()->setWrapText(true);
		$sheet->getStyle('J' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);

		$sheet->setCellValueByColumnAndRow(10, $row, date('m/d', strtotime($stump->stump_removal)));
		$sheet->getStyle('K' . $row)->getAlignment()->setWrapText(true);
		$sheet->getStyle('K' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
		$sheet->getStyle('K' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

		$sheet->setCellValueByColumnAndRow(11, $row, date('m/d', strtotime($stump->stump_clean)));
		$sheet->getStyle('L' . $row)->getAlignment()->setWrapText(true);
		$sheet->getStyle('L' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
		$sheet->getStyle('L' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

		$sheet->setCellValueByColumnAndRow(12, $row, $stump->grinded_crew);
		$sheet->getStyle('M' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);

		$range = trim($stump->grinded_crew) ? $stump->stump_range : '';
		$sheet->setCellValueByColumnAndRow(13, $row, $range);
		$sheet->getStyle('N' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);

		$count = trim($stump->grinded_crew) ? 1 : '';
		$sheet->setCellValueByColumnAndRow(14, $row, $count);
		$sheet->getStyle('O' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);

		$sheet->setCellValueByColumnAndRow(15, $row, $stump->cleaned_crew);
		$sheet->getStyle('P' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);

		$range = trim($stump->cleaned_crew) ? $stump->stump_range : '';
		$sheet->setCellValueByColumnAndRow(16, $row, $range);
		$sheet->getStyle('Q' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);

		$count = trim($stump->cleaned_crew) ? 1 : '';
		$sheet->setCellValueByColumnAndRow(17, $row, $count);
		$sheet->getStyle('R' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);

		$sheet->setCellValueByColumnAndRow(18, $row, $stump->stump_status);
		$sheet->getStyle('S' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);

		$grid = $stump->stump_map_grid;
		$row++;
	}

	foreach($sheet->getRowDimensions() as $rd) {
		$rd->setRowHeight(-1);
	}

	// Выводим HTTP-заголовки
	header ( "Expires: Mon, 1 Apr 1974 05:00:00 GMT" );
	header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
	header ( "Cache-Control: no-cache, must-revalidate" );
	header ( "Pragma: no-cache" );
	header ( "Content-type: application/vnd.ms-excel" );
	header ( "Content-Disposition: attachment; filename=stumps_report.xlsx" );

	// Выводим содержимое файла
	$objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
	$objWriter->setPreCalculateFormulas(TRUE);
	$objWriter->save('php://output');
}

function get_gwillimbury_stumps_data($file) {
	$CI = & get_instance();
	$CI->load->library('excel');
	$objReader = PHPExcel_IOFactory::load($file);
	$sheet = $objReader->getSheet(1);
	$highestRow = $sheet->getHighestRow();
	$highestColumn = 'K';

	$data = [];
	$key = 0;
	$grid = 'East Gwillimbury';

	for ($row = 3; $row <= $highestRow; $row++) {
		$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);

		if($rowData[0][0] && $rowData[0][9] && !$rowData[0][1] && !$rowData[0][2] && !$rowData[0][3] && !$rowData[0][4] && !$rowData[0][5] && !$rowData[0][6] && !$rowData[0][7] && !$rowData[0][8])
			$grid = $rowData[0][0];

		if(isset($rowData[0][3]) && isset($rowData[0][4]) && isset($rowData[0][9])) {
			$data[$key] = $rowData[0];
			$data[$key]['grid'] = $grid;
			$data[$key]['color'] = get_column_color($sheet, 'J' . $row);
			for ($i = 1; $i <= ($rowData[0][3] - 1); $i++) {
				$key++;
				$data[$key] = $rowData[0];
				$data[$key]['grid'] = $grid;
				$data[$key]['color'] = get_column_color($sheet, 'J' . $row);
				$data[$key][4] = $rowData[0][$i + 4];
			}
			$key++;
		}
	}

	return $data;
}

function get_vaughan_stumps_data($file) {
	$CI = & get_instance();
	$CI->load->library('excel');
	$objReader = PHPExcel_IOFactory::load($file);
	$sheet = $objReader->getSheet();

	$highestRow = $sheet->getHighestRow();
	$highestColumn = 'AH';

	$data = [];
	
	for ($row = 1; $row <= $highestRow; $row++) {
		$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);

		if(!empty($rowData))
			$data[] = $rowData;
	}

	return $data;

}

/**
 * @param         $file_name
 * @param  array  $csv_data
 * @param  bool   $show
 * @throws PHPExcel_Exception
 * @throws PHPExcel_Reader_Exception
 * @throws PHPExcel_Writer_Exception
 *
 * Make csv file, save in local or to specific path
 */
function save_csv($file_name, $csv_data = [], $saveToLocal=false)
{
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=$file_name");
    header("Content-Type: application/csv;");
//    header('Content-type: application/vnd.ms-excel');

    // columns name
    $headerLetters = range('A', 'Z');
    $objPHPExcel = new PHPExcel();
    $activeSheet = $objPHPExcel->getActiveSheet();

    // make csv data
    foreach ($csv_data as $row => $csvRow) {
        $row += 1; // excel row starts from 1
        $startLetterIndex = 0; // first column name
        $currentLetterIndex = 0; // get each next column name
        $csvRowHeader = false; // if there is data which contains header / footer
        $csvRowFooter = false;
        $mergeCells = false;

        if (isset($csvRow['header'])) {
            $csvRowHeader = $csvRow['header'];
            $csvRow = $csvRow['header'];
        }

        if (isset($csvRow['footer'])) {
            $csvRowFooter = $csvRow['footer'];
            $csvRow = $csvRow['footer'];
        }

        $firstCell = $headerLetters[$startLetterIndex].$row;
        if (isset($csvRow['mergeCells'])) {
            $activeSheet->setCellValue($firstCell, $csvRow['mergeCells'][1]);
            // A1:C1
            $currentLetterIndex = $csvRow['mergeCells'][0]; // cells count which need to be merged
            $lastCell = $headerLetters[$currentLetterIndex].$row;
            $activeSheet->mergeCells("$firstCell:$lastCell");
        } else {
            // fill cells by $csvRow's data for current row
            while (isset($headerLetters[$currentLetterIndex]) && isset($csvRow[$currentLetterIndex])) {
                $activeSheet->setCellValue($headerLetters[$currentLetterIndex].$row, $csvRow[$currentLetterIndex]);
                $currentLetterIndex += 1;
            }
            $currentLetterIndex -= 1; // set Current index to last item from column name
        }

        // set style for header / footer
        if ($csvRowHeader || $csvRowFooter) {
            // styles work for excel
            $objPHPExcel->getActiveSheet()->getStyle($headerLetters[$startLetterIndex].$row . ':' . $headerLetters[$currentLetterIndex].$row)->applyFromArray(
                array(
                    'font'    => array(
                        'name'      => 'Arial',
                        'bold'      => true,
                        'italic'    => false,
                        'underline' => PHPExcel_Style_Font::UNDERLINE_NONE,
                        'strike'    => false,
                        'color'     => array(
                            'rgb' => '30a998'
                        )
                    ),
                    'alignment' => [
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ]
                )
            );
        }
    }

    // generate csv and download
    $objPHPExcel->setActiveSheetIndex(0);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');

    $objWriter->save('php://output');
}

function read_csv($file_name) {
    $objReader = PHPExcel_IOFactory::createReader('CSV')->setDelimiter(',')
        ->setEnclosure('"')
        ->setSheetIndex(0);
    $objPHPExcelFromCSV = $objReader->load($file_name);

    return $objPHPExcelFromCSV;
}
