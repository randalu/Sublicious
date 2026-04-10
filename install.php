<?php
/**
 * Sublicious — Browser-based Web Installer
 * ─────────────────────────────────────────
 * Place this file in the project root.
 * Access it at: https://yourdomain.com/install.php
 *
 * Works on shared hosting — no terminal required.
 * Requires: PHP 8.3+, PDO.
 * If vendor/ is missing the installer will attempt to run composer for you.
 *
 * IMPORTANT: Delete this file after installation!
 */

// ─── Security ───────────────────────────────────────────────────────────────
if (file_exists(__DIR__ . '/installed.lock')) {
    http_response_code(403);
    die(vendorPage_html('Already Installed',
        '<p>Sublicious is already installed. <a href="/">Go to the app &rarr;</a></p>
         <p style="margin-top:.75rem;font-size:.82rem;color:#9ca3af">Delete <code>installed.lock</code> to re-run the installer.</p>',
        '', false));
}

// ─── Handle missing vendor/ ──────────────────────────────────────────────────
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    handleVendorSetup();
    exit;
}

// ────────────────────────────────────────────────────────────────────────────
// Vendor-setup page (shown when composer dependencies are not installed yet)
// ────────────────────────────────────────────────────────────────────────────
function handleVendorSetup(): void
{
    $output   = '';
    $success  = false;
    $errorMsg = '';

    // Detect exec availability
    $disabledFns = array_map('trim', explode(',', (string) ini_get('disable_functions')));
    $execAvail   = function_exists('exec') && !in_array('exec', $disabledFns);
    $shellAvail  = function_exists('shell_exec') && !in_array('shell_exec', $disabledFns);

    // Find composer binary
    $composerBin = '';
    if ($execAvail) {
        foreach (['composer', 'composer.phar'] as $try) {
            exec("which $try 2>/dev/null", $out, $rc);
            if ($rc === 0 && !empty(trim($out[0] ?? ''))) {
                $composerBin = trim($out[0]);
                break;
            }
            $out = [];
        }
        // composer.phar in project root?
        if (!$composerBin && file_exists(__DIR__ . '/composer.phar')) {
            $composerBin = PHP_BINARY . ' ' . escapeshellarg(__DIR__ . '/composer.phar');
        }
    }

    // ── POST: download composer.phar ──────────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download_composer'])) {
        $phar = @file_get_contents('https://getcomposer.org/composer.phar');
        if ($phar !== false) {
            file_put_contents(__DIR__ . '/composer.phar', $phar);
            $composerBin = PHP_BINARY . ' ' . escapeshellarg(__DIR__ . '/composer.phar');
            $success  = false; // still need to run install
            $output   = "composer.phar downloaded. Click 'Run composer install' to continue.";
        } else {
            $errorMsg = 'Could not download composer.phar. Check allow_url_fopen and outbound network access.';
        }
    }

    // ── POST: run composer install ────────────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_composer']) && $composerBin) {
        $cmd   = $composerBin . ' install --no-dev --no-interaction --prefer-dist 2>&1';
        $lines = [];
        $rc    = 0;
        if ($execAvail) {
            exec($cmd, $lines, $rc);
        } elseif ($shellAvail) {
            $lines = explode("\n", (string) shell_exec($cmd));
            $rc    = file_exists(__DIR__ . '/vendor/autoload.php') ? 0 : 1;
        }
        $output  = implode("\n", $lines);
        $success = $rc === 0 && file_exists(__DIR__ . '/vendor/autoload.php');
        if ($success) {
            // Redirect to installer step 1
            header('Location: install.php?step=1');
            exit;
        } else {
            $errorMsg = 'composer install failed (exit code ' . $rc . '). See output below.';
        }
    }

    $pharExists      = file_exists(__DIR__ . '/composer.phar');
    $composerPresent = !empty($composerBin);

    // Build action buttons
    $buttons = '';
    if ($composerPresent) {
        $label   = htmlspecialchars($composerBin);
        $buttons .= '<form method="post" style="display:inline">
            <input type="hidden" name="run_composer" value="1">
            <button type="submit" class="btn btn-primary">&#9654; Run composer install</button>
        </form> ';
    }
    if ($execAvail || $shellAvail) {
        if (!$pharExists) {
            $buttons .= '<form method="post" style="display:inline">
                <input type="hidden" name="download_composer" value="1">
                <button type="submit" class="btn btn-secondary">&#8595; Download composer.phar first</button>
            </form>';
        } elseif (!$composerPresent) {
            // phar exists but wasn't found by which — offer to run it
            $safeBin = PHP_BINARY . ' ' . escapeshellarg(__DIR__ . '/composer.phar');
            $buttons .= '<form method="post" style="display:inline">
                <input type="hidden" name="run_composer" value="1">
                <button type="submit" class="btn btn-primary">&#9654; Run composer.phar install</button>
            </form>';
        }
    }
    if (!$buttons) {
        $buttons = '<p style="color:#9ca3af;font-size:.85rem">exec() and shell_exec() are disabled on this server — use the manual steps below.</p>';
    }

    $outputHtml = $output ? '<pre style="background:#1e293b;color:#e2e8f0;border-radius:.65rem;padding:1rem;font-size:.78rem;overflow-x:auto;margin-top:1.25rem;max-height:260px;overflow-y:auto">'
        . htmlspecialchars($output) . '</pre>' : '';
    $errorHtml = $errorMsg ? '<div class="alert alert-error" style="margin-bottom:1rem">' . htmlspecialchars($errorMsg) . '</div>' : '';

    $manual = <<<HTML
