<?php 
include('header.php');

if (in_array(13, $HiddenProducts)){

        $whereConditions = [];
        $search_user = '';
        
        /* DATE FILTER */
        
        if (!empty($_GET['date'])) {
            $date = mysqli_real_escape_string($con, $_GET['date']);
            $whereConditions[] = "DATE(wr.created_at) = '$date'";
        }
        
        
        /* STATUS FILTER */
        
        if (!empty($_GET['status'])) {
            
                $status = $_GET['status'];
            
                if ($status == 'send') {
                    $whereConditions[] = "wr.status = 0";
                }
            
                elseif ($status == 'processing') {
                    $whereConditions[] = "wr.status = 1";
                }
            
                elseif ($status == 'pending') {
                    $whereConditions[] = "wr.status = 0";
                }
            
                elseif ($status == 'attempt') {
                    $whereConditions[] = "wr.status = 3";
                }
            
                elseif ($status == 'manual') {
                    $whereConditions[] = "wr.status = 1";
                }
            
                elseif ($status == 'wrong') {
                    $whereConditions[] = "wr.status = 2";
                }
            
            }
            
            /* DEFAULT = PENDING */
            
            else {
                $whereConditions[] = "wr.status = 0";
            }
        
        
        /* SEARCH USER */
        
        if (!empty($_GET['search_user'])) {
        
            $search_user = mysqli_real_escape_string($con, $_GET['search_user']);
        
            $whereConditions[] = "(
                wr.mobile LIKE '%$search_user%'
                OR wr.holder LIKE '%$search_user%'
                OR u.name LIKE '%$search_user%'
            )";
        
        }
        
        
        /* MAIN QUERY */
        
        $sql = "SELECT wr.*, u.name
                FROM withdraw_requests wr
                LEFT JOIN users u ON wr.mobile = u.mobile";
        
        
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        $sql .= " ORDER BY wr.sn DESC";
        
        
        $result = mysqli_query($con, $sql);
        
        if (!$result) { 
            die("SQL Error: " . mysqli_error($con)); 
        }
        
        
        /* TOTAL AMOUNT */
        
        $total_withdraw_amount = 0;
        
        while ($calc = mysqli_fetch_array($result)) {
            $total_withdraw_amount += $calc['amount'];
        }
        
        mysqli_data_seek($result, 0);
?>

