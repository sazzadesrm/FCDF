<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Settings</h4>
</div>

<div class="row g-3 mb-3">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">Add / Edit Term &amp; Condition</div>
      <div class="card-body">
        <form method="post" action="<?= route_url('settings/term/save') ?>">
          <?= csrf_field() ?>
          <div class="mb-2">
            <label class="form-label small">Content</label>
            <textarea name="content" class="form-control form-control-sm" rows="3" required></textarea>
          </div>
          <div class="row g-2">
            <div class="col-6">
              <label class="form-label small">Sort Order</label>
              <input type="number" name="sort_order" class="form-control form-control-sm" value="0">
            </div>
            <div class="col-6 d-flex align-items-end">
              <div class="form-check">
                <input type="checkbox" name="is_active" class="form-check-input" id="tcActive" checked>
                <label class="form-check-label small" for="tcActive">Active</label>
              </div>
            </div>
          </div>
          <button type="submit" class="btn btn-sm btn-primary mt-2">Save Term</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">Current Terms &amp; Conditions</div>
      <div class="list-group list-group-flush">
        <?php if (empty($terms)): ?>
          <div class="list-group-item text-muted small">No terms defined.</div>
        <?php else: foreach ($terms as $t): ?>
          <div class="list-group-item">
            <div class="d-flex justify-content-between">
              <span class="badge bg-<?= $t['is_active'] ? 'success' : 'secondary' ?> me-2"><?= $t['is_active'] ? 'Active' : 'Inactive' ?></span>
              <div class="d-flex gap-1">
                <form method="post" action="<?= route_url('settings/term/toggle') ?>">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= $t['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-2"><?= $t['is_active'] ? 'Deactivate' : 'Activate' ?></button>
                </form>
                <form method="post" action="<?= route_url('settings/term/delete') ?>" onsubmit="return confirm('Delete this term?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= $t['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">Delete</button>
                </form>
              </div>
            </div>
            <div class="small mt-1"><?= e($t['content']) ?></div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header">Application Settings</div>
  <div class="card-body">
    <form method="post" action="<?= route_url('settings/app/save') ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label small">Application Name</label>
          <input type="text" name="app_name" class="form-control form-control-sm" value="<?= e($settings['app_name'] ?? 'FCDF Pro') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label small">Support Contact Name</label>
          <input type="text" name="support_contact_name" class="form-control form-control-sm" value="<?= e($settings['support_contact_name'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label small">Support Contact Phone</label>
          <input type="text" name="support_contact_phone" class="form-control form-control-sm" value="<?= e($settings['support_contact_phone'] ?? '') ?>">
        </div>
      </div>
      <button type="submit" class="btn btn-sm btn-primary mt-3">Save Settings</button>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">Developer &amp; Support Information</div>
  <div class="card-body">
    <table class="table table-sm mb-0">
      <tr><td class="text-muted">System Admin</td><td><?= e($settings['dev_admin_name'] ?? 'Sazzad Kabir') ?></td></tr>
      <tr><td class="text-muted">Email</td><td><?= e($settings['dev_admin_email'] ?? 'sazzadmbstu@gmail.com') ?></td></tr>
      <tr><td class="text-muted">Admin Contact</td><td><?= e($settings['dev_admin_phone'] ?? '+88-01810-076761') ?></td></tr>
      <tr><td class="text-muted">Support</td><td><?= e($settings['dev_support_phone'] ?? '+88-01678-028928') ?></td></tr>
    </table>
  </div>
</div>
