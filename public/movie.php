<?php
require_once __DIR__ . '/../config.php';

$slug = $_GET['slug'] ?? '';
$movie = get_movie_by_slug($slug);

if (!$movie) {
    http_response_code(404);
    $page_title = "Movie Not Found | $SITE_NAME";
    include __DIR__ . '/partials/header.php';
    echo '<p class="text-slate-400">Movie not found.</p>';
    include __DIR__ . '/partials/footer.php';
    exit;
}

track_visit($CURRENT_SITE_ID);

$META_DESCRIPTION = mb_substr($movie['overview'] ?: $META_DESCRIPTION, 0, 150) . '...';
$page_title = $movie['title'] . ' | ' . $SITE_NAME;

include __DIR__ . '/partials/header.php';

$poster   = poster_url($movie['poster_path'], 'w500');
$backdrop = poster_url($movie['backdrop_path'], 'w780');
?>

<article class="grid md:grid-cols-[2fr,3fr] gap-6">
  <div>
    <?php if ($poster): ?>
      <img src="<?= htmlspecialchars($poster) ?>"
           alt="<?= htmlspecialchars($movie['title']) ?>"
           class="w-full rounded-xl border border-slate-800 shadow-lg">
    <?php endif; ?>
  </div>

  <div>
    <h1 class="text-2xl md:text-3xl font-bold mb-2">
      <?= htmlspecialchars($movie['title']) ?>
    </h1>

    <div class="flex flex-wrap gap-3 text-sm text-slate-400 mb-3">
      <?php if ($movie['release_date']): ?>
        <span><i class="fa-solid fa-calendar-day"></i> <?= htmlspecialchars($movie['release_date']) ?></span>
      <?php endif; ?>
      <span><i class="fa-solid fa-star"></i> <?= number_format($movie['vote_average'], 1) ?></span>
      <?php if ($movie['original_language']): ?>
        <span><i class="fa-solid fa-language"></i> <?= strtoupper($movie['original_language']) ?></span>
      <?php endif; ?>
    </div>

    <p class="text-sm text-slate-200 leading-relaxed mb-4">
      <?= nl2br(htmlspecialchars($movie['overview'])) ?>
    </p>

    <div class="mt-4 text-xs text-slate-500">
      <p>TMDB ID: <?= (int)$movie['tmdb_id'] ?></p>
    </div>
  </div>
</article>

<?php include __DIR__ . '/partials/footer.php'; ?>