<style>
    body {
        background-color: #f4f6f9;
        font-family: 'Source Sans Pro', sans-serif;
    }

    .content-wrapper { overflow-x: hidden; }

    @media (max-width: 576px) {
        .content-wrapper { padding: 8px !important; }
        .container-fluid { padding-left: 6px !important; padding-right: 6px !important; }
    }

    /* ── Header ── */
    .custom-header {
        background-color: black;
        color: white;
        text-align: center;
        padding: 8px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 18px;
        margin-bottom: 12px;
        margin-top: 8px;
    }

    /* ── Search dropdown ── */
    .search-container {
        position: relative;
        width: 100%;
        margin-bottom: 8px;
    }

    .search-input {
        border-radius: 20px;
        border: 1px solid #ced4da;
        padding: 8px 15px;
        width: 100%;
        outline: none;
        font-size: 14px;
        background-color: #fff;
        height: 40px;
    }

    .custom-dropdown-list {
        display: none;
        position: absolute;
        background-color: white;
        width: 100%;
        max-height: 220px;
        overflow-y: auto;
        border: 1px solid #ddd;
        border-top: none;
        z-index: 1000;
        border-radius: 0 0 15px 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        left: 0;
    }

    .dropdown-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #f1f1f1;
        font-size: 13px;
        color: #333;
        text-align: left;
    }

    .dropdown-item:hover { background-color: #f1f1f1; }
    .dropdown-item span { font-weight: bold; color: #000; }

    /* ── Filter button ── */
    .btn-filter-submit {
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 20px;
        padding: 6px 24px;
        font-weight: bold;
        margin-bottom: 10px;
        display: inline-block;
        font-size: 13px;
    }

    /* ── Tab buttons ── */
    .filter-tabs-container {
        display: flex;
        gap: 6px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }

    .tab-btn {
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 20px;
        padding: 5px 16px;
        font-size: 13px;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }

    .tab-btn:hover { color: white; text-decoration: none; opacity: 0.9; }

    /* ── Total bar ── */
    .total-bar {
        background-color: black;
        color: orange;
        text-align: center;
        padding: 8px;
        font-weight: bold;
        font-size: 15px;
        border-radius: 8px;
        margin-bottom: 12px; /* ← gap between total and table */
    }

    /* ── Table ── */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #bbb; /* outer border */
    }

    .custom-table {
        width: 100%;
        background-color: #eee;
        border-collapse: collapse; /* makes lines visible */
    }

    .custom-table thead th {
        background-color: #ff9800;
        color: black;
        text-align: center;
        padding: 10px 8px;
        font-size: 13px;
        font-weight: bold;
        border: 1px solid #e08000; /* visible header lines */
    }

    .col-user   { width: 30%; text-align: left; padding-left: 10px; }
    .col-date   { width: 30%; }
    .col-amount { width: 40%; }

    .custom-table tbody td {
        padding: 10px 6px;
        vertical-align: middle;
        font-size: 13px;
        background-color: #e6e6e6;
        border: 1px solid #c0c0c0; /* ← visible row/column lines */
    }

    /* Alternate row shading for readability */
    .custom-table tbody tr:nth-child(even) td {
        background-color: #d8d8d8;
    }

    .user-name  { font-weight: bold; display: block; font-size: 13px; color: #000; }
    .user-mode  { color: #007bff; font-size: 12px; font-weight: bold; }
    .action-icons a { font-size: 17px; margin: 0 4px; }

    .filter-bar{
        display:flex;
        flex-wrap:wrap;
        gap:12px;
        background:#fff;
        padding:12px;
        border-radius:10px;
        margin-bottom:15px;
        border:1px solid #ddd;
        }
        
        .filter-item{
        display:flex;
        flex-direction:column;
        font-size:13px;
        }
        
        .filter-item input[type="date"],
        .filter-item input[type="text"]{
        padding:6px 10px;
        border:1px solid #ccc;
        border-radius:6px;
        min-width:160px;
        }
        
        .status-group{
        display:flex;
        flex-wrap:wrap;
        gap:10px;
        margin-top:5px;
        }
        
        .status-group label{
        font-size:12px;
        }
        
        .btn-area{
        justify-content:flex-end;
        }
        
        .btn-filter-submit{
        background:#007bff;
        color:#fff;
        border:none;
        padding:8px 20px;
        border-radius:20px;
        cursor:pointer;
        }
        
        /* Mobile adjustments */
        @media (max-width:768px){
        
        .filter-bar{
        flex-direction:column;
        }
        
        .status-group{
        gap:6px;
        }
        
        .btn-area{
        align-items:flex-start;
        }

}
    @media (max-width: 480px) {
        .custom-table thead th,
        .custom-table tbody td { font-size: 12px; padding: 8px 4px; }
        .action-icons a { font-size: 15px; margin: 0 3px; }
    }
</style>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-5 col-12">

                <div class="custom-header">Withdraw Money Request</div>

                <form method="get" action="" autocomplete="off">
                    <div class="filter-bar">

                    <!-- Date -->
                    <div class="filter-item">
                        <label>Date</label>
                        <input type="date" name="date" value="<?php echo $_GET['date'] ?? ''; ?>">
                    </div>
                
                    <!-- Search -->
                    <div class="filter-item">
                        <label>Search User</label>
                        <input type="text" name="search_user" placeholder="Mobile / Name"
                               value="<?php echo $_GET['search_user'] ?? ''; ?>">
                    </div>
                
                    <!-- Status -->
                   <div class="filter-item">
                        <label>Status</label>
                        
                        <select name="status" class="status-dropdown">
                        
                        <option value="">All</option>
                        
                        <option value="send" <?php if(($_GET['status'] ?? '')=='send') echo 'selected'; ?>>Send Request</option>
                        
                        <option value="processing" <?php if(($_GET['status'] ?? '')=='processing') echo 'selected'; ?>>Processing</option>
                        
                        <option value="pending" <?php if(($_GET['status'] ?? '')=='pending') echo 'selected'; ?>>Pending</option>
                        
                        <option value="attempt" <?php if(($_GET['status'] ?? '')=='attempt') echo 'selected'; ?>>Attempt</option>
                        
                        <option value="manual" <?php if(($_GET['status'] ?? '')=='manual') echo 'selected'; ?>>Manual</option>
                        
                        <option value="wrong" <?php if(($_GET['status'] ?? '')=='wrong') echo 'selected'; ?>>Wrong Detail</option>
                        
                        </select>
                        
                    </div>
                
                    <!-- Button -->
                    <div class="filter-item btn-area">
                        <button type="submit" class="btn-filter-submit">Filter</button>
                    </div>
                
                </div>
                </form>

                <!--<div class="filter-tabs-container">-->
                <!--    <a href="withdraw-points-request.php" class="tab-btn">All</a>-->
                <!--    <a href="withdraw-points-request.php?type=request" class="tab-btn">Request</a>-->
                <!--    <a href="withdraw-points-request.php?type=manual" class="tab-btn">Manually</a>-->
                <!--</div>-->

                <!-- Total — gap below via margin-bottom -->
                <div class="total-bar">Total: <?php echo $total_withdraw_amount; ?></div>

                <!-- Table — sits freely, lines now visible -->
                <div class="table-responsive p-0">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th class="col-user">User Detail</th>
                                <th class="col-date">Date / Time</th>
                                <th class="col-amount">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $i = 0;
                        if(mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_array($result)) {
                                $i++;
                                $user_id = $row['user'];
                                $holder  = !empty($row['holder']) ? $row['holder'] : 'Unknown';
                                if (!isset($userCounter[$holder])) { $userCounter[$holder] = 1; } else { $userCounter[$holder]++; }
                        ?>
                        <tr>
                            <td class="col-user" style="text-align:center;">
                                <div style="font-weight:bold; font-size:13px;"><?php echo $i; ?></div>
                                <div style="font-weight:bold; font-size:13px;"><?php echo htmlspecialchars($holder); ?></div>
                            </td>

                            <td class="text-center col-date">
                                <?php
                                    $dt = new DateTime($row['created_at']);
                                    echo "<div style='font-weight:bold; font-size:12px;'>" . $dt->format('d-m-Y') . "</div>";
                                    echo "<div style='font-size:11px;'>" . $dt->format('h:i A') . "</div>";
                                ?>
                            </td>

                            <td class="text-center col-amount">
                                <div style="font-weight:bold; font-size:15px; margin-bottom:2px;">
                                    <?php echo htmlspecialchars($row['amount']); ?>
                                </div>
                                <?php
                                    if($row['status'] == 1){
                                        echo "<span class='badge badge-success' style='font-size:10px;'>Accepted</span>";
                                    } elseif($row['status'] == 0){
                                        echo "<span class='badge badge-warning' style='font-size:10px;'>Pending</span>";
                                    } else {
                                        echo "<span class='badge badge-danger' style='font-size:10px;'>Rejected</span>";
                                    }
                                ?>
                                <div class="action-icons mt-1">
                                    <a href="#ViewRequest<?php echo $i; ?>" data-toggle="modal" style="color:#333;"><i class="fas fa-eye"></i></a>
                                    <?php if($row['status'] == 0){ ?>
                                        <a href="#RequestApproved<?php echo $i; ?>" data-toggle="modal" class="text-success"><i class="fas fa-check-circle"></i></a>
                                        <a href="#RequestRejected<?php echo $i; ?>" data-toggle="modal" class="text-danger"><i class="fas fa-times-circle"></i></a>
                                    <?php } ?>
                                </div>
                            </td>
                        </tr>

                        <!-- View Modal -->
                        <div class="modal fade" id="ViewRequest<?php echo $i; ?>">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Details</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-6 mb-2"><b>Name:</b><br><?php echo $row['name']; ?></div>
                                            <div class="col-6 mb-2"><b>Mobile:</b><br><?php echo $user_id; ?></div>
                                            <div class="col-6 mb-2"><b>Amount:</b><br><?php echo $row['amount']; ?></div>
                                            <div class="col-6 mb-2"><b>Mode:</b><br><?php echo $row['mode']; ?></div>
                                            <div class="col-12"><hr></div>
                                            <div class="col-6 mb-2"><b>A/C No:</b><br><?php echo $row['ac']; ?></div>
                                            <div class="col-6 mb-2"><b>IFSC:</b><br><?php echo $row['ifsc']; ?></div>
                                            <div class="col-12 mb-2"><b>Holder:</b><br><?php echo $row['holder']; ?></div>
                                            <?php if(!empty($row['screenshot_with'])) { ?>
                                            <div class="col-12 mt-3 text-center border p-2">
                                                <strong>Proof:</strong><br>
                                                <img src="<?php echo htmlspecialchars($row['screenshot_with']); ?>" style="max-width:100%; height:auto;">
                                            </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reject Modal -->
                        <div class="modal fade" id="RequestRejected<?php echo $i; ?>">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h4 class="modal-title">Reject Request</h4>
                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="post">
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['sn']); ?>">
                                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                                            <p class="text-center">Are you sure you want to <b>REJECT</b> this request?</p>
                                            <button class="btn btn-danger btn-block" type="submit" name="requestRejected">Confirm Reject</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Approve Modal -->
                        <div class="modal fade" id="RequestApproved<?php echo $i; ?>">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-success text-white">
                                        <h4 class="modal-title">Approve Request</h4>
                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="post" enctype="multipart/form-data">
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['sn']); ?>">
                                            <input type="hidden" name="amount" value="<?php echo htmlspecialchars($row['amount']); ?>">
                                            <div class="form-group">
                                                <label>Upload Screenshot (Optional)</label>
                                                <input type="file" class="form-control" name="fileToUpload">
                                            </div>
                                            <button class="btn btn-success btn-block" type="submit" name="requestApproved">Confirm Approve</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php } }  else { echo "<tr><td colspan='3' class='text-center p-4'>No data found.</td></tr>"; } ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</section>

<?php
    if(isset($_POST['requestRejected'])){
        $id = $_POST['id'];
        $info = mysqli_fetch_array(mysqli_query($con,"select user, amount from withdraw_requests where sn='$id'"));
        $mobile = $info['user'];
        $amount = $info['amount'];
        mysqli_query($con,"update withdraw_requests set status='2' where sn='$id'");
        mysqli_query($con,"UPDATE users set wallet=wallet+$amount where mobile='$mobile'");
        mysqli_query($con,"INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `owner`, `created_at`) VALUES ('$mobile','$amount','1','Withdraw cancelled','user','$stamp')");
        log_action('Withdraw request rejected ' . $id);
        echo "<script>window.location.href= 'withdraw-points-request.php';</script>";
    }

    if (isset($_POST['requestApproved'])) {
        $id = $_POST['id'];
        $pointsAdd = $_POST['amount'];
        $target_file = '';
        if (isset($_FILES["fileToUpload"]) && $_FILES["fileToUpload"]["error"] == 0) {
            $target_dir = "../upload/";
            $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
            move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
        }
        $query = "UPDATE withdraw_requests SET status='1'";
        if ($target_file) $query .= ", screenshot_with='$target_file'";
        $query .= " WHERE sn='$id'";
        mysqli_query($con, $query);
        $uInfo = mysqli_fetch_array(mysqli_query($con, "SELECT user FROM withdraw_requests WHERE sn='$id'"));
        $mobile = $uInfo['user'];
        mysqli_query($con, "INSERT INTO `transactions`(`user`, `amount`, `type`, `remark`, `owner`, `created_at`) VALUES ('$mobile','$pointsAdd','0','Withdraw to Bank','user','$stamp')");
        log_action('Withdraw request Approved ' . $id);
        echo "<script>window.location.href= 'withdraw-points-request.php';</script>";
    }
} else {
    echo "<script>window.location.href = 'unauthorized.php';</script>";
    exit();
}
include('footer.php');
?>

