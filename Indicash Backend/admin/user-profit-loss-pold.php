<?php
include('header.php');

// 1. SECURITY CHECK
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

// 2. GET DATE FROM URL OR DEFAULT TO TODAY
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : date('Y-m-d');
$date_dmY = date('d/m/Y', strtotime($filter_date)); // Format for games table

// 3. FILTER & PAGINATION LOGIC
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page-1) * $limit;
$search = $_GET['search'] ?? '';

// Check if we only want active users (from dashboard click)
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

$totalQuery = mysqli_query($con, "SELECT COUNT(*) as total FROM users $whereSQL");
$totalRows = mysqli_fetch_assoc($totalQuery)['total'];
$totalPages = ceil($totalRows / $limit);
// Fetch Users
$userResult = mysqli_query($con, "SELECT * FROM users $whereSQL ORDER BY sn DESC LIMIT $limit OFFSET $offset");
?>

<style>
.header-title{
    background:#ffc107;
    color:#000;
    padding:14px;
    text-align:center;
    border-radius:40px;
    font-weight:700;
    margin:15px 0;
    font-size:20px;
}

.filter-card{
    background:#e0e0e0;
    padding:12px;
    border-radius:40px;
    margin-bottom:15px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
}

/* TABLE DESIGN */
.table{
    border-collapse:separate;
    border-spacing:0 12px;
    width:100%;
}

.table thead th{
    background:#1f5fae;
    color:#fff;
    padding:10px;
    text-align:center;
    font-weight:600;
    font-size:15px;
    border-right:1px solid #dcdcdc;
}

.table tbody tr{
    background:#f5f5f5;
    border-radius:15px;
    background: #fff;
}

.table tbody td{
    text-align:center;
    vertical-align:middle;
    font-size:15px;
    font-weight:500;
    border-right:1px solid #e5e5e5;
    padding:10px;
}

/* COLUMN COLORS */
.col-play{

    font-weight:600;
}

.col-win{
 
    font-weight:600;
}
/* CENTER VIEW BUTTON */
.col-view{
    display:flex;
    justify-content:center;
    align-items:center;
}

.col-view .btn{
    display:flex;
    align-items:center;
    justify-content:center;
}
.col-pl{
    background:#a5d6a7;
    font-weight:700;
}
.pagination .page-link { 
    border-radius: 20px; 
    margin: 0 3px; 
    color: #1b66b1; 
    border: 1px solid #ddd;
}
.pagination .page-item.active .page-link { 
    background: #1b66b1; 
    border-color: #1b66b1; 
    color: white;
}
.col-add{
    background:#0a8f08;
    color:#fff;
    font-weight:700;
}

.col-withdraw{
    background:#b30000;
    color:#fff;
    font-weight:700;
}

/* USERNAME STYLE */
.username{
    font-weight:600;
}

.username small{
    color:#666;
    font-size:12px;
}

/* VIEW BUTTON */
.btn-info{
    background:#17a2b8;
    border:none;
    border-radius:50%;
    width:35px;
    height:35px;
    display:flex;
    align-items:center;
    justify-content:center;
}
.table thead th:last-child,
.table tbody td:last-child{
    border-right:none;
}
/* LEFT ROUND (SN) */
.table tbody td:first-child{
    border-radius:15px 0 0 15px;
}

/* RIGHT ROUND (VIEW) */
.table tbody td:last-child{
    border-radius:0 15px 15px 0;
}
/* MOBILE LOOK */
@media(max-width:768px){

.table thead th{
    font-size:13px;
}

.table tbody td{
    font-size:13px;
    padding:8px;
}

}
</style>

