<?php include('header.php'); 

if (in_array(10, $HiddenProducts)){

?>



<style>

    /* Global Background */

    body {

        background-color: #f4f6f9;

        font-family: 'Segoe UI', sans-serif;

    }



    /* --- Filter Box Styling --- */

    .filter-container {

        background-color: white; 

        padding: 15px; 

        border-radius: 12px;

        box-shadow: 0 1px 3px rgba(0,0,0,0.1);

        margin-bottom: 20px;

    }



    .filter-label {

        font-weight: 500;

        color: #333;

        font-size: 13px; 

        margin-bottom: 5px;

        display: block;

    }



    /* Pill Shaped Inputs */

    .custom-pill-input {

        border-radius: 30px !important; 

        border: 1px solid #ced4da;

        height: 40px; 

        padding-left: 15px;

        padding-right: 15px;

        width: 100%;

        background-color: #fff;

        color: #495057;

        font-size: 13px;

        box-shadow: none;

    }

    

    .custom-pill-input:focus {

        border-color: #80bdff;

        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);

    }



    /* Select2 Styling */

    .select2-container .select2-selection--single {

        height: 40px !important; 

        border-radius: 30px !important;

        border: 1px solid #ced4da !important;

        padding-top: 6px; 

        padding-left: 15px;

        font-size: 13px;

    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {

        top: 6px !important;

        right: 10px !important;

    }



    /* Filter Button Styling */

    .btn-filter-blue {

        background-color: #007bff; 

        color: white;

        border: none;

        border-radius: 30px; 

        height: 40px; 

        width: 100%;

        font-weight: 600;

        font-size: 14px;

        cursor: pointer;

        transition: background-color 0.2s;

        margin-top: 0; 

    }

    .btn-filter-blue:hover {

        background-color: #0056b3;

    }



    #number { display: none; }

    

    .filter-row {

        margin-bottom: 15px;

    }



    /* Orange Table Header */

    .table-header-orange {

        background-color: #ffaa00 !important;

        color: black;

        font-weight: bold;

        text-transform: uppercase;

        border: none;

        font-size: 13px; 

    }

    

    .table td, .table th {

        vertical-align: middle;

        text-align: center;

        border-bottom: 1px solid #dee2e6;

        padding: 8px; 

        font-size: 13px;

    }

</style>



<section class="content-header">

    <div class="container-fluid">

        <div class="row mb-2">

            <div class="col-sm-6">

                <h1>Bet Report</h1>

            </div>

            <div class="col-sm-6">

                <ol class="breadcrumb float-sm-right">

                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>

                    <li class="breadcrumb-item active">Bet Report</li>

                </ol>

            </div>

        </div>

    </div>

</section>



<section class="content">

    <div class="container-fluid">

        <div class="row justify-content-center">

            <div class="col-md-8 col-12"> 

                <div class="filter-container">

                    <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <input type="number" id="number" value="0">



                    <div class="row filter-row">

                        <div class="col-6">

                            <label class="filter-label">Game Type</label>

                            <select class="form-control custom-pill-input">

                                <option>Game Type</option>

                                <option>Market</option>

                                <option>Starline</option>

                            </select>

                        </div>

                        <div class="col-6">

                            <label class="filter-label">Game List</label>

                            <select id="gameId" name="market" class="form-control select2bs4 custom-pill-input" style="width: 100%;">

                                <option value="" selected disabled>Select Game</option>

                                <?php

                                    $game = mysqli_query($con, "SELECT * FROM `gametime_delhi` ORDER BY market DESC");

                                    while($row = mysqli_fetch_array($game)){

                                ?>

                                    <option value="<?php echo htmlspecialchars($row['market']); ?>"><?php echo htmlspecialchars($row['market']); ?></option>

                                <?php } ?>

                                

                                <?php

                                    $game = mysqli_query($con, "SELECT * FROM `gametime_manual` ORDER BY market DESC");

                                    while($row = mysqli_fetch_array($game)){

                                ?>

                                    <option value="<?php echo $row['market']; ?>"><?php echo htmlspecialchars($row['market']); ?></option>

                                <?php } ?>

                            </select>

                        </div>

                    </div>



                    <div class="row filter-row">

                        <div class="col-6">

                            <label class="filter-label">Date</label>

                            <input type="date" name="date" id="resultDate" value="<?php echo date('Y-m-d'); ?>" class="form-control custom-pill-input" />

                        </div>

                        <div class="col-6">

                            <label class="filter-label">Open / Close</label>

                            <select class="form-control custom-pill-input" id="openClose">

                                <option value="">All</option>

                                <option value="Open">Open</option>

                                <option value="Close">Close</option>

                            </select>

                        </div>

                    </div>



                    <div class="row">

                        <div class="col-6">

                            <label class="filter-label">Search User</label>

                            <select class="form-control select2bs4 custom-pill-input" id="user_search">

                                <option value="">Search for a user</option>

                                <?php 

                                    $u_query = mysqli_query($con, "SELECT mobile, name FROM users ORDER BY name ASC");

                                    while($u_row = mysqli_fetch_array($u_query)){

                                ?>

                                    <option value="<?php echo $u_row['mobile']; ?>"><?php echo $u_row['name']; ?> (<?php echo $u_row['mobile']; ?>)</option>

                                <?php } ?>

                            </select>

                        </div>

                        <div class="col-6">

                            <label class="filter-label">&nbsp;</label>

                            <button type="button" id="go" class="btn-filter-blue">Filter</button>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <div class="row justify-content-center">

            <div class="col-md-10 col-12">

                <div class="card shadow-none">

                    <div class="card-body table-responsive p-0" style="border-radius: 10px; overflow: hidden;">

                        <table id="example1" class="w-100 table table-hover text-nowrap">

                            <thead class="table-header-orange">

                                <tr>

                                    <th>Game</th>

                                    <th>Type</th>

                                    <th>Open Close</th>

                                    <th>No.</th>

                                    <th>Bet</th>

                                </tr>

                            </thead>

                            <tbody id="result_data">

                                </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>



<?php } else { 

    echo "<script>window.location.href = 'unauthorized.php';</script>";

    exit();

}

