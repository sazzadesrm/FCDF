<?php
$rows = $result['rows']; $total = $result['total']; $page = $result['page']; $perPage = $result['per_page'];
$totalPages = max(1, (int) ceil($total / $perPage));
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Invoices</h4>
  <a href="<?= route_url('deposit/create') ?>" class="btn btn-primary btn-sm">+ New Deposit Form</a>
</div>

<div class="card mb-3">
  <div class="card-body">
    <form method="get" action="<?= route_url('invoices') ?>" class="row g-2">
      <input type="hidden" name="route" value="invoices">
      <div class="col-md-3">
        <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Invoice #, name, ID..." value="<?= e($filters['keyword']) ?>">
      </div>
      <div class="col-md-2">
        <input type="date" name="date_from" class="form-control form-control-sm" value="<?= e($filters['date_from']) ?>">
      </div>
      <div class="col-md-2">
        <input type="date" name="date_to" class="form-control form-control-sm" value="<?= e($filters['date_to']) ?>">
      </div>
      <div class="col-md-2">
        <select name="department_id" class="form-select form-select-sm">
          <option value="">All Departments</option>
          <?php foreach ($departments as $d): ?>
            <option value="<?= $d['id'] ?>" <?= $filters['department_id'] == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="legal_entity_id" class="form-select form-select-sm">
          <option value="">All Legal Entities</option>
          <?php foreach ($legal_entities as $le): ?>
            <option value="<?= $le['id'] ?>" <?= $filters['legal_entity_id'] == $le['id'] ? 'selected' : '' ?>><?= e($le['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-1">
        <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr>
        <th>Invoice #</th><th>Date</th><th>Name</th><th>ID No.</th>
        <th>Operating Unit</th><th>Legal Entity</th><th class="text-end">BDT Amount</th><th>Status</th><th></th>
      </tr></thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="9" class="text-center text-muted py-4">No invoices found.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td><a href="<?= route_url('invoice/view', ['id' => $r['id']]) ?>" class="fw-semibold"><?= e($r['invoice_number']) ?></a></td>
            <td><?= format_date($r['deposit_date']) ?></td>
            <td><?= e($r['name']) ?></td>
            <td><?= e($r['id_no']) ?></td>
            <td><?= e($r['operating_unit_name'] ?: '—') ?></td>
            <td><?= e($r['legal_entity_name'] ?: '—') ?></td>
            <td class="text-end">TK <?= format_money((float) $r['total_bdt_amount']) ?></td>
            <td><span class="badge bg-<?= $r['status'] === 'draft' ? 'secondary' : 'success' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
            <td class="text-nowrap">
              <a href="<?= route_url('invoice/view', ['id' => $r['id']]) ?>" class="btn btn-sm btn-outline-primary">View</a>
              <a href="<?= route_url('deposit/edit', ['id' => $r['id']]) ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
              <form method="post" action="<?= route_url('deposit/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this deposit form?');">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($totalPages > 1): ?>
<nav class="mt-3">
  <ul class="pagination pagination-sm justify-content-center">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
      <li class="page-item <?= $p == $page ? 'active' : '' ?>">
        <a class="page-link" href="<?= route_url('invoices', array_merge($filters, ['page' => $p])) ?>"><?= $p ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php endif; ?>
