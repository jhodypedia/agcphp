<?php
$page_title = "Global Settings";
include __DIR__ . '/partials/header.php';

require_admin();

$info = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();

    $tmdb_key      = trim($_POST['tmdb_api_key'] ?? '');
    $items_per     = (int)($_POST['items_per_page'] ?? 24);
    $premium_price = (int)($_POST['premium_price_idr'] ?? 30000);

    if ($items_per <= 0)  $items_per = 24;
    if ($premium_price <= 0) $premium_price = 30000;

    set_global_setting('tmdb_api_key', $tmdb_key);
    set_global_setting('items_per_page', (string)$items_per);
    set_global_setting('premium_price_idr', (string)$premium_price);

    $info = 'Settings saved.';
    log_activity(current_user_id(), 'save_global_settings', "items_per=$items_per,premium=$premium_price");
}

$tmdb_key      = get_global_setting('tmdb_api_key', '');
$items_per     = (int)get_global_setting('items_per_page', 24);
$premium_price = (int)get_global_setting('premium_price_idr', 30000);
?>

<h1 class="text-xl font-semibold mb-4 flex items-center gap-2">
  <i class="fa-solid fa-sliders text-indigo-300"></i> Global Settings
</h1>

<?php if ($info): ?>
  <div class="alert alert-success mb-4">
    <div class="alert-icon"><i class="fa-solid fa-circle-check"></i></div>
    <div>
      <div class="alert-title">Success</div>
      <div class="alert-text"><?= htmlspecialchars($info) ?></div>
    </div>
  </div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-danger mb-4">
    <div class="alert-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
    <div>
      <div class="alert-title">Error</div>
      <div class="alert-text"><?= htmlspecialchars($error) ?></div>
    </div>
  </div>
<?php endif; ?>

<form method="post" class="space-y-4 max-w-lg">
  <?php csrf_field(); ?>

  <div>
    <label class="block text-xs mb-1">TMDB API Key</label>
    <input type="text" name="tmdb_api_key"
           value="<?= htmlspecialchars($tmdb_key) ?>"
           class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm">
    <p class="text-[11px] text-slate-500 mt-1">
      Get your API key from TMDB dashboard and paste here.
    </p>
  </div>

  <div class="grid md:grid-cols-2 gap-4">
    <div>
      <label class="block text-xs mb-1">Items per page</label>
      <input type="number" name="items_per_page"
             value="<?= (int)$items_per ?>"
             class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm">
    </div>
    <div>
      <label class="block text-xs mb-1">Premium price (IDR)</label>
      <input type="number" name="premium_price_idr"
             value="<?= (int)$premium_price ?>"
             class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm">
    </div>
  </div>

  <button type="submit"
          class="bg-indigo-600 hover:bg-indigo-500 rounded px-4 py-2 text-sm font-medium">
    Save Settings
  </button>
</form>

<?php include __DIR__ . '/partials/footer.php'; ?>
