<?php
$page_title = "My Sites";
include __DIR__ . '/partials/header.php';

$CURRENT_USER = current_user();
$IS_PREMIUM   = is_premium($CURRENT_USER);

$info = '';
$error = '';

// HANDLE SAVE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $IS_PREMIUM) {
    csrf_verify_or_die();

    $domain           = trim($_POST['domain'] ?? '');
    $site_name        = trim($_POST['site_name'] ?? '');
    $tagline          = trim($_POST['tagline'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $ad_header        = trim($_POST['ad_header'] ?? '');
    $ad_between_grid  = trim($_POST['ad_between_grid'] ?? '');
    $theme            = $_POST['theme'] ?? 'classic';

    if (!$domain || !$site_name) {
        $error = 'Domain and site name are required.';
    } else {
        // NORMALIZE DOMAIN
        $domain = strtolower($domain);
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = trim($domain, "/");

        // VALIDATE
        if (!preg_match('/^([a-z0-9-]+\.)+[a-z]{2,}$/', $domain)) {
            $error = 'Invalid domain format. Example: example.com or sub.example.com';
        } elseif (!in_array($theme, ['classic','cinema','cards'], true)) {
            $error = 'Invalid theme.';
        } else {
            // INSERT / UPDATE
            $user_id = $CURRENT_USER['id'];

            // cek apakah site sudah ada oleh user yang sama
            $sql = "INSERT INTO sites (user_id, domain, site_name, tagline, meta_description, ad_header, ad_between_grid, theme)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                      user_id = VALUES(user_id),
                      site_name = VALUES(site_name),
                      tagline = VALUES(tagline),
                      meta_description = VALUES(meta_description),
                      ad_header = VALUES(ad_header),
                      ad_between_grid = VALUES(ad_between_grid),
                      theme = VALUES(theme)";
            $stmt = $mysqli->prepare($sql);
            if ($stmt) {
                $stmt->bind_param(
                    "isssssss",
                    $user_id,
                    $domain,
                    $site_name,
                    $tagline,
                    $meta_description,
                    $ad_header,
                    $ad_between_grid,
                    $theme
                );
                if ($stmt->execute()) {
                    $info = 'Site saved successfully.';
                    log_activity($CURRENT_USER['id'], 'save_site', "domain=$domain,theme=$theme");
                } else {
                    $error = 'Failed to save site. Maybe this domain is used by another user.';
                }
                $stmt->close();
            } else {
                $error = 'Prepare statement error.';
            }
        }
    }
}

$sites = get_sites_by_user($CURRENT_USER['id']);
?>

<h1 class="text-xl font-semibold mb-4">My Sites</h1>

<?php if (!$IS_PREMIUM): ?>
  <div class="alert alert-warning mb-4">
    <div class="alert-icon">
      <i class="fa-solid fa-lock"></i>
    </div>
    <div>
      <div class="alert-title">Premium required</div>
      <div class="alert-text">
        You need a <b>Premium</b> account to add domains and Adsterra ads.
        Go to <a href="/admin/billing" class="underline text-yellow-300">Billing / Upgrade</a>.
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- DNS GUIDE -->
<div class="alert alert-info mb-4">
  <div class="alert-icon">
    <i class="fa-solid fa-globe"></i>
  </div>
  <div>
    <div class="alert-title">How to connect your domain</div>
    <div class="alert-text">
      <p class="mb-1">To connect your domain to this TMDB AGC site:</p>
      <ol class="list-decimal list-inside space-y-1">
        <li>Open your domain provider (Cloudflare, Namecheap, Niagahoster, etc).</li>
        <li>Go to <b>DNS Management</b>.</li>
        <li>
          Create an <b>A Record</b>:
          <span class="inline-block bg-slate-800 px-2 py-0.5 rounded text-[11px]">
            Type: A &middot; Name: @ &middot; Value: <b>YOUR_SERVER_IP</b>
          </span>
        </li>
        <li>Wait a few minutes until DNS propagates (usually 1–15 minutes).</li>
        <li>Then add your domain in the form below (without http/https).</li>
      </ol>
      <p class="mt-2 text-[11px] text-slate-400">
        If you use a subdomain (e.g. <code>drama.yourdomain.com</code>), create an A record for that subdomain.
      </p>
    </div>
  </div>
</div>

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

<form method="post" class="space-y-4 max-w-2xl" id="siteForm">
  <?php csrf_field(); ?>

  <fieldset <?= $IS_PREMIUM ? '' : 'disabled' ?> class="<?= $IS_PREMIUM ? '' : 'opacity-50 cursor-not-allowed' ?>">
    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs mb-1">Domain</label>
        <input type="text" name="domain"
               placeholder="example.com"
               class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs mb-1">Site name</label>
        <input type="text" name="site_name"
               placeholder="My TMDB Site"
               class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm">
      </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs mb-1">Tagline</label>
        <input type="text" name="tagline"
               placeholder="Watch latest movies and series"
               class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-xs mb-1">Meta description</label>
        <input type="text" name="meta_description"
               placeholder="SEO description for your site"
               class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm">
      </div>
    </div>

    <div class="grid md:grid-cols-3 gap-4">
      <div>
        <label class="block text-xs mb-1">Theme</label>
        <select name="theme"
                class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm">
          <option value="classic">Classic Grid</option>
          <option value="cinema">Cinema Hero</option>
          <option value="cards">Big Cards</option>
        </select>
      </div>
    </div>

    <div>
      <label class="block text-xs mb-1">Ad script (header)</label>
      <textarea name="ad_header" rows="3"
                placeholder="Paste Adsterra header code here"
                class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-xs"></textarea>
    </div>

    <div>
      <label class="block text-xs mb-1">Ad script (between grid)</label>
      <textarea name="ad_between_grid" rows="3"
                placeholder="Paste Adsterra inline code here"
                class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-xs"></textarea>
    </div>

    <button type="submit"
            class="bg-indigo-600 hover:bg-indigo-500 rounded px-4 py-2 text-sm font-medium">
      Save Site
    </button>
  </fieldset>
</form>

<h2 class="text-lg font-semibold mt-8 mb-3">Your Sites</h2>

<div class="overflow-x-auto">
  <table class="min-w-full text-xs border border-slate-800">
    <thead class="bg-slate-900/80">
      <tr>
        <th class="px-2 py-2 border-b border-slate-800 text-left">Domain</th>
        <th class="px-2 py-2 border-b border-slate-800 text-left">Site name</th>
        <th class="px-2 py-2 border-b border-slate-800 text-left">Theme</th>
        <th class="px-2 py-2 border-b border-slate-800 text-left">Status</th>
        <th class="px-2 py-2 border-b border-slate-800 text-left">Created</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($sites as $site): ?>
        <tr class="hover:bg-slate-900/60">
          <td class="px-2 py-1 border-b border-slate-800">
            <?= htmlspecialchars($site['domain']) ?>
          </td>
          <td class="px-2 py-1 border-b border-slate-800">
            <?= htmlspecialchars($site['site_name']) ?>
          </td>
          <td class="px-2 py-1 border-b border-slate-800">
            <?= htmlspecialchars($site['theme']) ?>
          </td>
          <td class="px-2 py-1 border-b border-slate-800">
            <?= htmlspecialchars($site['status']) ?>
          </td>
          <td class="px-2 py-1 border-b border-slate-800 text-slate-400">
            <?= htmlspecialchars($site['created_at']) ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($sites)): ?>
        <tr>
          <td colspan="5" class="px-2 py-4 text-center text-slate-500">
            You have no sites yet.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
