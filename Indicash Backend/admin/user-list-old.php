<?php $page = basename($_SERVER['PHP_SELF']); ?>
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include('header.php'); ?>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$idd = $_SESSION['userID'];
$main_admin_mail = isset($main_admin_mail) ? $main_admin_mail : 'admin@gmail.com';
$sql = "SELECT * FROM admin WHERE email='". $idd ."'";
$result = $con->query($sql);
$row = $result->fetch_assoc();
$rol = $row['tasks'];
$sql2 = "SELECT * FROM task_manager WHERE id='". $rol ."'";
$result2 = $con->query($sql2);
$row2 = $result2->fetch_assoc();
$HiddenProducts = explode(',',$row2['tasks']);
if (in_array(4, $HiddenProducts)){
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Handle AJAX request for toggling status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_toggle_status'])) {
    $mobile = $_POST['mobile'];
    $newStatus = $_POST['status'];
    
    $update_sql = "UPDATE users SET active = '$newStatus' WHERE mobile = '$mobile'";
    $update_result = mysqli_query($con, $update_sql);
    
    if ($update_result) {
        $action = ($newStatus == '1') ? 'activated' : 'deactivated';
        $remark = 'User ' . $action . ': ' . $mobile . ' by ' . $idd;
        if (function_exists('log_action')) { log_action($remark); }
        echo json_encode(['success' => true, 'message' => 'User ' . $action . ' successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit();
}

// Handle form submission for creating new account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_account'])) {
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $errors = [];
    
    if (empty($username)) { $errors[] = "Username is required"; }
    if (empty($phone)) { $errors[] = "Phone number is required"; } 
    elseif (!preg_match('/^[0-9]{10}$/', $phone)) { $errors[] = "Phone number must be 10 digits"; }
    else {
        $check_sql = "SELECT * FROM users WHERE mobile = ?";
        $check_stmt = $con->prepare($check_sql);
        $check_stmt->bind_param("s", $phone);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows > 0) { $errors[] = "Phone number already exists"; }
        $check_stmt->close();
    }
    
    if (empty($password)) { $errors[] = "Password is required"; } 
    elseif (strlen($password) < 6) { $errors[] = "Password must be at least 6 characters"; }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $created_at = time();
        $active = 1; $wallet = 0;
        $stmt = $con->prepare("INSERT INTO users (name, mobile, password, created_at, active, wallet) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiii", $username, $phone, $hashed_password, $created_at, $active, $wallet);
        
        if ($stmt->execute()) {
            $success_message = "Account created successfully!";
            $remark = 'New user created: ' . $username . ' by ' . $idd;
            if (function_exists('log_action')) { log_action($remark); }
            $username = $phone = $password = ''; 
        } else {
            $errors[] = "Failed to create account. Please try again.";
        }
        $stmt->close();
    }
}

// Handle User Deletion
if (isset($_GET['deleteUser']) && in_array(4, $HiddenProducts)) {
    $sn = mysqli_real_escape_string($con, $_GET['deleteUser']);
    $user_query = $con->query("SELECT mobile FROM users WHERE sn = '$sn'");
    $user_data = $user_query->fetch_assoc();

    if ($user_data) {
        $mobile = $user_data['mobile'];
        $con->query("DELETE FROM withdraw_requests WHERE user = '$mobile'");
        $con->query("DELETE FROM auto_deposits WHERE mobile = '$mobile'");
        $del = $con->query("DELETE FROM users WHERE sn = '$sn'");

        if ($del) {
            if (function_exists('log_action')) { log_action('User SN ' . $sn . ' Deleted by ' . $idd); }
            echo "<script>alert('User deleted successfully'); window.location.href='users_old.php';</script>";
        } else {
            echo "<script>alert('Error deleting user'); window.location.href='users_old.php';</script>";
        }
    }
    exit;
}
?>

