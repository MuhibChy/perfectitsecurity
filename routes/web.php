<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BlogController as AdminBlog;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\FinancialController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoice;
use App\Http\Controllers\Admin\KnowledgeBaseController as AdminKB;
use App\Http\Controllers\Admin\LinkSubmissionController;
use App\Http\Controllers\Admin\ManualController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProjectController as AdminProject;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ServiceController as AdminService;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\TicketController as AdminTicket;
use App\Http\Controllers\Admin\UsefulLinkController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MfaController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboard;
use App\Http\Controllers\Customer\InvoiceController as CustomerInvoice;
use App\Http\Controllers\Customer\ProjectController as CustomerProject;
use App\Http\Controllers\Customer\ServiceRequestController;
use App\Http\Controllers\Customer\TicketController as CustomerTicket;
use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\ContentPageController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\KnowledgeBaseController;
use App\Http\Controllers\Public\LegalController;
use App\Http\Controllers\Public\PricingController;
use App\Http\Controllers\Public\ServiceController;
use App\Http\Controllers\Public\UsefulLinksController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/sitemap.xml', [\App\Http\Controllers\Public\SitemapController::class, 'index'])->name('sitemap');
Route::get('/lang/{locale}', [\App\Http\Controllers\Public\LanguageController::class, 'switch'])->name('lang.switch');
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->middleware('throttle:10,1')->name('contact.submit');

Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('services.show');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::post('/blog/{slug}/comment', [BlogController::class, 'comment'])->middleware('throttle:10,1')->name('blog.comment');

Route::get('/knowledge-base', [KnowledgeBaseController::class, 'index'])->name('kb.index');
Route::get('/knowledge-base/{slug}', [KnowledgeBaseController::class, 'show'])->name('kb.show');
Route::post('/knowledge-base/{slug}/vote', [KnowledgeBaseController::class, 'vote'])->middleware('throttle:20,1')->name('kb.vote');

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
Route::post('/useful-links/submit', [UsefulLinksController::class, 'submit'])->middleware('throttle:10,1')->name('useful-links.submit');

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
Route::post('/stripe/webhook', \App\Http\Controllers\StripeWebhookController::class)->name('stripe.webhook')->middleware('throttle:60,1');

// Multi-provider webhooks: one endpoint per provider key (signature checks
// inside PaymentWebhookService; must bypass CSRF + session like Stripe).
Route::post('/payments/webhook/{provider}', [\App\Http\Controllers\ProviderWebhookController::class, 'handle'])->name('payments.webhook')->middleware('throttle:60,1');

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
    Route::post('/mfa/recovery-codes', [MfaController::class, 'regenerateCodes'])->middleware('throttle:10,1')->name('mfa.recovery-codes');
});

// Member identity + digital ID + security (all authenticated members;
// controllers enforce ownership, privacy and role authorization).
Route::middleware('auth')->group(function () {
    Route::get('/avatar/{user}', [\App\Http\Controllers\AvatarController::class, 'show'])->middleware('throttle:120,1')->name('avatar.show');
    Route::get('/identity', [\App\Http\Controllers\IdentityController::class, 'index'])->name('identity.index');
    Route::post('/identity', [\App\Http\Controllers\IdentityController::class, 'store'])->middleware('throttle:10,60')->name('identity.store');
    Route::get('/identity/{document}/download', [\App\Http\Controllers\IdentityController::class, 'download'])->name('identity.download');
    Route::get('/id-card', [\App\Http\Controllers\IdCardController::class, 'show'])->name('idcard.show');
    Route::post('/id-card/issue', [\App\Http\Controllers\IdCardController::class, 'issue'])->middleware('throttle:10,60')->name('idcard.issue');
    Route::get('/id-card/pdf', [\App\Http\Controllers\IdCardController::class, 'pdf'])->name('idcard.pdf');
    Route::post('/id-card/{card}/revoke', [\App\Http\Controllers\IdCardController::class, 'revoke'])->name('idcard.revoke');
    Route::get('/security', [\App\Http\Controllers\SecurityDashboardController::class, 'show'])->name('security.dashboard');
    Route::post('/presence', [\App\Http\Controllers\PresenceController::class, 'update'])->middleware('throttle:60,1')->name('presence.update');
    Route::post('/presence/roster', [\App\Http\Controllers\PresenceController::class, 'roster'])->middleware('throttle:60,1')->name('presence.roster');
    // Contractor workspace: read-only own work for freelancer/commission_agent
    // (roles outside the customer/staff boundaries). Ownership enforced per row.
    Route::get('/workspace', [\App\Http\Controllers\WorkspaceController::class, 'index'])->name('workspace.index');
    Route::get('/workspace/tasks/{task}', [\App\Http\Controllers\WorkspaceController::class, 'task'])->name('workspace.tasks.show');
    Route::get('/workspace/commissions', [\App\Http\Controllers\WorkspaceController::class, 'commissions'])->name('workspace.commissions');
    Route::get('/workspace/commissions/report', [\App\Http\Controllers\WorkspaceController::class, 'commissionsReport'])->middleware('throttle:6,1')->name('workspace.commissions.report');
    Route::get('/workspace/work/report', [\App\Http\Controllers\WorkspaceController::class, 'workReport'])->middleware('throttle:6,1')->name('workspace.work.report');
});

