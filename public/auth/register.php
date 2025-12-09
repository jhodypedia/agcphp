<?php
require_once __DIR__ . '/../../config.php';

if (current_user_id()) {
    header('Location: /admin');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $nohp     = trim($_POST['nohp'] ?? '');
    $pass     = $_POST['password'] ?? '';
    $pass2    = $_POST['password_confirm'] ?? '';

    if (!$username || !$email || !$pass || !$pass2) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif ($pass !== $pass2) {
        $error = 'Password confirmation does not match.';
    } else {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $sql  = "INSERT INTO users (username, email, nohp, password, status, role)
                 VALUES (?, ?, ?, ?, 'free', 'user')";
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssss", $username, $email, $nohp, $hash);
            if ($stmt->execute()) {
                $uid = $stmt->insert_id;
                $stmt->close();
                log_activity($uid, 'register', 'New user registered');
                header('Location: /login');
                exit;
            } else {
                $error = 'Username or email already used.';
            }
            $stmt->close();
        } else {
            $error = 'Internal error.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center">

<div class="page-loader">
  <div class="flex flex-col items-center gap-3">
    <div class="loader-spinner"></div>
    <div class="text-[10px] tracking-[0.3em] uppercase text-slate-300">
      Loading
    </div>
  </div>
</div>

<script>
  $(window).on('load', function () {
    setTimeout(function () {
      $('.page-loader').addClass('hidden');
    }, 200);
  });
</script>

<div class="w-full max-w-sm bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-xl">
  <h1 class="text-lg font-semibold mb-4 text-center">Register</h1>

  <?php if ($error): ?>
    <div class="alert alert-danger mb-4">
      <div class="alert-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
      <div>
        <div class="alert-title">Register failed</div>
        <div class="alert-text"><?= htmlspecialchars($error) ?></div>
      </div>
    </div>
  <?php endif; ?>

  <form method="post" class="space-y-3">
    <?php csrf_field(); ?>
    <div>
      <label class="block text-xs mb-1">Username</label>
      <input type="text" name="username" required
             class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-sm">
    </div>
    <div>
      <label class="block text-xs mb-1">Email</label>
      <input type="email" name="email" required
             class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-sm">
    </div>
    <div>
      <label class="block text-xs mb-1">Phone Number</label>
      <input type="text" name="nohp"
             class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-sm">
    </div>
    <div>
      <label class="block text-xs mb-1">Password</label>
      <input type="password" name="password" required
             class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-sm">
    </div>
    <div>
      <label class="block text-xs mb-1">Confirm Password</label>
      <input type="password" name="password_confirm" required
             class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-sm">
    </div>
    <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-500 rounded py-2 text-sm font-medium">
      Register
    </button>
  </form>

  <p class="mt-4 text-xs text-slate-400 text-center">
    Already have an account?
    <a href="/login" class="text-indigo-400 hover:text-indigo-300">Login</a>
  </p>
</div>
</body>
</html>
