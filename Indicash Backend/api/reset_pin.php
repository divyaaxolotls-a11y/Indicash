<?php
include "con.php";

extract($_REQUEST);

mysqli_query($con,"update users set pin='$pin' WHERE mobile='$mobile'");

$data['success'] = "1";

echo json_encode($data);