<script>
    function showDropdown() {
        document.getElementById("userDropdown").style.display = "block";
    }

    window.onclick = function(event) {
        if (!event.target.matches('.search-input')) {
            var dropdowns = document.getElementsByClassName("custom-dropdown-list");
            for (var i = 0; i < dropdowns.length; i++) {
                if (dropdowns[i].style.display === "block") {
                    dropdowns[i].style.display = "none";
                }
            }
        }
    }

    function filterFunction() {
        var input  = document.getElementById("userInput");
        var filter = input.value.toUpperCase();
        var div    = document.getElementById("userDropdown");
        var items  = div.getElementsByClassName("dropdown-item");
        div.style.display = "block";
        for (var i = 0; i < items.length; i++) {
            var span  = items[i].getElementsByTagName("span")[0];
            var small = items[i].getElementsByTagName("small")[0];
            if (span || small) {
                var mobileTxt = span.textContent || span.innerText;
                var nameTxt   = small.textContent || small.innerText;
                items[i].style.display = (mobileTxt.toUpperCase().indexOf(filter) > -1 || nameTxt.toUpperCase().indexOf(filter) > -1) ? "" : "none";
            }
        }
    }

    function selectUser(mobile) {
        document.getElementById("userInput").value = mobile;
        document.getElementById("userDropdown").style.display = "none";
    }
</script>