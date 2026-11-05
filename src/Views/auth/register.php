<h5 class="mb-3">Create Account</h5>
<form method="post" action="<?= route_url('register') ?>">
  <?= csrf_field() ?>
  <div class="mb-3">
    <label class="form-label">Full Name</label>
    <input type="text" name="full_name" class="form-control" value="<?= e(old('full_name')) ?>" required autofocus>
  </div>
  <div class="mb-3">
    <label class="form-label">Email Address</label>
    <input type="email" name="email" class="form-control" value="<?= e(old('email')) ?>" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Password</label>
    <input type="password" name="password" class="form-control" minlength="8" required>
    <div class="form-text">Minimum 8 characters.</div>
  </div>
  <div class="mb-3">
    <label class="form-label">Confirm Password</label>
    <input type="password" name="password_confirmation" class="form-control" minlength="8" required>
  </div>
  <button type="submit" class="btn btn-primary w-100">Sign Up</button>
</form>
<p class="text-center mt-3 mb-0 small">Already have an account? <a href="<?= route_url('login') ?>">Sign In</a></p>
