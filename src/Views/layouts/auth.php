<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FCDF Pro</title>
<link rel="icon" href="<?= asset_url('assets/img/favicon.ico') ?>" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="<?= asset_url('assets/img/favicon-32.png') ?>">
<link rel="apple-touch-icon" href="<?= asset_url('assets/img/apple-touch-icon.png') ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
/* Auth pages: form on the left, photo on the right (self-contained, independent of style.css) */
.fa-body {
  --fa-green: #3f5e26;
  --fa-green-dark: #314a1d;
  --fa-ink: #0f1a0c;
  margin: 0;
  min-height: 100vh;
  background: #fff;
  color: var(--fa-ink);
  font-family: 'Poppins', 'Roboto', Helvetica, Arial, sans-serif;
}
.fa-wrap { display: grid; grid-template-columns: 1fr 1fr; min-height: 100vh; }

.fa-main { display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 2.5rem 1.5rem; }
.fa-main-inner { width: 100%; max-width: 404px; }

.fa-logo { display: flex; align-items: center; gap: .6rem; margin-bottom: 2.25rem; }
.fa-logo img { width: 42px; height: 42px; flex: 0 0 42px; object-fit: contain; }
.fa-logo-name { font-size: 1.15rem; font-weight: 700; color: var(--fa-green); letter-spacing: -.01em; }

.fa-main h5 { font-size: 2rem; font-weight: 600; letter-spacing: -.02em; color: var(--fa-ink); margin: 0 0 .35rem; }
.fa-main h5 + p, .fa-sub { font-size: .9rem; color: #6b7280; margin: 0 0 1.75rem; }
.fa-main .form-label { font-size: .95rem; font-weight: 500; color: var(--fa-ink); margin-bottom: .3rem; }
.fa-main .form-control {
  height: 44px; border-radius: 10px; border: 1px solid #d5d9d2; background: #fff;
  padding: .5rem .8rem; font-size: .9rem; box-shadow: none;
}
.fa-main .form-control::placeholder { color: #b8bdb4; }
.fa-main .form-control:focus { border-color: var(--fa-green); box-shadow: 0 0 0 .2rem rgba(63,94,38,.18); }
.fa-main .form-text { color: #6b7280; }
.fa-main a { color: var(--fa-green); font-weight: 500; text-decoration: none; }
.fa-main a:hover { text-decoration: underline; }
.fa-main .btn-primary {
  height: 44px; border: 0; border-radius: 10px; font-weight: 600; color: #fff;
  background: var(--fa-green); box-shadow: 0 6px 14px rgba(63,94,38,.28);
}
.fa-main .btn-primary:hover, .fa-main .btn-primary:active { background: var(--fa-green-dark); color: #fff; }
.fa-main .btn-primary:focus-visible { outline: 3px solid rgba(63,94,38,.4); outline-offset: 2px; }
.fa-main .form-check-input:checked { background-color: var(--fa-green); border-color: var(--fa-green); }
.fa-main .form-check-input:focus { border-color: var(--fa-green); box-shadow: 0 0 0 .2rem rgba(63,94,38,.18); }
.fa-main .alert { border-radius: 10px; font-size: .88rem; }
.fa-row { display: flex; justify-content: space-between; align-items: baseline; }
.fa-row a { font-size: .82rem; }
.fa-switch { text-align: center; font-size: .9rem; margin: 1.25rem 0 0; }

.fa-credit { margin-top: 2.5rem; max-width: 404px; text-align: center; color: #4b5563; font-size: .85rem; font-weight: 500; line-height: 1.5; }

.fa-photo {
  background-color: #3f5e26;
  background-position: center;
  background-size: cover;
  background-repeat: no-repeat;
  border-radius: 32px 0 0 32px;
}

@media (max-width: 767.98px) {
  .fa-wrap { grid-template-columns: 1fr; grid-template-rows: 170px auto; }
  .fa-photo { order: -1; border-radius: 0 0 28px 28px; }
  .fa-main { padding: 1.75rem 1.5rem 2rem; justify-content: flex-start; }
  .fa-logo { margin-bottom: 1.5rem; }
  .fa-main h5 { font-size: 1.65rem; }
}
</style>
</head>
<body class="fa-body">
<div class="fa-wrap">
  <main class="fa-main">
    <div class="fa-main-inner">
      <div class="fa-logo">
        <img src="<?= asset_url('assets/img/logo.png') ?>" alt="FCDF Pro" width="36" height="36">
        <span class="fa-logo-name">FCDF Pro</span>
      </div>
      <?php foreach (get_flashes() as $type => $messages): foreach ($messages as $msg): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
          <?= e($msg) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endforeach; endforeach; ?>
      <?php $content(); ?>
    </div>
    <div class="fa-credit">
      Sazzad Kabir &middot; Executive Assistant (EA) to AMD &middot; +88-01810-076761 &middot; sazzadmbstu@gmail.com
    </div>
  </main>
  <aside class="fa-photo" role="img" aria-label="Green monstera leaves" style="background-image:url('<?= e(asset_url('assets/img/auth-plant.jpg')) ?>')"></aside>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
