<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Reports</h4>
</div>

<div class="card mb-3 no-print">
  <div class="card-body">
    <form method="get" action="<?= route_url('reports') ?>" class="row g-2 align-items-end">
      <input type="hidden" name="route" value="reports">
      <input type="hidden" name="run" value="1">
      <div class="col-md-3">
        <label class="form-label small mb-1">Report Type</label>
        <select name="type" class="form-select form-select-sm">
          <?php foreach ($types as $key => $t): ?>
            <option value="<?= e($key) ?>" <?= $type === $key ? 'selected' : '' ?>><?= e($t['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-1">From Date</label>
        <input type="date" name="date_from" class="form-control form-control-sm" value="<?= e($date_from) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-1">To Date</label>
        <input type="date" name="date_to" class="form-control form-control-sm" value="<?= e($date_to) ?>">
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-primary btn-sm w-100">Run Report</button>
      </div>
    </form>
  </div>
</div>

<?php if ($ran): ?>
<div class="d-flex justify-content-end gap-2 mb-2 no-print">
  <button type="button" class="btn btn-sm btn-outline-primary" onclick="fcdfReportPrint()">Print</button>
  <button type="button" class="btn btn-sm btn-outline-primary" onclick="fcdfReportExport('pdf')">Export PDF</button>
  <button type="button" class="btn btn-sm btn-outline-primary" onclick="fcdfReportExport('jpg')">Export JPG</button>
  <button type="button" class="btn btn-sm btn-outline-primary" onclick="fcdfReportExport('png')">Export PNG</button>
</div>

<div class="card" id="reportCapture">
  <div class="card-header">
    <?= e($types[$type]['label']) ?> &mdash; <?= format_date($date_from) ?> to <?= format_date($date_to) ?>
  </div>
  <div class="table-responsive">
    <table class="table table-bordered mb-0">
      <thead>
        <tr>
          <?php if ($type === 'overall'): ?>
            <th>Invoice #</th><th>Date</th><th>Name</th><th class="text-end">BDT Amount</th>
          <?php else: ?>
            <th><?= e($types[$type]['label']) ?></th><th>Form Count</th><th class="text-end">Total BDT Amount</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="4" class="text-center text-muted py-4">No records found for this period.</td></tr>
        <?php else: $grand = 0; foreach ($rows as $r): $grand += (float) $r['total_bdt']; ?>
          <tr>
            <?php if ($type === 'overall'): ?>
              <td><?= e($r['group_label']) ?></td>
              <td><?= format_date($r['deposit_date']) ?></td>
              <td><?= e($r['name']) ?></td>
              <td class="text-end">TK <?= format_money((float) $r['total_bdt']) ?></td>
            <?php else: ?>
              <td><?= e($r['group_label'] ?: '—') ?></td>
              <td><?= (int) $r['form_count'] ?></td>
              <td class="text-end">TK <?= format_money((float) $r['total_bdt']) ?></td>
            <?php endif; ?>
          </tr>
        <?php endforeach; ?>
          <tr class="fw-bold">
            <td colspan="<?= $type === 'overall' ? 3 : 2 ?>" class="text-end">Grand Total</td>
            <td class="text-end">TK <?= format_money($grand) ?></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function fcdfReportPrint() {
  fcdfLogExport('<?= route_url('reports/log-export') ?>', { type: 'print' });
  fcdfPrintAsImage('#reportCapture', { windowWidth: 1000, landscape: true });
}
function fcdfReportExport(type) {
  fcdfLogExport('<?= route_url('reports/log-export') ?>', { type: type });
  fcdfExport('#reportCapture', type, '<?= e($types[$type]['label']) ?>', { windowWidth: 1000, orientation: 'landscape' });
}
</script>
<?php else: ?>
  <p class="text-muted">Select a report type and date range, then click "Run Report".</p>
<?php endif; ?>
