<?php
$page_title = "Dashboard";
include __DIR__ . '/partials/header.php';

$CURRENT_USER = current_user();
$user_sites   = get_sites_by_user($CURRENT_USER['id']);

$selected_site_id = null;
$selected_site    = null;

if (!empty($_GET['site_id'])) {
    $selected_site_id = (int)$_GET['site_id'];
} elseif (!empty($user_sites)) {
    $selected_site_id = (int)$user_sites[0]['id'];
}

if ($selected_site_id) {
    foreach ($user_sites as $s) {
        if ((int)$s['id'] === $selected_site_id) {
            $selected_site = $s;
            break;
        }
    }
}

$stats = ['total' => 0, 'today' => 0, 'last7days' => 0];
if ($selected_site) {
    $stats = get_site_stats_summary($selected_site['id']);
}

// chart per user
$userChart = get_user_traffic_and_income_last7days($CURRENT_USER['id']);

// chart global khusus admin
$systemChart = null;
if ($CURRENT_USER['role'] === 'admin') {
    $systemChart = get_system_traffic_and_income_last7days();
}
?>

<h1 class="text-xl font-semibold mb-4 flex items-center gap-2">
  <i class="fa-solid fa-chart-simple text-indigo-300"></i> Dashboard
</h1>

<!-- CUSTOM ALERT PREMIUM -->
<?php if ($CURRENT_USER['status'] !== 'premium'): ?>
  <div class="alert alert-warning mb-4">
    <div class="alert-icon">
      <i class="fa-solid fa-lock"></i>
    </div>
    <div>
      <div class="alert-title">You are using FREE plan</div>
      <div class="alert-text">
        Upgrade to <b>Premium</b> to unlock domain &amp; Adsterra settings and increase your TMDB AGC earnings.
        Go to <a href="/admin/billing" class="underline text-yellow-300">Billing / Upgrade</a>.
      </div>
    </div>
  </div>
<?php else: ?>
  <div class="alert alert-success mb-4">
    <div class="alert-icon">
      <i class="fa-solid fa-crown"></i>
    </div>
    <div>
      <div class="alert-title">Premium account active</div>
      <div class="alert-text">
        All features are unlocked. Manage your domains, Adsterra ads, and track your traffic &amp; income from this panel.
      </div>
    </div>
  </div>
<?php endif; ?>

<?php if (empty($user_sites)): ?>
  <div class="alert alert-info mb-4">
    <div class="alert-icon">
      <i class="fa-solid fa-globe"></i>
    </div>
    <div>
      <div class="alert-title">No site configured yet</div>
      <div class="alert-text">
        Create your first TMDB AGC site in <a href="/admin/sites" class="underline text-indigo-300">My Sites</a>
        and point your domain to this server.
      </div>
    </div>
  </div>
