<?php include('header.php'); 
if (!empty($_POST['date_filter'])) {
    $selected_date = $_POST['date_filter'];
} elseif (!empty($_GET['date_filter'])) {
    $selected_date = $_GET['date_filter'];
} else {
    // $selected_date = date('Y-m-d'); // Default to Today
    $selected_date = ''; // Now it defaults to empty (All Dates)
}

if (isset($_POST['UpdatePaymentAmt'])) {
    $pay_id = mysqli_real_escape_string($con, $_POST['pay_id']);
    $new_amount = (float)$_POST['new_amount'];

    // 1. Get original payment data
    $pay_res = mysqli_query($con, "SELECT * FROM payments WHERE id = '$pay_id' LIMIT 1");
    $pay_row = mysqli_fetch_assoc($pay_res);

    if ($pay_row) {
        $old_amount = (float)$pay_row['amount'];
        $user_mobile = $pay_row['mobile'];
        $diff = $new_amount - $old_amount; // e.g. 400 - 500 = -100

        // 2. Get current user wallet balance
        $user_res = mysqli_query($con, "SELECT wallet FROM users WHERE mobile = '$user_mobile' LIMIT 1");
        $user_row = mysqli_fetch_assoc($user_res);
        $wallet_before = (float)$user_row['wallet'];
        $wallet_after = $wallet_before + $diff;

        // Start Transaction
        mysqli_begin_transaction($con);
        try {
            // Update Payment Table
            mysqli_query($con, "UPDATE payments SET amount = '$new_amount' WHERE id = '$pay_id'");

            // Update User Wallet
            mysqli_query($con, "UPDATE users SET wallet = '$wallet_after' WHERE mobile = '$user_mobile'");

            // Insert into Transactions Table
            $type = ($diff >= 0) ? 1 : 0; // 1 = Receive/Add, 0 = Deduct
            $abs_diff = abs($diff);
            $remark = "Amt Change: $old_amount to $new_amount";
            
            mysqli_query($con, "INSERT INTO transactions 
                (user, amount, wallet_before, wallet_after, type, remark, created_at, dated_on) 
                VALUES 
                ('$user_mobile', '$abs_diff', '$wallet_before', '$wallet_after', '$type', '$remark', NOW(), NOW())");

            mysqli_commit($con);
            echo "<script>alert('Updated Successfully! Total Change: $diff'); window.location.href=window.location.href;</script>";
        } catch (Exception $e) {
            mysqli_rollback($con);
            echo "<script>alert('Error updating database');</script>";
        }
    }
}

if (in_array(13, $HiddenProducts)){
    $pdf_params = http_build_query([
    'type' => isset($_GET['type']) ? $_GET['type'] : 'all',
    'user_id' => isset($_POST['user_id']) ? $_POST['user_id'] : (isset($_GET['user_id']) ? $_GET['user_id'] : ''),
    'utr' => isset($_POST['utr']) ? $_POST['utr'] : (isset($_GET['utr']) ? $_GET['utr'] : '')
]);
?>

<style>
    :root {
        --primary-blue: #007bff;
        --success-green: #28a745;
        --warning-orange: #ff9800;
        --dark-bg: #000000;
        --light-grey: #f4f6f9;
    }

    body { background-color: var(--light-grey); }

    /* ── Content wrapper ── */
    .content-wrapper { overflow-x: hidden; }

    @media (max-width: 576px) {
        .content-wrapper { padding: 8px !important; }
        .container-fluid  { padding-left: 6px !important; padding-right: 6px !important; }
    }

    /* ── Header banner ── */
    .header-banner {
        background-color: var(--dark-bg);
        color: white;
        border-radius: 50px;
        text-align: center;
        padding: 9px 16px;
        margin-bottom: 14px;
        font-weight: bold;
        font-size: 1.1rem;
    }

    /* ── Form labels ── */
    .search-label {
        font-weight: 500;
        color: #555;
        margin-bottom: 4px;
        font-size: 13px;
        display: block;
    }

    /* ── Rounded inputs ── */
    .rounded-input {
        border-radius: 20px !important;
        border: 1px solid #ccc;
        padding: 8px 14px;
        font-size: 13px;
        height: 38px;
    }

    /* ── Select2 override ── */
    .select2-container--bootstrap4 .select2-selection--single {
        border-radius: 20px !important;
        height: 38px !important;
        font-size: 13px;
    }

    /* ── Filter button (half width) ── */
    .btn-filter {
        background-color: var(--primary-blue);
        color: white;
        border-radius: 20px;
        padding: 7px 0;
        border: none;
        font-weight: 500;
        font-size: 13px;
        width: 50%;
        margin-bottom: 8px;
        display: block;
    }

    /* ── Type filter pills row ── */
    .type-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 8px;
    }

    .type-pill {
        background-color: var(--primary-blue);
        color: white;
        border-radius: 20px;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        white-space: nowrap;
    }

    /* ── Download PDF button ── */
    .btn-download {
        background-color: var(--success-green);
        color: white;
        border-radius: 20px;
        padding: 6px 18px;
        border: none;
        font-size: 12px;
        font-weight: 500;
    }

    /* ── Total banner ── */
    .total-banner {
        background-color: var(--dark-bg);
        color: var(--warning-orange);
        text-align: center;
        padding: 7px 10px;
        margin: 12px 0 0;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 6px;
    }

    /* ── Table ── */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        margin-bottom: 0;
        font-size: 13px;
        min-width: 520px;
    }

    .table-header-custom {
        background-color: var(--warning-orange);
        color: black;
        font-weight: bold;
    }

    .table thead th,
    .table tbody td {
        padding: 8px 6px;
        vertical-align: middle;
        text-align: center;
        font-size: 12px;
    }

    /* ── Sticky footer nav ── */
    .footer-nav {
        position: sticky;
        bottom: 12px;
        display: flex;
        gap: 8px;
        padding: 0 8px;
        z-index: 1000;
        margin-top: 10px;
    }

    .nav-pill {
        height: 40px;
        border-radius: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 13px;
        color: #fff;
        white-space: nowrap;
    }

    .nav-prev, .nav-next {
        background-color: #17a2b8;
        flex: 1;
    }

    .nav-count {
        background-color: #6c757d;
        padding: 0 16px;
        flex-shrink: 0;
    }
    .table tbody td {
        padding: 8px 6px;
        vertical-align: middle;
        text-align: center;
        font-size: 12px;
        background-color: #ffffff !important; 
    }

    .table tbody td.bg-yellow {
        background-color: yellow !important;
        color: black !important;
        font-weight: bold !important;
    }
    /* ── Spacer before sticky nav ── */
    .nav-spacer { height: 60px; }
