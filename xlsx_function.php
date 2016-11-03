<?php
include_once("xlsxwriter.class.php");

function xlsx_function($array) {

$writer = new XLSXWriter();
$writer->writeSheet($array);
$writer->writeToFile('output.xlsx');

}

?>