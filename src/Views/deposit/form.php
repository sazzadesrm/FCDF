<?php
$isEdit = $mode === 'edit';
$d = $deposit ?: [];
$lines = $d['lines'] ?? [];
if (empty($lines)) $lines = [['currency_id' => '', 'amount' => '', 'rate' => '', 'bdt_amount' => 0]];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><?= $isEdit ? 'Edit Deposit Form' : 'Foreign Currency Deposit Transfer Form' ?></h4>
  <a href="<?= route_url('invoices') ?>" class="btn btn-sm btn-outline-secondary">Back to Invoices</a>
</div>

<form method="post" action="<?= route_url($isEdit ? 'deposit/update' : 'deposit/store') ?>" id="depositForm">
  <?= csrf_field() ?>
  <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int) $d['id'] ?>"><?php endif; ?>

  <div class="card mb-3">
    <div class="card-header">Header Information</div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Visited Country</label>
          <select name="visited_country_id" class="form-select searchable-select">
            <option value="">-- Select --</option>
            <?php foreach ($countries as $c): ?>
              <option value="<?= $c['id'] ?>" <?= (($d['visited_country_id'] ?? '') == $c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Date</label>
          <input type="date" name="deposit_date" class="form-control" value="<?= e($d['deposit_date'] ?? date('Y-m-d')) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Invoice Number</label>
          <input type="text" class="form-control" value="<?= $isEdit ? e($d['invoice_number']) : 'Auto-generated on submit' ?>" disabled>
        </div>

        <div class="col-md-4">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" value="<?= e($d['name'] ?? '') ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">ID No.</label>
          <input type="text" name="id_no" class="form-control" value="<?= e($d['id_no'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Department</label>
          <select name="department_id" class="form-select searchable-select">
            <option value="">-- Select --</option>
            <?php foreach ($departments as $x): ?>
              <option value="<?= $x['id'] ?>" <?= (($d['department_id'] ?? '') == $x['id']) ? 'selected' : '' ?>><?= e($x['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Product</label>
          <select name="product_id" class="form-select searchable-select">
            <option value="">-- Select --</option>
            <?php foreach ($products as $x): ?>
              <option value="<?= $x['id'] ?>" <?= (($d['product_id'] ?? '') == $x['id']) ? 'selected' : '' ?>><?= e($x['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Operating Unit</label>
          <select name="operating_unit_id" class="form-select searchable-select">
            <option value="">-- Select --</option>
            <?php foreach ($operating_units as $x): ?>
              <option value="<?= $x['id'] ?>" <?= (($d['operating_unit_id'] ?? '') == $x['id']) ? 'selected' : '' ?>><?= e($x['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Legal Entity</label>
          <select name="legal_entity_id" class="form-select searchable-select">
            <option value="">-- Select --</option>
            <?php foreach ($legal_entities as $x): ?>
              <option value="<?= $x['id'] ?>" <?= (($d['legal_entity_id'] ?? '') == $x['id']) ? 'selected' : '' ?>><?= e($x['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">EBS Number</label>
          <input type="text" name="ebs_number" class="form-control" placeholder="EBSADV-0000000" value="<?= e($d['ebs_number'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Purpose of Tour</label>
          <select name="purpose_of_tour_id" class="form-select searchable-select">
            <option value="">-- Select --</option>
            <?php foreach ($purposes as $x): ?>
              <option value="<?= $x['id'] ?>" <?= (($d['purpose_of_tour_id'] ?? '') == $x['id']) ? 'selected' : '' ?>><?= e($x['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Purpose of Tour (detail)</label>
          <input type="text" name="purpose_of_tour_text" class="form-control" value="<?= e($d['purpose_of_tour_text'] ?? '') ?>">
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>Foreign Currency Description</span>
      <button type="button" id="addCurrencyRow" class="btn btn-sm btn-outline-primary">+ Add Row</button>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead><tr><th>Currency</th><th>Amount</th><th>Rate</th><th>BDT Amount</th><th></th></tr></thead>
          <tbody id="currencyRows">
            <?php foreach ($lines as $line): ?>
            <tr class="currency-row">
              <td style="min-width:160px">
                <select name="currency_id[]" class="form-select searchable-select">
                  <option value="">-- Select --</option>
                  <?php foreach ($currencies as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (($line['currency_id'] ?? '') == $c['id']) ? 'selected' : '' ?>><?= e($c['code']) ?> — <?= e($c['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td style="min-width:120px"><input type="number" step="1" min="0" name="amount[]" class="form-control line-amount" value="<?= e($line['amount'] !== '' && $line['amount'] !== null ? (string) round((float) $line['amount']) : '') ?>"></td>
              <td style="min-width:120px"><input type="number" step="0.0001" min="0" name="rate[]" class="form-control line-rate" value="<?= e((string) ($line['rate'] ?? '')) ?>"></td>
              <td style="min-width:120px" class="line-bdt fw-semibold"><?= format_money((float) ($line['bdt_amount'] ?? 0)) ?></td>
              <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <template id="currencyRowTemplate">
        <tr class="currency-row">
          <td style="min-width:160px">
            <select name="currency_id[]" class="form-select searchable-select">
              <option value="">-- Select --</option>
              <?php foreach ($currencies as $c): ?>
                <option value="<?= $c['id'] ?>"><?= e($c['code']) ?> — <?= e($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td style="min-width:120px"><input type="number" step="1" min="0" name="amount[]" class="form-control line-amount"></td>
          <td style="min-width:120px"><input type="number" step="0.0001" min="0" name="rate[]" class="form-control line-rate"></td>
          <td style="min-width:120px" class="line-bdt fw-semibold">0</td>
          <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button></td>
        </tr>
      </template>

      <div class="d-flex justify-content-end mt-3">
        <div class="invoice-total-box text-end">
          TOTAL DEPOSITED BDT AMOUNT: TK <span id="grandTotalBdt">0</span>/=
          <div class="small fw-normal mt-1">Amount in Words: <span id="amountInWords">Taka Zero Only</span></div>
        </div>
      </div>
    </div>
  </div>

<?php
  // Signatory dropdowns are filtered by the role set in Master Data.
  // Depositor: depositor + verifier + receiver; Verified By: verifier only; Received By: receiver only.
  // A previously saved signatory is always kept so editing old records never drops the selection.
  $sigFor = function (array $roles, $currentId) use ($signatories): array {
      return array_values(array_filter($signatories, function ($s) use ($roles, $currentId) {
          return in_array($s['role'] ?? '', $roles, true) || (!empty($currentId) && (int) $currentId === (int) $s['id']);
      }));
  };
  $depositorOptions = $sigFor(['depositor', 'verifier', 'receiver'], $d['depositor_signatory_id'] ?? null);
  $verifierOptions  = $sigFor(['verifier'], $d['verified_by_signatory_id'] ?? null);
  $receiverOptions  = $sigFor(['receiver'], $d['received_by_signatory_id'] ?? null);
?>
  <div class="card mb-3">
    <div class="card-header">Signatures</div>
    <div class="card-body row g-3">
      <div class="col-md-4">
        <label class="form-label d-flex justify-content-between align-items-center">
          <span>Depositor</span>
          <span class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" id="depositorManualToggle" <?= !empty($d['depositor_manual_name']) ? 'checked' : '' ?>>
            <label class="form-check-label small text-muted" for="depositorManualToggle">Enter manually</label>
          </span>
        </label>
        <select name="depositor_signatory_id" id="depositorSelect" class="form-select searchable-select" <?= !empty($d['depositor_manual_name']) ? 'disabled' : '' ?>>
          <option value="">-- Select --</option>
          <?php foreach ($depositorOptions as $s): ?>
            <option value="<?= $s['id'] ?>" <?= (($d['depositor_signatory_id'] ?? '') == $s['id']) ? 'selected' : '' ?>><?= e($s['name']) ?><?= $s['designation'] ? ' — ' . e($s['designation']) : '' ?></option>
          <?php endforeach; ?>
        </select>
        <div id="depositorManualFields" class="mt-2 <?= empty($d['depositor_manual_name']) ? 'd-none' : '' ?>">
          <input type="text" name="depositor_manual_name" class="form-control form-control-sm mb-2" placeholder="Full Name" value="<?= e($d['depositor_manual_name'] ?? '') ?>">
          <input type="text" name="depositor_manual_id" class="form-control form-control-sm mb-2" placeholder="ID No." value="<?= e($d['depositor_manual_id'] ?? '') ?>">
          <input type="text" name="depositor_manual_phone" class="form-control form-control-sm" placeholder="Phone Number" value="<?= e($d['depositor_manual_phone'] ?? '') ?>">
        </div>
      </div>
      <div class="col-md-4">
        <label class="form-label">Verified By</label>
        <select name="verified_by_signatory_id" class="form-select searchable-select">
          <option value="">-- Select --</option>
          <?php foreach ($verifierOptions as $s): ?>
            <option value="<?= $s['id'] ?>" <?= (($d['verified_by_signatory_id'] ?? '') == $s['id']) ? 'selected' : '' ?>><?= e($s['name']) ?><?= $s['designation'] ? ' — ' . e($s['designation']) : '' ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Received By</label>
        <select name="received_by_signatory_id" class="form-select searchable-select">
          <option value="">-- Select --</option>
          <?php foreach ($receiverOptions as $s): ?>
            <option value="<?= $s['id'] ?>" <?= (($d['received_by_signatory_id'] ?? '') == $s['id']) ? 'selected' : '' ?>><?= e($s['name']) ?><?= $s['designation'] ? ' — ' . e($s['designation']) : '' ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label">Special Note</label>
        <textarea name="special_note" class="form-control" rows="2"><?= e($d['special_note'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <script>
  (function () {
    var toggle = document.getElementById('depositorManualToggle');
    var select = document.getElementById('depositorSelect');
    var manualFields = document.getElementById('depositorManualFields');
    if (!toggle) return;
    toggle.addEventListener('change', function () {
      if (toggle.checked) {
        manualFields.classList.remove('d-none');
        select.setAttribute('disabled', 'disabled');
        select.value = '';
        if (window.jQuery && jQuery.fn.select2) jQuery(select).val('').trigger('change');
      } else {
        manualFields.classList.add('d-none');
        select.removeAttribute('disabled');
      }
    });
  })();
  </script>

  <div class="d-flex gap-2">
    <button type="submit" name="action" value="submit" class="btn btn-primary">Submit</button>
    <?php if (!$isEdit): ?>
      <button type="submit" name="action" value="draft" class="btn btn-outline-secondary">Save Draft</button>
    <?php endif; ?>
    <a href="<?= route_url('invoices') ?>" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>

<script src="<?= asset_url('assets/js/deposit-form.js') ?>"></script>
