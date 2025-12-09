<?php
require_once __DIR__ . '/../config.php';

track_visit($CURRENT_SITE_ID);

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

$page_title = $q ? "Search: $q | $SITE_NAME" : "Search Movies | $SITE_NAME";

list($movies, $total) = get_movies($page, $ITEMS_PER_PAGE, $q);
$total_pages = max(1, ceil($total / $ITEMS_PER_PAGE));

include __DIR__ . '/partials/header.php';
?>

<h1 class="text-xl md:text-2xl font-semibold mb-4">
  Search: <span class="text-indigo-400"><?= htmlspecialchars($q) ?></span>
</h1>

<?php if (!$q): ?>
  <p class="text-slate-400">Type a keyword to search movies.</p>
<?php elseif (empty($movies)): ?>
  <p class="text-slate-400">No results found.</p>
<?php else: ?>
  <p class="text-xs text-slate-400 mb-3">
    Found <?= $total ?> result(s).
  </p>
  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
    <?php foreach ($movies as $m): $poster = poster_url($m['poster_path']); ?>
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
    <?php endforeach; ?>
  </div>

  <div class="mt-6 flex items-center justify-center gap-2 text-sm">
    <?php if ($page > 1): ?>
      <a href="<?= $BASE_URL ?>/search?q=<?= urlencode($q) ?>&page=<?= $page - 1 ?>"
         class="px-3 py-1 border border-slate-700 rounded-full hover:border-indigo-500">
        Prev
      </a>
    <?php endif; ?>

    <span class="px-3 py-1 text-slate-400">
      Page <?= $page ?> of <?= $total_pages ?>
    </span>

    <?php if ($page < $total_pages): ?>
      <a href="<?= $BASE_URL ?>/search?q=<?= urlencode($q) ?>&page=<?= $page + 1 ?>"
         class="px-3 py-1 border border-slate-700 rounded-full hover:border-indigo-500">
        Next
      </a>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
