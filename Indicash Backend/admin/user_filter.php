<?php
$page = basename($_SERVER['PHP_SELF']);
ini_set('display_errors', 1);
error_reporting(E_ALL);
include('header.php');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }

$idd = $_SESSION['userID'] ?? '';
$main_admin_mail = $main_admin_mail ?? 'admin@gmail.com';

// Fetch admin tasks
$sql = "SELECT * FROM admin WHERE email='". $idd ."'";
$result = $con->query($sql);
$row = $result->fetch_assoc();
$rol = $row['tasks'] ?? 0;
$sql2 = "SELECT * FROM task_manager WHERE id='". $rol ."'";
$result2 = $con->query($sql2);
$row2 = $result2->fetch_assoc();
$HiddenProducts = explode(',', $row2['tasks'] ?? '');
if (!in_array(4,$HiddenProducts)) { echo "<script>window.location.href='unauthorized.php';</script>"; exit(); }

// Handle AJAX toggle
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['ajax_toggle_status'])){
    $mobile = $_POST['mobile'];
    $newStatus = $_POST['status'];
    $update_sql = "UPDATE users SET active='$newStatus' WHERE mobile='$mobile'";
    if(mysqli_query($con,$update_sql)){
        $action = ($newStatus=='1')?'activated':'deactivated';
        if(function_exists('log_action')) log_action("User $action: $mobile by $idd");
        echo json_encode(['success'=>true,'message'=>"User $action successfully!"]);
    }else echo json_encode(['success'=>false,'message'=>'Database error']);
    exit();
}

// Handle account creation
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['create_account'])){
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $errors = [];

    if(empty($username)) $errors[]="Username required";
    if(empty($phone)) $errors[]="Phone required";
    elseif(!preg_match('/^[0-9]{10}$/',$phone)) $errors[]="Phone must be 10 digits";
    else {
        $check_sql="SELECT * FROM users WHERE mobile=?";
        $stmt=$con->prepare($check_sql);
        $stmt->bind_param("s",$phone); $stmt->execute();
        $res=$stmt->get_result();
        if($res->num_rows>0) $errors[]="Phone already exists";
        $stmt->close();
    }
    if(empty($password)) $errors[]="Password required";
    elseif(strlen($password)<6) $errors[]="Password min 6 characters";

    if(empty($errors)){
        $hashed=password_hash($password,PASSWORD_DEFAULT);
        $created_at=time(); $active=1; $wallet=0;
        $stmt=$con->prepare("INSERT INTO users (name,mobile,password,created_at,active,wallet) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("sssiii",$username,$phone,$hashed,$created_at,$active,$wallet);
        if($stmt->execute()){
            $success_message="Account created successfully!";
            if(function_exists('log_action')) log_action("New user created: $username by $idd");
            $username=$phone=$password='';
        }else $errors[]="Failed to create account";
        $stmt->close();
    }
}

// Handle deletion
if(isset($_GET['deleteUser']) && in_array(4,$HiddenProducts)){
    $sn=mysqli_real_escape_string($con,$_GET['deleteUser']);
    $user_query=$con->query("SELECT mobile FROM users WHERE sn='$sn'");
    $user_data=$user_query->fetch_assoc();
    if($user_data){
        $mobile=$user_data['mobile'];
        $con->query("DELETE FROM withdraw_requests WHERE user='$mobile'");
        $con->query("DELETE FROM auto_deposits WHERE mobile='$mobile'");
        $del=$con->query("DELETE FROM users WHERE sn='$sn'");
        if($del){
            if(function_exists('log_action')) log_action("User SN $sn Deleted by $idd");
            echo "<script>alert('User deleted'); window.location.href='users_old.php';</script>";
        }else echo "<script>alert('Error deleting user'); window.location.href='users_old.php';</script>";
    }
    exit;
}

// Filter & pagination variables
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page-1)*$limit;
$search = $_GET['search'] ?? '';
$where = [];

// Search filter
if(!empty($search)){
    $search_safe=mysqli_real_escape_string($con,$search);
    $where[]="(name LIKE '%$search_safe%' OR mobile LIKE '%$search_safe%' OR wallet LIKE '%$search_safe%')";
}

