<?php

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\DepositController;
use App\Controllers\InvoiceController;
use App\Controllers\ReportController;
use App\Controllers\MasterDataController;
use App\Controllers\SettingsController;
use App\Controllers\ProfileController;
use App\Controllers\AuditController;

$router = new Router();

// ---------------------------------------------------------------------
// Authentication (public routes)
// ---------------------------------------------------------------------
$router->get('login', fn() => (new AuthController())->showLogin());
$router->post('login', fn() => (new AuthController())->login());
$router->get('login/2fa', fn() => (new AuthController())->show2fa());
$router->post('login/2fa', fn() => (new AuthController())->verify2fa());
$router->get('register', fn() => (new AuthController())->showRegister());
$router->post('register', fn() => (new AuthController())->register());
$router->get('verify-email', fn() => (new AuthController())->verifyEmail());
$router->get('forgot-password', fn() => (new AuthController())->showForgotPassword());
$router->post('forgot-password', fn() => (new AuthController())->sendResetLink());
$router->get('reset-password', fn() => (new AuthController())->showResetPassword());
$router->post('reset-password', fn() => (new AuthController())->resetPassword());
$router->get('logout', fn() => (new AuthController())->logout());

// ---------------------------------------------------------------------
// Dashboard
// ---------------------------------------------------------------------
$router->get('dashboard', fn() => (new DashboardController())->index());

// ---------------------------------------------------------------------
// Deposit Form (core module)
// ---------------------------------------------------------------------
$router->get('deposit/create', fn() => (new DepositController())->create());
$router->post('deposit/store', fn() => (new DepositController())->store());
$router->get('deposit/edit', fn() => (new DepositController())->edit());
$router->post('deposit/update', fn() => (new DepositController())->update());
$router->post('deposit/delete', fn() => (new DepositController())->destroy());

// ---------------------------------------------------------------------
// Invoices
// ---------------------------------------------------------------------
$router->get('invoices', fn() => (new InvoiceController())->index());
$router->get('invoice/view', fn() => (new InvoiceController())->view());
$router->post('invoice/log-export', fn() => (new InvoiceController())->logExport());

// ---------------------------------------------------------------------
// Reports
// ---------------------------------------------------------------------
$router->get('reports', fn() => (new ReportController())->index());
$router->post('reports/log-export', fn() => (new ReportController())->logExport());

// ---------------------------------------------------------------------
// Master Data
// ---------------------------------------------------------------------
$router->get('master-data', fn() => (new MasterDataController())->index());
$router->post('master-data/save', fn() => (new MasterDataController())->save());
$router->post('master-data/toggle', fn() => (new MasterDataController())->toggle());
$router->post('master-data/delete', fn() => (new MasterDataController())->destroy());

// ---------------------------------------------------------------------
// Settings
// ---------------------------------------------------------------------
$router->get('settings', fn() => (new SettingsController())->index());
$router->post('settings/term/save', fn() => (new SettingsController())->saveTerm());
$router->post('settings/term/toggle', fn() => (new SettingsController())->toggleTerm());
$router->post('settings/term/delete', fn() => (new SettingsController())->deleteTerm());
$router->post('settings/app/save', fn() => (new SettingsController())->saveAppSettings());

// ---------------------------------------------------------------------
// Profile
// ---------------------------------------------------------------------
$router->get('profile', fn() => (new ProfileController())->index());
$router->post('profile/update', fn() => (new ProfileController())->update());
$router->post('profile/avatar', fn() => (new ProfileController())->uploadAvatar());
$router->post('profile/password', fn() => (new ProfileController())->changePassword());
$router->get('profile/2fa-setup', fn() => (new ProfileController())->setup2fa());
$router->post('profile/2fa-enable', fn() => (new ProfileController())->enable2fa());
$router->post('profile/2fa-disable', fn() => (new ProfileController())->disable2fa());

// ---------------------------------------------------------------------
// Audit Trail (admin only)
// ---------------------------------------------------------------------
$router->get('audit', fn() => (new AuditController())->index());

return $router;
