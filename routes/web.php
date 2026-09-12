<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\ServiceController;
use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\PricingController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\KnowledgeBaseController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboard;
use App\Http\Controllers\Customer\TicketController as CustomerTicket;
use App\Http\Controllers\Customer\InvoiceController as CustomerInvoice;
use App\Http\Controllers\Customer\ProjectController as CustomerProject;
use App\Http\Controllers\Customer\ServiceRequestController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\TicketController as AdminTicket;
use App\Http\Controllers\Admin\ProjectController as AdminProject;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\ServiceController as AdminService;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoice;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\FinancialController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\BlogController as AdminBlog;
use App\Http\Controllers\Admin\KnowledgeBaseController as AdminKB;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\UsefulLinkController;
use App\Http\Controllers\Admin\LinkSubmissionController;
use App\Http\Controllers\Public\UsefulLinksController;
use App\Http\Controllers\Admin\ManualController;
use App\Http\Controllers\Public\LegalController;
use App\Http\Controllers\Auth\MfaController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Public\ContentPageController;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/sitemap.xml', [\App\Http\Controllers\Public\SitemapController::class, 'index'])->name('sitemap');
Route::get('/lang/{locale}', [\App\Http\Controllers\Public\LanguageController::class, 'switch'])->name('lang.switch');
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('services.show');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::post('/blog/{slug}/comment', [BlogController::class, 'comment'])->name('blog.comment');

Route::get('/knowledge-base', [KnowledgeBaseController::class, 'index'])->name('kb.index');
Route::get('/knowledge-base/{slug}', [KnowledgeBaseController::class, 'show'])->name('kb.show');
Route::post('/knowledge-base/{slug}/vote', [KnowledgeBaseController::class, 'vote'])->name('kb.vote');

Route::get('/faq', fn () => view('public.faq'))->name('faq');
Route::get('/get-quote', [\App\Http\Controllers\Public\QuoteController::class, 'create'])->name('get-quote');
Route::post('/get-quote', [\App\Http\Controllers\Public\QuoteController::class, 'store'])->middleware('throttle:10,1')->name('get-quote.submit');
Route::get('/currency/{currency}', [\App\Http\Controllers\Public\CurrencyController::class, 'switch'])->name('currency.switch');
Route::get('/careers', fn () => view('public.careers'))->name('careers');
Route::get('/industries', fn () => view('public.industries'))->name('industries');
Route::get('/offices', fn () => view('public.offices'))->name('offices');
Route::get('/case-studies', [ContentPageController::class, 'caseStudies'])->name('case-studies');
Route::get('/case-studies/{slug}', [ContentPageController::class, 'caseStudy'])->name('case-studies.show');
Route::get('/careers/{slug}', [ContentPageController::class, 'career'])->name('careers.show');
Route::get('/portfolio', [ContentPageController::class, 'portfolio'])->name('portfolio.index');
Route::get('/portfolio/{slug}', [ContentPageController::class, 'portfolioItem'])->name('portfolio.show');
Route::get('/useful-links', [UsefulLinksController::class, 'index'])->name('useful-links');
Route::post('/useful-links/submit', [UsefulLinksController::class, 'submit'])->name('useful-links.submit');

// Legal routes
Route::prefix('legal')->name('legal.')->group(function () {
    Route::get('/privacy-policy', [LegalController::class, 'privacy'])->name('privacy');
    Route::get('/terms-of-service', [LegalController::class, 'terms'])->name('terms');
    Route::get('/cookie-policy', [LegalController::class, 'cookies'])->name('cookies');
    Route::get('/accessibility', [LegalController::class, 'accessibility'])->name('accessibility');
    Route::get('/refund-policy', fn () => view('public.legal.refund'))->name('refund');
    Route::get('/service-level-agreement', fn () => view('public.legal.sla'))->name('sla');
});

// Stripe webhook (signature-verified inside controller; must bypass CSRF + session).
Route::post('/stripe/webhook', \App\Http\Controllers\StripeWebhookController::class)->name('stripe.webhook');

