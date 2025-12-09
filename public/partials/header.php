<?php
// public/partials/header.php
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

<header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-20">
  <div class="max-w-6xl mx-auto px-4 py-3 flex items-center gap-3">
    <a href="<?= $BASE_URL ?>/"
       class="font-bold text-lg md:text-xl tracking-tight text-indigo-400 flex items-center gap-2">
      <i class="fa-solid fa-clapperboard text-indigo-300"></i>
      <?= htmlspecialchars($SITE_NAME) ?>
    </a>
    <span class="text-xs text-slate-400 hidden sm:inline">
      <?= htmlspecialchars($SITE_TAGLINE) ?>
    </span>

    <form action="<?= $BASE_URL ?>/search" method="get" class="ml-auto flex-1 max-w-sm">
      <input
        type="text"
        name="q"
        value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>"
        placeholder="Search movies..."
        class="w-full bg-slate-800/80 border border-slate-700 text-sm rounded-full px-4 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      />
    </form>
  </div>
</header>

<main class="flex-1">
  <div class="max-w-6xl mx-auto px-4 py-5">
