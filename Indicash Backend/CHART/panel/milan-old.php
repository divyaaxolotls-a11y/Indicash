<!DOCTYPE html>
<html amp lang="en-in">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>
      <?php
         $marketParam = isset($_GET['market']) ? $_GET['market'] : '';
         echo htmlspecialchars($marketParam);
         ?>  Panel Chart | <?php echo htmlspecialchars($marketParam); ?> Panel Record</title>
      <meta name="robots" content="follow, all">
    
      <link rel="shortcut icon" href="../fav/favicon.ico">
      <style amp-custom>html {
         scroll-behavior: smooth
         }
         body {
         background-color: #fff;
         text-align: center;
         font-weight: 700;
         padding: 0;
         font-family: Helvetica, sans-serif
         }
         .span {
         margin-right: 5px;
         }
         .logo {
         background: #fc9;
         padding: 0 10px;
         display: block;
         color: #fff8f8;
         margin-bottom: 5px;
         letter-spacing: 1px;
         font-weight: 700;
         border: 3px solid #ff0016;
         border-radius: .75em;
         transform-style: preserve-3d;
         transition: transform 150ms cubic-bezier(0, 0, .58, 1), background 150ms cubic-bezier(0, 0, .58, 1)
         }
         .logo amp-img {
         width: 220px;
         height: auto;
         padding: 6px 0 0
         }
         .button2 {
         background-color: #a0d5ff;
         color: #220c82;
         padding: 10px 30px;
         font-size: 14px;
         margin: 0 0 5px 0;
         border: 2px solid #0000005c;
         font-weight: 800;
         text-decoration: none;
         text-shadow: 1px 1px #00bcd4;
         box-shadow: 0 8px 10px 0 rgba(0, 0, 0, .2), 0 6px 8px 0 rgba(0, 0, 0, .19);
         display: inline-block;
         transition: all .3s
         }
         .ad-div11 {
         text-align: center;
         margin: 0 0 10px
         }
         .chart-list {
         border: 2px solid #eb008b;
         margin-bottom: 2px;
         width: 50%;
         margin: 0 auto 10px;
         text-align: center;
         font-weight: 600
         }
         .chart-list.ab1 {
         border-color: #003c6c
         }
         .chart-list h4 {
         color: #fff;
         padding: 5px 10px 3px;
         font-size: 24px;
         border-top-left-radius: 7px;
         margin: 0
         }
         .chart-list.ab1 h4 {
         background-color: #024c88
         }
         .chart-list a {
         display: block;
         font-size: 22px;
         padding: 5px 7px 4px;
         text-decoration: none
         }
         .chart-list a:hover {
         text-decoration: none
         }
         .chart-list.ab1 a {
         border-bottom: 2px solid #024c88;
         color: #003c6c
         }
         .chart-list a:hover {
         background-color: #fff;
         text-decoration: underline
         }
         @media only screen and (max-width:500px) {
         .chart-list {
         width: 95%
         }
         }
         footer {
         background-color: #fff;
         color: red;
         font-weight: bold;
         font-size: 25px;
         text-decoration: none;
         border: 4px groove purple;
         text-shadow: 1px 1px gold;
         margin: 3px
         }
         footer>div {
         border-bottom: 2px solid #b2ddff;
         padding: 10px 0;
         margin-bottom: 10px
         }
         footer>div a {
         text-decoration: none
         }
         footer>div a:hover {
         text-decoration: none
         }
         footer .ftr-icon {
         text-decoration: none;
         font-size: 20px;
         text-transform: uppercase;
         color: #007bff
         }
         footer p {
         margin: 10px 0 10px;
         line-height: 35px
         }
         footer p span {
         color: #36f
         }
         .panel.panel-info {
         border: 1px solid #3f51b5;
         width: 100%;
         margin: 0 auto 0
         }
         .panel-heading h1 {
         margin: 0;
         padding: 5px
         }
        
