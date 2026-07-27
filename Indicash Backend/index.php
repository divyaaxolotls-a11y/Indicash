<?php
// PHP LOGIC (Keeping your existing counter/db logic)
$counterFile = 'counter.txt';
if (!file_exists($counterFile)) { file_put_contents($counterFile, '0'); }
$count = (int)file_get_contents($counterFile);
$count++;
file_put_contents($counterFile, $count);

// Database Connection Placeholder
  $servername = "localhost";
$username = "apluscrm_mtkkdb";
$password = "&RNDrt3LA3sF";
    $dbname = "apluscrm_mtkdb";
                              
$conn = mysqli_connect($servername, $username, $password, $dbname);
$downloadCount = 12540; // Default fallback
if ($conn) {
    $countQuery = "SELECT app_download FROM admin WHERE sn = 1";
    $countResult = mysqli_query($conn, $countQuery);
    if ($countResult) { $row = mysqli_fetch_assoc($countResult); $downloadCount = $row['app_download']; }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indicash Mtk | Ultimate Online Matka Experience</title>
    
    <!-- Google Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #00d2ff;
            --secondary-color: #3a7bd5;
            --dark-bg: #0f172a;
            --card-bg: rgba(255, 255, 255, 0.05);
            --accent-gold: #fbbf24;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--dark-bg);
            color: #ffffff;
            margin: 0;
            overflow-x: hidden;
        }

        /* Creative Background */
        .bg-glow {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 50% -20%, #3a7bd544, transparent);
            z-index: -1;
        }

        /* Navbar */
        .navbar {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        /* Hero Section */
        .hero-section {
            padding: 120px 0 60px;
            text-align: center;
        }

        .app-icon {
            width: 120px;
            height: 120px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            margin-bottom: 20px;
            border: 2px solid rgba(255,255,255,0.1);
        }

        h1 {
            font-weight: 700;
            background: linear-gradient(to right, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2.5rem;
        }

        .stats-badge {
            background: var(--card-bg);
            padding: 10px 20px;
            border-radius: 50px;
            display: inline-flex;
            gap: 15px;
            border: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 30px;
        }

        /* Download Button Animation */
        .btn-download {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 18px 45px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(0, 210, 255, 0.3);
            border: none;
            font-size: 1.2rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(0, 210, 255, 0.7); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(0, 210, 255, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(0, 210, 255, 0); }
        }

        /* Screen Slider */
        .screen-mockup {
            max-width: 280px;
            border: 8px solid #1e293b;
            border-radius: 30px;
            margin: 40px auto;
            display: block;
            box-shadow: 0 50px 100px rgba(0,0,0,0.5);
        }

        /* Features Section */
        .feature-card {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.05);
            height: 100%;
            transition: 0.3s;
        }

        .feature-card:hover {
            background: rgba(255,255,255,0.08);
            transform: translateY(-5px);
        }

        .icon-circle {
            width: 60px; height: 60px;
            background: linear-gradient(45deg, #3a7bd5, #00d2ff);
            border-radius: 15px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
            font-size: 24px;
        }

        /* Mobile Floating Button */
        .mobile-cta {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            z-index: 1000;
            display: none;
        }

        @media (max-width: 768px) {
            .mobile-cta { display: block; }
            .hero-section { padding-top: 80px; }
            h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

<div class="bg-glow"></div>

<!-- Navbar -->
<nav class="navbar navbar-dark fixed-top">
    <div class="container justify-content-center">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="img/dm.png" alt="Logo" width="40" class="me-2">
            <span class="fw-bold">INDICASH MTK</span>
        </a>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section container">
    <img src="img/dm.png" alt="Indicash App Icon" class="app-icon">
    <h1>Experience the Future of <br><span style="color: var(--primary-color)">Matka Gaming</span></h1>
    
    <div class="stats-badge mt-4">
        <span><i class="fas fa-star text-warning"></i> 4.9 Rating</span>
        <span><i class="fas fa-download text-info"></i> <?php echo number_format($downloadCount); ?>+ Users</span>
    </div>

    <div class="d-block mb-5">
        <a href="app/Dmbossonline.apk" class="btn-download" onclick="trackDownload()">
            <i class="fab fa-android me-2"></i> DOWNLOAD APP
        </a>
        <p class="mt-3 text-secondary">v4.2 | Secure & Encrypted | 6.0 MB</p>
    </div>

    <!-- App Screenshots Row -->
    <div class="row g-4 mt-5">
        <div class="col-6 col-md-3">
            <img src="img/Screen1.jpg" class="screen-mockup img-fluid" alt="App Screen">
        </div>
        <div class="col-6 col-md-3">
            <img src="img/Screen2.jpg" class="screen-mockup img-fluid" alt="App Screen">
        </div>
        <div class="col-md-3 d-none d-md-block">
            <img src="img/Screen3.jpg" class="screen-mockup img-fluid" alt="App Screen">
        </div>
        <div class="col-md-3 d-none d-md-block">
            <img src="img/Screen4.jpg" class="screen-mockup img-fluid" alt="App Screen">
        </div>
    </div>
</section>

<!-- Features -->
<section class="container py-5">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="feature-card">
                <div class="icon-circle"><i class="fas fa-bolt"></i></div>
                <h4>Fastest Results</h4>
                <p class="text-secondary">Get live updates for Kalyan, Time Bazaar, and Milan instantly. No more waiting.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card">
                <div class="icon-circle"><i class="fas fa-headset"></i></div>
                <h4>24/7 Support</h4>
                <p class="text-secondary">Dedicated WhatsApp support to help you with registration and queries anytime.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card">
                <div class="icon-circle"><i class="fas fa-shield-halved"></i></div>
                <h4>100% Secure</h4>
                <p class="text-secondary">End-to-end encryption ensures your data and transactions are always safe.</p>
            </div>
        </div>
    </div>
</section>

<!-- Reviews Section -->
<section class="container py-5 mb-5">
    <h3 class="text-center mb-5">What Players Say</h3>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="feature-card">
                <div class="d-flex justify-content-between mb-3">
                    <span class="fw-bold">Rahul Sharma</span>
                    <div class="text-warning"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                </div>
                <p class="text-secondary">"Best app for matka. The results are very fast and the interface is very easy to use."</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="feature-card">
                <div class="d-flex justify-content-between mb-3">
                    <span class="fw-bold">Amit V.</span>
                    <div class="text-warning"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                </div>
                <p class="text-secondary">"Trustworthy app. I have tried many apps but Indicash is the most reliable one."</p>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="container py-5 border-top border-secondary text-center text-secondary">
    <p>© 2024 Indicash Mtk App. For Entertainment Purposes Only.</p>
    <div class="d-flex justify-content-center gap-3">
        <a href="#" class="text-secondary">Privacy Policy</a>
        <a href="#" class="text-secondary">Terms of Service</a>
    </div>
</footer>

<!-- Mobile Floating CTA -->
<div class="mobile-cta">
    <a href="app/Dmbossonline.apk" class="btn-download w-100 text-center">
        <i class="fab fa-android me-2"></i> INSTALL NOW
    </a>
</div>

<script>
    function trackDownload() {
        // Trigger your existing PHP update logic via AJAX
        fetch('?update_count=true')
            .then(response => console.log('Count Updated'))
            .catch(error => console.error('Error:', error));
    }
</script>

</body>
</html>