// Date filter
// if(!empty($_GET['filter_date'])){
//     $date_ts=strtotime($_GET['filter_date']);
//     $next_day=$date_ts+86400;
//     $where[]="(created_at >= $date_ts AND created_at < $next_day)";
// }

// // Month & Year filter
// if(!empty($_GET['month']) && !empty($_GET['year'])){
//     $month=$_GET['month'];
//     $year=$_GET['year'];
//     $start=strtotime("$year-$month-01");
//     $end=strtotime("+1 month",$start);
//     $where[]="(created_at >= $start AND created_at < $end)";
// }
// --- DATE FILTER ---
if(!empty($_GET['filter_date'])){
    $date_ts = strtotime($_GET['filter_date']);
    $next_day = $date_ts + 86400;
    $where[] = "(created_at >= $date_ts AND created_at < $next_day)";
}

// --- MONTH & YEAR FILTER ---
// If both month & year are present in URL, use original logic (for direct links)
elseif(!empty($_GET['month']) && !empty($_GET['year'])){
    $month = $_GET['month'];
    $year  = $_GET['year'];
    $start = strtotime("$year-$month-01");
    $end = strtotime("+1 month", $start);
    $where[] = "(created_at >= $start AND created_at < $end)";
}

// --- Partial filter (only month OR only year selected) ---
elseif(!empty($_GET['month']) || !empty($_GET['year'])){
    $year  = !empty($_GET['year'])  ? $_GET['year']  : date('Y'); // default current year
    $month = !empty($_GET['month']) ? $_GET['month'] : '01';      // default Jan if only year

    $start = strtotime("$year-$month-01");

    if(!empty($_GET['month'])){
        $end = strtotime("+1 month", $start); // filter that month
    } else {
        $end = strtotime("+1 year", $start);  // whole year
    }

    $where[] = "(created_at >= $start AND created_at < $end)";
}

$whereSQL = !empty($where) ? 'WHERE '.implode(' AND ',$where) : '';
$orderBy = "ORDER BY sn DESC";

// Total count for pagination
$totalQuery = mysqli_query($con,"SELECT COUNT(*) as total FROM users $whereSQL");
$totalRow = mysqli_fetch_assoc($totalQuery);
$totalUsers = (int)$totalRow['total'];
$totalPages = max(1,ceil($totalUsers/$limit));

// Fetch users
$user = mysqli_query($con,"SELECT * FROM users $whereSQL $orderBy LIMIT $limit OFFSET $offset");
?>

