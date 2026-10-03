@extends('layouts.public')

@section('title', 'Get a Quote — PerfectITSecurity')
@section('description', 'Request a custom quote for IT services, cybersecurity, cloud solutions, and managed services. Free consultation with no obligation.')

@section('content')

{{-- HERO — CONTACT://QUOTE --}}
<section class="relative w-full overflow-hidden" aria-labelledby="quote-hero-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-32 pb-12 lg:pb-16">
        <div class="max-w-4xl">
            <div class="flex flex-wrap items-center gap-2.5 mb-7">
                <span class="term-tag term-tag-accent">CONTACT://QUOTE</span>
                <span class="term-tag">Free Consultation</span>
            </div>
            <h1 id="quote-hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-6xl lg:text-7xl text-navy-900 dark:text-white text-balance">
                GET A <span class="text-accent-soft">CUSTOM QUOTE</span>
            </h1>
            <p class="mt-6 text-base sm:text-lg lg:text-xl leading-relaxed text-slate-600 dark:text-term-800 max-w-3xl">
                Tell us about your requirements and our solutions team will prepare a tailored proposal with transparent pricing. Free consultation, no obligation.
            </p>
        </div>
    </div>
</section>

{{-- Quote form --}}
<section class="relative w-full py-16 sm:py-20 lg:py-24 border-t border-term-300 dark:border-white/5" aria-label="Quote request">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <div class="grid lg:grid-cols-12 gap-6 lg:gap-8">

            {{-- Form --}}
            <div class="lg:col-span-7">
                <div class="term-panel p-6 sm:p-10">
                    <div class="term-sec-label mb-3">QUOTE://PARAMETERS</div>
                    <h2 class="font-display text-2xl font-bold tracking-tight text-navy-900 dark:text-white">Describe your requirements</h2>

                    @if(session('success'))
                    <div class="term-alert term-alert-ok mt-6" role="status">
                        <span class="term-alert-tag">SYS://OK</span>
                        <span>{{ session('success') }}</span>
                    </div>
                    @endif

                    <form action="{{ route('get-quote.submit') }}" method="POST" class="mt-8 space-y-6">
                        @csrf

                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label for="name" class="term-field-label">Full Name *</label>
                                <input type="text" id="name" name="name" required
                                       class="term-input"
                                       placeholder="John Smith" value="{{ old('name') }}" @error('name') aria-invalid="true" @enderror>
                                @error('name') <p class="term-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="email" class="term-field-label">Email Address *</label>
                                <input type="email" id="email" name="email" required
                                       class="term-input"
                                       placeholder="john@company.com" value="{{ old('email') }}" @error('email') aria-invalid="true" @enderror>
                                @error('email') <p class="term-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label for="phone" class="term-field-label">Phone Number</label>
                                <input type="tel" id="phone" name="phone"
                                       class="term-input"
                                       placeholder="+1 (555) 123-4567" value="{{ old('phone') }}">
                            </div>
                            <div>
                                <label for="company" class="term-field-label">Company Name</label>
                                <input type="text" id="company" name="company"
                                       class="term-input"
                                       placeholder="Acme Corp" value="{{ old('company') }}">
                            </div>
                        </div>

                        <div>
                            <label for="service_interest" class="term-field-label">Service of Interest</label>
                            <select id="service_interest" name="service_interest" class="term-input">
                                <option value="">Select a service category</option>
                                <option value="cybersecurity">Cybersecurity &amp; Penetration Testing</option>
                                <option value="managed-it">Managed IT Support</option>
                                <option value="cloud">Cloud Infrastructure &amp; Migration</option>
                                <option value="web-dev">Web Development</option>
                                <option value="software-dev">Software Development</option>
                                <option value="crm-erp">CRM / ERP Development</option>
                                <option value="mobile">Mobile App Development</option>
                                <option value="consulting">IT Consulting</option>
                                <option value="training">IT Training</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label for="service_id" class="term-field-label">Specific Service (optional)</label>
                                <select id="service_id" name="service_id"
                                        class="term-input" @error('service_id') aria-invalid="true" @enderror>
                                    <option value="">General enquiry</option>
                                    @isset($services)
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}" {{ (string) old('service_id', $selectedServiceId ?? '') === (string) $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                                        @endforeach
                                    @endisset
                                </select>
                                @error('service_id') <p class="term-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="country_id" class="term-field-label">Country (for local pricing &amp; tax)</label>
                                <select id="country_id" name="country_id"
                                        class="term-input" @error('country_id') aria-invalid="true" @enderror>
                                    <option value="">Select country</option>
                                    @isset($countries)
                                        @foreach($countries as $country)
                                            <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>{{ $country->name }} ({{ $country->currency_code }})</option>
                                        @endforeach
                                    @endisset
                                </select>
                                @error('country_id') <p class="term-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label for="budget_range" class="term-field-label">Estimated Budget</label>
                                <select id="budget_range" name="budget_range"
                                        class="term-input">
                                    <option value="">Select budget range</option>
                                    <option value="under-5k">Under $5,000</option>
                                    <option value="5k-15k">$5,000 - $15,000</option>
                                    <option value="15k-50k">$15,000 - $50,000</option>
                                    <option value="50k-100k">$50,000 - $100,000</option>
                                    <option value="100k-plus">$100,000+</option>
                                    <option value="not-sure">Not sure yet</option>
                                </select>
                            </div>
                            <div>
                                <label for="timeline" class="term-field-label">Project Timeline</label>
                                <select id="timeline" name="timeline"
                                        class="term-input">
                                    <option value="">Select timeline</option>
                                    <option value="urgent">Urgent (ASAP)</option>
                                    <option value="1-month">Within 1 month</option>
                                    <option value="3-months">Within 3 months</option>
                                    <option value="6-months">Within 6 months</option>
                                    <option value="exploring">Just exploring</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="message" class="term-field-label">Project Requirements *</label>
                            <textarea id="message" name="message" rows="6" required
                                      class="term-input resize-none"
                                      placeholder="Please describe your project requirements, challenges, and any specific needs..." @error('message') aria-invalid="true" @enderror>{{ old('message') }}</textarea>
                            @error('message') <p class="term-error">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" class="term-btn w-full sm:w-auto justify-center">
                            Submit Quote Request
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-5 space-y-4 sm:space-y-5">
                <div class="term-panel p-7 sm:p-8">
                    <div class="term-sec-label mb-6">QUOTE://PIPELINE</div>
                    <div class="space-y-5">
                        @foreach([
                            ['step' => '01', 'title' => 'Requirements Review', 'desc' => 'Our solutions team reviews your requirements within 24 hours.'],
                            ['step' => '02', 'title' => 'Free Consultation', 'desc' => 'We schedule a call to discuss your needs in detail and clarify scope.'],
                            ['step' => '03', 'title' => 'Custom Proposal', 'desc' => 'You receive a detailed proposal with transparent pricing and timeline.'],
                            ['step' => '04', 'title' => 'Project Kickoff', 'desc' => 'Once approved, we begin work with regular progress updates.'],
                        ] as $item)
                        <div class="flex gap-4">
                            <span class="font-mono text-[11px] tracking-[0.2em] text-accent-soft flex-shrink-0 pt-0.5">{{ $item['step'] }}</span>
                            <div>
                                <h4 class="text-sm font-bold text-navy-900 dark:text-white">{{ $item['title'] }}</h4>
                                <p class="text-xs leading-relaxed text-slate-600 dark:text-term-800 mt-1">{{ $item['desc'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="term-panel p-7 sm:p-8">
                    <div class="term-sec-label mb-5">CONTACT://VOICE</div>
                    <div class="space-y-3 text-sm">
                        <a href="tel:+15551234567" class="flex items-center gap-3 text-slate-600 dark:text-term-800 hover:text-accent-soft transition-colors">
                            <span class="font-mono text-[10px] text-term-700">TEL://</span> +1 (555) 123-4567
                        </a>
                        <a href="mailto:info@techsupport.com" class="flex items-center gap-3 text-slate-600 dark:text-term-800 hover:text-accent-soft transition-colors">
                            <span class="font-mono text-[10px] text-term-700">MAIL://</span> info@techsupport.com
                        </a>
                        <div class="flex items-center gap-3 text-slate-600 dark:text-term-800">
                            <span class="font-mono text-[10px] text-term-700">ETA://</span> Response within 24 hours
                        </div>
                    </div>
                </div>

                <div class="term-panel p-7 sm:p-8">
                    <div class="term-sec-label mb-5">SYS://TELEMETRY</div>
                    <dl class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <dt class="sr-only">Clients worldwide</dt>
                            <dd class="font-mono text-2xl font-bold text-navy-900 dark:text-white">250+</dd>
                            <dd class="term-sec-label mt-1">Clients worldwide</dd>
                        </div>
                        <div>
                            <dt class="sr-only">Uptime SLA</dt>
                            <dd class="font-mono text-2xl font-bold text-accent-soft">99.9%</dd>
                            <dd class="term-sec-label mt-1">Uptime SLA</dd>
                        </div>
                        <div>
                            <dt class="sr-only">Critical response</dt>
                            <dd class="font-mono text-2xl font-bold text-navy-900 dark:text-white">15min</dd>
                            <dd class="term-sec-label mt-1">Critical response</dd>
                        </div>
                        <div>
                            <dt class="sr-only">Emergency support</dt>
                            <dd class="font-mono text-2xl font-bold text-accent-soft">24/7</dd>
                            <dd class="term-sec-label mt-1">Emergency support</dd>
                        </div>
                    </dl>
                </div>
            </div>

        </div>
    </div>
</section>

@endsection