</style>

<section class="content pt-2">
    <div class="container-fluid">

        <!-- Header -->
            <a href="add-money-manual.php" style="text-decoration:none;">
                <div class="header-banner" style="cursor:pointer;">Add Money History</div>
            </a>
        <!-- Filters -->
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="row">
                 <div class="col-4">
                    <label class="search-label">Date</label>
                    <input type="date" name="date_filter" class="form-control rounded-input" value="<?php echo $selected_date; ?>">
                </div>
                <div class="col-6">
                    <label class="search-label">User</label>
                    <select name="user_id" class="form-control select2bs4 rounded-input">
                        <option value="" disabled selected>Search user</option>
                        <?php
                            $query_str = ($idd != 'admin@gmail.com') ?
                                "SELECT * FROM `users` WHERE refcode = '$refcodeq' ORDER BY name ASC" :
                                "SELECT * FROM `users` ORDER BY name  ASC";
                            $user_res = mysqli_query($con, $query_str);
                            while ($row = mysqli_fetch_array($user_res)) {
                                echo '<option value="' . htmlspecialchars($row['mobile']) . '">' . htmlspecialchars($row['name']) . '</option>';
                            }
                        ?>
                    </select>
                </div>
                <div class="col-6">
                    <label class="search-label">Search UTR</label>
                    <!--<input type="text" class="form-control rounded-input" placeholder="UTR Search">-->
                    <input type="text" name="utr" class="form-control rounded-input" placeholder="UTR Search" value="<?php echo $_POST['utr'] ?? ''; ?>">
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-12">
                    <!-- Filter button — compact -->
                    <button type="submit" name="AddPoints" class="btn-filter">Filter</button>

                    <!-- Type pills -->
                   <!--<div class="type-pills">-->
                   <!--     <a href="?type=all" class="type-pill">All</a>-->
                   <!--     <a href="?type=upi" class="type-pill">Upi</a>-->
                   <!--     <a href="?type=gateway" class="type-pill">Gateway</a>-->
                   <!--     <a href="?type=gateway_manual" class="type-pill">Gateway Manually</a>-->
                   <!--     <a href="?type=manual" class="type-pill">Manually</a>-->
                   <!-- </div>-->
                  <div class="type-pills mt-2">
                        <?php 
                        // This variable carries your current filters into the pill links
                        $params = "&date_filter=$selected_date&user_id=$user_id&utr=$utr"; 
                        ?>
                        <a href="?type=all<?php echo $params; ?>" class="type-pill">All</a>
                        <a href="?type=upi<?php echo $params; ?>" class="type-pill">Upi</a>
                        <a href="?type=gateway<?php echo $params; ?>" class="type-pill">Gateway</a>
                        <a href="?type=gateway_manual<?php echo $params; ?>" class="type-pill">Gateway Manually</a>
                        <a href="?type=manual<?php echo $params; ?>" class="type-pill">Manually</a>
                    </div>


                    <!-- Download -->
                    <a href="download_pdf.php?<?php echo $pdf_params; ?>" class="btn-download" target="_blank">Download PDF</a>


                </div>
            </div>
        </form>
        
