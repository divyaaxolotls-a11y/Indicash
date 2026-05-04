<?php
include('header.php');

// Check if user has permission
if (in_array(15, $HiddenProducts)) {

    // Ensure the session is active for CSRF
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Generate CSRF token if it doesn't exist
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // ---------------------------------------------------------
    // 1. CONFIGURATION: Map DB Columns to Display Names
    // ---------------------------------------------------------
    $gameMap = [
        'single'       => 'singleank',
        'jodi'         => 'jodi',
        'singlepatti'  => 'singlepana',
        'doublepatti'  => 'doublepana',
        'triplepatti'  => 'triplepana',
        'halfsangam'   => 'halfsangam',
        'fullsangam'   => 'fullsangam'
    ];

    // ---------------------------------------------------------
    // 2. HANDLE FORM SUBMISSION (UPDATE)
    // ---------------------------------------------------------
    if (isset($_POST['submit'])) {

        // Validate CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            echo "<script>alert('Invalid CSRF token. Please refresh the page.');</script>";
        } else {
            
            $selectedColumn = $_POST['game_play']; // value from dropdown
            $inputPrice     = floatval($_POST['price']); // value from input

            // Security check: ensure the column exists in our map
            if (array_key_exists($selectedColumn, $gameMap)) {
                
                // Logic: DB stores (Input / 10). Example: Input 9.5 -> DB 0.95
                $dbValue = $inputPrice / 10; 

                // Update 'rate' table
                $query1 = "UPDATE `rate` SET `$selectedColumn` = '$dbValue'";
                $update1 = mysqli_query($con, $query1);

                // Update 'rates' table (Format "10/95")
                // Logic: "10/" . (0.95 * 100) -> "10/95"
                $ratesValue = "10/" . ($dbValue * 100); 
                $query2 = "UPDATE `rates` SET `$selectedColumn` = '$ratesValue'";
                $update2 = mysqli_query($con, $query2);

                if ($update1) {
                    echo "<script>alert('Rate Updated Successfully'); window.location.href='game-rates.php';</script>";
                } else {
                    echo "<script>alert('Error updating the rates.');</script>";
                }

            } else {
                 echo "<script>alert('Invalid Game Type Selected');</script>";
            }
        }
    }

    // ---------------------------------------------------------
    // 3. FETCH CURRENT DATA
    // ---------------------------------------------------------
    $select = mysqli_query($con, "SELECT * FROM `rate`");
    $row = mysqli_fetch_array($select);
?>

<style>
    /* Orange header style matching your image */
    .bg-custom-orange {
        background-color: #e67e22; /* Burnt Orange */
        color: white;
    }
    
    /* Table Header styling */
    .game-table thead {
        background-color: #e67e22;
        color: white;
    }
    
    /* Center align table content */
    .game-table th, .game-table td {
        text-align: center;
        vertical-align: middle;
    }

    /* Bold labels */
    .form-label {
        font-weight: bold;
        color: #333;
    }

    /* --- MOBILE OPTIMIZATIONS --- */
    @media (max-width: 576px) {
        .game-table {
            font-size: 12px; /* Smaller font for mobile */
        }
        .game-table th, .game-table td {
            padding: 0.3rem; /* Tighter padding */
        }
        /* Allow text to wrap on mobile so it doesn't push width out */
        .game-table th {
            white-space: normal !important; 
        }
    }
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Game Rates</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Game Rates</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3"></div> 
            
            <div class="col-md-6">
                
                <div class="card">
                    <!--<div class="card-header bg-custom-orange">-->
                    <!--    <h3 class="card-title" style="float: none; text-align: center; font-size: 1.5rem; font-weight: bold;">Game Price</h3>-->
                    <!--</div>-->
                    
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="card-body">
                            
                            <div class="form-group">
                                <label class="form-label">Game Play</label>
                                <select name="game_play" class="form-control" required>
                                    <option value="" disabled selected>Select Game Type</option>
                                    <?php 
                                    // Dynamically fill dropdown from $gameMap
                                    foreach($gameMap as $dbCol => $displayName) {
                                        echo "<option value='$dbCol'>$displayName</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Price per 10 Rs.</label>
                                <input type="number" step="0.01" name="price" class="form-control" placeholder="" required>
                            </div>

                            <div class="form-group">
                                <button type="submit" name="submit" class="btn btn-primary bg-primary" style="width: 100px;">update</button>
                            </div>

                        </div>
                    </form>
                </div>

                <div class="card">
                    <div class="card-body p-0">
                        <table class="table table-bordered table-striped table-sm game-table mb-0">
                            <thead>
                                <tr>
                                    <th>Sn.</th>
                                    <th>Type</th>
                                    <th>Price</th>
                                    <th class="d-none d-sm-table-cell">Multiply</th> 
                                    <th>Grand Amt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $sn = 1;
                                foreach($gameMap as $dbCol => $displayName) {
                                    
                                    $price = isset($row[$dbCol]) ? $row[$dbCol] * 10 : 0;
                                    $multiply = 10;
                                    $grandAmount = $price * $multiply;
                                ?>
                                <tr>
                                    <td><?php echo $sn++; ?></td>
                                    <td><?php echo $displayName; ?></td>
                                    <td><?php echo $price; ?></td>
                                    <td class="d-none d-sm-table-cell"><?php echo $multiply; ?></td>
                                    <td><?php echo $grandAmount; ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3"></div> 
        </div>
    </div>
</section>

<?php 
} else { 
    echo "<script>window.location.href = 'unauthorized.php';</script>";
    exit();
}

include('footer.php'); 
?>