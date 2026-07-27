<?php
// include('db_connection.php'); // your db connection
include('config.php');

if(isset($_GET['filter_date'])){
    $date = $_GET['filter_date'];
    $count = mysqli_fetch_row(mysqli_query($con, "
        SELECT COUNT(*) FROM users 
        WHERE DATE(FROM_UNIXTIME(created_at))='$date'
    "))[0];
    echo $count;
}

if(isset($_GET['month']) && isset($_GET['year'])){
    $month = $_GET['month'];
    $year = $_GET['year'];
    $months = [
        '01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun',
        '07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'
    ];
    $count = mysqli_fetch_row(mysqli_query($con, "
        SELECT COUNT(*) FROM users 
        WHERE MONTH(FROM_UNIXTIME(created_at))='$month'
        AND YEAR(FROM_UNIXTIME(created_at))='$year'
    "))[0];
    echo $count;
}
?>