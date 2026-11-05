<?php

namespace App\Controllers;

use App\Core\AuditLogger;
use App\Core\Controller;
use App\Models\Deposit;

class ReportController extends Controller
{
    private const REPORT_TYPES = [
        'overall' => ['label' => 'Overall Period Report', 'group' => null],
        'department' => ['label' => 'Department-wise Period Report', 'group' => 'department_id'],
        'country' => ['label' => 'Visited Country-wise Period Report', 'group' => 'visited_country_id'],
        'product' => ['label' => 'Product-wise Period Report', 'group' => 'product_id'],
        'operating_unit' => ['label' => 'Operating Unit-wise Period Report', 'group' => 'operating_unit_id'],
        'legal_entity' => ['label' => 'Legal Entity-wise Period Report', 'group' => 'legal_entity_id'],
    ];

    public function index(): void
    {
        require_login();
        $type = (string) $this->input('type', 'overall');
        if (!isset(self::REPORT_TYPES[$type])) $type = 'overall';

        $dateFrom = (string) $this->input('date_from', date('Y-m-01'));
        $dateTo = (string) $this->input('date_to', date('Y-m-d'));
        $ran = $this->input('run') !== null;

        $rows = [];
        if ($ran) {
            $user = current_user();
            $userId = is_admin() ? null : $user['id'];
            $rows = Deposit::periodReport($dateFrom, $dateTo, self::REPORT_TYPES[$type]['group'], $userId);
            AuditLogger::log('report_run', 'report', null, self::REPORT_TYPES[$type]['label'] . " {$dateFrom} to {$dateTo}");
        }

        $this->render('reports/index', [
            'types' => self::REPORT_TYPES,
            'type' => $type,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'ran' => $ran,
            'rows' => $rows,
        ]);
    }

    public function logExport(): void
    {
        require_login();
        $type = (string) $this->input('type', 'pdf');
        if (!in_array($type, ['pdf', 'jpg', 'png', 'print'], true)) {
            json_response(['ok' => false], 400);
        }
        AuditLogger::logExport($type, null, 'report');
        json_response(['ok' => true]);
    }
}
