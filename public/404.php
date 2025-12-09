<?php
require_once __DIR__ . '/../config.php';
http_response_code(404);

$page_title = "404 Not Found | $SITE_NAME";
$META_DESCRIPTION = "The page you are looking for could not be found.";
include __DIR__ . '/partials/header.php';
?>

<div class="min-h-[60vh] flex flex-col items-center justify-center text-center">
  <div class="text-5xl md:text-6xl font-black text-slate-700 mb-2">404</div>
  <h1 class="text-xl md:text-2xl font-semibold mb-2">Page not found</h1>
  <p class="text-sm text-slate-400 mb-4 max-w-md">
    The page you tried to open does not exist or has been moved.
    Please check the URL or go back to the homepage.
  </p>
  <a href="<?= $BASE_URL ?>/"
     class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 rounded-full px-4 py-2 text-sm font-medium">
    <i class="fa-solid fa-house"></i> Back to Home
  </a>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
