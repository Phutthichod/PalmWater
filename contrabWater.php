<?php
include('cal.php');
$dimDate = getDIMDate()[1]['ID']-1;
calWerPerDay($dimDate,-1);
checkDrying($dimDate,-1);
?>