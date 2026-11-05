<?php
$route = trim((string) ($_GET['route'] ?? 'dashboard'), '/');
if (!function_exists('nav_active')) {
    function nav_active($prefixes, $route) {
        foreach ((array) $prefixes as $prefix) {
            if (str_starts_with($route, $prefix)) return 'active';
        }
        return '';
    }
}
if (!function_exists('nav_icon')) {
    function nav_icon($d) {
        return '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . $d . '"/></svg>';
    }
}
$items = [
    ['Dashboard',    'dashboard',      ['dashboard'],            'M4 4h7v7H4z M13 4h7v4h-7z M13 10h7v10h-7z M4 13h7v7H4z', false],
    ['Deposit Form', 'deposit/create', ['deposit'],              'M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z M14 3v5h5 M12 12v6 M9 15h6', false],
    ['Invoices',     'invoices',       ['invoices', 'invoice/'], 'M8 6h13 M8 12h13 M8 18h13 M3 6h.01 M3 12h.01 M3 18h.01', false],
    ['Reports',      'reports',        ['reports'],              'M18 20V10 M12 20V4 M6 20v-6', false],
    ['Master Data',  'master-data',    ['master-data'],          'M4 6c0-1.7 3.6-3 8-3s8 1.3 8 3-3.6 3-8 3-8-1.3-8-3z M4 6v6c0 1.7 3.6 3 8 3s8-1.3 8-3V6 M4 12v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6', true],
    ['Audit Trail',  'audit',          ['audit'],                'M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z', true],
    ['Settings',     'settings',       ['settings'],             'M4 21v-7 M4 10V3 M12 21v-9 M12 8V3 M20 21v-5 M20 12V3 M1 14h6 M9 8h6 M17 16h6', true],
    ['My Profile',   'profile',        ['profile'],              'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2 M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z', false],
];
?>
<div class="sidebar-brand d-lg-none p-3 fw-bold">
  <img src="<?= asset_url('assets/img/logo.png') ?>" alt="FCDF Pro" width="36" height="36">
  <span>FCDF Pro</span>
</div>
<ul class="nav flex-column sidebar-nav">
  <?php foreach ($items as [$label, $to, $prefixes, $icon, $adminOnly]): ?>
    <?php if ($adminOnly && !is_admin()) continue; ?>
    <li class="nav-item"><a class="nav-link <?= nav_active($prefixes, $route) ?>" href="<?= route_url($to) ?>"><?= nav_icon($icon) ?><span><?= e($label) ?></span></a></li>
  <?php endforeach; ?>
</ul>
