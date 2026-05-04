<?php 
include('header.php'); 

if (in_array(6, $HiddenProducts)){ 
?>

<style>

body{
background:#e5e5e5;
}

/* FILTER AREA */

.filter-area{
padding:12px;
background:#ddd;
border-radius:10px;
margin-bottom:10px;
}

.round-input{
width:100%;
border-radius:30px;
border:none;
height:42px;
padding-left:15px;
margin-bottom:10px;
box-shadow:0 1px 4px rgba(0,0,0,0.15);
font-size:14px;
}

.submit-btn{
background:#1976d2;
color:white;
border:none;
border-radius:25px;
padding:8px 35px;
font-size:16px;
font-weight:bold;
box-shadow:0 2px 5px rgba(0,0,0,0.2);
}

/* ORANGE HEADER */

.summary-header{
background:#FFA500;
padding:10px;
border-radius:12px;
font-weight:bold;
display:flex;
justify-content:space-between;
margin:12px 0;
}

/* TABLE HEADER */

.game-table-header{
background:black;
color:white;
border-radius:8px;
display:flex;
padding:10px;
font-weight:bold;
}

.game-table-header div{
flex:1;
text-align:center;
}

/* ROW CARD */

.game-row{
display:flex;
background:#f5f5f5;
margin-top:8px;
border-radius:10px;
padding:12px;
box-shadow:0 2px 4px rgba(0,0,0,0.2);
font-weight:600;
}

.game-row div{
flex:1;
text-align:center;
}

/* MOBILE */

@media(max-width:600px){

.game-row{
font-size:15px;
}

.game-table-header{
font-size:15px;
}

}

</style>


<section class="content">

    <div class="container-fluid">

<!-- FILTER AREA -->

        <div class="filter-area">

            <div class="row">

                <div class="col-6">

                    <select id="game_type" class="round-input">
                    <option value="">Game Type</option>
                    <option value="single">Single Ank</option>
                    <option value="jodi">Jodi</option>
                    <option value="singlepatti">Single Pana</option>
                    <option value="doublepatti">Double Pana</option>
                    <option value="triplepatti">Triple Pana</option>
                    <option value="halfsangam">Half Sangam</option>
                    <option value="fullsangam">Full Sangam</option>
                    </select>

                </div>


                <div class="col-6">

                    <select id="game_name" class="round-input">
                    <option value="">All Game</option>
                    
                    <?php
                    
                    $gq = mysqli_query($con,"SELECT DISTINCT market FROM gametime_manual ORDER BY market ASC");
                    
                    while($g = mysqli_fetch_assoc($gq)){
                    ?>
                    
                    <option value="<?php echo $g['market']; ?>">
                    <?php echo $g['market']; ?>
                    </option>
                    
                    <?php } ?>
                    
                    </select>

                </div>


                <div class="col-6">
                
                <select id="status" class="round-input">
                <option value="">Both</option>
                <option value="OPEN">Open</option>
                <option value="CLOSE">Close</option>
                </select>
                
                </div>


                <div class="col-6">
                
                <input type="date" id="date" value="<?php echo date('Y-m-d'); ?>" class="round-input">
                
                </div>


                <div class="col-12 text-center">
                
                <button id="submitFilter" class="submit-btn">Submit</button>
                
                </div>

            </div>

        </div>


        <!-- SUMMARY -->
        
        <div class="summary-header">
        
            <div id="summaryGame">
            Game: all , Market: all
            </div>
            
            <div id="summaryDate">
            Date : <?php echo date("d/M/Y"); ?>
            </div>
            
        </div>


        <!-- TABLE HEADER -->
        
        <div class="game-table-header">
            <div>Game Type</div>
            <div>Bids</div>
            <div>Win</div>
         </div>


        <div id="report-data">
        <?php
        
        $today = date('d/m/Y');
        
        $sql = "SELECT game, amount, status 
                FROM games 
                WHERE date='$today'";
        
        $select = mysqli_query($con,$sql);
        
        $data = [];
        
        if(mysqli_num_rows($select) > 0){
        
            while($row = mysqli_fetch_assoc($select)){
        
                $game = $row['game'];
                $bids = (float)$row['amount'];
        
                // WIN CALCULATION
                $win = ($row['status'] == 'win' || $row['status'] == '1') ? ($bids * 9) : 0;
        
                if(!isset($data[$game])){
                    $data[$game] = [
                        'bids' => 0,
                        'win' => 0
                    ];
                }
        
                $data[$game]['bids'] += $bids;
                $data[$game]['win']  += $win;
            }
        
        }
        // Starline Data
        $starlineData = [];
        $ssql = mysqli_query($con, "SELECT game, amount, win_amount FROM starline_games WHERE date='$today'");
        while($row = mysqli_fetch_assoc($ssql)){
            $g = $row['game'];
            if(!isset($starlineData[$g])) { $starlineData[$g] = ['bids'=>0, 'win'=>0]; }
            $starlineData[$g]['bids'] += (float)$row['amount'];
            $starlineData[$g]['win']  += (float)$row['win_amount'];
        }
        
        // Jackpot Data
        $jackpotData = [];
        $jsql = mysqli_query($con, "SELECT game, amount, win_amount FROM jackpot_games WHERE date='$today'");
        while($row = mysqli_fetch_assoc($jsql)){
            $g = $row['game'];
            if(!isset($jackpotData[$g])) { $jackpotData[$g] = ['bids'=>0, 'win'=>0]; }
            $jackpotData[$g]['bids'] += (float)$row['amount'];
            $jackpotData[$g]['win']  += (float)$row['win_amount'];
        }
                /* GAME TYPES */
        
        $gameTypes = [
        
        'single' => 'Single Ank',
        'jodi' => 'Jodi',
        'singlepatti' => 'Single Pana',
        'doublepatti' => 'Double Pana',
        'triplepatti' => 'Triple Pana',
        'halfsangam' => 'Half Sangam',
        'fullsangam' => 'Full Sangam'
        
        ];
        
        /* DISPLAY */
        
        foreach($gameTypes as $key=>$label){
        
        $bids = isset($data[$key]) ? $data[$key]['bids'] : 0;
        $win  = isset($data[$key]) ? $data[$key]['win'] : 0;
        
        ?>

        <div class="game-row">
            <div><?php echo $label; ?></div>
            <div><?php echo $bids; ?></div>
            <div><?php echo $win; ?></div>
        </div>

    <?php } ?>

    </div>
    
        <!-- 2. STARLINE TABLE (Show All) -->
    <div class="summary-header" style="background:#007bff; color:white; margin-top:25px;">Starline Report</div>
     <div class="game-table-header">
            <div>Game Type</div>
            <div>Bids</div>
            <div>Win</div>
         </div>

    <?php foreach($gameTypes as $key=>$label){ ?>
        <div class="game-row">
            <div><?php echo $label; ?></div>
            <div><?php echo isset($starlineData[$key]) ? $starlineData[$key]['bids'] : 0; ?></div>
            <div><?php echo isset($starlineData[$key]) ? $starlineData[$key]['win'] : 0; ?></div>
        </div>
    <?php } ?>
    
    <!-- 3. JACKPOT TABLE (Show ONLY Jodi) -->
    <div class="summary-header" style="background:#6f42c1; color:white; margin-top:25px;">Jackpot Report</div>
     <div class="game-table-header">
            <div>Game Type</div>
            <div>Bids</div>
            <div>Win</div>
         </div>

    <div class="game-row">
        <div>Jodi</div>
        <div><?php echo isset($jackpotData['jodi']) ? $jackpotData['jodi']['bids'] : 0; ?></div>
        <div><?php echo isset($jackpotData['jodi']) ? $jackpotData['jodi']['win'] : 0; ?></div>
    </div>


    </div>

</section>

<?php 
}else{
echo "<script>window.location.href='unauthorized.php';</script>";
}
include('footer.php');
?>


<script>

$('#submitFilter').click(function(){

var game_type = $('#game_type').val();
var game_name = $('#game_name').val();
var status = $('#status').val();
var date = $('#date').val();

$.ajax({

url:'profit-report-ajax.php',
type:'POST',

data:{
game_type:game_type,
game_name:game_name,
status:status,
date:date
},

success:function(data){

$('#report-data').html(data);

}

});

});

</script>