<?php
$servername = "localhost";
$username = "apluscrm_mtkkdb";
$password = "&RNDrt3LA3sF";
$dbname = "apluscrm_mtkdb";


// Create connection
$con = mysqli_connect($servername, $username, $password,$dbname);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
//echo "Connected successfully";


// try {
//     // Create PDO connection
//     $dsn = "mysql:host=localhost;dbname=dmboss;charset=utf8";
//     $usernamee = "dmboss";
//     $passwordd = "Zrs37reJ3H35tJBf";
//     $options = [
//         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
//         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
//         PDO::ATTR_EMULATE_PREPARES => false,
//     ];

//     // Establish PDO connection
//     $pdo = new PDO($dsn, $usernamee, $passwordd, $options);
// } catch (PDOException $e) {
//     die("Database connection failed: " . $e->getMessage());
// }

?>