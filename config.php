<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tmdb_agc');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    die('Database connection error');
}
$mysqli->set_charset('utf8mb4');

require_once __DIR__ . '/functions.php';

// SECURITY HEADERS
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-XSS-Protection: 1; mode=block');

// DETECT HOST & SITE (multi-tenant)
$current_host = strtolower($_SERVER['HTTP_HOST'] ?? '');
$current_host = preg_replace('/:\d+$/', '', $current_host);

$CURRENT_SITE    = get_site_by_domain($current_host);
$CURRENT_SITE_ID = $CURRENT_SITE['id'] ?? null;

// Auth / admin area boleh jalan walau belum ada site
if (!$CURRENT_SITE && !is_auth_or_admin_area()) {
    http_response_code(404);
    echo "Domain is not registered in system. Please login and add this domain from your panel.";
    exit;
}

// GLOBAL SETTINGS
$TMDB_API_KEY   = get_global_setting('tmdb_api_key', '');
$ITEMS_PER_PAGE = (int)get_global_setting('items_per_page', 24);
$PREMIUM_PRICE  = (int)get_global_setting('premium_price_idr', 30000);

// BASE URL (http/https + domain)
$BASE_URL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://')
            . $current_host;

// SITE-LEVEL
$SITE_NAME        = $CURRENT_SITE['site_name']        ?? 'TMDB AGC';
$SITE_TAGLINE     = $CURRENT_SITE['tagline']          ?? 'Auto generated movie database';
$META_DESCRIPTION = $CURRENT_SITE['meta_description'] ?? 'Auto generated movie database powered by TMDB.';
$AD_HEADER        = $CURRENT_SITE['ad_header']        ?? '';
$AD_BETWEEN_GRID  = $CURRENT_SITE['ad_between_grid']  ?? '';
