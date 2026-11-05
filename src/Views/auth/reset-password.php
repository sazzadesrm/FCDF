<h5 class="mb-3">Reset Password</h5>
<form method="post" action="<?= route_url('reset-password') ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="token" value="<?= e($token) ?>">
  <div class="mb-3">
    <label class="form-label">New Password</label>
    <input type="password" name="password" class="form-control" minlength="8" required autofocus>
  </div>
  <div class="mb-3">
    <label class="form-label">Confirm New Password</label>
    <input type="password" name="password_confirmation" class="form-control" minlength="8" required>
  </div>
  <button type="submit" class="btn btn-primary w-100">Reset Password</button>
</form>
