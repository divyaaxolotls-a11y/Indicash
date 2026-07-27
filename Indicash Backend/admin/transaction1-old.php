<?php include('header.php'); 
if (in_array(6, $HiddenProducts)){  
$selected_mobile = $_GET['user_mobile'] ?? '';
?>

<style>
    body { background-color: #f8f9fd; font-family: 'Poppins', sans-serif; }

    /* ── Wrapper ── */
    .content-wrapper { overflow-x: hidden; }

    @media (max-width: 576px) {
        .content-wrapper { padding: 8px !important; }
    }

    .report-container { padding: 10px 8px; }

    /* ── Labels ── */
    .filter-label {
        font-size: 12px;
        font-weight: 600;
        color: #333;
        margin-bottom: 3px;
        margin-left: 4px;
        display: block;
    }

    /* ── Rounded inputs ── */
    .custom-input-round {
        border-radius: 30px !important;
        border: 1px solid #d1d1d1 !important;
        height: 40px !important;
        background-color: white !important;
        padding-left: 15px !important;
        font-size: 13px !important;
        width: 100%;
        box-sizing: border-box;
    }

    /* ── Select2 ── */
    .select2-container--bootstrap4 .select2-selection--single {
        border-radius: 30px !important;
        height: 40px !important;
        border: 1px solid #d1d1d1 !important;
        padding-top: 5px !important;
    }

    /* ── Filter row: date + username side by side ── */
    .filter-row {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
    }

    .filter-row > div { flex: 1; min-width: 0; }

    /* ── Filter button: half width ── */
    .btn-filter-blue {
        background-color: #007bff;
        color: white;
        border-radius: 25px;
        padding: 7px 0;
        font-weight: bold;
        border: none;
        font-size: 14px;
        width: 50%;
        display: block;
        margin-bottom: 12px;
    }

    /* ── Pills row ── */
    .action-pills-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 4px;
        margin: 0 0 14px;
        width: 100%;
    }

    .pill {
        border-radius: 20px;
        padding: 6px 6px;
        color: white;
        font-size: 11px;
        font-weight: 500;
        border: none;
        flex: 1;
        text-align: center;
        white-space: nowrap;
        cursor: pointer;
    }

    .pill-all      { background-color: #3c3f44; }
    .pill-win      { background-color: #1fa1b6; }
    .pill-add      { background-color: #3cb44b; }
    .pill-withdraw { background-color: #e63946; }
    .pill-history  { background-color: #3c3f44; }

    /* ── Table header bar ── */
    .custom-table-header {
        background-color: #ffb100;
        border-radius: 12px;
        padding: 10px 14px;
        display: flex;
        justify-content: space-between;
        font-weight: 700;
        color: #444;
        margin-bottom: 4px;
    }

    .header-col      { flex: 1; text-align: center; font-size: 13px; }
    .header-col.desc { text-align: left; }
    .header-col.bal  { text-align: right; }
    
    .active-pill{
    box-shadow:0 0 0 2px #fff inset;
    } 
   /* Modal width and position */
    #filterModal .modal-dialog{
        max-width:520px;
        margin-top:10px;
    }
    
    /* Scroll inside modal */
    #filterModal .modal-body{
        max-height:70vh;
        overflow-y:auto;
    }
    
    /* Section titles */
    #filterModal h6{
        font-weight:600;
        margin-bottom:12px;
    }
    
    /* Game type & status checkboxes layout */
    #filterModal .modal-body label{
        font-size:16px;
        margin-right:20px;
    }
    
    /* Yellow game boxes */
    #filterModal .game-box{
        display:flex;
        align-items:center;
        background:#f4b400;
        padding:14px 16px;
        border-radius:14px;
        margin-bottom:12px;
        font-weight:600;
        font-size:18px;
    }
    
    /* checkbox spacing inside game box */
    #filterModal .game-box input{
        margin-right:12px;
        width:18px;
        height:18px;
    }
    
    /* bottom buttons */
    #filterModal .modal-footer{
        display:flex;
        justify-content:center;
        gap:15px;
    }
    
    #filterModal .btn-close{
        background:#6c757d;
        border:none;
        padding:10px 20px;
        border-radius:20px;
        color:white;
    }
    
    #filterModal .btn-filter{
        background:#28a745;
        border:none;
        padding:10px 22px;
        border-radius:20px;
        color:white;
    }
    /* ── Extra small screens ── */
    @media (max-width: 360px) {
        .pill { font-size: 10px; padding: 5px 3px; }
        .header-col { font-size: 12px; }
        .btn-filter-blue { width: 60%; font-size: 13px; }
    }
</style>

