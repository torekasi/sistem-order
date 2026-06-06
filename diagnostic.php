<?php
/**
 * DEBUG DIAGNOSTIC SCRIPT
 * Upload to the root of the site on cPanel and visit this file directly.
 * It bypasses all application code and tests each component individually.
 */
echo '<html><head><title>Diagnostic</title><style>body{font-family:monospace;background:#111;color:#fff;padding:20px} .ok{color:#0f0} .fail{color:#f00} .info{color:#0af} h2{color:#ff0;border-bottom:1px solid #333;padding-bottom:5px}</style></head><body>';
echo '<h1>Sistem Order - Diagnostic</h1>';

echo '<h2>1. PHP Version</h2>';
echo '<span class="info">' . phpversion() . '</span><br>';

echo '<h2>2. File Structure</h2>';
$files = ['.config.php', 'index.php', 'utils/Logger.php', 'utils/Security.php', 'public/index.php', 'models/SettingsModel.php', 'models/MenuModel.php', 'controllers/MenuController.php', 'views/includes/header.php', 'views/menu.php'];
foreach ($files as $f) {
    if (file_exists(__DIR__ . '/' . $f)) {
        echo "<span class='ok'>[OK]</span> $f<br>";
    } else {
        echo "<span class='fail'>[MISSING]</span> $f<br>";
    }
}

echo '<h2>3. .config.php Constants</h2>';
define('BASE_PATH', __DIR__ . DIRECTORY_SEPARATOR);
require_once __DIR__ . '/.config.php';

echo '<span class="info">DB_HOST:</span> ' . DB_HOST . '<br>';
echo '<span class="info">DB_NAME:</span> ' . DB_NAME . '<br>';
echo '<span class="info">DB_USER:</span> ' . DB_USER . '<br>';
echo '<span class="info">APP_URL:</span> ' . APP_URL . '<br>';
echo '<span class="info">BASE_PATH:</span> ' . BASE_PATH . '<br>';

echo '<h2>4. Database Connection</h2>';
try {
    $pdo = getDB();
    echo "<span class='ok'>[OK]</span> Connected to database<br>";
    
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<span class='ok'>[OK]</span> Tables: " . count($tables) . " found<br>";
    foreach ($tables as $t) {
        echo "  - $t<br>";
    }
    
    echo '<h2>5. Data Check</h2>';
    $uc = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $cc = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $mc = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
    $oc = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    echo "Users: $uc<br>Categories: $cc<br>Menu Items: $mc<br>Orders: $oc<br>";
    
    if ($uc == 0) echo "<span class='fail'>[WARN]</span> No users found. Run database.sql first.<br>";
    if ($mc == 0) echo "<span class='fail'>[WARN]</span> No menu items found.<br>";
    
} catch (Exception $e) {
    echo "<span class='fail'>[FAIL]</span> Database error: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "File: " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "<br>";
}

echo '<h2>6. Model Instantiation Test</h2>';
require_once __DIR__ . '/utils/Logger.php';
require_once __DIR__ . '/utils/Security.php';

$models = ['SettingsModel', 'MenuModel', 'UserModel'];
foreach ($models as $model) {
    try {
        require_once __DIR__ . "/models/$model.php";
        $instance = new $model();
        echo "<span class='ok'>[OK]</span> $model instantiated<br>";
    } catch (Exception $e) {
        echo "<span class='fail'>[FAIL]</span> $model: " . htmlspecialchars($e->getMessage()) . "<br>";
    } catch (Error $e) {
        echo "<span class='fail'>[FAIL]</span> $model: " . htmlspecialchars($e->getMessage()) . " (at " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . ")<br>";
    }
}

echo '<h2>7. Controller Instantiation Test</h2>';
$controllers = ['MenuController', 'AuthController', 'OrderController'];
foreach ($controllers as $ctrl) {
    try {
        require_once __DIR__ . "/controllers/$ctrl.php";
        $instance = new $ctrl();
        echo "<span class='ok'>[OK]</span> $ctrl instantiated<br>";
    } catch (Exception $e) {
        echo "<span class='fail'>[FAIL]</span> $ctrl: " . htmlspecialchars($e->getMessage()) . "<br>";
    } catch (Error $e) {
        echo "<span class='fail'>[FAIL]</span> $ctrl: " . htmlspecialchars($e->getMessage()) . " (at " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . ")<br>";
    }
}

echo '<h2>8. Session Test</h2>';
try {
    initSession();
    echo "<span class='ok'>[OK]</span> Session started (name: " . session_name() . ")<br>";
} catch (Exception $e) {
    echo "<span class='fail'>[FAIL]</span> Session: " . htmlspecialchars($e->getMessage()) . "<br>";
}

echo '<h2>9. View Include Test</h2>';
$views = ['views/includes/header.php', 'views/menu.php', 'views/login.php'];
foreach ($views as $view) {
    try {
        if (file_exists(__DIR__ . '/' . $view)) {
            $content = file_get_contents(__DIR__ . '/' . $view);
            echo "<span class='ok'>[OK]</span> $view exists (" . strlen($content) . " bytes)<br>";
        } else {
            echo "<span class='fail'>[MISSING]</span> $view<br>";
        }
    } catch (Exception $e) {
        echo "<span class='fail'>[FAIL]</span> $view: " . htmlspecialchars($e->getMessage()) . "<br>";
    }
}

echo '<p style="margin-top:30px;color:#0af">--- Diagnostic complete ---</p>';
echo '</body></html>';
