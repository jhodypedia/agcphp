<?php
$page_title = "Activity Log";
include __DIR__ . '/partials/header.php';

$CURRENT_USER = current_user();

$info = '';
$error = '';

// Query logs
if ($CURRENT_USER['role'] === 'admin') {
    $sql = "SELECT l.*, u.username
            FROM activity_logs l
            JOIN users u ON u.id = l.user_id
            ORDER BY l.created_at DESC
            LIMIT 200";
    $res = $mysqli->query($sql);
} else {
    $uid = $CURRENT_USER['id'];
    $sql = "SELECT l.*, u.username
            FROM activity_logs l
            JOIN users u ON u.id = l.user_id
            WHERE l.user_id = ?
            ORDER BY l.created_at DESC
            LIMIT 200";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $res = $stmt->get_result();
}
$logs = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}
?>

<h1 class="text-xl font-semibold mb-4 flex items-center gap-2">
  <i class="fa-solid fa-list-check text-indigo-300"></i> Activity Log
</h1>

<p class="text-xs text-slate-400 mb-4">
  Showing latest <?= count($logs) ?> activity entries.
</p>

<div class="overflow-x-auto">
  <table class="min-w-full text-xs border border-slate-800">
    <thead class="bg-slate-900/80">
      <tr>
        <?php if ($CURRENT_USER['role'] === 'admin'): ?>
          <th class="px-2 py-2 border-b border-slate-800 text-left">User</th>
        <?php endif; ?>
        <th class="px-2 py-2 border-b border-slate-800 text-left">Action</th>
        <th class="px-2 py-2 border-b border-slate-800 text-left">Meta</th>
        <th class="px-2 py-2 border-b border-slate-800 text-left">IP</th>
        <th class="px-2 py-2 border-b border-slate-800 text-left">User Agent</th>
        <th class="px-2 py-2 border-b border-slate-800 text-left">Time</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($logs as $log): ?>
        <tr class="hover:bg-slate-900/60">
          <?php if ($CURRENT_USER['role'] === 'admin'): ?>
            <td class="px-2 py-1 border-b border-slate-800">
              <?= htmlspecialchars($log['username']) ?>
            </td>
          <?php endif; ?>
          <td class="px-2 py-1 border-b border-slate-800">
            <?= htmlspecialchars($log['action']) ?>
          </td>
          <td class="px-2 py-1 border-b border-slate-800 text-slate-300">
            <?= htmlspecialchars($log['meta'] ?? '') ?>
          </td>
          <td class="px-2 py-1 border-b border-slate-800 text-slate-400">
            <?= htmlspecialchars($log['ip_address'] ?? '') ?>
          </td>
          <td class="px-2 py-1 border-b border-slate-800 text-slate-500 max-w-xs truncate">
            <?= htmlspecialchars($log['user_agent'] ?? '') ?>
          </td>
          <td class="px-2 py-1 border-b border-slate-800 text-slate-400">
            <?= htmlspecialchars($log['created_at']) ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($logs)): ?>
        <tr>
          <td colspan="<?= $CURRENT_USER['role'] === 'admin' ? 6 : 5 ?>" class="px-2 py-4 text-center text-slate-500">
            No activity yet.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
