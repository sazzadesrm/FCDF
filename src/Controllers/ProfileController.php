<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\AuditLogger;
use App\Core\Controller;
use App\Helpers\TOTP;
use App\Models\User;

class ProfileController extends Controller
{
    public function index(): void
    {
        require_login();
        $user = User::find(current_user()['id']);
        $this->render('profile/index', ['user' => $user]);
    }

    public function update(): void
    {
        require_login();
        $this->requireCsrf();
        $id = current_user()['id'];
        $fullName = trim((string) $this->input('full_name', ''));
        $phone = trim((string) $this->input('phone', ''));

        if ($fullName === '') {
            flash('error', 'Full name is required.');
            redirect(route_url('profile'));
        }

        User::update($id, ['full_name' => $fullName, 'phone' => $phone]);
        $_SESSION['user']['full_name'] = $fullName;
        AuditLogger::log('profile_update', 'user', $id, 'Profile information updated');
        flash('success', 'Profile updated.');
        redirect(route_url('profile'));
    }

    public function uploadAvatar(): void
    {
        require_login();
        $this->requireCsrf();
        $id = current_user()['id'];

        if (empty($_FILES['avatar']['tmp_name']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'Please choose a valid image file.');
            redirect(route_url('profile'));
        }

        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['avatar']['tmp_name']);
        finfo_close($finfo);

        if (!isset($allowed[$mime]) || $_FILES['avatar']['size'] > 2 * 1024 * 1024) {
            flash('error', 'Avatar must be a JPG, PNG, or WEBP under 2MB.');
            redirect(route_url('profile'));
        }

        $filename = 'avatar_' . $id . '_' . time() . '.' . $allowed[$mime];
        // Saved under uploads/avatars (not storage/) so the browser
        // can actually load it — storage/ is blocked from direct web access
        // on purpose for security, but avatars need to be publicly viewable.
        $destDir = PUBLIC_PATH . '/uploads/avatars';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $dest = $destDir . '/' . $filename;
        if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
            flash('error', 'Could not save the uploaded file.');
            redirect(route_url('profile'));
        }

        User::update($id, ['avatar' => 'uploads/avatars/' . $filename]);
        $_SESSION['user']['avatar'] = 'uploads/avatars/' . $filename;
        AuditLogger::log('avatar_update', 'user', $id);
        flash('success', 'Profile photo updated.');
        redirect(route_url('profile'));
    }

    public function changePassword(): void
    {
        require_login();
        $this->requireCsrf();
        $id = current_user()['id'];
        $current = (string) $this->input('current_password', '');
        $new = (string) $this->input('new_password', '');
        $confirm = (string) $this->input('new_password_confirmation', '');

        $user = User::find($id);
        if (!$user || !password_verify($current, $user['password_hash'])) {
            flash('error', 'Your current password is incorrect.');
            redirect(route_url('profile'));
        }
        if (strlen($new) < 8 || $new !== $confirm) {
            flash('error', 'New password must be at least 8 characters and match confirmation.');
            redirect(route_url('profile'));
        }

        User::update($id, ['password_hash' => Auth::hashPassword($new)]);
        AuditLogger::log('password_change', 'user', $id, 'Password changed from profile');
        flash('success', 'Password updated successfully.');
        redirect(route_url('profile'));
    }

    public function setup2fa(): void
    {
        require_login();
        $user = User::find(current_user()['id']);
        $secret = $user['twofa_secret'] ?: TOTP::generateSecret();
        if (empty($user['twofa_secret'])) {
            User::update($user['id'], ['twofa_secret' => $secret]);
        }
        $uri = TOTP::getProvisioningUri($secret, $user['email']);
        $this->render('profile/2fa-setup', ['secret' => $secret, 'uri' => $uri, 'user' => $user]);
    }

    public function enable2fa(): void
    {
        require_login();
        $this->requireCsrf();
        $id = current_user()['id'];
        $user = User::find($id);
        $code = (string) $this->input('code', '');

        if (empty($user['twofa_secret']) || !TOTP::verify($user['twofa_secret'], $code)) {
            flash('error', 'Invalid code. Please scan the QR code again and try the current 6-digit code.');
            redirect(route_url('profile/2fa-setup'));
        }

        User::update($id, ['twofa_enabled' => 1]);
        AuditLogger::log('2fa_enabled', 'user', $id, 'Two-factor authentication enabled');
        flash('success', 'Two-factor authentication is now enabled on your account.');
        redirect(route_url('profile'));
    }

    public function disable2fa(): void
    {
        require_login();
        $this->requireCsrf();
        $id = current_user()['id'];
        User::update($id, ['twofa_enabled' => 0, 'twofa_secret' => null]);
        AuditLogger::log('2fa_disabled', 'user', $id, 'Two-factor authentication disabled');
        flash('success', 'Two-factor authentication disabled.');
        redirect(route_url('profile'));
    }
}
