<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: text/plain; charset=utf-8');
?>
User-agent: *
Allow: /

Sitemap: <?= $BASE_URL ?>/sitemap.xml
