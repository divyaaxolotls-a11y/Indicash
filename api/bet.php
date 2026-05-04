<?php
include "con.php";

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// ─── INPUT SANITIZATION ───────────────────────────────────────────────────────
function safe($con, $val) {
    return mysqli_real_escape_string($con, trim($val));
}

$mobile   = safe($con, $_POST['mobile']   ?? '');
$session  = safe($con, $_POST['session']  ?? '');
$number   = safe($con, $_POST['number']   ?? '');
$amount   = safe($con, $_POST['amount']   ?? '');
$game     = safe($con, $_POST['game']     ?? '');
$bazar    = safe($con, $_POST['bazar']    ?? '');
$total    = (float)($_POST['total']       ?? 0);
$game_type = safe($con, $_POST['game_type'] ?? $game); // fallback to game if not provided
$types    = safe($con, $_POST['types']    ?? '');

// ─── TIMEZONE & TIME ──────────────────────────────────────────────────────────
date_default_timezone_set('Asia/Calcutta');
$stamp     = time();
$time      = date("H:i", $stamp);
$timingsss = $time;
$day       = strtoupper(date("l", $stamp));
$date      = date('d/m/Y');

// ─── SESSION CHECK ────────────────────────────────────────────────────────────
$auth_q = mysqli_query($con,
    "SELECT sn FROM users WHERE mobile='$mobile' AND session='$session' LIMIT 1"
);
if (mysqli_num_rows($auth_q) == 0) {
    $dd = mysqli_query($con, "SELECT session, active FROM users WHERE mobile='$mobile'");
    $d  = mysqli_fetch_array($dd);
    echo json_encode([
        'msg'     => 'You are not authorized to use this',
        'session' => $d['session'] ?? '',
        'active'  => $d['active']  ?? '',
    ]);
    return;
}

$dd = mysqli_query($con, "SELECT session, active FROM users WHERE mobile='$mobile'");
$d  = mysqli_fetch_array($dd);
$data['session'] = $d['session'];
$data['active']  = "1";

// ─── PARSE NUMBERS & AMOUNTS ──────────────────────────────────────────────────
$nm = explode(",", $number);
$am = explode(",", $amount);

// ─── BAZAR CLEANUP (for normal market lookup) ─────────────────────────────────
$_bazar = str_replace(["_OPEN", "_CLOSE", "_"], ["", "", " "], $bazar);

// ─── WALLET TOTAL CHECK (pre-flight) ──────────────────────────────────────────
$pre_check = mysqli_query($con,
    "SELECT wallet FROM users WHERE mobile='$mobile' AND wallet < '$total'"
);

// ══════════════════════════════════════════════════════════════════════════════
// STARLINE PATH  (timing param passed AND market exists in starline_markets)
// ══════════════════════════════════════════════════════════════════════════════
$is_starline = false;
if (isset($_POST['timing']) && $_POST['timing'] !== '') {
  $starline_check = mysqli_query($con,
    "SELECT * FROM starline_markets 
     WHERE LOWER(TRIM(name)) = LOWER(TRIM('$_bazar')) 
     LIMIT 1"
);
    
    // TEMP DEBUG - remove after testing
    $all_markets = mysqli_query($con, "SELECT name FROM starline_markets");
    $market_names = [];
    while($row = mysqli_fetch_assoc($all_markets)) {
        $market_names[] = $row['name'];
    }
    error_log("Bazar received: '$bazar' | Starline markets in DB: " . implode(", ", $market_names));
    error_log("Starline match count: " . mysqli_num_rows($starline_check));
    // END DEBUG

    if (mysqli_num_rows($starline_check) > 0) {
        $is_starline = true;
    }
}

