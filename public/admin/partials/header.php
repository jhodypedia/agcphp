<?php
// public/admin/partials/header.php
require_once __DIR__ . '/../../../config.php';
require_login();

$CURRENT_USER = current_user();
$IS_PREMIUM   = is_premium($CURRENT_USER);

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
function nav_active($pattern, $currentPath) {
    return preg_match($pattern, $currentPath) ? 'bg-slate-800 text-slate-50' : 'text-slate-300 hover:bg-slate-800';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($page_title ?? "Dashboard - $SITE_NAME") ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="/assets/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
    $(function () {
      // Page loader
      $(window).on('load', function () {
        setTimeout(function () {
          $('.page-loader').addClass('hidden');
        }, 200);
      });

      // Mobile sidebar
      $('#btnSidebarOpen').on('click', function () {
        $('#sidebarDrawer').removeClass('translate-x-[-100%]').addClass('translate-x-0');
        $('#sidebarBackdrop').removeClass('hidden');
      });
      $('#btnSidebarClose, #sidebarBackdrop').on('click', function () {
        $('#sidebarDrawer').removeClass('translate-x-0').addClass('translate-x-[-100%]');
        $('#sidebarBackdrop').addClass('hidden');
      });
    });
  </script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex">

<!-- GLOBAL LOADER -->
<div class="page-loader">
  <div class="flex flex-col items-center gap-3">
    <div class="loader-spinner"></div>
    <div class="text-[10px] tracking-[0.3em] uppercase text-slate-300">
      Loading panel
    </div>
  </div>
</div>

<!-- MOBILE BACKDROP -->
<div id="sidebarBackdrop"
     class="hidden fixed inset-0 bg-black/50 z-30 md:hidden"></div>

<!-- SIDEBAR (desktop) + DRAWER (mobile) -->
<aside id="sidebarDrawer"
       class="fixed md:static z-40 inset-y-0 left-0 w-64 bg-slate-900 border-r border-slate-800 flex flex-col transform translate-x-[-100%] md:translate-x-0 transition-transform duration-200 ease-out">

  <!-- TOP BRAND -->
  <div class="px-4 py-4 border-b border-slate-800 flex items-center justify-between">
    <div>
      <div class="font-bold text-lg text-indigo-400 flex items-center gap-2">
        <i class="fa-solid fa-gauge-high text-indigo-300"></i>
        Panel
      </div>
      <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-2">
        <span><?= htmlspecialchars($CURRENT_USER['username']) ?></span>
        <span class="w-1 h-1 rounded-full bg-slate-600"></span>
        <span class="<?= $IS_PREMIUM ? 'text-emerald-400' : 'text-yellow-400' ?>">
          <?= strtoupper($CURRENT_USER['status']) ?>
        </span>
      </div>
    </div>

    <!-- Close btn (mobile) -->
    <button id="btnSidebarClose"
            class="md:hidden inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-800 text-slate-300">
      <i class="fa-solid fa-xmark text-sm"></i>
    </button>
  </div>

  <!-- NAV -->
  <nav class="flex-1 px-2 py-4 space-y-1 text-sm overflow-y-auto">
    <a href="/admin"
       class="flex items-center gap-2 px-3 py-2 rounded <?= nav_active('#^/admin/?$#', $currentPath) ?>">
      <i class="fa-solid fa-chart-line text-xs"></i>
      <span>Dashboard</span>
    </a>

    <a href="/admin/sites"
       class="flex items-center gap-2 px-3 py-2 rounded <?= nav_active('#^/admin/sites#', $currentPath) ?>">
      <i class="fa-solid fa-globe text-xs"></i>
      <span>My Sites</span>
    </a>

    <a href="/admin/billing"
       class="flex items-center gap-2 px-3 py-2 rounded <?= nav_active('#^/admin/billing#', $currentPath) ?>">
      <i class="fa-solid fa-crown text-xs"></i>
      <span>Billing / Upgrade</span>
    </a>

    <a href="/admin/activity"
       class="flex items-center gap-2 px-3 py-2 rounded <?= nav_active('#^/admin/activity#', $currentPath) ?>">
      <i class="fa-solid fa-list-check text-xs"></i>
      <span>Activity Log</span>
    </a>

    <?php if ($CURRENT_USER['role'] === 'admin'): ?>
      <div class="mt-4 text-[10px] uppercase tracking-[0.15em] text-slate-500 px-3">
        Admin Controls
      </div>

      <a href="/admin/global-settings"
         class="flex items-center gap-2 px-3 py-2 rounded <?= nav_active('#^/admin/global-settings#', $currentPath) ?>">
        <i class="fa-solid fa-sliders text-xs"></i>
        <span>Global Settings</span>
      </a>

      <a href="/admin/payments"
         class="flex items-center gap-2 px-3 py-2 rounded <?= nav_active('#^/admin/payments#', $currentPath) ?>">
        <i class="fa-solid fa-money-check-dollar text-xs"></i>
        <span>Payment Methods</span>
      </a>

      <a href="/admin/orders"
         class="flex items-center gap-2 px-3 py-2 rounded <?= nav_active('#^/admin/orders#', $currentPath) ?>">
        <i class="fa-solid fa-receipt text-xs"></i>
        <span>Orders</span>
      </a>

      <a href="/admin/users"
         class="flex items-center gap-2 px-3 py-2 rounded <?= nav_active('#^/admin/users#', $currentPath) ?>">
        <i class="fa-solid fa-users-gear text-xs"></i>
        <span>Users</span>
      </a>
    <?php endif; ?>
  </nav>

  <!-- FOOTER -->
  <div class="px-4 py-4 border-t border-slate-800 text-xs text-slate-500 flex items-center justify-between">
    <span class="hidden md:inline-block">&copy; <?= date('Y') ?> <?= htmlspecialchars($SITE_NAME) ?></span>
    <a href="/logout" class="hover:text-red-400 flex items-center gap-1">
      <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
    </a>
  </div>
</aside>

<!-- MAIN AREA -->
<div class="flex-1 flex flex-col min-h-screen md:ml-0">

  <!-- TOPBAR (mobile + desktop) -->
  <header class="bg-slate-900/90 border-b border-slate-800 px-4 py-3 flex items-center justify-between sticky top-0 z-20">
    <div class="flex items-center gap-3">
      <button id="btnSidebarOpen"
              class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-full bg-slate-800 text-slate-100">
        <i class="fa-solid fa-bars-staggered text-sm"></i>
      </button>
      <div class="flex flex-col">
        <span class="text-xs text-slate-400 uppercase tracking-[0.18em]">TMDB AGC Panel</span>
        <span class="text-sm font-semibold">
          <?= htmlspecialchars($page_title ?? 'Dashboard') ?>
        </span>
      </div>
    </div>

    <div class="flex items-center gap-3 text-xs">
      <span class="hidden sm:flex items-center gap-2 px-2 py-1 rounded-full bg-slate-800/80 text-slate-300">
        <i class="fa-solid fa-user-circle"></i>
        <span><?= htmlspecialchars($CURRENT_USER['username']) ?></span>
      </span>
      <span class="px-2 py-1 rounded-full text-[10px] <?= $IS_PREMIUM ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/40' : 'bg-yellow-500/15 text-yellow-300 border border-yellow-500/40' ?>">
        <?= $IS_PREMIUM ? 'PREMIUM' : 'FREE' ?>
      </span>
    </div>
  </header>

  <main class="flex-1 p-4 md:p-6">