<?php
$totalQuery = mysqli_query($con,
    "SELECT SUM(amount) as total_amount 
     FROM payments 
     WHERE status='SUCCESS'"
);

$totalData = mysqli_fetch_assoc($totalQuery);
$total_amount = $totalData['total_amount'] ?? 0;
?>

<?php
 /*   $perPage = 25;
$whereClause = "";
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$selected_date = $_POST['date_filter'] ?? $_GET['date_filter'] ?? date('Y-m-d');
$user_id = $_POST['user_id'] ?? $_GET['user_id'] ?? '';
$utr = $_POST['utr'] ?? $_GET['utr'] ?? '';

if ($type == 'upi') {
    $whereClause = "WHERE payment_id IS NOT NULL AND payment_id != ''";
}
elseif ($type == 'manual') {
    $whereClause = "WHERE payment_id IS NULL OR payment_id = ''";
}
elseif ($type == 'gateway') {
    $whereClause = "WHERE payment_id IS NOT NULL AND payment_id != ''";
}
elseif ($type == 'gateway_manual') {
    $whereClause = "WHERE payment_id IS NULL OR payment_id = ''";
}
// current page
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;

// total records count (NO LIMIT HERE)
$countSql = "SELECT COUNT(*) as total FROM payments $whereClause";
// echo $countSql;
$countQuery = mysqli_query($con, $countSql);

$countData = mysqli_fetch_assoc($countQuery);
$totalRecords = $countData['total'];

$totalPages = ($totalRecords > 0) ? ceil($totalRecords / $perPage) : 1;

if ($currentPage > $totalPages) $currentPage = $totalPages;

$start = ($currentPage - 1) * $perPage;



$userFilter = "";

if (!empty($_POST['user_id'])) {
    $user = mysqli_real_escape_string($con, $_POST['user_id']);
    $userFilter = " WHERE  mobile='$user'";
}

$utrFilter = "";

if (!empty($_POST['utr'])) {
    $utr = mysqli_real_escape_string($con, $_POST['utr']);
    $utrFilter = " AND payment_id LIKE '%$utr%'";
}
$sql = "SELECT * FROM payments 
        $whereClause
        $userFilter
        $utrFilter
        ORDER BY id DESC 
        LIMIT $start, $perPage";
// echo $sql;        
// $sql = "SELECT * FROM payments 
//         $whereClause
//         ORDER BY id DESC 
//         LIMIT $start, $perPage";


$result = mysqli_query($con, $sql);
if (!$result) { die(mysqli_error($con)); }*/
?>

