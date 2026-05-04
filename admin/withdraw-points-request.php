<?php 
include('header.php');

if (in_array(13, $HiddenProducts)){

$whereConditions = [];
if (!empty($_POST['date_filter'])) {
    $selected_date = $_POST['date_filter'];
} elseif (!empty($_GET['date_filter'])) {
    $selected_date = $_GET['date_filter'];
} else {
    $selected_date = date('Y-m-d'); // Default to Today
        $selected_date = ''; // Default to Today
}
$search_user = '';

if (isset($_GET['type'])) {
    if ($_GET['type'] == 'request') {
        $whereConditions[] = "wr.status = 0";
    } elseif ($_GET['type'] == 'manual') {
        $whereConditions[] = "wr.status = 1";
    }
}
if (!empty($selected_date)) {
    $whereConditions[] = "DATE(wr.created_at) = '$selected_date'";
}
if (!empty($_GET['search_user'])) {
    $search_user = mysqli_real_escape_string($con, $_GET['search_user']);
    $whereConditions[] = "(
        wr.mobile LIKE '%$search_user%'
        OR wr.holder LIKE '%$search_user%'
        OR u.name LIKE '%$search_user%'
    )";
}

$sql = "SELECT wr.*, u.name
        FROM withdraw_requests wr
        LEFT JOIN users u ON wr.mobile = u.mobile";

if (!empty($whereConditions)) {
    $sql .= " WHERE " . implode(' AND ', $whereConditions);
}

$sql .= " ORDER BY wr.sn DESC";

