<?php
define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);
require_once  'library/PhpExcel/Classes/PHPExcel.php' ;
$xls = new PHPExcel();
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="your_name.xls"');
header('Cache-Control: max-age=0');
$writer = PHPExcel_IOFactory::createWriter($xls, 'Excel5');
$writer->save('php://output');