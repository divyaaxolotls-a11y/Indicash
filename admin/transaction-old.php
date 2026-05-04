<?php include('header.php');
$selected_mobile = $_GET['mobile'] ?? '';

$user_name_for_display = '';

if ($selected_mobile) {
    $res = mysqli_query($con, "SELECT name FROM users WHERE mobile='$selected_mobile'");
    $data = mysqli_fetch_assoc($res);
    $user_name_for_display = $data['name'] ?? '';
}

if (in_array(6, $HiddenProducts)){  ?>

<style>
    body { background-color: #f1f1f1; }

    /* ===== FILTER SECTION ===== */
    .filter-wrapper {
        padding: 4px 0;
        margin-bottom: 16px;
    }

    .custom-input-round {
        border-radius: 25px;
        border: none;
        height: 42px;
        background-color: white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        width: 100%;
        padding-left: 15px;
        color: #555;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .select2-container .select2-selection--single {
        height: 42px !important;
        border-radius: 25px !important;
        border: none !important;
        padding-top: 7px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .btn-filter-blue {
        background-color: #007bff;
        color: white;
        border-radius: 25px;
        width: 80%;
        max-width: 300px;
        height: 42px;
        font-weight: bold;
        font-size: 15px;
        border: none;
        margin-top: 8px;
        box-shadow: 0 4px 6px rgba(0,123,255,0.3);
    }

    /* ===== TABLE ===== */
    .table-header-orange {
        background-color: #FFA500 !important;
        color: black;
        font-weight: bold;
        text-align: center;
        border: none;
    }

    .row-green { background-color: #008000 !important; color: white; }
    .row-red   { background-color: #ff4d4d !important; color: white; }

    .table td, .table th {
        vertical-align: middle;
        text-align: center;
        border: 1px solid white;
        padding: 10px 8px;
        font-size: 14px;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* ===== MOBILE ===== */
    @media (max-width: 576px) {
        .content-wrapper {
            padding: 8px !important;
        }

        .container-fluid {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .custom-input-round {
            height: 38px;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .select2-container .select2-selection--single {
            height: 38px !important;
            padding-top: 5px;
        }

        .btn-filter-blue {
            width: 100%;
            max-width: 100%;
            height: 40px;
            font-size: 14px;
        }

        .table td, .table th {
            padding: 8px 6px;
            font-size: 12px;
        }
    }
</style>

<section class="content">
    <div class="container-fluid">

        <div class="filter-wrapper">
            <div class="row">
                <div class="col-6">
                    <small class="text-muted font-weight-bold" style="padding-left:14px;">Game List</small>
                    <select class="form-control custom-input-round" id="game_name">
                        <option value="">All Games</option>
                        <?php
                        $game_query = mysqli_query($con, "SELECT DISTINCT bazar FROM games ORDER BY bazar ASC");
                        while($row_game = mysqli_fetch_array($game_query)){ ?>
                            <option value="<?php echo $row_game['bazar']; ?>"><?php echo $row_game['bazar']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-6">
                    <small class="text-muted font-weight-bold" style="padding-left:14px;">Date</small>
                    <input type="date" id="date" value="<?php echo date('Y-m-d'); ?>" class="form-control custom-input-round" />
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <select class="form-control custom-input-round" id="status_filter">
                        <option value="">Open-Close</option>
                        <option value="OPEN">Open</option>
                        <option value="CLOSE">Close</option>
                    </select>
                </div>
                <div class="col-6">
                   <?php if ($selected_mobile): ?>
                        <!-- From popup -->
                        <input type="text" 
                               class="form-control custom-input-round" 
                               value="<?php echo $user_name_for_display; ?>" 
                               readonly>
                    
                        <input type="hidden" id="user_search" value="<?php echo $selected_mobile; ?>">
                    <?php else: ?>
                        <!-- Default -->
                        <select class="form-control custom-input-round select2bs4" id="user_search">
                            <option value="">Search for a User</option>
                            <?php 
                            $user_query = mysqli_query($con, "SELECT mobile, name FROM users ORDER BY name ASC");
                            while($user_row = mysqli_fetch_array($user_query)){ ?>
                                <option value="<?php echo $user_row['mobile']; ?>">
                                    <?php echo $user_row['name']; ?> (<?php echo $user_row['mobile']; ?>)
                                </option>
                            <?php } ?>
                        </select>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-center">
                   <button type="button" id="go" class="btn btn-filter-blue">Filter</button>
                </div>
            </div>
        </div>

        <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <div id="report-container">
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-header-orange">
                        <tr>
                            <th style="text-align:left; padding-left:15px;">Game</th>
                            <th>Bids</th>
                            <th>Win</th>
                            <th>PL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $today = date('d/m/Y');
                        $select = mysqli_query($con, "SELECT * FROM games WHERE date = '$today' ORDER BY sn DESC");
                        if(mysqli_num_rows($select) > 0) {
                            while($row = mysqli_fetch_array($select)){
                                $bids = $row['amount'];
                                $win = ($row['status'] == 'win' || $row['status'] == '1') ? ($row['amount'] * 9) : 0;
                                $pl = $bids - $win;
                                $rowClass = ($pl >= 0) ? 'row-green' : 'row-red';
                            ?>
                                <tr class="<?php echo $rowClass; ?>">
                                    <td style="text-align:left; padding-left:15px; font-weight:bold;"><?php echo $row['bazar']; ?> (<?php echo $row['game_type']; ?>)</td>
                                    <td><?php echo $bids; ?></td>
                                    <td><?php echo $win; ?></td>
                                    <td><?php echo $pl; ?></td>
                                </tr>
                            <?php } 
                        } else {
                            echo "<tr><td colspan='4' style='background:white; color:black; padding:20px;'>No data found</td></tr>";
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>

<?php } else { echo "<script>window.location.href = 'unauthorized.php';</script>"; exit(); }
include('footer.php'); ?>

<script>
window.onload = function() {
    if (window.jQuery) {
        $(document).ready(function(){
            // Initialize Select2
            // if ($.fn.select2) {
            //     $('.select2bs4').select2({ theme: 'bootstrap4' });
            // }
            // if ($.fn.select2) {
            //     $('#user_search').select2({
            //         theme: 'bootstrap4',
            //         minimumInputLength: 4, // Requirement: start searching after 4 characters
            //         placeholder: "Search for a user",
            //         ajax: {
            //             url: 'user-search-live.php', // We will create this file in Step 3
            //             dataType: 'json',
            //             delay: 250, // Wait 250ms after typing finishes before sending request
            //             data: function (params) {
            //                 return {
            //                     q: params.term // Search term entered by user
            //                 };
            //             },
            //             processResults: function (data) {
            //                 return {
            //                     results: data
            //                 };
            //             },
            //             cache: true
            //         }
            //     });
            // }
            
            var selected_mobile = "<?= $selected_mobile ?>";
            
            if ($.fn.select2 && selected_mobile === '') {
                $('#user_search').select2({
                    theme: 'bootstrap4',
                    minimumInputLength: 4,
                    placeholder: "Search for a user",
                    ajax: {
                        url: 'user-search-live.php',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return { q: params.term };
                        },
                        processResults: function (data) {
                            return { results: data };
                        },
                        cache: true
                    }
                });
            }
        
                    $('#go').on('click', function(e){
                e.preventDefault();

                var date = $('#date').val();
                var csrf_token = $('#csrf_token').val();
                var game_name = $('#game_name').val(); 
                var user_mobile = $('#user_search').val();
                var session = $('#status_filter').val();

                $.ajax({    
                    type: "POST",
                    url: "transaction-ajax.php",              
                    data:{
                        date:date,
                        csrf_token:csrf_token,
                        game_name:game_name,
                        user_mobile:user_mobile,
                        session:session
                    },                 
                    success: function(data){
                        $('#report-container').html(data);
                    },
                    error: function(xhr){
                        console.log("AJAX Error:", xhr.responseText);
                    }
                });
            });
        });
    } else {
        console.error("jQuery is still not loaded. Please check footer.php");
    }
};
</script>