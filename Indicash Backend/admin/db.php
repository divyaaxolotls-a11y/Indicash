<?php
$host = "localhost";
$user = "apluscrm_mtkkdb";
$pass = "&RNDrt3LA3sF";
$db   = "apluscrm_mtkdb";

$con = mysqli_connect($host, $user, $pass, $db);

if (!$con) {
    die(json_encode([
        "status" => false,
        "message" => "Database connection failed"
    ]));
}
