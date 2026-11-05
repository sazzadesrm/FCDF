<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Set Up Two-Factor Authentication</h4>
</div>

<div class="card" style="max-width:520px">
  <div class="card-body text-center">
    <p class="text-muted small">Scan this QR code with Google Authenticator, Microsoft Authenticator, or any compatible TOTP app.</p>
    <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?= urlencode($uri) ?>" alt="2FA QR Code" class="mb-3">
    <p class="small">Or enter this key manually:</p>
    <code class="d-block mb-3"><?= e($secret) ?></code>

    <form method="post" action="<?= route_url('profile/2fa-enable') ?>">
      <?= csrf_field() ?>
      <label class="form-label small">Enter the 6-digit code to confirm</label>
      <input type="text" name="code" class="form-control text-center mb-3" style="letter-spacing:.3em;font-size:1.3rem" maxlength="6" pattern="\d{6}" required>
      <button type="submit" class="btn btn-primary w-100">Enable 2FA</button>
    </form>
    <a href="<?= route_url('profile') ?>" class="d-block mt-3 small">Cancel</a>
  </div>
</div>
