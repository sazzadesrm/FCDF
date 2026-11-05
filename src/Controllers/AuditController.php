<?php

namespace App\Controllers;

use App\Config\Database;
use App\Core\Controller;

class AuditController extends Controller
{
    public function index(): void
    {
        require_admin();

        $page = max(1, (int) $this->input('page', 1));
        $perPage = 30;
        $offset = ($page - 1) * $perPage;

        $keyword = trim((string) $this->input('keyword', ''));
        $pdo = Database::connect();

        $where = '';
        $params = [];
        if ($keyword !== '') {
            $where = 'WHERE user_name LIKE :kw OR action LIKE :kw OR entity LIKE :kw OR details LIKE :kw';
            $params['kw'] = '%' . $keyword . '%';
        }

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM audit_logs {$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT * FROM audit_logs {$where} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $this->render('audit/index', [
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'keyword' => $keyword,
        ]);
    }
}
