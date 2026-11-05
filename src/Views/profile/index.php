<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">My Profile</h4>
</div>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-header">Profile Photo</div>
      <div class="card-body text-center">
        <?php if (!empty($user['avatar'])): ?>
          <img src="<?= base_url(e($user['avatar'])) ?>" class="rounded-circle mb-3" width="110" height="110" style="object-fit:cover">
        <?php else: ?>
          <div class="avatar-fallback mx-auto mb-3" style="width:110px;height:110px;font-size:2.5rem;background:#3f5e26;"><?= e(strtoupper(substr($user['full_name'],0,1))) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= route_url('profile/avatar') ?>" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp" class="form-control form-control-sm mb-2" required>
          <button type="submit" class="btn btn-sm btn-outline-primary w-100">Upload Photo</button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header">Two-Factor Authentication</div>
      <div class="card-body">
        <?php if (!empty($user['twofa_enabled'])): ?>
          <p class="small text-success mb-2">2FA is currently <strong>enabled</strong> on your account.</p>
          <form method="post" action="<?= route_url('profile/2fa-disable') ?>" onsubmit="return confirm('Disable two-factor authentication?');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-outline-danger w-100">Disable 2FA</button>
          </form>
        <?php else: ?>
          <p class="small text-muted mb-2">Add an extra layer of security using Google Authenticator or a compatible TOTP app.</p>
          <a href="<?= route_url('profile/2fa-setup') ?>" class="btn btn-sm btn-outline-primary w-100">Set Up 2FA</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header">Contact Information</div>
      <div class="card-body">
        <form method="post" action="<?= route_url('profile/update') ?>">
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small">Full Name</label>
              <input type="text" name="full_name" class="form-control" value="<?= e($user['full_name']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small">Email Address</label>
              <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label small">Phone</label>
              <input type="text" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>">
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-sm mt-3">Save Changes</button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header">Change Password</div>
      <div class="card-body">
        <form method="post" action="<?= route_url('profile/password') ?>">
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label small">Current Password</label>
              <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small">New Password</label>
              <input type="password" name="new_password" class="form-control" minlength="8" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small">Confirm New Password</label>
              <input type="password" name="new_password_confirmation" class="form-control" minlength="8" required>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-sm mt-3">Update Password</button>
        </form>
      </div>
    </div>
  </div>
</div>
