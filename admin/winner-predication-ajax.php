<?php include('config.php');
session_start();
// Generate CSRF token if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a CSRF token
}

if (!isset($_REQUEST['csrf_token']) || $_REQUEST['csrf_token'] !== $_SESSION['csrf_token']) {
    // Alert the user and redirect back to transaction.php
    echo "<script>
        alert('Invalid CSRF token. Please refresh the page and try again.');
        window.location.href = 'winning-prediction.php';
    </script>";
    exit; // Stop further execution
}


$date2 = $_REQUEST['date'];
$date = date('d/m/Y', strtotime($_REQUEST['date']));

$digit = $_REQUEST['digit'];
$panna = $_REQUEST['panna'];

$cdigit = $_REQUEST['cdigit'];
$cpanna = $_REQUEST['cpanna'];

$session = $_REQUEST['session'];
$market = $_REQUEST['market'];
$get_rates = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM `rate`"));

if ($session == 'open') {
    $mrk = str_replace(' ', '_', $market . ' OPEN');
    $qry = "SELECT * FROM games WHERE bazar='$mrk' AND (number='$digit' OR number='$panna') AND date='$date'";
} else {
    $chk_if_query = mysqli_query($con, "SELECT * FROM manual_market_results WHERE market='$market' AND date='$date'");
    $chk_if_updated = mysqli_fetch_array($chk_if_query);

    $open = $chk_if_updated['open'];
    $opanna = $chk_if_updated['open_panna'];

    $mrk = str_replace(' ', '_', $market . ' CLOSE');
    $mrk2 = str_replace(' ', '_', $market . '');

    $jodi = $open . $digit;
    $half1 = $opanna . '-' . $digit;
    $half2 = $panna . '-' . $open;
    $full = $opanna . '-' . $panna;

    $qry = "SELECT * FROM games WHERE (bazar='$mrk' OR bazar='$mrk2') AND (number='$cdigit' OR number='$cpanna' OR number='$jodi' OR number='$half1' OR number='$half2' OR number='$full') AND date='$date'";
}

?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Games Data</title>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <!-- DataTables Buttons CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.7.2/css/buttons.dataTables.min.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .spanblock {
            background: #e9e9e9;
            border-radius: 5px;
            padding: 10px 18px;
            margin-right: 10px; 
        }
    </style>
</head>

<body>
    <div class="card-header">
        <h3 class="card-title" style="padding: 10px;">
            <span class="spanblock">Total Bid Amount: <b id='totalBid'></b></span>
            <span class="spanblock">Total Winning Amount: <b id='totalWin'></b></span>
        </h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User Name</th>
                    <th>Bid Points</th>
                    <th>Winning Points</th>
                    <th>Market Name</th>
                    <th>Game Name</th>
                    <th>Bid number</th>
                    <th>Date</th>
                    <!--<th>Edit</th>-->
                </tr>
            </thead>
            <tbody id="tbody">
                <?php
                $winning = mysqli_query($con, $qry);
                $i = 1;
                $total_bid = 0;
                $total_win = 0;
                
                while ($row = mysqli_fetch_array($winning)) {
                    $userID = $row['user'];
                    $user = mysqli_query($con, "SELECT * FROM `users` WHERE `mobile`='$userID'");
                    $fetch = mysqli_fetch_array($user);
                    $total_bid += $row['amount'];   
                    $total_win += $get_rates[$row['game']] * $row['amount'];          
                ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td><?php echo htmlspecialchars($fetch['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['amount']); ?></td>
                        <td><?php echo htmlspecialchars($get_rates[$row['game']]) * htmlspecialchars($row['amount']); ?></td>
                        <td><?php echo htmlspecialchars($row['bazar']); ?></td>
                        <td><?php echo htmlspecialchars($row['game']); ?></td>
                        <td><?php echo htmlspecialchars($row['number']); ?></td>
                        <td><?php echo date('h:i A d-m-Y', $row['created_at']); ?></td>
                     
                    </tr>
                <?php
                    $i++;
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- jQuery, Bootstrap JS, and DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.2.2/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.2/js/buttons.print.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#example1').DataTable({
                "paging": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "lengthChange": true,
                "pageLength": 10, // Adjust as needed
                dom: 'Bfrtip', // Position of the buttons
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });

            // Set total bid and winning values
            $("#totalBid").html('<?php echo $total_bid; ?>');
            $("#totalWin").html('<?php echo $total_win; ?>');
        });
    </script>
</body>