<?php
$perPage = 25;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;

// 1. Get all filter values
$type = $_GET['type'] ?? 'all';
// $selected_date = $_POST['date_filter'] ?? $_GET['date_filter'] ?? date('Y-m-d');

$user_id = $_POST['user_id'] ?? $_GET['user_id'] ?? '';
$utr = $_POST['utr'] ?? $_GET['utr'] ?? '';

// 2. Build the array of conditions
$conditions = [];

// Always apply Date Filter
if (!empty($selected_date)) {
    $conditions[] = "DATE(created_at) = '$selected_date'";
}

// HANDLE ALL 5 PILL TYPES
if ($type == 'upi' || $type == 'gateway') {
    // Show only payments with a Gateway ID
    $conditions[] = "(payment_id IS NOT NULL AND payment_id != '')";
} 
elseif ($type == 'manual' || $type == 'gateway_manual') {
    // Show only manual payments (No Gateway ID)
    $conditions[] = "(payment_id IS NULL OR payment_id = '')";
}
// Note: 'all' adds no extra ID condition

// USER FILTER
if (!empty($user_id)) {
    $user_clean = mysqli_real_escape_string($con, $user_id);
    $conditions[] = "mobile = '$user_clean'";
}

// UTR FILTER
if (!empty($utr)) {
    $utr_clean = mysqli_real_escape_string($con, $utr);
    $conditions[] = "payment_id LIKE '%$utr_clean%'";
}

// 3. Construct the WHERE clause
$whereClause = "";
if (count($conditions) > 0) {
    $whereClause = " WHERE " . implode(' AND ', $conditions);
}

// 4. Update the Total Query to match the filters on screen
$totalQuery = mysqli_query($con, "SELECT SUM(amount) as total_amount FROM payments $whereClause " . (empty($whereClause) ? "WHERE status='SUCCESS'" : " AND status='SUCCESS'"));
$totalData = mysqli_fetch_assoc($totalQuery);
$total_amount = $totalData['total_amount'] ?? 0;

// 5. Pagination count
$countSql = "SELECT COUNT(*) as total FROM payments $whereClause";
$countQuery = mysqli_query($con, $countSql);
$totalRecords = mysqli_fetch_assoc($countQuery)['total'];
$totalPages = ($totalRecords > 0) ? ceil($totalRecords / $perPage) : 1;
$start = ($currentPage - 1) * $perPage;

