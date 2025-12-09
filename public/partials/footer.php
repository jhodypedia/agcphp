<?php
// public/partials/footer.php
?>
  </div>
</main>

<footer class="border-t border-slate-800 bg-slate-900/80">
  <div class="max-w-6xl mx-auto px-4 py-4 text-xs text-slate-400 flex flex-col sm:flex-row gap-2 items-center justify-between">
    <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($SITE_NAME) ?>. All rights reserved.</p>
    <p>
      Data provided by
      <a href="https://www.themoviedb.org" target="_blank" rel="noopener"
         class="text-indigo-400 hover:text-indigo-300">TMDB</a>.
    </p>
  </div>
</footer>

</body>
</html>
