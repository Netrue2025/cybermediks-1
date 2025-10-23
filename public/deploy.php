<?php
// === CONFIG ===
$secret   = 'cgGHi73W83WmVwnQGqir'; // GitHub Webhook "Secret"
$git_path = '/home/u405460257/domains/nexusedu.org/public_html/cybermediks_new';
$log_path = '/home/u405460257/domains/nexusedu.org/public_html/cybermediks_new/deploy.log';

// Branch to deploy (or set to null to accept all)
$required_ref = 'refs/heads/main';

// Fallback: a flag file that cron will watch for
$flag_file = $git_path . '/.deploy.request';

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
// Check if a function is effectively disabled by config
function func_unavailable($fn)
{
    if (!function_exists($fn)) return true;
    $disabled = array_map('trim', explode(',', (string)ini_get('disable_functions')));
    return in_array($fn, $disabled, true);
}

try {
    $raw = file_get_contents('php://input') ?: ($_POST['payload'] ?? '');
    $hub_sig = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
    if (!$hub_sig || strpos($hub_sig, 'sha256=') !== 0) {
        logx('Missing/invalid X-Hub-Signature-256 header');
        respond(400, 'Missing/invalid signature');
    }

    $calc = 'sha256=' . hash_hmac('sha256', $raw, $secret);
    if (!hash_equals($calc, $hub_sig)) {
        logx('Signature mismatch');
        respond(403, 'Invalid signature');
    }

    $event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? 'unknown';
    logx("Event: $event");
    if ($event === 'ping') {
        logx('Ping OK');
        respond(200, 'pong');
    }
    if ($event !== 'push') {
        logx("Ignoring event: $event");
        respond(200, "Ignored $event");
    }

    $payload = json_decode($raw, true) ?: [];
    $ref = $payload['ref'] ?? '';
    logx("Push ref: $ref");

    if ($required_ref && $ref !== $required_ref) {
        logx("Ref not allowed; required=$required_ref, got=$ref");
        respond(200, "Ignoring push to $ref");
    }

    // Detect availability of shell functions
    $disabled_list = ini_get('disable_functions');
    $exec_disabled = func_unavailable('exec');
    $shell_disabled = func_unavailable('shell_exec');
    $proc_disabled = func_unavailable('proc_open');

    logx("disable_functions=$disabled_list");
    logx("exec_disabled=" . ($exec_disabled ? 'yes' : 'no') . ", shell_exec_disabled=" . ($shell_disabled ? 'yes' : 'no') . ", proc_open_disabled=" . ($proc_disabled ? 'yes' : 'no'));

    if ($exec_disabled && $shell_disabled && $proc_disabled) {
        // FALLBACK: trigger a cron-based deploy by touching a flag file
        if (@file_put_contents($flag_file, date('c')) === false) {
            logx("Failed to write flag file: $flag_file");
            respond(500, 'Cannot write deploy flag; check permissions');
        }
        logx("Created deploy flag: $flag_file (cron will handle git pull)");
        respond(200, 'Deploy queued (cron will run git pull).');
    }

    // If at least one is available, try exec first
    putenv('HOME=/home/u405460257');
    putenv('PATH=/usr/local/bin:/usr/bin:/bin');
    $git_bin = file_exists('/usr/bin/git') ? '/usr/bin/git' : '/usr/local/bin/git';
    $cmd = 'cd ' . escapeshellarg($git_path) . ' && ' . escapeshellarg($git_bin) . ' pull 2>&1';
    logx("Running: $cmd");

    $output = [];
    $exit_code = 0;
    if (!$exec_disabled) {
        exec($cmd, $output, $exit_code);
    } elseif (!$shell_disabled) {
        $out = shell_exec($cmd);
        $output = explode("\n", trim((string)$out));
        $exit_code = 0; // shell_exec has no exit code; assume 0 if output exists
    } else {
        // As a last resort, try proc_open
        if ($proc_disabled) {
            logx("No shell function available; writing flag file instead.");
            @file_put_contents($flag_file, date('c'));
            respond(200, 'Deploy queued (cron will run git pull).');
        }
        $descriptorspec = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $proc = proc_open($cmd, $descriptorspec, $pipes);
        if (is_resource($proc)) {
            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            $exit_code = proc_close($proc);
            $output = array_filter(explode("\n", trim($stdout . "\n" . $stderr)));
        } else {
            logx("proc_open failed; writing flag file instead.");
            @file_put_contents($flag_file, date('c'));
            respond(200, 'Deploy queued (cron will run git pull).');
        }
    }

    logx("Exit: $exit_code");
    if (!empty($output)) logx("Output:\n" . implode("\n", $output));
    if ($exit_code !== 0) respond(500, 'git pull failed — check deploy.log');

    respond(200, 'Deployment completed.');
} catch (Throwable $e) {
    logx('Fatal: ' . $e->getMessage());
    respond(500, 'Server error');
}
