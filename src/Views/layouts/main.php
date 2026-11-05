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
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= asset_url('assets/css/theme.css') ?>">
</head>
<body>
<nav class="navbar navbar-expand-lg app-navbar sticky-top">
  <div class="container-fluid">
    <button class="btn btn-sm btn-outline-secondary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
      <span aria-hidden="true">&#9776;</span>
    </button>
    <a class="navbar-brand" href="<?= route_url('dashboard') ?>"><img src="<?= asset_url('assets/img/logo.png') ?>" alt="FCDF Pro" width="36" height="36"><span>FCDF Pro</span></a>
    <div class="ms-auto d-flex align-items-center gap-3">
      <?php if (is_logged_in()): $u = current_user(); ?>
        <span class="app-role-pill d-none d-md-inline"><?= e($u['role'] === 'admin' ? 'Administrator' : 'Standard User') ?></span>
        <div class="dropdown">
          <a class="app-user-link d-flex align-items-center text-decoration-none dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <?php if (!empty($u['avatar'])): ?>
              <img src="<?= base_url(e($u['avatar'])) ?>" class="rounded-circle me-2" width="30" height="30" style="object-fit:cover">
            <?php else: ?>
              <span class="avatar-fallback me-2"><?= e(strtoupper(substr($u['full_name'],0,1))) ?></span>
            <?php endif; ?>
            <span class="d-none d-md-inline"><?= e($u['full_name']) ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= route_url('profile') ?>">My Profile</a></li>
            <?php if (is_admin()): ?><li><a class="dropdown-item" href="<?= route_url('settings') ?>">Settings</a></li><?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= route_url('logout') ?>">Sign Out</a></li>
          </ul>
        </div>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="app-shell">
  <div class="offcanvas-lg offcanvas-start app-sidebar" tabindex="-1" id="sidebarOffcanvas">
    <div class="offcanvas-body p-0">
      <?php require __DIR__ . '/../partials/sidebar.php'; ?>
    </div>
  </div>

  <main class="app-content">
    <div class="container-fluid py-3">
      <?php foreach (get_flashes() as $type => $messages): foreach ($messages as $msg): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
          <?= e($msg) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endforeach; endforeach; ?>

      <?php $content(); ?>
    </div>
  </main>
</div>

<footer class="app-footer text-center py-3 small text-muted">
  FCDF Pro &middot; Walton Hi-Tech Industries PLC &middot;
  System Admin: Sazzad Kabir &middot; +88-01810-076761 &middot; sazzadmbstu@gmail.com &middot; Support: +88-01678-028928
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="<?= asset_url('assets/js/app.js') ?>"></script>
</body>
</html>
