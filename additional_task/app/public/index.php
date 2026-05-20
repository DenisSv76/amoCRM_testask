<?php
declare(strict_types=1);

// Точка входа: трекинг, статистика (с авторизацией)
require_once __DIR__ . '/../src/db.php';

session_start();

function auth_required(): void {
    if (empty($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 1) Приём треков (публичный)
if ($uri === '/track' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = get_db();
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? ($data['ip'] ?? null);
    $stmt = $db->prepare('INSERT INTO visits (ip, city, country, device, ua, url, referrer, screen_w, screen_h, created_at) VALUES (:ip, :city, :country, :device, :ua, :url, :referrer, :sw, :sh, datetime("now"))');
    $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
    $stmt->bindValue(':city', $data['city'] ?? null, SQLITE3_TEXT);
    $stmt->bindValue(':country', $data['country'] ?? null, SQLITE3_TEXT);
    $stmt->bindValue(':device', $data['device'] ?? null, SQLITE3_TEXT);
    $stmt->bindValue(':ua', $data['ua'] ?? null, SQLITE3_TEXT);
    $stmt->bindValue(':url', $data['url'] ?? null, SQLITE3_TEXT);
    $stmt->bindValue(':referrer', $data['referrer'] ?? null, SQLITE3_TEXT);
    $stmt->bindValue(':sw', $data['screen_w'] ?? null, SQLITE3_INTEGER);
    $stmt->bindValue(':sh', $data['screen_h'] ?? null, SQLITE3_INTEGER);
    $stmt->execute();
    echo json_encode(['ok' => true]);
    exit;
}

// 2) Логин
if ($uri === '/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = get_db();
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $stmt = $db->prepare('SELECT id, password_hash FROM users WHERE username = :u');
    $stmt->bindValue(':u', $username, SQLITE3_TEXT);
    $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    if ($row && password_verify($password, $row['password_hash'])) {
        $_SESSION['user'] = ['id' => $row['id'], 'username' => $username];
        echo json_encode(['ok' => true]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid username or password']);
    }
    exit;
}

// 2.1) Регистрация
if ($uri === '/register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = get_db();
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';

    if (strlen($username) < 3) {
        http_response_code(400);
        echo json_encode(['error' => 'Username must be at least 3 characters']);
        exit;
    }
    if (strlen($password) < 4) {
        http_response_code(400);
        echo json_encode(['error' => 'Password must be at least 4 characters']);
        exit;
    }

    try {
        $stmt = $db->prepare('INSERT INTO users (username, password_hash) VALUES (:u, :p)');
        $stmt->bindValue(':u', $username, SQLITE3_TEXT);
        $stmt->bindValue(':p', password_hash($password, PASSWORD_BCRYPT), SQLITE3_TEXT);
        $stmt->execute();
        echo json_encode(['ok' => true, 'message' => 'Registration successful. You can now log in.']);
    } catch (\Exception $e) {
        http_response_code(409);
        echo json_encode(['error' => 'Username already exists']);
    }
    exit;
}

// 3) Логаут
if ($uri === '/logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION = [];
    session_destroy();
    echo json_encode(['ok' => true]);
    exit;
}

// 4) Статистика по часам (требуется авторизация)
if ($uri === '/stats/hourly' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    auth_required();
    $db = get_db();
    $hours = intval($_GET['hours'] ?? 24);
    if ($hours < 1) $hours = 24;

    $q = "
        SELECT strftime('%Y-%m-%d %H:00:00', created_at) as hour,
               COUNT(DISTINCT ip || '-' || strftime('%Y-%m-%d %H', created_at)) as unique_visits
        FROM visits
        WHERE created_at >= datetime('now', :hours)
        GROUP BY hour
        ORDER BY hour ASC
    ";
    $stmt = $db->prepare($q);
    $stmt->bindValue(':hours', "-{$hours} hours", SQLITE3_TEXT);
    $res = $stmt->execute();

    $dataMap = [];
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $dataMap[$row['hour']] = (int)$row['unique_visits'];
    }

    // Генерируем все часы диапазона, заполняя нулями отсутствующие
    date_default_timezone_set('UTC');
    $now = new DateTime('now');
    // Округляем до начала следующего часа
    $now->setTime((int)$now->format('H'), 0, 0);
    $now->modify('+1 hour');
    // Центрируем окно: половина часов назад, половина вперёд
    $halfBefore = intdiv($hours, 2);
    $halfAfter = $hours - $halfBefore;
    $start = (clone $now)->modify("-{$halfBefore} hours");
    $end   = (clone $now)->modify("+{$halfAfter} hours");

    $out = [];
    $current = clone $start;
    while ($current < $end) {
        $hourKey = $current->format('Y-m-d H:00:00');
        $out[] = [
            'hour' => $hourKey,
            'unique_visits' => $dataMap[$hourKey] ?? 0,
        ];
        $current->modify('+1 hour');
    }

    echo json_encode($out);
    exit;
}

// 5) Распределение по городам (требуется авторизация)
if ($uri === '/stats/cities' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    auth_required();
    $db = get_db();
    $stmt = $db->prepare("
        SELECT COALESCE(city, 'Unknown') as city, COUNT(*) as cnt
        FROM visits
        GROUP BY city
        ORDER BY cnt DESC
        LIMIT 50
    ");
    $res = $stmt->execute();
    $out = [];
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) $out[] = $row;
    echo json_encode($out);
    exit;
}

// 6) Страницы (HTML)
if ($uri === '/' || $uri === '/dashboard') {
    if (empty($_SESSION['user'])) {
        header('Location: /login', true, 302);
        exit;
    }
    header('Content-Type: text/html; charset=utf-8');
    echo file_get_contents(__DIR__ . '/dashboard.html');
    exit;
}
if ($uri === '/login') {
    header('Content-Type: text/html; charset=utf-8');
    echo file_get_contents(__DIR__ . '/login.html');
    exit;
}
if ($uri === '/register') {
    header('Content-Type: text/html; charset=utf-8');
    echo file_get_contents(__DIR__ . '/register.html');
    exit;
}
if ($uri === '/test_page') {
    header('Content-Type: text/html; charset=utf-8');
    echo file_get_contents(__DIR__ . '/test_page.html');
    exit;
}
if ($uri === '/track.js') {
    header('Content-Type: application/javascript; charset=utf-8');
    echo file_get_contents(__DIR__ . '/track.js');
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
exit;
