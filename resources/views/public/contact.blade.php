@extends('layouts.public')

@section('title', 'Direct Infrastructure & SOC Contact — PerfectITSecurity')
@section('description', 'Connect with our senior cybersecurity engineers, solutions architects, and 24/7 technical operations center.')

@section('content')

{{-- HERO — CONTACT://SECURE-CHANNEL --}}
<section class="relative w-full overflow-hidden" aria-labelledby="contact-hero-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-32 pb-12 lg:pb-16">
        <div class="max-w-4xl">
            <div class="flex flex-wrap items-center gap-2.5 mb-7">
                <span class="term-tag term-tag-accent">CONTACT://SECURE-CHANNEL</span>
                <span class="term-status text-accent-soft"><span class="term-status-dot" aria-hidden="true"></span>24/7 Gateway</span>
            </div>
            <h1 id="contact-hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-6xl lg:text-7xl text-navy-900 dark:text-white text-balance">
                CONNECT DIRECTLY WITH <span class="text-accent-soft">OUR ENGINEERING TEAM.</span>
            </h1>
            <p class="mt-6 text-base sm:text-lg lg:text-xl leading-relaxed text-slate-600 dark:text-term-800 max-w-3xl">
                Whether requesting an immediate threat assessment, migrating cloud infrastructure, or configuring enterprise SLA support, our team is standing by.
            </p>
        </div>
    </div>
</section>

{{-- Contact grid --}}
<section class="relative w-full py-16 sm:py-20 lg:py-24 border-t border-term-300 dark:border-white/5" aria-label="Contact form and channels">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <div class="grid lg:grid-cols-12 gap-6 lg:gap-8">

            {{-- Form column --}}
            <div class="lg:col-span-8">
                <div class="term-panel p-6 sm:p-10">
                    <div class="term-sec-label mb-3">CONTACT://CONSULT-REQUEST</div>
                    <h2 class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-navy-900 dark:text-white">Initiate technical consultation</h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-term-800">Fill out the parameters below and a principal architect will reach out within 2 hours.</p>

                    @if(session('success'))
                    <div class="mt-6 border border-accent/50 bg-accent/10 px-4 py-3 text-sm font-semibold text-navy-900 dark:text-white" role="status">
                        {{ session('success') }}
                    </div>
                    @endif
                    @if($errors->any())
                    <div class="mt-6 border border-red-500/50 bg-red-500/10 px-4 py-3 text-sm text-red-700 dark:text-red-300" role="alert">
                        <p class="font-bold mb-1">Please correct the following:</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('contact.submit') }}" method="POST" class="mt-8 space-y-6">
                        @csrf
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label class="term-field-label" for="contact-name">Full Name *</label>
                                <input type="text" id="contact-name" name="name" value="{{ old('name') }}" required
                                       class="term-input" @error('name') aria-invalid="true" @enderror>
                                @error('name') <p class="term-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="term-field-label" for="contact-email">Corporate Email *</label>
                                <input type="email" id="contact-email" name="email" value="{{ old('email') }}" required
                                       class="term-input" @error('email') aria-invalid="true" @enderror>
                                @error('email') <p class="term-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label class="term-field-label" for="contact-company">Company / Organization</label>
                                <input type="text" id="contact-company" name="company" value="{{ old('company') }}" placeholder="Acme Enterprise"
                                       class="term-input">
                            </div>
                            <div>
                                <label class="term-field-label" for="contact-phone">Direct Phone</label>
                                <input type="tel" id="contact-phone" name="phone" value="{{ old('phone') }}" placeholder="+1 (555) 000-0000"
                                       class="term-input">
                            </div>
                        </div>

                        <div>
                            <label class="term-field-label" for="contact-subject">Primary Consultation Focus *</label>
                            <select name="subject" id="contact-subject" class="term-input">
                                <option value="cybersecurity">Cybersecurity Architecture &amp; Penetration Audit</option>
                                <option value="managed-it">Managed IT Infrastructure &amp; 24/7 SOC</option>
                                <option value="cloud">Cloud Migration &amp; Multi-Cloud DevOps</option>
                                <option value="general">Custom Enterprise Work Order / Quote</option>
                                <option value="support">Active Contract SLA Escalation</option>
                            </select>
                        </div>

                        <div>
                            <label class="term-field-label" for="contact-message">Project Scope &amp; Technical Details *</label>
                            <textarea name="message" id="contact-message" rows="5" required
                                      placeholder="Provide an overview of your current infrastructure, timeline, user count, or specific threat vectors..."
                                      class="term-input resize-none" @error('message') aria-invalid="true" @enderror>{{ old('message') }}</textarea>
                            @error('message') <p class="term-error">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" class="term-btn">
                            Submit Request to Engineering
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Sidebar column --}}
            <div class="lg:col-span-4 space-y-4 sm:space-y-5">
                <div class="term-panel p-7 sm:p-8">
                    <div class="term-sec-label mb-4">SOC://EMERGENCY-DISPATCH</div>
                    <h3 class="font-display text-xl font-bold tracking-tight text-navy-900 dark:text-white">Critical incident response</h3>
                    <p class="mt-2 text-[13px] leading-relaxed text-slate-600 dark:text-term-800">
                        Under active attack or experiencing severe downtime? Our SOC tier-1 dispatch hotline is active 24/7/365 with immediate engineer escalation.
                    </p>
                    <div class="mt-5 px-4 py-3 border border-accent/40 bg-accent/5 font-mono text-accent-soft text-sm font-bold tracking-wider">
                        +1 (800) 555-TECH-SOC
                    </div>
                </div>

                <div class="term-panel p-7 sm:p-8">
                    <div class="term-sec-label mb-5">CONTACT://DIRECTORY</div>
                    <dl class="space-y-5">
                        <div>
                            <dt class="font-mono text-[10px] uppercase tracking-[0.2em] text-term-700 mb-1">Corporate dispatch</dt>
                            <dd class="text-base font-bold text-navy-900 dark:text-white">ops@techsupportsolutions.com</dd>
                        </div>
                        <div class="pt-4 border-t border-term-300 dark:border-white/5">
                            <dt class="font-mono text-[10px] uppercase tracking-[0.2em] text-term-700 mb-1">Guaranteed response</dt>
                            <dd class="text-sm font-bold text-accent-soft">Under 15 minutes for P1 tickets</dd>
                        </div>
                        <div class="pt-4 border-t border-term-300 dark:border-white/5">
                            <dt class="font-mono text-[10px] uppercase tracking-[0.2em] text-term-700 mb-1">Headquarters</dt>
                            <dd class="text-sm text-slate-600 dark:text-term-800">Level 42, Cyber Tower One, Technology Corridor</dd>
                        </div>
                    </dl>
                </div>
            </div>

        </div>
    </div>
</section>

@endsection