if ($is_starline) {

    $timing_name = safe($con, $_POST['timing']);

    $get_timings = mysqli_query($con,
    "SELECT * FROM starline_timings
     WHERE LOWER(TRIM(market)) = LOWER(TRIM('$_bazar'))
       AND LOWER(TRIM(name)) = LOWER(TRIM('$timing_name'))
     LIMIT 1"
);
    $xc = mysqli_fetch_array($get_timings);

    if (!$xc) {
        echo json_encode(['success' => '0', 'msg' => 'Market timing not found']);
        return;
    }

    // ── Open/Close check ──────────────────────────────────────────────────────
   $mrk = mysqli_fetch_assoc(mysqli_query($con,
    "SELECT * FROM starline_markets 
     WHERE LOWER(TRIM(name)) = LOWER(TRIM('$_bazar')) 
     LIMIT 1"
));

    $market_open = "1";
    if (!empty($mrk['days']) && substr_count($mrk['days'], $day) > 0) {
        // Holiday / forced close day
        $market_open = "0";
    } else {
        // Rule: bet at 04:29 allowed when close=04:30 (strictly less than)
        // At exactly 04:30 → already closed
       
    }

    if ($market_open == "0") {
        echo json_encode(['success' => '0', 'msg' => 'Market Already Closed']);
        return;
    }

    // ── Pre-flight wallet check ───────────────────────────────────────────────
    if (mysqli_num_rows($pre_check) > 0) {
        echo json_encode(['success' => '0', 'msg' => "You don't have enough wallet balance"]);
        return;
    }

    // ── Loop bets ─────────────────────────────────────────────────────────────
    $msg  = "New bets game - $game, bazar - $bazar, user - $mobile, bets - ";
    $bets = [];

    for ($a = 0; $a < count($am); $a++) {
        $amoun = (float)$am[$a];
        $numbe = safe($con, $nm[$a]);

        // Minimum check BEFORE any deduction
        if ($amoun < 10) {
            echo json_encode(['success' => '0', 'msg' => 'Minimum bet amount is 10 INR']);
            return;
        }

        // ── Atomic wallet deduction ───────────────────────────────────────────
        $wallet_before_q = mysqli_fetch_assoc(mysqli_query($con,
            "SELECT wallet FROM users WHERE mobile='$mobile'"
        ));
        $wallet_before = (float)$wallet_before_q['wallet'];

        if ($wallet_before < $amoun) {
            echo json_encode(['success' => '0', 'msg' => "You don't have enough wallet balance to place this bet"]);
            return;
        }

        $wallet_after = $wallet_before - $amoun;

        mysqli_query($con,
            "UPDATE users SET wallet = wallet - $amoun
             WHERE mobile='$mobile' AND wallet >= $amoun"
        );
        if (mysqli_affected_rows($con) == 0) {
            echo json_encode(['success' => '0', 'msg' => 'Insufficient wallet balance (concurrent update)']);
            return;
        }

        // ── Get timing name ───────────────────────────────────────────────────
        $getName_q = mysqli_fetch_assoc(mysqli_query($con,
            "SELECT name FROM starline_timings
             WHERE market='$bazar' AND close='$timing' LIMIT 1"
        ));
        $timing_name = $getName_q['name'] ?? $bazar;

        // ── Insert starline_games ─────────────────────────────────────────────
      $timing_sn = $xc['sn'];

      mysqli_query($con,
         "INSERT INTO starline_games
          (user, game, bazar, date, number, amount, created_at, timing_sn)
          VALUES ('$mobile','$game','$_bazar','$date','$numbe','$amoun','$stamp','$timing_sn')"
     );

        // ── Insert transaction ────────────────────────────────────────────────
        mysqli_query($con,
            "INSERT INTO transactions (user, amount, type, remark, created_at, owner)
             VALUES ('$mobile','$amoun','0','Bet Placed on $game bazar $bazar on number $numbe','$stamp','$mobile')"
        );

        $msg .= "( Num-$numbe - {$amoun}INR ) ";

        $bets[] = [
            'amount' => $amoun,
            'number' => $numbe,
            'game'   => $game,
            'market' => $timing_name,
        ];
    }

    // ── Notification ──────────────────────────────────────────────────────────
    $msg_safe = mysqli_real_escape_string($con, $msg);
    mysqli_query($con,
        "INSERT INTO notifications (msg, created_at) VALUES ('$msg_safe','$stamp')"
    );

    // ── Log ───────────────────────────────────────────────────────────────────
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = mysqli_real_escape_string($con, $_SERVER['HTTP_USER_AGENT']);
    $remark     = mysqli_real_escape_string($con,
        "Starline Bet: $game | Bazar: $bazar | Numbers: $number | Amounts: $amount"
    );
    $insert_stmt = $con->prepare(
        "INSERT INTO login_logs (user_email, ip_address, user_agent, remark)
         VALUES (?, ?, ?, ?)"
    );
    $insert_stmt->bind_param("ssss", $mobile, $ip_address, $user_agent, $remark);
    $insert_stmt->execute();
    $insert_stmt->close();

    $data['success'] = "1";
    $data['bets']    = $bets;
    echo json_encode($data);
    return;
}

// ══════════════════════════════════════════════════════════════════════════════
// NORMAL MARKET PATH
// ══════════════════════════════════════════════════════════════════════════════

// ── Get market timing ─────────────────────────────────────────────────────────
$get_mrkt = mysqli_query($con, "SELECT * FROM gametime_new WHERE market='$_bazar'");

