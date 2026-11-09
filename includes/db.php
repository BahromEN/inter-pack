<?php
/**
 * SQLite connection + schema bootstrap.
 * Creates data/cms.sqlite on first run with all required tables.
 */

function cms_db(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dbPath = __DIR__ . '/../data/cms.sqlite';
    $isNew = !file_exists($dbPath);

    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON;');

    if ($isNew) {
        cms_bootstrap_schema($pdo);
    } else {
        cms_migrate_schema($pdo);
    }

    return $pdo;
}

function cms_bootstrap_schema(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE admin_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE content_overrides (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            lang TEXT NOT NULL,
            path TEXT NOT NULL,
            value TEXT NOT NULL,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(lang, path)
        );

        CREATE TABLE blog_posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT UNIQUE NOT NULL,
            cover_image TEXT,
            published INTEGER NOT NULL DEFAULT 0,
            published_at TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE blog_post_i18n (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            post_id INTEGER NOT NULL REFERENCES blog_posts(id) ON DELETE CASCADE,
            lang TEXT NOT NULL,
            title TEXT NOT NULL DEFAULT '',
            excerpt TEXT NOT NULL DEFAULT '',
            body TEXT NOT NULL DEFAULT '',
            UNIQUE(post_id, lang)
        );

        CREATE TABLE login_attempts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip TEXT NOT NULL,
            attempted_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE INDEX idx_content_overrides_lang ON content_overrides(lang);
        CREATE INDEX idx_blog_post_i18n_post ON blog_post_i18n(post_id);
        CREATE INDEX idx_login_attempts_ip_time ON login_attempts(ip, attempted_at);
    ");
}

/**
 * Lightweight forward-only migration: add tables/columns that might be
 * missing on an existing DB (safe to run every request; cheap no-ops).
 */
function cms_migrate_schema(PDO $pdo): void {
    // Reserved for future ALTERs. Nothing to do yet.
}
