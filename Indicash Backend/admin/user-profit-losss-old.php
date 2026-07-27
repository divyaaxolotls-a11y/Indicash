<?php
include('header.php');

// 1. SECURITY CHECK (Move to top)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$idd = $_SESSION['userID'] ?? '';
$sql = "SELECT * FROM admin WHERE email='". $idd ."'";
$result = $con->query($sql);
$row = $result->fetch_assoc();
$rol = $row['tasks'] ?? 0;
$sql2 = "SELECT * FROM task_manager WHERE id='". $rol ."'";
$result2 = $con->query($sql2);
$row2 = $result2->fetch_assoc();
$HiddenProducts = explode(',', $row2['tasks'] ?? '');
if (!in_array(4, $HiddenProducts)) { echo "<script>window.location.href='unauthorized.php';</script>"; exit(); }

// 2. GET DATE & FILTERS (Must be defined before the Query)
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : date('Y-m-d');
$date_dmY = date('d/m/Y', strtotime($filter_date)); 

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page-1) * $limit;
$search = $_GET['search'] ?? '';

$active_only = "";
if (isset($_GET['active']) && $_GET['active'] !== '') {
    $status = mysqli_real_escape_string($con, $_GET['active']);
    $active_only = " AND active='$status'";
}

$where = ["1=1 $active_only"];
if(!empty($search)){
    $search_safe = mysqli_real_escape_string($con, $search);
    $where[] = "(name LIKE '%$search_safe%' OR mobile LIKE '%$search_safe%')";
}
$whereSQL = 'WHERE '.implode(' AND ', $where);

// 3. SORTING LOGIC
$order_by = $_GET['order_by'] ?? 'sn'; 
$sort = $_GET['sort'] ?? 'DESC';       
$next_sort = ($sort == 'DESC') ? 'ASC' : 'DESC'; 

function getSortIcon($column, $current_order, $current_sort) {
    if ($current_order == $column) {
        return ($current_sort == 'ASC') ? ' <i class="fas fa-long-arrow-alt-up"></i>' : ' <i class="fas fa-long-arrow-alt-down"></i>';
    }
    return ' <i class="fas fa-sort" style="opacity:0.3;"></i>';
}

$sort_map = [
    'sn'    => 'u.sn',
    'play'  => 'play_total',
    'win'   => 'win_total',
    'pl'    => '(play_total - win_total)',
    'point' => 'u.wallet'
];
$actual_sort_column = $sort_map[$order_by] ?? 'u.sn';

// 4. THE MAIN QUERY (Executed only once with all variables ready)
// $userQuery = "SELECT u.*, 
//     (SELECT COALESCE(SUM(amount), 0) FROM games WHERE user = u.mobile AND date = '$date_dmY') AS play_total,
//     (SELECT COALESCE(SUM(amount*9), 0) FROM games WHERE user = u.mobile AND date = '$date_dmY' AND (status='1' OR status='win')) AS win_total
//     FROM users u 
//     $whereSQL 
//     ORDER BY $actual_sort_column $sort 
//     LIMIT $limit OFFSET $offset";
// Modification: INNER JOIN ensures only active bettors for $db_date are shown
$userQuery = "SELECT 
    u.mobile, u.name, u.wallet, u.sn as user_sn,
    SUM(g.amount) AS play_total,
    SUM(CASE WHEN g.status='1' OR g.status='win' THEN (g.amount * 9.5) ELSE 0 END) AS win_total,
    (SUM(g.amount) - SUM(CASE WHEN g.status='1' OR g.status='win' THEN (g.amount * 9.5) ELSE 0 END)) AS pl_total
    FROM users u
    INNER JOIN games g ON u.mobile = g.user 
    WHERE g.date = '$date_dmY' $search_where
    GROUP BY u.mobile 
    ORDER BY $actual_sort_column $sort 
    LIMIT $limit OFFSET $offset";
$userResult = mysqli_query($con, $userQuery);

// Get total rows for pagination
$totalQuery = mysqli_query($con, "SELECT COUNT(*) as total FROM users  WHERE date='$date_dmY'");
$totalRows = mysqli_fetch_assoc($totalQuery)['total'];
$totalPages = ceil($totalRows / $limit);
?>