if (mysqli_num_rows($get_mrkt) == 0) {
    $get_mrkt = mysqli_query($con,
        "SELECT * FROM gametime_manual WHERE market='$_bazar' AND close>='$timingsss'"
    );
}
if (mysqli_num_rows($get_mrkt) == 0) {
    $get_mrkt = mysqli_query($con,
        "SELECT * FROM gametime_delhi WHERE market='$_bazar' AND close>='$timingsss'"
    );
}
if (mysqli_num_rows($get_mrkt) == 0) {
    echo json_encode([
        'success' => '0',
        'msg'     => 'We are not able to get market details, Please restart application and try again',
    ]);
    return;
}

$xc = mysqli_fetch_array($get_mrkt);

// ── Determine is_open / is_close ──────────────────────────────────────────────
if ($xc['days'] == "ALL" || substr_count($xc['days'], $day) == 0) {

    $xc['is_open']  = (strtotime($time) < strtotime($xc['open']))  ? "1" : "0";
    $xc['is_close'] = (strtotime($time) < strtotime($xc['close'])) ? "1" : "0";

} else if (substr_count($xc['days'], $day . "(CLOSE)") > 0) {

    echo json_encode(['success' => '0', 'msg' => 'Market already closed, Try again later']);
    return;

} else {
    // Day-specific timings e.g. "MONDAY(10:00-12:00)"
    $time_array = explode(",", $xc['days']);
    $day_conf   = '';
    foreach ($time_array as $t) {
        if (substr_count($t, $day) > 0) {
            $day_conf = $t;
            break;
        }
    }
    $day_conf = str_replace([$day . "(", ")"], ["", ""], $day_conf);
    $mrk_time = explode("-", $day_conf);

    $xc['open']     = $mrk_time[0] ?? $xc['open'];
    $xc['close']    = $mrk_time[1] ?? $xc['close'];
    $xc['is_open']  = (strtotime($time) < strtotime($xc['open']))  ? "1" : "0";
    $xc['is_close'] = (strtotime($time) < strtotime($xc['close'])) ? "1" : "0";
}

// ── Pre-flight wallet check ───────────────────────────────────────────────────
if (mysqli_num_rows($pre_check) > 0) {
    echo json_encode(['success' => '0', 'msg' => "You don't have enough wallet balance"]);
    return;
}

// ── Loop bets ─────────────────────────────────────────────────────────────────
$msg = "New bets game - $game, user - $mobile, bets - ";

