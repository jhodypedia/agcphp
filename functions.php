<?php

/* --------- General helpers --------- */

function is_auth_or_admin_area() {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    return (strpos($script, '/auth/') !== false) || (strpos($script, '/admin/') !== false);
}

/* --------- Global settings --------- */

function get_global_setting($key, $default = null) {
    global $mysqli;
    $sql = "SELECT setting_value FROM global_settings WHERE setting_key = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) return $default;
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $stmt->bind_result($val);
    if ($stmt->fetch()) {
        $stmt->close();
        return $val;
    }
    $stmt->close();
    return $default;
}

function set_global_setting($key, $value) {
    global $mysqli;
    $sql = "INSERT INTO global_settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) return false;
    $stmt->bind_param("ss", $key, $value);
    return $stmt->execute();
}

/* --------- Users & auth --------- */

function get_user_by_id($id) {
    global $mysqli;
    $sql = "SELECT * FROM users WHERE id = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) return null;
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();
    return $user ?: null;
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function current_user() {
    static $cache = null;
    if ($cache !== null) return $cache;
    $uid = current_user_id();
    if (!$uid) return null;
    $cache = get_user_by_id($uid);
    return $cache;
}

function is_premium($user = null) {
    if ($user === null) $user = current_user();
    if (!$user) return false;
    return $user['status'] === 'premium';
}

function require_login() {
    if (!current_user_id()) {
        header('Location: /login');
        exit;
    }
}

function require_admin() {
    $u = current_user();
    if (!$u || $u['role'] !== 'admin') {
        http_response_code(403);
        echo "Forbidden";
        exit;
    }
}

/* --------- CSRF --------- */

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    echo '<input type="hidden" name="csrf_token" value="'.$token.'">';
}

function csrf_verify_or_die() {
    $token = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('Invalid CSRF token');
    }
}

/* --------- Sites / tenant --------- */

function get_site_by_domain($domain) {
    global $mysqli;
    if (!$domain) return null;
    $sql = "SELECT * FROM sites WHERE domain = ? AND status = 'active' LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) return null;
    $stmt->bind_param("s", $domain);
    $stmt->execute();
    $res = $stmt->get_result();
    $site = $res->fetch_assoc();
    $stmt->close();
    return $site ?: null;
}

function get_sites_by_user($user_id) {
    global $mysqli;
    $sql = "SELECT * FROM sites WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) return [];
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}

/* --------- Slug helper --------- */

function slugify($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9]+/i', '-', $string);
    $string = trim($string, '-');
    return $string ?: 'movie';
}

/* --------- TMDB / movies --------- */

function fetch_tmdb_popular($page = 1) {
    global $TMDB_API_KEY;
    if (empty($TMDB_API_KEY)) return null;

    $url = "https://api.themoviedb.org/3/movie/popular?api_key=" . urlencode($TMDB_API_KEY) .
           "&language=en-US&page=" . intval($page);

    $context = stream_context_create([
        'http' => ['timeout' => 8]
    ]);

    $json = @file_get_contents($url, false, $context);
    if ($json === false) return null;
    return json_decode($json, true);
}

function save_tmdb_movie_to_db($movie) {
    global $mysqli;

    $tmdb_id   = (int)$movie['id'];
    $title     = $movie['title'] ?? $movie['name'] ?? 'Untitled';
    $slug      = slugify($title . '-' . $tmdb_id);
    $overview  = $movie['overview'] ?? '';
    $poster    = $movie['poster_path'] ?? null;
    $backdrop  = $movie['backdrop_path'] ?? null;
    $release   = $movie['release_date'] ?? null;
    $vote      = isset($movie['vote_average']) ? (float)$movie['vote_average'] : 0;
    $lang      = $movie['original_language'] ?? '';
    $pop       = isset($movie['popularity']) ? (float)$movie['popularity'] : 0;

    $sql = "INSERT INTO movies
      (tmdb_id, title, slug, overview, poster_path, backdrop_path, release_date, vote_average, original_language, popularity)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
      ON DUPLICATE KEY UPDATE
        title = VALUES(title),
        slug = VALUES(slug),
        overview = VALUES(overview),
        poster_path = VALUES(poster_path),
        backdrop_path = VALUES(backdrop_path),
        release_date = VALUES(release_date),
        vote_average = VALUES(vote_average),
        original_language = VALUES(original_language),
        popularity = VALUES(popularity)";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) return;
    $stmt->bind_param(
        "issssssdsd",
        $tmdb_id, $title, $slug, $overview, $poster, $backdrop, $release, $vote, $lang, $pop
    );
    $stmt->execute();
    $stmt->close();
}

