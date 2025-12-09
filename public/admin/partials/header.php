<?php
require_once __DIR__ . '/../../../config.php';
require_login();

$CURRENT_USER = current_user();
$IS_PREMIUM   = is_premium($CURRENT_USER);
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
      $(window).on('load', function () {
        setTimeout(function () {
          $('.page-loader').addClass('hidden');
        }, 200);
      });
    });
  </script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex">

<div class="page-loader">
  <div class="flex flex-col items-center gap-3">
    <div class="loader-spinner"></div>
    <div class="text-[10px] tracking-[0.3em] uppercase text-slate-300">
      Loading panel
    </div>
  </div>
</div>

<aside class="w-60 bg-slate-900 border-r border-slate-800 hidden md:flex flex-col">
  <div class="px-4 py-4 border-b border-slate-800">
    <div class="font-bold text-lg text-indigo-400 flex items-center gap-2">
      <i class="fa-solid fa-gauge-high text-indigo-300"></i> Control Panel
    </div>
    <div class="text-[11px] text-slate-400 mt-1">
      <?= htmlspecialchars($CURRENT_USER['username']) ?> ·
      <span class="<?= $IS_PREMIUM ? 'text-emerald-400' : 'text-yellow-400' ?>">
        <?= strtoupper($CURRENT_USER['status']) ?>
      </span>
    </div>
  </div>
  <nav class="flex-1 px-2 py-4 space-y-1 text-sm">
    <a href="/admin" class="block px-3 py-2 rounded hover:bg-slate-800 flex items-center gap-2">
      <i class="fa-solid fa-chart-simple text-slate-400 text-xs"></i> Dashboard
    </a>
    <a href="/admin/sites" class="block px-3 py-2 rounded hover:bg-slate-800 flex items-center gap-2">
      <i class="fa-solid fa-globe text-slate-400 text-xs"></i> My Sites
    </a>
    <a href="/admin/billing" class="block px-3 py-2 rounded hover:bg-slate-800 flex items-center gap-2">
      <i class="fa-solid fa-crown text-slate-400 text-xs"></i> Billing / Upgrade
    </a>

    <?php if ($CURRENT_USER['role'] === 'admin'): ?>
      <div class="mt-4 text-[10px] uppercase tracking-[0.15em] text-slate-500">
        Admin
      </div>
      <a href="/admin/global-settings" class="block px-3 py-2 rounded hover:bg-slate-800 flex items-center gap-2">
        <i class="fa-solid fa-sliders text-slate-400 text-xs"></i> Global Settings
      </a>
      <a href="/admin/payments" class="block px-3 py-2 rounded hover:bg-slate-800 flex items-center gap-2">
        <i class="fa-solid fa-money-check-dollar text-slate-400 text-xs"></i> Payment Methods
      </a>
      <a href="/admin/orders" class="block px-3 py-2 rounded hover:bg-slate-800 flex items-center gap-2">
        <i class="fa-solid fa-receipt text-slate-400 text-xs"></i> Orders
      </a>
      <a href="/admin/users" class="block px-3 py-2 rounded hover:bg-slate-800 flex items-center gap-2">
        <i class="fa-solid fa-users-gear text-slate-400 text-xs"></i> Users
      </a>
      <a href="/admin/activity" class="block px-3 py-2 rounded hover:bg-slate-800 flex items-center gap-2">
        <i class="fa-solid fa-list-check text-slate-400 text-xs"></i> Activity Log
      </a>
    <?php endif; ?>
  </nav>
  <div class="px-4 py-4 border-t border-slate-800 text-xs text-slate-500">
    <a href="/logout" class="hover:text-red-400 flex items-center gap-1">
      <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
    </a>
  </div>
</aside>

<div class="flex-1 flex flex-col">
  <header class="md:hidden bg-slate-900 border-b border-slate-800 px-4 py-3 flex items-center justify-between">
    <div class="font-semibold text-sm flex items-center gap-2">
      <i class="fa-solid fa-gauge-high text-indigo-300"></i> Panel
    </div>
    <a href="/logout" class="text-xs text-red-400">Logout</a>
  </header>
  <main class="flex-1 p-4">
