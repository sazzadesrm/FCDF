<?php

namespace App\Controllers;

use App\Core\AuditLogger;
use App\Core\Controller;
use App\Helpers\InvoiceNumberGenerator;
use App\Helpers\NumberToWords;
use App\Config\Database;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Department;
use App\Models\LegalEntity;
use App\Models\OperatingUnit;
use App\Models\Product;
use App\Models\PurposeOfTour;
use App\Models\Signatory;
use App\Models\TermsCondition;
use App\Models\VisitedCountry;

class DepositController extends Controller
{
    public function create(): void
    {
        require_login();
        $this->render('deposit/form', array_merge($this->masterData(), [
            'mode' => 'create',
            'deposit' => null,
        ]));
    }

    public function store(): void
    {
        require_login();
        $this->requireCsrf();

        [$header, $lines, $errors] = $this->collectFormData();

        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect(route_url('deposit/create'));
        }

        $pdo = Database::connect();
        $pdo->beginTransaction();
        try {
            $invoiceNumber = InvoiceNumberGenerator::generate($pdo, new \DateTime($header['deposit_date']));
            $id = Deposit::createWithLines($header, $lines, current_user()['id'], $invoiceNumber);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            error_log('Deposit store failed: ' . $e->getMessage());
            flash('error', 'Could not save the deposit form. Please try again.');
            redirect(route_url('deposit/create'));
        }

        AuditLogger::log('create', 'deposit_form', $id, 'Created invoice ' . $invoiceNumber);
        flash('success', "Deposit form {$invoiceNumber} submitted successfully.");
        redirect(route_url('invoice/view', ['id' => $id]));
    }

    public function edit(): void
    {
        require_login();
        $id = (int) $this->input('id');
        $deposit = Deposit::findFull($id);
        if (!$deposit) { flash('error', 'Deposit form not found.'); redirect(route_url('invoices')); }
        $this->authorizeOwner($deposit);

        $this->render('deposit/form', array_merge($this->masterData(), [
            'mode' => 'edit',
            'deposit' => $deposit,
        ]));
    }

    public function update(): void
    {
        require_login();
        $this->requireCsrf();
        $id = (int) $this->input('id');
        $existing = Deposit::find($id);
        if (!$existing) { flash('error', 'Deposit form not found.'); redirect(route_url('invoices')); }
        $this->authorizeOwner($existing);

        [$header, $lines, $errors] = $this->collectFormData();
        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect(route_url('deposit/edit', ['id' => $id]));
        }

        Deposit::updateWithLines($id, $header, $lines);
        AuditLogger::log('update', 'deposit_form', $id, 'Updated invoice ' . $existing['invoice_number']);
        flash('success', 'Deposit form updated successfully.');
        redirect(route_url('invoice/view', ['id' => $id]));
    }

    public function destroy(): void
    {
        require_login();
        $this->requireCsrf();
        $id = (int) $this->input('id');
        $existing = Deposit::find($id);
        if (!$existing) { flash('error', 'Deposit form not found.'); redirect(route_url('invoices')); }
        $this->authorizeOwner($existing);

        Deposit::delete($id);
        AuditLogger::log('delete', 'deposit_form', $id, 'Deleted invoice ' . $existing['invoice_number']);
        flash('success', 'Deposit form deleted.');
        redirect(route_url('invoices'));
    }

    // -----------------------------------------------------------------
    private function authorizeOwner(array $deposit): void
    {
        $user = current_user();
        if (!is_admin() && (int) $deposit['created_by'] !== (int) $user['id']) {
            http_response_code(403);
            flash('error', 'You do not have permission to modify this record.');
            redirect(route_url('invoices'));
        }
    }

    private function masterData(): array
    {
        return [
            'countries' => VisitedCountry::where(['is_active' => 1], 'name'),
            'departments' => Department::where(['is_active' => 1], 'name'),
            'products' => Product::where(['is_active' => 1], 'name'),
            'operating_units' => OperatingUnit::where(['is_active' => 1], 'name'),
            'legal_entities' => LegalEntity::where(['is_active' => 1], 'name'),
            'purposes' => PurposeOfTour::where(['is_active' => 1], 'name'),
            'currencies' => Currency::where(['is_active' => 1], 'code'),
            'signatories' => Signatory::where(['is_active' => 1], 'name'),
            'terms' => TermsCondition::where(['is_active' => 1], 'sort_order'),
        ];
    }

    private function collectFormData(): array
    {
        $errors = [];

        $header = [
            'visited_country_id' => (int) $this->input('visited_country_id') ?: null,
            'deposit_date' => (string) $this->input('deposit_date'),
            'name' => trim((string) $this->input('name')),
            'id_no' => trim((string) $this->input('id_no')),
            'department_id' => (int) $this->input('department_id') ?: null,
            'product_id' => (int) $this->input('product_id') ?: null,
            'operating_unit_id' => (int) $this->input('operating_unit_id') ?: null,
            'legal_entity_id' => (int) $this->input('legal_entity_id') ?: null,
            'ebs_number' => trim((string) $this->input('ebs_number')),
            'purpose_of_tour_id' => (int) $this->input('purpose_of_tour_id') ?: null,
            'purpose_of_tour_text' => trim((string) $this->input('purpose_of_tour_text')),
            'special_note' => trim((string) $this->input('special_note')),
            'depositor_signatory_id' => (int) $this->input('depositor_signatory_id') ?: null,
            'depositor_manual_name' => trim((string) $this->input('depositor_manual_name')) ?: null,
            'depositor_manual_id' => trim((string) $this->input('depositor_manual_id')) ?: null,
            'depositor_manual_phone' => trim((string) $this->input('depositor_manual_phone')) ?: null,
            'verified_by_signatory_id' => (int) $this->input('verified_by_signatory_id') ?: null,
            'received_by_signatory_id' => (int) $this->input('received_by_signatory_id') ?: null,
            'status' => $this->input('action') === 'draft' ? 'draft' : 'submitted',
        ];

        // If a manual depositor name was entered, that takes precedence over
        // any selected Master Data signatory for the Depositor line.
        if (!empty($header['depositor_manual_name'])) {
            $header['depositor_signatory_id'] = null;
        } else {
            $header['depositor_manual_name'] = null;
            $header['depositor_manual_id'] = null;
            $header['depositor_manual_phone'] = null;
        }

        if ($header['name'] === '') $errors[] = 'Name is required.';
        if ($header['deposit_date'] === '' || strtotime($header['deposit_date']) === false) $errors[] = 'A valid date is required.';

        $currencyIds = $_POST['currency_id'] ?? [];
        $amounts = $_POST['amount'] ?? [];
        $rates = $_POST['rate'] ?? [];

        $lines = [];
        $grandTotal = 0.0;
        foreach ($currencyIds as $i => $cid) {
            $cid = (int) $cid;
            // Amount and BDT Amount are whole numbers only (no fractional
            // part) per policy — Rate keeps decimal precision since FX
            // rates legitimately need it.
            $amount = round((float) ($amounts[$i] ?? 0));
            $rate = (float) ($rates[$i] ?? 0);
            if ($cid <= 0 || $amount <= 0) continue;
            $bdt = round($amount * $rate);
            $grandTotal += $bdt;
            $lines[] = ['currency_id' => $cid, 'amount' => $amount, 'rate' => $rate, 'bdt_amount' => $bdt];
        }

        if (empty($lines)) $errors[] = 'At least one currency line with a valid amount is required.';

        $header['total_bdt_amount'] = round($grandTotal);
        $header['amount_in_words'] = NumberToWords::convert(round($grandTotal));

        return [$header, $lines, $errors];
    }
}
