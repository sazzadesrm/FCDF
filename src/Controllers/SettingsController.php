<?php

namespace App\Controllers;

use App\Config\Database;
use App\Core\AuditLogger;
use App\Core\Controller;
use App\Models\TermsCondition;

class SettingsController extends Controller
{
    public function index(): void
    {
        require_admin();
        $terms = TermsCondition::all('sort_order ASC');
        $settings = $this->allSettings();
        $this->render('settings/index', ['terms' => $terms, 'settings' => $settings]);
    }

    public function saveTerm(): void
    {
        require_admin();
        $this->requireCsrf();
        $id = (int) $this->input('id', 0);
        $content = trim((string) $this->input('content', ''));
        $sortOrder = (int) $this->input('sort_order', 0);
        $isActive = $this->input('is_active') ? 1 : 0;

        if ($content === '') {
            flash('error', 'Term content cannot be empty.');
            redirect(route_url('settings'));
        }

        if ($id > 0) {
            TermsCondition::update($id, ['content' => $content, 'sort_order' => $sortOrder, 'is_active' => $isActive]);
            AuditLogger::log('update', 'terms_conditions', $id);
            flash('success', 'Term updated.');
        } else {
            $newId = TermsCondition::insert(['content' => $content, 'sort_order' => $sortOrder, 'is_active' => $isActive]);
            AuditLogger::log('create', 'terms_conditions', $newId);
            flash('success', 'Term added.');
        }
        redirect(route_url('settings'));
    }

    public function deleteTerm(): void
    {
        require_admin();
        $this->requireCsrf();
        $id = (int) $this->input('id');
        TermsCondition::delete($id);
        AuditLogger::log('delete', 'terms_conditions', $id);
        flash('success', 'Term removed.');
        redirect(route_url('settings'));
    }

    public function toggleTerm(): void
    {
        require_admin();
        $this->requireCsrf();
        $id = (int) $this->input('id');
        $row = TermsCondition::find($id);
        if ($row) {
            TermsCondition::update($id, ['is_active' => $row['is_active'] ? 0 : 1]);
            AuditLogger::log('toggle_active', 'terms_conditions', $id);
        }
        redirect(route_url('settings'));
    }

    public function saveAppSettings(): void
    {
        require_admin();
        $this->requireCsrf();
        $pdo = Database::connect();
        $keys = ['app_name', 'support_contact_name', 'support_contact_phone',
                 'dev_admin_name', 'dev_admin_email', 'dev_admin_phone', 'dev_support_phone'];
        $stmt = $pdo->prepare('INSERT INTO app_settings (setting_key, setting_value) VALUES (:k, :v)
                                ON DUPLICATE KEY UPDATE setting_value = :v2');
        foreach ($keys as $key) {
            $value = trim((string) $this->input($key, ''));
            $stmt->execute(['k' => $key, 'v' => $value, 'v2' => $value]);
        }
        AuditLogger::log('update', 'app_settings', null, 'Application settings updated');
        flash('success', 'Settings saved.');
        redirect(route_url('settings'));
    }

    private function allSettings(): array
    {
        $pdo = Database::connect();
        $rows = $pdo->query('SELECT setting_key, setting_value FROM app_settings')->fetchAll();
        $out = [];
        foreach ($rows as $r) {
            $out[$r['setting_key']] = $r['setting_value'];
        }
        return $out;
    }
}
