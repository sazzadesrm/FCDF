<?php $totalPages = max(1, (int) ceil($total / $per_page)); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Audit Trail</h4>
</div>

<div class="card mb-3">
  <div class="card-body">
    <form method="get" action="<?= route_url('audit') ?>" class="row g-2">
      <input type="hidden" name="route" value="audit">
      <div class="col-md-4">
        <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Search by user, action, entity..." value="<?= e($keyword) ?>">
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-sm btn-primary w-100">Search</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-sm mb-0">
      <thead><tr><th>Date/Time</th><th>User</th><th>Action</th><th>Entity</th><th>Details</th><th>IP Address</th></tr></thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No audit records found.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td class="text-nowrap"><?= format_date($r['created_at'], 'd M Y, H:i:s') ?></td>
            <td><?= e($r['user_name'] ?: 'System') ?></td>
            <td><span class="badge bg-light text-dark border"><?= e($r['action']) ?></span></td>
            <td><?= e($r['entity'] ?: '—') ?><?= $r['entity_id'] ? ' #' . (int) $r['entity_id'] : '' ?></td>
            <td class="small"><?= e($r['details'] ?: '—') ?></td>
            <td class="small text-muted"><?= e($r['ip_address']) ?></td>
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
        <a class="page-link" href="<?= route_url('audit', ['page' => $p, 'keyword' => $keyword]) ?>"><?= $p ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php endif; ?>