// 6. Final SQL Execution
$sql = "SELECT * FROM payments $whereClause ORDER BY id DESC LIMIT $start, $perPage";
$result = mysqli_query($con, $sql);
if (!$result) { die(mysqli_error($con)); }
?>

        <!-- Total -->
        <div class="total-banner">Total: <?php echo $total_amount; ?></div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-bordered mt-0">
                <thead class="table-header-custom text-center">
                    <tr>
                        <th>User Detail</th>
                        <th>Bank Time</th>
                        <th>Amount</th>
                        <th>Note</th>
                        <th>Change</th>
                        <th>Total</th>
                        <th>Accept Time</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $i = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    $i++;
                    $created = new DateTime($row['created_at']);
                    $updated = new DateTime($row['updated_at']);
                   if (!empty($row['payment_id'])) {
    $note = "Add By UPI<br><small style='font-size:11px; color:#555;'>".$row['payment_id']."</small>";
} else {
    $note = "Add Manually";
}

                ?>
                <tr>
                    <td>
                        <div style="font-weight:bold;"><?php echo $i; ?></div>
                        <!-- Add class and data attributes here -->
                        <div class="open-user-modal" 
                             style="font-weight:bold; color: #007bff; cursor: pointer;" 
                             data-name="<?php echo htmlspecialchars($row['name']); ?>" 
                             data-mobile="<?php echo $row['mobile']; ?>">
                             <?php echo htmlspecialchars($row['name']); ?>
                        </div>
                    </td>
                    <td><?php echo $created->format('d/m/Y h:i A'); ?></td>
                   <!--<td <?php //if (!empty($row['payment_id'])) echo 'style="background-color: yellow; color: black; font-weight: bold;"'; ?>>-->
   <td class="<?php echo !empty($row['payment_id']) ? 'bg-yellow' : ''; ?>">
    <?php 
        echo $row['amount'];
        if (!empty($row['payment_id'])) {
            echo " GATEWAY";
        }
    ?>
</td>

                    <td><?php echo $note; ?></td>
                    <td>
                        <span class="open-change-modal" 
                              style="color:#007bff; font-weight:600; cursor:pointer;"
                              data-name="<?php echo htmlspecialchars($row['name']); ?>" 
                              data-amount="<?php echo $row['amount']; ?>"
                              data-id="<?php echo $row['id']; ?>">
                            Change
                        </span>
                    </td>

                    <td><?php echo $row['amount']; ?></td>
                    <td><?php echo $updated->format('d/m/y h:i A'); ?></td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="nav-spacer"></div>

    </div>
</section>
<!-- User Action Modal -->
<div class="modal fade" id="userActionModal" tabindex="-1" role="dialog" aria-labelledby="userActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userNameHeader">Name : </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="#" id="modalProfileBtn" class="btn btn-success rounded-pill m-1">Profile</a>
                    <a href="#" id="modalTransBtn" class="btn btn-info rounded-pill m-1" style="background-color: #17a2b8; border:none;">Transaction</a>
                    <a href="#" id="modalCallBtn" class="btn btn-danger rounded-pill m-1">Call</a>
                    <a href="#" id="modalWhatsappBtn" class="btn btn-warning rounded-pill m-1" style="background-color: #ffc107; color: black;">WhatsApp</a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" style="border-radius: 12px; background-color: #6c757d;" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- The Popup (Modal) -->
<div class="modal fade" id="updateResultModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 10px;">
            <div class="modal-body text-center">
                <p style="font-size: 18px; color: #555;">
                    Update Result<br>
                    <span id="display_name" style="font-weight:bold;"></span> for Amount <span id="display_amount"></span>
                </p>
                <input type="number" id="input_new_amount" class="form-control mb-4" style="height: 50px; font-size: 20px; text-align:center;">
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-primary" id="confirmUpdateBtn" style="background-color: #8e7cc3; width: 80px; margin-right: 10px;">OK</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="width: 80px;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Form to submit data -->
<form id="hiddenUpdateForm" method="POST" style="display:none;">
    <input type="hidden" name="pay_id" id="form_pay_id">
    <input type="hidden" name="new_amount" id="form_new_amount">
    <input type="hidden" name="UpdatePaymentAmt" value="1">
</form>
<style>
    /* Styling to match your image exactly */
    #userActionModal .modal-title { font-weight: bold; color: #333; }
    #userActionModal .btn { 
        padding: 8px 20px; 
        font-size: 14px; 
        font-weight: 500;
        min-width: 100px;
    }
    #userActionModal .modal-content { border-radius: 15px; }
