<?php
$page_title = "Users";
include __DIR__ . '/partials/header.php';

require_admin();

$info = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    csrf_verify_or_die();

    $user_id = (int)($_POST['user_id'] ?? 0);
    $status  = $_POST['status'] ?? 'free';
    $role    = $_POST['role'] ?? 'user';

    if ($user_id <= 0) {
        $error = 'Invalid user id.';
    } elseif (!in_array($status, ['free','premium'], true) || !in_array($role, ['user','admin'], true)) {
        $error = 'Invalid status or role.';
    } else {
        $sql = "UPDATE users SET status = ?, role = ? WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssi", $status, $role, $user_id);
            if ($stmt->execute()) {
                $info = 'User updated.';
                log_activity(current_user_id(), 'update_user', "target_id=$user_id,status=$status,role=$role");
            } else {
                $error = 'Failed to update user.';
            }
            $stmt->close();
        } else {
            $error = 'Prepare statement error.';
        }
    }
}

$res = $mysqli->query("SELECT id, username, email, nohp, status, role, created_at FROM users ORDER BY created_at DESC LIMIT 200");
$users = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>

<h1 class="text-xl font-semibold mb-4 flex items-center gap-2">
  <i class="fa-solid fa-users-gear text-indigo-300"></i> Users
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

<div class="overflow-x-auto">
  <table class="min-w-full text-xs border border-slate-800">
    <thead class="bg-slate-900/80">
      <tr>
        <th class="px-2 py-2 border-b border-slate-800 text-left">#</th>
        <th class="px-2 py-2 border-b border-slate-800 text-left">User</th>
        <th class="px-2 py-2 border-b border-slate-800 text-left">Status</th>
        <th class="px-2 py-2 border-b border-slate-800 text-left">Role</th>
        <th class="px-2 py-2 border-b border-slate-800 text-left">Created</th>
        <th class="px-2 py-2 border-b border-slate-800 text-center">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr class="hover:bg-slate-900/60">
          <td class="px-2 py-1 border-b border-slate-800">
            <?= (int)$u['id'] ?>
          </td>
          <td class="px-2 py-1 border-b border-slate-800">
            <div class="font-semibold"><?= htmlspecialchars($u['username']) ?></div>
            <div class="text-[10px] text-slate-400"><?= htmlspecialchars($u['email']) ?></div>
            <?php if (!empty($u['nohp'])): ?>
              <div class="text-[10px] text-slate-500"><?= htmlspecialchars($u['nohp']) ?></div>
            <?php endif; ?>
          </td>
          <td class="px-2 py-1 border-b border-slate-800">
            <?= htmlspecialchars(strtoupper($u['status'])) ?>
          </td>
          <td class="px-2 py-1 border-b border-slate-800">
            <?= htmlspecialchars(strtoupper($u['role'])) ?>
          </td>
          <td class="px-2 py-1 border-b border-slate-800 text-slate-400">
            <?= htmlspecialchars($u['created_at']) ?>
          </td>
          <td class="px-2 py-1 border-b border-slate-800 text-center">
            <form method="post" class="inline-flex items-center gap-1">
              <?php csrf_field(); ?>
              <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
              <select name="status"
                      class="bg-slate-900 border border-slate-700 rounded px-1 py-0.5 text-[11px]">
                <option value="free" <?= $u['status'] === 'free' ? 'selected' : '' ?>>FREE</option>
                <option value="premium" <?= $u['status'] === 'premium' ? 'selected' : '' ?>>PREMIUM</option>
              </select>
              <select name="role"
                      class="bg-slate-900 border border-slate-700 rounded px-1 py-0.5 text-[11px]">
                <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>USER</option>
                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>ADMIN</option>
              </select>
              <button type="submit" name="update_user"
                      class="bg-indigo-600 hover:bg-indigo-500 text-white text-[11px] px-2 py-0.5 rounded">
                Save
              </button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($users)): ?>
        <tr>
          <td colspan="6" class="px-2 py-4 text-center text-slate-500">
            No users.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