$result = mysqli_query($con, $sql);
if (!$result) { die("SQL Error: " . mysqli_error($con)); }

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
        background-color: #fff;
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
        background-color: #fff;
        border: 1px solid #c0c0c0; /* ← visible row/column lines */
    }

    /* Alternate row shading for readability */
    .custom-table tbody tr:nth-child(even) td {
        background-color: #fff;
    }

    .user-name  { font-weight: bold; display: block; font-size: 13px; color: #000; }
    .user-mode  { color: #007bff; font-size: 12px; font-weight: bold; }
    .action-icons a { font-size: 17px; margin: 0 4px; }
    /* Screenshot 2 Table Styles */
    .text-accept { color: #28a745; font-weight: bold; } /* Green color for Accepted Date */
    .text-manual { color: #333; font-size: 11px; font-weight: 500; }
    .amount-trigger { cursor: pointer; }
    .amount-trigger:hover { background-color: #f8f9fa; }

    /* Screenshot 1 Modal Styles */
    #detailModal .modal-body p {
        margin-bottom: 12px;
        font-size: 14px;
        color: #333;
    }
    #detailModal .modal-body b {
        color: #000;
        font-weight: 700;
        display: inline-block;
        width: 130px; /* Aligns the labels */
    }
    /* Screenshot 2 Table Styles */
    .text-accept { color: #28a745 !important; font-weight: bold; } 
    .text-manual { color: #333; font-size: 11px; font-weight: 500; }
    .amount-trigger { cursor: pointer; transition: 0.2s; }
    .amount-trigger:hover { background-color: rgba(0,0,0,0.05); }

    /* Screenshot 1 Modal Styles */
    .modal-content { border-radius: 12px !important; }
    .modal-header { border-bottom: none; padding-bottom: 0; }
    .modal-body p { margin-bottom: 15px; font-size: 15px; color: #333; border-bottom: 1px solid #f8f8f8; padding-bottom: 5px;}
    .modal-body b { color: #000; font-weight: 800; display: inline-block; width: 150px; }
    .modal-header-name { font-weight: bold; font-size: 18px; }
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

                <a href="withdraw-money-manual.php" style="text-decoration:none;">
                    <div class="custom-header">Withdraw Money History</div>
                </a>
                <!--<form method="get" action="" autocomplete="off">-->
                <!--    <div class="search-container">-->
                <!--        <input type="text" id="userInput" class="search-input" name="search_user"-->
                <!--               placeholder="Search for a user"-->
                <!--               value="<?php echo htmlspecialchars($search_user); ?>"-->
                <!--               onkeyup="filterFunction()"-->
                <!--               onfocus="showDropdown()">-->

                <!--        <div id="userDropdown" class="custom-dropdown-list">-->
                <!--            <?php-->
                <!--                $u_query = mysqli_query($con, "SELECT name, mobile FROM users");-->
                <!--                while($u_row = mysqli_fetch_array($u_query)){-->
                <!--                    $display_text = $u_row['name'];-->
                <!--                    $mobile = $u_row['mobile'];-->
                <!--                    echo "<div class='dropdown-item' onclick='selectUser(\"$mobile\")'>";-->
                <!--                    echo "<span>$mobile</span><br><small>$display_text</small>";-->
                <!--                    echo "</div>";-->
                <!--                }-->
                <!--            ?>-->
                <!--        </div>-->
                <!--    </div>-->

                <!--    <div>-->
                <!--        <button type="submit" class="btn-filter-submit">Filter</button>-->
                <!--    </div>-->

                <!--    <?php if(isset($_GET['type'])) { ?>-->
                <!--        <input type="hidden" name="type" value="<?php echo $_GET['type']; ?>">-->
                <!--    <?php } ?>-->
                <!--</form>-->
                <form method="get" action="" autocomplete="off">
                        <!-- Row for Date and Search -->
                        <div class="row">
                            <div class="col-5">
                                <label class="search-label" style="font-size:12px; font-weight:600;">Date</label>
                                <input type="date" name="date_filter" class="search-input" value="<?php echo $selected_date; ?>">
                            </div>
                            <div class="col-7">
                                <label class="search-label" style="font-size:12px; font-weight:600;">User</label>
                                <div class="search-container">
                                    <input type="text" id="userInput" class="search-input" name="search_user"
                                           placeholder="Mobile or Name"
                                           value="<?php echo htmlspecialchars($search_user); ?>"
                                           onkeyup="filterFunction()"
                                           onfocus="showDropdown()">
                    
                                    <div id="userDropdown" class="custom-dropdown-list">
                                        <?php
                                            $u_query = mysqli_query($con, "SELECT name, mobile FROM users LIMIT 50");
                                            while($u_row = mysqli_fetch_array($u_query)){
                                                echo "<div class='dropdown-item' onclick='selectUser(\"".$u_row['mobile']."\")'>";
                                                echo "<span>".$u_row['mobile']."</span><br><small>".$u_row['name']."</small>";
                                                echo "</div>";
                                            }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                        <div class="mt-2">
                            <button type="submit" class="btn-filter-submit">Filter Now</button>
                        </div>
                    
                        <!-- Keep the current type (All/Request/Manual) active when filtering -->
                        <input type="hidden" name="type" value="<?php echo $type; ?>">
                    </form>

                <div class="filter-tabs-container">
                    <a href="withdraw-points-request.php" class="tab-btn">All</a>
                    <a href="withdraw-points-request.php?type=request" class="tab-btn">Request</a>
                    <a href="withdraw-points-request.php?type=manual" class="tab-btn">Manually</a>
                </div>

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
                                <div style="font-size:12px; color:#666;"><?php echo $i; ?></div>
                                <div class="open-user-modal" style="font-weight:bold; font-size:13px; cursor: pointer;" data-name="<?php echo htmlspecialchars($row['name']); ?>" data-mobile="<?php echo $row['mobile']; ?>">
                                     <?php echo htmlspecialchars($row['name']); ?>
                                </div>
                            </td>

                            <td class="text-center col-date">
                                <?php
                                    $dt = new DateTime($row['created_at']);
                                    if($row['status'] == 1) {
                                        echo "<div class='text-accept'>A-" . $dt->format('d-m-Y') . "</div>";
                                        echo "<div class='text-accept'>" . $dt->format('h:i A') . "</div>";
                                        echo "<div style='color:#777; font-size:12px;'>**</div>";
                                    } else {
                                        echo "<div style='font-weight:bold; font-size:12px;'>" . $dt->format('d-m-Y') . "</div>";
                                        echo "<div style='font-size:11px;'>" . $dt->format('h:i A') . "</div>";
                                    }
                                ?>
                            </td>

                            <!-- CLICK ON THIS CELL TO OPEN POPUP -->
                            <td class="text-center col-amount amount-trigger" data-toggle="modal" data-target="#ViewRequest<?php echo $i; ?>">
                                <div style="font-weight:bold; font-size:16px; color:#000;">
                                    <?php echo htmlspecialchars($row['amount']); ?>
                                </div>
                                <div class="text-manual"><?php echo ($row['status'] == 1) ? 'manually' : 'pending'; ?></div>
                            </td>
                        </tr>

                        <!-- View Modal -->
                        <!-- View Modal Matching Screenshot 1 -->
                        <div class="modal fade" id="ViewRequest<?php echo $i; ?>">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" style="font-weight:bold;">Name : <?php echo htmlspecialchars($row['name']); ?></h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body" style="padding: 25px;">
                                        <p><b>Amount :</b> <?php echo $row['amount']; ?></p>
                                        <p><b>A/c Holder Name :</b> <?php echo !empty($row['holder']) ? $row['holder'] : 'null'; ?></p>
                                        <p><b>Bank Name :</b> <?php echo !empty($row['mode']) ? $row['mode'] : 'null'; ?></p>
                                        <p><b>Bank A/c :</b> <?php echo !empty($row['ac']) ? $row['ac'] : 'null'; ?></p>
                                        <p><b>IFSC :</b> <?php echo !empty($row['ifsc']) ? $row['ifsc'] : 'null'; ?></p>
                                        <p><b>Request Time :</b> <span style="color:#777;"><?php echo !empty($row['created_at']) ? (new DateTime($row['created_at']))->format('d-m-Y h:i A') : 'null'; ?></span></p>
                                        <p><b>Accept Time :</b> <span class="text-accept"><?php echo ($row['status'] == 1) ? (new DateTime($row['created_at']))->format('d-m-Y h:i A') : 'null'; ?></span></p>
                                        
                                        <?php if(!empty($row['screenshot_with'])) { ?>
                                            <div class="mt-3 text-center">
                                                <img src="<?php echo $row['screenshot_with']; ?>" style="max-width:100%; border-radius:10px;">
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" style="border-radius:20px; padding: 8px 25px; background-color: #6c757d;" data-dismiss="modal">Close</button>
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
<!-- User Action Modal for Withdraw Section -->
<div class="modal fade" id="userActionModal" tabindex="-1" role="dialog" aria-labelledby="userActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header">
                <h5 class="modal-title" id="userNameHeader" style="font-weight:bold;">Name : </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div class="d-flex flex-wrap justify-content-center">
                    <a href="#" id="modalProfileBtn" class="btn btn-success rounded-pill m-1" style="min-width:100px;">Profile</a>
                    <a href="#" id="modalTransBtn" class="btn btn-info rounded-pill m-1" style="background-color: #17a2b8; border:none; min-width:100px;">Transaction</a>
                    <a href="#" id="modalCallBtn" class="btn btn-danger rounded-pill m-1" style="min-width:100px;">Call</a>
                    <a href="#" id="modalWhatsappBtn" class="btn btn-warning rounded-pill m-1" style="background-color: #ffc107; color: black; min-width:100px;">WhatsApp</a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" style="border-radius: 12px;" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
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
    
    $(document).ready(function() {
    // This handles the popup for BOTH Add Money and Withdraw Money history
    $('.open-user-modal').on('click', function() {
        var name = $(this).data('name');
        var mobile = $(this).data('mobile');

        // Update Modal Heading
        $('#userNameHeader').text('Name : ' + name);

        // Update Button Links
        $('#modalProfileBtn').attr('href', 'user-profile.php?userID=' + mobile);
        $('#modalTransBtn').attr('href', 'user-wallet-history.php?user_mobile=' + mobile);
        $('#modalCallBtn').attr('href', 'tel:' + mobile);
        $('#modalWhatsappBtn').attr('href', 'https://wa.me/91' + mobile);

        // Show the Modal
        $('#userActionModal').modal('show');
    });
});
</script>