<script>
$(document).ready(function () {
    $(document).on('click', '.toggle-status', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var badge = $(this);
        if (badge.data('loading')) return;
        badge.data('loading', true);
        var mobile = badge.data('mobile');
        var currentStatus = badge.data('status');
        var newStatus = (currentStatus == '1') ? '0' : '1';
        var row = badge.closest('tr');

        badge.removeClass('badge-success badge-danger');
        if (newStatus == '1') {
            badge.addClass('badge-success').text('Active');
            row.removeClass('table-danger').addClass('table-success');
        } else {
            badge.addClass('badge-danger').text('Inactive');
            row.removeClass('table-success').addClass('table-danger');
        }
        badge.data('status', newStatus);

        $.ajax({
            url: 'users_old.php',
            type: 'POST',
            data: { ajax_toggle_status: 1, mobile: mobile, status: newStatus },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    setTimeout(function () { location.reload(); }, 300);
                } else {
                    alert(response.message);
                    location.reload();
                }
            },
            error: function () { location.reload(); },
            complete: function () { badge.data('loading', false); }
        });
    });
});
</script>

<style>
/* ===== RESET ===== */
.content-wrapper {
    overflow-x: hidden;
}

/* ===== SEARCH ROW: dropdown + input same height, full width on mobile ===== */
.search-container {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    width: 100%;
}

.rows-dropdown {
    height: 36px;
    padding: 0 8px;
    font-size: 14px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    background: #fff;
    width: 72px;
    flex-shrink: 0;
    box-sizing: border-box;
}

.filter-search-input {
    height: 36px;
    padding: 0 10px;
    font-size: 14px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    flex: 1;
    min-width: 0;
    box-sizing: border-box;
}

/* ===== ACTION BUTTONS ROW ===== */
.action-btn-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}

.action-btn-row .btn {
    height: 36px;
    line-height: 1;
    padding: 0 14px;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
    flex-shrink: 0;
}

/* ===== TABLE ===== */
.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table {
    margin-bottom: 0;
    font-size: 14px;
    min-width: 580px; /* prevent collapse on small screens */
}

.table thead tr th {
    background-color: #007bff;
    color: white;
    white-space: nowrap;
    padding: 10px 8px;
    font-size: 13px;
}

.table tbody td {
    padding: 8px 8px;
    vertical-align: middle;
    font-size: 13px;
}

/* ===== PAGINATION ===== */
.pagination {
    margin: 12px 10px 10px;
}

/* ===== MOBILE SPECIFIC ===== */
@media (max-width: 576px) {

    /* Content wrapper padding on mobile */
    .content-wrapper {
        padding: 8px !important;
    }

    /* Search row stacks neatly */
    .search-container {
        gap: 6px;
    }

    .rows-dropdown {
        width: 64px;
        font-size: 13px;
    }

    .filter-search-input {
        font-size: 13px;
    }

    /* Buttons: equal width, fill row */
    .action-btn-row .btn {
        flex: 1;
        justify-content: center;
        font-size: 13px;
        padding: 0 8px;
    }

    /* Table font slightly smaller */
    .table {
        font-size: 12px;
    }

    .table thead tr th,
    .table tbody td {
        padding: 7px 6px;
        font-size: 12px;
    }

    /* Badge status pill */
    .badge {
        font-size: 11px;
        padding: 4px 8px;
    }

    /* Action icon buttons */
    .btn-group-sm .btn {
        padding: 4px 7px;
        font-size: 12px;
    }
}
</style>