</style>
<!-- Sticky pagination nav -->
<!--<div class="footer-nav">-->
<!--    <a href="?page=<?php echo ($currentPage > 1) ? $currentPage - 1 : 1; ?>" -->
<!--   class="nav-pill nav-prev">PREVS</a>-->

<!--    <div class="nav-pill nav-count"><?php echo $currentPage . " / " . $totalPages; ?></div>-->
<!--    <a href="?page=<?php echo ($currentPage < $totalPages) ? $currentPage + 1 : $totalPages; ?>" -->
<!--   class="nav-pill nav-next">NEXT</a>-->

<!--</div>-->
<div class="footer-nav">
    <?php 
    // This keeps your filters active when you click Next/Prev
    $nav_params = "type=$type&date_filter=$selected_date&user_id=$user_id&utr=$utr"; 
    ?>
    <a href="?page=<?php echo ($currentPage > 1 ? $currentPage - 1 : 1); ?>&<?php echo $nav_params; ?>" class="nav-pill nav-prev">PREVS</a>
    <div class="nav-pill nav-count"><?php echo $currentPage . " / " . $totalPages; ?></div>
    <a href="?page=<?php echo ($currentPage < $totalPages ? $currentPage + 1 : $totalPages); ?>&<?php echo $nav_params; ?>" class="nav-pill nav-next">NEXT</a>
</div>

<?php
    if (isset($_POST['AddPoints'])) {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            echo "<script>alert('Invalid CSRF token.');</script>";
        } else {
            // $user_id = htmlspecialchars($_POST['user_id']); 
            // $pointsAdd = filter_var($_POST['pointsAdd'], FILTER_VALIDATE_INT); 

            // if ($pointsAdd === false || $pointsAdd <= 0) {
            //     echo "<script>alert('Invalid points.');</script>";
            //     return;
            // }
            // ... (Your original logic code remains here) ...
        }
    }
} else { 
    echo "<script>window.location.href = 'unauthorized.php';</script>";
    exit();
}
include('footer.php'); ?>

<script>
    $(function () {
        $('.select2bs4').select2({ theme: 'bootstrap4' });
    });
    
    $(document).ready(function() {
    // Select2 initialization (existing)
    $('.select2bs4').select2({ theme: 'bootstrap4' });

    // Handle User Click for Modal
    $('.open-user-modal').on('click', function() {
        var name = $(this).data('name');
        var mobile = $(this).data('mobile');

        // 1. Set the Title
        $('#userNameHeader').text('Name : ' + name);

        // 2. Update the Links
        $('#modalProfileBtn').attr('href', 'user-profile.php?userID=' + mobile);
        $('#modalTransBtn').attr('href', 'user-wallet-history.php?user_mobile=' + mobile); // Adjust filename if different
        $('#modalCallBtn').attr('href', 'tel:' + mobile);
        $('#modalWhatsappBtn').attr('href', 'https://wa.me/91' + mobile); // Added 91 for India, change if needed

        // 3. Show the Modal
        $('#userActionModal').modal('show');
    });
});

$(document).ready(function() {
    // 1. When 'Change' is clicked: Open Modal
    $('.open-change-modal').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var amount = $(this).data('amount');

        $('#display_name').text(name);
        $('#display_amount').text(amount);
        
        // --- CHANGED HERE: Clear the input so it is empty ---
        $('#input_new_amount').val(''); 
        
        $('#form_pay_id').val(id); // Keep the ID so we know which record to update
        $('#updateResultModal').modal('show');
    });

    // 2. When 'OK' is clicked: Submit
    $('#confirmUpdateBtn').on('click', function() {
        var newAmt = $('#input_new_amount').val();
        
        // Validation to ensure they typed something
        if(newAmt === "" || newAmt === null) { 
            alert("Please enter a new amount"); 
            return; 
        }
        
        $('#form_new_amount').val(newAmt);
        $('#hiddenUpdateForm').submit();
    });
});
</script>