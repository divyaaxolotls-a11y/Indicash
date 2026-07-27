<?php
include('header.php');

if (in_array(15, $HiddenProducts)) {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    if (!isset($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }

    // Mapping DB columns to the readable labels shown in your image
    $gameMap =[
        'single'      => 'singleank',
        'jodi'        => 'jodi',
        'singlepatti' => 'singlepana',
        'doublepatti' => 'doublepana',
        'triplepatti' => 'triplepana',
        'halfsangam'  => 'halfsangam',
        'fullsangam'  => 'fullsangam'
    ];

    if (isset($_POST['submit'])) {
        $selectedColumn = $_POST['game_play'];
        $inputPrice     = floatval($_POST['price']);

        if (array_key_exists($selectedColumn, $gameMap)) {
            // Update 'rate' table
            mysqli_query($con, "UPDATE `rate` SET `$selectedColumn` = '$inputPrice'");
            
            // Update 'rates' table (Format: 10/Value)
            $ratesValue = "10/" . ($inputPrice * 10);
            mysqli_query($con, "UPDATE `rates` SET `$selectedColumn` = '$ratesValue'");

            echo "<script>alert('Updated Successfully'); window.location.href='".basename($_SERVER['PHP_SELF'])."';</script>";
        }
    }

    $select = mysqli_query($con, "SELECT * FROM `rate`");
    $row = mysqli_fetch_array($select);
?>

<style>
    .game-price-header { background-color: #f1c40f; text-align: center; padding: 10px; font-weight: bold; font-size: 20px; margin-bottom: 10px; border: 1px solid #ccc; }
    .table thead { background-color: #e67e22; color: white; }
    .btn-update { background-color: #007bff; color: white; border: none; width: 100%; padding: 10px; }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="game-price-header">Game Price</div>
            
            <div class="card p-3">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="form-group">
                        <label class="form-label">Game Play</label>
                        <select name="game_play" class="form-control" onchange="updatePrice(this.value)" required>
                            <option value="" disabled selected>Select Game Type</option>
                            <?php 
                            foreach($gameMap as $dbCol => $displayName) {
                                echo "<option value='$dbCol'>$displayName</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Price per 10 Rs.</label>
                        <input type="number" step="any" name="price" class="form-control" required>
                    </div>
                    <button type="submit" name="submit" class="btn-update">update</button>
                </form>
            </div>

            <div class="card p-0">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Sn. no</th><th>Game Type</th><th>Price</th><th>multiply</th><th>Grand Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sn = 1;
                        foreach($gameMap as $dbCol => $label) {
                            $val = isset($row[$dbCol]) ? $row[$dbCol] : 0;
                            echo "<tr>
                                    <td>{$sn}</td>
                                    <td>{$label}</td>
                                    <td>{$val}</td>
                                    <td>10</td>
                                    <td>" . ($val * 10) . "</td>
                                  </tr>";
                            $sn++;
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    // Create a JavaScript object containing all current rates from PHP
    const gameRates = {
        <?php 
        foreach($gameMap as $dbCol => $displayName) {
            $val = isset($row[$dbCol]) ? $row[$dbCol] : 0;
            echo "'$dbCol': '$val',";
        }
        ?>
    };

    function updatePrice(gameType) {
        // Find the input field
        const priceInput = document.querySelector('input[name="price"]');
        
        // Update the input field with the value from our JS object
        if (gameRates[gameType] !== undefined) {
            priceInput.value = gameRates[gameType];
        }
    }
</script>
<?php 
} else { echo "<script>window.location.href = 'unauthorized.php';</script>"; }
include('footer.php'); 
?>