<?php
$secret = 'cgGHi73W83WmVwnQGqir';
$git_path = '/home/u405460257/domains/nexusedu.org/public_html/cybermediks_new'; // Path to your repository
$log_path = '/home/u405460257/domains/nexusedu.org/public_html/cybermediks_new/deploy.log'; // Log file


// Use $_SERVER to get the header (case-insensitive)
$hub_signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

$payload = file_get_contents('php://input');
$calculated_signature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (hash_equals($calculated_signature, $hub_signature)) {
    // Execute git pull
    exec("cd $git_path && git pull 2>&1", $output, $exit_code);
    
    
    echo "Deployment completed.";
} else {
    header("HTTP/1.1 403 Forbidden");
    echo "Invalid signature.";
}
?>
