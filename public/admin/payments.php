<?php
$page_title = "Payment Methods";
include __DIR__ . '/partials/header.php';

require_admin();

$info = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();

    $method          = $_POST['method'] ?? '';
    $receiver_name   = trim($_POST['receiver_name'] ?? '');
    $receiver_number = trim($_POST['receiver_number'] ?? '');
    $extra_info      = trim($_POST['extra_info'] ?? '');

    if (!$method || !$receiver_name || !$receiver_number) {
        $error = 'Method, receiver name and number are required.';
    } else {
        $sql = "INSERT INTO payment_settings (method, receiver_name, receiver_number, extra_info)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    receiver_name = VALUES(receiver_name),
                    receiver_number = VALUES(receiver_number),
                    extra_info = VALUES(extra_info)";
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssss", $method, $receiver_name, $receiver_number, $extra_info);
            if ($stmt->execute()) {
                $info = 'Payment method saved.';
                log_activity(current_user_id(), 'save_payment_method', "method=$method");
            } else {
                $error = 'Failed to save payment method.';
            }
            $stmt->close();
        } else {
            $error = 'Prepare statement error.';
        }
    }
}

$methods = get_payment_settings();
?>

<h1 class="text-xl font-semibold mb-4 flex items-center gap-2">
  <i class="fa-solid fa-money-check-dollar text-indigo-300"></i> Payment Methods
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

<form method="post" class="space-y-3 max-w-md mb-6">
  <?php csrf_field(); ?>
  <div>
    <label class="block text-xs mb-1">Method</label>
    <select name="method"
            class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm">
      <option value="qris">QRIS</option>
      <option value="bank">Bank Transfer</option>
      <option value="ewallet">eWallet</option>
    </select>
  </div>
  <div>
    <label class="block text-xs mb-1">Receiver Name</label>
    <input type="text" name="receiver_name"
           class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm">
  </div>
  <div>
    <label class="block text-xs mb-1">Receiver Number / ID</label>
    <input type="text" name="receiver_number"
           class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm">
  </div>
  <div>
    <label class="block text-xs mb-1">Extra Info (optional)</label>
    <textarea name="extra_info" rows="2"
              class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-xs"></textarea>
  </div>
  <button type="submit"
          class="bg-indigo-600 hover:bg-indigo-500 rounded px-4 py-2 text-sm font-medium">
    Save
  </button>
</form>

<h2 class="text-lg font-semibold mb-2">Configured Methods</h2>
<div class="overflow-x-auto">
  <table class="min-w-full text-xs border border-slate-800">
    <thead class="bg-slate-900/80">
      <tr>
        <th class="px-2 py-2 border-b border-slate-800 text-left">Method</th>
        <th class="px-2 py-2 border-b border-slate-800 text-left">Receiver</th>
        <th class="px-2 py-2 border-b border-slate-800 text-left">Number</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($methods as $m): ?>
        <tr class="hover:bg-slate-900/60">
          <td class="px-2 py-1 border-b border-slate-800"><?= htmlspecialchars(strtoupper($m['method'])) ?></td>
          <td class="px-2 py-1 border-b border-slate-800"><?= htmlspecialchars($m['receiver_name']) ?></td>
          <td class="px-2 py-1 border-b border-slate-800"><?= htmlspecialchars($m['receiver_number']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($methods)): ?>
        <tr>
          <td colspan="3" class="px-2 py-3 text-center text-slate-500">
            No methods configured.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