<details style="margin-top:1.5rem">
  <summary style="cursor:pointer;font-size:.85rem;font-weight:600;color:#64748b">Manual install instructions (click to expand)</summary>
  <ol style="margin:.75rem 0 0 1.25rem;font-size:.83rem;color:#64748b;line-height:1.9">
    <li>On your local machine, clone the repo and run:<br><code>composer install --no-dev --prefer-dist</code></li>
    <li>Upload the generated <code>vendor/</code> folder to the same location on your server via FTP/SFTP.</li>
    <li>Reload this page — the installer will continue automatically.</li>
  </ol>
</details>
HTML;

    $body = <<<HTML
<p style="color:#64748b;font-size:.88rem;margin-bottom:1.5rem">
    PHP dependencies are not installed yet (<code>vendor/</code> is missing).
    The installer can run <strong>composer install</strong> for you if composer is available on this server.
</p>
{$errorHtml}
<div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:center">
    {$buttons}
</div>
{$outputHtml}
{$manual}
HTML;

    echo vendorPage_html('Install Dependencies', $body, $composerBin, true);
}

function vendorPage_html(string $title, string $body, string $composerBin, bool $showRefresh): string
{
    $refresh = $showRefresh ? '<meta http-equiv="refresh" content="5;url=install.php">' : '';
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sublicious Installer — {$title}</title>
{$refresh}
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(135deg,#fff7ed 0%,#fef3c7 100%);min-height:100vh;display:flex;flex-direction:column;align-items:center;padding:2rem 1rem}
.logo{font-size:1.8rem;font-weight:800;color:#ea580c;margin-bottom:.25rem;letter-spacing:-.03em}
.logo span{color:#9a3412}
.subtitle{font-size:.85rem;color:#9a3412;margin-bottom:2rem;opacity:.8}
.card{background:#fff;border-radius:1.25rem;box-shadow:0 8px 40px rgba(0,0,0,.10);width:100%;max-width:580px;overflow:hidden}
.card-header{background:linear-gradient(135deg,#ea580c,#c2410c);padding:1.5rem 2rem;color:#fff}
.card-header h2{font-size:1.15rem;font-weight:700}
.card-header p{font-size:.82rem;opacity:.85;margin-top:.25rem}
.card-body{padding:2rem}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;padding:.7rem 1.5rem;border-radius:.65rem;font-size:.88rem;font-weight:600;cursor:pointer;border:none;transition:background .2s}
.btn-primary{background:#ea580c;color:#fff}.btn-primary:hover{background:#c2410c}
.btn-secondary{background:#f3f4f6;color:#374151}.btn-secondary:hover{background:#e5e7eb}
.alert-error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:.65rem;padding:.85rem 1rem;font-size:.85rem}
code{background:#f1f5f9;padding:.2em .4em;border-radius:.3rem;font-size:.88em}
</style>
</head>
<body>
<div class="logo">&#127860; Sublici<span>ous</span></div>
<p class="subtitle">Restaurant &amp; Delivery Management — Web Installer</p>
<div class="card">
    <div class="card-header">
        <h2>{$title}</h2>
        <p>Sublicious Web Installer</p>
    </div>
    <div class="card-body">{$body}</div>
</div>
</body>
</html>
HTML;
}

session_start();

// ─── Step routing ────────────────────────────────────────────────────────────
$step = (int) ($_GET['step'] ?? $_SESSION['step'] ?? 1);
$errors = [];
$success = '';

// Handle POST for each step
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_step = (int) ($_POST['step'] ?? 1);

    switch ($posted_step) {
        case 1: // Requirements — fix permissions or advance
            if (!empty($_POST['fix_permissions'])) {
                // Recursively chmod storage and bootstrap/cache via PHP
                $fixPaths = [
                    __DIR__ . '/storage',
                    __DIR__ . '/bootstrap/cache',
                ];
                foreach ($fixPaths as $base) {
                    if (!is_dir($base)) continue;
                    $iter = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS),
                        RecursiveIteratorIterator::SELF_FIRST
                    );
                    @chmod($base, 0775);
                    foreach ($iter as $item) {
                        @chmod($item->getPathname(), $item->isDir() ? 0775 : 0664);
                    }
                }
                // Also chmod project root
                @chmod(__DIR__, 0775);
                redirect(1); // reload to re-run checks
            }
            $_SESSION['step'] = 2;
            redirect(2);

        case 2: // Database
            $db = sanitize($_POST, ['db_driver','db_host','db_port','db_database','db_username','db_password']);
            $db['db_driver']   = in_array($db['db_driver'], ['mysql','sqlite']) ? $db['db_driver'] : 'mysql';
            $db['db_host']     = $db['db_host']     ?: '127.0.0.1';
            $db['db_port']     = $db['db_port']     ?: '3306';
            $db['db_database'] = $db['db_database'] ?: 'sublicious';
            $db['db_username'] = $db['db_username'] ?: 'root';

            $connErr = testDbConnection($db);
            if ($connErr) {
                $errors[] = $connErr;
            } else {
                $_SESSION['db'] = $db;
                $_SESSION['step'] = 3;
                redirect(3);
            }
            break;

        case 3: // App config
            $app = sanitize($_POST, ['app_name','app_url','app_timezone','app_env']);
            $app['app_name'] = $app['app_name'] ?: 'Sublicious';
            $app['app_url']  = rtrim($app['app_url'] ?: 'http://localhost', '/');
            $app['app_timezone'] = $app['app_timezone'] ?: 'Asia/Colombo';
            $app['app_env'] = in_array($app['app_env'], ['production','local']) ? $app['app_env'] : 'production';

            if (!filter_var($app['app_url'], FILTER_VALIDATE_URL)) {
                $errors[] = 'Please enter a valid App URL (include http:// or https://)';
            } else {
                $_SESSION['app'] = $app;
                $_SESSION['step'] = 4;
                redirect(4);
            }
            break;

        case 4: // Admin account
            $admin = sanitize($_POST, ['admin_name','admin_email','admin_password','admin_password_confirm']);

            if (empty($admin['admin_name']))     $errors[] = 'Admin name is required.';
            if (!filter_var($admin['admin_email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid admin email is required.';
            if (strlen($admin['admin_password']) < 8) $errors[] = 'Password must be at least 8 characters.';
            if ($admin['admin_password'] !== $admin['admin_password_confirm']) $errors[] = 'Passwords do not match.';

            if (empty($errors)) {
                unset($admin['admin_password_confirm']);
                $_SESSION['admin'] = $admin;
                $_SESSION['step'] = 5;
                redirect(5);
            }
            break;

        case 5: // SMS (optional — skip allowed)
            $sms = sanitize($_POST, ['sms_user_id','sms_api_key','sms_sender_id']);
            $sms['sms_sender_id'] = $sms['sms_sender_id'] ?: 'SMSlenzDEMO';
            $_SESSION['sms'] = $sms;
            $_SESSION['step'] = 6;
            redirect(6);

        case 6: // Run installation
            $result = runInstallation();
            if ($result === true) {
                $_SESSION['step'] = 7;
                redirect(7);
            } else {
                $errors[] = $result;
            }
            break;
    }
}

// ─── Render current step ──────────────────────────────────────────────────────
$step = (int) ($_SESSION['step'] ?? $step);
echo renderPage($step, $errors);
exit;

// ════════════════════════════════════════════════════════════════════════════
// HELPER FUNCTIONS
// ════════════════════════════════════════════════════════════════════════════

function redirect(int $step): void
{
    header("Location: install.php?step=$step");
    exit;
}

function sanitize(array $data, array $keys): array
{
    $out = [];
    foreach ($keys as $k) {
        $out[$k] = trim(htmlspecialchars_decode(strip_tags($data[$k] ?? '')));
    }
    return $out;
}

function testDbConnection(array $db): ?string
{
    if ($db['db_driver'] === 'sqlite') {
        return null; // SQLite file will be created during install
    }
    try {
        $dsn = "mysql:host={$db['db_host']};port={$db['db_port']};charset=utf8mb4";
        $pdo = new PDO($dsn, $db['db_username'], $db['db_password'], [PDO::ATTR_TIMEOUT => 5]);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db['db_database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        return null;
    } catch (PDOException $e) {
        return 'Database connection failed: ' . $e->getMessage();
    }
}

function runInstallation(): bool|string
{
    $db    = $_SESSION['db']    ?? [];
    $app   = $_SESSION['app']   ?? [];
    $admin = $_SESSION['admin'] ?? [];
    $sms   = $_SESSION['sms']   ?? [];

    if (empty($db) || empty($app) || empty($admin)) {
        return 'Session data lost. Please start again.';
    }

    // 1. Write .env
    try {
        writeEnv($db, $app, $admin, $sms);
    } catch (Throwable $e) {
        return 'Could not write .env file: ' . $e->getMessage() . '. Check file permissions on the project root.';
    }

    // 2. Bootstrap Laravel and run artisan commands
    try {
        // Clear any cached config first (in case partial install)
        @unlink(__DIR__ . '/bootstrap/cache/config.php');
        @unlink(__DIR__ . '/bootstrap/cache/routes.php');
        @unlink(__DIR__ . '/bootstrap/cache/services.php');

        define('LARAVEL_START', microtime(true));
        require __DIR__ . '/vendor/autoload.php';

        $app = require_once __DIR__ . '/bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();

        // Generate app key
        Illuminate\Support\Facades\Artisan::call('key:generate', ['--force' => true]);

        // SQLite: create file
        if (($db['db_driver'] ?? 'mysql') === 'sqlite') {
            $dbFile = __DIR__ . '/database/database.sqlite';
            if (!file_exists($dbFile)) {
                touch($dbFile);
            }
        }

        // Run migrations
        Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);

        // Seed plans + super admin
        Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);

        // Storage link
        Illuminate\Support\Facades\Artisan::call('storage:link', ['--force' => true]);

        // Save SMS platform settings
        if (!empty($sms['sms_user_id']) && !empty($sms['sms_api_key'])) {
            saveSmsSettings($sms);
        }

    } catch (Throwable $e) {
        return 'Installation error: ' . $e->getMessage() . ' (in ' . basename($e->getFile()) . ':' . $e->getLine() . ')';
    }

    // 3. Write lock file
    file_put_contents(__DIR__ . '/installed.lock', date('Y-m-d H:i:s') . ' UTC');

    // 4. Clear session
    session_destroy();

    return true;
}

function writeEnv(array $db, array $app, array $admin, array $sms): void
{
    $key = ''; // will be generated by artisan key:generate
    $dbBlock = $db['db_driver'] === 'sqlite'
        ? "DB_CONNECTION=sqlite"
        : implode("\n", [
            "DB_CONNECTION=mysql",
            "DB_HOST={$db['db_host']}",
            "DB_PORT={$db['db_port']}",
            "DB_DATABASE={$db['db_database']}",
            "DB_USERNAME={$db['db_username']}",
            "DB_PASSWORD={$db['db_password']}",
          ]);

    $env = <<<ENV
APP_NAME="{$app['app_name']}"
APP_ENV={$app['app_env']}
APP_KEY=
APP_DEBUG=false
APP_URL={$app['app_url']}
APP_TIMEZONE={$app['app_timezone']}

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

{$dbBlock}

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=public
QUEUE_CONNECTION=database

CACHE_STORE=database

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=587
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="noreply@sublicious.app"
MAIL_FROM_NAME="\${APP_NAME}"

STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=

VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=

SUPER_ADMIN_EMAIL={$admin['admin_email']}
SUPER_ADMIN_PASSWORD={$admin['admin_password']}
SUPER_ADMIN_NAME="{$admin['admin_name']}"
ENV;

    file_put_contents(__DIR__ . '/.env', $env);
}

function saveSmsSettings(array $sms): void
{
    try {
        App\Models\BusinessSetting::updateOrCreate(
            ['business_id' => null, 'key' => 'sms_user_id'],
            ['value' => $sms['sms_user_id'], 'group' => 'integrations']
        );
        App\Models\BusinessSetting::updateOrCreate(
            ['business_id' => null, 'key' => 'sms_api_key'],
            ['value' => $sms['sms_api_key'], 'group' => 'integrations']
        );
        App\Models\BusinessSetting::updateOrCreate(
            ['business_id' => null, 'key' => 'sms_sender_id'],
            ['value' => $sms['sms_sender_id'], 'group' => 'integrations']
        );
        App\Models\BusinessSetting::updateOrCreate(
            ['business_id' => null, 'key' => 'sms_base_url'],
            ['value' => 'https://smslenz.lk/api', 'group' => 'integrations']
        );
    } catch (Throwable) {
        // Non-fatal — SMS can be configured after install
    }
}

function checkRequirements(): array
{
    $checks = [];

    // PHP version
    $phpOk = version_compare(PHP_VERSION, '8.3.0', '>=');
    $checks[] = ['label' => 'PHP ' . PHP_VERSION, 'ok' => $phpOk, 'note' => $phpOk ? '' : 'PHP 8.3+ required'];

    // Extensions
    foreach (['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'curl', 'fileinfo', 'bcmath'] as $ext) {
        $checks[] = ['label' => "PHP extension: $ext", 'ok' => extension_loaded($ext), 'note' => extension_loaded($ext) ? '' : "Enable $ext in php.ini"];
    }

    // Writable paths — auto-attempt chmod via PHP before checking
    $writablePaths = [
        __DIR__,
        __DIR__ . '/storage',
        __DIR__ . '/storage/app',
        __DIR__ . '/storage/app/public',
        __DIR__ . '/storage/framework',
        __DIR__ . '/storage/framework/cache',
        __DIR__ . '/storage/framework/sessions',
        __DIR__ . '/storage/framework/views',
        __DIR__ . '/storage/logs',
        __DIR__ . '/bootstrap/cache',
    ];
    foreach ($writablePaths as $path) {
        if (is_dir($path) && !is_writable($path)) {
            @chmod($path, 0775);
        }
    }

    foreach ([__DIR__, __DIR__ . '/storage', __DIR__ . '/bootstrap/cache'] as $path) {
        $rel = str_replace(__DIR__, '.', $path);
        $ok  = is_writable($path);
        $checks[] = ['label' => "Writable: $rel", 'ok' => $ok, 'note' => $ok ? '' : "Run: chmod -R 775 $rel"];
    }

    // vendor/ present
    $hasVendor = is_dir(__DIR__ . '/vendor');
    $checks[] = ['label' => 'vendor/ directory', 'ok' => $hasVendor, 'note' => $hasVendor ? '' : 'Run composer install --no-dev first'];

    return $checks;
}

// ════════════════════════════════════════════════════════════════════════════
// RENDER FUNCTIONS
// ════════════════════════════════════════════════════════════════════════════

function renderError(string $title, string $body): string
{
    return <<<HTML
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Installer — {$title}</title><style>*{box-sizing:border-box}body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f8fafc;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:1rem}.card{background:#fff;border-radius:1rem;padding:2.5rem;max-width:480px;width:100%;box-shadow:0 4px 24px rgba(0,0,0,.08);text-align:center}h2{color:#dc2626;margin:0 0 1rem}p{color:#64748b;line-height:1.6}code{background:#f1f5f9;padding:.2em .4em;border-radius:.25rem;font-size:.9em}</style></head>
<body><div class="card"><h2>{$title}</h2><p>{$body}</p></div></body></html>
HTML;
}

function renderPage(int $step, array $errors = []): string
{
    $steps = ['Requirements', 'Database', 'App Config', 'Admin Account', 'SMS Setup', 'Install', 'Complete'];
    $stepTotal = count($steps);
    $stepLabel = $steps[$step - 1] ?? '';
    $progress  = round(($step / $stepTotal) * 100);

    $errorHtml = '';
    if (!empty($errors)) {
        $items = implode('', array_map(fn($e) => "<li>$e</li>", $errors));
        $errorHtml = "<div class=\"alert alert-error\"><ul>$items</ul></div>";
    }

    $content = match($step) {
        1 => stepRequirements(),
        2 => stepDatabase(),
        3 => stepAppConfig(),
        4 => stepAdminAccount(),
        5 => stepSms(),
        6 => stepInstall(),
        7 => stepComplete(),
        default => stepRequirements(),
    };

    // Build step indicator
    $stepDots = '';
    for ($i = 1; $i <= $stepTotal; $i++) {
        $cls = $i < $step ? 'done' : ($i === $step ? 'active' : '');
        $stepDots .= "<div class=\"step-dot $cls\" title=\"{$steps[$i-1]}\">".($i < $step ? '✓' : $i)."</div>";
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sublicious Installer — {$stepLabel}</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(135deg,#fff7ed 0%,#fef3c7 100%);min-height:100vh;display:flex;flex-direction:column;align-items:center;padding:2rem 1rem}
.logo{font-size:1.8rem;font-weight:800;color:#ea580c;margin-bottom:.25rem;letter-spacing:-.03em}
.logo span{color:#9a3412}
.subtitle{font-size:.85rem;color:#9a3412;margin-bottom:2rem;opacity:.8}
.card{background:#fff;border-radius:1.25rem;box-shadow:0 8px 40px rgba(0,0,0,.10);width:100%;max-width:560px;overflow:hidden}
.card-header{background:linear-gradient(135deg,#ea580c,#c2410c);padding:1.5rem 2rem;color:#fff}
.card-header h2{font-size:1.15rem;font-weight:700}
.card-header p{font-size:.82rem;opacity:.85;margin-top:.25rem}
.progress-bar{height:4px;background:rgba(255,255,255,.3);border-radius:2px;margin-top:1rem;overflow:hidden}
.progress-bar-fill{height:100%;background:#fff;border-radius:2px;transition:width .4s ease;width:{$progress}%}
.step-dots{display:flex;gap:.5rem;margin-top:.75rem;flex-wrap:wrap}
.step-dot{width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,.25);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;cursor:default;transition:background .2s}
.step-dot.active{background:#fff;color:#ea580c}
.step-dot.done{background:rgba(255,255,255,.6);color:#c2410c}
.card-body{padding:2rem}
.form-group{margin-bottom:1.25rem}
label{display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:.4rem}
label .note{font-weight:400;color:#9ca3af;font-size:.78rem;margin-left:.25rem}
input[type=text],input[type=email],input[type=password],input[type=url],input[type=number],select,textarea{width:100%;padding:.65rem .85rem;border:1.5px solid #e5e7eb;border-radius:.6rem;font-size:.88rem;color:#111827;transition:border-color .2s,box-shadow .2s;background:#fff;outline:none}
input:focus,select:focus{border-color:#ea580c;box-shadow:0 0 0 3px rgba(234,88,12,.12)}
.input-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;padding:.7rem 1.75rem;border-radius:.65rem;font-size:.9rem;font-weight:600;cursor:pointer;border:none;transition:background .2s,transform .1s}
.btn:active{transform:scale(.98)}
.btn-primary{background:#ea580c;color:#fff}
.btn-primary:hover{background:#c2410c}
.btn-secondary{background:#f3f4f6;color:#374151}
.btn-secondary:hover{background:#e5e7eb}
.btn-row{display:flex;align-items:center;justify-content:space-between;margin-top:1.75rem;gap:1rem}
.alert{border-radius:.65rem;padding:1rem 1.25rem;margin-bottom:1.25rem;font-size:.85rem}
.alert-error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626}
.alert-error ul{padding-left:1.25rem;margin:0}
.alert-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534}
.check-row{display:flex;align-items:center;gap:.75rem;padding:.55rem 0;border-bottom:1px solid #f3f4f6;font-size:.85rem}
.check-row:last-child{border-bottom:none}
.badge{width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;shrink:0}
.badge-ok{background:#dcfce7;color:#166534}
.badge-fail{background:#fee2e2;color:#dc2626}
.check-note{color:#ef4444;font-size:.78rem;margin-left:auto}
.divider{border:none;border-top:1px solid #f3f4f6;margin:1.25rem 0}
.hint{font-size:.78rem;color:#9ca3af;margin-top:.35rem}
.optional-badge{background:#fef3c7;color:#92400e;font-size:.7rem;font-weight:700;padding:.15rem .5rem;border-radius:.4rem;margin-left:.5rem;vertical-align:middle}
.success-icon{width:72px;height:72px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;font-size:2rem}
.creds-box{background:#f8fafc;border:1.5px solid #e5e7eb;border-radius:.75rem;padding:1.25rem;margin-top:1rem}
.creds-row{display:flex;justify-content:space-between;align-items:center;font-size:.85rem;padding:.35rem 0;border-bottom:1px solid #f1f5f9}
.creds-row:last-child{border-bottom:none}
.creds-label{color:#64748b}
.creds-value{font-weight:600;color:#1e293b;font-family:monospace}
.warning-box{background:#fffbeb;border:1.5px solid #fde68a;border-radius:.75rem;padding:1rem 1.25rem;margin-top:1.5rem;font-size:.82rem;color:#92400e}
.radio-group{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-top:.4rem}
.radio-card{border:1.5px solid #e5e7eb;border-radius:.65rem;padding:.85rem 1rem;cursor:pointer;transition:border-color .2s,background .2s;display:flex;align-items:flex-start;gap:.65rem}
.radio-card:has(input:checked){border-color:#ea580c;background:#fff7ed}
.radio-card input{margin-top:.15rem;accent-color:#ea580c}
.radio-card .rc-label{font-size:.85rem;font-weight:600;color:#374151}
.radio-card .rc-desc{font-size:.75rem;color:#9ca3af;margin-top:.15rem}
@media(max-width:480px){.input-row{grid-template-columns:1fr}.btn-row{flex-direction:column}.radio-group{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="logo">🍽 Sublici<span>ous</span></div>
<p class="subtitle">Restaurant & Delivery Management — Web Installer</p>

<div class="card">
    <div class="card-header">
        <h2>Step {$step} of {$stepTotal} — {$stepLabel}</h2>
        <p>Set up your Sublicious installation</p>
        <div class="progress-bar"><div class="progress-bar-fill"></div></div>
        <div class="step-dots">{$stepDots}</div>
    </div>
    <div class="card-body">
        {$errorHtml}
        {$content}
    </div>
</div>
</body>
</html>
HTML;
}

function stepRequirements(): string
{
    $checks   = checkRequirements();
    $allOk    = array_reduce($checks, fn($c, $r) => $c && $r['ok'], true);

    // Check specifically for writable failures (chmod fixable)
    $hasWriteFailure = false;
    foreach ($checks as $c) {
        if (!$c['ok'] && str_starts_with($c['label'], 'Writable:')) {
            $hasWriteFailure = true;
            break;
        }
    }

    $rows = '';
    foreach ($checks as $c) {
        $badge = $c['ok']
            ? '<span class="badge badge-ok">✓</span>'
            : '<span class="badge badge-fail">✗</span>';
        $note = $c['note'] ? "<span class=\"check-note\">{$c['note']}</span>" : '';
        $rows .= "<div class=\"check-row\">{$badge}<span>{$c['label']}</span>{$note}</div>";
    }

    $btnDisabled = $allOk ? '' : 'disabled style="opacity:.5;cursor:not-allowed"';
    $msg = $allOk
        ? '<div class="alert alert-success">All requirements met — you\'re good to go!</div>'
        : '<div class="alert alert-error">Some requirements are not met. Fix them before continuing.</div>';

    $fixBtn = $hasWriteFailure ? '
        <form method="post" action="install.php?step=1" style="display:inline">
            <input type="hidden" name="step" value="1">
            <input type="hidden" name="fix_permissions" value="1">
            <button type="submit" class="btn btn-secondary">&#128736; Fix Permissions Automatically</button>
        </form>' : '';

    return <<<HTML
{$msg}
{$rows}
<form method="post" action="install.php?step=1">
    <input type="hidden" name="step" value="1">
    <div class="btn-row">
        {$fixBtn}
        <button type="submit" class="btn btn-primary" {$btnDisabled}>Continue →</button>
    </div>
</form>
HTML;
}

function stepDatabase(): string
{
    $saved = $_SESSION['db'] ?? [];
    $driver   = $saved['db_driver']   ?? 'mysql';
    $host     = htmlspecialchars($saved['db_host']     ?? '127.0.0.1');
    $port     = htmlspecialchars($saved['db_port']     ?? '3306');
    $database = htmlspecialchars($saved['db_database'] ?? 'sublicious');
    $username = htmlspecialchars($saved['db_username'] ?? 'root');

    $mysqlChecked  = $driver === 'mysql'  ? 'checked' : '';
    $sqliteChecked = $driver === 'sqlite' ? 'checked' : '';
    $mysqlBlock    = $driver === 'sqlite' ? 'style="display:none"' : '';

    return <<<HTML
<form method="post" action="install.php?step=2">
    <input type="hidden" name="step" value="2">

    <div class="form-group">
        <label>Database Driver</label>
        <div class="radio-group">
            <label class="radio-card">
                <input type="radio" name="db_driver" value="mysql" {$mysqlChecked} onchange="document.getElementById('mysql-fields').style.display='block'">
                <div><div class="rc-label">MySQL / MariaDB</div><div class="rc-desc">Recommended for production</div></div>
            </label>
            <label class="radio-card">
                <input type="radio" name="db_driver" value="sqlite" {$sqliteChecked} onchange="document.getElementById('mysql-fields').style.display='none'">
                <div><div class="rc-label">SQLite</div><div class="rc-desc">Simple — no setup needed</div></div>
            </label>
        </div>
    </div>

    <div id="mysql-fields" {$mysqlBlock}>
        <div class="input-row">
            <div class="form-group">
                <label>Host</label>
                <input type="text" name="db_host" value="{$host}" placeholder="127.0.0.1">
            </div>
            <div class="form-group">
                <label>Port</label>
                <input type="number" name="db_port" value="{$port}" placeholder="3306">
            </div>
        </div>
        <div class="form-group">
            <label>Database Name</label>
            <input type="text" name="db_database" value="{$database}" placeholder="sublicious">
            <p class="hint">Will be created if it doesn't exist.</p>
        </div>
        <div class="input-row">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="db_username" value="{$username}" placeholder="root">
            </div>
            <div class="form-group">
                <label>Password <span class="note">(leave blank if none)</span></label>
                <input type="password" name="db_password" placeholder="••••••••">
            </div>
        </div>
    </div>

    <div class="btn-row">
        <a href="install.php?step=1" class="btn btn-secondary">← Back</a>
        <button type="submit" class="btn btn-primary">Test & Continue →</button>
    </div>
</form>
HTML;
}

function stepAppConfig(): string
{
    $saved = $_SESSION['app'] ?? [];
    $name  = htmlspecialchars($saved['app_name']     ?? 'Sublicious');
    $url   = htmlspecialchars($saved['app_url']      ?? 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $tz    = htmlspecialchars($saved['app_timezone'] ?? 'Asia/Colombo');
    $env   = $saved['app_env'] ?? 'production';

    $timezones = DateTimeZone::listIdentifiers();
    $tzOptions = '';
    foreach ($timezones as $tzName) {
        $sel = $tzName === $tz ? 'selected' : '';
        $tzOptions .= "<option value=\"$tzName\" $sel>$tzName</option>";
    }

    $prodChecked  = $env === 'production' ? 'checked' : '';
    $localChecked = $env === 'local'      ? 'checked' : '';

    return <<<HTML
<form method="post" action="install.php?step=3">
    <input type="hidden" name="step" value="3">

    <div class="form-group">
        <label>Application Name</label>
        <input type="text" name="app_name" value="{$name}" placeholder="Sublicious" required>
    </div>
    <div class="form-group">
        <label>Application URL</label>
        <input type="url" name="app_url" value="{$url}" placeholder="https://yourdomain.com" required>
        <p class="hint">The full URL where your app is hosted. No trailing slash.</p>
    </div>
    <div class="form-group">
        <label>Timezone</label>
        <select name="app_timezone">{$tzOptions}</select>
    </div>
    <div class="form-group">
        <label>Environment</label>
        <div class="radio-group">
            <label class="radio-card">
                <input type="radio" name="app_env" value="production" {$prodChecked}>
                <div><div class="rc-label">Production</div><div class="rc-desc">Live site — errors hidden</div></div>
            </label>
            <label class="radio-card">
                <input type="radio" name="app_env" value="local" {$localChecked}>
                <div><div class="rc-label">Local / Dev</div><div class="rc-desc">Error details visible</div></div>
            </label>
        </div>
    </div>

    <div class="btn-row">
        <a href="install.php?step=2" class="btn btn-secondary">← Back</a>
        <button type="submit" class="btn btn-primary">Continue →</button>
    </div>
</form>
HTML;
}

function stepAdminAccount(): string
{
    $saved = $_SESSION['admin'] ?? [];
    $name  = htmlspecialchars($saved['admin_name']  ?? 'Super Admin');
    $email = htmlspecialchars($saved['admin_email'] ?? '');

    return <<<HTML
<form method="post" action="install.php?step=4">
    <input type="hidden" name="step" value="4">

    <p style="font-size:.85rem;color:#64748b;margin-bottom:1.25rem">
        This creates the super admin account used to manage all businesses on the platform.
    </p>

    <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="admin_name" value="{$name}" placeholder="Super Admin" required>
    </div>
    <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="admin_email" value="{$email}" placeholder="admin@yourdomain.com" required>
    </div>
    <div class="input-row">
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="admin_password" placeholder="Min. 8 characters" required>
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="admin_password_confirm" placeholder="Repeat password" required>
        </div>
    </div>

    <div class="btn-row">
        <a href="install.php?step=3" class="btn btn-secondary">← Back</a>
        <button type="submit" class="btn btn-primary">Continue →</button>
    </div>
</form>
HTML;
}

function stepSms(): string
{
    $saved    = $_SESSION['sms'] ?? [];
    $userId   = htmlspecialchars($saved['sms_user_id']   ?? '');
    $apiKey   = htmlspecialchars($saved['sms_api_key']   ?? '');
    $senderId = htmlspecialchars($saved['sms_sender_id'] ?? 'SMSlenzDEMO');

    return <<<HTML
<form method="post" action="install.php?step=5">
    <input type="hidden" name="step" value="5">

    <p style="font-size:.85rem;color:#64748b;margin-bottom:1.25rem">
        Configure <strong>SMSlenz</strong> for order SMS notifications.
        <span class="optional-badge">OPTIONAL</span><br>
        You can skip this and configure it later in <em>Settings → Integrations</em>.
    </p>

    <div class="input-row">
        <div class="form-group">
            <label>SMSlenz User ID</label>
            <input type="text" name="sms_user_id" value="{$userId}" placeholder="e.g. 1557">
        </div>
        <div class="form-group">
            <label>API Key</label>
            <input type="text" name="sms_api_key" value="{$apiKey}" placeholder="Your API key">
        </div>
    </div>
    <div class="form-group">
        <label>Sender ID</label>
        <input type="text" name="sms_sender_id" value="{$senderId}" placeholder="SMSlenzDEMO">
        <p class="hint">Use <strong>SMSlenzDEMO</strong> for testing, or your approved Sender ID for live SMS.</p>
    </div>

    <div class="btn-row">
        <a href="install.php?step=4" class="btn btn-secondary">← Back</a>
        <div style="display:flex;gap:.75rem">
            <button type="submit" name="skip_sms" class="btn btn-secondary">Skip →</button>
            <button type="submit" class="btn btn-primary">Save & Continue →</button>
        </div>
    </div>
</form>
HTML;
}

function stepInstall(): string
{
    $db    = $_SESSION['db']    ?? [];
    $app   = $_SESSION['app']   ?? [];
    $admin = $_SESSION['admin'] ?? [];
    $sms   = $_SESSION['sms']   ?? [];

    $dbSummary  = ($db['db_driver'] ?? 'mysql') === 'sqlite'
        ? 'SQLite (database/database.sqlite)'
        : ($db['db_database'] ?? '?') . ' @ ' . ($db['db_host'] ?? '?');
    $appUrl     = htmlspecialchars($app['app_url'] ?? '');
    $adminEmail = htmlspecialchars($admin['admin_email'] ?? '');
    $smsStatus  = !empty($sms['sms_user_id']) ? 'Configured (User ID: ' . htmlspecialchars($sms['sms_user_id']) . ')' : 'Skipped';

    return <<<HTML
<p style="font-size:.85rem;color:#64748b;margin-bottom:1.25rem">
    Review your configuration, then click <strong>Install Now</strong> to begin.
    This will run database migrations and create your admin account.
</p>

<div class="creds-box" style="margin-bottom:1.25rem">
    <div class="creds-row"><span class="creds-label">Database</span><span class="creds-value">{$dbSummary}</span></div>
    <div class="creds-row"><span class="creds-label">App URL</span><span class="creds-value">{$appUrl}</span></div>
    <div class="creds-row"><span class="creds-label">Admin Email</span><span class="creds-value">{$adminEmail}</span></div>
    <div class="creds-row"><span class="creds-label">SMS</span><span class="creds-value">{$smsStatus}</span></div>
</div>

<form method="post" action="install.php?step=6" onsubmit="this.querySelector('.btn-primary').disabled=true;this.querySelector('.btn-primary').textContent='Installing…'">
    <input type="hidden" name="step" value="6">
    <div class="btn-row">
        <a href="install.php?step=5" class="btn btn-secondary">← Back</a>
        <button type="submit" class="btn btn-primary">🚀 Install Now</button>
    </div>
</form>
HTML;
}

function stepComplete(): string
{
    $app   = $_SESSION['app']   ?? [];
    $admin = $_SESSION['admin'] ?? [];

    $appUrl     = htmlspecialchars($app['app_url'] ?? '/');
    $adminUrl   = rtrim($appUrl, '/') . '/login';
    $adminEmail = htmlspecialchars($admin['admin_email'] ?? '');

    return <<<HTML
<div style="text-align:center;padding:.5rem 0">
    <div class="success-icon">🎉</div>
    <h3 style="font-size:1.3rem;font-weight:800;color:#166534;margin-bottom:.5rem">Installation Complete!</h3>
    <p style="color:#64748b;font-size:.9rem;margin-bottom:1.5rem">Sublicious is ready. Log in with your admin account to get started.</p>
</div>

<div class="creds-box">
    <div class="creds-row"><span class="creds-label">Login URL</span><span class="creds-value">{$adminUrl}</span></div>
    <div class="creds-row"><span class="creds-label">Email</span><span class="creds-value">{$adminEmail}</span></div>
    <div class="creds-row"><span class="creds-label">Password</span><span class="creds-value">The one you entered</span></div>
</div>

<div class="warning-box" style="margin-top:1.25rem">
    <strong>⚠ Security:</strong> Delete <code>install.php</code> from your server immediately after this step.
    Anyone with access to this file can reinstall and overwrite your data.
</div>

<div style="margin-top:1.5rem;text-align:center">
    <a href="{$adminUrl}" class="btn btn-primary" style="font-size:1rem;padding:.85rem 2.5rem">Open Sublicious →</a>
</div>
HTML;
}
