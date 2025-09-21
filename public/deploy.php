<?php
// === CONFIG ===
$secret   = 'cgGHi73W83WmVwnQGqir'; // must match GitHub Webhook "Secret"
$git_path = '/home/u405460257/domains/nexusedu.org/public_html/cybermediks_new';
$log_path = '/home/u405460257/domains/nexusedu.org/public_html/cybermediks_new/deploy.log';

// If git isn't at /usr/bin/git on your host, try /usr/local/bin/git instead.
$git_bin  = file_exists('/usr/bin/git') ? '/usr/bin/git' : '/usr/local/bin/git';

// (Optional) only deploy on pushes to this ref. Set to null to accept all.
$required_ref = 'refs/heads/main'; // change to 'refs/heads/master' or your branch, or set to null

// === UTIL ===
function logx($msg)
{
    global $log_path;
    @file_put_contents($log_path, '[' . date('c') . "] $msg\n", FILE_APPEND | LOCK_EX);
}
function respond($code, $msg)
{
    http_response_code($code);
    echo $msg;
    exit;
}

try {
    // Read raw payload (supports both JSON and legacy form-encoded)
    $raw = file_get_contents('php://input');
    if (!$raw && isset($_POST['payload'])) {
        $raw = $_POST['payload'];
    }

    // Verify signature (X-Hub-Signature-256)
    $hub_sig = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
    if (!$hub_sig) {
        logx('Missing X-Hub-Signature-256 header');
        respond(400, 'Missing signature');
    }
    if (strpos($hub_sig, 'sha256=') !== 0) {
        logx("Bad signature format: $hub_sig");
        respond(400, 'Bad signature format');
    }
    $calc = 'sha256=' . hash_hmac('sha256', $raw, $secret);
    if (!hash_equals($calc, $hub_sig)) {
        logx('Signature mismatch');
        respond(403, 'Invalid signature');
    }

    // Parse event and ref
    $event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? 'unknown';
    logx("Event: $event");

    if ($event === 'ping') {
        logx('Ping OK');
        respond(200, 'pong');
    }
    if ($event !== 'push') {
        logx("Ignoring non-push event: $event");
        respond(200, "Ignored $event");
    }

    $payload = json_decode($raw, true);
    $ref = $payload['ref'] ?? '';
    logx("Push ref: $ref");

    if ($required_ref && $ref !== $required_ref) {
        logx("Ref not allowed; required=$required_ref, got=$ref");
        respond(200, "Ignoring push to $ref");
    }

    // Ensure environment for git/ssh on shared hosting
    // HOME lets git/ssh find known_hosts and deploy key under /home/<user>/.ssh
    putenv('HOME=/home/u405460257');
    putenv('PATH=/usr/local/bin:/usr/bin:/bin');

    // Do the pull (escapeshellarg to avoid path issues)
    $cmd = 'cd ' . escapeshellarg($git_path) . ' && ' . escapeshellarg($git_bin) . ' pull 2>&1';
    logx("Running: $cmd");

    $output = [];
    $exit_code = 0;
    exec($cmd, $output, $exit_code);

    logx("Exit: $exit_code");
    if (!empty($output)) {
        logx("Output:\n" . implode("\n", $output));
    }

    if ($exit_code !== 0) {
        respond(500, 'git pull failed — check deploy.log');
    }

    // (Optional) post-deploy steps — uncomment as needed:
    // $php_bin = '/usr/bin/php'; // adjust if necessary
    // $artisan = 'cd ' . escapeshellarg($git_path) . " && $php_bin artisan config:cache && $php_bin artisan route:cache 2>&1";
    // exec($artisan, $aout, $aexit); logx("Artisan exit=$aexit\n".implode("\n", $aout));

    respond(200, 'Deployment completed.');
} catch (Throwable $e) {
    logx('Fatal: ' . $e->getMessage());
    respond(500, 'Server error');
}
