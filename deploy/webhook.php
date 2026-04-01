<?php

declare(strict_types=1);

$scriptDir = __DIR__;
$envPath = $scriptDir . '/.env';

if (is_file($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

$secret = $_ENV['DEPLOY_WEBHOOK_SECRET'] ?? '';
$allowedBranches = array_filter(array_map('trim', explode(',', $_ENV['DEPLOY_ALLOWED_BRANCHES'] ?? 'production,develop')));
$defaultBranch = $_ENV['DEPLOY_DEFAULT_BRANCH'] ?? 'production';
$logPath = $_ENV['DEPLOY_WEBHOOK_LOG'] ?? ($scriptDir . '/logs/webhook.log');
$deployScript = $scriptDir . '/deploy.sh';

header('Content-Type: application/json');

$writeLog = static function (string $message) use ($logPath): void {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    @file_put_contents($logPath, $line, FILE_APPEND);
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

$rawPayload = file_get_contents('php://input') ?: '';
$signatureHeader = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';

if ($event !== 'push') {
    echo json_encode(['ok' => true, 'message' => 'Ignored event', 'event' => $event]);
    exit;
}

if ($secret === '') {
    $writeLog('Blocked: DEPLOY_WEBHOOK_SECRET is missing.');
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Server secret not configured']);
    exit;
}

if (!str_starts_with($signatureHeader, 'sha256=')) {
    $writeLog('Blocked: invalid signature header format.');
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Invalid signature header']);
    exit;
}

$sentSignature = substr($signatureHeader, 7);
$computedSignature = hash_hmac('sha256', $rawPayload, $secret);
if (!hash_equals($computedSignature, $sentSignature)) {
    $writeLog('Blocked: signature mismatch.');
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Invalid webhook signature']);
    exit;
}

$payload = json_decode($rawPayload, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Invalid JSON payload']);
    exit;
}

$ref = $payload['ref'] ?? '';
$branch = $defaultBranch;
if (is_string($ref) && str_starts_with($ref, 'refs/heads/')) {
    $branch = substr($ref, 11);
}

if (!in_array($branch, $allowedBranches, true)) {
    $writeLog('Ignored push for non-allowed branch: ' . $branch);
    echo json_encode(['ok' => true, 'message' => 'Ignored branch', 'branch' => $branch]);
    exit;
}

if (!is_file($deployScript) || !is_executable($deployScript)) {
    $writeLog('Blocked: deploy script missing or not executable.');
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Deploy script not executable']);
    exit;
}

$command = 'bash ' . escapeshellarg($deployScript) . ' ' . escapeshellarg($branch) . ' > /dev/null 2>&1 &';
exec($command);

$writeLog('Accepted deploy for branch=' . $branch);
echo json_encode(['ok' => true, 'message' => 'Deployment started', 'branch' => $branch]);
