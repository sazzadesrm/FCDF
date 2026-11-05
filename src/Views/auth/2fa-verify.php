<h5 class="mb-3">Two-Factor Verification</h5>
<p class="text-muted small">Enter the 6-digit code from your authenticator app.</p>
<form method="post" action="<?= route_url('login/2fa') ?>">
  <?= csrf_field() ?>
  <div class="mb-3">
    <label class="form-label">Authentication Code</label>
    <input type="text" name="code" class="form-control text-center" style="letter-spacing:.3em;font-size:1.3rem" maxlength="6" pattern="\d{6}" required autofocus>
  </div>
  <button type="submit" class="btn btn-primary w-100">Verify</button>
</form>
<p class="text-center mt-3 mb-0 small"><a href="<?= route_url('login') ?>">Cancel and sign in again</a></p>
