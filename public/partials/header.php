<?php
// public/partials/header.php
$FRONT_USER   = current_user();
$is_logged_in = (bool)$FRONT_USER;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($page_title ?? $SITE_NAME) ?></title>
  <meta name="description" content="<?= htmlspecialchars($META_DESCRIPTION) ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link rel="canonical" href="<?= htmlspecialchars($BASE_URL . ($_SERVER['REQUEST_URI'] ?? '')) ?>" />

  <meta property="og:title" content="<?= htmlspecialchars($page_title ?? $SITE_NAME) ?>" />
  <meta property="og:description" content="<?= htmlspecialchars($META_DESCRIPTION) ?>" />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="<?= htmlspecialchars($BASE_URL . ($_SERVER['REQUEST_URI'] ?? '')) ?>" />

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="/assets/style.css">

  <script>
    $(function () {
      // global loader
      $(window).on('load', function () {
        setTimeout(function () {
          $('.page-loader').addClass('hidden');
        }, 250);
      });
    });
  </script>

  <?php
  if (!empty($AD_HEADER)) {
      echo $AD_HEADER;
  }
  ?>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col">

<div class="page-loader">
  <div class="flex flex-col items-center gap-3">
    <div class="loader-spinner"></div>
    <div class="text-[11px] tracking-[0.25em] uppercase text-slate-300">Loading</div>
  </div>
</div>

<!-- TOP NAVBAR -->
<header class="border-b border-slate-800 bg-slate-900/90 backdrop-blur z-20 sticky top-0">
  <div class="max-w-6xl mx-auto px-4 py-3 flex items-center gap-3">
    <!-- Brand -->
    <a href="<?= $BASE_URL ?>/"
       class="font-bold text-lg md:text-xl tracking-tight text-indigo-400 flex items-center gap-2">
      <i class="fa-solid fa-clapperboard text-indigo-300"></i>
      <span class="hidden sm:inline"><?= htmlspecialchars($SITE_NAME) ?></span>
      <span class="sm:hidden">TMDB</span>
    </a>

    <!-- Main nav -->
    <nav class="hidden md:flex items-center gap-4 text-sm ml-4">
      <a href="<?= $BASE_URL ?>/"
         class="text-slate-300 hover:text-indigo-400 flex items-center gap-1">
        <i class="fa-solid fa-house-chimney text-xs"></i> Home
      </a>
      <a href="<?= $BASE_URL ?>/search"
         class="text-slate-300 hover:text-indigo-400 flex items-center gap-1">
        <i class="fa-solid fa-magnifying-glass text-xs"></i> Explore
      </a>
      <a href="<?= $BASE_URL ?>/sitemap.xml"
         class="text-slate-400 hover:text-indigo-300 text-xs flex items-center gap-1">
        <i class="fa-solid fa-sitemap text-[11px]"></i> Sitemap
      </a>
    </nav>

    <!-- Search -->
    <form action="<?= $BASE_URL ?>/search" method="get" class="ml-auto flex-1 max-w-sm">
      <div class="relative">
        <span class="absolute inset-y-0 left-3 flex items-center text-slate-500 text-xs">
          <i class="fa-solid fa-magnifying-glass"></i>
        </span>
        <input
          type="text"
          name="q"
          value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>"
          placeholder="Search movies..."
          class="w-full bg-slate-800/80 border border-slate-700 text-sm rounded-full pl-8 pr-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />
      </div>
    </form>

    <!-- Auth / Dashboard buttons -->
    <div class="ml-3 flex items-center gap-2 text-xs">
      <?php if ($is_logged_in): ?>
        <a href="<?= $BASE_URL ?>/admin"
           class="hidden sm:inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-indigo-600 hover:bg-indigo-500">
          <i class="fa-solid fa-gauge-high text-[11px]"></i> Dashboard
        </a>
        <a href="<?= $BASE_URL ?>/logout"
           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-slate-800 hover:bg-slate-700">
          <i class="fa-solid fa-arrow-right-from-bracket text-[11px]"></i>
          <span class="hidden sm:inline">Logout</span>
        </a>
      <?php else: ?>
        <a href="<?= $BASE_URL ?>/login"
           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-indigo-600 hover:bg-indigo-500">
          <i class="fa-solid fa-right-to-bracket text-[11px]"></i> Login
        </a>
        <a href="<?= $BASE_URL ?>/register"
           class="hidden sm:inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-slate-800 hover:bg-slate-700">
          <i class="fa-solid fa-user-plus text-[11px]"></i> Register
        </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Bottom nav (mobile) -->
  <nav class="md:hidden border-t border-slate-800 bg-slate-900/95">
    <div class="max-w-6xl mx-auto px-4 py-2 flex items-center justify-between text-[11px] text-slate-300">
      <a href="<?= $BASE_URL ?>/"
         class="flex flex-col items-center gap-1">
        <i class="fa-solid fa-house-chimney"></i>
        <span>Home</span>
      </a>
      <a href="<?= $BASE_URL ?>/search"
         class="flex flex-col items-center gap-1">
        <i class="fa-solid fa-magnifying-glass"></i>
        <span>Search</span>
      </a>
      <?php if ($is_logged_in): ?>
        <a href="<?= $BASE_URL ?>/admin"
           class="flex flex-col items-center gap-1">
          <i class="fa-solid fa-gauge-high"></i>
          <span>Panel</span>
        </a>
        <a href="<?= $BASE_URL ?>/logout"
           class="flex flex-col items-center gap-1">
          <i class="fa-solid fa-arrow-right-from-bracket"></i>
          <span>Logout</span>
        </a>
      <?php else: ?>
        <a href="<?= $BASE_URL ?>/login"
           class="flex flex-col items-center gap-1">
          <i class="fa-solid fa-right-to-bracket"></i>
          <span>Login</span>
        </a>
      <?php endif; ?>
    </div>
  </nav>
</header>

<main class="flex-1">
  <div class="max-w-6xl mx-auto px-4 py-5">
