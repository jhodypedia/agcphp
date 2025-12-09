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
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $error = 'Username and password are required.';
    } else {
        $sql = "SELECT id, password FROM users WHERE username = ? LIMIT 1";
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->bind_result($uid, $hash);
            if ($stmt->fetch() && password_verify($password, $hash)) {
                $stmt->close();
                session_regenerate_id(true);
                $_SESSION['user_id'] = $uid;
                log_activity($uid, 'login', 'User logged in');
                header('Location: /admin');
                exit;
            }
            $stmt->close();
        }
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
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
  <h1 class="text-lg font-semibold mb-4 text-center">Login</h1>

  <?php if ($error): ?>
    <div class="alert alert-danger mb-4">
      <div class="alert-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
      <div>
        <div class="alert-title">Login failed</div>
        <div class="alert-text"><?= htmlspecialchars($error) ?></div>
      </div>
    </div>
  <?php endif; ?>

  <form method="post" class="space-y-4">
    <?php csrf_field(); ?>
    <div>
      <label class="block text-xs mb-1">Username</label>
      <input type="text" name="username" required
             class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>
    <div>
      <label class="block text-xs mb-1">Password</label>
      <input type="password" name="password" required
             class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>
    <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-500 rounded py-2 text-sm font-medium">
      Login
    </button>
  </form>

  <p class="mt-4 text-xs text-slate-400 text-center">
    Don't have an account?
    <a href="/register" class="text-indigo-400 hover:text-indigo-300">Register</a>
  </p>
</div>
</body>
</html>
