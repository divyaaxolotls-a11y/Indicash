<?php 
include('header.php'); 
if (in_array(12, $HiddenProducts)) {
?>

<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
    * { box-sizing: border-box; }

    /* ── Page wrapper ── */
    .page-wrap {
        width: 100%;
        padding: 12px;
        font-family: sans-serif;
    }

    /* ── Centered black pill title ── */
    .page-title-pill {
        display: block;
        width: 100%;
        margin: 0 auto 18px auto;
        background-color: #000;
        color: #fff;
        font-size: 20px;
        font-weight: 700;
        padding: 8px 28px;
        border-radius: 50px;
        text-align: center;
        letter-spacing: 0.5px;
    }

    /* ── Filter row: always 2 columns ── */
    .filter-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 16px;
    }

    .field-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #444;
        margin-bottom: 4px;
        margin-left: 4px;
    }

    .filter-grid input,
    .filter-grid select {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 13px;
        background: #fff;
        outline: none;
    }

    /* ── Log items — no card, no box ── */
    .log-item {
        padding: 11px 0;
        border-bottom: 1px solid #e0e0e0;
    }
    .log-item:last-child { border-bottom: none; }

    .log-item.green { border-left: 4px solid #28a745; padding-left: 10px; }
    .log-item.pink  { border-left: 4px solid #dc3545; padding-left: 10px; }

    .log-header {
        font-weight: 700;
        font-size: 14px;
        color: #222;
        margin-bottom: 3px;
    }

    .log-activity-title {
        font-weight: 600;
        font-size: 13px;
        color: #333;
        margin-bottom: 4px;
    }

    .log-details {
        font-size: 12px;
        color: #555;
        line-height: 1.5;
    }

    .log-footer {
        font-size: 11px;
        color: #888;
        margin-top: 5px;
        text-align: right;
    }

    @media (max-width: 400px) {
        .filter-grid input,
        .filter-grid select { font-size: 12px; padding: 8px 8px; }
        .page-title-pill { font-size: 13px; padding: 7px 20px; }
        .log-header { font-size: 13px; }
        .log-activity-title { font-size: 12px; }
        .log-details { font-size: 11px; }
    }
</style>

<div class="page-wrap">

    <!-- Centered black pill title -->
    <span class="page-title-pill">User Activity Log</span>

    <!-- Filter row: Date | Activity — 2 per row always -->
    <div class="filter-grid">
        <div>
            <label class="field-label">Date</label>
            <input type="date" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div>
            <label class="field-label">Activity</label>
            <select>
                <option>Select Activity</option>
            </select>
        </div>
    </div>

    <!-- Log list — no cards, no boxes -->
    <div>
        <?php 
            // EXISTING BUSINESS LOGIC START
            $sql = "SELECT * FROM login_logs ORDER BY login_timestamp DESC";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                echo "Error executing query: " . mysqli_error($con);
            } else {
                $i = 0;
                while ($row = mysqli_fetch_array($result)) {
                    $i++;
                    $userEmail     = htmlspecialchars($row['user_email']);
                    $ipAddress     = htmlspecialchars($row['ip_address']);
                    $loginTimestamp = htmlspecialchars($row['login_timestamp']);
                    $userAgent     = htmlspecialchars($row['user_agent']);
                    $remark        = htmlspecialchars($row['remark']);
                    // EXISTING BUSINESS LOGIC END

                    $colorClass = ($i % 2 == 0) ? 'pink' : 'green';
                    $icon = (strpos(strtolower($remark), 'bank') !== false) ? 'fa-university' : 'fa-sign-in-alt';
        ?>
            <div class="log-item <?php echo $colorClass; ?>">
                <div class="log-header">(<?php echo $i; ?>) - <?php echo $userEmail; ?></div>
                <div class="log-activity-title">
                    <i class="fas <?php echo $icon; ?>"></i> <?php echo $remark; ?>
                </div>
                <div class="log-details">
                    <div><strong>IP:</strong> <?php echo $ipAddress; ?></div>
                    <div><strong>Device:</strong> <?php echo $userAgent; ?></div>
                </div>
                <div class="log-footer">user, <?php echo $loginTimestamp; ?></div>
            </div>
        <?php
                }
            }
        ?>
    </div>

</div>

<?php 
} else { 
    echo "<script>window.location.href = 'unauthorized.php';</script>";
    exit();
}
include('footer.php'); ?>