table{border-collapse:collapse}
table,th,td{border:1px solid #000}thead{background-color:#ffc107;text-shadow:1px 1px 2px #9a7400ab}tbody td{padding:5px 0;font-size:18px}

         .red-color {
         color: red;
         }
         @media only screen and (max-width:770px) {
         .panel.panel-info {
         width: 95%
         }
         }
         .r {
         color: red
         }
         tr td:nth-child(1) {
         font-size: 13px
         }
         tbody tr td:nth-child(27)
         tbody tr td:nth-child(2),
         tbody tr td:nth-child(5),
         tbody tr td:nth-child(8),
         tbody tr td:nth-child(11),
         tbody tr td:nth-child(14),
         tbody tr td:nth-child(17),
         tbody tr td:nth-child(20),
         tbody tr td:nth-child(23) {
         border-right-width: 1px;
         font-size: 15px
         }
         tbody tr td:nth-child(3),
         tbody tr td:nth-child(6),
         tbody tr td:nth-child(9),
         tbody tr td:nth-child(12),
         tbody tr td:nth-child(15),
         tbody tr td:nth-child(18),
         tbody tr td:nth-child(21),
         tbody tr td:nth-child(24),
         {
         border-left-width: 0;
         border-right-width: 0;
         font-size: 23px
         }
         tbody tr td:nth-child(4),
         tbody tr td:nth-child(7),
         tbody tr td:nth-child(10),
         tbody tr td:nth-child(13),
         tbody tr td:nth-child(16),
         tbody tr td:nth-child(19),
         tbody tr td:nth-child(22),
         tbody tr td:nth-child(25),
         tbody tr td:nth-child(28) {
         border-left-width: 0;
         font-size: 15px
         }
         @media only screen and (max-width:500px) {
         .panel.panel-info {
         width: 99%
         }
         tbody tr td:nth-child(6),
         tbody tr td:nth-child(9),
         tbody tr td:nth-child(12),
         tbody tr td:nth-child(15),
         tbody tr td:nth-child(18),
         tbody tr td:nth-child(21),
         tbody tr td:nth-child(24),
         tbody tr td:nth-child(27) {
         font-size: 9px
         }
         tr td:nth-child(1),
         tbody tr td:nth-child(3),
         tbody tr td:nth-child(2),
         tbody tr td:nth-child(5),
         tbody tr td:nth-child(8),
         tbody tr td:nth-child(11),
         tbody tr td:nth-child(14),
         tbody tr td:nth-child(17),
         tbody tr td:nth-child(20),
         tbody tr td:nth-child(23),
         tbody tr td:nth-child(4),
         tbody tr td:nth-child(7),
         tbody tr td:nth-child(10),
         tbody tr td:nth-child(13),
         tbody tr td:nth-child(16),
         tbody tr td:nth-child(19),
         tbody tr td:nth-child(22),
         tbody tr td:nth-child(25),
         tbody tr td:nth-child(28) {
         font-size: 9px
         }
         th {
         font-size: 15px
         }
         }
         thead tr th:nth-child(1) {
         width: 100px
         }
         @media only screen and (max-width:770px) {
         tr td:nth-child(1) {
         font-size: 9px
         }
         tbody tr td:nth-child(6),
         tbody tr td:nth-child(9),
         tbody tr td:nth-child(12),
         tbody tr td:nth-child(15),
         tbody tr td:nth-child(18),
         tbody tr td:nth-child(21),
         tbody tr td:nth-child(24),
         tbody tr td:nth-child(27) {
         border-left-width: 0;
         border-right-width: 0;
         font-size: 9px
         }
         tbody tr td:nth-child(4),
         tbody tr td:nth-child(7),
         tbody tr td:nth-child(10),
         tbody tr td:nth-child(13),
         tbody tr td:nth-child(16),
         tbody tr td:nth-child(19),
         tbody tr td:nth-child(22),
         tbody tr td:nth-child(25),
         tbody tr td:nth-child(28) {
         border-left-width: 1px;
         font-size: 9px
         }
         tbody tr td:nth-child(2),
         tbody tr td:nth-child(5),
         tbody tr td:nth-child(8),
         tbody tr td:nth-child(11),
         tbody tr td:nth-child(14),
         tbody tr td:nth-child(17),
         tbody tr td:nth-child(20),
         tbody tr td:nth-child(23) {
         border-right-width: 1px;
         font-size: 9px
         }
         thead tr th:nth-child(1) {
         width: 55px
         }
         .panel.panel-info {
         width: 99%
         }
         }
         @media only screen and (max-width:770px) {
         .panel.panel-info {
         width: 99%
         }
         }
         @media only screen and (max-width:500px) {
         .chart-list {
         width: 95%
         }
         }
         .mp-btn {
         position: fixed;
         bottom: 9px;
         left: 5px;
         padding: 5px 8px;
         font-size: 15px;
         border: 1px solid #fff;
         text-decoration: none;
         background-color: #039;
         color: #fff
         }
         .nd td:nth-child(2),
         .nd td:nth-child(4),
         .nd td:nth-child(5),
         .nd td:nth-child(7),
         .nd td:nth-child(8),
         .nd td:nth-child(10),
         .nd td:nth-child(11),
         .nd td:nth-child(13),
         .nd td:nth-child(14),
         .nd td:nth-child(16),
         .nd td:nth-child(17),
         .nd td:nth-child(19),
         .nd td:nth-child(20),
         .nd td:nth-child(22),
         .nd td:nth-child(23),
         .nd td:nth-child(25),
         .nd td:nth-child(26),
         .nd td:nth-child(28),
         .nd td:nth-child(29) {
         writing-mode: vertical-rl;
         text-orientation: upright
         }
         .para3 {
         background: linear-gradient(187deg, #fc9 50%, #ffc387 50%);
         border: 2px solid #ff0016;
         border-top-style: solid;
         border-right-style: solid;
         border-bottom-style: solid;
         border-left-style: solid;
         border-style: outset;
         margin-bottom: 3px;
         line-height: 1.4;
         font-size: 14px;
         padding: 4px 10px;
         color: #00094d;
         text-shadow: 1px 1px 2px #fff;
         box-shadow: 0 0 20px 0 rgb(0 0 0 / 40%)
         }
         .chart-h1 {
         background: #ff00a2;
         padding: 5px 10px;
         text-shadow: 1px 1px 2px #000;
         display: block;
         color: #fff8f8;
         margin-bottom: 3px;
         letter-spacing: 1px;
         font-weight: 700;
         border: 2px solid #fff;
         transform-style: preserve-3d;
         transition: transform 150ms cubic-bezier(0, 0, .58, 1), background 150ms cubic-bezier(0, 0, .58, 1);
         font-size: 18px;
         margin: 3px 0
         }
         h1 {
         color: #fff8f8
         }
         .logo img {
         margin: 5px 0
         }
         h3 {
         font-size: 16px;
         color: #fff;
         text-shadow: 0 0;
         margin: 0;
         padding: 5px
         }
         .chart-result {
         margin: 6px 2px;
         line-height: 1.4;
         font-size: 14px;
         padding: 4px 10px;
         color: #00094d;
         text-shadow: 1px 1px 2px #fff;
         box-shadow: 0 0 20px 0 rgb(0 0 0 / 40%);
         border: 1px solid #000
         }
         .chart-result div {
         font-size: 22px;
         color: #00094d;
         text-shadow: 1px 1px 2px #fff
         }
         .chart-result span {
         color: #880e4f;
         text-shadow: 1px 1px 2px #ffe2c6;
         font-size: 21px
         }
         .chart-result a {
         border: 1px solid #e6e6e6;
         background: #522f92;
         color: #fff;
         padding: 5px 7px;
         font-size: 12px;
         margin: 2px 0 -1px;
         display: inline-block;
         transition: all .3s;
         cursor: pointer;
         text-shadow: none;
         text-decoration: none
         }
         @media screen and (max-width:400px) {
         .logo img {
         height: 60px;
         width: auto;
         max-width: 100%
         }
         }
         @media screen and (max-width:300px) {
         .logo img {
         height: 40px;
         width: auto;
         max-width: 100%
         }
         }
         
      </style>
      <head>
   <body>
      <div id="top"></div>
      <div class="panel panel-info">
         <div class="panel-heading text-center" style="background: #3f51b5;">
            <h3><?php echo htmlspecialchars($marketParam); ?> MATKA PANEL RECORD 2025 - 2026</h3>
         </div>
         <div class="panel-body">
            <table style="width: 100%; text-align:center;" class="panel-chart chart-table" cellpadding="2">
               <thead>
                  <tr>
                     <th>Date</th>
                     <th>Mon</th>
                     <th>Tue</th>
                     <th>Wed</th>
                     <th>Thu</th>
                     <th>Fri</th>
                     <th>Sat</th>
                     <th>Sun</th>
                  </tr>
               </thead>
               <tbody>
                  <?php
                     $marketParam = isset($_GET['market']) ? $_GET['market'] : '';
                    echo htmlspecialchars($marketParam);
                     $apiUrl = 'https://test.apluscrm.in/api/ax_pannel.php?market=' . urlencode($marketParam);
                     $apiData = json_decode(file_get_contents($apiUrl), true);
                     $startDate = '01/01/2024';
                     $rowCount = count($apiData) / 7;
                     ?>
               <tbody>
                  <?php for ($row = 0; $row < $rowCount; $row++): ?>
                  <tr>
                     <td>
                        <?php
                           echo $startDate . '<br> to <br>';
                           $endDateObj = DateTime::createFromFormat('d/m/Y', $startDate);
                           $endDateObj->modify('+6 days');
                           $endDate = $endDateObj->format('d/m/Y');
                           echo $endDate;
                           $startDateObj = DateTime::createFromFormat('d/m/Y', $endDate);
                           $startDateObj->modify('+1 day');
                           $startDate = $startDateObj->format('d/m/Y');
                           ?>
                     </td>
                     <?php for ($col = 0; $col < 7; $col++): ?>
                     <?php $index = $row * 7 + $col; ?>
                     <?php if (isset($apiData[$index])): ?>
                     <?php
                        $combinedValue = $apiData[$index]['open'] . $apiData[$index]['close'];
                        $redNumbers = ['16', '61', '66', '11', '22', '77', '27', '72', '33', '88', '38', '83', '44', '99', '49', '94', '55', '00', '50', '05'];
                        
                        $colorClass = in_array($combinedValue, $redNumbers, true) ? 'red-color' : '';
                        ?>
                     <td class="chart <?= $colorClass ?>">
                        <div style="display:flex ;  justify-content:space-between; align-items:center">
                           <p style="padding-left:4px; padding-right:4px;"><?php
                              $openPannaDigits = str_split(!empty($apiData[$index]['open_panna']) ? $apiData[$index]['open_panna'] : '***');
                              foreach ($openPannaDigits as $digit) {
                                  echo $digit . "<br>";
                              }
                              ?></p>
                           <p  style="font-size:14px; bold"> <?php
                              $openValue = !empty($apiData[$index]['open']) ? $apiData[$index]['open'] : '*';
                              if ($apiData[$index]['open'] === '0') {
                                  $openValue = 0;
                              }
                              
                              $closeValue = !empty($apiData[$index]['close']) ? $apiData[$index]['close'] : '*';
                              if ($apiData[$index]['close'] === '0') {
                                  $closeValue = 0;
                              }
                              echo $openValue . $closeValue . "<br>";
                              ?></p>
                           <p style="padding-left:4px; padding-right:4px;"><?php
                              $closePannaDigits = str_split(!empty($apiData[$index]['close_panna']) ? $apiData[$index]['close_panna'] : '***');
                              foreach ($closePannaDigits as $digit) {
                                  echo $digit . "<br>";
                              }
                              ?></p>
                        </div>
                     </td>
                     <?php else: ?>
                     <td></td>
                     <?php endif; ?>
                     <?php endfor; ?>
                  </tr>
                  <?php endfor; ?>
               </tbody>
               </tbody>
            </table>
         </div>
      </div>
      </div>
      </div>
      </div>
   </body>
</html>
