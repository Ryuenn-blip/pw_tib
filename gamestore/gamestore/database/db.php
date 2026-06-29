<?php
/**
 * GameStore — Database Connection & Query Helper
 * PDO-based, PHP 7.4+
 *
 * KONFIGURASI:
 *   Isi DB_HOST, DB_NAME, DB_USER, DB_PASS sesuai hosting kamu.
 *   Di cPanel biasanya: DB_HOST = 'localhost'
 */

// ── Konfigurasi Koneksi ───────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_NAME',    'gamestore_db');   // ← ganti nama database kamu
define('DB_USER',    'root');           // ← ganti username database
define('DB_PASS',    '');               // ← ganti password database
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT',    3306);

class DB
{
    private static ?PDO $pdo = null;

    public static function conn(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );
            self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ]);
        }
        return self::$pdo;
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::conn()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function rows(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function row(string $sql, array $params = []): ?array
    {
        $result = self::query($sql, $params)->fetch();
        return $result === false ? null : $result;
    }

    public static function value(string $sql, array $params = []): mixed
    {
        $result = self::query($sql, $params)->fetchColumn();
        return $result === false ? null : $result;
    }

    public static function count(string $sql, array $params = []): int
    {
        return (int) self::value($sql, $params);
    }

    public static function insert(string $sql, array $params = []): int
    {
        self::query($sql, $params);
        return (int) self::conn()->lastInsertId();
    }

    public static function insertRow(string $table, array $data): int
    {
        $cols         = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        return self::insert(
            "INSERT INTO `$table` ($cols) VALUES ($placeholders)",
            array_values($data)
        );
    }

    public static function updateRow(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set  = implode(', ', array_map(fn($k) => "`$k`=?", array_keys($data)));
        $stmt = self::query(
            "UPDATE `$table` SET $set WHERE $where",
            array_merge(array_values($data), $whereParams)
        );
        return $stmt->rowCount();
    }

    public static function begin():    void { self::conn()->beginTransaction(); }
    public static function commit():   void { self::conn()->commit(); }
    public static function rollback(): void { self::conn()->rollBack(); }

    public static function transaction(callable $fn): mixed
    {
        self::begin();
        try {
            $result = $fn(self::conn());
            self::commit();
            return $result;
        } catch (Throwable $e) {
            self::rollback();
            throw $e;
        }
    }
}

function gs_setting(string $key, mixed $default = null): mixed
{
    try {
        $val = DB::value("SELECT `value` FROM `settings` WHERE `key`=?", [$key]);
        return $val !== null ? $val : $default;
    } catch (Throwable) {
        return $default;
    }
}

function generate_order_id(): string
{
    return 'GS' . date('ymd') . strtoupper(substr(uniqid(), -6));
}
