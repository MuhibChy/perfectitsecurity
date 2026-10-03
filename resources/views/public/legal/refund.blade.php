@extends('layouts.public')

@section('title', 'Refund Policy — PerfectITSecurity')
@section('description', 'PerfectITSecurity refund policy for IT services, subscriptions, and professional services.')

@section('content')

{{-- HERO — LEGAL://REFUNDS --}}
<section class="relative w-full overflow-hidden" aria-labelledby="refund-hero-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-4xl mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-32 pb-12 lg:pb-16 text-center">
        <div class="flex flex-wrap items-center justify-center gap-2.5 mb-7">
            <span class="term-tag term-tag-accent">LEGAL://REFUNDS</span>
        </div>
        <h1 id="refund-hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-5xl text-navy-900 dark:text-white text-balance">REFUND POLICY</h1>
        <p class="mt-4 font-mono text-[11px] tracking-wider text-term-700">LAST-UPDATED:// SEPTEMBER 8, 2026</p>
    </div>
</section>

<section class="relative w-full pb-16 sm:pb-20 lg:pb-24" aria-label="Refund policy content">
    <div class="w-full max-w-4xl mx-auto px-4 sm:px-6 lg:px-10">
        <div class="term-panel p-6 sm:p-8 lg:p-12">
            <div class="prose dark:prose-invert max-w-none space-y-8 text-slate-600 dark:text-term-800">

                <div>
                    <div class="term-sec-label mb-2">SECTION://01</div>
                    <h2 class="font-display text-xl font-bold text-navy-900 dark:text-white mb-3">Overview</h2>
                    <p class="text-sm leading-relaxed">
                        At PerfectITSecurity, we are committed to delivering high-quality IT services. This Refund Policy outlines the terms under which refunds may be requested for our services, subscriptions, and professional engagements.
                    </p>
                </div>

                <div>
                    <div class="term-sec-label mb-2">SECTION://02</div>
                    <h2 class="font-display text-xl font-bold text-navy-900 dark:text-white mb-3">Service-Based Refunds</h2>
                    <p class="text-sm leading-relaxed mb-3">
                        Refunds for professional IT services are handled on a case-by-case basis:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-sm">
                        <li><strong class="text-navy-900 dark:text-white">Fixed-Scope Projects:</strong> If work has not commenced, a full refund minus any administrative processing fees (up to 5%) may be issued within 14 days of payment. If work has commenced, refunds are calculated proportionally based on completed milestones.</li>
                        <li><strong class="text-navy-900 dark:text-white">Retainer/Monthly Services:</strong> Monthly retainer services may be cancelled with 30 days' written notice. No partial-month refunds are issued for services already delivered.</li>
                        <li><strong class="text-navy-900 dark:text-white">Hourly Support:</strong> Billed hours already worked are non-refundable. Pre-paid hour blocks may be refunded on a pro-rata basis for unused hours within 30 days of purchase.</li>
                    </ul>
                </div>

                <div>
                    <div class="term-sec-label mb-2">SECTION://03</div>
                    <h2 class="font-display text-xl font-bold text-navy-900 dark:text-white mb-3">Subscription Refunds</h2>
                    <p class="text-sm leading-relaxed">
                        For subscription-based services (e.g., managed IT support plans, monitoring services), refunds may be issued within the first 14 days of a new subscription if no services have been rendered. After 14 days, subscriptions follow a 30-day cancellation notice period with no retroactive refund.
                    </p>
                </div>

                <div>
                    <div class="term-sec-label mb-2">SECTION://04</div>
                    <h2 class="font-display text-xl font-bold text-navy-900 dark:text-white mb-3">Deposit &amp; Escrow Payments</h2>
                    <p class="text-sm leading-relaxed">
                        Deposits paid for project escrow are refundable in full only if the project has not been initiated by our team. Once work begins, the deposit covers initial mobilisation costs and is non-refundable. The remaining balance follows the proportional milestone refund structure outlined above.
                    </p>
                </div>

                <div>
                    <div class="term-sec-label mb-2">SECTION://05</div>
                    <h2 class="font-display text-xl font-bold text-navy-900 dark:text-white mb-3">Non-Refundable Items</h2>
                    <ul class="list-disc list-inside space-y-2 text-sm">
                        <li>Third-party software licenses purchased on behalf of the client</li>
                        <li>Cloud infrastructure costs already incurred</li>
                        <li>Domain registrations and SSL certificate purchases</li>
                        <li>Completed penetration testing and security audit reports</li>
                        <li>Training sessions already delivered</li>
                    </ul>
                </div>

                <div>
                    <div class="term-sec-label mb-2">SECTION://06</div>
                    <h2 class="font-display text-xl font-bold text-navy-900 dark:text-white mb-3">How to Request a Refund</h2>
                    <p class="text-sm leading-relaxed">
                        To request a refund, please contact our billing team at <strong class="text-navy-900 dark:text-white">billing@techsupport.com</strong> with your invoice number, reason for the refund request, and any relevant documentation. We will review your request within 5 business days and respond with our decision.
                    </p>
                </div>

                <div>
                    <div class="term-sec-label mb-2">SECTION://07</div>
                    <h2 class="font-display text-xl font-bold text-navy-900 dark:text-white mb-3">Refund Processing</h2>
                    <p class="text-sm leading-relaxed">
                        Approved refunds will be processed within 10 business days using the original payment method. For international transactions, processing times may vary based on your bank and region.
                    </p>
                </div>

                <div>
                    <div class="term-sec-label mb-2">SECTION://08</div>
                    <h2 class="font-display text-xl font-bold text-navy-900 dark:text-white mb-3">Dispute Resolution</h2>
                    <p class="text-sm leading-relaxed">
                        If you disagree with our refund decision, you may escalate the matter to our senior management team at <strong class="text-navy-900 dark:text-white">complaints@techsupport.com</strong>. We aim to resolve all disputes amicably within 20 business days.
                    </p>
                </div>

                <div>
                    <div class="term-sec-label mb-2">SECTION://09</div>
                    <h2 class="font-display text-xl font-bold text-navy-900 dark:text-white mb-3">Contact Information</h2>
                    <p class="text-sm leading-relaxed">
                        For any questions about this Refund Policy, please contact us at:<br>
                        <strong class="text-navy-900 dark:text-white">Email:</strong> billing@techsupport.com<br>
                        <strong class="text-navy-900 dark:text-white">Phone:</strong> +1 (555) 123-4567<br>
                        <strong class="text-navy-900 dark:text-white">Address:</strong> PerfectITSecurity, 123 Tech Avenue, San Francisco, CA 94105, USA
                    </p>
                </div>

            </div>
        </div>
    </div>
</section>

@endsection
