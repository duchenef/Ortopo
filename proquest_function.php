<?php

function proquest_function($csvarray, $row) {
   
$title = $csvarray[$row][1]." - ".$csvarray[$row][0]." - ".$csvarray[$row][2];
$price = $csvarray[$row][4];
$qty = $csvarray[$row][5];

return array($title, $price, $qty);

}

?>