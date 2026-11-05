<h5>Welcome back</h5>
<p class="fa-sub">Sign in to continue to your deposit forms.</p>
<form method="post" action="<?= route_url('login') ?>">
  <?= csrf_field() ?>
  <div class="mb-3">
    <label class="form-label" for="email">Email address</label>
    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" value="<?= e(old('email')) ?>" required autofocus>
  </div>
  <div class="mb-3">
    <div class="fa-row">
      <label class="form-label" for="password">Password</label>
      <a href="<?= route_url('forgot-password') ?>">Forgot password?</a>
    </div>
    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
  </div>
  <div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" name="remember" id="remember">
    <label class="form-check-label small" for="remember">Remember me</label>
  </div>
  <button type="submit" class="btn btn-primary w-100">Sign in</button>
</form>
<p class="fa-switch">New here? <a href="<?= route_url('register') ?>">Create an account</a></p>