<style>
/* ... keep your existing styles ... */
.header-title{ background:#ffc107; color:#000; padding:14px; text-align:center; border-radius:40px; font-weight:700; margin:15px 0; font-size:20px; }
.filter-card{ background:#e0e0e0; padding:12px; border-radius:40px; margin-bottom:15px; display:flex; align-items:center; justify-content:center; gap:10px; }
.table{ border-collapse:separate; border-spacing:0 12px; width:100%; }
.table thead th{ background:#1f5fae; color:#fff; padding:10px; text-align:center; font-weight:600; font-size:15px; border-right:1px solid #dcdcdc; }
.table thead th a { color: white !important; text-decoration: none; }
.table tbody tr{ background: #fff; border-radius:15px; }
.table tbody td{ text-align:center; vertical-align:middle; font-size:15px; font-weight:500; border-right:1px solid #e5e5e5; padding:10px; }
.col-pl{ background:#a5d6a7; font-weight:700; }
.col-add{ background:#0a8f08; color:#fff; font-weight:700; }
.col-withdraw{ background:#b30000; color:#fff; font-weight:700; }
/* Popup Button Styles */
.modal-content { border-radius: 15px; border: none; }
.modal-header { border-bottom: 1px solid #eee; }
.modal-title { font-weight: 700; font-size: 22px; color: #333; }
.action-btn-container { display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-start; padding: 10px; }

.btn-action-pill {
    border-radius: 30px;
    padding: 8px 20px;
    font-weight: 600;
    font-size: 14px;
    border: none;
    color: white !important;
    text-decoration: none !important;
}
.btn-manual-add { background-color: #3cb44b; }
.btn-manual-withdraw { background-color: #e63946; }
.btn-game-cancel { background-color: #ffc107; color: #000 !important; }
.btn-pl-action { background-color: #1fa1b6; }
.btn-notify { background-color: #007bff; }
.btn-logs { background-color: #1fa1b6; }

/* Make username look clickable */
.username-link { color: #444; font-weight: 600; cursor: pointer; text-decoration: none; }
.username-link:hover { color: #1f5fae; text-decoration: underline; }
</style>

<div class="content">
    <div class="container-fluid">
        <div class="header-title">User Profit Loss</div>

        <form method="get" id="filterForm">
            <div class="filter-card">
                <select name="limit" class="form-control" style="max-width:85px; border-radius:20px;" onchange="this.form.submit()">
                    <option value="20" <?php if($limit==20) echo 'selected'; ?>>20</option>
                    <option value="50" <?php if($limit==50) echo 'selected'; ?>>50</option>
                    <option value="100" <?php if($limit==100) echo 'selected'; ?>>100</option>
                    <option value="500" <?php if($limit==500) echo 'selected'; ?>>500</option>
                </select>
                <input type="date" name="filter_date" class="form-control" style="max-width:200px; border-radius:20px;" 
                       value="<?php echo $filter_date; ?>" onchange="document.getElementById('filterForm').submit()">
                
                <input type="text" name="search" class="form-control" placeholder="Search User" 
                       style="max-width:200px; border-radius:20px;" value="<?php echo htmlspecialchars($search); ?>">
                
                <button type="submit" class="btn btn-primary" style="border-radius:20px;">Go</button>
            </div>
            <!-- Keep sorting state in form -->
            <input type="hidden" name="order_by" value="<?php echo $order_by; ?>">
            <input type="hidden" name="sort" value="<?php echo $sort; ?>">
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['order_by' => 'sn', 'sort' => $next_sort])); ?>">SN <?php echo getSortIcon('sn', $order_by, $sort); ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['order_by' => 'play', 'sort' => $next_sort])); ?>">Play <?php echo getSortIcon('play', $order_by, $sort); ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['order_by' => 'win', 'sort' => $next_sort])); ?>">Win <?php echo getSortIcon('win', $order_by, $sort); ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['order_by' => 'pl', 'sort' => $next_sort])); ?>">PL <?php echo getSortIcon('pl', $order_by, $sort); ?></a></th>
                        <th>Username</th>
                        <th>Add</th>
                        <th>Withdraw</th>
                        <th><a href="?<?php echo http_build_query(array_merge($_GET, ['order_by' => 'point', 'sort' => $next_sort])); ?>">Point <?php echo getSortIcon('point', $order_by, $sort); ?></a></th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sn_count = $offset + 1;
                    while($u = mysqli_fetch_assoc($userResult)) { 
                        $mob = $u['mobile'];
                        $play = $u['play_total'];
                        $win  = $u['win_total'];
                        $pl   = $play - $win;

                        $qAdd = mysqli_query($con, "SELECT SUM(amount) as total FROM payments WHERE mobile='$mob' AND status='SUCCESS' AND DATE(created_at)='$filter_date'");
                        $add = mysqli_fetch_assoc($qAdd)['total'] ?? 0;

                        $qWd = mysqli_query($con, "SELECT SUM(amount) as total FROM withdraw_requests WHERE mobile='$mob' AND status='APPROVED' AND DATE(created_at)='$filter_date'");
                        $withdraw = mysqli_fetch_assoc($qWd)['total'] ?? 0;
                    ?>
                    <tr>
                        <td class="col-sn">
                             <a href="gateway-history.php?username=<?php echo urlencode($u['name']); ?>" style="text-decoration:none; color:#1e70cd;">
                                <?php echo $sn_count++; ?>
                            </a>
                        </td>
                        <td class="col-play"><?php echo $play; ?></td>
                        <td class="col-win"><?php echo $win; ?></td>
                        <td class="col-pl"><?php echo $pl; ?></td>
                        <td class="username">
                            <span class="username-link" 
                                  onclick="openUserActions('<?php echo htmlspecialchars($u['name']); ?>', '<?php echo $u['mobile']; ?>')">
                                <?php echo htmlspecialchars($u['name']); ?>
                            </span>
                        </td>                        <td class="col-add"><?php echo $add; ?></td>
                        <td class="col-withdraw"><?php echo $withdraw; ?></td>
                        <td class="col-point">
                            <a href="user-wallet-history.php?user_mobile=<?php echo $u['mobile']; ?>" style="text-decoration:none; color:#1e70cd; font-weight:bold;">
                                <?php echo number_format($u['wallet'], 2); ?>
                            </a>
                        </td>
                        <td class="col-view">
                            <a href="user-profile.php?userID=<?php echo $u['mobile']; ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page-1])); ?>">Previous</a></li>
                    <?php endif; ?>
                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    for($i = $start; $i <= $end; $i++): 
                    ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if($page < $totalPages): ?>
                        <li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page+1])); ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>
<!-- User Actions Popup Modal -->
<div class="modal fade" id="userActionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Name : <span id="modalUserName"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="action-btn-container">
                    <a href="#" id="link-add" class="btn-action-pill btn-manual-add">Add money Manualy</a>
                    <a href="#" id="link-withdraw" class="btn-action-pill btn-manual-withdraw">Withdraw money Manualy</a>
                    <a href="#" id="link-cancel" class="btn-action-pill btn-game-cancel">Game Cancel</a>
                    <a href="#" id="link-pl" class="btn-action-pill btn-pl-action">Profit & Loss</a>
                    <a href="#" id="link-notify" class="btn-action-pill btn-notify">Send Notification</a>
                    <a href="#" id="link-logs" class="btn-action-pill btn-logs">Activity Logs</a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" style="border-radius:20px; padding:8px 30px; background:#6c757d;" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Notification Send To <span id="notifUserName"></span></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal">
            </div>
            <div class="modal-body">
                <form id="notifForm">
                    <input type="hidden" id="notifUserMobile">
                    
                    <div class="form-group mb-3">
                        <label style="font-weight:600;">View <input type="checkbox" checked id="notifView"></label>
                    </div>

                    <div class="form-group mb-3">
                        <input type="text" class="form-control" id="notifTitle" placeholder="Title" style="border-radius:20px; height:45px;">
                    </div>

                    <div class="form-group mb-3">
                        <textarea class="form-control" id="notifMsg" rows="3" placeholder="Message" style="border-radius:20px;"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:none; justify-content: center; gap: 10px;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius:25px; padding:10px 30px; background:#6c757d;">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitNotification()" style="border-radius:25px; padding:10px 30px; background:#007bff;">Send Notification</button>
            </div>
        </div>
    </div>
</div>
<script>
var currentName = "";
var currentMobile = "";

function openUserActions(name, mobile) {
    currentName = name;
    currentMobile = mobile;

    document.getElementById('modalUserName').innerText = name;

    // Update links
     document.getElementById('link-add').href = "add-money-manual.php?mobile=" + mobile;
    document.getElementById('link-withdraw').href = "withdraw-money-manual.php?mobile=" + mobile;
    document.getElementById('link-cancel').href = "game-cancel.php?mobile=" + mobile;
    document.getElementById('link-pl').href = "transaction.php?mobile=" + mobile;
    // document.getElementById('link-notify').href = "send-notification.php?mobile=" + mobile;
    document.getElementById('link-logs').href = "activity-logs.php?mobile=" + mobile;

    // IMPORTANT: Change the notification button to trigger a function instead of a link
    document.getElementById('link-notify').onclick = function(e) {
        e.preventDefault();
        openNotificationPopup();
    };

    $('#userActionModal').modal('show');
}

function openNotificationPopup() {
    // 1. Hide the first modal
    $('#userActionModal').modal('hide');

    // 2. Set user details in the second modal
     document.getElementById('notifUserName').innerText = currentName;
    document.getElementById('notifUserMobile').value = currentMobile;
    // 3. Show the notification modal after a short delay (for smooth transition)
    setTimeout(function() {
        $('#notificationModal').modal('show');
    }, 400);
}

function submitNotification() {
    var title = document.getElementById('notifTitle').value;
    var msg = document.getElementById('notifMsg').value;
    var mobile = document.getElementById('notifUserMobile').value;

    if(title == "" || msg == "") {
        alert("Please enter title and message");
        return;
    }

    // You can add your AJAX call here to save the notification to the database
    alert("Sending Notification to " + mobile + "\nTitle: " + title);
    $('#notificationModal').modal('hide');
}
</script>
<?php include('footer.php'); ?>