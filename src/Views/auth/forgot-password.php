<h5 class="mb-3">Forgot Password</h5>
<p class="text-muted small">Enter your account email and we will send you a password reset link.</p>
<form method="post" action="<?= route_url('forgot-password') ?>">
  <?= csrf_field() ?>
  <div class="mb-3">
    <label class="form-label">Email Address</label>
    <input type="email" name="email" class="form-control" required autofocus>
  </div>
  <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
</form>
<p class="text-center mt-3 mb-0 small"><a href="<?= route_url('login') ?>">Back to Sign In</a></p>
