<?php
$xw = xmlwriter_open_memory();
xmlwriter_set_indent($xw, 1);
$res = xmlwriter_set_indent_string($xw, ' ');

// Первый элемент
xmlwriter_start_element($xw, 'data');

xmlwriter_start_element($xw, 'action');

foreach ($result as $key => $value) {
    //echo ' ' . $key . '="' . htmlentities($value, ENT_QUOTES, 'UTF-8') . '"';
    xmlwriter_start_attribute($xw, $key);
    xmlwriter_text($xw, $value);

    xmlwriter_end_attribute($xw);
}

xmlwriter_end_element($xw);
xmlwriter_end_element($xw);

echo xmlwriter_output_memory($xw);
?>