for ($a = 0; $a < count($am); $a++) {
    $amoun = (float)$am[$a];
    $numbe = safe($con, $nm[$a]);

    // ── Determine bazar2 (OPEN / CLOSE suffix) ────────────────────────────────
    // For jodi/sangam: use bazar as-is (no suffix needed)
    // For others: suffix already baked into $bazar from client (e.g. MILAN_NIGHT_OPEN)
    // If client sends bare market name, auto-detect from time
    if ($game == "jodi" || $game == "halfsangam" || $game == "fullsangam") {
        $bazar2 = $bazar; // No OPEN/CLOSE suffix for these game types
    } else {
        // If bazar already has _OPEN or _CLOSE, use as-is; otherwise auto-detect
        if (strpos($bazar, '_OPEN') !== false || strpos($bazar, '_CLOSE') !== false) {
            $bazar2 = $bazar;
        } else {
            // Rule:
            //   time < open  → OPEN bets still accepted
            //   open <= time < close → CLOSE bets accepted
            //   time >= close → market fully closed (handled by is_close gate below)
            // e.g. open=12:00, close=16:30
            //   at 11:59 → OPEN, at 12:00 → CLOSE, at 16:29 → CLOSE, at 16:30 → rejected
            if (strtotime($time) < strtotime($xc['open'])) {
                $bazar2 = $bazar . "_OPEN";
            } else {
                $bazar2 = $bazar . "_CLOSE";
            }
        }
    }
    $bazar2 = str_replace(' ', '_', $bazar2);

    // ── Minimum check BEFORE deduction ───────────────────────────────────────
    if ($amoun < 5) {
        echo json_encode(['success' => '0', 'msg' => 'Minimum bet amount is 5 INR']);
        return;
    }

    // ── Open/Close gate ───────────────────────────────────────────────────────
    if (strpos($bazar2, 'OPEN') !== false) {

        if ($xc['is_open'] == "0") {
            echo json_encode(['success' => '0', 'msg' => 'Market already closed, Try again later']);
            return;
        }
        $chk = mysqli_query($con,
            "SELECT * FROM manual_market_results WHERE market='$_bazar' AND date='$date'"
        );
        if (mysqli_num_rows($chk) > 0) {
            echo json_encode(['success' => '0', 'msg' => 'Market already closed, Try again later']);
            return;
        }

    } else if (strpos($bazar2, 'CLOSE') !== false) {

        if ($xc['is_close'] == "0") {
            echo json_encode(['success' => '0', 'msg' => 'Market already closed, Try again later']);
            return;
        }
        $chk = mysqli_query($con,
            "SELECT * FROM manual_market_results WHERE market='$_bazar' AND date='$date'"
        );
        if (mysqli_num_rows($chk) > 0) {
            $chk_row = mysqli_fetch_array($chk);
            if ($chk_row['close'] != '') {
                echo json_encode(['success' => '0', 'msg' => 'Market already closed, Try again later']);
                return;
            }
        }

    } else if ($game == "jodi" || $game == "halfsangam" || $game == "fullsangam") {

        if ($xc['is_open'] == "0") {
            echo json_encode(['success' => '0', 'msg' => 'Market already closed, Try again later']);
            return;
        }
        $chk = mysqli_query($con,
            "SELECT * FROM manual_market_results WHERE market='$_bazar' AND date='$date'"
        );
        if (mysqli_num_rows($chk) > 0) {
            echo json_encode(['success' => '0', 'msg' => 'Market already closed, Try again later']);
            return;
        }
    }

    // ── Atomic wallet deduction ───────────────────────────────────────────────
    $wallet_row    = mysqli_fetch_assoc(mysqli_query($con,
        "SELECT wallet FROM users WHERE mobile='$mobile'"
    ));
    $wallet_before = (float)$wallet_row['wallet'];

    if ($wallet_before < $amoun) {
        echo json_encode(['success' => '0', 'msg' => "You don't have enough wallet balance"]);
        return;
    }

    mysqli_query($con,
        "UPDATE users SET wallet = wallet - $amoun
         WHERE mobile='$mobile' AND wallet >= $amoun"
    );
    if (mysqli_affected_rows($con) == 0) {
        echo json_encode(['success' => '0', 'msg' => 'Insufficient wallet balance (concurrent update)']);
        return;
    }

    $wallet_after = $wallet_before - $amoun;

    // ── Insert transaction ────────────────────────────────────────────────────
    // $t_remark = mysqli_real_escape_string($con,
    //     "Bet Placed on $game market name $bazar2 on number $numbe"
    // );
     $session_label = "Open"; 
    if (strpos($bazar2, '_CLOSE') !== false) {
        $session_label = "Close";
    } else if ($game == "jodi" || $game == "halfsangam" || $game == "fullsangam") {
        $session_label = "Open-Close";
    }
    
    // Create the remark using $bazar2 (which includes _OPEN/_CLOSE)
    $t_remark_text = "Game:$game | Market:$bazar2 | Session:$session_label | Number:$numbe | Bet Placed";
    $t_remark = mysqli_real_escape_string($con, $t_remark_text);
    mysqli_query($con,
        "INSERT INTO transactions (user, amount,wallet_before,wallet_after, type, remark, created_at, owner)
         VALUES ('$mobile','$amoun','$wallet_before','$wallet_after','0','$t_remark','$stamp','$mobile')"
    );

    // ── Insert games ──────────────────────────────────────────────────────────
    // bazar2 is always set here — guaranteed to be non-empty
    mysqli_query($con,
        "INSERT INTO games
         (user, game, bazar, date, game_type, number, amount, created_at, wallet_before, wallet_after)
         VALUES
         ('$mobile','$game','$bazar2','$date','$game_type','$numbe','$amoun','$stamp','$wallet_before','$wallet_after')"
    );

    $msg .= "( Market - $bazar2 , Num-$numbe - {$amoun}INR ) ";
    error_log("Game: $game | Market: $bazar2 | Number: $numbe | Amount: $amoun");
}

// ── Notification ──────────────────────────────────────────────────────────────
$msg_safe = mysqli_real_escape_string($con, $msg);
mysqli_query($con,
    "INSERT INTO notifications (msg, created_at) VALUES ('$msg_safe','$stamp')"
);

// ── Log ───────────────────────────────────────────────────────────────────────
$ip_address = $_SERVER['REMOTE_ADDR'];
$user_agent = mysqli_real_escape_string($con, $_SERVER['HTTP_USER_AGENT']);
$remark     = mysqli_real_escape_string($con,
    "Bet Placed on $game | Number: $number | Amount: $amount"
);
$insert_stmt = $con->prepare(
    "INSERT INTO login_logs (user_email, ip_address, user_agent, remark)
     VALUES (?, ?, ?, ?)"
);
$insert_stmt->bind_param("ssss", $mobile, $ip_address, $user_agent, $remark);
$insert_stmt->execute();
$insert_stmt->close();

$data['success'] = "1";
$data['msg']     = "1";
$data['type']    = $types;
echo json_encode($data);



