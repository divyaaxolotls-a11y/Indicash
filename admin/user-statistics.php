<?php include('header.php');
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : date('Y-m-d');
$date_dmY = date('d/m/Y', strtotime($filter_date));
?>

<style>
/* GENERAL BODY */
body {
    background: #e6e6e6;
    font-family: "Source Sans Pro", sans-serif;
}

/* DASHBOARD CARDS */
.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    border-radius: 15px;
    padding: 18px;
    text-align: center;
    color: white;
    font-size: 20px;
    font-weight: 600;
}

.stat-card span {
    display: block;
    font-size: 26px;
    margin-top: 5px;
}

/* CARD COLORS */
.green { background: #008000; }
.orange { background: #f08a00; }
.blue { background: #3b94bf; }
.purple { background: #b03a7c; }

/* FILTER BOX */
/* FILTER BOX */
.filter-box {
    display: flex;
    align-items: center;
    justify-content: space-between; /* pushes count to far right */
    gap: 15px;
    padding: 15px 20px;
    border-radius: 40px;
    background: #dcdcdc;
    flex-wrap: nowrap;
    overflow-x: auto;
    margin-bottom: 20px; /* added margin-bottom to separate from next box */
}

.form-controls-wrapper {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: nowrap;
}

.filter-box .form-control {
    min-width: 140px;
    padding: 8px 12px;
    border-radius: 25px;
    font-size: 16px;
    height: 42px; /* consistent height for alignment */
    border: none;
    background: #eee;
}

.filter-value {
    font-size: 20px;
    font-weight: bold;
    color: #007bff;
    cursor: pointer;
    flex-shrink: 0;
    white-space: nowrap;
}

/* STATUS BOX */
.status-box {
    border-radius: 40px;
    padding: 18px;
    display: flex;
    justify-content: space-between; /* pushes count to far right */
    align-items: center;
    margin-bottom: 15px;
    font-size: 22px;
}

.active-user { background: #bde0b8; color: #1b4f72; }
.inactive-user { background: #e9bcbc; color: #1b4f72; }
.block-device { background: #e9bcbc; color: #1b4f72; }

/* MOBILE ADJUSTMENTS */
@media (max-width: 768px) {
    .stat-card { font-size: 18px; }
    .stat-card span { font-size: 20px; }
    .filter-box .form-control { min-width: 120px; font-size: 16px; height: 38px; }
    .filter-value { font-size: 18px; }
    .status-box { font-size: 20px; }
    .status-box div:last-child { font-size: 20px; }
}
</style>


<section class="content">
<div class="container-fluid">

<?php

/* TOTAL USERS */
$totalUser = mysqli_fetch_row(mysqli_query($con,"SELECT COUNT(*) FROM users"))[0];

/* TODAY REGISTER */

$today = date('Y-m-d');
$todayRegister = mysqli_fetch_row(mysqli_query($con,"
    SELECT COUNT(*) FROM users 
    WHERE DATE(FROM_UNIXTIME(created_at))='$today'
"))[0];

$yesterday = date('Y-m-d', strtotime('-1 day'));
$yesterdayRegister = mysqli_fetch_row(mysqli_query($con,"
    SELECT COUNT(*) FROM users 
    WHERE DATE(FROM_UNIXTIME(created_at))='$yesterday'
"))[0];

$weekStart = date('Y-m-d', strtotime("monday this week"));
$currentWeek = mysqli_fetch_row(mysqli_query($con,"
    SELECT COUNT(*) FROM users 
    WHERE DATE(FROM_UNIXTIME(created_at))>='$weekStart'
"))[0];

/* ACTIVE USERS */
// $activeUser = mysqli_fetch_row(mysqli_query($con,"SELECT COUNT(*) FROM users WHERE active='1'"))[0];
$activeUser = mysqli_fetch_row(mysqli_query($con,"
    SELECT COUNT(DISTINCT u.mobile)
    FROM users u
    INNER JOIN games g ON u.mobile = g.user
    WHERE g.date = '$date_dmY'
"))[0];

/* INACTIVE USERS */
// $inactiveUser = mysqli_fetch_row(mysqli_query($con,"SELECT COUNT(*) FROM users WHERE active='0'"))[0];
$inactiveUser = mysqli_fetch_row(mysqli_query($con,"
    SELECT COUNT(*) 
    FROM users u
    LEFT JOIN games g 
        ON u.mobile = g.user 
        AND g.date = '$date_dmY'
    WHERE g.user IS NULL
"))[0];

/* BLOCK DEVICE */
$blockDevice = mysqli_fetch_row(mysqli_query($con,"SELECT COUNT(*) FROM users WHERE verify='0'"))[0];

?>

<!-- TOP CARDS -->

<div class="dashboard-grid">

<a href="users_old.php" style="text-decoration:none;">
<div class="stat-card green" style="cursor:pointer;">
Total user
<span><?php echo $totalUser; ?></span>
</div>
</a>

<div class="stat-card orange">
Today Register
<span><?php echo $todayRegister; ?></span>
</div>

<div class="stat-card blue">
Yesterday Register
<span><?php echo $yesterdayRegister; ?></span>
</div>

<div class="stat-card purple">
Current Week
<span><?php echo $currentWeek; ?></span>
</div>

</div>

<!-- DATE + MONTH + YEAR FILTER ON ONE LINE -->
<!-- DATE FILTER (vertical) -->
<!-- DATE FILTER -->
<!-- DATE FILTER -->
<div class="filter-box">
    <div class="form-controls-wrapper">
        <input type="date" id="filter_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
    </div>
    <div class="filter-value" id="date_count">
        <?php
        $today = date('Y-m-d');
        $dateCount = mysqli_fetch_row(mysqli_query($con,"
            SELECT COUNT(*) FROM users 
            WHERE DATE(FROM_UNIXTIME(created_at))='$today'
        "))[0];
        echo $dateCount;
        ?>
    </div>
</div>

<!-- MONTH + YEAR FILTER -->
<div class="filter-box">
    <div class="form-controls-wrapper">
        <select id="month" class="form-control">
            <?php
            $months = [
                '01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun',
                '07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'
            ];
            $currentMonth = date('m');
            foreach($months as $num => $name){
                $selected = ($num == $currentMonth) ? 'selected' : '';
                echo "<option value='$num' $selected>$name</option>";
            }
            ?>
        </select>

        <select id="year" class="form-control">
            <?php
            $startYear = 2020;
            $currentYear = date('Y');
            for($y = $startYear; $y <= $currentYear; $y++){
                $selected = ($y == $currentYear) ? 'selected' : '';
                echo "<option value='$y' $selected>$y</option>";
            }
            ?>
        </select>
    </div>
    <div class="filter-value" id="month_year_count">
        <?php
        $monthCount = mysqli_fetch_row(mysqli_query($con,"
            SELECT COUNT(*) FROM users 
            WHERE MONTH(FROM_UNIXTIME(created_at))='$currentMonth'
            AND YEAR(FROM_UNIXTIME(created_at))='$currentYear'
        "))[0];
        echo $monthCount;
        ?>
    </div>
</div>

<script>
// When user clicks on the date count
document.getElementById('date_count').addEventListener('click', function() {
    var date = document.getElementById('filter_date').value;
    if(date) {
        window.location.href = "user_filter.php?filter_date=" + date;
    }
});

// When user clicks on the month-year count
document.getElementById('month_year_count').addEventListener('click', function() {
    var month = document.getElementById('month').value;
    var year = document.getElementById('year').value;
    if(month && year) {
        window.location.href = "user_filter.php?month=" + month + "&year=" + year;
    }
});
</script>

<!-- ACTIVE USER -->
<a href="user-profit-loss.php?active=1" style="text-decoration:none;">
    <div class="status-box active-user" style="cursor:pointer;">
        <div>Play Active User</div>
        <div><?php echo $activeUser; ?></div>
    </div>
</a>

<!-- INACTIVE USER -->
<a href="user-profit-loss.php?active=0" style="text-decoration:none;">
    <div class="status-box inactive-user">
        <div>Play Inactive User</div>
        <div><?php echo $inactiveUser; ?></div>
    </div>
</a>


<!-- BLOCK DEVICE -->
<div class="status-box block-device">
    <div>Block Device</div>
    <div><?php echo $blockDevice; ?></div>
</div>
<script>
function updateDateCount(date) {
    fetch('ajax_count.php?filter_date=' + date)
        .then(res => res.text())
        .then(data => {
            document.getElementById('date_count').innerHTML = data;
        });
}

function updateMonthYearCount(month, year) {
    fetch('ajax_count.php?month=' + month + '&year=' + year)
        .then(res => res.text())
        .then(data => {
            document.getElementById('month_year_count').innerHTML = data;
        });
}

document.getElementById('filter_date').addEventListener('change', function(){
    updateDateCount(this.value);
});

document.getElementById('month').addEventListener('change', function(){
    updateMonthYearCount(this.value, document.getElementById('year').value);
});

document.getElementById('year').addEventListener('change', function(){
    updateMonthYearCount(document.getElementById('month').value, this.value);
});
</script>
<!-- Optional CSS for inline layout -->

</section>


<?php include('footer.php'); ?>