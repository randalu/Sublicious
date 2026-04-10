<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Perfect for trying out Sublicious. Up to 50 orders per month.',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'max_orders_per_month' => 50,
                'max_staff' => 3,
                'max_menu_items' => 30,
                'max_delivery_zones' => 1,
                'features' => [
                    'delivery' => true,
                    'hr_module' => false,
                    'reports_export' => false,
                    'api_integrations' => false,
                    'advanced_reports' => false,
                    'inventory' => false,
                    'discount_codes' => false,
                    'sms_notifications' => false,
                ],
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'For small restaurants. Up to 300 orders per month.',
                'price_monthly' => 1999, // $19.99
                'price_yearly' => 19990, // $199.90
                'max_orders_per_month' => 300,
                'max_staff' => 10,
                'max_menu_items' => 100,
                'max_delivery_zones' => 5,
                'features' => [
                    'delivery' => true,
                    'hr_module' => true,
                    'reports_export' => true,
                    'api_integrations' => true,
                    'advanced_reports' => false,
                    'inventory' => true,
                    'discount_codes' => true,
                    'sms_notifications' => true,
                ],
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'For growing restaurants. Up to 1,000 orders per month.',
                'price_monthly' => 4999, // $49.99
                'price_yearly' => 49990, // $499.90
                'max_orders_per_month' => 1000,
                'max_staff' => 25,
                'max_menu_items' => 500,
                'max_delivery_zones' => 15,
                'features' => [
                    'delivery' => true,
                    'hr_module' => true,
                    'reports_export' => true,
                    'api_integrations' => true,
                    'advanced_reports' => true,
                    'inventory' => true,
                    'discount_codes' => true,
                    'sms_notifications' => true,
                ],
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Unlimited orders and staff. Full feature access.',
                'price_monthly' => 9999, // $99.99
                'price_yearly' => 99990, // $999.90
                'max_orders_per_month' => 999999,
                'max_staff' => 999,
                'max_menu_items' => 9999,
                'max_delivery_zones' => 999,
                'features' => [
                    'delivery' => true,
                    'hr_module' => true,
                    'reports_export' => true,
                    'api_integrations' => true,
                    'advanced_reports' => true,
                    'inventory' => true,
                    'discount_codes' => true,
                    'sms_notifications' => true,
                ],
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