function ensure_popular_cached($max_pages = 2) {
    global $mysqli;
    $res = $mysqli->query("SELECT COUNT(*) AS c FROM movies");
    if ($res) {
        $row = $res->fetch_assoc();
        if ($row['c'] > 0) return;
    }

    for ($p = 1; $p <= $max_pages; $p++) {
        $data = fetch_tmdb_popular($p);
        if (!$data || empty($data['results'])) break;
        foreach ($data['results'] as $m) {
            save_tmdb_movie_to_db($m);
        }
    }
}

function get_movies($page = 1, $per_page = 24, $search = null) {
    global $mysqli;
    $offset = ($page - 1) * $per_page;

    if ($search) {
        $search_like = '%' . $search . '%';
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM movies
                WHERE title LIKE ? OR overview LIKE ?
                ORDER BY popularity DESC
                LIMIT ?, ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssii", $search_like, $search_like, $offset, $per_page);
    } else {
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM movies
                ORDER BY popularity DESC
                LIMIT ?, ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ii", $offset, $per_page);
    }

    $stmt->execute();
    $res = $stmt->get_result();
    $movies = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $total_res = $mysqli->query("SELECT FOUND_ROWS() AS total");
    $total = $total_res ? $total_res->fetch_assoc()['total'] : 0;

    return [$movies, $total];
}

function get_movie_by_slug($slug) {
    global $mysqli;
    $sql = "SELECT * FROM movies WHERE slug = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) return null;
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $res = $stmt->get_result();
    $movie = $res->fetch_assoc();
    $stmt->close();
    return $movie ?: null;
}

function poster_url($path, $size = 'w342') {
    if (!$path) return null;
    return "https://image.tmdb.org/t/p/$size$path";
}

/* --------- Stats & tracking --------- */

function track_visit($site_id) {
    global $mysqli;
    if (!$site_id) return;
    $today = date('Y-m-d');

    $sql = "INSERT INTO site_stats_daily (site_id, stat_date, page_views)
            VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE page_views = page_views + 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) return;
    $stmt->bind_param("is", $site_id, $today);
    $stmt->execute();
    $stmt->close();
}

function get_site_stats_summary($site_id) {
    global $mysqli;
    $summary = ['total' => 0, 'today' => 0, 'last7days' => 0];
    if (!$site_id) return $summary;

    // total
    $sql = "SELECT SUM(page_views) AS total FROM site_stats_daily WHERE site_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $site_id);
    $stmt->execute();
    $stmt->bind_result($total);
    if ($stmt->fetch()) $summary['total'] = (int)$total;
    $stmt->close();

    $today = date('Y-m-d');
    $sql = "SELECT page_views FROM site_stats_daily WHERE site_id = ? AND stat_date = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("is", $site_id, $today);
    $stmt->execute();
    $stmt->bind_result($today_views);
    if ($stmt->fetch()) $summary['today'] = (int)$today_views;
    $stmt->close();

    $from = date('Y-m-d', strtotime('-6 days'));
    $sql = "SELECT SUM(page_views) AS v FROM site_stats_daily
            WHERE site_id = ? AND stat_date BETWEEN ? AND ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("iss", $site_id, $from, $today);
    $stmt->execute();
    $stmt->bind_result($v7);
    if ($stmt->fetch()) $summary['last7days'] = (int)$v7;
    $stmt->close();

    return $summary;
}

/* --------- Chart data: system & per-user --------- */

function get_system_traffic_and_income_last7days() {
    global $mysqli;
    $labels  = [];
    $traffic = [];
    $income  = [];

    $today = new DateTime();
    $start = (clone $today)->modify('-6 days');

    $mapTraffic = [];
    $mapIncome  = [];
    $cursor = clone $start;
    while ($cursor <= $today) {
        $d = $cursor->format('Y-m-d');
        $labels[] = $d;
        $mapTraffic[$d] = 0;
        $mapIncome[$d]  = 0;
        $cursor->modify('+1 day');
    }

    $from = $start->format('Y-m-d');
    $to   = $today->format('Y-m-d');

    // traffic
    $sql = "SELECT stat_date, SUM(page_views) AS views
            FROM site_stats_daily
            WHERE stat_date BETWEEN ? AND ?
            GROUP BY stat_date";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ss", $from, $to);
        $stmt->execute();
        $stmt->bind_result($d, $v);
        while ($stmt->fetch()) {
            if (isset($mapTraffic[$d])) $mapTraffic[$d] = (int)$v;
        }
        $stmt->close();
    }

    // income
    $sql = "SELECT DATE(created_at) AS d, SUM(final_amount) AS total
            FROM payment_orders
            WHERE status = 'paid'
              AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at)";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ss", $from, $to);
        $stmt->execute();
        $stmt->bind_result($d, $total);
        while ($stmt->fetch()) {
            if (isset($mapIncome[$d])) $mapIncome[$d] = (int)$total;
        }
        $stmt->close();
    }

    foreach ($labels as $d) {
        $traffic[] = $mapTraffic[$d];
        $income[]  = $mapIncome[$d];
    }

    return ['labels' => $labels, 'traffic' => $traffic, 'income' => $income];
}

