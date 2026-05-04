<?php $page = basename($_SERVER['PHP_SELF']); ?>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<?php

$servername = "localhost";
$username = "apluscrm_mtkkdb";
$password = "&RNDrt3LA3sF";
$dbname = "apluscrm_mtkdb";

$con = mysqli_connect($servername, $username, $password, $dbname);

try {
    $dsn = "mysql:host=localhost;dbname=apluscrm_mtkdb;charset=utf8";
    $usernamee = "apluscrm_mtkkdb";
    $passwords = "&RNDrt3LA3sF";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $usernamee, $passwords, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}



$stamp = time();
  
function getPatti(){
    $numbers[] ="100"; $numbers[] ="119"; $numbers[] ="155"; $numbers[] ="227"; $numbers[] ="335";
    $numbers[] ="344"; $numbers[] ="399"; $numbers[] ="588"; $numbers[] ="669"; $numbers[] ="200";
    $numbers[] ="110"; $numbers[] ="228"; $numbers[] ="255"; $numbers[] ="336"; $numbers[] ="499";
    $numbers[] ="660"; $numbers[] ="688"; $numbers[] ="778"; $numbers[] ="300"; $numbers[] ="166";
    $numbers[] ="229"; $numbers[] ="337"; $numbers[] ="355"; $numbers[] ="445"; $numbers[] ="599";
    $numbers[] ="779"; $numbers[] ="788"; $numbers[] ="400"; $numbers[] ="112"; $numbers[] ="220";
    $numbers[] ="266"; $numbers[] ="338"; $numbers[] ="446"; $numbers[] ="455"; $numbers[] ="699";
    $numbers[] ="770"; $numbers[] ="500"; $numbers[] ="113"; $numbers[] ="122"; $numbers[] ="177";
    $numbers[] ="339"; $numbers[] ="366"; $numbers[] ="447"; $numbers[] ="799"; $numbers[] ="889";
    $numbers[] ="600"; $numbers[] ="114"; $numbers[] ="277"; $numbers[] ="330"; $numbers[] ="448";
    $numbers[] ="466"; $numbers[] ="556"; $numbers[] ="880"; $numbers[] ="899"; $numbers[] ="700";
    $numbers[] ="115"; $numbers[] ="133"; $numbers[] ="188"; $numbers[] ="223"; $numbers[] ="377";
    $numbers[] ="449"; $numbers[] ="557"; $numbers[] ="566"; $numbers[] ="800"; $numbers[] ="116";
    $numbers[] ="224"; $numbers[] ="233"; $numbers[] ="288"; $numbers[] ="440"; $numbers[] ="477";
    $numbers[] ="558"; $numbers[] ="990"; $numbers[] ="900"; $numbers[] ="117"; $numbers[] ="144";
    $numbers[] ="199"; $numbers[] ="225"; $numbers[] ="388"; $numbers[] ="559"; $numbers[] ="577";
    $numbers[] ="667"; $numbers[] ="550"; $numbers[] ="668"; $numbers[] ="244"; $numbers[] ="299";
    $numbers[] ="226"; $numbers[] ="488"; $numbers[] ="677"; $numbers[] ="118"; $numbers[] ="334";
    $numbers[] ="128"; $numbers[] ="137"; $numbers[] ="146"; $numbers[] ="236"; $numbers[] ="245";
    $numbers[] ="290"; $numbers[] ="380"; $numbers[] ="470"; $numbers[] ="489"; $numbers[] ="560";
    $numbers[] ="678"; $numbers[] ="579"; $numbers[] ="129"; $numbers[] ="138"; $numbers[] ="147";
    $numbers[] ="156"; $numbers[] ="237"; $numbers[] ="246"; $numbers[] ="345"; $numbers[] ="390";
    $numbers[] ="480"; $numbers[] ="570"; $numbers[] ="679"; $numbers[] ="120"; $numbers[] ="139";
    $numbers[] ="148"; $numbers[] ="157"; $numbers[] ="238"; $numbers[] ="247"; $numbers[] ="256";
    $numbers[] ="346"; $numbers[] ="490"; $numbers[] ="580"; $numbers[] ="670"; $numbers[] ="689";
    $numbers[] ="130"; $numbers[] ="149"; $numbers[] ="158"; $numbers[] ="167"; $numbers[] ="239";
    $numbers[] ="248"; $numbers[] ="257"; $numbers[] ="347"; $numbers[] ="356"; $numbers[] ="590";
    $numbers[] ="680"; $numbers[] ="789"; $numbers[] ="140"; $numbers[] ="159"; $numbers[] ="168";
    $numbers[] ="230"; $numbers[] ="249"; $numbers[] ="258"; $numbers[] ="267"; $numbers[] ="348";
    $numbers[] ="357"; $numbers[] ="456"; $numbers[] ="690"; $numbers[] ="780"; $numbers[] ="123";
    $numbers[] ="150"; $numbers[] ="169"; $numbers[] ="178"; $numbers[] ="240"; $numbers[] ="259";
    $numbers[] ="268"; $numbers[] ="349"; $numbers[] ="358"; $numbers[] ="457"; $numbers[] ="367";
    $numbers[] ="790"; $numbers[] ="124"; $numbers[] ="160"; $numbers[] ="179"; $numbers[] ="250";
    $numbers[] ="269"; $numbers[] ="278"; $numbers[] ="340"; $numbers[] ="359"; $numbers[] ="368";
    $numbers[] ="458"; $numbers[] ="467"; $numbers[] ="890"; $numbers[] ="125"; $numbers[] ="134";
    $numbers[] ="170"; $numbers[] ="189"; $numbers[] ="260"; $numbers[] ="279"; $numbers[] ="350";
    $numbers[] ="369"; $numbers[] ="378"; $numbers[] ="459"; $numbers[] ="567"; $numbers[] ="468";
    $numbers[] ="126"; $numbers[] ="135"; $numbers[] ="180"; $numbers[] ="234"; $numbers[] ="270";
    $numbers[] ="289"; $numbers[] ="360"; $numbers[] ="379"; $numbers[] ="450"; $numbers[] ="469";
    $numbers[] ="478"; $numbers[] ="568"; $numbers[] ="127"; $numbers[] ="136"; $numbers[] ="145";
    $numbers[] ="190"; $numbers[] ="235"; $numbers[] ="280"; $numbers[] ="370"; $numbers[] ="479";
    $numbers[] ="460"; $numbers[] ="569"; $numbers[] ="389"; $numbers[] ="578"; $numbers[] ="589";
    $numbers[] ="000"; $numbers[] ="111"; $numbers[] ="222"; $numbers[] ="333"; $numbers[] ="444";
    $numbers[] ="555"; $numbers[] ="666"; $numbers[] ="777"; $numbers[] ="888"; $numbers[] ="999";
    return $numbers;
}

