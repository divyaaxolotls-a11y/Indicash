<?php
// Database credentials
$host = 'localhost';
$user = 'dmboss';
$pass = 'Zrs37reJ3H35tJBf';
$dbname = 'dmboss';

// Connect to database
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set filename
$filename = 'backup-' . date('Y-m-d_H-i-s') . '.sql';

// Send headers for download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Get all tables
$tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}

// Generate SQL for each table
foreach ($tables as $table) {
    // Table structure
    $create = $conn->query("SHOW CREATE TABLE `$table`")->fetch_row();
    echo "DROP TABLE IF EXISTS `$table`;\n";
    echo $create[1] . ";\n\n";
    
    // Table data
    $rows = $conn->query("SELECT * FROM `$table`");
    while ($row = $rows->fetch_assoc()) {
        $values = array_map(function($v) use ($conn) {
            return "'" . $conn->real_escape_string($v) . "'";
        }, $row);
        echo "INSERT INTO `$table` VALUES(" . implode(',', $values) . ");\n";
    }
    echo "\n\n";
}

$conn->close();
exit;
?>