include('footer.php'); ?>



<script>

$(function () {

    $('.select2bs4').select2({

        theme: 'bootstrap4'

    });

});



$('#go').click(function(){

    var date = $('#resultDate').val();

    var gameId = $('#gameId').val();

    var amount = $('#number').val(); 

    var csrf_token = $('#csrf_token').val();

    

    // CAPTURING ALL FILTER VALUES

    var openClose = $('#openClose').val(); 

    var userSearch = $('#user_search').val(); 



    if((date) && (gameId) && (csrf_token)){

        // Visual indicator that it's loading

        $('#result_data').html('<tr><td colspan="5" style="text-align:center;">Loading...</td></tr>');



        $.ajax({    

            type: "POST",

            url: "bet-list-ajax.php",              

            data:{

                resultDate: date, 

                gameID: gameId, 

                amount: amount, 

                csrf_token: csrf_token,

                openClose: openClose,

                userSearch: userSearch

            },

            success: function(data){

                $('#result_data').html(data);

            },

            error: function(){

                alert("Error retrieving data. Please try again.");

            }

        });

    } else {

        alert("Please select Game and Date");

    }

});

$(document).ready(function() {

    // 1. URL madhun parameters ghyayche

    const urlParams = new URLSearchParams(window.location.search);

    const marketParam = urlParams.get('market');

    const dateParam = urlParams.get('date');

    const sessionParam = urlParams.get('session');



    // 2. Jar market aani date asel, tar form auto-fill kara

    if (marketParam && dateParam) {

        // Game Dropdown set kara

        $('#gameId').val(marketParam).trigger('change');

        

        // Date Input set kara

        $('#resultDate').val(dateParam);

        

        // Session (Open/Close) set kara jar asel tar

        if(sessionParam) {

            $('#openClose').val(sessionParam); // Tumchya select tag cha ID 'openClose' asava

        }



        // 3. 'Filter' button var auto-click kara jyamule lagech records distil

        setTimeout(function() {

            $('#go').click();

        }, 500); 

    }

});

</script>