<section class="content">
  <div class="container-fluid px-0 px-md-2">

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show mx-2" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show mx-2" role="alert">
            <ul class="mb-0"><?php foreach ($errors as $error): ?><li><?php echo $error; ?></li><?php endforeach; ?></ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    <?php endif; ?>

    <div class="row mx-0"><div class="col-12 px-0">

          <!-- Row 1: Search + Limit dropdown -->
          <form method="get" id="searchForm">
            <div class="search-container">
                <select name="limit" class="rows-dropdown" onchange="this.form.submit()">
                    <option value="10"  <?php echo (isset($_GET['limit']) && $_GET['limit'] == 10)  ? 'selected' : ''; ?>>10</option>
                    <option value="20"  <?php echo (!isset($_GET['limit']) || $_GET['limit'] == 20) ? 'selected' : ''; ?>>20</option>
                    <option value="50"  <?php echo (isset($_GET['limit']) && $_GET['limit'] == 50)  ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo (isset($_GET['limit']) && $_GET['limit'] == 100) ? 'selected' : ''; ?>>100</option>
                </select>
                <input type="text" name="search" class="filter-search-input" placeholder="Filter Search"
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            </div>
            <input type="hidden" name="sort" value="<?php echo isset($_GET['sort']) ? $_GET['sort'] : ''; ?>">
          </form>

          <!-- Row 2: Action buttons -->
          <div class="action-btn-row">
            <a href="" class="btn btn-danger" id="filterInactive">Deactive</a>

            <a href="users_old.php?sort=points&search=<?php echo isset($_GET['search']) ? urlencode($_GET['search']) : ''; ?>"
               class="btn btn-warning" style="background-color:#ffc107;color:#000;">Point</a>

            <a href="#" class="btn btn-success" data-toggle="modal" data-target="#createAccountModal">Create Account</a>
          </div>

          <div class="table-responsive mt-2">
            <table class="table table-bordered table-striped table-hover">
              <thead>
                <tr style="background-color:#007bff;color:white;">
                  <th>SN</th>
                  <th class="text-center">WP</th>
                  <th>Username</th>
                  <th>Point</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Inactive Days</th>
                  <th class="text-center">View</th>
                </tr>
              </thead>
              <tbody>
<?php
$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) 
    ? (int) $_GET['limit'] 
    : 20;  
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : ''; 

$where = "";
if ($search !== "") {
    $search_safe = mysqli_real_escape_string($con, $search);
    $where = "WHERE name LIKE '%$search_safe%' OR mobile LIKE '%$search_safe%'";
}

// Logic for sorting points
if ($sort === 'points') {
    $orderBy = "ORDER BY CAST(wallet AS UNSIGNED) DESC";
} else {
    $orderBy = "ORDER BY sn DESC";
}

$totalQuery = mysqli_query($con, "SELECT COUNT(*) as total FROM users $where");
$totalRow = mysqli_fetch_assoc($totalQuery);
$totalUsers = (int)$totalRow['total'];
$totalPages = max(1, (int)ceil($totalUsers / $limit));
$user = mysqli_query($con, "SELECT * FROM users $where $orderBy LIMIT $limit OFFSET $offset");
$i = $offset + 1;

$today = new DateTime(); 

