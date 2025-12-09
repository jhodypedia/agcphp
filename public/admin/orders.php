<?php
$page_title = "Orders";
include __DIR__ . '/partials/header.php';

require_admin();

$info = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_paid'])) {
    csrf_verify_or_die();

    $order_id = (int)($_POST['order_id'] ?? 0);
    if ($order_id > 0) {
        $sql = "SELECT user_id, status FROM payment_orders WHERE id = ? LIMIT 1";
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $stmt->bind_result($uid, $status);
            if ($stmt->fetch()) {
                $stmt->close();
                if ($status !== 'paid') {
                    $sql1 = "UPDATE payment_orders SET status = 'paid', paid_at = NOW() WHERE id = ?";
                    $st1 = $mysqli->prepare($sql1);
                    if ($st1) {
                        $st1->bind_param("i", $order_id);
                        $st1->execute();
                        $st1->close();
                    }

                    $sql2 = "UPDATE users SET status = 'premium' WHERE id = ?";
                    $st2 = $mysqli->prepare($sql2);
                    if ($st2) {
                        $st2->bind_param("i", $uid);
                        $st2->execute();
                        $st2->close();
                    }

                    $info = "Order #$order_id has been marked as paid and user set to PREMIUM.";
                    log_activity(current_user_id(), 'mark_order_paid', "order_id=$order_id,user_id=$uid");
                } else {
                    $info = "Order #$order_id is already paid.";
                }
            } else {
                $stmt->close();
                $error = "Order not found.";
            }
        } else {
            $error = "Prepare statement error.";
        }
    } else {
        $error = "Invalid order id.";
    }
}

$sql = "SELECT o.*, u.username, u.email
        FROM payment_orders o
        JOIN users u ON u.id = o.user_id
        ORDER BY o.created_at DESC
        LIMIT 100";
$res = $mysqli->query($sql);
$orders = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>

<h1 class="text-xl font-semibold mb-4 flex items-center gap-2">
  <i class="fa-solid fa-receipt text-indigo-300"></i> Orders
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
        <th class="px-2 py-2 border-b border-slate-800 text-left">Method</th>
        <th class="px-2 py-2 border-b border-slate-800 text-right">Base</th>
        <th class="px-2 py-2 border-b border-slate-800 text-right">Final</th>
        <th class="px-2 py-2 border-b border-slate-800 text-left">Status</th>
        <th class="px-2 py-2 border-b border-slate-800 text-left">Created</th>
        <th class="px-2 py-2 border-b border-slate-800 text-left">Paid</th>
        <th class="px-2 py-2 border-b border-slate-800 text-center">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
        <tr class="hover:bg-slate-900/60">
          <td class="px-2 py-1 border-b border-slate-800">#<?= (int)$o['id'] ?></td>
          <td class="px-2 py-1 border-b border-slate-800">
            <div class="font-semibold"><?= htmlspecialchars($o['username']) ?></div>
            <div class="text-[10px] text-slate-400"><?= htmlspecialchars($o['email']) ?></div>
          </td>
          <td class="px-2 py-1 border-b border-slate-800 uppercase"><?= htmlspecialchars($o['method']) ?></td>
          <td class="px-2 py-1 border-b border-slate-800 text-right text-slate-400">
            <?= number_format($o['base_amount'], 0, ',', '.') ?>
          </td>
          <td class="px-2 py-1 border-b border-slate-800 text-right text-emerald-300">
            <?= number_format($o['final_amount'], 0, ',', '.') ?>
          </td>
          <td class="px-2 py-1 border-b border-slate-800">
            <?php if ($o['status'] === 'paid'): ?>
              <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/15 text-emerald-300 px-2 py-0.5">
                <i class="fa-solid fa-circle-check text-[9px]"></i> Paid
              </span>
            <?php elseif ($o['status'] === 'pending'): ?>
              <span class="inline-flex items-center gap-1 rounded-full bg-yellow-500/15 text-yellow-300 px-2 py-0.5">
                <i class="fa-solid fa-clock text-[9px]"></i> Pending
              </span>
            <?php else: ?>
              <span class="inline-flex items-center gap-1 rounded-full bg-slate-500/15 text-slate-300 px-2 py-0.5">
                <i class="fa-solid fa-ban text-[9px]"></i> <?= htmlspecialchars($o['status']) ?>
              </span>
            <?php endif; ?>
          </td>
          <td class="px-2 py-1 border-b border-slate-800 text-slate-400">
            <?= htmlspecialchars($o['created_at']) ?>
          </td>
          <td class="px-2 py-1 border-b border-slate-800 text-slate-400">
            <?= $o['paid_at'] ? htmlspecialchars($o['paid_at']) : '-' ?>
          </td>
          <td class="px-2 py-1 border-b border-slate-800 text-center">
            <?php if ($o['status'] === 'pending'): ?>
              <form method="post"
                    onsubmit="return confirm('Mark this order as paid and upgrade user to PREMIUM?');">
                <?php csrf_field(); ?>
                <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                <button type="submit" name="mark_paid"
                        class="inline-flex items-center gap-1 text-[11px] bg-emerald-600 hover:bg-emerald-500 text-white px-2 py-1 rounded">
                  <i class="fa-solid fa-circle-check text-[9px]"></i> Mark paid
                </button>
              </form>
            <?php else: ?>
              <span class="text-[10px] text-slate-500">-</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($orders)): ?>
        <tr>
          <td colspan="9" class="px-2 py-4 text-center text-slate-500">
            No orders yet.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
