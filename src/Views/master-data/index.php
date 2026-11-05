<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Master Data</h4>
</div>

<ul class="nav nav-tabs mb-3">
  <?php foreach ($types as $key => $t): ?>
    <li class="nav-item">
      <a class="nav-link <?= $type === $key ? 'active' : '' ?>" href="<?= route_url('master-data', ['type' => $key]) ?>"><?= e($t['label']) ?></a>
    </li>
  <?php endforeach; ?>
</ul>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="card" id="mdFormCard">
      <div class="card-header"><?= $edit_item ? 'Edit — ' . e($entry['label']) : 'Add New — ' . e($entry['label']) ?></div>
      <div class="card-body">
        <form method="post" action="<?= route_url('master-data/save') ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="type" value="<?= e($type) ?>">
          <?php if ($edit_item): ?><input type="hidden" name="id" value="<?= (int) $edit_item['id'] ?>"><?php endif; ?>
          <?php foreach ($entry['fields'] as $field): ?>
            <div class="mb-2">
              <label class="form-label small text-capitalize"><?= e(str_replace('_', ' ', $field)) ?></label>
              <?php if ($field === 'legal_entity_id'): ?>
                <select name="legal_entity_id" class="form-select form-select-sm searchable-select">
                  <option value="">-- None --</option>
                  <?php foreach ($legal_entities as $le): ?>
                    <option value="<?= $le['id'] ?>" <?= $edit_item && (int) ($edit_item['legal_entity_id'] ?? 0) === (int) $le['id'] ? 'selected' : '' ?>><?= e($le['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              <?php elseif ($field === 'role'): ?>
                <select name="role" class="form-select form-select-sm">
                  <?php foreach (['depositor' => 'Depositor', 'verifier' => 'Verifier', 'receiver' => 'Receiver', 'approved' => 'Approved'] as $val => $lbl): ?>
                    <option value="<?= $val ?>" <?= $edit_item && ($edit_item['role'] ?? '') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                  <?php endforeach; ?>
                </select>
              <?php else: ?>
                <input type="text" name="<?= e($field) ?>" class="form-control form-control-sm" value="<?= e((string) ($edit_item[$field] ?? '')) ?>">
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
          <div class="form-check mb-2">
            <input type="checkbox" name="is_active" class="form-check-input" id="newActive" <?= (!$edit_item || $edit_item['is_active']) ? 'checked' : '' ?>>
            <label class="form-check-label small" for="newActive">Active</label>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary"><?= $edit_item ? 'Update Record' : 'Add Record' ?></button>
            <?php if ($edit_item): ?>
              <a href="<?= route_url('master-data', ['type' => $type]) ?>" class="btn btn-sm btn-outline-secondary">Cancel Edit</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card">
      <div class="card-header"><?= e($entry['label']) ?> List</div>
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead><tr>
            <?php foreach ($entry['fields'] as $field): ?><th class="text-capitalize"><?= e(str_replace('_', ' ', $field)) ?></th><?php endforeach; ?>
            <th>Status</th><th></th>
          </tr></thead>
          <tbody>
            <?php if (empty($items)): ?>
              <tr><td colspan="<?= count($entry['fields']) + 2 ?>" class="text-center text-muted py-3">No records yet.</td></tr>
            <?php else: foreach ($items as $item): ?>
              <tr class="<?= $edit_item && (int) $edit_item['id'] === (int) $item['id'] ? 'table-active' : '' ?>">
                <?php foreach ($entry['fields'] as $field): ?>
                  <td>
                    <?php if ($field === 'legal_entity_id'):
                      $leName = '—';
                      foreach ($legal_entities as $le) { if ($le['id'] == ($item['legal_entity_id'] ?? null)) { $leName = $le['name']; break; } }
                      echo e($leName);
                    else: echo e((string) ($item[$field] ?? '—')); endif; ?>
                  </td>
                <?php endforeach; ?>
                <td>
                  <span class="badge bg-<?= $item['is_active'] ? 'success' : 'secondary' ?>"><?= $item['is_active'] ? 'Active' : 'Inactive' ?></span>
                </td>
                <td class="text-nowrap">
                  <a href="<?= route_url('master-data', ['type' => $type, 'edit_id' => $item['id']]) ?>#mdFormCard" class="btn btn-sm btn-outline-primary">Edit</a>
                  <form method="post" action="<?= route_url('master-data/toggle') ?>" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="type" value="<?= e($type) ?>">
                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-secondary"><?= $item['is_active'] ? 'Deactivate' : 'Activate' ?></button>
                  </form>
                  <form method="post" action="<?= route_url('master-data/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this record?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="type" value="<?= e($type) ?>">
                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
