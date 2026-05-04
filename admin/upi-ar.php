<?php
include 'header.php'; 

if(isset($_POST['save_upi'])) {
    $upi_id = mysqli_real_escape_string($con, $_POST['upi_id']);
    
    // Admin table update
    $query = "UPDATE admin SET upi_ar = '$upi_id' WHERE sn = '1'";
    $result = mysqli_query($con, $query);
    
    if($result) {
        echo "<script>alert('Saved!');</script>";
    }
}

// Current value fetch karnya sathi
$res = mysqli_query($con, "SELECT upi_ar FROM admin WHERE sn = '1'");
$row = mysqli_fetch_assoc($res);
?>

<div class="main-content" style="background-color: #e9e9e9; min-height: 100vh;">
    <div class="container-fluid pt-3">
        
        <p style="font-family: serif; color: #444; margin-bottom: 10px; font-size: 18px;">Add for Auto Accept Off Upi</p>
        
        <form method="POST" action="">
            <div class="row">
                <div class="col-12">
                    <input type="text" 
                           name="upi_id" 
                           class="form-control" 
                           value="<?php echo $row['upi_ar']; ?>"
                           style="width: 100%; border-radius: 4px; border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <button type="submit" 
                            name="save_upi" 
                            class="btn btn-primary" 
                            style="background-color: #007bff; border: none; padding: 8px 25px; border-radius: 4px; font-size: 16px;">
                        Save
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>

<style>
    /* Mobile responsiveness tweaks */
    @media (max-width: 768px) {
        .main-content {
            padding: 10px;
        }
        input[type="text"] {
            height: 45px; /* Mobile touch sathi thodi height */
        }
    }
</style>