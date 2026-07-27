<?php 
include('header.php');

// 1. Permission Check
if (!in_array(15, $HiddenProducts)){
    echo "<script>window.location.href = 'unauthorized.php';</script>";
    exit();
}

$week = ["SUNDAY", "MONDAY", "TUESDAY", "WEDNESDAY", "THURSDAY", "FRIDAY", "SATURDAY"];

// --- DELETE LOGIC ---
if (isset($_POST['DeleteGame'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<script>alert('Security Token Mismatch.');</script>";
    } else {
        $market_sn = mysqli_real_escape_string($con, $_POST['market_sn']);
        $table_name = mysqli_real_escape_string($con, $_POST['table_name']);

        if ($table_name === 'gametime_new' || $table_name === 'gametime_manual') {
            $query = "DELETE FROM `$table_name` WHERE `sn`='$market_sn'";
            if (mysqli_query($con, $query)) {
                echo "<script>alert('Game Deleted Successfully'); window.location.href = 'game-list.php';</script>";
            }
        }
    }
}

// --- CREATE LOGIC ---
if (isset($_POST['CreateNew'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<script>alert('Security Token Mismatch.');</script>";
    } else {
        $gameName = mysqli_real_escape_string($con, trim($_POST['gameName']));
        $openTime = date("H:i", strtotime($_POST['OpenTime']));
        $closeTime = date("H:i", strtotime($_POST['CloseTime']));
        $type = $_POST['type'];
        $selected_days = isset($_POST['select_days']) ? $_POST['select_days'] : [];
        
        $data = [];
        foreach ($week as $day) {
            if ($type == "Yes" && in_array($day, $selected_days)) {
                $data[] = $day . "(" . $openTime . "-" . $closeTime . ")";
            } else {
                $data[] = $day . "(CLOSED)";
            }
        }
        $dd = implode(",", $data);

        $query = "INSERT INTO `gametime_manual`(`market`, `open`, `close`, `days`, `sort_no`) VALUES ('$gameName', '$openTime', '$closeTime', '$dd', '0')";
        if (mysqli_query($con, $query)) {
            echo "<script>window.location.href = 'game-list.php';</script>";
        }
    }
}

// --- UPDATE LOGIC ---
if (isset($_POST['UpdateGame'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<script>alert('Security Token Mismatch.');</script>";
    } else {
        $sn = mysqli_real_escape_string($con, $_POST['market_sn']);
        $table = mysqli_real_escape_string($con, $_POST['table_name']);
        $gameName = mysqli_real_escape_string($con, trim($_POST['gameName']));
        $openTime = date("H:i", strtotime($_POST['OpenTime']));
        $closeTime = date("H:i", strtotime($_POST['CloseTime']));
        $type = $_POST['type'];
        $selected_days = isset($_POST['select_days']) ? $_POST['select_days'] : [];
        $active_status = ($type == "Yes") ? 1 : 0;

        $data = [];
        foreach ($week as $day) {
            if ($type == "Yes" && in_array($day, $selected_days)) {
                $data[] = $day . "(" . $openTime . "-" . $closeTime . ")";
            } else {
                $data[] = $day . "(CLOSED)";
            }
        }
        $dd = implode(",", $data);

        $query = "UPDATE `$table` SET `market`='$gameName', `open`='$openTime', `close`='$closeTime', `days`='$dd', `active`='$active_status' WHERE `sn`='$sn'";
        if (mysqli_query($con, $query)) {
            echo "<script>alert('Game Updated Successfully'); window.location.href = 'game-list.php';</script>";
        }
    }
}
?>

<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body { background-color: #fff; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
    
    /* Full Width setup - Minimal Side Space */
    .full-container { width: 100%; padding: 0 5px; margin: 0; }
    
    .game-card { background: transparent; padding: 10px 0; margin: 0; }
    
    .page-title { font-weight: 700; font-size: 22px; color: #333; margin-bottom: 15px; text-align: center; }

    .custom-pill { border-radius: 50px !important; border: 1px solid #ccc; font-size: 14px; }
    .btn-pill { border-radius: 50px; padding: 6px 25px; font-weight: 600; font-size: 14px; }

    /* --- TABLE STYLING --- */
    .table-wrapper { width: 100%; margin-top: 15px; }

    .custom-table { width: 100%; border-collapse: collapse; table-layout: fixed; border: 1px solid #bbb; }

    .custom-table thead th {
        background-color: #ff9800 !important;
        color: #fff !important;
        text-align: center;
        font-size: 11px;
        font-weight: 600;
        padding: 8px 2px;
        border: 1px solid #e68900;
        word-break: break-word;
    }

    .custom-table tbody td {
        text-align: center;
        font-size: 11px;
        padding: 7px 2px;
        border: 1px solid #bbb;
        color: #000;
        word-break: break-word;
        white-space: normal;
        line-height: 1.3;
    }

    .custom-table tbody tr:nth-child(even) td { background: #fafafa; }
    .custom-table tbody tr:hover td { background: #fff3e0; }

    /* Column widths - fixed so all 6 fit on screen */
    .col-name { width: 26%; }
    .col-time { width: 17%; }
    .col-play { width: 10%; }
    .col-days { width: 13%; }
    .col-edit { width: 10%; }

    .edit-btn {
        background-color: #03a9f4; border: none; color: white;
        padding: 4px 6px; border-radius: 4px; font-size: 11px;
        cursor: pointer;
    }
    .edit-btn:active { background-color: #0288d1; }
/* --- TABLE STYLING: MATCHING users_old.php EXACTLY --- */
.table-wrapper { 
    width: 100%; 
    margin-top: 25px; 
    background: white; 
    border-radius: 15px; /* Same rounded corners */
    overflow: hidden; 
    box-shadow: 0 2px 10px rgba(0,0,0,0.05); /* Same soft shadow */
}

.custom-table { 
    width: 100%; 
    border-collapse: collapse; 
    table-layout: fixed; 
    border: none; 
}

/* Header: Changed from Orange to the Blue used in your user list */
.custom-table thead th {
     background-color: #ff9800 !important;
        color: #fff !important;
    text-align: center;
    font-size: 15px; /* Matching font size */
    font-weight: 500;
    padding: 15px 5px; /* Deep vertical padding */
    border: none;
    word-break: break-word;
}

/* Body Rows: Matching the "Thick" row look and white background */
.custom-table tbody td {
    text-align: center;
    font-size: 14px; /* Increased font size */
    padding: 15px 8px; /* Deep vertical padding for height/depth */
    border-top: 1px solid #f0f0f0; /* Thin, light horizontal line only */
    border-left: none;
    border-right: none;
    color: #333;
    vertical-align: middle;
    background-color: #ffffff !important;
}

/* Row Hover: Subtle grey like the user list */
.custom-table tbody tr:hover td { 
    background-color: #f8f9fa !important; 
}

/* Column Width Adjustments for better alignment */
.col-name { width: 28%; }
.col-time { width: 18%; }
.col-play { width: 10%; }
.col-days { width: 16%; }
.col-edit { width: 10%; }

/* Edit Icon Button Styling */
.edit-btn {
    background-color: #03a9f4; 
    border: none; 
    color: white;
    padding: 6px 10px; 
    border-radius: 6px; 
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
    #hiddenFields { display: none; }
    .day-item { font-size: 11px; margin-right: 8px; display: inline-block; }

    @media (max-width: 400px) {
        .custom-table thead th { font-size: 9px; padding: 6px 1px; }
        .custom-table tbody td { font-size: 9px; padding: 6px 1px; }
        .full-container { padding: 0 2px; }
    }
</style>
<script>
document.addEventListener("DOMContentLoaded", function() {

    const btn = document.querySelector('[data-widget="pushmenu"]');

    if(btn){
        btn.addEventListener("click", function(e){
            e.preventDefault();
            document.body.classList.toggle("sidebar-collapse");
        });
    }

});
</script>
<div class="full-container">
    
    <div class="game-card">
        <h2 class="page-title">Add New Game</h2>
        
        <form method="POST" id="gameForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="market_sn" id="edit_market_sn">
            <input type="hidden" name="table_name" id="edit_table_name" value="gametime_manual">

            <!-- CHANGE 1: Added "Game Name" label above input -->
            <div class="mb-2">
                <label class="small fw-bold ms-2">Game Name</label>
                <input type="text" class="form-control custom-pill" name="gameName" id="gameName" placeholder="Enter Game Name" required onclick="showFields()">
            </div>

            <div id="hiddenFields">
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="small fw-bold ms-2">Open</label>
                        <input type="time" class="form-control custom-pill" name="OpenTime" id="open">
                    </div>
                    <div class="col-6">
                        <label class="small fw-bold ms-2">Close</label>
                        <input type="time" class="form-control custom-pill" name="CloseTime" id="close">
                    </div>
                </div>

                <div class="mb-2">
                    <select id="type" name="type" class="form-select custom-pill" onchange="toggleDays(this.value)">
                        <option value='Yes'>Game Play On</option>
                        <option value='No'>Game Play Off</option>
                    </select>
                </div>

                <div id="days_container" class="mb-2 px-2">
                    <div class="d-flex flex-wrap">
                        <?php foreach($week as $day) { ?>
                            <div class="day-item">
                                <input type="checkbox" name="select_days[]" value="<?php echo $day; ?>" id="d_<?php echo $day; ?>">
                                <label for="d_<?php echo $day; ?>"><?php echo substr($day,0,3); ?></label>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <!-- CHANGE 2: Removed text-center, added text-start for left alignment -->
            <div class="text-start mt-2">
                <button type="submit" name="CreateNew" id="btnAdd" class="btn btn-primary text-white btn-pill">Add Game</button>
                
                <div id="btnGroupEdit" style="display: none;" class="justify-content-start gap-2">
                    <button type="submit" name="UpdateGame" class="btn btn-primary btn-pill">Update</button>
                    <button type="submit" name="DeleteGame" class="btn btn-primary text-white btn-pill" onclick="return confirm('Delete game?')">Delete</button>
                    <button type="button" class="btn btn-primary text-white btn-pill" onclick="resetForm()">Add New</button>
                </div>
            </div>
        </form>
    </div>

    <div class="table-wrapper">
        <table class="custom-table">
            <thead>
                <tr>
                    <th class="col-name">Game Name</th>
                    <th class="col-time">Open</th>
                    <th class="col-time">Close</th>
                    <th class="col-play">Play</th>
                    <th class="col-days">Days</th>
                    <th class="col-edit">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                function renderRows($con, $table_name) {
                    $res = mysqli_query($con, "SELECT * FROM `$table_name` ORDER BY sn DESC");
                    while($row = mysqli_fetch_array($res)){
                        $dayMapping = ['MONDAY'=>1, 'TUESDAY'=>2, 'WEDNESDAY'=>3, 'THURSDAY'=>4, 'FRIDAY'=>5, 'SATURDAY'=>6, 'SUNDAY'=>0];
                        $activeNums = [];
                        $parts = explode(',', $row['days'] ?? '');
                        foreach($parts as $p){
                            if(strpos($p, 'CLOSED') === false && !empty($p)){
                                foreach($dayMapping as $dName => $dNum){
                                    if(strpos($p, $dName) !== false) { $activeNums[] = $dNum; break; }
                                }
                            }
                        }
                        sort($activeNums);
                        $displayDays = !empty($activeNums) ? implode('-', $activeNums) : '';

                        $rowData = htmlspecialchars(json_encode([
                            'sn' => $row['sn'], 'market' => $row['market'], 
                            'open' => date("H:i", strtotime($row['open'])), 
                            'close' => date("H:i", strtotime($row['close'])),
                            'days' => $row['days'], 'table' => $table_name
                        ]), ENT_QUOTES, 'UTF-8');
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['market']); ?></strong></td>
                    <td><?php echo date("h:i A", strtotime($row['open'])); ?></td>
                    <td><?php echo date("h:i A", strtotime($row['close'])); ?></td>
                    <td><?php echo ($row['active'] == 1) ? 'Yes' : 'No'; ?></td>
                    <td><?php echo $displayDays; ?></td>
                    <td>
                        <button type="button" class="edit-btn" data-json='<?php echo $rowData; ?>' onclick="editGame(this)">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
                <?php } }
                renderRows($con, 'gametime_new');
                renderRows($con, 'gametime_manual');
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function showFields() { $("#hiddenFields").slideDown(); }
    function toggleDays(value){ (value == "Yes") ? $("#days_container").slideDown() : $("#days_container").slideUp(); }

    function editGame(btn) {
    // Read JSON from the data attribute instead of passing as argument
    var data = $(btn).data('json');
    
    $("#hiddenFields").show();
    $('#gameName').val(data.market);
    $('#open').val(data.open);
    $('#close').val(data.close);
    $('#edit_market_sn').val(data.sn);
    $('#edit_table_name').val(data.table);

    // Set the Play status dropdown
    var playStatus = (data.days.indexOf('CLOSED') !== -1 && data.days.split(',').every(d => d.includes('CLOSED'))) ? 'No' : 'Yes';
    $('#type').val(playStatus).change();

    // Check the correct day checkboxes
    $('input[name="select_days[]"]').prop('checked', false);
    if (data.days) {
        data.days.split(',').forEach(function(day_str) {
            if (day_str.indexOf('(CLOSED)') === -1) {
                var dayName = day_str.split('(')[0].trim();
                $('input[name="select_days[]"][value="' + dayName + '"]').prop('checked', true);
            }
        });
    }
    
    $('#btnAdd').hide();
    $('#btnGroupEdit').attr('style', 'display: flex !important'); // Force flex show
    window.scrollTo({top: 0, behavior: 'smooth'});
}

    function resetForm() {
        $('#gameForm')[0].reset();
        $('#edit_market_sn').val('');
        $('#btnAdd').show();
        $('#btnGroupEdit').removeClass('d-flex').hide();
        $("#hiddenFields").hide();
    }
</script>