<?php
require_once __DIR__ . '/../config.php';

track_visit($CURRENT_SITE_ID);
ensure_popular_cached(3);

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
list($movies, $total) = get_movies($page, $ITEMS_PER_PAGE);
$total_pages = max(1, ceil($total / $ITEMS_PER_PAGE));

$page_title = "Latest Movies | $SITE_NAME";

include __DIR__ . '/partials/header.php';

$theme = $CURRENT_SITE['theme'] ?? 'classic';
?>

<?php if (empty($movies)): ?>
  <p class="text-slate-400">No movies available.</p>
<?php else: ?>

<?php if ($theme === 'classic'): ?>
  <h1 class="text-xl md:text-2xl font-semibold mb-4">Popular Movies</h1>
  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
    <?php $i = 0; foreach ($movies as $m): $i++; $poster = poster_url($m['poster_path']); ?>
      <a href="<?= $BASE_URL ?>/movie/<?= urlencode($m['slug']) ?>"
         class="group bg-slate-900/80 border border-slate-800 rounded-xl overflow-hidden hover:border-indigo-500 transition">
        <div class="aspect-[2/3] overflow-hidden">
          <?php if ($poster): ?>
            <img src="<?= htmlspecialchars($poster) ?>"
                 alt="<?= htmlspecialchars($m['title']) ?>"
                 loading="lazy"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
          <?php else: ?>
            <div class="w-full h-full flex items-center justify-center text-slate-500 text-xs">
              No Image
            </div>
          <?php endif; ?>
        </div>
        <div class="p-2">
          <h2 class="text-xs font-semibold line-clamp-2 group-hover:text-indigo-400">
            <?= htmlspecialchars($m['title']) ?>
          </h2>
          <div class="mt-1 flex items-center justify-between text-[11px] text-slate-400">
            <span><?= $m['release_date'] ?: '-' ?></span>
            <span class="flex items-center gap-1">
              ⭐ <?= number_format($m['vote_average'], 1) ?>
            </span>
          </div>
        </div>
      </a>
      <?php if ($i === 6 && !empty($AD_BETWEEN_GRID)): ?>
        <div class="col-span-2 sm:col-span-3 md:col-span-4 lg:col-span-6 flex justify-center">
          <?= $AD_BETWEEN_GRID ?>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

<?php elseif ($theme === 'cinema'): ?>
  <?php $first = $movies[0]; $posterHero = poster_url($first['backdrop_path'] ?: $first['poster_path'], 'w780'); ?>
  <section class="mb-6 rounded-2xl overflow-hidden border border-slate-800 bg-slate-900/70 relative">
    <?php if ($posterHero): ?>
      <div class="absolute inset-0">
        <img src="<?= htmlspecialchars($posterHero) ?>" alt=""
             class="w-full h-full object-cover opacity-40">
      </div>
    <?php endif; ?>
    <div class="relative p-5 md:p-8">
      <p class="text-xs uppercase tracking-[0.2em] text-indigo-300 mb-2">Featured</p>
      <h1 class="text-2xl md:text-3xl font-bold mb-2">
        <?= htmlspecialchars($first['title']) ?>
      </h1>
      <p class="text-sm md:text-base text-slate-200 line-clamp-3 md:line-clamp-4 mb-4">
        <?= htmlspecialchars($first['overview']) ?>
      </p>
      <a href="<?= $BASE_URL ?>/movie/<?= urlencode($first['slug']) ?>"
         class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 rounded-full px-4 py-2 text-sm font-medium">
        <i class="fa-solid fa-circle-play text-xs"></i> View Details
      </a>
    </div>
  </section>

  <h2 class="text-lg font-semibold mb-3">More Movies</h2>
  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
    <?php $i = 0; foreach (array_slice($movies, 1) as $m): $i++; $poster = poster_url($m['poster_path']); ?>
      <a href="<?= $BASE_URL ?>/movie/<?= urlencode($m['slug']) ?>"
         class="group bg-slate-900/80 border border-slate-800 rounded-xl overflow-hidden hover:border-indigo-500 transition">
        <div class="aspect-[2/3] overflow-hidden">
          <?php if ($poster): ?>
            <img src="<?= htmlspecialchars($poster) ?>"
                 alt="<?= htmlspecialchars($m['title']) ?>"
                 loading="lazy"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
          <?php else: ?>
            <div class="w-full h-full flex items-center justify-center text-slate-500 text-xs">
              No Image
            </div>
          <?php endif; ?>
        </div>
        <div class="p-2">
          <h2 class="text-xs font-semibold line-clamp-2 group-hover:text-indigo-400">
            <?= htmlspecialchars($m['title']) ?>
          </h2>
        </div>
      </a>
      <?php if ($i === 5 && !empty($AD_BETWEEN_GRID)): ?>
        <div class="col-span-2 sm:col-span-3 md:col-span-5 flex justify-center">
          <?= $AD_BETWEEN_GRID ?>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

<?php elseif ($theme === 'cards'): ?>
  <h1 class="text-xl md:text-2xl font-semibold mb-4">Discover Movies</h1>
  <div class="space-y-4">
    <?php $i = 0; foreach ($movies as $m): $i++; $poster = poster_url($m['poster_path']); ?>
      <article class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden flex flex-col md:flex-row card-soft">
        <?php if ($poster): ?>
          <div class="md:w-1/4">
            <img src="<?= htmlspecialchars($poster) ?>"
                 alt="<?= htmlspecialchars($m['title']) ?>"
                 loading="lazy"
                 class="w-full h-full object-cover">
          </div>
        <?php endif; ?>
        <div class="flex-1 p-4 md:p-5">
          <h2 class="text-lg font-semibold mb-1">
            <a href="<?= $BASE_URL ?>/movie/<?= urlencode($m['slug']) ?>"
               class="hover:text-indigo-400">
              <?= htmlspecialchars($m['title']) ?>
            </a>
          </h2>
          <div class="text-xs text-slate-400 mb-2 flex flex-wrap gap-3">
            <span>Release: <?= $m['release_date'] ?: '-' ?></span>
            <span>Rating: ⭐ <?= number_format($m['vote_average'], 1) ?></span>
          </div>
          <p class="text-sm text-slate-200 line-clamp-3 mb-3">
            <?= htmlspecialchars($m['overview']) ?>
          </p>
          <a href="<?= $BASE_URL ?>/movie/<?= urlencode($m['slug']) ?>"
             class="inline-flex text-xs text-indigo-400 hover:text-indigo-300">
            View details →
          </a>
        </div>
      </article>

      <?php if ($i === 3 && !empty($AD_BETWEEN_GRID)): ?>
        <div class="flex justify-center">
          <?= $AD_BETWEEN_GRID ?>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="mt-6 flex items-center justify-center gap-2 text-sm">
  <?php if ($page > 1): ?>
    <a href="<?= $BASE_URL ?>/?page=<?= $page - 1 ?>"
       class="px-3 py-1 border border-slate-700 rounded-full hover:border-indigo-500">
      Prev
    </a>
  <?php endif; ?>

  <span class="px-3 py-1 text-slate-400">
    Page <?= $page ?> of <?= $total_pages ?>
  </span>

  <?php if ($page < $total_pages): ?>
    <a href="<?= $BASE_URL ?>/?page=<?= $page + 1 ?>"
       class="px-3 py-1 border border-slate-700 rounded-full hover:border-indigo-500">
      Next
    </a>
  <?php endif; ?>
</div>

<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