function getOpenCloseTiming($xc){
    global $day, $time;
    
    if(!empty($day) && ($xc['days'] == "ALL" || substr_count($xc['days'], $day) == 0)){
        if(strtotime($time) < strtotime($xc['open'])) { $xc['is_open'] = "1"; } else { $xc['is_open'] = "0"; }
        if(strtotime($time) < strtotime($xc['close'])) { $xc['is_close'] = "1"; } else { $xc['is_close'] = "0"; }
    } else if(!empty($day) && substr_count($xc['days'], $day . "(CLOSE)") > 0) {
        $xc['is_open'] = "0"; $xc['is_close'] = "0";
        $xc['open'] = "CLOSE"; $xc['close'] = "CLOSE";
    } else if(!empty($day)) {
        $time_array = explode(",", $xc['days']);
        for($i = 0; $i < count($time_array); $i++){
            if(substr_count($time_array[$i], $day) > 0){
                $day_conf = $time_array[$i];
            }
        }
        $day_conf = str_replace($day . "(", "", $day_conf);
        $day_conf = str_replace(")", "", $day_conf);
        $mrk_time = explode("-", $day_conf);

        $xc['open'] = $mrk_time[0]; $xc['close'] = $mrk_time[1];

        if(strtotime($time) < strtotime($mrk_time[0])) { $xc['is_open'] = "1"; } else { $xc['is_open'] = "0"; }
        if(strtotime($time) < strtotime($mrk_time[1])) { $xc['is_close'] = "1"; } else { $xc['is_close'] = "0"; }
    }
    return $xc;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <!--<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">-->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Panel | Dashboard</title>
  <link rel="stylesheet" href="plugins/fonts.googleapis.com/Source_Sans_Pro.css">
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
  <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">
  <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <script src="plugins/jquery/jquery.min.js"></script>
</head>
<style>
  /* Prevent horizontal scroll on mobile */
 
  
  body {
    overflow-x: hidden;
}
  
  .sidebar-dark-primary .sidebar { background-color: #ffffff !important; }
  .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link { color: #000000 !important; }
  .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link:hover { background-color: #000000 !important; color: #ffffff !important; }
  .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active { background-color: #000000 !important; color: #ffffff !important; }
  .sidebar-dark-primary .nav-sidebar .nav-treeview > .nav-item > .nav-link { color: #333333 !important; }
  .sidebar-dark-primary .nav-sidebar .nav-treeview > .nav-item > .nav-link:hover { background-color: #000000 !important; color: #ffffff !important; }
  .sidebar-dark-primary .nav-sidebar .nav-treeview > .nav-item > .nav-link.active { background-color: #000000 !important; color: #ffffff !important; }
  .sidebar-dark-primary .nav-sidebar > .nav-item.menu-open > .nav-link { background-color: #000000 !important; color: #ffffff !important; }
  .sidebar-dark-primary .brand-link { background-color: #ffffff !important; color: #000000 !important; border-bottom: 1px solid #dee2e6 !important; }
  .sidebar-dark-primary .brand-link:hover { background-color: #f8f9fa !important; }
  .sidebar-dark-primary .user-panel { border-bottom: 1px solid #dee2e6 !important; }
  .sidebar-dark-primary .nav-sidebar .nav-link p .badge { background-color: #dc3545 !important; color: #ffffff !important; }
  .sidebar-dark-primary .nav-sidebar .nav-link.active { background-color: #000000 !important; color: #ffffff !important; }
  .sidebar-dark-primary .nav-sidebar > .nav-item.menu-open.bg-danger > .nav-link { background-color: #dc3545 !important; color: #ffffff !important; }
  .sidebar-dark-primary .nav-sidebar > .nav-item.menu-open.bg-danger > .nav-link:hover { background-color: #c82333 !important; color: #ffffff !important; }
  
  .notification-badge { position: relative; }
  .notification-badge-green { background-color: white; color: white; }
  .tooltip {
    visibility: hidden; width: 120px; background-color: rgba(0, 0, 0, 0.7);
    color: #fff; text-align: center; border-radius: 5px; padding: 5px;
    position: absolute; z-index: 1; bottom: 125%; left: 50%;
    margin-left: -60px; opacity: 0; transition: opacity 0.3s;
  }
  .notification-badge:hover .tooltip { visibility: visible; opacity: 1; }
  
  /* Prevent Body Scroll when Popup is open */
  body.popup-open {
    overflow: hidden;
}


.sidebar .nav-sidebar > .nav-item > .nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    font-size: 16px;
    font-weight: 500;
    color: #2c2c2c !important;
    background: transparent !important;
    border-radius: 0;
}

.sidebar .nav-sidebar > .nav-item > .nav-link i {
    font-size: 18px;
    width: 22px;
    text-align: center;
    color: #000;
}

.sidebar .nav-sidebar > .nav-item > .nav-link:hover {
    background-color: #f2f2f2 !important;
}

.sidebar .nav-sidebar > .nav-item > .nav-link.active {
    background-color: #f2f2f2 !important;
    color: #000 !important;
    font-weight: 600;
}

/* Remove arrows & borders */
.sidebar .nav-sidebar .right,
.sidebar .nav-sidebar .fas.fa-angle-left {
    display: none;
}

/* Spacing between items */
.sidebar .nav-sidebar > .nav-item {
    margin-bottom: 2px;
}

 .main-header {
    position: relative;
    z-index: 1050 !important;
}
 /* Sidebar Divider Line */
.nav-sidebar .nav-divider {
    height: 1px;
    background-color: #909090; /* Light grey color */
    margin: 10px 15px;
    opacity: 0.8;
}

/* 1. Default Sidebar Link Appearance */
.sidebar .nav-sidebar > .nav-item > .nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 18px;
    font-size: 15px;
    font-weight: 500;
    color: #333 !important; /* Default dark grey text */
    background: transparent !important;
    border-radius: 4px;
    margin: 2px 10px;
    transition: background 0.2s;
}

/* 2. Default Icon Color */
.sidebar .nav-sidebar > .nav-item > .nav-link i {
    font-size: 18px;
    width: 22px;
    text-align: center;
    color: #444; 
}

/* 3. ACTIVE STATE ONLY (When clicked/on the page) */
.sidebar .nav-sidebar > .nav-item > .nav-link.active {
    background-color: #000000 !important; /* Black Background */
    color: #ffffff !important;            /* White Text */
    font-weight: 600;
}

/* 4. Icon inside Active Link */
.sidebar .nav-sidebar > .nav-item > .nav-link.active i {
    color: #ffffff !important;            /* White Icon */
}

/* 5. Subtle Hover (Light grey only, not black) */
.sidebar .nav-sidebar > .nav-item > .nav-link:hover:not(.active) {
    background-color: #f4f6f9 !important; 
    color: #000 !important;
}
</style>

<body class="hold-transition sidebar-mini layout-fixed sidebar-light-primary">
<div class="wrapper">

<?php 
  $idd = $_SESSION['userID'];
  $sql = "SELECT * FROM admin WHERE email='". $idd ."'";
  $result = $con->query($sql);
  $row = $result->fetch_assoc();
  $rol = $row['tasks'];
  $walllet = $row['wallet'];
  $sql2 = "SELECT * FROM task_manager WHERE id='". $rol ."'";
  $result2 = $con->query($sql2);
  $row2 = $result2->fetch_assoc();
  $HiddenProducts = explode(',',$row2['tasks']);
?>

  <!--<nav class="main-header navbar navbar-expand navbar-white navbar-light">-->
  <!--  <ul class="navbar-nav">-->
  <!--    <li class="nav-item">-->
  <!--      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>-->
  <!--    </li>-->
  <!--  </ul>-->

  <!--  <ul class="navbar-nav ml-auto">-->
  <!--    <li class="nav-item">-->
  <!--        <a class="nav-link" href="download-backup.php" role="button" title="Download Database Backup">-->
  <!--            <i class="fas fa-download" style="font-size:20px;color:green;"></i>-->
  <!--            <span class="badge badge-success" style="position: absolute; top: -5px; right: -1px;"></span>-->
  <!--        </a>-->
  <!--    </li>-->
  <!--    <li class="nav-item">-->
  <!--        <a class="nav-link" href="withdraw-points-request.php" role="button">-->
  <!--        <i class='fas fa-bell' style='font-size:20px;color:red'></i>-->
  <!--            <span class="badge badge-danger" id="pending-withdrawal-count" style="position: absolute; top: -5px; right: -1px;">0</span>-->
  <!--        </a>-->
  <!--    </li>-->
  <!--    <li class="nav-item">-->
  <!--        <a class="nav-link" href="auto_deposite_display.php" role="button">-->
  <!--        <i class='fas fa-bell' style='font-size:20px;color:green'></i>-->
  <!--            <span class="badge badge-primary notification-badge-green" id="pending-deposit-count" style="position: absolute; top: -5px; right: 1px;">0-->
  <!--                <span class="tooltip">Pending Deposits</span>-->
  <!--            </span>-->
  <!--        </a>-->
  <!--    </li>-->
  <!--    <li class="nav-item"><a class="nav-link" data-widget="fullscreen" href="logout.php" role="button"><i class="fas fa-power-off"></i></a></li>-->
  <!--    <li class="nav-item"><a class="nav-link" data-widget="fullscreen" href="#" role="button"><i class="fas fa-expand-arrows-alt"></i></a></li>-->
  <!--    <li class="nav-item"><a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button"><i class="fas fa-th-large"></i></a></li>-->
  <!--  </ul>-->
  <!--</nav>-->
  
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left side: Hamburger menu -->
    <!--<ul class="navbar-nav">-->
    <!--    <li class="nav-item">-->
    <!--        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>-->
    <!--    </li>-->
    <!--</ul>-->
    <!-- Left side: Hamburger menu and Brand Name -->
    <ul class="navbar-nav align-items-center">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars" style="color:#000; font-size:22px;"></i></a>
        </li>
        <li class="nav-item">
            <span style="font-size: 19px; font-weight: bold; color: #333; margin-left: 10px; font-family: 'Poppins', sans-serif;">IndiCash Admin</span>
        </li>
    </ul>

    <!-- Right side: Logic for Desktop vs Mobile -->
    <ul class="navbar-nav ml-auto align-items-center">

        <!-- [MOBILE ONLY] Date, Time, and 3-Dot Menu -->
        <li class="nav-item d-md-none mr-2 text-right" style="line-height: 1.2;">
            <div class="dashboard-date" style="font-size: 12px; font-weight: bold; color: #333;"></div>
            <div class="dashboard-clock" style="font-size: 12px; color: #666;"></div>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="javascript:void(0)" onclick="location.reload()">
                <i class="fas fa-sync-alt" style="font-size: 22px; color: #000; font-weight: bold;"></i>
            </a>
        </li>

        <li class="nav-item d-md-none">
            <a class="nav-link" href="javascript:void(0)" onclick="openMobileHeaderMenu()">
                <i class="fas fa-ellipsis-v" style="font-size: 18px; color: #000;"></i>
            </a>
        </li>

        <!-- [DESKTOP ONLY] Original 6 Icons -->
        <div class="d-none d-md-flex align-items-center">
            <li class="nav-item">
                <a class="nav-link" href="download-backup.php" title="Download Database Backup">
                    <i class="fas fa-download" style="font-size:20px;color:green;"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="withdraw-points-request.php">
                    <i class='fas fa-bell' style='font-size:20px;color:red'></i>
                    <span class="badge badge-danger pending-withdrawal-count" style="position: absolute; top: -5px; right: -1px;">0</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="auto_deposite_display.php">
                    <i class='fas fa-bell' style='font-size:20px;color:green'></i>
                    <span class="badge badge-primary pending-deposit-count" style="position: absolute; top: -5px; right: 1px;">0</span>
                </a>
            </li>
            <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-power-off"></i></a></li>
            <li class="nav-item"><a class="nav-link" data-widget="fullscreen" href="#"><i class="fas fa-expand-arrows-alt"></i></a></li>
            <li class="nav-item"><a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#"><i class="fas fa-th-large"></i></a></li>
        </div>
    </ul>
</nav>

<!-- [MOBILE ONLY] The Vertical Popup for 6 Icons -->
<div id="mobileHeaderOverlay" class="settings-overlay" onclick="closeMobileHeaderMenu()"></div>
<div id="mobileHeaderPopup" class="bottom-popup">
    <div class="popup-handle-bar"></div>
    <div class="popup-content">
        <div class="text-center mb-3 font-weight-bold" style="font-size: 1.1rem; border-bottom: 1px solid #eee; padding-bottom: 10px;">Menu</div>
        
        <a href="download-backup.php" class="popup-item text-left px-4">
            <i class="fas fa-download mr-3 text-success"></i> Backup Database
        </a>
        <a href="withdraw-points-request.php" class="popup-item text-left px-4">
            <i class="fas fa-bell mr-3 text-danger"></i> Withdrawals 
            <span class="badge badge-danger ml-2 pending-withdrawal-count">0</span>
        </a>
        <a href="auto_deposite_display.php" class="popup-item text-left px-4">
            <i class="fas fa-bell mr-3 text-success"></i> Deposits 
            <span class="badge badge-primary ml-2 pending-deposit-count">0</span>
        </a>
        <a href="javascript:void(0)" onclick="document.documentElement.requestFullscreen()" class="popup-item text-left px-4">
            <i class="fas fa-expand-arrows-alt mr-3"></i> Full Screen
        </a>
        <a href="#" class="popup-item text-left px-4">
            <i class="fas fa-th-large mr-3"></i> Quick Settings
        </a>
        <a href="logout.php" class="popup-item text-left px-4 text-danger font-weight-bold">
            <i class="fas fa-power-off mr-3"></i> Logout
        </a>
    </div>
</div>

  <aside class="main-sidebar sidebar-light-primary">
    <a href="dashboard1.php" class="brand-link">
      <img src="../admin/upload/dmboss.png" alt="AdminLTE Logo" class="brand-image img-circle" style="opacity:1;">
      <span class="brand-text font-weight-bold h4">Indicash</span>
    </a>

    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          
       <?php if (in_array(1, $HiddenProducts)) { ?>
  <li class="nav-item">
    <a href="dashboard1.php"
       class="nav-link <?php echo ($page == 'dashboard1.php') ? 'active' : ''; ?>">
       <i class="nav-icon fas fa-th-large"></i>
       <p>Dashboard</p>
    </a>
  </li>
<?php } ?>

        
        <?php if (in_array(2, $HiddenProducts)){ ?>
         <!--<li class="nav-item">-->
         <!--   <a href="#" class="nav-link">-->
         <!--     <i class="nav-icon fas fa-edit"></i><p>Role Management <i class="fas fa-angle-left right"></i></p>-->
         <!--   </a>-->
         <!--   <ul class="nav nav-treeview">-->
         <!--     <li class="nav-item"><a href="task.php" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Create Role</p></a></li>-->
         <!--     <li class="nav-item"><a href="asign_task.php" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Assign Role</p></a></li>-->
         <!--     <li class="nav-item"><a href="crud_task.php" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Manage Users </p></a></li>-->
         <!--   </ul>-->
         <!-- </li>-->
        <?php } ?>
        
        <?php if (in_array(4, $HiddenProducts)){ 
          $current_page = basename($_SERVER['PHP_SELF']); ?>
          <li class="nav-item">
          <a href="users_old.php" class="nav-link <?php echo ($current_page == 'users_old.php') ? 'active' : ''; ?>" style="<?php echo ($current_page == 'users_old.php') ? 'background-color: #ffffff; color: #000000;' : ''; ?>">
          <i class="fas fa-list-ul"></i><p>User List </p></a>
        </li>
          <li class="nav-divider"></li> <!-- ADD THIS LINE -->
        <?php } ?>

    <!--    <?php if (in_array(5, $HiddenProducts)){ 
         $current_page = basename($_SERVER['PHP_SELF']); ?>
         <li class="nav-item">
          <a href="winning-prediction.php" class="nav-link <?php echo ($current_page == 'winning-prediction.php') ? 'active' : ''; ?>" style="<?php echo ($current_page == 'winning-prediction.php') ? 'background-color: #ffffff; color: #000000;' : ''; ?>">
          <i class="nav-icon fas fa-th"></i><p>Winner prediction </p></a>
        </li>
      <?php } ?>-->
       
       <?php if (in_array(6, $HiddenProducts)){ 
         $current_page = basename($_SERVER['PHP_SELF']); ?>
         <li class="nav-item">
          <a href="transaction.php" class="nav-link <?php echo ($current_page == 'transaction.php') ? 'active' : ''; ?>" style="<?php echo ($current_page == 'transaction.php') ? 'background-color: #ffffff; color: #000000;' : ''; ?>">
          <i class="fas fa-bookmark"></i><p>Profit Loss </p></a>
        </li>
       <?php } ?>

       <?php if (in_array(7, $HiddenProducts)){ 
          $current_page = basename($_SERVER['PHP_SELF']); ?>
          <li class="nav-item">
         <a href="declare-result.php" class="nav-link <?php echo ($current_page == 'declare-result.php') ? 'active' : ''; ?>" style="<?php echo ($current_page == 'declare-result.php') ? 'background-color: #ffffff; color: #000000;' : ''; ?>">
         <i class="fas fa-chart-area"></i><p> Result & Report</p></a>
      </li>
     <?php } ?>

        <?php if (in_array(8, $HiddenProducts)){ 
           $current_page = basename($_SERVER['PHP_SELF']); ?>
        <li class="nav-item">
        <a href="add-points-user-wallet.php" class="nav-link <?php echo ($current_page == 'add-points-user-wallet.php') ? 'active' : ''; ?>" style="<?php echo ($current_page == 'add-points-user-wallet.php') ? 'background-color: #ffffff; color: #000000;' : ''; ?>">
         <i class="fas fa-angle-double-left"></i><p>Add Money History</p></a>
      </li>
     <?php } ?> 

        <?php if (in_array(9, $HiddenProducts)){ 
        $current_page = basename($_SERVER['PHP_SELF']); ?>
        <li class="nav-item">
        <a href="withdraw-points-request.php" class="nav-link <?php echo ($current_page == 'withdraw-points-request.php') ? 'active' : ''; ?>" style="<?php echo ($current_page == 'withdraw-points-request.php') ? 'background-color: #ffffff; color: #000000;' : ''; ?>">
         <i class="fas fa-redo"></i><p>Withdraw Money History</p></a>
        </li>
       <?php } ?>
       <?php if (in_array(9, $HiddenProducts)){ 
        $current_page = basename($_SERVER['PHP_SELF']); ?>
        <li class="nav-item">
        <a href="report.php" class="nav-link <?php echo ($current_page == 'report.php') ? 'active' : ''; ?>" style="<?php echo ($current_page == 'report.php') ? 'background-color: #ffffff; color: #000000;' : ''; ?>">
         <i class="fas fa-chart-bar"></i><p>Report Generation</p></a>
        </li>
       <?php } ?>
       

        <?php if (in_array(10, $HiddenProducts)){ 
        $current_page = basename($_SERVER['PHP_SELF']); ?>
        <li class="nav-item">
       <!--<a href="transaction1.php" class="nav-link <?php echo ($current_page == 'transaction1.php') ? 'active' : ''; ?>" style="<?php echo ($current_page == 'transaction1.php') ? 'background-color: #ffffff; color: #000000;' : ''; ?>">-->
       <a href="user-wallet-history.php" class="nav-link <?php echo ($current_page == 'user-wallet-history.php') ? 'active' : ''; ?>" style="<?php echo ($current_page == 'user-wallet-history.php') ? 'background-color: #ffffff; color: #000000;' : ''; ?>">
         <i class="fas fa-exchange-alt"></i><p>Transaction</p></a>
      </li>
    <?php } ?> 
     
      <?php if (in_array(10, $HiddenProducts)){ 
        $current_page = basename($_SERVER['PHP_SELF']); ?>
        <li class="nav-item">
       <a href="winners.php" class="nav-link <?php echo ($current_page == 'winners.php') ? 'active' : ''; ?>" style="<?php echo ($current_page == 'winners.php') ? 'background-color: #ffffff; color: #000000;' : ''; ?>">
         <i class="fas fa-trophy"></i><p>Win History</p></a>
      </li>
    <li class="nav-divider"></li> <!-- ADD THIS LINE -->
    <?php } ?> 
    
     <?php if (in_array(11, $HiddenProducts)){ 
           $current_page = basename($_SERVER['PHP_SELF']); ?>
        <li class="nav-item">
        <a href="game-list.php" class="nav-link <?php echo ($current_page == 'game-list.php') ? 'active' : ''; ?>" style="<?php echo ($current_page == 'game-list.php') ? 'background-color: #ffffff; color: #000000;' : ''; ?>">
         <i class="fas fa-plus-square"></i><p>Add New Game</p></a>
      </li>
     <?php } ?> 
     
     <?php if (in_array(12, $HiddenProducts)){ 
           $current_page = basename($_SERVER['PHP_SELF']); ?>
        <li class="nav-item">
        <a href="inactive-users.php" class="nav-link <?php echo ($current_page == 'inactive-users.php') ? 'active' : ''; ?>" style="<?php echo ($current_page == 'inactive-users.php') ? 'background-color: #ffffff; color: #000000;' : ''; ?>">
         <i class="fas fa-user-slash"></i><p>Inactive Users</p></a>
      </li>
     <?php } ?> 
     
     <?php if (in_array(13, $HiddenProducts)){ 
           $current_page = basename($_SERVER['PHP_SELF']); ?>
        <li class="nav-item">
        <a href="game-cancel.php" class="nav-link <?php echo ($current_page == 'game-cancel.php') ? 'active' : ''; ?>" style="<?php echo ($current_page == 'game-cancel.php') ? 'background-color: #ffffff; color: #000000;' : ''; ?>">
         <i class="fas fa-times"></i><p>Game Cancel</p></a>
      </li>
        <li class="nav-divider"></li> <!-- ADD THIS LINE -->
     <?php } ?>
     


    <!--<?php if (in_array(14, $HiddenProducts)): ?>-->
    <!--  <li class="nav-item">-->
    <!--    <a href="#" class="nav-link" onclick="openSettingsPopup(event)">-->
    <!--        <i class="nav-icon fas fa-tree"></i>-->
    <!--        <p>Settings <i class="right fas fa-cog"></i></p>-->
    <!--    </a>-->
    <!--  </li>-->
    <!--<?php endif; ?>-->
    
   
   
   <!-- ADDED: Notice Management Section -->
   <!-- <?php if (in_array(16, $HiddenProducts)): ?>-->
   <!--   <li class="nav-item has-treeview <?php echo ($current_page == 'notice.php' || $current_page == 'send-notification.php') ? 'menu-open' : ''; ?>">-->
   <!--      <a href="#" class="nav-link">-->
   <!--         <i class="nav-icon fas fa-table"></i>-->
   <!--         <p>Notice Management <i class="right fas fa-angle-left"></i></p>-->
   <!--     </a>-->
   <!--     <ul class="nav nav-treeview">-->
            <!--<li class="nav-item">-->
            <!--    <a href="notice.php" class="nav-link <?php echo ($current_page == 'notice.php') ? 'active' : ''; ?>">-->
            <!--        <i class="far fa-circle nav-icon <?php echo ($current_page == 'notice.php') ? 'text-primary' : ''; ?>"></i>-->
            <!--        <p>Notice Management</p>-->
            <!--    </a>-->
            <!--</li>-->
   <!--         <li class="nav-item">-->
   <!--             <a href="send-notification.php" class="nav-link <?php echo ($current_page == 'send-notification.php') ? 'active' : ''; ?>">-->
   <!--                 <i class="far fa-circle nav-icon <?php echo ($current_page == 'send-notification.php') ? 'text-primary' : ''; ?>"></i>-->
   <!--                 <p>Send Notification</p>-->
   <!--             </a>-->
   <!--         </li>-->
   <!--     </ul>-->
   <!-- </li>-->
   <!--<?php endif; ?>-->

  <?php $currentFile = basename($_SERVER['PHP_SELF']); ?>

 <!--<?php if (in_array(20, $HiddenProducts)) { ?>-->
 <!--  <li class="nav-item has-treeview <?php echo ($currentFile == 'add_rating.php' || $currentFile == 'reting_view.php') ? 'menu-open' : ''; ?>">-->
 <!--     <a href="#" class="nav-link">-->
 <!--        <i class="nav-icon fas fa-table"></i><p>Add Reviews <i class="fas fa-angle-left right"></i></p>-->
 <!--     </a>-->
 <!--     <ul class="nav nav-treeview">-->
 <!--        <li class="nav-item">-->
 <!--           <a href="add_rating.php" class="nav-link <?php echo ($currentFile === 'add_rating.php') ? 'active' : ''; ?>">-->
 <!--              <i class="far fa-circle nav-icon <?php echo ($currentFile === 'add_rating.php') ? 'text-primary' : ''; ?>"></i><p>Add Rating</p></a>-->
 <!--        </li>-->
 <!--        <li class="nav-item">-->
 <!--           <a href="reting_view.php" class="nav-link <?php echo ($currentFile === 'reting_view.php') ? 'active' : ''; ?>">-->
 <!--              <i class="far fa-circle nav-icon <?php echo ($currentFile === 'reting_view.php') ? 'text-primary' : ''; ?>"></i><p>View</p></a>-->
 <!--        </li>-->
 <!--     </ul>-->
 <!--  </li>-->
 <!--<?php } ?>-->
 
 <!-- ADDED: Rules Section -->
 <?php if (in_array(20, $HiddenProducts)) { ?>
   <!--<li class="nav-item">-->
   <!--  <a href="money_rules.php" class="nav-link <?php echo ($currentFile === 'money_rules.php') ? 'active' : ''; ?>">-->
   <!--     <i class="far fa-circle nav-icon <?php echo ($currentFile === 'money_rules.php') ? 'text-primary' : ''; ?>"></i>-->
   <!--     <p>Rules</p>-->
   <!--  </a>-->
   <!--</li>-->
 <?php } ?>

        <!--<?php if (in_array(18, $HiddenProducts)){ ?>-->
        <!--  <li class="nav-item">-->
        <!--    <a href="#" class="nav-link">-->
        <!--      <i class="nav-icon far fa-envelope"></i>-->
        <!--      <p>Starline Market <i class="fas fa-angle-left right"></i></p>-->
        <!--    </a>-->
        <!--    <ul class="nav nav-treeview">-->
        <!--      <li class="nav-item"><a href="starline-market-list.php" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Market List</p></a></li> -->
        <!--        <li class="nav-item"><a href="starline-game-list.php" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Game List</p></a></li> -->
        <!--      <li class="nav-item"><a href="starline-game-rates.php" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Game Rates</p></a></li>-->
        <!--      <li class="nav-item"><a href="starline-bid-history.php" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Bid History</p></a></li>-->
              <!--<li class="nav-item"><a href="starline_results.php" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Declare Result</p></a></li>-->
        <!--      <li class="nav-item"><a href="starline-declare-result.php" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Declare Result</p></a></li>-->
        <!--      <li class="nav-item"><a href="sell-report-starline.php" class="nav-link"><i class="nav-icon fas fa-th"></i><p>Sell Report <span class="right badge badge-danger">New</span></p></a></li>-->
        <!--      <li class="nav-item"><a href="starline-winning-report.php" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Winning Report</p></a></li>-->
        <!--    </ul>-->
        <!--  </li>-->
        <!--<?php } ?>-->
        
        <!--<?php if (in_array(21, $HiddenProducts)){ ?>-->
        <!--    <li class="nav-item">-->
        <!--      <a href="#" class="nav-link">-->
        <!--        <i class="nav-icon fas fa-dice"></i>-->
        <!--        <p>Jackpot Market <i class="fas fa-angle-left right"></i></p>-->
        <!--      </a>-->
            
        <!--      <ul class="nav nav-treeview">-->
        <!--        <li class="nav-item"> <a href="jackpot-market-list.php" class="nav-link"> <i class="far fa-circle nav-icon"></i>-->
        <!--            <p>Market List</p>-->
        <!--          </a>-->
        <!--        </li>-->
            
        <!--        <li class="nav-item">-->
        <!--          <a href="jackpot-game-rates.php" class="nav-link">-->
        <!--            <i class="far fa-circle nav-icon"></i>-->
        <!--            <p>Game Rates</p>-->
        <!--          </a>-->
        <!--        </li>-->
            
        <!--        <li class="nav-item">-->
        <!--          <a href="jackpot-bid-history.php" class="nav-link">-->
        <!--            <i class="far fa-circle nav-icon"></i>-->
        <!--            <p>Bid History</p>-->
        <!--          </a>-->
        <!--        </li>-->
            
        <!--        <li class="nav-item">-->
        <!--          <a href="jackpot-declare-result.php" class="nav-link">-->
        <!--            <i class="far fa-circle nav-icon"></i>-->
        <!--            <p>Declare Result</p>-->
        <!--          </a>-->
        <!--        </li>-->
            
        <!--        <li class="nav-item">-->
        <!--          <a href="jackpot-sell-report.php" class="nav-link">-->
        <!--            <i class="nav-icon fas fa-chart-bar"></i>-->
        <!--            <p>Sell Report</p>-->
        <!--          </a>-->
        <!--        </li>-->
            
        <!--        <li class="nav-item">-->
        <!--          <a href="jackpot-winning-report.php" class="nav-link">-->
        <!--            <i class="far fa-circle nav-icon"></i>-->
        <!--            <p>Winning Report</p>-->
        <!--          </a>-->
        <!--        </li>-->
            
        <!--      </ul>-->
        <!--    </li>-->
        <!--    <?php } ?>-->
        <!-- STARLINE SECTION -->
        <?php if (in_array(18, $HiddenProducts)){ ?>
          <li class="nav-item">
            <a href="profit-loss-straline.php" class="nav-link <?php echo ($page == 'sell-report-starline.php') ? 'active' : ''; ?>">
              <i class="fas fa-chart-line"></i><p>Profit Loss Starline</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="starline-winning-report.php" class="nav-link <?php echo ($page == 'starline-winning-report.php') ? 'active' : ''; ?>">
              <i class="fas fa-trophy"></i><p>Win History Starline</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="starline-report-generation.php" class="nav-link <?php echo ($page == 'starline-bid-history.php') ? 'active' : ''; ?>">
              <i class="fas fa-file-invoice"></i><p>Report Generation Starline</p>
            </a>
          </li>
          <li class="nav-divider"></li>
        <?php } ?>

        <!-- JACKPOT SECTION -->
        <?php if (in_array(21, $HiddenProducts)){ ?>
          <li class="nav-item">
            <a href="profit-loss-jackpot.php" class="nav-link <?php echo ($page == 'jackpot-sell-report.php') ? 'active' : ''; ?>">
              <i class="fas fa-chart-pie"></i><p>Profit Loss Jackpot</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="jackpot-winning-report.php" class="nav-link <?php echo ($page == 'jackpot-winning-report.php') ? 'active' : ''; ?>">
              <i class="fas fa-crown"></i><p>Win History Jackpot</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="jackpot-report-generation.php" class="nav-link <?php echo ($page == 'jackpot-bid-history.php') ? 'active' : ''; ?>">
              <i class="fas fa-receipt"></i><p>Report Generation Jackpot</p>
            </a>
          </li>
          <li class="nav-divider"></li>
        <?php } ?>
      <?php if (in_array(14, $HiddenProducts)): ?>
      <li class="nav-item">
        <a href="#" class="nav-link" onclick="openSettingsPopup(event)">
            <i class="nav-icon fas fa-tree"></i>
            <p>Settings <i class="right fas fa-cog"></i></p>
        </a>
      </li>
      
            
    <?php endif; ?>
            
        <?php if (in_array(13, $HiddenProducts)){ 
           $current_page = basename($_SERVER['PHP_SELF']); ?>
        <li class="nav-item">
        <a href="activity_log.php" class="nav-link <?php echo ($current_page == 'activity_log.php') ? 'active' : ''; ?>" style="<?php echo ($current_page == 'activity_log.php') ? 'background-color: #ffffff; color: #000000;' : ''; ?>">
         <i class="fas fa-cog"></i><p>Activity Logs</p></a>
      </li>  
      <li class="nav-divider"></li> <!-- ADD THIS LINE -->

     <?php } ?>
        
            
        <?php if (in_array(13, $HiddenProducts)){ 
           $current_page = basename($_SERVER['PHP_SELF']); ?>
        <li class="nav-item">
        <a href="logout.php" class="nav-link <?php echo ($current_page == 'logout.php') ? 'active' : ''; ?>" style="<?php echo ($current_page == 'logout.php') ? 'background-color: #ffffff; color: #000000;' : ''; ?>">
         <i class="fas fa-sign-out-alt"></i><p>Log Out</p></a>
      </li>
     <?php } ?>    
        
        <!-- <?php if (in_array(19, $HiddenProducts)){ ?>-->
        <!--   <li class="nav-item">-->
        <!--    <a href="#" class="nav-link">-->
        <!--      <i class="nav-icon far fa-envelope"></i>-->
        <!--      <p>Delhi Jodi <i class="fas fa-angle-left right"></i></p>-->
        <!--    </a>-->
        <!--    <ul class="nav nav-treeview">-->
        <!--      <li class="nav-item"><a href="delhi-market-list.php" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Game List</p></a></li>-->
        <!--      <li class="nav-item"><a href="starline-game-rates.php" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Game Rates</p></a></li>-->
        <!--      <li class="nav-item"><a href="bid-history-delhi.php" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Bid History</p></a></li>-->
        <!--      <li class="nav-item"><a href="declare-result-delhi.php" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Declare Result</p></a></li>-->
        <!--      <li class="nav-item"><a href="sell-report-delhi.php" class="nav-link"><i class="nav-icon fas fa-th"></i><p>Sell Report <span class="right badge badge-danger">New</span></p></a></li>-->
        <!--    </ul>-->
        <!--  </li>-->
        <!--<?php } ?>-->
           
          <!--<li class="nav-item menu-open bg-danger">-->
          <!--  <a href="logout.php" class="nav-link "><i class="nav-icon fas fa-tachometer-alt"></i><p>Log-Out</p></a>-->
          <!--</li> -->
            </ul>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
      
<script>
    function fetchCounts() {
        $.ajax({
            url: 'get_counts.php', 
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#pending-withdrawal-count').text(data.withdrawals);
                $('#pending-deposit-count').text(data.deposits);
            },
            error: function(xhr, status, error) { console.error('AJAX error:', error); }
        });
    }
    $(document).ready(function() { fetchCounts(); setInterval(fetchCounts, 30000); });
</script>
<?php if ($page == 'dashboard1.php') { ?>
<script src="plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="plugins/jqvmap/maps/jquery.vmap.world.js"></script>
<script src="plugins/sparklines/sparkline.js"></script>
<script src="dist/js/pages/dashboard.js"></script>
<?php } ?>


<div id="settingsOverlay" class="settings-overlay" onclick="closeSettingsPopup()"></div>

<div id="settingsPopup" class="bottom-popup">
    <div class="popup-handle-bar"></div>
    
    <div class="popup-content">
        <a href="profile.php" class="popup-item"> Profile</a>
       
        <a href="game_timetable.php" class="popup-item">Game On Off</a>
        <a href="starline_timetable.php" class="popup-item" >Starline Game ON/OFF</a>
        <a href="jackpot_timetable.php" class="popup-item" >Jackpot Game ON/OFF</a>
        <a href="money_rules.php" class="popup-item">Rules Set</a>
        <a href="block-upi.php" class="popup-item">Block Upi</a>
        <a href="upi-ar.php" class="popup-item">Upi AR</a>
        <a href="notice.php" class="popup-item">Notice Board</a>
        <a href="personal_notice.php" class="popup-item">Personal Notice Board</a>
        <a href="popup_notice.php" class="popup-item">POPUP Notice</a>
        <a href="youtube_videos.php" class="popup-item">Video</a>
        <a href="block-device.php" class="popup-item">Block Device List</a>
        <a href="game-rates.php" class="popup-item">Game Rate</a>
        <a href="starline-game-rate-new.php" class="popup-item">Starline Game Rate</a>
        <a href="jackpot-game-rate-new.php" class="popup-item">Jackpot Game Rate</a>
        <a href="all-games.php" class="popup-item">All Result</a>
        <!--<a href="main-settings.php" class="popup-item">Main Settings</a>-->
        <!--<a href="qr_code.php" class="popup-item">QR Code</a>-->
        <!--<a href="contact-us.php" class="popup-item">Contact Us Details</a>-->
        <!--<a href="how-to-play.php" class="popup-item">How To Play</a>-->
        <!-- <a href="change-password.php" class="popup-item">Change Password</a>-->
        <!--<a href="javascript:void(0)" onclick="closeSettingsPopup()" class="popup-item text-danger">Close</a>-->
    </div>
</div>

<style>
    /* Overlay */
/*   .settings-overlay {*/
/*    display: none;*/
/*    pointer-events: none;*/
/*}*/

.settings-overlay {
    position: fixed;
    top:0;
    left:0;
    right:0;
    bottom:0;
    background: rgba(0,0,0,0.3);
    display:none;
    z-index:9998;
}
.main-header {
    z-index: 2000 !important;
}


    /* Bottom Popup Main Container - Mobile First Design */
    .bottom-popup {
        position: fixed;
        bottom: -100%;
        left: 0;
        right: 0;
        width: 100%;
        background-color: #ffffff;
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
        box-shadow: 0 -5px 25px rgba(0,0,0,0.2);
        z-index: 9999;
        transition: bottom 0.4s ease-out;
        
        /* Fixed height for mobile - fits within viewport */
        max-height: 85vh;
        
        /* Flexbox layout */
        display: flex;
        flex-direction: column;
    }

    /* Active state */
    .bottom-popup.active {
        bottom: 0;
    }

    /* Handle Bar - drag indicator */
    .popup-handle-bar {
        width: 40px;
        height: 5px;
        background-color: #e0e0e0;
        border-radius: 10px;
        margin: 12px auto;
        flex-shrink: 0;
        cursor: pointer;
    }

    /* Content Area - Scrollable part */
    .popup-content {
    flex: 1;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    padding: 0 15px 30px 15px;
    max-height: 75vh;
}


    /* Item Styling */
    .popup-item {
        display: block;
        padding: 16px 0;
        text-align: center;
        color: #4a90e2;
        font-weight: 500;
        font-size: 16px;
        text-decoration: none;
        border-bottom: 1px solid #f0f0f0;
        background: white;
        transition: all 0.2s;
    }
    
    .popup-item:active {
        background-color: #f9f9f9;
    }
    
    .popup-item:hover {
        background-color: #f9f9f9;
        color: #2c3e50;
        text-decoration: none;
    }
    
    .popup-item:last-child {
        border-bottom: none;
        margin-top: 5px;
        padding-top: 20px;
    }
    
    .text-danger {
        color: #dc3545 !important;
        font-weight: bold;
    }

    /* Tablet and Desktop */
    @media (min-width: 768px) {
        .bottom-popup {
            width: 400px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 20px;
            bottom: -500px;
            max-height: 70vh;
        }
        
        .bottom-popup.active {
            bottom: 50px;
        }
    }

    /* Extra small devices */
    @media (max-width: 350px) {
        .popup-item {
            padding: 14px 0;
            font-size: 15px;
        }
    }
</style>

<script>

    function openSettingsPopup(e) {
        if(e) e.preventDefault();
        
        // Add class to body to prevent background scrolling
        document.body.classList.add('popup-open');
        
        // Show overlay
        document.getElementById('settingsOverlay').style.display = 'block';
        
        // Show popup with slight delay for smooth animation
        setTimeout(function() {
            document.getElementById('settingsPopup').classList.add('active');
        }, 10);
    }

    function closeSettingsPopup() {
        // Remove active class
        document.getElementById('settingsPopup').classList.remove('active');
        
        // Hide overlay with delay
        setTimeout(function() {
            document.getElementById('settingsOverlay').style.display = 'none';
            document.body.classList.remove('popup-open');
        }, 400);
    }

    // Close popup when clicking on overlay
    document.getElementById('settingsOverlay').addEventListener('click', closeSettingsPopup);

    // Prevent popup content clicks from closing the popup
    document.getElementById('settingsPopup').addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Close popup with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSettingsPopup();
        }
    });

    // Handle swipe down to close on mobile
    let startY = 0;
    let currentY = 0;
    let isSwiping = false;
    
    const popup = document.getElementById('settingsPopup');
    const handleBar = document.querySelector('.popup-handle-bar');
    
    if (handleBar) {
        handleBar.addEventListener('touchstart', function(e) {
            startY = e.touches[0].clientY;
            isSwiping = true;
        }, {passive: true});
        
        document.addEventListener('touchmove', function(e) {
            if (!isSwiping) return;
            
            currentY = e.touches[0].clientY;
            const diff = currentY - startY;
            
            // Only allow swipe down to close
            if (diff > 20) {
                e.preventDefault();
                closeSettingsPopup();
                isSwiping = false;
            }
        }, {passive: false});
        
        document.addEventListener('touchend', function() {
            isSwiping = false;
        });
    }
    
    $(document).ready(function () {
    $('[data-widget="pushmenu"]').on('click', function(e){
        e.preventDefault();
        $('body').toggleClass('sidebar-collapse');
    });
    });
    
    $('.nav-sidebar a').on('click', function(){
    if ($(window).width() < 768) {
        $('body').addClass('sidebar-collapse');
    }
  });
</script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>
<script>
    // Handles the mobile menu toggle
    function openMobileHeaderMenu() {
        document.getElementById('mobileHeaderOverlay').style.display = 'block';
        document.body.classList.add('popup-open');
        setTimeout(() => { document.getElementById('mobileHeaderPopup').classList.add('active'); }, 10);
    }

    function closeMobileHeaderMenu() {
        document.getElementById('mobileHeaderPopup').classList.remove('active');
        setTimeout(() => {
            document.getElementById('mobileHeaderOverlay').style.display = 'none';
            document.body.classList.remove('popup-open');
        }, 400);
    }

    // Live clock logic
    function updateClock() {
        const now = new Date();
        const dateStr = now.toLocaleDateString('en-GB').replace(/\//g, '-'); // 13-03-2026
        const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true }).toLowerCase();
        
        document.querySelectorAll('.dashboard-date').forEach(el => el.innerText = dateStr);
        document.querySelectorAll('.dashboard-clock').forEach(el => el.innerText = timeStr);
    }

    // Updated fetch counts (targets CLASS so both views update)
    function fetchCounts() {
        $.ajax({
            url: 'get_counts.php', 
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                $('.pending-withdrawal-count').text(data.withdrawals);
                $('.pending-deposit-count').text(data.deposits);
            }
        });
    }

    $(document).ready(function() {
        updateClock();
        setInterval(updateClock, 1000); // Update clock every second
        fetchCounts();
        setInterval(fetchCounts, 30000); // Update counts every 30 seconds
    });
</script>



