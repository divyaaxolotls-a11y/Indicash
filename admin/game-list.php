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
        $sn = mysqli_real_escape_string($con, $_POST['market_sn']);
        $table = mysqli_real_escape_string($con, $_POST['table_name']);
        
        $query = "DELETE FROM `$table` WHERE `sn`='$sn'";
        if (mysqli_query($con, $query)) {
            echo "<script>alert('Deleted Successfully'); window.location.href = 'game-list.php';</script>";
        }
    }
}

// --- CREATE / UPDATE LOGIC ---
if (isset($_POST['SaveGame'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<script>alert('Security Token Mismatch.');</script>";
    } else {
        $type = $_POST['game_type']; 
        $name = mysqli_real_escape_string($con, $_POST['gameName']);
        $openTime = !empty($_POST['OpenTime']) ? date("H:i", strtotime($_POST['OpenTime'])) : "00:00";
        $closeTime = date("H:i", strtotime($_POST['CloseTime']));
        $active_status = ($_POST['is_active'] == "Yes") ? 1 : 0;
        $sn = $_POST['market_sn'];
        $table_to_use = $_POST['table_name'];

        if ($type == 'normal') {
            $selected_days = isset($_POST['select_days']) ? $_POST['select_days'] : [];
            $data = [];
            foreach ($week as $day) {
                if ($active_status == 1 && in_array($day, $selected_days)) {
                    $data[] = $day . "(" . $openTime . "-" . $closeTime . ")";
                } else {
                    $data[] = $day . "(CLOSED)";
                }
            }
            $dd = implode(",", $data);
            $final_table = !empty($table_to_use) ? $table_to_use : 'gametime_manual';

            if (!empty($sn)) {
                $query = "UPDATE `$final_table` SET `market`='$name', `open`='$openTime', `close`='$closeTime', `days`='$dd', `active`='$active_status' WHERE `sn`='$sn'";
            } else {
                $query = "INSERT INTO `gametime_manual`(`market`, `open`, `close`, `days`, `active`) VALUES ('$name', '$openTime', '$closeTime', '$dd', '$active_status')";
            }
        } 
        elseif ($type == 'starline') {
            if (!empty($sn)) {
                $query = "UPDATE `starline_timings` SET `name`='$name', `close`='$closeTime' WHERE `sn`='$sn'";
            } else {
                $query = "INSERT INTO `starline_timings`(`name`, `market`, `open`, `close`, `auto`) VALUES ('$name', 'Starline', '', '$closeTime', '0')";
            }
        } 
        elseif ($type == 'jackpot') {
            if (!empty($sn)) {
                $query = "UPDATE `jackpot_markets` SET `name`='$name', `close`='$closeTime', `is_active`='$active_status' WHERE `sn`='$sn'";
            } else {
                $query = "INSERT INTO `jackpot_markets`(`name`, `close`, `is_active`) VALUES ('$name', '$closeTime', '$active_status')";
            }
        }

        if (mysqli_query($con, $query)) {
            echo "<script>alert('Success'); window.location.href = 'game-list.php';</script>";
        }
    }
}
?>

<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
    .full-container { width: 100%; padding: 15px; }
    .game-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); margin-bottom: 20px; }
    .page-title { font-weight: 700; font-size: 20px; color: #333; margin-bottom: 15px; }
    .custom-pill { border-radius: 50px !important; border: 1px solid #ced4da; }
    .btn-pill { border-radius: 50px; padding: 8px 25px; font-weight: 600; }
    
    .table-wrapper { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .custom-table { width: 100%; border-collapse: collapse; }
    .custom-table thead th { background-color: #ff9800; color: #fff; padding: 12px; font-size: 13px; text-align: center; border: none; }
    .custom-table tbody td { padding: 12px; text-align: center; border-top: 1px solid #f0f0f0; font-size: 13px; vertical-align: middle; }
    
    .badge-type { padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
    .type-normal { background: #e3f2fd; color: #1976d2; }
    .type-starline { background: #f3e5f5; color: #7b1fa2; }
    .type-jackpot { background: #e8f5e9; color: #2e7d32; }

    .edit-btn { background-color: #03a9f4; border: none; color: white; padding: 5px 10px; border-radius: 5px; cursor: pointer; }
    #hiddenFields { display: none; }
    .day-item { font-size: 11px; margin-right: 8px; display: inline-block; }
</style>

<div class="full-container">
    <div class="game-card">
        <h2 class="page-title" id="formTitle">Manage All Games</h2>
        
        <form method="POST" id="gameForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="market_sn" id="edit_market_sn">
            <input type="hidden" name="table_name" id="edit_table_name">

            <div class="row g-2">
                <div class="col-md-3">
                    <label class="small fw-bold">Game Type</label>
                    <select name="game_type" id="game_type" class="form-select custom-pill" onchange="handleTypeChange(this.value)" required>
                        <option value="normal">Normal Game</option>
                        <option value="starline">Starline Game</option>
                        <option value="jackpot">Jackpot Game</option>
                    </select>
                </div>
                <div class="col-md-9">
                    <label class="small fw-bold">Game Name</label>
                    <input type="text" class="form-control custom-pill" name="gameName" id="gameName" placeholder="Enter Game Name" required onclick="showFormDetails()">
                </div>
            </div>

            <div id="hiddenFields" class="mt-3">
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-4" id="openTimeGroup">
                        <label class="small fw-bold">Open Time</label>
                        <input type="time" class="form-control custom-pill" name="OpenTime" id="open">
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="small fw-bold">Close Time</label>
                        <input type="time" class="form-control custom-pill" name="CloseTime" id="close" required>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold">Active Status</label>
                        <select id="is_active" name="is_active" class="form-select custom-pill">
                            <option value='Yes'>Active (On)</option>
                            <option value='No'>Inactive (Off)</option>
                        </select>
                    </div>
                </div>

                <div id="days_container" class="mb-3 p-2 border rounded bg-light">
                    <label class="small fw-bold d-block mb-1">Select Days (Normal Games)</label>
                    <div class="d-flex flex-wrap">
                        <?php foreach($week as $day) { ?>
                            <div class="day-item">
                                <input type="checkbox" name="select_days[]" value="<?php echo $day; ?>" id="d_<?php echo $day; ?>" checked>
                                <label for="d_<?php echo $day; ?>"><?php echo substr($day,0,3); ?></label>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="mt-3 text-start">
                <button type="submit" name="SaveGame" id="btnSubmit" class="btn btn-primary btn-pill">Save Game</button>
                <button type="button" id="btnDelete" class="btn btn-danger btn-pill d-none" onclick="confirmDelete()">Delete</button>
                <button type="button" class="btn btn-outline-secondary btn-pill" onclick="resetUnifiedForm()">Add New</button>
            </div>
            <input type="submit" name="DeleteGame" id="deleteTrigger" class="d-none">
        </form>
    </div>

    <div class="table-wrapper">
        <table class="custom-table">
            <thead>
                <tr>
                    <th style="width: 15%">Type</th>
                    <th style="width: 25%">Game Name</th>
                    <th>Open</th>
                    <th>Close</th>
                    <th style="width: 15%">Days</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch All
                $res1 = mysqli_query($con, "SELECT sn, market as name, open, close, active, days, 'normal' as gtype, 'gametime_manual' as tname FROM gametime_manual UNION SELECT sn, market as name, open, close, active, days, 'normal' as gtype, 'gametime_new' as tname FROM gametime_new");
                $res2 = mysqli_query($con, "SELECT sn, name, '' as open, close, '1' as active, '' as days, 'starline' as gtype, 'starline_timings' as tname FROM starline_timings");
                $res3 = mysqli_query($con, "SELECT sn, name, '' as open, close, is_active as active, '' as days, 'jackpot' as gtype, 'jackpot_markets' as tname FROM jackpot_markets");

                $all_games = [];
                while($r = mysqli_fetch_assoc($res1)) $all_games[] = $r;
                while($r = mysqli_fetch_assoc($res2)) $all_games[] = $r;
                while($r = mysqli_fetch_assoc($res3)) $all_games[] = $r;

                foreach($all_games as $row) {
                    // Calculate Display Days for Normal Games
                    $displayDays = 'Daily';
                    if($row['gtype'] == 'normal') {
                        $dayMapping = ['SUNDAY'=>0, 'MONDAY'=>1, 'TUESDAY'=>2, 'WEDNESDAY'=>3, 'THURSDAY'=>4, 'FRIDAY'=>5, 'SATURDAY'=>6];
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
                        $displayDays = !empty($activeNums) ? implode('-', $activeNums) : 'None';
                    }

                    $json = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                ?>
                <tr>
                    <td><span class="badge-type type-<?php echo $row['gtype']; ?>"><?php echo $row['gtype']; ?></span></td>
                    <td><strong><?php echo $row['name']; ?></strong></td>
                    <td><?php echo (!empty($row['open'])) ? date("h:i A", strtotime($row['open'])) : '-'; ?></td>
                    <td><?php echo date("h:i A", strtotime($row['close'])); ?></td>
                    <td><small class="fw-bold text-muted"><?php echo $displayDays; ?></small></td>
                    <td>
                        <button class="edit-btn" onclick='editUnifiedGame(<?php echo $json; ?>)'>
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function showFormDetails() { $("#hiddenFields").slideDown(); }

    function handleTypeChange(val) {
        if(val === 'normal') {
            $("#openTimeGroup").show();
            $("#days_container").show();
        } else {
            $("#openTimeGroup").hide();
            $("#days_container").hide();
        }
    }

    function editUnifiedGame(data) {
        $("#hiddenFields").show();
        $("#formTitle").text("Edit: " + data.name);
        
        $('#game_type').val(data.gtype).trigger('change');
        $('#gameName').val(data.name);
        $('#open').val(data.open);
        $('#close').val(data.close);
        $('#edit_market_sn').val(data.sn);
        $('#edit_table_name').val(data.tname);
        $('#is_active').val(data.active == 1 ? 'Yes' : 'No');

        $('input[name="select_days[]"]').prop('checked', false);
        if (data.days) {
            data.days.split(',').forEach(function(day_str) {
                if (day_str.indexOf('(CLOSED)') === -1) {
                    var dayName = day_str.split('(')[0].trim();
                    $('input[name="select_days[]"][value="' + dayName + '"]').prop('checked', true);
                }
            });
        }

        $('#btnDelete').removeClass('d-none');
        window.scrollTo({top: 0, behavior: 'smooth'});
    }

    function confirmDelete() {
        if(confirm("Delete this game?")) { $("#deleteTrigger").click(); }
    }

    function resetUnifiedForm() {
        $('#gameForm')[0].reset();
        $('#edit_market_sn').val('');
        $('#edit_table_name').val('');
        $('#formTitle').text("Manage All Games");
        $('#btnDelete').addClass('d-none');
        $("#hiddenFields").hide();
        handleTypeChange('normal');
    }
</script>

<?php include('footer.php'); ?>