if ($user) {
    while($row = mysqli_fetch_array($user)){
        $inactiveDays = 0;
        if (!empty($row['created_at'])) {
            try {
                $createdDate = new DateTime();
                $createdDate->setTimestamp($row['created_at']);
                $interval = $today->diff($createdDate);
                $inactiveDays = $interval->days;
            } catch (Exception $e) { $inactiveDays = 0; }
        }
        $statusClass = (isset($row['active']) && $row['active'] == 1) ? 'badge-success' : 'badge-danger';
        $statusText = (isset($row['active']) && $row['active'] == 1) ? 'Active' : 'Deactive';
        $currentStatus = isset($row['active']) ? $row['active'] : '0';
        $formattedDate = !empty($row['created_at']) ? date('d-m-Y', $row['created_at']) : 'N/A';
        $rowClass = ($currentStatus == '1') ? 'table-success' : 'table-danger';
?>
<tr class="<?php echo $rowClass; ?>">
  <td class="text-center"><?php echo $i; ?></td>
  <td class="text-center">
    <a href="tel:+91<?php echo htmlspecialchars($row['mobile']); ?>" title="Call">
      <i class="fas fa-phone-alt" style="font-size:18px;color:#007bff;"></i>
    </a>
  </td>
  <td>
    <div class="d-flex flex-column">
      <span class="font-weight-bold"><?php echo htmlspecialchars($row['name']); ?></span>
      <small class="text-muted">+91 <?php echo htmlspecialchars($row['mobile']); ?></small>
    </div>
  </td>
  <td class="text-center font-weight-bold"><?php echo isset($row['wallet']) ? htmlspecialchars($row['wallet']) : '0'; ?></td>
  <td><?php echo $formattedDate; ?></td>
  <td>
    <a href="javascript:void(0);" class="badge toggle-status <?php echo $statusClass; ?>"
       data-mobile="<?php echo htmlspecialchars($row['mobile']); ?>"
       data-status="<?php echo $currentStatus; ?>">
      <?php echo $statusText; ?>
    </a>
  </td>
  <td class="text-center"><?php echo $inactiveDays; ?> days</td>
  <td class="text-center">
    <div class="btn-group btn-group-sm">
      <a href="user-profile.php?userID=<?php echo htmlspecialchars($row['mobile']); ?>" class="btn btn-info"><i class="fas fa-eye"></i></a>
      <a href="users_old.php?deleteUser=<?php echo $row['sn']; ?>" onclick="return confirm('Delete this user?');" class="btn btn-danger"><i class="fas fa-trash"></i></a>
    </div>
  </td>
</tr>
<?php $i++; } } else { echo '<tr><td colspan="8" class="text-center">No users found</td></tr>'; } ?>
              </tbody>
            </table>

            <?php if ($totalPages > 1): ?>
            <nav class="mt-2">
              <ul class="pagination justify-content-center flex-wrap">
                <?php if($page > 1): ?>
                  <li class="page-item">
                    <a class="page-link" href="?page=<?php echo ($page-1); ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>">‹</a>
                  </li>
                <?php endif; ?>
                <?php
                  $window = 2; $start = max(1, $page - $window); $end = min($totalPages, $page + $window);
                  if ($start > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?page=1&search='.urlencode($search).'&sort='.$sort.'">1</a></li>';
                    if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                  }
                  for($p=$start; $p<=$end; $p++){
                    $active = $p==$page ? ' active' : '';
                    echo '<li class="page-item'.$active.'"><a class="page-link" href="?page='.$p.'&search='.urlencode($search).'&sort='.$sort.'">'.$p.'</a></li>';
                  }
                  if ($end < $totalPages) {
                    if ($end < $totalPages-1) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                    echo '<li class="page-item"><a class="page-link" href="?page='.$totalPages.'&search='.urlencode($search).'&sort='.$sort.'">'.$totalPages.'</a></li>';
                  }
                ?>
                <?php if($page < $totalPages): ?>
                  <li class="page-item">
                    <a class="page-link" href="?page=<?php echo ($page+1); ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>">Next</a>
                  </li>
                <?php endif; ?>
              </ul>
            </nav>
            <?php endif; ?>

          </div>
    </div></div>
  </div>
</section>

<!-- Create Account Modal -->
<div class="modal fade" id="createAccountModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create New Account</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="form-group"><label>Username:</label><input type="text" class="form-control" name="username" required></div>
          <div class="form-group"><label>Phone:</label><input type="tel" class="form-control" name="phone" pattern="[0-9]{10}" required></div>
          <div class="form-group"><label>Password:</label><input type="password" class="form-control" name="password" minlength="6" required></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" name="create_account" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function () {
    let inactiveFilterOn = false;
    $('#filterInactive').on('click', function (e) {
        e.preventDefault();
        inactiveFilterOn = !inactiveFilterOn;
        if (inactiveFilterOn) {
            $('table tbody tr.table-success').hide();
            $('table tbody tr.table-danger').show();
            $(this).text('Show All').removeClass('btn-danger').addClass('btn-secondary');
        } else {
            $('table tbody tr').show();
            $(this).html('<i class=""></i> Deactive').removeClass('btn-secondary').addClass('btn-danger');
        }
    });
});
</script>

<?php include('footer.php'); } else { echo "<script>window.location.href = 'unauthorized.php';</script>"; exit(); } ?>