@extends('layouts.public')

@section('title', 'Refund Policy — TechSupport Solutions')
@section('description', 'TechSupport Solutions refund policy for IT services, subscriptions, and professional services.')

@section('content')

<section class="relative w-full py-24 lg:py-32 bg-space-deep overflow-hidden z-10">
    <div class="absolute inset-0 bg-cyber-grid opacity-15 pointer-events-none"></div>
    <div class="relative z-10 max-w-4xl mx-auto px-6 sm:px-10 lg:px-16">
        <div class="text-center mb-16">
            <h1 class="text-4xl sm:text-5xl font-black text-white tracking-tight mb-4">Refund Policy</h1>
            <p class="text-sm text-slate-400">Last updated: September 8, 2026</p>
        </div>

        <div class="prose prose-invert prose-lg max-w-none">
            <div class="cosmic-card p-8 lg:p-12 space-y-8">

                <div>
                    <h2 class="text-xl font-bold text-white mb-3">1. Overview</h2>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        At TechSupport Solutions, we are committed to delivering high-quality IT services. This Refund Policy outlines the terms under which refunds may be requested for our services, subscriptions, and professional engagements.
                    </p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-white mb-3">2. Service-Based Refunds</h2>
                    <p class="text-sm text-slate-300 leading-relaxed mb-3">
                        Refunds for professional IT services are handled on a case-by-case basis:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-sm text-slate-300">
                        <li><strong class="text-white">Fixed-Scope Projects:</strong> If work has not commenced, a full refund minus any administrative processing fees (up to 5%) may be issued within 14 days of payment. If work has commenced, refunds are calculated proportionally based on completed milestones.</li>
                        <li><strong class="text-white">Retainer/Monthly Services:</strong> Monthly retainer services may be cancelled with 30 days' written notice. No partial-month refunds are issued for services already delivered.</li>
                        <li><strong class="text-white">Hourly Support:</strong> Billed hours already worked are non-refundable. Pre-paid hour blocks may be refunded on a pro-rata basis for unused hours within 30 days of purchase.</li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-white mb-3">3. Subscription Refunds</h2>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        For subscription-based services (e.g., managed IT support plans, monitoring services), refunds may be issued within the first 14 days of a new subscription if no services have been rendered. After 14 days, subscriptions follow a 30-day cancellation notice period with no retroactive refund.
                    </p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-white mb-3">4. Deposit & Escrow Payments</h2>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        Deposits paid for project escrow are refundable in full only if the project has not been initiated by our team. Once work begins, the deposit covers initial mobilisation costs and is non-refundable. The remaining balance follows the proportional milestone refund structure outlined above.
                    </p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-white mb-3">5. Non-Refundable Items</h2>
                    <ul class="list-disc list-inside space-y-2 text-sm text-slate-300">
                        <li>Third-party software licenses purchased on behalf of the client</li>
                        <li>Cloud infrastructure costs already incurred</li>
                        <li>Domain registrations and SSL certificate purchases</li>
                        <li>Completed penetration testing and security audit reports</li>
                        <li>Training sessions already delivered</li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-white mb-3">6. How to Request a Refund</h2>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        To request a refund, please contact our billing team at <strong class="text-white">billing@techsupport.com</strong> with your invoice number, reason for the refund request, and any relevant documentation. We will review your request within 5 business days and respond with our decision.
                    </p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-white mb-3">7. Refund Processing</h2>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        Approved refunds will be processed within 10 business days using the original payment method. For international transactions, processing times may vary based on your bank and region.
                    </p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-white mb-3">8. Dispute Resolution</h2>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        If you disagree with our refund decision, you may escalate the matter to our senior management team at <strong class="text-white">complaints@techsupport.com</strong>. We aim to resolve all disputes amicably within 20 business days.
                    </p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-white mb-3">9. Contact Information</h2>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        For any questions about this Refund Policy, please contact us at:<br>
                        <strong class="text-white">Email:</strong> billing@techsupport.com<br>
                        <strong class="text-white">Phone:</strong> +1 (555) 123-4567<br>
                        <strong class="text-white">Address:</strong> TechSupport Solutions, 123 Tech Avenue, San Francisco, CA 94105, USA
                    </p>
                </div>

            </div>
        </div>
    </div>
</section>

@endsection