<div class="report-container">
<input type="hidden" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

    <!-- Date + Username -->
    <div class="filter-row">
        <div>
            <label class="filter-label">Date</label>
            <input type="date" id="date" value="<?php echo date('Y-m-d'); ?>" class="form-control custom-input-round" />
            <input type="hidden" id="tdate" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div>
            <label class="filter-label">Username</label>
            <select class="form-control select2bs4 custom-input-round" id="user_search">
                <option value="">Search for a User</option>
                <?php 
                    $user_query = mysqli_query($con, "SELECT mobile, name FROM users ORDER BY name ASC");
                    while($user_row = mysqli_fetch_array($user_query)){
                    $selected = ($selected_mobile == $user_row['mobile']) ? 'selected' : '';
                ?>
                    <option value="<?php echo $user_row['mobile']; ?>" <?php echo $selected; ?>>
                        <?php echo $user_row['name']; ?>
                    </option>
                <?php } ?>
            </select>
        </div>
    </div>

    <!-- Filter button — half width -->
    <button id="go" class="btn-filter-blue">Filter</button>

    <!-- Action pills -->
    <div class="action-pills-wrapper">
        <button class="pill pill-all" data-type="all">All</button>
        <button class="pill pill-win" data-type="win">Win</button>
        <button class="pill pill-add" data-type="add">Add</button>
        <button class="pill pill-withdraw" data-type="withdraw">Withdraw</button>
        <button class="pill pill-history" id="bid_history_btn">Bid History</button>
    </div>

    <!-- Table header -->
    <!--<div class="custom-table-header">-->
    <!--    <div class="header-col desc">Description</div>-->
    <!--    <div class="header-col">Point</div>-->
    <!--    <div class="header-col bal">Balance</div>-->
    <!--</div>-->

    <div id="report"></div>
        <div class="modal fade" id="matkaModal">
            <div class="modal-dialog modal-dialog-centered">
             <div class="modal-content">
            
                    <div class="modal-header">
                    <h5>Select Game</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
            
                    <div class="modal-body">
                    
                    <button class="btn btn-warning w-100 mb-2" onclick="openFilter()">Matka</button>
                    
                    </div>
            
                 </div>
            </div>
        </div>
        <div class="modal fade" id="filterModal">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable custom-filter-modal">
                <div class="modal-content">
                
                <div class="modal-header">
                <h5>Name :</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                
                <div class="modal-body">
                
                <form id="historyFilterForm">
                
                <h6>By Game Type</h6>
                
                <label><input type="checkbox" name="session" value="Open"> Open</label>
                <label><input type="checkbox" name="session" value="Close"> Close</label>
                
                <hr>
                
                <h6>By Winning Status</h6>
                
                <label><input type="checkbox" name="filter" value="win"> Win</label>
                <label><input type="checkbox" name="filter" value="loss"> Loose</label>
                <label><input type="checkbox" name="filter" value="pending"> Pending</label>
                
                <hr>
                
                <h6>By Game</h6>
                
                <?php
                    $g_q = mysqli_query($con, "SELECT DISTINCT bazar FROM games");
                    $seen = [];
                    
                    while($g = mysqli_fetch_assoc($g_q)){
                    
                        $clean = trim(str_replace(['_OPEN','_CLOSE','_'], ['','', ' '], $g['bazar']));
                    
                        if(!in_array($clean,$seen) && $clean!=''){
                            ?>
                    
                            <label class="game-box">
                                <input type="checkbox" name="game_name[]" value="<?php echo $clean; ?>">
                                <?php echo $clean; ?>
                            </label>
                    
                            <?php
                            $seen[] = $clean;
                        }
                    }
                ?>
                
                <hr>
                
                <button type="button" class="btn btn-success" onclick="goToWinner()">Filter Go</button>
                
                </form>
                
                </div>
            
            </div>
        </div>
</div>
  </div>

<?php } else { 
    echo "<script>window.location.href = 'unauthorized.php';</script>";
    exit();
}
include('footer.php'); ?>

<script>
$(function () {
    // 1. Initialize your searchable dropdown (Keep this as is)
    $('.select2bs4').select2({
        theme: 'bootstrap4',
        placeholder: "Search for a user"
    });

    // 2. NEW LOGIC: Trigger AJAX call automatically if a user is selected on load
    // This happens when you redirect from the User List
    var preselectedUser = $('#user_search').val();
    if(preselectedUser != ''){
        // This triggers your existing $('#go').click() function below
        $('#go').trigger('click');
    }
});
$('#date').change(function(){
    $('#tdate').val($(this).val());
});

$('#go').click(function(){
    var date = $('#date').val();
    var tdate = $('#tdate').val();
    var user_search = $('#user_search').val(); 
    var csrf_token = $('#csrf_token').val();

    if(date != ''){
        $.ajax({    
            type: "POST",
            url: "transaction-ajax.php",              
            data:{
                date: date, 
                tdate: tdate, 
                user_mobile: user_search ,
                csrf_token: csrf_token
            },                  
            success: function(data){
                $('#report').html(data);
            }
        });
    }
});

$(function () {
    $('.select2bs4').select2({
        theme: 'bootstrap4',
        placeholder: "Search for a user"
    });
});
$('.pill').click(function(){

    $('.pill').removeClass('active-pill');
    $(this).addClass('active-pill');

    var type = $(this).data('type');
    var date = $('#date').val();
    var tdate = $('#tdate').val();
    var user_search = $('#user_search').val();
    var csrf_token = $('#csrf_token').val();

    $.ajax({
        type: "POST",
        url: "transaction-ajax.php",
        data:{
            date: date,
            tdate: tdate,
            user_mobile: user_search,
            filter_type: type,
            csrf_token: csrf_token
        },
        success:function(data){
            $('#report').html(data);
        }
    });

});
$('#bid_history_btn').click(function(){
    $('#matkaModal').modal('show');
});
function openFilter(){

$('#matkaModal').modal('hide');
$('#filterModal').modal('show');

}

function goToWinner(){

var game_name = $('input[name="game_name[]"]:checked').val();
var session = $('input[name="session"]:checked').val();
var filter = $('input[name="filter"]:checked').val();

var url = "winners.php?";

if(game_name){
url += "game_name="+encodeURIComponent(game_name)+"&";
}

if(session){
url += "session="+encodeURIComponent(session)+"&";
}

if(filter){
url += "filter="+encodeURIComponent(filter)+"&";
}

window.location.href = url;

}
</script>