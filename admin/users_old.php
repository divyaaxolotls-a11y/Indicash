<?php 
$page = basename($_SERVER['PHP_SELF']); 
ini_set('display_errors', 1);
error_reporting(E_ALL);
include('header.php'); 

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$idd = $_SESSION['userID'];

// Permission Check
$sql = "SELECT * FROM admin WHERE email='". $idd ."'";
$result = $con->query($sql);
$row = $result->fetch_assoc();
$rol = $row['tasks'];
$sql2 = "SELECT * FROM task_manager WHERE id='". $rol ."'";
$result2 = $con->query($sql2);
$row2 = $result2->fetch_assoc();
$HiddenProducts = explode(',',$row2['tasks']);

if (in_array(4, $HiddenProducts)){

// 1. AJAX Toggle Status Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_toggle_status'])) {
    $mobile = $_POST['mobile'];
    $newStatus = $_POST['status'];
    $update_sql = "UPDATE users SET active = '$newStatus' WHERE mobile = '$mobile'";
    $update_result = mysqli_query($con, $update_sql);
    if ($update_result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

// 2. Create Account Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_account'])) {
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $active = 1; $wallet = 0;
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $plain_password = $password;

    $created_at = time();
    // $stmt = $con->prepare("INSERT INTO users (name, mobile, password, created_at, active, wallet) VALUES (?, ?, ?, ?, ?, ?)");
    // $stmt->bind_param("sssiii", $username, $phone, $hashed_password, $created_at, $active, $wallet);
    $stmt = $con->prepare("INSERT INTO users (name, mobile, password, plain_password, created_at, active, wallet) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiis", $username, $phone, $hashed_password, $plain_password, $created_at, $active, $wallet);

    if($stmt->execute()) { $success_message = "Account created successfully!"; }
    $stmt->close();
}

// 3. Delete User Logic
if (isset($_GET['deleteUser'])) {
    $sn = mysqli_real_escape_string($con, $_GET['deleteUser']);
    $con->query("DELETE FROM users WHERE sn = '$sn'");
    echo "<script>window.location.href='users_old.php';</script>";
    exit;
}
?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<?php
// Stickiness for Select2
$u_search = isset($_GET['user_search']) ? mysqli_real_escape_string($con, $_GET['user_search']) : '';
$text_search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : ''; // Add this line
$user_display_info = "";
if($u_search != "") {
    $u_info_res = mysqli_query($con, "SELECT name FROM users WHERE mobile='$u_search'");
    $u_info_data = mysqli_fetch_assoc($u_info_res);
    $user_display_info = ($u_info_data['name'] ?? 'User') . " ($u_search)";
}
?>
<style>
    body { background-color: #f0f2f5; font-family: 'Source Sans Pro', sans-serif; }
    
    .main-wrapper { 
        padding: 10px; 
        width: 100%; 
        /*max-width: 1100px; */
        margin: auto; 
    }
    
    /* --- Filter Area --- */
    .filter-row {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
        align-items: center;
    }
    .limit-select {
        width: 80px;
        height: 42px;
        border-radius: 8px;
        border: 1px solid #ccc;
        padding-left: 10px;
        font-weight: bold;
    }
    .search-input {
        flex: 1;
        height: 42px;
        border-radius: 8px;
        border: 1px solid #ccc;
        padding: 0 15px;
        font-size: 15px;
    }

    /* --- Action Buttons --- */
    .btn-container {
        display: flex;
        gap: 12px;
        justify-content: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .pill-btn {
        border-radius: 12px;
        padding: 10px 25px;
        font-weight: bold;
        border: none;
        color: white;
        font-size: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        cursor: pointer;
        min-width: 120px;
        text-align: center;
        text-decoration: none !important;
    }
    .btn-red { background: #dc3545; }
    .btn-amber { background: #ffc107; color: black; }
    .btn-green { background: #28a745; }

    /* --- Table Styling --- */
    .table-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .table-header {
        background-color: #1a66cc; /* Deep Blue from SC */
        color: white;
    }
    .table thead th {
        border: none;
        /*text-transform: lowercase;*/
        font-weight: 500;
        text-align: center;
        padding: 15px 5px;
        font-size: 15px;
    }
    .table tbody td {
        vertical-align: middle;
        text-align: center;
        padding: 15px 8px;
        border-top: 1px solid #f0f0f0;
        font-size: 14px;
    }

    /* Specific Row/Cell Colors */
    .sn-col { color: #007bff; font-weight: bold; font-size: 16px; }
    .username-col { font-weight: 600; color: #333; }
    .point-col { color: #007bff; font-weight: bold; }

    /* Status Pill */
    .status-btn {
        border-radius: 20px;
        padding: 4px 15px;
        font-size: 12px;
        font-weight: bold;
        color: white;
        display: inline-block;
        cursor: pointer;
        border: none;
    }
    .status-active { background-color: #28a745; }
    .status-inactive { background-color: #dc3545; }

    /* Icons */
    .action-stack {
        display: flex;
        flex-direction: column;
        gap: 12px;
        align-items: center;
    }
    .icon-btn { font-size: 18px; cursor: pointer; }
    .icon-view { color: #17a2b8; }
    .icon-delete { color: #dc3545; }
    /* Admin Action Popup Styles */
.btn-pill { border-radius: 50px; padding: 10px 20px; font-weight: bold; color: white !important; border: none; margin: 5px; font-size: 13px; display: inline-block; text-decoration: none !important; }
.bg-manual-add { background-color: #4CAF50; }
.bg-manual-withdraw { background-color: #E91E63; }
.bg-game-cancel { background-color: #FFC107; color: black !important; }
.bg-profit-loss { background-color: #00ACC1; }
.bg-notification { background-color: #007BFF; }
.bg-activity-log { background-color: #0097A7; }
.modal-round { border-radius: 20px !important; }
.btn-close-gray { background-color: #6c757d; border-radius: 25px; padding: 8px 40px; color: white; border: none; }

/* Modification for Inactive Row Colors */
.inactive-user td { 
    background-color: #ffe5e5 !important; /* This matches the light pink/red in your SC */
    color: #333 !important; 
}

/* Ensure the blue link colors for SN and WP still stand out on the pink background */
.inactive-user .sn-col a, 
.inactive-user td i { 
    color: #007bff !important; 
}

.select2-container .select2-selection--single { height: 38px !important; border-radius: 20px !important; border: 1px solid #ccc !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px !important; padding-left: 15px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }

    .search-row { display: flex; gap: 8px; margin-bottom: 8px; }
    .search-row > div { flex: 1; min-width: 0; }
/* --- MODIFICATION 2: PAGINATION CSS --- */
.pagination-container {
    margin: 20px 0;
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 5px;
}
.page-link-custom {
    padding: 8px 14px;
    background: white;
    border: 1px solid #ddd;
    color: #1a66cc;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    transition: 0.3s;
}
.page-link-custom:hover {
    background: #f0f2f5;
}
.page-link-custom.active {
    background: #1a66cc;
    color: white;
    border-color: #1a66cc;
}
.pagination-info {
    text-align: center;
    font-size: 13px;
    color: #666;
    margin-bottom: 10px;
}
    @media (max-width: 768px) {
        .pill-btn { flex: 1; padding: 10px 5px; font-size: 13px; min-width: 90px; }
        .table thead th { font-size: 12px; padding: 10px 2px; }
        .table tbody td { font-size: 12px; padding: 10px 4px; }
    }
</style>

<div class="main-wrapper">
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <!-- Search & Filters -->
    <form method="get">
        <!--<div class="filter-row">-->
        <!--    <select name="limit" class="limit-select" onchange="this.form.submit()">-->
        <!--        <option value="10" <?php if(($_GET['limit']??"")=="10") echo 'selected'; ?>>10</option>-->
        <!--        <option value="100" <?php if(($_GET['limit']??"")=="100") echo 'selected'; ?>>100</option>-->
        <!--        <option value="500" <?php if(($_GET['limit']??"")=="500") echo 'selected'; ?>>500</option>-->
        <!--    </select>-->
        <!--    <input type="text" name="search" class="search-input" placeholder="Filter Search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">-->
        <!--</div>-->
        <div class="filter-row">
            <select name="limit" class="limit-select" onchange="this.form.submit()">
                <option value="10" <?php if(($_GET['limit']??"")=="10") echo 'selected'; ?>>10</option>
                <option value="100" <?php if(($_GET['limit']??"")=="100") echo 'selected'; ?>>100</option>
                <option value="500" <?php if(($_GET['limit']??"")=="500") echo 'selected'; ?>>500</option>
            </select>
            
            <!-- AJAX Search Dropdown -->
            <div style="flex: 1;">
                <select name="user_search" id="user_search_ajax" onchange="this.form.submit()">
                    <?php if($u_search != ""): ?>
                        <option value="<?php echo $u_search; ?>" selected><?php echo $user_display_info; ?></option>
                    <?php else: ?>
                        <option value="">Search Mobile or Name...</option>
                    <?php endif; ?>
                </select>
            </div>
    
            <?php if($u_search != "" || $text_search != ""): ?>
                <a href="users_old.php" class="btn btn-sm btn-secondary" style="border-radius:20px;">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Action Buttons -->
    <div class="btn-container">
        <button class="pill-btn btn-red" id="deactiveToggle">Deactive</button>
        <a href="?sort=points" class="pill-btn btn-amber">Point</a>
        <button class="pill-btn btn-green" data-toggle="modal" data-target="#createAccountModal">Create Account</button>
    </div>

    <!-- Table Content -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="table">
                <thead class="table-header">
                    <tr>
                        <th>SN</th>
                        <th>WP</th>
                        <th>Username</th>
                        <th>Point</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Inactive Days</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // $limit = $_GET['limit'] ?? 20;
                    // $page = $_GET['page'] ?? 1;
                    // $offset = ($page - 1) * $limit;
                    // $search = mysqli_real_escape_string($con, $_GET['search'] ?? '');
                    // $where = $search ? "WHERE name LIKE '%$search%' OR mobile LIKE '%$search%'" : "";
                    // $orderBy = ($_GET['sort'] ?? '') == 'points' ? "ORDER BY CAST(wallet AS UNSIGNED) DESC" : "ORDER BY sn DESC";

                    // $user_query = mysqli_query($con, "SELECT * FROM users $where $orderBy LIMIT $limit OFFSET $offset");
                    $limit = $_GET['limit'] ?? 20;
                    $page = $_GET['page'] ?? 1;
                    $offset = ($page - 1) * $limit;
                    
                    // NEW SEARCH LOGIC
                    $u_search = mysqli_real_escape_string($con, $_GET['user_search'] ?? '');
                    $text_search = mysqli_real_escape_string($con, $_GET['search'] ?? '');
                    
                    $where = "WHERE 1=1";
                    if ($u_search != '') {
                        $where .= " AND mobile = '$u_search'";
                    } elseif ($text_search != '') {
                        $where .= " AND (name LIKE '%$text_search%' OR mobile LIKE '%$text_search%')";
                    }
                    
                    $count_query = mysqli_query($con, "SELECT COUNT(*) as total FROM users $where");
                    $total_data = mysqli_fetch_assoc($count_query);
                    $total_records = $total_data['total'];
                    $total_pages = ceil($total_records / $limit);
                                        
                    $orderBy = ($_GET['sort'] ?? '') == 'points' ? "ORDER BY CAST(wallet AS UNSIGNED) DESC" : "ORDER BY sn DESC";
                    $user_query = mysqli_query($con, "SELECT * FROM users $where $orderBy LIMIT $limit OFFSET $offset");
                    $i = $offset + 1;

                    while($row = mysqli_fetch_array($user_query)){
                        $statusClass = ($row['active'] == 1) ? 'status-active' : 'status-inactive';
                        $statusText = ($row['active'] == 1) ? 'active' : 'deactive';
                        
                        $dt = new DateTime(); $dt->setTimestamp($row['created_at']);
                        $formattedDate = $dt->format('d/m/Y');
                        $formattedTime = $dt->format('h:i A');
                        $inactiveDays = (new DateTime())->diff($dt)->days;
                    ?>
                    <tr class="user-row <?php echo ($row['active'] == 1) ? 'active-user' : 'inactive-user'; ?>">
                        <td class="sn-col">
                                <a href="gateway-history.php?username=<?php echo urlencode($row['name']); ?>" style="text-decoration:none; color:inherit;">
                                    <?php echo $i++; ?>
                                </a>
                        </td>
                        <td>
                             <a href="javascript:void(0);" class="contact-popup-trigger" 
                                   data-mobile="<?php echo $row['mobile']; ?>" 
                                   data-name="<?php echo htmlspecialchars($row['name']); ?>">
                                    <i class="fas fa-phone-alt" style="color:#007bff; font-size:18px;"></i>
                                </a>
                        </td>
                        <td class="username-col">
                            <a href="javascript:void(0);" class="open-admin-action" 
                               data-name="<?php echo htmlspecialchars($row['name']); ?>" 
                               data-mobile="<?php echo $row['mobile']; ?>"
                               style="text-decoration:none; color:inherit; cursor:pointer; font-weight:600;">
                                <?php echo htmlspecialchars($row['name']); ?>
                            </a>
                        </td>      
                        <td class="point-col"> 
                                 <a href="user-wallet-history.php?user_mobile=<?php echo $row['mobile']; ?>" class="blue-bold">
                                    <?php echo $row['wallet']; ?>
                                </a>
                        </td>
                        <td style="line-height:1.2;">
                            <span style="font-size: 12px;"><?php echo $formattedDate; ?></span><br>
                            <span style="font-size: 11px; color:#666;"><?php echo $formattedTime; ?></span>
                        </td>
                        <td>
                            <button class="status-btn toggle-status <?php echo $statusClass; ?>" 
                                    data-mobile="<?php echo $row['mobile']; ?>" 
                                    data-status="<?php echo $row['active']; ?>">
                                <?php echo $statusText; ?>
                            </button>
                        </td>
                        <td style="font-weight:500;"><?php echo $inactiveDays; ?></td>
                        <td>
                            <div class="action-stack">
                                <a href="user-profile.php?userID=<?php echo $row['mobile']; ?>" class="icon-btn icon-view"><i class="fas fa-eye"></i></a>
                                <a href="?deleteUser=<?php echo $row['sn']; ?>" onclick="return confirm('Delete this user?');" class="icon-btn icon-delete"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- MODIFICATION 3: PAGINATION UI -->
        <div class="pagination-info">
            Showing <?php echo ($offset + 1); ?> to <?php echo min($offset + $limit, $total_records); ?> of <?php echo $total_records; ?> Users
        </div>
        
        <div class="pagination-container">
            <?php if($page > 1): ?>
                <a href="?page=<?php echo ($page-1); ?>&limit=<?php echo $limit; ?>&user_search=<?php echo $u_search; ?>&search=<?php echo $text_search; ?>" class="page-link-custom">Prev</a>
            <?php endif; ?>
        
            <?php
            // Show max 5 page links to keep it clean
            $start_loop = max(1, $page - 2);
            $end_loop = min($total_pages, $page + 2);
        
            for($i = $start_loop; $i <= $end_loop; $i++): ?>
                <a href="?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>&user_search=<?php echo $u_search; ?>&search=<?php echo $text_search; ?>" 
                   class="page-link-custom <?php echo ($page == $i) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        
            <?php if($page < $total_pages): ?>
                <a href="?page=<?php echo ($page+1); ?>&limit=<?php echo $limit; ?>&user_search=<?php echo $u_search; ?>&search=<?php echo $text_search; ?>" class="page-link-custom">Next</a>
            <?php endif; ?>
        </div>
</div>

<!-- Create Account Modal -->
<div class="modal fade" id="createAccountModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create New Account</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="form-group"><label>Username</label><input type="text" class="form-control" name="username" required></div>
          <div class="form-group"><label>Phone (10 Digits)</label><input type="tel" class="form-control" name="phone" pattern="[0-9]{10}" required></div>
          <div class="form-group"><label>Password</label><input type="password" class="form-control" name="password" minlength="6" required></div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="create_account" class="btn btn-primary btn-block">Create Account</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Contact Choice Modal -->
<div class="modal fade" id="contactChoiceModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 320px;">
    <div class="modal-content" style="border-radius: 15px;">
      <div class="modal-header border-0 pb-0">
        <h6 class="modal-title font-weight-bold" id="popupUserName">User</h6>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <a href="" id="modalCallBtn" class="btn btn-primary btn-block mb-3" style="border-radius:10px; padding:12px;">
            <i class="fas fa-phone"></i> &nbsp; Call
        </a>
        <a href="" id="modalWABtn" target="_blank" class="btn btn-success btn-block" style="border-radius:10px; padding:12px; background-color:#28a745;">
            <i class="fab fa-whatsapp"></i> &nbsp; WhatsApp
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Admin Action Modal -->
<div class="modal fade" id="adminActionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-round">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" id="actionNameHeader">Name : </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-wrap">
                    <a href="#" id="linkAdd" class="btn-pill bg-manual-add">Add money Manually</a>
                    <a href="#" id="linkWithdraw" class="btn-pill bg-manual-withdraw">Withdraw money Manually</a>
                    <a href="#" id="linkCancel" class="btn-pill bg-game-cancel">Game Cancel</a>
                    <a href="#" id="linkPL" class="btn-pill bg-profit-loss">Profit & Loss</a>
                    <a href="#" id="linkNotify" class="btn-pill bg-notification">Send Notification</a>
                    <a href="#" id="linkLogs" class="btn-pill bg-activity-log">Activity Logs</a>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn-close-gray" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-round">
            <div class="modal-header border-0">
                <h5 class="modal-title font-weight-bold">Notification Send To <span id="notifUserName"></span></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="notifForm">
                    <input type="hidden" id="notifUserMobile">
                    <div class="form-group mb-3">
                        <label style="font-weight:600;">View <input type="checkbox" checked id="notifView"></label>
                    </div>
                    <div class="form-group mb-3">
                        <input type="text" class="form-control" id="notifTitle" placeholder="Title" style="border-radius:20px; height:45px; border: 1px solid #ced4da;">
                    </div>
                    <div class="form-group mb-3">
                        <textarea class="form-control" id="notifMsg" rows="3" placeholder="Message" style="border-radius:20px; border: 1px solid #ced4da;"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 justify-content-center" style="gap: 10px;">
                <button type="button" class="btn-close-gray" data-dismiss="modal" style="padding: 10px 30px;">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitNotification()" style="border-radius:25px; padding:10px 25px; font-weight:bold; background-color:#007bff;">Send Notification</button>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function () {
    
     $('#user_search_ajax').select2({
        width: '100%',
        placeholder: "Search Mobile or Name...",
        minimumInputLength: 2,
        ajax: {
            url: 'user-search-live.php',
            dataType: 'json',
            delay: 250,
            data: function (params) { return { q: params.term }; },
            processResults: function (data) { return { results: data }; },
            cache: true
        }
    });
    // 1. AJAX Status Toggle
    $(document).on('click', '.toggle-status', function () {
        var btn = $(this);
        var mobile = btn.data('mobile');
        var currentStatus = btn.data('status');
        var newStatus = (currentStatus == '1') ? '0' : '1';

        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: { ajax_toggle_status: 1, mobile: mobile, status: newStatus },
            success: function(res) {
                location.reload();
            }
        });
    });

    // 2. Local Filter for "Deactive" Button
    let filterOn = false;
    $('#deactiveToggle').on('click', function() {
        filterOn = !filterOn;
        if(filterOn) {
            $('.active-user').hide();
            $(this).text('Show All').css('background', '#6c757d');
        } else {
            $('.active-user').show();
            $(this).text('Deactive').css('background', '#dc3545');
        }
    });
});
// Contact Popup Logic
$('.contact-popup-trigger').on('click', function() {
    var mobile = $(this).data('mobile');
    var name = $(this).data('name');
    
    $('#popupUserName').text(name);
    $('#modalCallBtn').attr('href', 'tel:+91' + mobile);
    $('#modalWABtn').attr('href', 'https://wa.me/91' + mobile);
    
    $('#contactChoiceModal').modal('show');
});

// Open Admin Action Popup
var currentName = "";
var currentMobile = "";

// 1. Open Admin Action Popup
$(document).on('click', '.open-admin-action', function() {
    currentName = $(this).data('name');
    currentMobile = $(this).data('mobile');

    $('#actionNameHeader').text('Name : ' + currentName);

    // Set Regular Links
    $('#linkAdd').attr('href', 'add-money-manual.php?mobile=' + currentMobile);
    $('#linkWithdraw').attr('href', 'withdraw-money-manual.php?mobile=' + currentMobile);
    $('#linkCancel').attr('href', 'game-cancel.php?mobile=' + currentMobile);
    $('#linkPL').attr('href', 'transaction.php?mobile=' + currentMobile);
    $('#linkLogs').attr('href', 'activity-logs.php?mobile=' + currentMobile);

    // CHANGE: Notification button triggers second popup
    $('#linkNotify').off('click').on('click', function(e) {
        e.preventDefault();
        openNotificationPopup();
    });

    $('#adminActionModal').modal('show');
});

// 2. Transition to Notification Popup
function openNotificationPopup() {
    $('#adminActionModal').modal('hide');
    
    // Set user data in notification modal
    $('#notifUserName').text(currentName);
    $('#notifUserMobile').val(currentMobile);
    
    // Clear previous inputs
    $('#notifTitle, #notifMsg').val('');

    // Open second modal after a tiny delay for smooth animation
    setTimeout(function() {
        $('#notificationModal').modal('show');
    }, 400);
}

// 3. Final Submit Logic
function submitNotification() {
    var title  = $('#notifTitle').val();
    var msg    = $('#notifMsg').val();
    var mobile = $('#notifUserMobile').val();
    var view   = $('#notifView').is(':checked') ? 1 : 0;

    if(title == "" || msg == "") {
        alert("Please enter both title and message");
        return;
    }

    // You can add your AJAX here to save to database. For now:
    alert("Notification Sent Successfully to " + currentName);
    $('#notificationModal').modal('hide');
}
</script>

<?php 
include('footer.php'); 
} else { 
    echo "<script>window.location.href = 'unauthorized.php';</script>"; 
    exit(); 
} 
?>