<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Deposit;
use App\Models\OperatingUnit;

class DashboardController extends Controller
{
    public function index(): void
    {
        require_login();
        $user = current_user();
        $userId = is_admin() ? null : $user['id']; // admins see all, users see own

        $yearMonth = (string) $this->input('month', date('Y-m'));

        $data = [
            'year_month' => $yearMonth,
            'active_count' => Deposit::monthlyCount($yearMonth, $userId),
            'monthly_total_bdt' => Deposit::monthlySumBdt($yearMonth, $userId),
            'total_operating_units' => OperatingUnit::count(['is_active' => 1]),
            'by_operating_unit' => Deposit::groupedMonthlyTotals('operating_unit_id', 'operating_units', 'name', $yearMonth, $userId),
            'by_legal_entity' => Deposit::groupedMonthlyTotals('legal_entity_id', 'legal_entities', 'name', $yearMonth, $userId),
            'by_product' => Deposit::groupedMonthlyTotals('product_id', 'products', 'name', $yearMonth, $userId),
            'by_country' => Deposit::groupedMonthlyTotals('visited_country_id', 'visited_countries', 'name', $yearMonth, $userId),
            'by_currency' => Deposit::currencyWiseMonthlyTotals($yearMonth, $userId),
            'country_breakdown' => Deposit::countryWiseMonthlyBreakdown($yearMonth, $userId),
            'recent' => Deposit::recent(10, $userId),
        ];

        $this->render('dashboard/index', $data);
    }
}