<?php else: ?>

  <!-- SITE SELECTOR -->
  <div class="mb-4 flex flex-wrap gap-3 items-center text-sm">
    <span class="text-slate-400">Site:</span>
    <form method="get" id="siteSelector">
      <select name="site_id"
              class="bg-slate-900 border border-slate-700 rounded px-2 py-1 text-sm text-slate-100">
        <?php foreach ($user_sites as $s): ?>
          <option value="<?= (int)$s['id'] ?>" <?= $selected_site_id == $s['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($s['domain']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>

  <script>
    $(function () {
      $('#siteSelector select').on('change', function () {
        $('#siteSelector').submit();
      });
    });
  </script>

  <?php if ($selected_site): ?>
    <div class="grid md:grid-cols-3 gap-4 mb-6">
      <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 card-soft">
        <div class="text-xs text-slate-400 mb-1 flex items-center gap-1">
          <i class="fa-solid fa-eye text-slate-500"></i> Total Page Views
        </div>
        <div class="text-2xl font-bold"><?= $stats['total'] ?></div>
      </div>
      <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 card-soft">
        <div class="text-xs text-slate-400 mb-1 flex items-center gap-1">
          <i class="fa-solid fa-calendar-day text-slate-500"></i> Today
        </div>
        <div class="text-2xl font-bold"><?= $stats['today'] ?></div>
      </div>
      <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 card-soft">
        <div class="text-xs text-slate-400 mb-1 flex items-center gap-1">
          <i class="fa-solid fa-calendar-week text-slate-500"></i> Last 7 Days
        </div>
        <div class="text-2xl font-bold"><?= $stats['last7days'] ?></div>
      </div>
    </div>
  <?php endif; ?>

<?php endif; ?>

<!-- CHART TRAFFIC & INCOME UNTUK USER -->
<section class="mt-4 chart-card p-4 md:p-5">
  <div class="flex items-center justify-between mb-3">
    <h2 class="text-sm font-semibold flex items-center gap-2">
      <i class="fa-solid fa-wave-square text-indigo-200 text-xs"></i>
      Your Traffic &amp; Income (last 7 days)
    </h2>
  </div>
  <canvas id="userTrafficIncomeChart" height="90"></canvas>
</section>

<script>
  $(function () {
    const ctx = document.getElementById('userTrafficIncomeChart').getContext('2d');
    const labels  = <?= json_encode($userChart['labels']) ?>;
    const traffic = <?= json_encode($userChart['traffic']) ?>;
    const income  = <?= json_encode($userChart['income']) ?>;

    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Page Views',
            data: traffic,
            borderWidth: 2,
            tension: 0.3,
            borderColor: '#60a5fa',
            backgroundColor: 'rgba(37, 99, 235, 0.18)',
            pointRadius: 2,
            yAxisID: 'y'
          },
          {
            label: 'Income (IDR)',
            data: income,
            borderWidth: 2,
            tension: 0.3,
            borderColor: '#34d399',
            backgroundColor: 'rgba(16, 185, 129, 0.12)',
            pointRadius: 2,
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            position: 'left',
            grid: { color: 'rgba(30, 64, 175, 0.15)' },
            ticks: { color: '#94a3b8', font: { size: 10 } }
          },
          y1: {
            position: 'right',
            grid: { drawOnChartArea: false },
            ticks: { color: '#a7f3d0', font: { size: 10 } }
          },
          x: {
            ticks: { color: '#64748b', font: { size: 10 } },
            grid: { color: 'transparent' }
          }
        },
        plugins: {
          legend: {
            labels: { color: '#e5e7eb', font: { size: 11 } }
          }
        }
      }
    });
  });
</script>

<?php if ($CURRENT_USER['role'] === 'admin' && $systemChart): ?>
  <section class="mt-6 chart-card p-4 md:p-5">
    <div class="flex items-center justify-between mb-3">
      <h2 class="text-sm font-semibold flex items-center gap-2">
        <i class="fa-solid fa-globe text-indigo-200 text-xs"></i>
        System Traffic &amp; Income (last 7 days)
      </h2>
    </div>
    <canvas id="systemTrafficIncomeChart" height="90"></canvas>
  </section>

  <script>
    $(function () {
      const ctx2 = document.getElementById('systemTrafficIncomeChart').getContext('2d');
      const labels  = <?= json_encode($systemChart['labels']) ?>;
      const traffic = <?= json_encode($systemChart['traffic']) ?>;
      const income  = <?= json_encode($systemChart['income']) ?>;

      new Chart(ctx2, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [
            {
              label: 'Total Page Views',
              data: traffic,
              borderWidth: 2,
              tension: 0.3,
              borderColor: '#f97316',
              backgroundColor: 'rgba(249, 115, 22, 0.18)',
              pointRadius: 2,
              yAxisID: 'y'
            },
            {
              label: 'Total Income (IDR)',
              data: income,
              borderWidth: 2,
              tension: 0.3,
              borderColor: '#22c55e',
              backgroundColor: 'rgba(34, 197, 94, 0.14)',
              pointRadius: 2,
              yAxisID: 'y1'
            }
          ]
        },
        options: {
          responsive: true,
          scales: {
            y: {
              position: 'left',
              grid: { color: 'rgba(30, 64, 175, 0.15)' },
              ticks: { color: '#94a3b8', font: { size: 10 } }
            },
            y1: {
              position: 'right',
              grid: { drawOnChartArea: false },
              ticks: { color: '#a7f3d0', font: { size: 10 } }
            },
            x: {
              ticks: { color: '#64748b', font: { size: 10 } },
              grid: { color: 'transparent' }
            }
          },
          plugins: {
            legend: {
              labels: { color: '#e5e7eb', font: { size: 11 } }
            }
          }
        }
      });
    });
  </script>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