// Foreground glove preference (authenticated user only; ownership always
// resolved from the session — no user_id accepted, ever).
Route::middleware('auth')->prefix('glove-preference')->name('glove-preference.')->group(function () {
    Route::get('/', [\App\Http\Controllers\GlovePreferenceController::class, 'show'])->name('show');
    Route::patch('/', [\App\Http\Controllers\GlovePreferenceController::class, 'update'])->name('update');
    Route::post('/reset', [\App\Http\Controllers\GlovePreferenceController::class, 'reset'])->name('reset');
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
    Route::post('/notifications/read-all', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Profile
    Route::get('/profile', [\App\Http\Controllers\Customer\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Customer\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/settings', [\App\Http\Controllers\Customer\ProfileController::class, 'updateSettings'])->name('profile.settings');
    Route::get('/profile/password', [\App\Http\Controllers\Customer\ProfileController::class, 'editPassword'])->name('profile.password');
    Route::put('/profile/password', [\App\Http\Controllers\Customer\ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Individual messaging (permission-scoped, server-enforced).
    Route::get('/messages', [\App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/create', [\App\Http\Controllers\MessageController::class, 'create'])->name('messages.create');
    Route::post('/messages', [\App\Http\Controllers\MessageController::class, 'store'])->middleware('throttle:10,1')->name('messages.store');
    Route::get('/messages/{id}', [\App\Http\Controllers\MessageController::class, 'show'])->name('messages.show');

    // Account ecosystem: lifelong financial account, comms timeline,
    // support-team directory, permission-aware search (own records only).
    Route::get('/account', [\App\Http\Controllers\Customer\AccountController::class, 'summary'])->name('account.summary');
    Route::get('/account/comms', [\App\Http\Controllers\Customer\AccountController::class, 'comms'])->name('account.comms');
    Route::get('/directory', [\App\Http\Controllers\Customer\DirectoryController::class, 'index'])->name('directory.index');
    Route::get('/directory/{user}', [\App\Http\Controllers\Customer\DirectoryController::class, 'show'])->name('directory.show');
    Route::get('/search', [\App\Http\Controllers\Customer\SearchController::class, 'index'])->name('search.index');

    // Emergency lane (creation throttled: abuse/cost sensitive).
    Route::get('/emergency', [\App\Http\Controllers\EmergencyController::class, 'index'])->name('emergency.index');
    Route::get('/emergency/create', [\App\Http\Controllers\EmergencyController::class, 'create'])->name('emergency.create');
    Route::post('/emergency', [\App\Http\Controllers\EmergencyController::class, 'store'])->middleware('throttle:5,60')->name('emergency.store');
    Route::get('/emergency/{id}', [\App\Http\Controllers\EmergencyController::class, 'show'])->name('emergency.show');

    Route::get('/', [CustomerDashboard::class, 'index'])->name('dashboard');
    Route::get('/manual', [\App\Http\Controllers\Customer\CustomerManualController::class, 'index'])->name('manual');
    Route::get('/tickets', [CustomerTicket::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [CustomerTicket::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [CustomerTicket::class, 'store'])->middleware('throttle:10,1')->name('tickets.store');
    Route::get('/tickets/{id}', [CustomerTicket::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{id}/reply', [CustomerTicket::class, 'reply'])->middleware('throttle:10,1')->name('tickets.reply');
    Route::get('/tickets/{ticket}/attachments/{attachment}', [CustomerTicket::class, 'downloadAttachment'])->name('tickets.attachments.download');
    Route::get('/invoices', [CustomerInvoice::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{id}', [CustomerInvoice::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{id}/pdf', [CustomerInvoice::class, 'pdf'])->name('invoices.pdf');
    Route::get('/projects', [CustomerProject::class, 'index'])->name('projects.index');
    Route::get('/projects/{id}', [CustomerProject::class, 'show'])->name('projects.show');
    Route::get('/services', [\App\Http\Controllers\Customer\PortalServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{slug}', [\App\Http\Controllers\Customer\PortalServiceController::class, 'show'])->name('services.show');
    Route::get('/service-request', [ServiceRequestController::class, 'create'])->name('service-request.create');
    Route::post('/service-request', [ServiceRequestController::class, 'store'])->middleware('throttle:10,1')->name('service-request.store');
    // Documents
    Route::get('/documents', [\App\Http\Controllers\Customer\DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [\App\Http\Controllers\Customer\DocumentController::class, 'store'])->middleware('throttle:10,1')->name('documents.store');
    Route::get('/documents/{document}/download', [\App\Http\Controllers\Customer\DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [\App\Http\Controllers\Customer\DocumentController::class, 'destroy'])->name('documents.destroy');

    Route::get('/quotations', [\App\Http\Controllers\Customer\QuotationController::class, 'index'])->name('quotations.index');
    Route::get('/quotations/{id}', [\App\Http\Controllers\Customer\QuotationController::class, 'show'])->name('quotations.show');
    Route::get('/quotations/{id}/pdf', [\App\Http\Controllers\Customer\QuotationController::class, 'pdf'])->name('quotations.pdf');
    Route::post('/quotations/{id}/accept', [\App\Http\Controllers\Customer\QuotationController::class, 'accept'])->middleware('throttle:10,1')->name('quotations.accept');
    Route::post('/quotations/{id}/reject', [\App\Http\Controllers\Customer\QuotationController::class, 'reject'])->middleware('throttle:10,1')->name('quotations.reject');

    // Stripe online payment for own invoices (gracefully disabled when Stripe not configured).
    Route::post('/invoices/{id}/checkout', [\App\Http\Controllers\Customer\StripeCheckoutController::class, 'checkoutInvoice'])->middleware('throttle:5,1')->name('invoices.checkout');
    Route::get('/invoices/{id}/checkout/success', [\App\Http\Controllers\Customer\StripeCheckoutController::class, 'success'])->name('invoices.checkout.success');

    // Multi-provider checkout (summary → initiate → provider → verified callback).
    Route::get('/checkout/{invoice}', [\App\Http\Controllers\Customer\PaymentCheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/{invoice}/initiate', [\App\Http\Controllers\Customer\PaymentCheckoutController::class, 'initiate'])->middleware('throttle:5,1')->name('checkout.initiate');
    Route::get('/payments/callback/{provider}', [\App\Http\Controllers\Customer\PaymentCheckoutController::class, 'callback'])->name('checkout.callback');
    Route::get('/bank-transfer/{transaction}', [\App\Http\Controllers\Customer\PaymentCheckoutController::class, 'bankTransferShow'])->name('bank-transfer.show');
    Route::post('/bank-transfer/{transaction}', [\App\Http\Controllers\Customer\PaymentCheckoutController::class, 'bankTransferSubmit'])->middleware('throttle:5,1')->name('bank-transfer.submit');

    // Customer payment history + refund requests (own records only).
    Route::get('/payments', [\App\Http\Controllers\Customer\PaymentCheckoutController::class, 'history'])->name('payments.index');
    Route::get('/payments/{reference}', [\App\Http\Controllers\Customer\PaymentCheckoutController::class, 'historyShow'])->name('payments.show');
    Route::post('/payments/{reference}/refund', [\App\Http\Controllers\Customer\PaymentCheckoutController::class, 'requestRefund'])->middleware('throttle:5,1')->name('payments.refund-request');

    // Customer self-service history (own records only).
    Route::get('/history', [\App\Http\Controllers\Customer\HistoryController::class, 'index'])->name('history.index');
    Route::get('/reports/mine', [\App\Http\Controllers\Customer\ReportController::class, 'mine'])->middleware('throttle:6,1')->name('reports.mine');
    Route::get('/reports/service/{id}', [\App\Http\Controllers\Customer\ReportController::class, 'service'])->middleware('throttle:6,1')->name('reports.service');

    // FWallet: own wallets only (controller scopes every lookup to auth user).
    Route::get('/wallet', [\App\Http\Controllers\Customer\WalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/{wallet}', [\App\Http\Controllers\Customer\WalletController::class, 'show'])->name('wallet.show');
    Route::post('/wallet/{wallet}/top-up', [\App\Http\Controllers\Customer\WalletController::class, 'topUp'])->middleware('throttle:5,1')->name('wallet.topup');
    Route::get('/wallet-topup-return/{provider}', [\App\Http\Controllers\Customer\WalletController::class, 'topUpReturn'])->name('wallet.topup.return');
    Route::post('/wallet/{wallet}/pay-invoice', [\App\Http\Controllers\Customer\WalletController::class, 'payInvoice'])->middleware('throttle:5,1')->name('wallet.pay-invoice');
    Route::get('/wallet/{wallet}/statement', [\App\Http\Controllers\Customer\WalletController::class, 'statement'])->name('wallet.statement');

    // Customer live service tracking (own services only).
    Route::get('/tracking', [\App\Http\Controllers\Customer\TrackingController::class, 'index'])->name('tracking.index');
    Route::get('/tracking/{project}', [\App\Http\Controllers\Customer\TrackingController::class, 'show'])->name('tracking.show');
    Route::post('/tracking/{project}/request-update', [\App\Http\Controllers\Customer\TrackingController::class, 'requestUpdate'])->middleware('throttle:10,1')->name('tracking.request-update');
    Route::post('/tracking/{project}/ask', [\App\Http\Controllers\Customer\TrackingController::class, 'askQuery'])->middleware('throttle:10,1')->name('tracking.ask');
    Route::post('/orders/{order}/change-request', [\App\Http\Controllers\Customer\TrackingController::class, 'storeChange'])->middleware('throttle:10,1')->name('orders.change-request');

    // Customer documents (owner-scoped downloads).
    Route::get('/orders/{id}/pdf', [\App\Http\Controllers\Customer\DocumentCenterController::class, 'orderPdf'])->name('orders.pdf');
    Route::get('/receipts/{receipt}/pdf', [\App\Http\Controllers\Customer\DocumentCenterController::class, 'receiptPdf'])->name('receipts.pdf');
    Route::get('/cash-memos/{memo}/pdf', [\App\Http\Controllers\Customer\DocumentCenterController::class, 'cashMemoPdf'])->name('cash-memos.pdf');
    Route::get('/tracking/{project}/report-pdf', [\App\Http\Controllers\Customer\DocumentCenterController::class, 'serviceReport'])->name('tracking.report-pdf');
    Route::post('/orders/{id}/confirm-completion', [\App\Http\Controllers\Customer\CustomerOrderController::class, 'confirmCompletion'])->name('orders.confirm-completion');

    // Customer Service Orders & Negotiations
    Route::get('/orders', [\App\Http\Controllers\Customer\CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [\App\Http\Controllers\Customer\CustomerOrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [\App\Http\Controllers\Customer\CustomerOrderController::class, 'store'])->middleware('throttle:10,1')->name('orders.store');
    Route::get('/orders/{id}', [\App\Http\Controllers\Customer\CustomerOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}/negotiate', [\App\Http\Controllers\Customer\CustomerOrderController::class, 'negotiate'])->middleware('throttle:10,1')->name('orders.negotiate');
    Route::post('/orders/{id}/accept-price', [\App\Http\Controllers\Customer\CustomerOrderController::class, 'acceptPrice'])->middleware('throttle:10,1')->name('orders.accept-price');
    Route::post('/orders/{id}/reject-price', [\App\Http\Controllers\Customer\CustomerOrderController::class, 'rejectPrice'])->middleware('throttle:10,1')->name('orders.reject-price');
    Route::post('/orders/{id}/cancel', [\App\Http\Controllers\Customer\CustomerOrderController::class, 'cancel'])->middleware('throttle:10,1')->name('orders.cancel');
    Route::post('/orders/{id}/pay', [\App\Http\Controllers\Customer\CustomerOrderController::class, 'pay'])->middleware('throttle:5,1')->name('orders.pay');
    Route::get('/orders/{orderId}/receipts/{receiptId}', [\App\Http\Controllers\Customer\CustomerOrderController::class, 'showReceipt'])->name('orders.receipts.show');

    // Customer ITSM self-service (all owner-scoped in controller).
    Route::get('/my-assets', [\App\Http\Controllers\Customer\ItsmPortalController::class, 'assets'])->name('itsm.assets');
    Route::get('/my-systems', [\App\Http\Controllers\Customer\ItsmPortalController::class, 'cis'])->name('itsm.cis');
    Route::get('/my-agreements', [\App\Http\Controllers\Customer\ItsmPortalController::class, 'agreements'])->name('itsm.agreements');
    Route::get('/remote-support', [\App\Http\Controllers\Customer\ItsmPortalController::class, 'remoteIndex'])->name('itsm.remote');
    Route::post('/remote-support', [\App\Http\Controllers\Customer\ItsmPortalController::class, 'remoteStore'])->middleware('throttle:10,1')->name('itsm.remote.store');
    Route::post('/remote-support/{session}/consent', [\App\Http\Controllers\Customer\ItsmPortalController::class, 'remoteConsent'])->middleware('throttle:10,1')->name('itsm.remote.consent');
    Route::get('/site-visits', [\App\Http\Controllers\Customer\ItsmPortalController::class, 'visits'])->name('itsm.visits');
    Route::post('/site-visits/{visit}/confirm', [\App\Http\Controllers\Customer\ItsmPortalController::class, 'confirmVisit'])->middleware('throttle:10,1')->name('itsm.visits.confirm');
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
    Route::get('/work-orders/{id}/pdf', [\App\Http\Controllers\Admin\DocumentController::class, 'orderPdf'])->name('work-orders.pdf');
    Route::get('/service/{project}/report-pdf', [\App\Http\Controllers\Admin\DocumentController::class, 'serviceReport'])->name('service.report-pdf');
    // Manager override is a delivery authorization decision, not a money
    // movement: any staff member may reach it, but the service enforces
    // manager-tier roles server-side (admin/support/project/finance managers).
    Route::post('/work-orders/{id}/manager-override', [\App\Http\Controllers\Admin\WorkOrderController::class, 'managerOverride'])->name('work-orders.manager-override');
    Route::middleware('requires.role:isFinanceManager')->group(function () {
        Route::post('/work-orders/{id}/propose-price', [\App\Http\Controllers\Admin\WorkOrderController::class, 'proposePrice'])->name('work-orders.propose-price');
        Route::post('/work-orders/{id}/approve-price', [\App\Http\Controllers\Admin\WorkOrderController::class, 'approvePrice'])->name('work-orders.approve-price');
        Route::post('/work-orders/{id}/reject-price', [\App\Http\Controllers\Admin\WorkOrderController::class, 'rejectPrice'])->name('work-orders.reject-price');
        Route::post('/work-orders/{id}/cancel', [\App\Http\Controllers\Admin\WorkOrderController::class, 'cancel'])->name('work-orders.cancel');
        Route::post('/work-orders/{id}/record-payment', [\App\Http\Controllers\Admin\WorkOrderController::class, 'recordPayment'])->name('work-orders.record-payment');
        Route::post('/work-orders/{id}/schedule', [\App\Http\Controllers\Admin\WorkOrderController::class, 'storeSchedule'])->name('work-orders.schedule.store');
        Route::post('/work-orders/{id}/add-expense', [\App\Http\Controllers\Admin\WorkOrderController::class, 'addExpense'])->name('work-orders.add-expense');
        Route::post('/work-orders/{id}/close', [\App\Http\Controllers\Admin\WorkOrderController::class, 'close'])->name('work-orders.close');
    });

    // Users (admin only)
    Route::middleware('requires.role:isAdmin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
        Route::post('/users/{user}/reject', [UserController::class, 'reject'])->name('users.reject');
        Route::post('/users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');

        // Test users & role testing (admin only; no passwords ever shown).
        Route::get('/role-testing', [\App\Http\Controllers\Admin\RoleTestingController::class, 'index'])->name('role-testing.index');
        Route::post('/role-testing/requests/{user}/approve', [\App\Http\Controllers\Admin\RoleTestingController::class, 'approve'])->name('role-testing.approve');
        Route::post('/role-testing/requests/{user}/reject', [\App\Http\Controllers\Admin\RoleTestingController::class, 'reject'])->name('role-testing.reject');
        Route::post('/role-testing/users/{user}/toggle', [\App\Http\Controllers\Admin\RoleTestingController::class, 'toggleActive'])->name('role-testing.toggle');
        Route::post('/role-testing/roles/{role}/retire', [\App\Http\Controllers\Admin\RoleTestingController::class, 'retire'])->name('role-testing.retire');
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
    Route::post('leads/{lead}/convert', [\App\Http\Controllers\Admin\LeadController::class, 'convert'])->name('leads.convert');

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

        // ITSM extension: Problem Management (staff support hierarchy only).
        Route::get('/problems', [\App\Http\Controllers\Admin\ProblemController::class, 'index'])->name('problems.index');
        Route::get('/problems/create', [\App\Http\Controllers\Admin\ProblemController::class, 'create'])->name('problems.create');
        Route::post('/problems', [\App\Http\Controllers\Admin\ProblemController::class, 'store'])->name('problems.store');
        Route::get('/problems/{problem}', [\App\Http\Controllers\Admin\ProblemController::class, 'show'])->name('problems.show');
        Route::patch('/problems/{problem}', [\App\Http\Controllers\Admin\ProblemController::class, 'update'])->name('problems.update');
        Route::post('/problems/{problem}/transition', [\App\Http\Controllers\Admin\ProblemController::class, 'transition'])->name('problems.transition');
        Route::post('/problems/{problem}/link-ticket', [\App\Http\Controllers\Admin\ProblemController::class, 'linkTicket'])->name('problems.link-ticket');
        Route::delete('/problems/{problem}/tickets/{ticket}', [\App\Http\Controllers\Admin\ProblemController::class, 'unlinkTicket'])->name('problems.unlink-ticket');

        // ITSM extension: Change Management (lite, proportionate).
        Route::get('/changes', [\App\Http\Controllers\Admin\ItsmChangeController::class, 'index'])->name('changes.index');
        Route::get('/changes/create', [\App\Http\Controllers\Admin\ItsmChangeController::class, 'create'])->name('changes.create');
        Route::post('/changes', [\App\Http\Controllers\Admin\ItsmChangeController::class, 'store'])->name('changes.store');
        Route::get('/changes/{change}', [\App\Http\Controllers\Admin\ItsmChangeController::class, 'show'])->name('changes.show');
        Route::patch('/changes/{change}', [\App\Http\Controllers\Admin\ItsmChangeController::class, 'update'])->name('changes.update');
        Route::post('/changes/{change}/transition', [\App\Http\Controllers\Admin\ItsmChangeController::class, 'transition'])->name('changes.transition');
        Route::post('/changes/{change}/approve', [\App\Http\Controllers\Admin\ItsmChangeController::class, 'approve'])->name('changes.approve');

        // ITSM extension: Assets + lightweight CMDB.
        Route::get('/assets', [\App\Http\Controllers\Admin\AssetController::class, 'index'])->name('assets.index');
        Route::get('/assets/create', [\App\Http\Controllers\Admin\AssetController::class, 'create'])->name('assets.create');
        Route::post('/assets', [\App\Http\Controllers\Admin\AssetController::class, 'store'])->name('assets.store');
        Route::get('/assets/{asset}', [\App\Http\Controllers\Admin\AssetController::class, 'show'])->name('assets.show');
        Route::patch('/assets/{asset}', [\App\Http\Controllers\Admin\AssetController::class, 'update'])->name('assets.update');
        Route::get('/configuration-items', [\App\Http\Controllers\Admin\AssetController::class, 'ciIndex'])->name('ci.index');
        Route::post('/configuration-items', [\App\Http\Controllers\Admin\AssetController::class, 'ciStore'])->name('ci.store');
        Route::get('/configuration-items/{ci}', [\App\Http\Controllers\Admin\AssetController::class, 'ciShow'])->name('ci.show');
        Route::post('/configuration-items/{ci}/relate', [\App\Http\Controllers\Admin\AssetController::class, 'ciRelate'])->name('ci.relate');
        Route::delete('/configuration-items/{ci}/relationships/{relationship}', [\App\Http\Controllers\Admin\AssetController::class, 'ciUnrelate'])->name('ci.unrelate');

        // ITSM extension: Remote support + onsite visits (field service).
        Route::get('/remote-sessions', [\App\Http\Controllers\Admin\FieldServiceController::class, 'remoteIndex'])->name('remote.index');
        Route::post('/remote-sessions', [\App\Http\Controllers\Admin\FieldServiceController::class, 'remoteStore'])->name('remote.store');
        Route::get('/remote-sessions/{session}', [\App\Http\Controllers\Admin\FieldServiceController::class, 'remoteShow'])->name('remote.show');
        Route::patch('/remote-sessions/{session}', [\App\Http\Controllers\Admin\FieldServiceController::class, 'remoteUpdate'])->name('remote.update');
        Route::get('/site-visits', [\App\Http\Controllers\Admin\FieldServiceController::class, 'visitIndex'])->name('visits.index');
        Route::post('/site-visits', [\App\Http\Controllers\Admin\FieldServiceController::class, 'visitStore'])->name('visits.store');
        Route::get('/site-visits/{visit}', [\App\Http\Controllers\Admin\FieldServiceController::class, 'visitShow'])->name('visits.show');
        Route::patch('/site-visits/{visit}', [\App\Http\Controllers\Admin\FieldServiceController::class, 'visitUpdate'])->name('visits.update');

        // ITSM extension: Service agreements + SLA breach ledger + approvals.
        Route::get('/service-agreements', [\App\Http\Controllers\Admin\ServiceAgreementController::class, 'index'])->name('agreements.index');
        Route::post('/service-agreements', [\App\Http\Controllers\Admin\ServiceAgreementController::class, 'store'])->name('agreements.store');
        Route::get('/service-agreements/{agreement}', [\App\Http\Controllers\Admin\ServiceAgreementController::class, 'show'])->name('agreements.show');
        Route::patch('/service-agreements/{agreement}', [\App\Http\Controllers\Admin\ServiceAgreementController::class, 'update'])->name('agreements.update');
        Route::get('/sla-breaches', [\App\Http\Controllers\Admin\ServiceAgreementController::class, 'breaches'])->name('sla-breaches.index');
        Route::post('/sla-breaches/{breach}/acknowledge', [\App\Http\Controllers\Admin\ServiceAgreementController::class, 'acknowledgeBreach'])->name('sla-breaches.acknowledge');
        Route::get('/approvals', [\App\Http\Controllers\Admin\ServiceAgreementController::class, 'approvals'])->name('approvals.index');
        Route::post('/approvals/{approval}/decide', [\App\Http\Controllers\Admin\ServiceAgreementController::class, 'decideApproval'])->name('approvals.decide');
    });

    // Project and task operations are restricted to the project-management hierarchy.
    Route::middleware('requires.role:isProjectManager')->group(function () {
        Route::resource('projects', AdminProject::class);
        Route::post('/projects/{project}/status', [AdminProject::class, 'updateStatus'])->name('projects.status');

        // Tasks
        Route::resource('tasks', TaskController::class);
        Route::post('/tasks/{task}/assign', [TaskController::class, 'assign'])->name('tasks.assign');
        Route::post('/tasks/{task}/pause', [TaskController::class, 'pause'])->name('tasks.pause');
        Route::post('/tasks/{task}/resume', [TaskController::class, 'resume'])->name('tasks.resume');
        Route::post('/tasks/{task}/approve', [TaskController::class, 'approve'])->name('tasks.approve');
        Route::post('/tasks/{task}/reject', [TaskController::class, 'reject'])->name('tasks.reject');
    });

    // Financial records must not be exposed to all staff roles.
    Route::middleware('requires.role:isFinanceManager')->group(function () {
        Route::get('/receipts/{receipt}/pdf', [\App\Http\Controllers\Admin\DocumentController::class, 'receiptPdf'])->name('receipts.pdf');
        Route::get('/cash-memos/{memo}/pdf', [\App\Http\Controllers\Admin\DocumentController::class, 'cashMemoPdf'])->name('cash-memos.pdf');
        // FWallet oversight (no arbitrary balance editing; adjustments audited).
        Route::get('/wallets', [\App\Http\Controllers\Admin\WalletController::class, 'index'])->name('wallets.index');
        Route::get('/wallets/{wallet}', [\App\Http\Controllers\Admin\WalletController::class, 'show'])->name('wallets.show');
        Route::post('/wallets/{wallet}/adjust', [\App\Http\Controllers\Admin\WalletController::class, 'adjust'])->name('wallets.adjust');
        Route::post('/wallets/{wallet}/freeze', [\App\Http\Controllers\Admin\WalletController::class, 'freeze'])->name('wallets.freeze');
        Route::post('/wallets/{wallet}/unfreeze', [\App\Http\Controllers\Admin\WalletController::class, 'unfreeze'])->name('wallets.unfreeze');
        Route::post('/wallet-transactions/{transaction}/refund', [\App\Http\Controllers\Admin\WalletController::class, 'refund'])->name('wallets.refund');
        Route::post('/wallets/{wallet}/correct-owner', [\App\Http\Controllers\Admin\WalletController::class, 'correctOwner'])->name('wallets.correct-owner');
        Route::get('/users/{user}/wallets', [\App\Http\Controllers\Admin\WalletController::class, 'userWallets'])->name('users.wallets');
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
        Route::post('/expenses/{expense}/mark-paid', [ExpenseController::class, 'markPaid'])->name('expenses.mark-paid');
        Route::resource('commissions', CommissionController::class)->only(['index', 'show']);
        Route::post('/commissions/{commission}/transition', [CommissionController::class, 'transition'])->name('commissions.transition');
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
        Route::post('/quotations/{quotation}/apply-promo', [QuotationController::class, 'applyPromo'])->name('quotations.apply-promo');

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
        Route::post('/contracts/{contract}/renew', [\App\Http\Controllers\Admin\ContractController::class, 'renew'])->name('contracts.renew');

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
        Route::get('/financials/reconciliation', [FinancialController::class, 'reconciliation'])->name('financials.reconciliation');
        Route::get('/financials/export', [FinancialController::class, 'export'])->middleware('throttle:6,1')->name('financials.export');

        // Multi-provider payment system (additive; existing rails untouched).
        Route::get('/payments/overview', [\App\Http\Controllers\Admin\PaymentDashboardController::class, 'index'])->name('payments.overview');
        Route::get('/payments/reconciliation', [\App\Http\Controllers\Admin\PaymentDashboardController::class, 'reconciliation'])->name('payments.reconciliation');
        // Go-live readiness hardening (additive; no ledger/settlement changes).
        Route::get('/payments/readiness', [\App\Http\Controllers\Admin\PaymentGoLiveController::class, 'readiness'])->name('payments.readiness');
        Route::get('/payments/readiness/{provider}', [\App\Http\Controllers\Admin\PaymentGoLiveController::class, 'show'])->name('payments.readiness.show');
        Route::post('/payments/readiness/{provider}/activate-live', [\App\Http\Controllers\Admin\PaymentGoLiveController::class, 'activateLive'])->middleware('throttle:6,1')->name('payments.readiness.activate');
        Route::get('/payments/health', [\App\Http\Controllers\Admin\PaymentGoLiveController::class, 'health'])->name('payments.health');
        Route::get('/payments/config-check', [\App\Http\Controllers\Admin\PaymentGoLiveController::class, 'configCheck'])->name('payments.config-check');
        Route::get('/payments/go-live-checklist', [\App\Http\Controllers\Admin\PaymentGoLiveController::class, 'checklist'])->name('payments.checklist');
        Route::resource('payment-providers', \App\Http\Controllers\Admin\PaymentProviderController::class)->only(['index', 'create', 'store', 'edit', 'update'])->names('payment-providers')->parameters(['payment-providers' => 'provider']);
        Route::post('/payment-providers/{provider}/toggle', [\App\Http\Controllers\Admin\PaymentProviderController::class, 'toggle'])->name('payment-providers.toggle');
        Route::resource('bank-accounts', \App\Http\Controllers\Admin\BankAccountController::class)->only(['index', 'create', 'store', 'edit', 'update'])->names('bank-accounts')->parameters(['bank-accounts' => 'account']);
        Route::post('/bank-accounts/{account}/toggle', [\App\Http\Controllers\Admin\BankAccountController::class, 'toggle'])->name('bank-accounts.toggle');
        Route::get('/bank-transfers', [\App\Http\Controllers\Admin\ManualBankPaymentController::class, 'index'])->name('bank-transfers.index');
        Route::get('/bank-transfers/{bankTransfer}', [\App\Http\Controllers\Admin\ManualBankPaymentController::class, 'show'])->name('bank-transfers.show');
        Route::get('/bank-transfers/{bankTransfer}/receipt', [\App\Http\Controllers\Admin\ManualBankPaymentController::class, 'receipt'])->name('bank-transfers.receipt');
        Route::post('/bank-transfers/{bankTransfer}/verify', [\App\Http\Controllers\Admin\ManualBankPaymentController::class, 'verify'])->name('bank-transfers.verify');
        Route::post('/bank-transfers/{bankTransfer}/reject', [\App\Http\Controllers\Admin\ManualBankPaymentController::class, 'reject'])->name('bank-transfers.reject');
        Route::get('/refunds', [\App\Http\Controllers\Admin\PaymentRefundController::class, 'index'])->name('refunds.index');
        Route::post('/refunds', [\App\Http\Controllers\Admin\PaymentRefundController::class, 'store'])->name('refunds.store');
        Route::get('/refunds/{refund}', [\App\Http\Controllers\Admin\PaymentRefundController::class, 'show'])->name('refunds.show');
        Route::post('/refunds/{refund}/approve', [\App\Http\Controllers\Admin\PaymentRefundController::class, 'approve'])->name('refunds.approve');
        Route::post('/refunds/{refund}/reject', [\App\Http\Controllers\Admin\PaymentRefundController::class, 'reject'])->name('refunds.reject');
        Route::post('/refunds/{refund}/execute', [\App\Http\Controllers\Admin\PaymentRefundController::class, 'execute'])->name('refunds.execute');
    });

    // Staff self-service work report (own authorized work only; scope forced
    // server-side — never peers, never finance). Available to all staff.
    Route::get('/reports/my-work', [\App\Http\Controllers\Admin\StaffReportController::class, 'myWork'])->middleware('throttle:6,1')->name('reports.my-work');

    // Reports (finance + admin only)
    Route::middleware('requires.role:isFinanceManager')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/financial', [ReportController::class, 'financial'])->name('reports.financial');
        Route::get('/reports/tickets', [ReportController::class, 'tickets'])->name('reports.tickets');
        Route::get('/reports/employees', [ReportController::class, 'employees'])->name('reports.employees');
        Route::get('/reports/sla', [ReportController::class, 'sla'])->name('reports.sla');
        Route::get('/reports/profitability', [ReportController::class, 'profitability'])->name('reports.profitability');
        Route::get('/reports/financial/export', [ReportController::class, 'exportFinancial'])->middleware('throttle:6,1')->name('reports.financial.export');
        Route::get('/reports/tickets/export', [ReportController::class, 'exportTickets'])->middleware('throttle:6,1')->name('reports.tickets.export');
        Route::get('/reports/service', [ReportController::class, 'serviceOrder'])->name('reports.service.form');
        Route::get('/reports/service/{id}', [ReportController::class, 'serviceOrder'])->name('reports.service');
        Route::get('/reports/export/{type}', [\App\Http\Controllers\Admin\ReportExportController::class, 'export'])->middleware('throttle:6,1')->name('reports.export');
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
        Route::get('/settings/payments', [SettingController::class, 'payments'])->name('settings.payments');
        Route::post('/settings/payments', [SettingController::class, 'updatePayments'])->name('settings.payments.update');
        Route::get('/settings/promotion', [SettingController::class, 'promotion'])->name('settings.promotion');
        Route::post('/settings/promotion', [SettingController::class, 'updatePromotion'])->name('settings.promotion.update');
    });

    // Audit Logs (admin only)
    Route::middleware('requires.role:isAdmin')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        // Identity verification queue (admin-only reviewers; every action audited).
        Route::get('/identity', [\App\Http\Controllers\Admin\IdentityVerificationController::class, 'index'])->name('identity.index');
        Route::get('/identity/{document}', [\App\Http\Controllers\Admin\IdentityVerificationController::class, 'show'])->name('identity.show');
        Route::get('/identity/{document}/download', [\App\Http\Controllers\Admin\IdentityVerificationController::class, 'download'])->name('identity.download');
        Route::post('/identity/{document}/review', [\App\Http\Controllers\Admin\IdentityVerificationController::class, 'review'])->name('identity.review');
        Route::post('/identity/{document}/approve', [\App\Http\Controllers\Admin\IdentityVerificationController::class, 'approve'])->name('identity.approve');
        Route::post('/identity/{document}/reject', [\App\Http\Controllers\Admin\IdentityVerificationController::class, 'reject'])->name('identity.reject');
    });

    // Countries & currencies (admin only)
    Route::middleware('requires.role:isAdmin')->group(function () {
        Route::get('/countries', [\App\Http\Controllers\Admin\CountryController::class, 'index'])->name('countries.index');
        Route::get('/countries/{country}/edit', [\App\Http\Controllers\Admin\CountryController::class, 'edit'])->name('countries.edit');
        Route::put('/countries/{country}', [\App\Http\Controllers\Admin\CountryController::class, 'update'])->name('countries.update');
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

    // ── Staff self-profile (whitelisted personal fields; audited) ──
    Route::get('/my-profile', [\App\Http\Controllers\Admin\StaffProfileController::class, 'edit'])->name('my-profile.edit');
    Route::put('/my-profile', [\App\Http\Controllers\Admin\StaffProfileController::class, 'update'])->name('my-profile.update');
    Route::put('/my-profile/settings', [\App\Http\Controllers\Admin\StaffProfileController::class, 'updateSettings'])->name('my-profile.settings');
    Route::post('/my-profile/password', [\App\Http\Controllers\Admin\StaffProfileController::class, 'updatePassword'])->name('my-profile.password');
    Route::get('/my-work', [\App\Http\Controllers\Admin\StaffProfileController::class, 'myWork'])->name('my-profile.work');

    // ── People / Accounts 360° (staff boundary; object-level gates in controller) ──
    Route::get('/people', [\App\Http\Controllers\Admin\PeopleController::class, 'index'])->name('people.index');
    Route::get('/people/{user}', [\App\Http\Controllers\Admin\PeopleController::class, 'show'])->name('people.show');

    // ── Account ecosystem: own earnings, directory, assignments, calls ──
    Route::get('/earnings', [\App\Http\Controllers\Admin\StaffEarningsController::class, 'show'])->name('earnings.show');
    Route::get('/directory', [\App\Http\Controllers\Admin\AccountEcosystemController::class, 'directory'])->name('directory.index');
    Route::get('/directory/{user}', [\App\Http\Controllers\Admin\AccountEcosystemController::class, 'directoryShow'])->name('directory.show');
    Route::get('/team-assignments', [\App\Http\Controllers\Admin\AccountEcosystemController::class, 'assignments'])->name('ecosystem.assignments');
    Route::post('/team-assignments', [\App\Http\Controllers\Admin\AccountEcosystemController::class, 'storeAssignment'])->name('ecosystem.assignments.store');
    Route::post('/team-assignments/{assignment}/transition', [\App\Http\Controllers\Admin\AccountEcosystemController::class, 'transitionAssignment'])->name('ecosystem.assignments.transition');
    Route::get('/call-logs', [\App\Http\Controllers\Admin\AccountEcosystemController::class, 'calls'])->name('ecosystem.calls');
    Route::post('/call-logs', [\App\Http\Controllers\Admin\AccountEcosystemController::class, 'storeCall'])->name('ecosystem.calls.store');
    Route::get('/team/{user}/compensation', [\App\Http\Controllers\Admin\AccountEcosystemController::class, 'editCompensation'])->name('ecosystem.compensation.edit');
    Route::put('/team/{user}/compensation', [\App\Http\Controllers\Admin\AccountEcosystemController::class, 'updateCompensation'])->name('ecosystem.compensation.update');

    // ── Staff messaging + emergency triage ──
    Route::get('/messages', [\App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/create', [\App\Http\Controllers\MessageController::class, 'create'])->name('messages.create');
    Route::post('/messages', [\App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{id}', [\App\Http\Controllers\MessageController::class, 'show'])->name('messages.show');
    Route::get('/emergency', [\App\Http\Controllers\EmergencyController::class, 'index'])->name('emergency.index');
    Route::get('/emergency/{id}', [\App\Http\Controllers\EmergencyController::class, 'show'])->name('emergency.show');
    Route::post('/emergency/{id}/transition', [\App\Http\Controllers\EmergencyController::class, 'transition'])->name('emergency.transition');

    // ── Payroll + bank transfers (finance only) ──
    Route::middleware('requires.role:isFinanceManager')->group(function () {
        Route::get('/salaries', [\App\Http\Controllers\Admin\SalaryController::class, 'index'])->name('salaries.index');
        Route::get('/salaries/create', [\App\Http\Controllers\Admin\SalaryController::class, 'create'])->name('salaries.create');
        Route::post('/salaries', [\App\Http\Controllers\Admin\SalaryController::class, 'store'])->name('salaries.store');
        Route::get('/salaries/{salary}', [\App\Http\Controllers\Admin\SalaryController::class, 'show'])->name('salaries.show');
        Route::post('/salaries/{salary}/approve', [\App\Http\Controllers\Admin\SalaryController::class, 'approve'])->name('salaries.approve');
        Route::post('/salaries/{salary}/pay', [\App\Http\Controllers\Admin\SalaryController::class, 'pay'])->name('salaries.pay');
        Route::get('/salaries/{salary}/payslip', [\App\Http\Controllers\Admin\SalaryController::class, 'payslip'])->name('salaries.payslip');
        Route::post('/commissions/payout/{payout}/complete', [\App\Http\Controllers\Admin\CommissionController::class, 'completePayout'])->name('commissions.payout.complete');
        Route::get('/transfers', [\App\Http\Controllers\Admin\BankTransferController::class, 'index'])->name('transfers.index');
        Route::get('/transfers/{transfer}', [\App\Http\Controllers\Admin\BankTransferController::class, 'show'])->name('transfers.show');
        Route::post('/transfers/{transfer}/approve', [\App\Http\Controllers\Admin\BankTransferController::class, 'approve'])->name('transfers.approve');
        Route::post('/transfers/{transfer}/process', [\App\Http\Controllers\Admin\BankTransferController::class, 'process'])->name('transfers.process');
        Route::post('/transfers/{transfer}/complete', [\App\Http\Controllers\Admin\BankTransferController::class, 'complete'])->name('transfers.complete');
        Route::post('/transfers/{transfer}/fail', [\App\Http\Controllers\Admin\BankTransferController::class, 'fail'])->name('transfers.fail');
        Route::post('/transfers/{transfer}/cancel', [\App\Http\Controllers\Admin\BankTransferController::class, 'cancel'])->name('transfers.cancel');
        Route::get('/franchises', [\App\Http\Controllers\Admin\FranchiseController::class, 'index'])->name('franchises.index');
        Route::get('/franchises/create', [\App\Http\Controllers\Admin\FranchiseController::class, 'create'])->name('franchises.create');
        Route::post('/franchises', [\App\Http\Controllers\Admin\FranchiseController::class, 'store'])->name('franchises.store');
        Route::get('/franchises/{franchise}', [\App\Http\Controllers\Admin\FranchiseController::class, 'show'])->name('franchises.show');
    });
    // ── Traceability: customer 360°, employee history, global search ──
    Route::get('/history/customer/{user}', [\App\Http\Controllers\Admin\TraceabilityController::class, 'customer'])->name('history.customer');
    Route::get('/history/employee/{user}', [\App\Http\Controllers\Admin\TraceabilityController::class, 'employee'])->name('history.employee');
    Route::get('/my-work-history', [\App\Http\Controllers\Admin\TraceabilityController::class, 'myWork'])->name('history.my-work');
    Route::get('/search', [\App\Http\Controllers\Admin\TraceabilityController::class, 'search'])->name('search.index');

    // ── Audit & traceability dashboard + consistency (admin only) ──
    Route::middleware('requires.role:isAdmin')->group(function () {
        Route::get('/history/audit', [\App\Http\Controllers\Admin\TraceabilityController::class, 'auditDashboard'])->name('history.audit');
        Route::get('/history/consistency', [\App\Http\Controllers\Admin\TraceabilityController::class, 'consistency'])->name('history.consistency');
    });

    // ── Live service operations (project hierarchy) ──
    Route::middleware('requires.role:isProjectManager')->group(function () {
        Route::get('/operations', [\App\Http\Controllers\Admin\ServiceTrackingController::class, 'operations'])->name('operations.index');
        Route::post('/projects/{project}/publish-update', [\App\Http\Controllers\Admin\ServiceTrackingController::class, 'publishUpdate'])->name('projects.publish-update');
        Route::post('/projects/{project}/internal-note', [\App\Http\Controllers\Admin\ServiceTrackingController::class, 'internalNote'])->name('projects.internal-note');
        Route::post('/service-events/{event}/answer', [\App\Http\Controllers\Admin\ServiceTrackingController::class, 'answerQuery'])->name('service-events.answer');
        Route::post('/projects/{project}/maintenance', [\App\Http\Controllers\Admin\ServiceTrackingController::class, 'storeMaintenance'])->name('projects.maintenance.store');
        Route::post('/maintenance/{maintenance}/complete', [\App\Http\Controllers\Admin\ServiceTrackingController::class, 'completeMaintenance'])->name('maintenance.complete');
        Route::post('/change-requests/{change}/review', [\App\Http\Controllers\Admin\ServiceTrackingController::class, 'reviewChange'])->name('change-requests.review');
    });
    Route::get('/service/{project}', [\App\Http\Controllers\Admin\ServiceTrackingController::class, 'service'])->name('service.show');

    // ── Website-based HTML Training + Problem & Solution Center (all staff) ──
    Route::prefix('help')->name('help.')->group(function () {
        Route::get('/training', [\App\Http\Controllers\Admin\HelpCenterController::class, 'trainingIndex'])->name('training.index');
        Route::get('/training/{slug}', [\App\Http\Controllers\Admin\HelpCenterController::class, 'trainingShow'])->name('training.show');
        Route::get('/problems', [\App\Http\Controllers\Admin\HelpCenterController::class, 'problemsIndex'])->name('problems.index');
        Route::get('/problems/{slug}', [\App\Http\Controllers\Admin\HelpCenterController::class, 'problemShow'])->name('problems.show');
    });

    // ── PerfectITSecurity Academy: employee learning (all staff) ──
    Route::prefix('academy')->name('academy.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\LearningController::class, 'index'])->name('index');
        Route::get('/my-learning', [\App\Http\Controllers\Admin\LearningController::class, 'my'])->name('my');
        Route::get('/courses/{course:slug}', [\App\Http\Controllers\Admin\LearningController::class, 'course'])->name('course');
        Route::get('/lessons/{lesson}', [\App\Http\Controllers\Admin\LearningController::class, 'lesson'])->name('lesson');
        Route::post('/lessons/{lesson}/complete', [\App\Http\Controllers\Admin\LearningController::class, 'completeLesson'])->name('lesson.complete');
        Route::get('/quizzes/{quiz}', [\App\Http\Controllers\Admin\LearningController::class, 'quiz'])->name('quiz');
        Route::post('/quizzes/{quiz}', [\App\Http\Controllers\Admin\LearningController::class, 'submitQuiz'])->name('quiz.submit');
        Route::get('/attempts/{attempt}', [\App\Http\Controllers\Admin\LearningController::class, 'quizResult'])->name('quiz.result');
        Route::get('/practicals/{practical}', [\App\Http\Controllers\Admin\LearningController::class, 'practical'])->name('practical');
        Route::post('/practicals/{practical}', [\App\Http\Controllers\Admin\LearningController::class, 'submitPractical'])->name('practical.submit');
        Route::get('/certificates', [\App\Http\Controllers\Admin\LearningController::class, 'certificates'])->name('certificates');
        Route::get('/certificates/{certificate}', [\App\Http\Controllers\Admin\LearningController::class, 'certificate'])->name('certificate');
    });

    // ── Academy management: trainers / training managers (admins inherit) ──
    Route::middleware('requires.role:isTrainingManager')->prefix('training')->name('training.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\TrainingManageController::class, 'dashboard'])->name('dashboard');
        Route::get('/courses', [\App\Http\Controllers\Admin\TrainingManageController::class, 'courses'])->name('courses');
        Route::get('/courses/create', [\App\Http\Controllers\Admin\TrainingManageController::class, 'createCourse'])->name('courses.create');
        Route::post('/courses', [\App\Http\Controllers\Admin\TrainingManageController::class, 'storeCourse'])->name('courses.store');
        Route::get('/courses/{course:slug}', [\App\Http\Controllers\Admin\TrainingManageController::class, 'showCourse'])->name('courses.show');
        Route::get('/courses/{course:slug}/edit', [\App\Http\Controllers\Admin\TrainingManageController::class, 'editCourse'])->name('courses.edit');
        Route::put('/courses/{course:slug}', [\App\Http\Controllers\Admin\TrainingManageController::class, 'updateCourse'])->name('courses.update');
        Route::post('/courses/{course}/modules', [\App\Http\Controllers\Admin\TrainingManageController::class, 'storeModule'])->name('modules.store');
        Route::delete('/modules/{module}', [\App\Http\Controllers\Admin\TrainingManageController::class, 'destroyModule'])->name('modules.destroy');
        Route::get('/courses/{course:slug}/lessons/create', [\App\Http\Controllers\Admin\TrainingManageController::class, 'createLesson'])->name('lessons.create');
        Route::post('/courses/{course:slug}/lessons', [\App\Http\Controllers\Admin\TrainingManageController::class, 'storeLesson'])->name('lessons.store');
        Route::get('/lessons/{lesson}/edit', [\App\Http\Controllers\Admin\TrainingManageController::class, 'editLesson'])->name('lessons.edit');
        Route::put('/lessons/{lesson}', [\App\Http\Controllers\Admin\TrainingManageController::class, 'updateLesson'])->name('lessons.update');
        Route::post('/courses/{course}/quizzes', [\App\Http\Controllers\Admin\TrainingManageController::class, 'storeQuiz'])->name('quizzes.store');
        Route::post('/quizzes/{quiz}/questions', [\App\Http\Controllers\Admin\TrainingManageController::class, 'storeQuestion'])->name('questions.store');
        Route::delete('/questions/{question}', [\App\Http\Controllers\Admin\TrainingManageController::class, 'destroyQuestion'])->name('questions.destroy');
        Route::post('/courses/{course}/practicals', [\App\Http\Controllers\Admin\TrainingManageController::class, 'storePractical'])->name('practicals.store');
        Route::post('/courses/{course}/assign', [\App\Http\Controllers\Admin\TrainingManageController::class, 'assign'])->name('assign');
        Route::post('/assignments/{assignment}/reset', [\App\Http\Controllers\Admin\TrainingManageController::class, 'resetProgress'])->name('assignments.reset');
        Route::get('/submissions', [\App\Http\Controllers\Admin\TrainingManageController::class, 'submissions'])->name('submissions');
        Route::post('/submissions/{submission}/review', [\App\Http\Controllers\Admin\TrainingManageController::class, 'reviewSubmission'])->name('submissions.review');
        Route::post('/certificates/{certificate}/revoke', [\App\Http\Controllers\Admin\TrainingManageController::class, 'revokeCertificate'])->name('certificates.revoke');
        Route::get('/employees/{user}', [\App\Http\Controllers\Admin\TrainingManageController::class, 'employee'])->name('employees.show');
        Route::post('/employees/{user}/notes', [\App\Http\Controllers\Admin\TrainingManageController::class, 'storeNote'])->name('notes.store');
        Route::get('/reports', [\App\Http\Controllers\Admin\TrainingManageController::class, 'reports'])->name('reports');
        Route::get('/assign', [\App\Http\Controllers\Admin\TrainingManageController::class, 'staffList'])->name('assign.index');
    });
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

// Public member verification (QR target: minimal disclosure, throttled).
Route::get('/verify/member/{token}', [\App\Http\Controllers\VerifyMemberController::class, 'show'])->middleware('throttle:30,1')->name('verify.member');
// Public certificate verification (signed token, minimal disclosure, throttled).
Route::get('/verify/certificate/{token}', [\App\Http\Controllers\VerifyCertificateController::class, 'show'])->middleware('throttle:30,1')->name('verify.certificate');

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
    Route::get('/test-bench', [\App\Http\Controllers\Admin\AiController::class, 'testBench'])->name('test-bench');
    Route::post('/test-bench', [\App\Http\Controllers\Admin\AiController::class, 'runTestBench'])->name('test-bench.run');
    Route::get('/skills', [\App\Http\Controllers\Admin\AiSkillController::class, 'index'])->name('skills.index');
    Route::get('/skills/create', [\App\Http\Controllers\Admin\AiSkillController::class, 'create'])->name('skills.create');
    Route::post('/skills', [\App\Http\Controllers\Admin\AiSkillController::class, 'store'])->name('skills.store');
    Route::get('/skills/{skill}/edit', [\App\Http\Controllers\Admin\AiSkillController::class, 'edit'])->name('skills.edit');
    Route::put('/skills/{skill}', [\App\Http\Controllers\Admin\AiSkillController::class, 'update'])->name('skills.update');
    Route::delete('/skills/{skill}', [\App\Http\Controllers\Admin\AiSkillController::class, 'destroy'])->name('skills.destroy');
    Route::post('/skills/{skill}/duplicate', [\App\Http\Controllers\Admin\AiSkillController::class, 'duplicate'])->name('skills.duplicate');
    Route::post('/skills/{skill}/toggle', [\App\Http\Controllers\Admin\AiSkillController::class, 'toggle'])->name('skills.toggle');
});
