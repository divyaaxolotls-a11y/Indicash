<?php
// Start output buffering to prevent header HTML from breaking AJAX
ob_start(); 
include('header.php');

// --- AJAX HANDLER FOR JACKPOT STATUS TOGGLE ---
if (isset($_POST['action']) && $_POST['action'] == 'toggle_status') {
    ob_clean(); 
    
    $sn = mysqli_real_escape_string($con, $_POST['sn']);
    $current_status = (int)$_POST['status']; // 1 or 0
    
    // Toggle logic: If 1, make 0. If 0, make 1.
    $new_status = ($current_status == 1) ? 0 : 1;
    
    // Update the jackpot_markets table using 'is_active' column
    $updateQuery = "UPDATE `jackpot_markets` SET `is_active`='$new_status' WHERE `sn`='$sn'";
    
    if (mysqli_query($con, $updateQuery)) {
        echo $new_status; // Send back "0" or "1"
    } else {
        echo "error";
    }
    exit; 
}
ob_end_flush(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Jackpot Game Timetable</title>
    <style>
        /* --- STYLING --- */
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, sans-serif; margin: 0; padding: 0; }
        
        .page-title {
            text-align: center; font-size: 20px; font-weight: 700; padding: 15px 0;
            background: #2e7d32; color: #fff; /* Dark Green for Jackpot */
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 100; margin-bottom: 20px;
        }

        .main-content { max-width: 800px; margin: 0 auto; padding: 0 15px; }

        .table-header-row {
            background-color: #388e3c; color: white; display: flex; align-items: center;
            padding: 12px 15px; font-weight: 600; font-size: 14px; border-radius: 8px;
            margin-bottom: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .col-sno { width: 15%; text-align: left; }
        .col-game { width: 60%; text-align: left; }
        .col-action { width: 25%; text-align: right; }

        .game-card {
            background: #ffffff; border-radius: 12px; padding: 15px; margin-bottom: 12px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 5px solid #2e7d32;
            transition: transform 0.2s;
        }

        .gc-left { display: flex; align-items: center; flex-grow: 1; }
        .gc-sno { font-weight: bold; color: #aaa; width: 35px; font-size: 14px; }
        .gc-name { font-size: 16px; font-weight: 700; color: #333; text-transform: uppercase; }

        .toggle-btn {
            background-color: #28a745; color: white; border: none; border-radius: 50px;
            padding: 8px 0; width: 70px; font-size: 13px; font-weight: bold;
            cursor: pointer; transition: all 0.3s ease; text-align: center;
        }

        .toggle-btn.off { background-color: #dc3545 !important; }

        @media (max-width: 480px) {
            .gc-name { font-size: 14px; }
            .toggle-btn { width: 60px; font-size: 11px; }
        }
    </style>
</head>
<body>

    <div class="page-title">Jackpot Market Management</div>

    <div class="main-content">
        <div class="table-header-row">
            <div class="col-sno">S.No</div>
            <div class="col-game">Jackpot Name</div>
            <div class="col-action">Status</div>
        </div>

        <?php
        $count = 1;
        // Fetching from jackpot_markets table
        $query = "SELECT * FROM `jackpot_markets` ORDER BY sn DESC"; 
        $result = mysqli_query($con, $query);

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $status = $row['is_active']; // Specific column for jackpot
                $btnClass = ($status == 1) ? '' : 'off';
                $btnText = ($status == 1) ? 'ON' : 'OFF';
        ?>
            
            <div class="game-card">
                <div class="gc-left">
                    <div class="gc-sno"><?php echo $count; ?></div>
                    <div class="gc-info">
                        <span class="gc-name"><?php echo $row['name']; ?></span>
                    </div>
                </div>
                <div class="gc-right">
                    <button class="toggle-btn <?php echo $btnClass; ?>" 
                            data-sn="<?php echo $row['sn']; ?>" 
                            data-status="<?php echo $status; ?>"
                            onclick="toggleJackpot(this)">
                        <?php echo $btnText; ?>
                    </button>
                </div>
            </div>

        <?php
                $count++;
            }
        } else {
            echo "<div style='text-align:center; padding:20px; color:#999;'>No Jackpot Markets found.</div>";
        }
        ?>

    </div>

    <?php include('footer.php'); ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function toggleJackpot(btn) {
            var $btn = $(btn);
            var sn = $btn.data('sn');
            var currentStatus = $btn.data('status');

            $btn.prop('disabled', true).css('opacity', '0.6');

            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: {
                    action: 'toggle_status',
                    sn: sn,
                    status: currentStatus
                },
                success: function(response) {
                    var cleanResponse = response.trim();
                    if (cleanResponse === '0' || cleanResponse === '1') {
                        var newStatus = parseInt(cleanResponse);
                        $btn.data('status', newStatus);
                        if (newStatus === 1) {
                            $btn.removeClass('off').text('ON');
                        } else {
                            $btn.addClass('off').text('OFF');
                        }
                    } else {
                        alert("Update failed. Try again.");
                    }
                    $btn.prop('disabled', false).css('opacity', '1');
                },
                error: function() {
                    alert('Internet error.');
                    $btn.prop('disabled', false).css('opacity', '1');
                }
            });
        }
    </script>

</body>
</html>