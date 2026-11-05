<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <h4 class="mb-0">Dashboard</h4>
  <form method="get" action="<?= route_url('dashboard') ?>" class="d-flex gap-2">
    <input type="hidden" name="route" value="dashboard">
    <input type="month" name="month" value="<?= e($year_month) ?>" class="form-control form-control-sm" onchange="this.form.submit()">
  </form>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-md-3">
    <div class="widget-card">
      <div class="widget-label">Submitted Forms</div>
      <div class="widget-value"><?= (int) $active_count ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="widget-card">
      <div class="widget-label">Monthly Total (BDT)</div>
      <div class="widget-value">TK <?= format_money($monthly_total_bdt) ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="widget-card">
      <div class="widget-label">Total Operating Units</div>
      <div class="widget-value"><?= (int) $total_operating_units ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="widget-card">
      <div class="widget-label">Currencies In Use</div>
      <div class="widget-value"><?= count($by_currency) ?></div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-header">Operating Unit-wise Deposit Total</div>
      <div class="card-body"><canvas id="chartOU" height="180"></canvas></div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-header">Legal Entity-wise Deposit Total</div>
      <div class="card-body"><canvas id="chartLE" height="180"></canvas></div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-header">Product-wise Deposit Total</div>
      <div class="card-body"><canvas id="chartProduct" height="200"></canvas></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-header">Country-wise Deposit Total</div>
      <div class="card-body"><canvas id="chartCountry" height="200"></canvas></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-header">Currency-wise Deposit Total</div>
      <div class="card-body"><canvas id="chartCurrency" height="200"></canvas></div>
    </div>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header">Country-wise Monthly Submission &amp; Currency Mix</div>
  <div class="table-responsive">
    <table class="table table-sm mb-0">
      <thead><tr><th>Country</th><th>Form Count</th><th>Currency Mix</th><th class="text-end">Grand Total (BDT)</th></tr></thead>
      <tbody>
        <?php if (empty($country_breakdown)): ?>
          <tr><td colspan="4" class="text-center text-muted py-3">No data for this period.</td></tr>
        <?php else: foreach ($country_breakdown as $row): ?>
          <tr>
            <td><?= e($row['country'] ?: '—') ?></td>
            <td><?= (int) $row['form_count'] ?></td>
            <td><?= e($row['currency_mix'] ?: '—') ?></td>
            <td class="text-end">TK <?= format_money((float) $row['grand_total']) ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Recent Deposits</span>
    <a href="<?= route_url('invoices') ?>" class="small">View All &rarr;</a>
  </div>
  <div class="table-responsive">
    <table class="table table-sm mb-0">
      <thead><tr>
        <th>Invoice #</th><th>Date</th><th>Name</th><th>ID No.</th><th>Operating Unit</th>
        <th>Legal Entity</th><th>Product</th><th class="text-end">BDT Amount</th><th></th>
      </tr></thead>
      <tbody>
        <?php if (empty($recent)): ?>
          <tr><td colspan="9" class="text-center text-muted py-3">No deposits recorded yet.</td></tr>
        <?php else: foreach ($recent as $r): ?>
          <tr>
            <td><a href="<?= route_url('invoice/view', ['id' => $r['id']]) ?>"><?= e($r['invoice_number']) ?></a></td>
            <td><?= format_date($r['deposit_date']) ?></td>
            <td><?= e($r['name']) ?></td>
            <td><?= e($r['id_no']) ?></td>
            <td><?= e($r['operating_unit_name'] ?: '—') ?></td>
            <td><?= e($r['legal_entity_name'] ?: '—') ?></td>
            <td><?= e($r['product_name'] ?: '—') ?></td>
            <td class="text-end">TK <?= format_money((float) $r['total_bdt_amount']) ?></td>
            <td><a href="<?= route_url('invoice/view', ['id' => $r['id']]) ?>" class="btn btn-sm btn-outline-primary">Open</a></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function fcdfChart(canvasId, labels, data, type) {
  var el = document.getElementById(canvasId);
  if (!el) return;
  new Chart(el.getContext('2d'), {
    type: type || 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'BDT Amount',
        data: data,
        backgroundColor: ['#3f5e26','#5f8a3c','#84b060','#b4d38f','#dceac9','#2c4419','#6f9a4b'],
      }]
    },
    options: { plugins: { legend: { display: type === 'pie' } }, responsive: true }
  });
}
fcdfChart('chartOU', <?= json_encode(array_column($by_operating_unit, 'label')) ?>, <?= json_encode(array_map('floatval', array_column($by_operating_unit, 'total'))) ?>, 'bar');
fcdfChart('chartLE', <?= json_encode(array_column($by_legal_entity, 'label')) ?>, <?= json_encode(array_map('floatval', array_column($by_legal_entity, 'total'))) ?>, 'bar');
fcdfChart('chartProduct', <?= json_encode(array_column($by_product, 'label')) ?>, <?= json_encode(array_map('floatval', array_column($by_product, 'total'))) ?>, 'pie');
fcdfChart('chartCountry', <?= json_encode(array_column($by_country, 'label')) ?>, <?= json_encode(array_map('floatval', array_column($by_country, 'total'))) ?>, 'pie');
fcdfChart('chartCurrency', <?= json_encode(array_column($by_currency, 'label')) ?>, <?= json_encode(array_map('floatval', array_column($by_currency, 'total'))) ?>, 'pie');
</script>