<style>
.table-responsive {
    margin-top: 20px; /* space below top controls */
}
/* Popup Button Styles */
.modal-content { border-radius: 15px; border: none; }
.action-btn-container { display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-start; padding: 10px; }
.btn-action-pill { border-radius: 30px; padding: 8px 20px; font-weight: 600; font-size: 14px; border: none; color: white !important; text-decoration: none !important; }
.btn-manual-add { background-color: #3cb44b; }
.btn-manual-withdraw { background-color: #e63946; }
.btn-game-cancel { background-color: #ffc107; color: #000 !important; }
.btn-pl-action { background-color: #1fa1b6; }
.btn-notify { background-color: #007bff; }
.btn-logs { background-color: #1fa1b6; }
.username-link { color: #007bff; font-weight: 600; cursor: pointer; text-decoration: none; }
.username-link:hover { text-decoration: underline; color: #1f5fae; }
.table thead th { background-color:#3399ff;color:#fff;text-align:center;font-weight:600;font-size:14px; }
.table tbody td { background:#fff;color:#333;text-align:center;vertical-align:middle;font-size:13px; }
.table-striped tbody tr:nth-of-type(odd){ background:#f7f9fc; }
.table-hover tbody tr:hover{ background:#d0e7ff; }
.badge-success{background:#28a745;color:#fff;}
.badge-danger{background:#dc3545;color:#fff;}
.rows-dropdown,.filter-search-input,.form-control{height:36px;border-radius:4px;border:1px solid #ced4da;padding:0 8px;}
#searchForm { display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px;align-items:center; }
@media(max-width:768px){ #searchForm{flex-direction:column;} .rows-dropdown,.form-control{width:100%!important;} }
</style>

<section class="content">
  <div class="container-fluid">

    <?php if(isset($success_message)): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if(isset($errors) && !empty($errors)): ?>
    <div class="alert alert-danger">
        <ul><?php foreach($errors as $err) echo "<li>$err</li>"; ?></ul>
    </div>
    <?php endif; ?>
    <!-- Filters + Search -->
    <!-- TOP CONTROLS: Rows + Date/Month-Year + Search -->
<!-- TOP CONTROLS: Rows + Date/Month-Year + Search -->
<script>
$(document).ready(function(){

    function toggleInputs() {
        var dateVal  = $('input[name="filter_date"]').val().trim();
        var monthVal = $('select[name="month"]').val();
        var yearVal  = $('select[name="year"]').val();

        if(dateVal !== '') {
            // If a date is selected, hide month/year
            $('select[name="month"], select[name="year"]').hide();
            $('input[name="filter_date"]').show();
        } else if((monthVal !== '' && monthVal !== null) || (yearVal !== '' && yearVal !== null)) {
            // If month or year is selected, hide date
            $('input[name="filter_date"]').hide();
            $('select[name="month"], select[name="year"]').show();
        } else {
            // Nothing selected → show all
            $('input[name="filter_date"], select[name="month"], select[name="year"]').show();
        }
    }

    // Run on page load
    toggleInputs();

    // On change
    $('input[name="filter_date"]').on('change', function(){
        if($(this).val().trim() !== '') {
            $('select[name="month"], select[name="year"]').val('').hide();
        }
        toggleInputs();
    });

    $('select[name="month"], select[name="year"]').on('change', function(){
        if($('select[name="month"]').val() !== '' || $('select[name="year"]').val() !== '') {
            $('input[name="filter_date"]').val('').hide();
        }
        toggleInputs();
    });
});
</script>
<form method="get" id="topControlsForm" class="d-flex align-items-center" style="gap:10px; flex-wrap:nowrap;margin-top: 20px;">

    <!-- Rows per page -->
    <select name="limit" class="form-control" style="min-width:70px;">
        <option value="10"  <?php echo (isset($_GET['limit']) && $_GET['limit']==10)?'selected':''; ?>>10</option>
        <option value="20"  <?php echo (!isset($_GET['limit']) || $_GET['limit']==20)?'selected':''; ?>>20</option>
        <option value="50"  <?php echo (isset($_GET['limit']) && $_GET['limit']==50)?'selected':''; ?>>50</option>
        <option value="100" <?php echo (isset($_GET['limit']) && $_GET['limit']==100)?'selected':''; ?>>100</option>
    </select>
    
    <?php
    $filter_date = $_GET['filter_date'] ?? '';
    $month       = $_GET['month'] ?? '';
    $year        = $_GET['year'] ?? '';
    ?>
    <input type="date" name="filter_date" class="form-control"
           value="<?php echo htmlspecialchars($filter_date); ?>" style="max-width:160px;">
    
    <select name="month" class="form-control" style="max-width:120px;">
    <?php
    $months = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun',
               '07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'];
    foreach($months as $num => $name){
        $sel = ($num === $month) ? 'selected' : '';
        echo "<option value='$num' $sel>$name</option>";
    }
    ?>
    </select>
    
    <select name="year" class="form-control" style="max-width:100px;">
    <?php
    $startYear = 2020;
    $currentYear = date('Y');
    for($y=$startYear; $y<=$currentYear; $y++){
        $sel = ($y == $year) ? 'selected' : '';
        echo "<option value='$y' $sel>$y</option>";
    }
    ?>
    </select>

    <!-- Filter search -->
    <input type="text" name="search" class="form-control" placeholder="Filter Search"
           value="<?php echo isset($_GET['search'])?htmlspecialchars($_GET['search']):''; ?>" 
           style="flex-grow:1; min-width:150px;">
           
   <button type="submit" class="btn btn-primary">Filter</button>

</form>

    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered">
            <thead>
                <tr>
                    <th>SN</th>
                    <th>Call</th>
                    <th>Username</th>
                    <th>Points</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
<?php
$i=$offset+1;
$today=new DateTime();
if($user && mysqli_num_rows($user)>0){
while($row=mysqli_fetch_assoc($user)){
    $created = !empty($row['created_at']) ? date('d-m-Y',$row['created_at']) : 'N/A';
    $createdTime = !empty($row['created_at']) ? date('h:i A', $row['created_at']) : '';
    $statusClass = ($row['active']==1)?'badge-success':'badge-danger';
    $statusText  = ($row['active']==1)?'Active':'Deactive';
    $rowClass = ($row['active']==1)?'table-success':'table-danger';
?>
<tr class="<?php echo $rowClass;?>">
    <td>
        <a href="gateway-history.php?username=<?php echo urlencode($row['name']); ?>" style="text-decoration:none; color: #007bff; font-weight: bold; display:block; width:100%; height:100%;">
            <?php echo $i; ?>
        </a>
    </td>
    <td>
        <a href="javascript:void(0);" class="open-wp-modal" 
       data-name="<?php echo htmlspecialchars($row['name']);?>" 
       data-mobile="<?php echo $row['mobile'];?>">
       <i class="fas fa-phone-alt" style="color:#007bff;"></i>
        </a>
    </td>
    <!--<td><?php echo htmlspecialchars($row['name']);?><br><small>+91 <?php echo $row['mobile'];?></small></td>-->
    <td>
        <span class="username-link" onclick="openUserActions('<?php echo htmlspecialchars($row['name']); ?>', '<?php echo $row['mobile']; ?>')">
            <?php echo htmlspecialchars($row['name']);?>
        </span>
        <br><small>+91 <?php echo $row['mobile'];?></small>
    </td>
    <td><a href="user-wallet-history.php?user_mobile=<?php echo $row['mobile']; ?>" 
       style="text-decoration: none; color: #007bff; font-weight: bold; display: block;">
        <?php echo number_format($row['wallet'] ?? 0, 2); ?>
    </a></td>
    <!--<td><?php //echo $created;?></td>-->
     <td style="line-height:1.2;">
        <span style="font-size: 13px; font-weight: 500;"><?php echo $created; ?></span><br>
        <span style="font-size: 11px; color: #666;"><?php echo $createdTime; ?></span>
    </td>
    <td><a href="javascript:void(0);" class="badge toggle-status <?php echo $statusClass;?>" data-mobile="<?php echo $row['mobile'];?>" data-status="<?php echo $row['active'];?>"><?php echo $statusText;?></a></td>
    <td>
        <a href="user-profile.php?userID=<?php echo $row['mobile'];?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
        <a href="?deleteUser=<?php echo $row['sn'];?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete user?');"><i class="fas fa-trash"></i></a>
    </td>
</tr>
<?php $i++; } }else{ echo '<tr><td colspan="7" class="text-center">No users found</td></tr>';} ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if($totalPages>1): ?>
    <nav class="mt-2">
        <ul class="pagination justify-content-center flex-wrap">
            <?php if($page>1): ?>
            <li class="page-item"><a class="page-link" href="?page=<?php echo $page-1;?>&limit=<?php echo $limit;?>&search=<?php echo urlencode($search);?>">‹</a></li>
            <?php endif; ?>
            <?php
            $window=2; $start=max(1,$page-$window); $end=min($totalPages,$page+$window);
            for($p=$start;$p<=$end;$p++){
                $active=($p==$page)?' active':'';
                echo '<li class="page-item'.$active.'"><a class="page-link" href="?page='.$p.'&limit='.$limit.'&search='.urlencode($search).'">'.$p.'</a></li>';
            }
            ?>
            <?php if($page<$totalPages): ?>
            <li class="page-item"><a class="page-link" href="?page=<?php echo $page+1;?>&limit=<?php echo $limit;?>&search=<?php echo urlencode($search);?>">Next</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>

  </div>
</section>
<!-- WhatsApp/Call Modal -->
<div class="modal fade" id="wpPopupModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 400px;">
    <div class="modal-content" style="border-radius: 5px; border: none;">
      <div class="modal-header" style="border-bottom: 1px solid #f4f4f4;">
        <h5 class="modal-title" style="font-size: 1.2rem; font-weight: 500;">
            Name : <span id="pop_user_name" style="text-transform: lowercase;"></span>
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="padding: 25px 15px;">
        <div class="d-flex" style="gap: 12px;">
            <a href="#" id="pop_call_btn" class="btn" style="background-color: #11a1b3; color: white; border-radius: 20px; padding: 6px 20px; font-size: 14px;">Call</a>
            <a href="#" id="pop_wp_btn" target="_blank" class="btn" style="background-color: #28a745; color: white; border-radius: 20px; padding: 6px 20px; font-size: 14px;">Whats App</a>
        </div>
      </div>
      <div class="modal-footer" style="border-top: none; padding-top: 0;">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="background-color: #6c757d; border-radius: 10px; min-width: 80px;">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- User Actions Popup Modal -->
<div class="modal fade" id="userActionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Name : <span id="modalUserName"></span></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
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
        </div>
    </div>
</div>

<!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Notification To <span id="notifUserName"></span></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="notifUserMobile">
                <input type="text" class="form-control mb-2" id="notifTitle" placeholder="Title" style="border-radius:20px;">
                <textarea class="form-control" id="notifMsg" rows="3" placeholder="Message" style="border-radius:20px;"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="submitNotification()" style="border-radius:25px;">Send</button>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function(){
    $('.toggle-status').click(function(){
        var badge=$(this);
        var mobile=badge.data('mobile');
        var current=badge.data('status');
        var newStatus=(current==1)?0:1;
        $.ajax({
            url:'user_filter.php',
            type:'POST',
            data:{ajax_toggle_status:1,mobile:mobile,status:newStatus},
            dataType:'json',
            // success:function(resp){
            //     if(resp.success) location.reload();
            //     else alert(resp.message);
            // }
            success: function(resp) {
            if (resp.success) {
                // 1. Update the data-status attribute for the next click
                badge.data('status', newStatus);

                // 2. Update Text and Colors instantly without reloading
                if (newStatus === 1) {
                    badge.text('Active').removeClass('badge-danger').addClass('badge-success');
                    row.removeClass('table-danger').addClass('table-success');
                } else {
                    badge.text('Deactive').removeClass('badge-success').addClass('badge-danger');
                    row.removeClass('table-success').addClass('table-danger');
                }
            } else {
                alert("Error: " + resp.message);
            }
        },
        error: function() {
            // alert("Connection error. Refreshing page...");
            location.reload();
        }
        });
    });
    
    $('.open-wp-modal').on('click', function() {
        var name = $(this).data('name');
        var mobile = $(this).data('mobile');
        
        // Update Modal Content
        $('#pop_user_name').text(name);
        $('#pop_call_btn').attr('href', 'tel:+91' + mobile);
        $('#pop_wp_btn').attr('href', 'https://api.whatsapp.com/send?phone=91' + mobile);
        
        // Show Modal
        $('#wpPopupModal').modal('show');
    });
});
var currentName = "";
var currentMobile = "";

function openUserActions(name, mobile) {
    currentName = name;
    currentMobile = mobile;

    document.getElementById('modalUserName').innerText = name;

    // Set URLs for the buttons
    document.getElementById('link-add').href = "add-money-manual.php?mobile=" + mobile;
    document.getElementById('link-withdraw').href = "withdraw-money-manual.php?mobile=" + mobile;
    document.getElementById('link-cancel').href = "game-cancel.php?mobile=" + mobile;
    document.getElementById('link-pl').href = "transaction.php?mobile=" + mobile;
    document.getElementById('link-logs').href = "activity-logs.php?mobile=" + mobile;

    // Handle Notification Button Click
    document.getElementById('link-notify').onclick = function(e) {
        e.preventDefault();
        $('#userActionModal').modal('hide');
        document.getElementById('notifUserName').innerText = currentName;
        document.getElementById('notifUserMobile').value = currentMobile;
        setTimeout(function() { $('#notificationModal').modal('show'); }, 400);
    };

    $('#userActionModal').modal('show');
}

function submitNotification() {
    var title = document.getElementById('notifTitle').value;
    var msg = document.getElementById('notifMsg').value;
    if(title == "" || msg == "") { alert("Enter title and message"); return; }
    
    alert("Notification sent to " + currentMobile);
    $('#notificationModal').modal('hide');
}
</script>

<?php include('footer.php'); ?>