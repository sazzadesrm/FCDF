<?php

namespace App\Core;

use App\Config\Database;
use PDO;

/**
 * Lightweight active-record-ish base model. All queries use PDO prepared
 * statements — never raw string concatenation.
 */
abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    protected static function pdo(): PDO
    {
        return Database::connect();
    }

    public static function find(int $id): ?array
    {
        $stmt = static::pdo()->prepare('SELECT * FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function all(string $orderBy = ''): array
    {
        $sql = 'SELECT * FROM ' . static::$table;
        if ($orderBy) $sql .= ' ORDER BY ' . $orderBy;
        return static::pdo()->query($sql)->fetchAll();
    }

    public static function where(array $conditions, string $orderBy = ''): array
    {
        $clauses = [];
        $params = [];
        foreach ($conditions as $col => $val) {
            $clauses[] = "$col = :$col";
            $params[$col] = $val;
        }
        $sql = 'SELECT * FROM ' . static::$table;
        if ($clauses) $sql .= ' WHERE ' . implode(' AND ', $clauses);
        if ($orderBy) $sql .= ' ORDER BY ' . $orderBy;
        $stmt = static::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function insert(array $data): int
    {
        $cols = array_keys($data);
        $placeholders = array_map(fn($c) => ":$c", $cols);
        $sql = 'INSERT INTO ' . static::$table . ' (' . implode(',', $cols) . ') VALUES (' . implode(',', $placeholders) . ')';
        $stmt = static::pdo()->prepare($sql);
        $stmt->execute($data);
        return (int) static::pdo()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $sets = [];
        foreach (array_keys($data) as $col) {
            $sets[] = "$col = :$col";
        }
        $sql = 'UPDATE ' . static::$table . ' SET ' . implode(',', $sets) . ' WHERE ' . static::$primaryKey . ' = :__id';
        $data['__id'] = $id;
        $stmt = static::pdo()->prepare($sql);
        return $stmt->execute($data);
    }

    public static function delete(int $id): bool
    {
        $stmt = static::pdo()->prepare('DELETE FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = :id');
        return $stmt->execute(['id' => $id]);
    }

    public static function count(array $conditions = []): int
    {
        $clauses = [];
        $params = [];
        foreach ($conditions as $col => $val) {
            $clauses[] = "$col = :$col";
            $params[$col] = $val;
        }
        $sql = 'SELECT COUNT(*) FROM ' . static::$table;
        if ($clauses) $sql .= ' WHERE ' . implode(' AND ', $clauses);
        $stmt = static::pdo()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}