<div class="content">
    <div class="container-fluid">
        
        <div class="header-title">User Profit Loss</div>

        <!-- Date Filter Form -->
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
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>SN</th>
                        <th>Play</th>
                        <th>Win</th>
                        <th>PL</th>
                        <th>username</th>
                        <th>Add</th>
                        <th>Withdraw</th>
                        <th>point</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sn_count = $offset + 1;
                    while($u = mysqli_fetch_assoc($userResult)) { 
                        $mob = $u['mobile'];

                        // 1. Play (Filtering by selected date)
                        $qPlay = mysqli_query($con, "SELECT SUM(amount) as total FROM games WHERE user='$mob' AND date='$date_dmY'");
                        $play = mysqli_fetch_assoc($qPlay)['total'] ?? 0;

                        // 2. Win (Filtering by selected date and status=1, calculating amount * 9)
                        $qWin = mysqli_query($con, "SELECT SUM(amount*9) as total FROM games WHERE user='$mob' AND date='$date_dmY' AND (status='1' OR status='win')");
                        $win = mysqli_fetch_assoc($qWin)['total'] ?? 0;

                        $pl = $play - $win;

                        // 3. Add (Payments) - Filtering by date
                        $qAdd = mysqli_query($con, "SELECT SUM(amount) as total FROM payments WHERE mobile='$mob' AND status='SUCCESS' AND DATE(created_at)='$filter_date'");
                        $add = mysqli_fetch_assoc($qAdd)['total'] ?? 0;

                        // 4. Withdraw - Filtering by date
                        $qWd = mysqli_query($con, "SELECT SUM(amount) as total FROM withdraw_requests WHERE mobile='$mob' AND status='APPROVED' AND DATE(created_at)='$filter_date'");
                        $withdraw = mysqli_fetch_assoc($qWd)['total'] ?? 0;
                    ?>
                    <tr>
                        <td style="color:#1e70cd;" class="col-sn" >
                            <?php //echo $sn_count++; ?>
                             <a href="gateway-history.php?username=<?php echo urlencode($u['name']); ?>" style="text-decoration:none; color:inherit; display:block; width:100%; height:100%;">
                                <?php echo $sn_count++; ?>
                            </a>
                            </td>
                        <td class="col-play"><?php echo $play; ?></td>
                        <td class="col-win"><?php echo $win; ?></td>
                        <td class="col-pl"><?php echo $pl; ?></td>
                        <td class="username"><?php echo htmlspecialchars($u['name']); ?></td>  
                        <td class="col-add"><?php echo $add; ?></td>
                        <td class="col-withdraw"><?php echo $withdraw; ?></td>
                        <td class="col-point">
                            <a href="user-wallet-history.php?user_mobile=<?php echo $u['mobile']; ?>" 
                               style="text-decoration:none; color:#1e70cd; font-weight:bold; display:block; width:100%;">
                                <?php echo number_format($u['wallet'], 2); ?>
                            </a>
                        </td>
                        <td class="col-view">
                            <a href="user-profile.php?userID=<?php echo $u['mobile']; ?>" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
                        <!-- PAGINATION NAVIGATION -->
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $page-1; ?>&filter_date=<?php echo $filter_date; ?>&limit=<?php echo $limit; ?>&search=<?php echo $search; ?><?php echo ($active_only != "" ? "&active=1" : ""); ?>">Previous</a></li>
                    <?php endif; ?>
            
                    <?php
                    // Show 2 pages before and 2 pages after current page
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    for($i = $start; $i <= $end; $i++): 
                    ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&filter_date=<?php echo $filter_date; ?>&limit=<?php echo $limit; ?>&search=<?php echo $search; ?><?php echo ($active_only != "" ? "&active=1" : ""); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
            
                    <?php if($page < $totalPages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $page+1; ?>&filter_date=<?php echo $filter_date; ?>&limit=<?php echo $limit; ?>&search=<?php echo $search; ?><?php echo ($active_only != "" ? "&active=1" : ""); ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <p class="text-center text-muted">Showing page <?php echo $page; ?> of <?php echo $totalPages; ?> (Total: <?php echo $totalRows; ?> users)</p>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>