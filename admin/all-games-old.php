<?php
include('header.php');

// १. मुख्य क्वेरी: गॅम्स आणि त्यांचे लेटेस्ट रिझल्ट्स एकत्र मिळवण्यासाठी
$query = "
    SELECT g.*, r.open_panna, r.open as open_num, r.close as close_num, r.close_panna , r.res_time 
    FROM (
        SELECT market, open, close FROM gametime_new
        UNION
        SELECT market, open, close FROM gametime_manual
    ) g
    LEFT JOIN (
        SELECT market, open_panna, open, close, close_panna , created_at as res_time 
        FROM manual_market_results 
        WHERE sn IN (SELECT MAX(sn) FROM manual_market_results GROUP BY market)
    ) r ON g.market = r.market
    ORDER BY g.market ASC";
$result = mysqli_query($con, $query);
$current_date = date('d-m-Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { background-color: #f4f6f9; font-family: sans-serif; margin: 0; }
        .game-list-wrapper { max-width: 480px; margin: 0 auto; background: #fff; border: 1px solid #ddd; }
        .game-card { display: flex; border-bottom: 2px solid #f2f2f2; min-height: 120px; }
        
        .card-left { 
            flex: 1.2; display: flex; flex-direction: column; 
            justify-content: center; align-items: center; 
            border-right: 1px solid #eee; padding: 10px; 
        }
        .market-name { color: #007bff; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; text-align: center; }
        .market-number { color: #007bff; font-weight: bold; font-size: 1.2rem; letter-spacing: 1px; }

        .card-right { flex: 1; padding: 10px; text-align: center; font-size: 0.85rem; display: flex; flex-direction: column; justify-content: center; }
        .status-open { color: #333; font-weight: bold; }
        .status-close { color: #dc3545; font-weight: bold; margin-top: 8px; }
        .time-display { display: block; color: #666; }
        .result-time-label { font-size: 0.75rem; color: #cc0000; font-weight: bold; margin-top: 5px; }
    </style>
</head>
<body>

<div class="game-list-wrapper">
    <?php 
    if(mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $open_time = date("h:i:s A", strtotime($row['open']));
            $close_time = date("h:i:s A", strtotime($row['close']));
            $result_recorded_time = !empty($row['res_time']) ? date("h:i A", $row['res_time']) : '';

            // रिझल्ट फॉरमॅट तयार करणे (उदा. 123-45-678)
            // जर close चा डेटा नसेल तर फक्त '***' दाखवले जाईल
            $res_open_panna = !empty($row['open_panna']) ? $row['open_panna'] : '***';
            $res_open_digit = ($row['open_num'] !== null) ? $row['open_num'] : '*';
            $res_close_digit = ($row['close_num'] !== null) ? $row['close_num'] : '*';
            $res_close_panna = !empty($row['close_panna']) ? $row['close_panna'] : '***';
            
            $display_result = $res_open_panna . "-" . $res_open_digit . $res_close_digit . "-" . $res_close_panna;
            ?>
            
            <div class="game-card">
                <div class="card-left">
                    <div class="market-name"><?php echo htmlspecialchars($row['market']); ?></div>
                    <div class="market-number"><?php echo $display_result; ?></div>
                    <?php if($result_recorded_time): ?>
                        <div style="font-size: 0.75rem; color: #cc0000; font-weight: bold; margin-top: 5px;">
                            Result Time: <?php echo $result_recorded_time; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card-right">
                    <div>
                        <span class="status-open">Open</span>
                        <span class="time-display"><?php echo $current_date; ?></span>
                        <span class="time-display"><?php echo $open_time; ?></span>
                    </div>
                    <div>
                        <span class="status-close">Close</span>
                        <span class="time-display"><?php echo $current_date; ?></span>
                        <span class="time-display"><?php echo $close_time; ?></span>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    ?>
</div>

</body>
</html>