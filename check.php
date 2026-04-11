<?php
/**
 * Sublicious — Temporary Diagnostic Script
 * DELETE THIS FILE after diagnosing the issue.
 */

// Basic security — only accessible locally or with a token
// Change 'sublicious_check' to something private if needed
if (($_GET['token'] ?? '') !== 'sublicious_check') {
    die('<h2>Access denied.</h2><p>Append <code>?token=sublicious_check</code> to the URL.</p>');
}

header('Content-Type: text/html; charset=utf-8');
echo '<style>body{font-family:monospace;padding:2rem;background:#f8fafc}h2{color:#ea580c}pre{background:#1e293b;color:#e2e8f0;padding:1rem;border-radius:.5rem;overflow-x:auto;white-space:pre-wrap}table{border-collapse:collapse;width:100%}td,th{padding:.4rem .75rem;border:1px solid #e5e7eb;text-align:left}th{background:#f1f5f9}.ok{color:#16a34a}.fail{color:#dc2626}</style>';

echo '<h1>Sublicious Diagnostics</h1>';

// ── PHP ──────────────────────────────────────────────────────────────────────
echo '<h2>PHP</h2>';
echo '<p>Version: <strong>' . PHP_VERSION . '</strong></p>';
$exts = ['pdo','pdo_mysql','mbstring','openssl','tokenizer','xml','json','curl','fileinfo','bcmath'];
echo '<table><tr><th>Extension</th><th>Status</th></tr>';
foreach ($exts as $e) {
    $ok = extension_loaded($e);
    echo "<tr><td>$e</td><td class='".($ok?'ok':'fail')."'>".($ok?'✓ loaded':'✗ missing')."</td></tr>";
}
echo '</table>';

// ── .env ─────────────────────────────────────────────────────────────────────
echo '<h2>.env</h2>';
$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    echo "<p class='fail'>❌ .env not found</p>";
} else {
    echo "<p class='ok'>✓ .env exists</p>";
    $env = file_get_contents($envPath);
    // Show non-sensitive keys
    foreach (explode("\n", $env) as $line) {
        $line = trim($line);
        if (!$line || str_starts_with($line, '#')) continue;
        [$key] = explode('=', $line, 2);
        $sensitive = in_array($key, ['APP_KEY','DB_PASSWORD','STRIPE_SECRET','SMS_API_KEY','VAPID_PRIVATE_KEY']);
        $display = $sensitive ? $key . '=[hidden]' : $line;
        if ($key === 'APP_KEY') {
            $val = substr($line, strlen('APP_KEY='));
            $display = 'APP_KEY=' . (strlen($val) > 5 ? '[SET, len='.strlen($val).']' : '<span class="fail">EMPTY!</span>');
        }
        echo "<code>$display</code><br>";
    }
}

// ── Storage writability ────────────────────────────────────────────────────────
echo '<h2>Storage writability</h2><table><tr><th>Path</th><th>Status</th></tr>';
$paths = ['.', 'storage', 'storage/logs', 'storage/framework/sessions',
          'storage/framework/views', 'storage/framework/cache', 'bootstrap/cache'];
foreach ($paths as $rel) {
    $abs = __DIR__ . '/' . $rel;
    $probe = $abs . '/.write_probe';
    $ok = @file_put_contents($probe, 'x') !== false;
    if ($ok) @unlink($probe);
    echo "<tr><td>$rel</td><td class='".($ok?'ok':'fail')."'>".($ok?'✓':'✗ NOT writable')."</td></tr>";
}
echo '</table>';

// ── Latest Laravel log ────────────────────────────────────────────────────────
echo '<h2>Latest Laravel errors (storage/logs/laravel.log)</h2>';
$log = __DIR__ . '/storage/logs/laravel.log';
if (!file_exists($log)) {
    echo "<p class='fail'>Log file not found (storage/logs/laravel.log)</p>";
} else {
    $lines = file($log);
    $last  = array_slice($lines, -80);
    echo '<pre>' . htmlspecialchars(implode('', $last)) . '</pre>';
}

echo '<hr><p style="color:#9ca3af;font-size:.8rem">⚠ Delete check.php after diagnosing.</p>';
