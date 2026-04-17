<?php

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isSuperAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('app.dashboard');
    }
    return view('welcome');
})->name('home');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});

Route::post('/logout', function () {
    \App\Models\AuditLog::record('logout');
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout')->middleware('auth');

// Public customer order form
Route::get('/order/{slug}', \App\Livewire\Public\OnlineOrderForm::class)->name('order.form');

// QR menu viewer
Route::get('/menu/{token}', \App\Livewire\Public\QrMenuViewer::class)->name('menu.qr');

// Stripe webhook
Route::post('/webhooks/stripe', [\App\Http\Controllers\StripeWebhookController::class, 'handle'])
    ->name('webhooks.stripe');

/*
|--------------------------------------------------------------------------
| Business App Routes — authenticated, business-scoped
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'business'])->prefix('app')->name('app.')->group(function () {

    Route::get('/dashboard', \App\Livewire\App\Dashboard::class)->name('dashboard');

    // Orders
    Route::get('/orders', \App\Livewire\App\Orders\OrderBoard::class)->name('orders');
    Route::get('/orders/create', \App\Livewire\App\Orders\OrderCreate::class)->name('orders.create');
    Route::get('/orders/live', \App\Livewire\App\Orders\KitchenDisplay::class)->name('orders.live');
    Route::get('/orders/{order}', \App\Livewire\App\Orders\OrderDetail::class)->name('orders.show');

    // Tables
    Route::get('/tables', \App\Livewire\App\Tables\TableGrid::class)->name('tables');
    Route::get('/tables/{table}/session', \App\Livewire\App\Tables\TableSession::class)->name('tables.session');

    // Menu
    Route::get('/menu/items', \App\Livewire\App\Menu\ItemList::class)->name('menu.items');
    Route::get('/menu/items/create', \App\Livewire\App\Menu\ItemForm::class)->name('menu.items.create');
    Route::get('/menu/items/{item}/edit', \App\Livewire\App\Menu\ItemForm::class)->name('menu.items.edit');
    Route::get('/menu/categories', \App\Livewire\App\Menu\CategoryList::class)->name('menu.categories');
    Route::get('/menu/addons', \App\Livewire\App\Menu\AddonGroupManager::class)->name('menu.addons');
    Route::get('/menu', \App\Livewire\App\Menu\ItemList::class)->name('menu');

    // Delivery
    Route::get('/delivery', \App\Livewire\App\Delivery\DeliveryBoard::class)->name('delivery');
    Route::get('/delivery/riders', \App\Livewire\App\Delivery\RiderList::class)->name('delivery.riders');
    Route::get('/delivery/zones', \App\Livewire\App\Delivery\ZoneManager::class)->name('delivery.zones');
    Route::get('/delivery/commissions', \App\Livewire\App\Delivery\CommissionTracker::class)->name('delivery.commissions');

    // Billing
    Route::get('/billing', \App\Livewire\App\Billing\BillList::class)->name('billing');
    Route::get('/billing/{bill}', \App\Livewire\App\Billing\BillDetail::class)->name('billing.show');

    // Customers
    Route::get('/customers', \App\Livewire\App\Customers\CustomerList::class)->name('customers');
    Route::get('/customers/{customer}', \App\Livewire\App\Customers\CustomerProfile::class)->name('customers.show');

    // Employees
    Route::get('/employees', \App\Livewire\App\Employees\EmployeeList::class)->name('employees');
    Route::get('/employees/attendance', \App\Livewire\App\Employees\AttendanceBoard::class)->name('employees.attendance');
    Route::get('/employees/shifts', \App\Livewire\App\Employees\ShiftManager::class)->name('employees.shifts');
    Route::get('/employees/payroll', \App\Livewire\App\Employees\PayrollSummary::class)->name('employees.payroll');

    // Inventory
    Route::get('/inventory', \App\Livewire\App\Inventory\InventoryList::class)->name('inventory');
    Route::get('/inventory/{inventoryItem}/history', \App\Livewire\App\Inventory\InventoryHistory::class)->name('inventory.history');

    // Expenses
    Route::get('/expenses', \App\Livewire\App\Expenses\ExpenseList::class)->name('expenses');

    // Reports
    Route::get('/reports/financial', \App\Livewire\App\Reports\FinancialReport::class)->name('reports.financial');
    Route::get('/reports/orders', \App\Livewire\App\Reports\OrderReport::class)->name('reports.orders');
    Route::get('/reports/delivery', \App\Livewire\App\Reports\DeliveryReport::class)->name('reports.delivery');
    Route::get('/reports/employees', \App\Livewire\App\Reports\EmployeeReport::class)->name('reports.employees');

    // Notifications
    Route::get('/notifications', \App\Livewire\App\NotificationList::class)->name('notifications');

    // Settings
    Route::get('/settings/business', \App\Livewire\App\Settings\BusinessInfo::class)->name('settings.business');
    Route::get('/settings/operating-hours', \App\Livewire\App\Settings\OperatingHours::class)->name('settings.operating-hours');
    Route::get('/settings/integrations', \App\Livewire\App\Settings\Integrations::class)->name('settings.integrations');
    Route::get('/settings/billing-charges', \App\Livewire\App\Settings\BillingCharges::class)->name('settings.billing-charges');
    Route::get('/settings/discounts', \App\Livewire\App\Settings\DiscountCodes::class)->name('settings.discounts');
    Route::get('/settings/subscription', \App\Livewire\App\Settings\Subscription::class)->name('settings.subscription');
    Route::get('/settings/users', \App\Livewire\App\Settings\Users::class)->name('settings.users');
    Route::get('/settings/security', \App\Livewire\App\Settings\Security::class)->name('settings.security');
    Route::get('/settings/notifications', \App\Livewire\App\Settings\NotificationSettings::class)->name('settings.notifications');
});

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'super_admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->name('dashboard');

    // Businesses
    Route::get('/businesses', \App\Livewire\Admin\Businesses\BusinessList::class)->name('businesses');
    Route::get('/businesses/create', \App\Livewire\Admin\Businesses\BusinessForm::class)->name('businesses.create');
    Route::get('/businesses/{business}', \App\Livewire\Admin\Businesses\BusinessDetail::class)->name('businesses.show');
    Route::get('/businesses/{business}/edit', \App\Livewire\Admin\Businesses\BusinessForm::class)->name('businesses.edit');
    Route::get('/businesses/{business}/logs', \App\Livewire\Admin\Businesses\BusinessLogs::class)->name('businesses.logs');

    // Plans
    Route::get('/plans', \App\Livewire\Admin\Plans\PlanList::class)->name('plans');
    Route::get('/plans/create', \App\Livewire\Admin\Plans\PlanForm::class)->name('plans.create');
    Route::get('/plans/{plan}/edit', \App\Livewire\Admin\Plans\PlanForm::class)->name('plans.edit');

    // Subscriptions
    Route::get('/subscriptions', \App\Livewire\Admin\SubscriptionList::class)->name('subscriptions');

    // Logs
    Route::get('/logs', \App\Livewire\Admin\AuditLogViewer::class)->name('logs');

    // Settings
    Route::get('/settings/api-keys', \App\Livewire\Admin\Settings\ApiKeys::class)->name('settings.api-keys');
    Route::get('/settings/smtp', \App\Livewire\Admin\Settings\Smtp::class)->name('settings.smtp');
    Route::get('/settings/platform', \App\Livewire\Admin\Settings\Platform::class)->name('settings.platform');
});
