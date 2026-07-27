<?php
include('header.php');

function getAccessTokenss() {
    $keyFilePath = 'demodmboss.json'; // Path to your service account JSON file
    $json = file_get_contents($keyFilePath);
    $key = json_decode($json, true);

    $jwt = createJWTss($key);

    $url = 'https://oauth2.googleapis.com/token';
    $postData = [
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion'  => $jwt
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        echo "cURL Error: $error";
        return null;
    }

    curl_close($ch);

    $response = json_decode($result, true);

    if (isset($response['access_token'])) {
        return $response['access_token'];
    } else {
        echo "Error fetching access token: " . $response['error'];
        return null;
    }
}

function createJWTss($key) {
    $header = [
        'alg' => 'RS256',
        'typ' => 'JWT'
    ];

    $payload = [
        'iss' => $key['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => time() + 3600, // 1 hour expiration
        'iat' => time()
    ];

    $headerEncoded = base64url_encodess(json_encode($header));
    $payloadEncoded = base64url_encodess(json_encode($payload));
    $signatureInput = $headerEncoded . '.' . $payloadEncoded;

    $privateKey = $key['private_key'];
    $privateKey = openssl_pkey_get_private($privateKey);
    
    openssl_sign($signatureInput, $signature, $privateKey, 'sha256');
    $signatureEncoded = base64url_encodess($signature);

    return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
}

function base64url_encodess($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

if (in_array(16, $HiddenProducts)){

// CSRF Token validation and form submission
if (isset($_REQUEST['submit'])) {
    // CSRF token check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<script>alert('Invalid CSRF token. Please refresh the page and try again.');</script>";
    } else {
        // Extract user inputs
        extract($_REQUEST);

        // Sanitize inputs
        $title = htmlspecialchars(trim($title), ENT_QUOTES, 'UTF-8');
        $body = htmlspecialchars(trim($body), ENT_QUOTES, 'UTF-8');
        
        // Validate inputs
        if (empty($title) || empty($body)) {
            echo "<script>alert('Both Title and Body are required fields.');</script>";
        } elseif (strlen($title) > 255) { // Validate title length (max 255 characters)
            echo "<script>alert('Title is too long. Maximum length is 255 characters.');</script>";
        } elseif (strlen($body) > 2000) { // Validate body length (max 2000 characters)
            echo "<script>alert('Message is too long. Maximum length is 2000 characters.');</script>";
        } else {
            // Valid inputs: Proceed with sending the notification

            $projectId = 'dmbossdemo';

            // Get the access token
            $accessToken = getAccessTokenss();

            if ($accessToken) {
                $notification = array(
                    'title' => $title,
                    'body'  => $body,
                );

                $message = array(
                    'notification' => $notification,
                    'topic' => 'all', // Use 'token' for specific device tokens
                );

                $fields = array(
                    'message' => $message,
                );

                $headers = array(
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json',
                );

                // Send the notification via cURL
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                
                $result = curl_exec($ch);

                if (curl_errno($ch)) {
                    $error = curl_error($ch);
                    echo "cURL Error: $error";
                } else {
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $response = json_decode($result, true);

                    if ($httpCode == 200) {
                        echo "<script>alert('Notification sent successfully');</script>";
                    } else {
                        echo "<script>alert('Firebase Error: " . $response['error']['message'] . "');</script>";
                    }
                }

                curl_close($ch);
            } else {
                echo "<script>alert('Failed to retrieve access token');</script>";
            }
        }
    }
}


?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Send Notification</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Send Notification</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-danger">
                        Send Notification
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <?php if (isset($success)) { ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $success; ?>
                            </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-md-3"></div>
                            <div class="col-md-6">
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" class="form-control" name="title" placeholder="Enter Title" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Message</label>
                                        <textarea name="body" rows="5" class="form-control" required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" name="submit" class="btn btn-primary btn-block">Submit</button>
                                    </div>
                                </form>
                            </div>
                            <!-- /.col -->
                            <div class="col-md-3"></div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>
<!-- /.content -->

<?php 
}else{ 
echo "<script>
window.location.href = 'unauthorized.php';
</script>";
exit();
}
include('footer.php'); ?>

<script>
    $(function () {
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
    });
</script>
