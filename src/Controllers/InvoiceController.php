<?php

namespace App\Controllers;

use App\Core\AuditLogger;
use App\Core\Controller;
use App\Models\Deposit;
use App\Models\Department;
use App\Models\LegalEntity;
use App\Models\OperatingUnit;
use App\Models\VisitedCountry;

class InvoiceController extends Controller
{
    public function index(): void
    {
        require_login();
        $user = current_user();

        $filters = [
            'user_id' => is_admin() ? null : $user['id'],
            'keyword' => trim((string) $this->input('keyword', '')),
            'date_from' => (string) $this->input('date_from', ''),
            'date_to' => (string) $this->input('date_to', ''),
            'department_id' => (int) $this->input('department_id', 0) ?: null,
            'legal_entity_id' => (int) $this->input('legal_entity_id', 0) ?: null,
            'operating_unit_id' => (int) $this->input('operating_unit_id', 0) ?: null,
            'country_id' => (int) $this->input('country_id', 0) ?: null,
        ];

        $page = max(1, (int) $this->input('page', 1));
        $result = Deposit::search($filters, $page, 15);

        $this->render('invoices/index', [
            'result' => $result,
            'filters' => $filters,
            'departments' => Department::all('name'),
            'legal_entities' => LegalEntity::all('name'),
            'operating_units' => OperatingUnit::all('name'),
            'countries' => VisitedCountry::all('name'),
        ]);
    }

    public function view(): void
    {
        require_login();
        $id = (int) $this->input('id');
        $deposit = Deposit::findFull($id);
        if (!$deposit) { flash('error', 'Invoice not found.'); redirect(route_url('invoices')); }

        $user = current_user();
        if (!is_admin() && (int) $deposit['created_by'] !== (int) $user['id']) {
            http_response_code(403);
            flash('error', 'You do not have permission to view this invoice.');
            redirect(route_url('invoices'));
        }

        $this->render('deposit/invoice', ['deposit' => $deposit]);
    }

    /** AJAX endpoint hit by client-side export JS to record the audit trail. */
    public function logExport(): void
    {
        require_login();
        $id = (int) $this->input('id');
        $type = (string) $this->input('type', 'pdf');
        if (!in_array($type, ['pdf', 'jpg', 'png', 'print'], true)) {
            json_response(['ok' => false], 400);
        }
        AuditLogger::logExport($type, $id ?: null, 'invoice');
        json_response(['ok' => true]);
    }
}
