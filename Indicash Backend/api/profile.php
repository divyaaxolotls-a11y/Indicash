<?php
include "con.php";

extract($_REQUEST);

mysqli_query($con,"update users set name='$name',email='$email' WHERE mobile='$mobile'");

$data['success'] = "1";

echo json_encode($data);