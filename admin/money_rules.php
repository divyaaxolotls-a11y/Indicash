<?php
include('header.php'); 

/* ================= DELETE LOGIC ================= */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($con, "DELETE FROM money_rules WHERE id=$id");
    echo "<script>window.location.href='money_rules.php';</script>";
}

/* ================= FETCH FOR EDIT ================= */
$editData = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = mysqli_query($con, "SELECT * FROM money_rules WHERE id=$id");
    $editData = mysqli_fetch_assoc($res);
}

/* ================= ADD / UPDATE LOGIC ================= */
if (isset($_POST['submit'])) {
    $type = $_POST['rule_type'];
    $message = trim($_POST['message']); 

    if ($type != '' && $message != '') {
        $message = mysqli_real_escape_string($con, $message); 
        
        if (!empty($_POST['id'])) {
            $id = intval($_POST['id']);
            mysqli_query($con, "UPDATE money_rules SET rule_type='$type', message='$message' WHERE id=$id");
        } else {
            mysqli_query($con, "INSERT INTO money_rules (rule_type, message) VALUES ('$type', '$message')");
        }
        echo "<script>window.location.href='money_rules.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <title>Money Rules Admin</title>
    <style>
        /* FORCE ROOT TO BE LIGHT MODE */
        :root {
            color-scheme: light;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 10px;
            color: #333;
        }

        .mobile-container {
            max-width: 480px; 
            margin: 0 auto;
            background: #fff;
            min-height: 90vh;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
            padding-bottom: 20px;
        }

        .header {
            background: #e9e9f0;
            padding: 15px;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
            font-size: 18px;
            color: #444;
        }

        .form-section {
            padding: 20px;
        }

        label {
            font-weight: 700;
            color: #000;
            margin-bottom: 8px;
            display: block;
            font-size: 15px;
        }

        /* --- THE FIX: color-scheme: light --- */
        .custom-select {
            width: 100%;
            padding: 14px;
            border: 2px solid #ccc;
            border-radius: 10px;
            margin-bottom: 15px;
            box-sizing: border-box;
            outline: none;
            
            /* FORCE LIGHT MODE COLORS */
            background-color: #ffffff !important;
            color: #000000 !important;
            color-scheme: light !important; /* Forces Native Picker to be Light */
            
            font-size: 16px !important; /* Prevents iOS Zoom */
            font-weight: 600 !important;
            
            /* Reset Appearance */
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            
            /* Custom Arrow */
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23000000%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 12px;
        }

        /* Force Options to be Black */
        .custom-select option {
            background-color: #ffffff;
            color: #000000;
        }
        
        .custom-textarea {
            width: 100%;
            padding: 14px;
            border: 2px solid #ccc;
            border-radius: 10px;
            margin-bottom: 15px;
            box-sizing: border-box;
            outline: none;
            height: 100px;
            resize: none;
            background: #fff;
            color: #000;
            font-size: 15px;
            color-scheme: light; /* Forces Light Mode */
        }

        .btn-save {
            background-color: #2196F3;
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 3px 8px rgba(33, 150, 243, 0.3);
            width: 100%; 
        }

        .section-title {
            padding: 0 20px;
            color: #555;
            font-size: 16px;
            margin-top: 15px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .rule-card {
            background-color: #e6e6fa; 
            margin: 10px 20px;
            padding: 15px;
            border-radius: 15px;
        }

        .rule-content {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            font-size: 15px;
            color: #333; 
            font-weight: 500;
            line-height: 1.4;
        }

        .icon {
            margin-right: 10px;
            font-size: 18px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end; 
        }

        .btn-pill {
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            color: white;
            font-size: 13px;
            font-weight: bold;
            border: none;
            display: inline-block;
            cursor: pointer;
        }

        .btn-update { background-color: #2196F3; }
        .btn-delete { background-color: #dc3545; }

    </style>
</head>
<body>

<div class="mobile-container">

    <div class="form-section">
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $editData['id'] ?? ''; ?>">

            <label>Select Type</label>

<select name="rule_type" class="form-control" required>
    <option value="">-- Select Option --</option>
    <option value="ADD" <?php if(($editData['rule_type'] ?? '')=='ADD') echo 'selected'; ?>>
        Add Money
    </option>
    <option value="WITHDRAW" <?php if(($editData['rule_type'] ?? '')=='WITHDRAW') echo 'selected'; ?>>
        Withdraw Money
    </option>
</select>


            <label>Message</label>
            <textarea name="message" class="custom-textarea" placeholder="Enter rule message..." required><?php echo $editData['message'] ?? ''; ?></textarea>

            <button type="submit" name="submit" class="btn-save">
                <?php echo $editData ? 'Update Rule' : 'Save Rule'; ?>
            </button>
        </form>
    </div>

    <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 10px;">

    <?php
    $result = mysqli_query($con, "SELECT * FROM money_rules ORDER BY id DESC");
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $headerText = ($row['rule_type'] == 'ADD') ? "Set Rules Add Money" : "Set Rules Withdraw Money";
    ?>
            <div class="section-title">
                <?php echo $headerText; ?>
            </div>

            <div class="rule-card">
                <div class="rule-content">
                    
                    <span><?php echo nl2br(htmlspecialchars($row['message'])); ?></span>
                </div>

                <div class="action-buttons">
                    <a href="money_rules.php?edit=<?php echo $row['id']; ?>" class="btn-pill btn-update">
                        Update
                    </a>

                    <a href="money_rules.php?delete=<?php echo $row['id']; ?>" 
                       class="btn-pill btn-delete"
                       onclick="return confirm('Are you sure you want to delete this?')">
                        Delete
                    </a>
                </div>
            </div>
    <?php 
        } 
    } else {
        echo "<p style='text-align:center; color:#999; padding:20px;'>No rules set yet.</p>";
    }
    ?>

</div>

</body>
</html>