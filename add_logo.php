<?php
define('BASE_PATH', __DIR__ . '/');
require '.config.php';
require 'utils/Database.php';
require 'utils/Logger.php';
$db = getDB();
$stmt = $db->query("SELECT * FROM system_settings WHERE setting_key = 'store_logo'");
if (!$stmt->fetch()) {
    $db->exec("INSERT INTO system_settings (setting_key, setting_value, setting_group, setting_type, setting_label, setting_description, urutan) VALUES ('store_logo', '', 'store', 'image', 'Logo Kedai', 'Muat naik fail logo PNG/JPG/WEBP.', 1)");
    echo 'Inserted store_logo setting.';
} else {
    echo 'store_logo already exists.';
}