function get_user_traffic_and_income_last7days($user_id) {
    global $mysqli;
    $labels  = [];
    $traffic = [];
    $income  = [];

    $today = new DateTime();
    $start = (clone $today)->modify('-6 days');

    $mapTraffic = [];
    $mapIncome  = [];
    $cursor = clone $start;
    while ($cursor <= $today) {
        $d = $cursor->format('Y-m-d');
        $labels[] = $d;
        $mapTraffic[$d] = 0;
        $mapIncome[$d]  = 0;
        $cursor->modify('+1 day');
    }

    $from = $start->format('Y-m-d');
    $to   = $today->format('Y-m-d');

    // traffic per user (join sites)
    $sql = "SELECT d.stat_date, SUM(d.page_views) AS views
            FROM site_stats_daily d
            JOIN sites s ON s.id = d.site_id
            WHERE s.user_id = ?
              AND d.stat_date BETWEEN ? AND ?
            GROUP BY d.stat_date";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iss", $user_id, $from, $to);
        $stmt->execute();
        $stmt->bind_result($d, $v);
        while ($stmt->fetch()) {
            if (isset($mapTraffic[$d])) $mapTraffic[$d] = (int)$v;
        }
        $stmt->close();
    }

    // income per user
    $sql = "SELECT DATE(created_at) AS d, SUM(final_amount) AS total
            FROM payment_orders
            WHERE status = 'paid'
              AND user_id = ?
              AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at)";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iss", $user_id, $from, $to);
        $stmt->execute();
        $stmt->bind_result($d, $total);
        while ($stmt->fetch()) {
            if (isset($mapIncome[$d])) $mapIncome[$d] = (int)$total;
        }
        $stmt->close();
    }

    foreach ($labels as $d) {
        $traffic[] = $mapTraffic[$d];
        $income[]  = $mapIncome[$d];
    }

    return ['labels' => $labels, 'traffic' => $traffic, 'income' => $income];
}

/* --------- Payments & orders --------- */

function generate_unique_amount($base_amount, $method) {
    global $mysqli;
    $base = (int)$base_amount;
    if ($base <= 0) return 0;

    $thousands = floor($base / 1000) * 1000;

    for ($i = 0; $i < 10; $i++) {
        $randLast3 = rand(101, 999);
        $final = $thousands + $randLast3;

        $sql = "SELECT COUNT(*) FROM payment_orders
                WHERE final_amount = ? AND status = 'pending' AND method = ?";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) continue;
        $stmt->bind_param("is", $final, $method);
        $stmt->execute();
        $stmt->bind_result($cnt);
        $stmt->fetch();
        $stmt->close();

        if ($cnt == 0) {
            return $final;
        }
    }

    return $base;
}

function create_payment_order($user_id, $base_amount, $method) {
    global $mysqli;
    $final = generate_unique_amount($base_amount, $method);
    if ($final <= 0) return null;

    $sql = "INSERT INTO payment_orders (user_id, base_amount, final_amount, method)
            VALUES (?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) return null;
    $stmt->bind_param("iiis", $user_id, $base_amount, $final, $method);
    if ($stmt->execute()) {
        $id = $stmt->insert_id;
        $stmt->close();
        return [
            'id'           => $id,
            'base_amount'  => $base_amount,
            'final_amount' => $final,
            'method'       => $method,
        ];
    }
    $stmt->close();
    return null;
}

function get_payment_settings() {
    global $mysqli;
    $sql = "SELECT * FROM payment_settings ORDER BY method";
    $res = $mysqli->query($sql);
    if (!$res) return [];
    return $res->fetch_all(MYSQLI_ASSOC);
}

/* --------- Activity logs --------- */

function log_activity($user_id, $action, $meta = null) {
    global $mysqli;
    if (!$user_id) return;
    $ip  = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua  = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $sql = "INSERT INTO activity_logs (user_id, action, meta, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) return;
    $stmt->bind_param("issss", $user_id, $action, $meta, $ip, $ua);
    $stmt->execute();
    $stmt->close();
}
