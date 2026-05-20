<?php
declare(strict_types=1);

function get_db(): SQLite3 {
    $dbFile = __DIR__ . '/../data/data.sqlite';
    if (!is_dir(dirname($dbFile))) mkdir(dirname($dbFile), 0755, true);
    $db = new SQLite3($dbFile);
    $db->exec('PRAGMA foreign_keys = ON;');

    // Создаём таблицы, если их нет
    $db->exec("
    CREATE TABLE IF NOT EXISTS visits (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      ip TEXT,
      city TEXT,
      country TEXT,
      device TEXT,
      ua TEXT,
      url TEXT,
      referrer TEXT,
      screen_w INTEGER,
      screen_h INTEGER,
      created_at DATETIME DEFAULT (datetime('now'))
    );
    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT UNIQUE,
      password_hash TEXT
    );
    ");

    // seed-пользователь admin/admin (если ещё не создан)
    $stmt = $db->prepare('INSERT OR IGNORE INTO users (username, password_hash) VALUES (:u, :p)');
    $stmt->bindValue(':u', 'admin', SQLITE3_TEXT);
    $stmt->bindValue(':p', password_hash('admin', PASSWORD_BCRYPT), SQLITE3_TEXT);
    $stmt->execute();

    return $db;
}
