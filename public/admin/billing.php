<?php
$page_title = "Billing / Upgrade";
include __DIR__ . '/partials/header.php';

$CURRENT_USER = current_user();
$methods      = get_payment_settings();

$info = '';
$error = '';
$new_order = null;

$premium_price = (int)get_global_setting('premium_price_idr', 30000);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $method = $_POST['method'] ?? '';
    if (!$method) {
        $error = 'Please choose a payment method.';
    } else {
        $order = create_payment_order($CURRENT_USER['id'], $premium_price, $method);
        if ($order) {
            $new_order = $order;
            log_activity($CURRENT_USER['id'], 'create_order', "order_id={$order['id']},method=$method");
        } else {
            $error = 'Failed to create payment order.';
        }
    }
}

// last pending order
$sql = "SELECT * FROM payment_orders WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 1";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $CURRENT_USER['id']);
$stmt->execute();
$res = $stmt->get_result();
$pending = $res->fetch_assoc();
$stmt->close();
?>

<h1 class="text-xl font-semibold mb-4">Billing / Upgrade to Premium</h1>

<p class="text-sm text-slate-300 mb-4">
  Current status:
  <span class="font-semibold <?= $CURRENT_USER['status'] === 'premium' ? 'text-emerald-400' : 'text-yellow-400' ?>">
    <?= strtoupper($CURRENT_USER['status']) ?>
  </span>
</p>

<?php if ($error): ?>
  <div class="alert alert-danger mb-4">
    <div class="alert-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
    <div>
      <div class="alert-title">Error</div>
      <div class="alert-text"><?= htmlspecialchars($error) ?></div>
    </div>
  </div>
<?php endif; ?>

<?php if (!$methods): ?>
  <p class="text-slate-400 text-sm">Payment methods are not configured yet. Please contact admin.</p>
<?php else: ?>

  <div class="mb-4 p-3 border border-slate-700 bg-slate-900/60 rounded text-sm">
    <p class="mb-1">Premium price:
      <b>IDR <?= number_format($premium_price, 0, ',', '.') ?></b>
    </p>
    <p class="text-xs text-slate-400">
      Final transfer amount will be slightly unique (e.g., 30.123) for manual verification.
    </p>
  </div>

  <form method="post" class="space-y-3 max-w-md mb-6">
    <?php csrf_field(); ?>
    <div>
      <label class="block text-xs mb-1">Payment Method</label>
      <select name="method"
              class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm">
        <?php foreach ($methods as $m): ?>
          <option value="<?= htmlspecialchars($m['method']) ?>">
            <?= strtoupper($m['method']) ?> - <?= htmlspecialchars($m['receiver_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit"
            class="bg-indigo-600 hover:bg-indigo-500 rounded px-4 py-2 text-sm font-medium">
      Generate Payment Code
    </button>
  </form>

  <?php if ($new_order || $pending): ?>
    <?php $order = $new_order ?: $pending; ?>
    <?php
      $methodConfig = null;
      foreach ($methods as $m) {
          if ($m['method'] === $order['method']) {
              $methodConfig = $m;
              break;
          }
      }
    ?>
    <div class="border border-slate-700 bg-slate-900/70 rounded-xl p-4 text-sm">
      <h2 class="text-base font-semibold mb-2">Payment Instruction</h2>
      <p class="mb-1">
        Transfer exactly:
        <span class="font-bold text-emerald-400">
          IDR <?= number_format($order['final_amount'], 0, ',', '.') ?>
        </span>
      </p>
      <?php if ($methodConfig): ?>
        <p class="mb-1">
          Receiver: <b><?= htmlspecialchars($methodConfig['receiver_name']) ?></b><br>
          Number / ID: <b><?= htmlspecialchars($methodConfig['receiver_number']) ?></b>
        </p>
        <?php if (!empty($methodConfig['extra_info'])): ?>
          <p class="text-xs text-slate-400 mb-2">
            <?= nl2br(htmlspecialchars($methodConfig['extra_info'])) ?>
          </p>
        <?php endif; ?>
      <?php endif; ?>
      <p class="text-xs text-slate-400">
        After payment, please contact admin with the exact transfer amount for manual verification.
      </p>
    </div>
  <?php endif; ?>

<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