// Auth routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:5,1');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/email/verify', fn () => view('auth.verify-email'))->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('portal.dashboard')->with('success', 'Your email address has been verified.');
})->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');
Route::post('/email/verification-notification', function (Request $request) {
    try {
        $request->user()->sendEmailVerificationNotification();
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::warning('Verification resend failed: '.$e->getMessage(), ['user_id' => $request->user()->id]);
        return back()->withErrors(['email' => 'Could not send verification email. Mail server is unreachable. Please try again later.']);
    }
    return back()->with('success', 'A new verification link has been sent.');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:5,1')->name('password.email');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');

// Two-factor authentication (TOTP). Setup is staff-only; the challenge
// applies to any account with MFA enabled. Verification attempts throttled.
Route::middleware('auth')->group(function () {
    Route::get('/mfa/challenge', [MfaController::class, 'challenge'])->name('mfa.challenge');
    Route::post('/mfa/verify', [MfaController::class, 'verify'])->middleware('throttle:10,1')->name('mfa.verify');
    Route::get('/mfa/setup', [MfaController::class, 'setup'])->name('mfa.setup');
    Route::post('/mfa/enable', [MfaController::class, 'enable'])->middleware('throttle:10,1')->name('mfa.enable');
    Route::post('/mfa/disable', [MfaController::class, 'disable'])->middleware('throttle:10,1')->name('mfa.disable');
});

// Customer Portal (Phone verification accessible to authenticated customers)
Route::middleware(['auth', 'customer'])->prefix('portal')->name('portal.')->group(function () {
    // Email verification (OTP) routes
    Route::get('/verify-email', [\App\Http\Controllers\Customer\EmailVerificationController::class, 'show'])->name('verification.email');
    Route::post('/verify-email/send', [\App\Http\Controllers\Customer\EmailVerificationController::class, 'sendOtp'])->middleware('throttle:6,1')->name('verification.email.send');
    Route::post('/verify-email/verify', [\App\Http\Controllers\Customer\EmailVerificationController::class, 'verifyOtp'])->middleware('throttle:10,1')->name('verification.email.verify');

    Route::get('/verify-phone', [\App\Http\Controllers\Customer\PhoneVerificationController::class, 'show'])->name('verification.phone');
    Route::post('/verify-phone/send', [\App\Http\Controllers\Customer\PhoneVerificationController::class, 'sendOtp'])->middleware('throttle:6,1')->name('verification.phone.send');
    Route::post('/verify-phone/verify', [\App\Http\Controllers\Customer\PhoneVerificationController::class, 'verifyOtp'])->middleware('throttle:10,1')->name('verification.phone.verify');
});

// Customer Portal (Verified customer services)
Route::middleware(['auth', 'verified', 'customer', 'mfa'])->prefix('portal')->name('portal.')->group(function () {
    // Notifications
    Route::get('/notifications', function () {
        $user = auth()->user();
        $notifications = \App\Models\Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $user->id)
            ->latest()
            ->paginate(20);
        return view('customer.notifications.index', compact('notifications'));
    })->name('notifications.index');
    Route::get('/notifications/preferences', [\App\Http\Controllers\Customer\NotificationPreferenceController::class, 'index'])->name('notifications.preferences');
    Route::put('/notifications/preferences', [\App\Http\Controllers\Customer\NotificationPreferenceController::class, 'update'])->name('notifications.preferences.update');

    // Profile
    Route::get('/profile', [\App\Http\Controllers\Customer\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Customer\ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/password', [\App\Http\Controllers\Customer\ProfileController::class, 'editPassword'])->name('profile.password');
    Route::put('/profile/password', [\App\Http\Controllers\Customer\ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/', [CustomerDashboard::class, 'index'])->name('dashboard');
    Route::get('/manual', [\App\Http\Controllers\Customer\CustomerManualController::class, 'index'])->name('manual');
    Route::get('/tickets', [CustomerTicket::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [CustomerTicket::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [CustomerTicket::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{id}', [CustomerTicket::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{id}/reply', [CustomerTicket::class, 'reply'])->name('tickets.reply');
    Route::get('/tickets/{ticket}/attachments/{attachment}', [CustomerTicket::class, 'downloadAttachment'])->name('tickets.attachments.download');
    Route::get('/invoices', [CustomerInvoice::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{id}', [CustomerInvoice::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{id}/pdf', [CustomerInvoice::class, 'pdf'])->name('invoices.pdf');
    Route::get('/projects', [CustomerProject::class, 'index'])->name('projects.index');
    Route::get('/projects/{id}', [CustomerProject::class, 'show'])->name('projects.show');
    Route::get('/services', [\App\Http\Controllers\Customer\PortalServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{slug}', [\App\Http\Controllers\Customer\PortalServiceController::class, 'show'])->name('services.show');
    Route::get('/service-request', [ServiceRequestController::class, 'create'])->name('service-request.create');
    Route::post('/service-request', [ServiceRequestController::class, 'store'])->name('service-request.store');
    // Documents
    Route::get('/documents', [\App\Http\Controllers\Customer\DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [\App\Http\Controllers\Customer\DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}/download', [\App\Http\Controllers\Customer\DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [\App\Http\Controllers\Customer\DocumentController::class, 'destroy'])->name('documents.destroy');

    Route::get('/quotations', [\App\Http\Controllers\Customer\QuotationController::class, 'index'])->name('quotations.index');
    Route::get('/quotations/{id}', [\App\Http\Controllers\Customer\QuotationController::class, 'show'])->name('quotations.show');
    Route::get('/quotations/{id}/pdf', [\App\Http\Controllers\Customer\QuotationController::class, 'pdf'])->name('quotations.pdf');
    Route::post('/quotations/{id}/accept', [\App\Http\Controllers\Customer\QuotationController::class, 'accept'])->name('quotations.accept');
    Route::post('/quotations/{id}/reject', [\App\Http\Controllers\Customer\QuotationController::class, 'reject'])->name('quotations.reject');

    // Stripe online payment for own invoices (gracefully disabled when Stripe not configured).
    Route::post('/invoices/{id}/checkout', [\App\Http\Controllers\Customer\StripeCheckoutController::class, 'checkoutInvoice'])->name('invoices.checkout');
    Route::get('/invoices/{id}/checkout/success', [\App\Http\Controllers\Customer\StripeCheckoutController::class, 'success'])->name('invoices.checkout.success');

    // Customer Service Orders & Negotiations
    Route::get('/orders', [\App\Http\Controllers\Customer\CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [\App\Http\Controllers\Customer\CustomerOrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [\App\Http\Controllers\Customer\CustomerOrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{id}', [\App\Http\Controllers\Customer\CustomerOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}/negotiate', [\App\Http\Controllers\Customer\CustomerOrderController::class, 'negotiate'])->name('orders.negotiate');
    Route::post('/orders/{id}/accept-price', [\App\Http\Controllers\Customer\CustomerOrderController::class, 'acceptPrice'])->name('orders.accept-price');
    Route::post('/orders/{id}/pay', [\App\Http\Controllers\Customer\CustomerOrderController::class, 'pay'])->name('orders.pay');
    Route::get('/orders/{orderId}/receipts/{receiptId}', [\App\Http\Controllers\Customer\CustomerOrderController::class, 'showReceipt'])->name('orders.receipts.show');
});

// Admin Panel
Route::middleware(['auth', 'staff', 'mfa'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [AdminDashboard::class, 'index'])->name('dashboard');

    // User Manual
    Route::get('/manual', [ManualController::class, 'index'])->name('manual');

    // Manual Work Orders — listing/creation for staff, financial actions restricted.
    Route::get('/work-orders/search-customers', [\App\Http\Controllers\Admin\WorkOrderController::class, 'searchCustomers'])->name('work-orders.search-customers');
    Route::resource('work-orders', \App\Http\Controllers\Admin\WorkOrderController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/work-orders/{id}/receipts/{receiptId}', [\App\Http\Controllers\Admin\WorkOrderController::class, 'showReceipt'])->name('work-orders.receipts.show');
    // Manager override is a delivery authorization decision, not a money
    // movement: any staff member may reach it, but the service enforces
    // manager-tier roles server-side (admin/support/project/finance managers).
    Route::post('/work-orders/{id}/manager-override', [\App\Http\Controllers\Admin\WorkOrderController::class, 'managerOverride'])->name('work-orders.manager-override');
    Route::middleware('requires.role:isFinanceManager')->group(function () {
        Route::post('/work-orders/{id}/propose-price', [\App\Http\Controllers\Admin\WorkOrderController::class, 'proposePrice'])->name('work-orders.propose-price');
        Route::post('/work-orders/{id}/approve-price', [\App\Http\Controllers\Admin\WorkOrderController::class, 'approvePrice'])->name('work-orders.approve-price');
        Route::post('/work-orders/{id}/record-payment', [\App\Http\Controllers\Admin\WorkOrderController::class, 'recordPayment'])->name('work-orders.record-payment');
        Route::post('/work-orders/{id}/add-expense', [\App\Http\Controllers\Admin\WorkOrderController::class, 'addExpense'])->name('work-orders.add-expense');
        Route::post('/work-orders/{id}/close', [\App\Http\Controllers\Admin\WorkOrderController::class, 'close'])->name('work-orders.close');
    });

    // Users (admin only)
    Route::middleware('requires.role:isAdmin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    });

    // Companies
    Route::resource('companies', CompanyController::class)->except(['show']);

    // Services
    Route::resource('services', AdminService::class);
    Route::get('service-categories', [\App\Http\Controllers\Admin\ServiceCategoryController::class, 'index'])->name('service-categories.index');
    Route::get('service-categories/create', [\App\Http\Controllers\Admin\ServiceCategoryController::class, 'create'])->name('service-categories.create');
    Route::post('service-categories', [\App\Http\Controllers\Admin\ServiceCategoryController::class, 'store'])->name('service-categories.store');
    Route::get('service-categories/{id}/edit', [\App\Http\Controllers\Admin\ServiceCategoryController::class, 'edit'])->name('service-categories.edit');
    Route::put('service-categories/{id}', [\App\Http\Controllers\Admin\ServiceCategoryController::class, 'update'])->name('service-categories.update');
    Route::delete('service-categories/{id}', [\App\Http\Controllers\Admin\ServiceCategoryController::class, 'destroy'])->name('service-categories.destroy');
    Route::get('service-requests', [AdminService::class, 'requests'])->name('service-requests.index');
    Route::get('service-requests-pipeline', [AdminService::class, 'pipeline'])->name('service-requests.pipeline');
    Route::get('service-requests/{id}', [AdminService::class, 'showRequest'])->name('service-requests.show');
    Route::put('service-requests/{id}', [AdminService::class, 'updateRequest'])->name('service-requests.update');
    Route::post('service-requests/{id}/status', [AdminService::class, 'updateRequestStatus'])->name('service-requests.update-status');
    Route::post('service-requests/{id}/graduate', [AdminService::class, 'graduateToQuote'])->name('service-requests.graduate');

    // CRM: leads & sales pipeline (staff-wide read, finance/admin mutate via controller policies).
    Route::get('leads', [\App\Http\Controllers\Admin\LeadController::class, 'index'])->name('leads.index');
    Route::get('leads/create', [\App\Http\Controllers\Admin\LeadController::class, 'create'])->name('leads.create');
    Route::post('leads', [\App\Http\Controllers\Admin\LeadController::class, 'store'])->name('leads.store');
    Route::get('leads/{lead}', [\App\Http\Controllers\Admin\LeadController::class, 'show'])->name('leads.show');
    Route::put('leads/{lead}', [\App\Http\Controllers\Admin\LeadController::class, 'update'])->name('leads.update');
    Route::post('leads/{lead}/activities', [\App\Http\Controllers\Admin\LeadController::class, 'addActivity'])->name('leads.activities.store');

    // Support tickets contain customer data: only the support hierarchy may access them.
    Route::middleware('requires.role:isSupportAgent')->group(function () {
    // Customers create tickets; the staff controller only implements review operations.
    Route::resource('tickets', AdminTicket::class)->only(['index', 'show']);
    Route::post('/tickets/{ticket}/assign', [AdminTicket::class, 'assign'])->name('tickets.assign');
    Route::post('/tickets/{ticket}/reply', [AdminTicket::class, 'reply'])->name('tickets.reply');
    Route::post('/tickets/{ticket}/status', [AdminTicket::class, 'updateStatus'])->name('tickets.status');
    Route::post('/tickets/{ticket}/note', [AdminTicket::class, 'addNote'])->name('tickets.note');
    Route::post('/tickets/{ticket}/tags', [AdminTicket::class, 'updateTags'])->name('tickets.tags');
    Route::post('/tickets/{ticket}/merge', [AdminTicket::class, 'merge'])->name('tickets.merge');
    Route::post('/tickets/{ticket}/reopen', [AdminTicket::class, 'reopen'])->name('tickets.reopen');
    });

    // Project and task operations are restricted to the project-management hierarchy.
    Route::middleware('requires.role:isProjectManager')->group(function () {
    Route::resource('projects', AdminProject::class);
    Route::post('/projects/{project}/status', [AdminProject::class, 'updateStatus'])->name('projects.status');

    // Tasks
    Route::resource('tasks', TaskController::class);
    Route::post('/tasks/{task}/assign', [TaskController::class, 'assign'])->name('tasks.assign');
    Route::post('/tasks/{task}/approve', [TaskController::class, 'approve'])->name('tasks.approve');
    Route::post('/tasks/{task}/reject', [TaskController::class, 'reject'])->name('tasks.reject');
    });

    // Financial records must not be exposed to all staff roles.
    Route::middleware('requires.role:isFinanceManager')->group(function () {
        Route::resource('invoices', AdminInvoice::class)->only(['index', 'create', 'store', 'edit', 'update']);
        Route::get('/invoices/{invoice}', [AdminInvoice::class, 'show'])->name('invoices.show');
        Route::post('/invoices/{invoice}/send', [AdminInvoice::class, 'send'])->name('invoices.send');
        Route::get('/invoices/{invoice}/pdf', [AdminInvoice::class, 'pdf'])->name('invoices.pdf');
        Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store']);
        Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
        Route::resource('expenses', ExpenseController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('/expenses/{expense}/receipt', [ExpenseController::class, 'downloadReceipt'])->name('expenses.receipt');
        Route::post('/expenses/{expense}/approve', [ExpenseController::class, 'approve'])->name('expenses.approve');
        Route::post('/expenses/{expense}/reject', [ExpenseController::class, 'reject'])->name('expenses.reject');
        Route::resource('commissions', CommissionController::class)->only(['index', 'show']);
        Route::post('/commissions/{commission}/approve', [CommissionController::class, 'approve'])->name('commissions.approve');
        Route::post('/commissions/{commission}/reject', [CommissionController::class, 'reject'])->name('commissions.reject');
        Route::post('/commissions/payout', [CommissionController::class, 'payout'])->name('commissions.payout');
        Route::get('commission-rules', [\App\Http\Controllers\Admin\CommissionRuleController::class, 'index'])->name('commission-rules.index');
    });

    // Quotations carry financial totals — restrict to finance managers/admins.
    Route::middleware('requires.role:isFinanceManager')->group(function () {
        Route::resource('quotations', QuotationController::class);
        Route::get('/quotations/{quotation}/pdf', [QuotationController::class, 'pdf'])->name('quotations.pdf');
        Route::post('/quotations/{quotation}/send', [QuotationController::class, 'send'])->name('quotations.send');
        Route::post('/quotations/{quotation}/convert', [QuotationController::class, 'convertToInvoice'])->name('quotations.convert');

        // Proposals, contracts & subscriptions (financial documents).
        Route::get('/proposals', [\App\Http\Controllers\Admin\ProposalController::class, 'index'])->name('proposals.index');
        Route::get('/proposals/create', [\App\Http\Controllers\Admin\ProposalController::class, 'create'])->name('proposals.create');
        Route::post('/proposals', [\App\Http\Controllers\Admin\ProposalController::class, 'store'])->name('proposals.store');
        Route::get('/proposals/{proposal}', [\App\Http\Controllers\Admin\ProposalController::class, 'show'])->name('proposals.show');
        Route::put('/proposals/{proposal}', [\App\Http\Controllers\Admin\ProposalController::class, 'update'])->name('proposals.update');
        Route::post('/proposals/{proposal}/send', [\App\Http\Controllers\Admin\ProposalController::class, 'send'])->name('proposals.send');

        Route::get('/contracts', [\App\Http\Controllers\Admin\ContractController::class, 'index'])->name('contracts.index');
        Route::get('/contracts/create', [\App\Http\Controllers\Admin\ContractController::class, 'create'])->name('contracts.create');
        Route::post('/contracts', [\App\Http\Controllers\Admin\ContractController::class, 'store'])->name('contracts.store');
        Route::get('/contracts/{contract}', [\App\Http\Controllers\Admin\ContractController::class, 'show'])->name('contracts.show');
        Route::put('/contracts/{contract}', [\App\Http\Controllers\Admin\ContractController::class, 'update'])->name('contracts.update');

        Route::get('/subscriptions', [\App\Http\Controllers\Admin\SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('/subscriptions/create', [\App\Http\Controllers\Admin\SubscriptionController::class, 'create'])->name('subscriptions.create');
        Route::post('/subscriptions', [\App\Http\Controllers\Admin\SubscriptionController::class, 'store'])->name('subscriptions.store');
        Route::get('/subscriptions/{subscription}', [\App\Http\Controllers\Admin\SubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::put('/subscriptions/{subscription}', [\App\Http\Controllers\Admin\SubscriptionController::class, 'update'])->name('subscriptions.update');
        Route::post('/subscriptions/{subscription}/bill-now', [\App\Http\Controllers\Admin\SubscriptionController::class, 'billNow'])->name('subscriptions.bill-now');
    });

    // Financials (finance manager + admin only)
    Route::middleware('requires.role:isFinanceManager')->group(function () {
        Route::get('/financials', [FinancialController::class, 'index'])->name('financials.index');
        Route::get('/financials/transactions', [FinancialController::class, 'transactions'])->name('financials.transactions');
        Route::get('/financials/profit-loss', [FinancialController::class, 'profitLoss'])->name('financials.profit-loss');
        Route::get('/financials/export', [FinancialController::class, 'export'])->name('financials.export');
    });

    // Reports (finance + admin only)
    Route::middleware('requires.role:isFinanceManager')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/financial', [ReportController::class, 'financial'])->name('reports.financial');
        Route::get('/reports/tickets', [ReportController::class, 'tickets'])->name('reports.tickets');
        Route::get('/reports/employees', [ReportController::class, 'employees'])->name('reports.employees');
        Route::get('/reports/sla', [ReportController::class, 'sla'])->name('reports.sla');
        Route::get('/reports/profitability', [ReportController::class, 'profitability'])->name('reports.profitability');
        Route::get('/reports/financial/export', [ReportController::class, 'exportFinancial'])->name('reports.financial.export');
        Route::get('/reports/tickets/export', [ReportController::class, 'exportTickets'])->name('reports.tickets.export');
    });

    // Published content and KB administration require administrator approval.
    Route::middleware('requires.role:isAdmin')->group(function () {
    Route::resource('blog', AdminBlog::class)->except(['show']);

    // Knowledge Base
    Route::resource('knowledge-base', AdminKB::class)->except(['show']);

    // Marketing content: case studies, careers, portfolio (ContentController).
    Route::get('/content/case-studies', [ContentController::class, 'caseStudies'])->name('content.case-studies');
    Route::post('/content/case-studies', [ContentController::class, 'storeCaseStudy'])->name('content.case-studies.store');
    Route::put('/content/case-studies/{caseStudy}', [ContentController::class, 'updateCaseStudy'])->name('content.case-studies.update');
    Route::delete('/content/case-studies/{caseStudy}', [ContentController::class, 'destroyCaseStudy'])->name('content.case-studies.destroy');
    Route::get('/content/careers', [ContentController::class, 'careers'])->name('content.careers');
    Route::post('/content/careers', [ContentController::class, 'storeCareer'])->name('content.careers.store');
    Route::put('/content/careers/{career}', [ContentController::class, 'updateCareer'])->name('content.careers.update');
    Route::delete('/content/careers/{career}', [ContentController::class, 'destroyCareer'])->name('content.careers.destroy');
    Route::get('/content/portfolio', [ContentController::class, 'portfolio'])->name('content.portfolio');
    Route::post('/content/portfolio', [ContentController::class, 'storePortfolio'])->name('content.portfolio.store');
    Route::put('/content/portfolio/{portfolio}', [ContentController::class, 'updatePortfolio'])->name('content.portfolio.update');
    Route::delete('/content/portfolio/{portfolio}', [ContentController::class, 'destroyPortfolio'])->name('content.portfolio.destroy');
    });

    // Useful Links (controller has no public show action)
    Route::resource('useful-links', UsefulLinkController::class)->except(['show']);
    Route::post('/useful-links/{usefulLink}/toggle', [UsefulLinkController::class, 'toggle'])->name('useful-links.toggle');

    // Link Submissions
    Route::get('/link-submissions', [LinkSubmissionController::class, 'index'])->name('link-submissions.index');
    Route::get('/link-submissions/{linkSubmission}', [LinkSubmissionController::class, 'show'])->name('link-submissions.show');
    Route::post('/link-submissions/{linkSubmission}/approve', [LinkSubmissionController::class, 'approve'])->name('link-submissions.approve');
    Route::post('/link-submissions/{linkSubmission}/reject', [LinkSubmissionController::class, 'reject'])->name('link-submissions.reject');

    // Settings (admin only)
    Route::middleware('requires.role:isAdmin')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });

    // Audit Logs (admin only)
    Route::middleware('requires.role:isAdmin')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    // Security findings / vulnerability tracker (admin only).
    Route::middleware('requires.role:isAdmin')->group(function () {
        Route::get('/security-dashboard', [\App\Http\Controllers\Admin\SecurityController::class, 'dashboard'])->name('security.dashboard');
        Route::get('/sbom', [\App\Http\Controllers\Admin\SecurityController::class, 'sbom'])->name('sbom');
        Route::get('/security-findings', [\App\Http\Controllers\Admin\SecurityFindingController::class, 'index'])->name('security-findings.index');
        Route::get('/security-findings/create', [\App\Http\Controllers\Admin\SecurityFindingController::class, 'create'])->name('security-findings.create');
        Route::post('/security-findings', [\App\Http\Controllers\Admin\SecurityFindingController::class, 'store'])->name('security-findings.store');
        Route::get('/security-findings/{finding}', [\App\Http\Controllers\Admin\SecurityFindingController::class, 'show'])->name('security-findings.show');
        Route::get('/security-findings/{finding}/edit', [\App\Http\Controllers\Admin\SecurityFindingController::class, 'edit'])->name('security-findings.edit');
        Route::put('/security-findings/{finding}', [\App\Http\Controllers\Admin\SecurityFindingController::class, 'update'])->name('security-findings.update');
        Route::delete('/security-findings/{finding}', [\App\Http\Controllers\Admin\SecurityFindingController::class, 'destroy'])->name('security-findings.destroy');
    });

    // Backup & Disaster Recovery Center (admin only — never customers/employees).
    Route::middleware('requires.role:isAdmin')->prefix('backups')->name('backups.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\BackupController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\BackupController::class, 'store'])->middleware('throttle:3,10')->name('store');
        Route::get('/{id}', [\App\Http\Controllers\Admin\BackupController::class, 'show'])->name('show');
        Route::post('/{id}/verify', [\App\Http\Controllers\Admin\BackupController::class, 'verify'])->middleware('throttle:6,10')->name('verify');
        Route::post('/{id}/restore-test', [\App\Http\Controllers\Admin\BackupController::class, 'restoreTest'])->middleware('throttle:3,10')->name('restore-test');
        Route::post('/{id}/restore', [\App\Http\Controllers\Admin\BackupController::class, 'restore'])->middleware('throttle:3,60')->name('restore');
        Route::get('/{id}/download', [\App\Http\Controllers\Admin\BackupController::class, 'download'])->name('download');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\BackupController::class, 'destroy'])->name('destroy');
        Route::post('/connection-test', [\App\Http\Controllers\Admin\BackupController::class, 'connectionTest'])->name('connection-test');
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Health data includes errors, disk usage and configuration details.
    Route::middleware('requires.role:isAdmin')->group(function () {
    // Centralized Website Overview & System Health
    Route::prefix('health')->name('health.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\HealthController::class, 'index'])->name('index');
        Route::post('/run-check', [\App\Http\Controllers\Admin\HealthController::class, 'runCheck'])->name('run-check');
        Route::get('/pages', [\App\Http\Controllers\Admin\HealthController::class, 'pages'])->name('pages');
        Route::post('/pages/{id}/check', [\App\Http\Controllers\Admin\HealthController::class, 'checkSinglePage'])->name('check-page');
        Route::get('/errors', [\App\Http\Controllers\Admin\HealthController::class, 'errors'])->name('errors');
        Route::post('/errors/{id}/resolve', [\App\Http\Controllers\Admin\HealthController::class, 'resolveError'])->name('resolve-error');
        Route::post('/errors/{id}/note', [\App\Http\Controllers\Admin\HealthController::class, 'addErrorNote'])->name('add-error-note');
        Route::get('/history', [\App\Http\Controllers\Admin\HealthController::class, 'history'])->name('history');
        Route::post('/history/cleanup', [\App\Http\Controllers\Admin\HealthController::class, 'cleanupHistory'])->name('cleanup-history');
    Route::post('/maintenance', [\App\Http\Controllers\Admin\HealthController::class, 'maintenance'])->name('maintenance');
    });
    });
});

// API routes for notifications
Route::middleware('auth:sanctum')->get('/api/notifications/unread', function () {
    $user = auth()->user();
    $notifications = $user->notifications()->unread()->latest()->limit(20)->get();
    return response()->json([
        'notifications' => $notifications,
        'unread_count' => $user->notifications()->unread()->count(),
    ]);
});

// Load-balancer / container health probe — no auth, minimal output, no secrets.
Route::get('/healthz', function () {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        return response()->json(['status' => 'ok', 'db' => 'ok', 'time' => now()->toIso8601String()]);
    } catch (\Throwable $e) {
        return response()->json(['status' => 'degraded', 'db' => 'down'], 503);
    }
})->name('healthz');

// Frontend Error Telemetry Ingestion
Route::post('/api/health/frontend-error', [\App\Http\Controllers\Api\HealthApiController::class, 'logFrontendError'])
    ->middleware('throttle:30,1')
    ->name('api.health.frontend-error');

// AI Chat API Routes (throttled: LLM-backed, abuse/cost sensitive).
Route::middleware('throttle:60,1')->group(function () {
Route::post('/api/ai/conversation', [\App\Http\Controllers\AiChatController::class, 'startConversation'])->name('ai.conversation.start');
Route::post('/api/ai/message', [\App\Http\Controllers\AiChatController::class, 'sendMessage'])->name('ai.message.send');
Route::post('/api/ai/ticket/confirm', [\App\Http\Controllers\AiChatController::class, 'confirmTicket'])->name('ai.ticket.confirm');
Route::post('/api/ai/escalate', [\App\Http\Controllers\AiChatController::class, 'escalate'])->name('ai.escalate');
Route::get('/api/ai/conversation/{id}/messages', [\App\Http\Controllers\AiChatController::class, 'getMessages'])->name('ai.messages');
Route::post('/api/ai/conversation/{id}/close', [\App\Http\Controllers\AiChatController::class, 'close'])->name('ai.conversation.close');
Route::post('/api/ai/rate', [\App\Http\Controllers\AiChatController::class, 'rate'])->name('ai.rate');
Route::get('/api/ai/suggestions', [\App\Http\Controllers\AiChatController::class, 'suggestions'])->name('ai.suggestions');
});

// Admin AI Management
Route::middleware(['auth', 'staff', 'requires.role:isAdmin'])->prefix('admin/ai')->name('admin.ai.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\AiController::class, 'index'])->name('index');
    Route::get('/conversations', [\App\Http\Controllers\Admin\AiController::class, 'conversations'])->name('conversations');
    Route::get('/conversations/{id}', [\App\Http\Controllers\Admin\AiController::class, 'showConversation'])->name('conversation.show');
    Route::get('/knowledge-gaps', [\App\Http\Controllers\Admin\AiController::class, 'knowledgeGaps'])->name('knowledge-gaps');
    Route::post('/knowledge-gaps/{id}/update', [\App\Http\Controllers\Admin\AiController::class, 'updateGap'])->name('gap.update');
    Route::get('/settings', [\App\Http\Controllers\Admin\AiController::class, 'settings'])->name('settings');
    Route::post('/settings', [\App\Http\Controllers\Admin\AiController::class, 'updateSettings'])->name('settings.update');
    Route::get('/usage', [\App\Http\Controllers\Admin\AiController::class, 'usage'])->name('usage');
    Route::get('/questions', [\App\Http\Controllers\Admin\AiController::class, 'questions'])->name('questions');
});
