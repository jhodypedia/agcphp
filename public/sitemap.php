<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc><?= htmlspecialchars($BASE_URL . '/') ?></loc>
    <changefreq>hourly</changefreq>
    <priority>1.0</priority>
  </url>
<?php
$res = $mysqli->query("SELECT slug, updated_at FROM movies ORDER BY updated_at DESC LIMIT 5000");
while ($row = $res->fetch_assoc()):
?>
  <url>
    <loc><?= htmlspecialchars($BASE_URL . '/movie/' . urlencode($row['slug'])) ?></loc>
    <lastmod><?= date('c', strtotime($row['updated_at'])) ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>
<?php endwhile; ?>
</urlset>
