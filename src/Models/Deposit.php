<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Deposit extends Model
{
    protected static string $table = 'deposit_forms';

    /**
     * Fetch a single deposit form with all joined labels + currency lines.
     */
    public static function findFull(int $id): ?array
    {
        $sql = "SELECT df.*, 
                    vc.name AS country_name, d.name AS department_name, p.name AS product_name,
                    ou.name AS operating_unit_name, le.name AS legal_entity_name,
                    pot.name AS purpose_name,
                    s1.name AS depositor_name, s1.designation AS depositor_designation,
                    s2.name AS verifier_name, s2.designation AS verifier_designation,
                    s3.name AS receiver_name, s3.designation AS receiver_designation,
                    u.full_name AS created_by_name
                FROM deposit_forms df
                LEFT JOIN visited_countries vc ON vc.id = df.visited_country_id
                LEFT JOIN departments d ON d.id = df.department_id
                LEFT JOIN products p ON p.id = df.product_id
                LEFT JOIN operating_units ou ON ou.id = df.operating_unit_id
                LEFT JOIN legal_entities le ON le.id = df.legal_entity_id
                LEFT JOIN purposes_of_tour pot ON pot.id = df.purpose_of_tour_id
                LEFT JOIN signatories s1 ON s1.id = df.depositor_signatory_id
                LEFT JOIN signatories s2 ON s2.id = df.verified_by_signatory_id
                LEFT JOIN signatories s3 ON s3.id = df.received_by_signatory_id
                LEFT JOIN users u ON u.id = df.created_by
                WHERE df.id = :id LIMIT 1";
        $stmt = static::pdo()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $lineStmt = static::pdo()->prepare(
            "SELECT dcl.*, c.code AS currency_code, c.name AS currency_name
             FROM deposit_currency_lines dcl
             JOIN currencies c ON c.id = dcl.currency_id
             WHERE dcl.deposit_form_id = :id ORDER BY dcl.sort_order ASC"
        );
        $lineStmt->execute(['id' => $id]);
        $row['lines'] = $lineStmt->fetchAll();

        return $row;
    }

    /**
     * Paginated, filterable list for the Invoices module.
     */
    public static function search(array $filters, int $page = 1, int $perPage = 20): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = 'df.created_by = :user_id';
            $params['user_id'] = $filters['user_id'];
        }
        if (!empty($filters['keyword'])) {
            $where[] = '(df.invoice_number LIKE :kw OR df.name LIKE :kw OR df.id_no LIKE :kw)';
            $params['kw'] = '%' . $filters['keyword'] . '%';
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'df.deposit_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'df.deposit_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }
        if (!empty($filters['department_id'])) {
            $where[] = 'df.department_id = :department_id';
            $params['department_id'] = $filters['department_id'];
        }
        if (!empty($filters['legal_entity_id'])) {
            $where[] = 'df.legal_entity_id = :legal_entity_id';
            $params['legal_entity_id'] = $filters['legal_entity_id'];
        }
        if (!empty($filters['operating_unit_id'])) {
            $where[] = 'df.operating_unit_id = :operating_unit_id';
            $params['operating_unit_id'] = $filters['operating_unit_id'];
        }
        if (!empty($filters['country_id'])) {
            $where[] = 'df.visited_country_id = :country_id';
            $params['country_id'] = $filters['country_id'];
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $countSql = "SELECT COUNT(*) FROM deposit_forms df {$whereSql}";
        $countStmt = static::pdo()->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $offset = max(0, ($page - 1) * $perPage);
        $sql = "SELECT df.*, ou.name AS operating_unit_name, le.name AS legal_entity_name,
                       d.name AS department_name, vc.name AS country_name, p.name AS product_name
                FROM deposit_forms df
                LEFT JOIN operating_units ou ON ou.id = df.operating_unit_id
                LEFT JOIN legal_entities le ON le.id = df.legal_entity_id
                LEFT JOIN departments d ON d.id = df.department_id
                LEFT JOIN visited_countries vc ON vc.id = df.visited_country_id
                LEFT JOIN products p ON p.id = df.product_id
                {$whereSql}
                ORDER BY df.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";
        $stmt = static::pdo()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    public static function createWithLines(array $header, array $lines, int $userId, string $invoiceNumber): int
    {
        $pdo = static::pdo();
        $header['invoice_number'] = $invoiceNumber;
        $header['created_by'] = $userId;
        $id = static::insert($header);

        $sort = 0;
        $stmt = $pdo->prepare(
            'INSERT INTO deposit_currency_lines (deposit_form_id, currency_id, amount, rate, bdt_amount, sort_order)
             VALUES (:form_id, :currency_id, :amount, :rate, :bdt_amount, :sort_order)'
        );
        foreach ($lines as $line) {
            $stmt->execute([
                'form_id' => $id,
                'currency_id' => $line['currency_id'],
                'amount' => $line['amount'],
                'rate' => $line['rate'],
                'bdt_amount' => $line['bdt_amount'],
                'sort_order' => $sort++,
            ]);
        }
        return $id;
    }

    public static function updateWithLines(int $id, array $header, array $lines): void
    {
        $pdo = static::pdo();
        static::update($id, $header);

        $del = $pdo->prepare('DELETE FROM deposit_currency_lines WHERE deposit_form_id = :id');
        $del->execute(['id' => $id]);

        $sort = 0;
        $stmt = $pdo->prepare(
            'INSERT INTO deposit_currency_lines (deposit_form_id, currency_id, amount, rate, bdt_amount, sort_order)
             VALUES (:form_id, :currency_id, :amount, :rate, :bdt_amount, :sort_order)'
        );
        foreach ($lines as $line) {
            $stmt->execute([
                'form_id' => $id,
                'currency_id' => $line['currency_id'],
                'amount' => $line['amount'],
                'rate' => $line['rate'],
                'bdt_amount' => $line['bdt_amount'],
                'sort_order' => $sort++,
            ]);
        }
    }

    // ---------------------------------------------------------------
    // Dashboard & Reports aggregation queries
    // ---------------------------------------------------------------

    public static function monthlyCount(string $yearMonth, ?int $userId = null): int
    {
        $sql = "SELECT COUNT(*) FROM deposit_forms WHERE DATE_FORMAT(deposit_date, '%Y-%m') = :ym";
        $params = ['ym' => $yearMonth];
        if ($userId) { $sql .= ' AND created_by = :uid'; $params['uid'] = $userId; }
        $stmt = static::pdo()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function monthlySumBdt(string $yearMonth, ?int $userId = null): float
    {
        $sql = "SELECT COALESCE(SUM(total_bdt_amount),0) FROM deposit_forms WHERE DATE_FORMAT(deposit_date, '%Y-%m') = :ym";
        $params = ['ym' => $yearMonth];
        if ($userId) { $sql .= ' AND created_by = :uid'; $params['uid'] = $userId; }
        $stmt = static::pdo()->prepare($sql);
        $stmt->execute($params);
        return (float) $stmt->fetchColumn();
    }

    public static function groupedMonthlyTotals(string $groupCol, string $labelTable, string $labelCol, string $yearMonth, ?int $userId = null): array
    {
        $joinMap = [
            'operating_unit_id' => 'operating_units',
            'legal_entity_id' => 'legal_entities',
            'product_id' => 'products',
            'visited_country_id' => 'visited_countries',
            'department_id' => 'departments',
        ];
        $joinTable = $joinMap[$groupCol] ?? $labelTable;

        $sql = "SELECT t.{$labelCol} AS label, COALESCE(SUM(df.total_bdt_amount),0) AS total, COUNT(df.id) AS cnt
                FROM deposit_forms df
                LEFT JOIN {$joinTable} t ON t.id = df.{$groupCol}
                WHERE DATE_FORMAT(df.deposit_date, '%Y-%m') = :ym";
        $params = ['ym' => $yearMonth];
        if ($userId) { $sql .= ' AND df.created_by = :uid'; $params['uid'] = $userId; }
        $sql .= " GROUP BY t.{$labelCol} ORDER BY total DESC";
        $stmt = static::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function currencyWiseMonthlyTotals(string $yearMonth, ?int $userId = null): array
    {
        $sql = "SELECT c.code AS label, COALESCE(SUM(dcl.bdt_amount),0) AS total, COALESCE(SUM(dcl.amount),0) AS fx_total
                FROM deposit_currency_lines dcl
                JOIN currencies c ON c.id = dcl.currency_id
                JOIN deposit_forms df ON df.id = dcl.deposit_form_id
                WHERE DATE_FORMAT(df.deposit_date, '%Y-%m') = :ym";
        $params = ['ym' => $yearMonth];
        if ($userId) { $sql .= ' AND df.created_by = :uid'; $params['uid'] = $userId; }
        $sql .= ' GROUP BY c.code ORDER BY total DESC';
        $stmt = static::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countryWiseMonthlyBreakdown(string $yearMonth, ?int $userId = null): array
    {
        $sql = "SELECT vc.name AS country, COUNT(DISTINCT df.id) AS form_count,
                       GROUP_CONCAT(DISTINCT c.code ORDER BY c.code SEPARATOR ', ') AS currency_mix,
                       COALESCE(SUM(df.total_bdt_amount),0) AS grand_total
                FROM deposit_forms df
                LEFT JOIN visited_countries vc ON vc.id = df.visited_country_id
                LEFT JOIN deposit_currency_lines dcl ON dcl.deposit_form_id = df.id
                LEFT JOIN currencies c ON c.id = dcl.currency_id
                WHERE DATE_FORMAT(df.deposit_date, '%Y-%m') = :ym";
        $params = ['ym' => $yearMonth];
        if ($userId) { $sql .= ' AND df.created_by = :uid'; $params['uid'] = $userId; }
        $sql .= ' GROUP BY vc.name ORDER BY grand_total DESC';
        $stmt = static::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function recent(int $limit = 10, ?int $userId = null): array
    {
        $sql = "SELECT df.id, df.invoice_number, df.deposit_date, df.name, df.id_no, df.total_bdt_amount,
                       ou.name AS operating_unit_name, le.name AS legal_entity_name, p.name AS product_name
                FROM deposit_forms df
                LEFT JOIN operating_units ou ON ou.id = df.operating_unit_id
                LEFT JOIN legal_entities le ON le.id = df.legal_entity_id
                LEFT JOIN products p ON p.id = df.product_id";
        $params = [];
        if ($userId) { $sql .= ' WHERE df.created_by = :uid'; $params['uid'] = $userId; }
        $sql .= " ORDER BY df.created_at DESC LIMIT {$limit}";
        $stmt = static::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Period report: list of deposits between two dates, optionally grouped
     * by a dimension (department/country/product/operating_unit/legal_entity).
     */
    public static function periodReport(string $dateFrom, string $dateTo, ?string $groupCol = null, ?int $userId = null): array
    {
        $joinMap = [
            'department_id' => ['departments', 'name'],
            'visited_country_id' => ['visited_countries', 'name'],
            'product_id' => ['products', 'name'],
            'operating_unit_id' => ['operating_units', 'name'],
            'legal_entity_id' => ['legal_entities', 'name'],
        ];

        $params = ['from' => $dateFrom, 'to' => $dateTo];
        $userClause = '';
        if ($userId) { $userClause = ' AND df.created_by = :uid'; $params['uid'] = $userId; }

        if ($groupCol && isset($joinMap[$groupCol])) {
            [$table, $col] = $joinMap[$groupCol];
            $sql = "SELECT t.{$col} AS group_label, COUNT(df.id) AS form_count, COALESCE(SUM(df.total_bdt_amount),0) AS total_bdt
                    FROM deposit_forms df
                    LEFT JOIN {$table} t ON t.id = df.{$groupCol}
                    WHERE df.deposit_date BETWEEN :from AND :to {$userClause}
                    GROUP BY t.{$col} ORDER BY total_bdt DESC";
        } else {
            $sql = "SELECT df.invoice_number AS group_label, df.deposit_date, df.name, 1 AS form_count, df.total_bdt_amount AS total_bdt
                    FROM deposit_forms df
                    WHERE df.deposit_date BETWEEN :from AND :to {$userClause}
                    ORDER BY df.deposit_date ASC";
        }

        $stmt = static::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
