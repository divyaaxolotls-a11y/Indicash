<?php 
include('config.php');
session_start();

// 1. CSRF Validation
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('<tr><td colspan="5" style="text-align:center; color:red;">Invalid Session. Please refresh.</td></tr>');
}

// 2. Variable Reception & Sanitization
$date_input    = isset($_POST['resultDate']) ? $_POST['resultDate'] : ''; // Format: YYYY-MM-DD
$market_raw    = isset($_POST['gameID']) ? $_POST['gameID'] : '';     // e.g., "MAIN BAZAR"
$amount_filter = isset($_POST['amount']) ? (int)$_POST['amount'] : 0;
$openClose     = isset($_POST['openClose']) ? $_POST['openClose'] : '';
$userSearch    = isset($_POST['userSearch']) ? mysqli_real_escape_string($con, $_POST['userSearch']) : '';

if (empty($date_input) || empty($market_raw)) {
    die('<tr><td colspan="5" style="text-align:center;">Please select Date and Game.</td></tr>');
}

// 3. Date Formatting
// Converting "2026-02-02" to "02/02/2026" to match your DB screenshot
$date_timestamp = strtotime($date_input);
$date_db_format = date('d/m/Y', $date_timestamp); 

// 4. Table Selection Logic (Current month vs Archive)
$limit_date = strtotime('-29 days');
$table_name = ($date_timestamp >= $limit_date) ? "games" : "games_archive";

// 5. Smart Market Search Logic
// This replaces spaces with % so "MAIN BAZAR" matches "MAIN_BAZAR_OPEN"
$market_search = mysqli_real_escape_string($con, str_replace(" ", "%", $market_raw));

// 6. SQL Query Construction
$qry = "SELECT * FROM `$table_name` WHERE 
        `bazar` LIKE '%$market_search%' 
        AND `date` = '$date_db_format' 
        AND `amount` >= $amount_filter";

// Add User Filter (Mobile Number)
if (!empty($userSearch)) {
    $qry .= " AND `user` = '$userSearch'";
}

// Add Open/Close Filter
if ($openClose == 'Open') {
    $qry .= " AND `bazar` LIKE '%OPEN%'";
} elseif ($openClose == 'Close') {
    $qry .= " AND `bazar` LIKE '%CLOSE%'";
}

$qry .= " ORDER BY sn DESC";

$winning = mysqli_query($con, $qry);

// 7. Output Generation
if (!$winning) {
    die('<tr><td colspan="5" style="color:red;">Database Error: ' . mysqli_error($con) . '</td></tr>');
}

if (mysqli_num_rows($winning) == 0) {
    echo '<tr><td colspan="5" style="text-align:center; padding: 20px;">No records found for ' . htmlspecialchars($market_raw) . ' on ' . $date_db_format . '.</td></tr>';
} else {
    while ($row = mysqli_fetch_array($winning)) {
        // Format Bazar name for display (Remove underscores)
        $display_bazar = str_replace("_", " ", $row['bazar']);
        
        // Determine session label
        $session_label = "-";
        if (stripos($row['bazar'], 'OPEN') !== false) {
            $session_label = '<span class="badge badge-success">Open</span>';
        } elseif (stripos($row['bazar'], 'CLOSE') !== false) {
            $session_label = '<span class="badge badge-danger">Close</span>';
        }
        ?>
        <tr>
            <td style="font-weight: 600;"><?php echo htmlspecialchars($display_bazar); ?></td>
            <td><?php echo htmlspecialchars(ucfirst($row['game'])); ?></td>
            <td><?php echo $session_label; ?></td>
            <td style="color: #007bff; font-weight: bold;"><?php echo htmlspecialchars($row['number']); ?></td>
            <td><strong><?php echo htmlspecialchars($row['amount']); ?></strong></td>
        </tr>
        <?php
    }
}
?>