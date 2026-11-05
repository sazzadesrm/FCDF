<?php

namespace App\Controllers;

use App\Core\AuditLogger;
use App\Core\Controller;
use App\Models\Currency;
use App\Models\Department;
use App\Models\LegalEntity;
use App\Models\OperatingUnit;
use App\Models\Product;
use App\Models\PurposeOfTour;
use App\Models\Signatory;
use App\Models\VisitedCountry;

class MasterDataController extends Controller
{
    /** @var array<string,array{model:string,label:string,fields:array}> */
    private static array $registry = [
        'departments' => ['model' => Department::class, 'label' => 'Departments', 'fields' => ['name']],
        'legal-entities' => ['model' => LegalEntity::class, 'label' => 'Legal Entities', 'fields' => ['name']],
        'operating-units' => ['model' => OperatingUnit::class, 'label' => 'Operating Units', 'fields' => ['name', 'legal_entity_id']],
        'products' => ['model' => Product::class, 'label' => 'Products', 'fields' => ['name', 'legal_entity_id']],
        'countries' => ['model' => VisitedCountry::class, 'label' => 'Visited Countries', 'fields' => ['name']],
        'purposes' => ['model' => PurposeOfTour::class, 'label' => 'Purpose of Tour', 'fields' => ['name']],
        'signatories' => ['model' => Signatory::class, 'label' => 'Signatories', 'fields' => ['name', 'designation', 'role']],
        'currencies' => ['model' => Currency::class, 'label' => 'Currencies', 'fields' => ['code', 'name']],
    ];

    public static function types(): array
    {
        return self::$registry;
    }

    public function index(): void
    {
        require_admin();
        $type = (string) $this->input('type', 'departments');
        if (!isset(self::$registry[$type])) $type = 'departments';
        $entry = self::$registry[$type];
        /** @var string $model */
        $model = $entry['model'];

        $items = $model::all('id DESC');
        $legalEntities = LegalEntity::where(['is_active' => 1], 'name');

        $editItem = null;
        $editId = (int) $this->input('edit_id', 0);
        if ($editId > 0) {
            $editItem = $model::find($editId);
        }

        $this->render('master-data/index', [
            'types' => self::$registry,
            'type' => $type,
            'entry' => $entry,
            'items' => $items,
            'legal_entities' => $legalEntities,
            'edit_item' => $editItem,
        ]);
    }

    public function save(): void
    {
        require_admin();
        $this->requireCsrf();
        $type = (string) $this->input('type');
        if (!isset(self::$registry[$type])) { flash('error', 'Unknown master data type.'); redirect(route_url('master-data')); }
        $entry = self::$registry[$type];
        /** @var string $model */
        $model = $entry['model'];

        $id = (int) $this->input('id', 0);
        $data = [];
        foreach ($entry['fields'] as $field) {
            $data[$field] = trim((string) $this->input($field, '')) ?: null;
        }
        $data['is_active'] = $this->input('is_active') ? 1 : 0;

        if (empty($data[$entry['fields'][0]])) {
            flash('error', 'The primary field is required.');
            redirect(route_url('master-data', ['type' => $type]));
        }

        if ($id > 0) {
            $model::update($id, $data);
            AuditLogger::log('update', $type, $id, 'Updated master data record');
            flash('success', $entry['label'] . ' record updated.');
        } else {
            $newId = $model::insert($data);
            AuditLogger::log('create', $type, $newId, 'Created master data record');
            flash('success', $entry['label'] . ' record added.');
        }

        redirect(route_url('master-data', ['type' => $type]));
    }

    public function toggle(): void
    {
        require_admin();
        $this->requireCsrf();
        $type = (string) $this->input('type');
        $id = (int) $this->input('id');
        if (!isset(self::$registry[$type]) || !$id) { redirect(route_url('master-data')); }
        /** @var string $model */
        $model = self::$registry[$type]['model'];
        $row = $model::find($id);
        if ($row) {
            $model::update($id, ['is_active' => $row['is_active'] ? 0 : 1]);
            AuditLogger::log('toggle_active', $type, $id, 'Toggled active status');
        }
        redirect(route_url('master-data', ['type' => $type]));
    }

    public function destroy(): void
    {
        require_admin();
        $this->requireCsrf();
        $type = (string) $this->input('type');
        $id = (int) $this->input('id');
        if (!isset(self::$registry[$type]) || !$id) { redirect(route_url('master-data')); }
        /** @var string $model */
        $model = self::$registry[$type]['model'];
        try {
            $model::delete($id);
            AuditLogger::log('delete', $type, $id, 'Deleted master data record');
            flash('success', 'Record deleted.');
        } catch (\Throwable $e) {
            flash('error', 'Cannot delete: this record is referenced by existing deposit forms. Deactivate it instead.');
        }
        redirect(route_url('master-data', ['type' => $type]));
    }
}
