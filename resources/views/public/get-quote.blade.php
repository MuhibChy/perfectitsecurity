@extends('layouts.public')

@section('title', 'Get a Quote — TechSupport Solutions')
@section('description', 'Request a custom quote for IT services, cybersecurity, cloud solutions, and managed services. Free consultation with no obligation.')

@section('content')

{{-- Hero --}}
<section class="relative w-full py-28 lg:py-36 bg-space-radial border-b border-white/10 overflow-hidden z-10">
    {{-- Global 3D hero scene (same implementation as Home) --}}
    <x-hero-scene />
    <div class="absolute inset-0 bg-cyber-grid opacity-15 pointer-events-none"></div>
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16 relative z-10">
        <div class="max-w-4xl">
            <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-brand-600/10 border border-brand-500/25 text-xs font-semibold uppercase tracking-widest text-brand-300 mb-6">
                <span class="w-2 h-2 rounded-full bg-brand-500 animate-pulse"></span>
                Free Consultation
            </div>
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight leading-[1.05] mb-8">
                Get a <span class="gradient-text-cyber">Custom Quote</span>
            </h1>
            <p class="text-lg sm:text-xl text-slate-300 leading-relaxed font-normal">
                Tell us about your requirements and our solutions team will prepare a tailored proposal with transparent pricing. Free consultation, no obligation.
            </p>
        </div>
    </div>
</section>

{{-- Quote Form --}}
<section class="relative w-full py-16 lg:py-24 bg-[#030712] z-10">
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="grid lg:grid-cols-12 gap-12 lg:gap-16">

            {{-- Form --}}
            <div class="lg:col-span-7">
                @if(session('success'))
                <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </div>
                @endif

                <form action="{{ route('get-quote.submit') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid sm:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-slate-300 mb-2">Full Name *</label>
                            <input type="text" id="name" name="name" required
                                   class="w-full px-4 py-3 bg-white/[0.03] border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-brand-500/60 focus:ring-1 focus:ring-brand-500/30 transition-all"
                                   placeholder="John Smith" value="{{ old('name') }}">
                            @error('name') <p class="mt-1 text-sm text-brand-300">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-300 mb-2">Email Address *</label>
                            <input type="email" id="email" name="email" required
                                   class="w-full px-4 py-3 bg-white/[0.03] border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-brand-500/60 focus:ring-1 focus:ring-brand-500/30 transition-all"
                                   placeholder="john@company.com" value="{{ old('email') }}">
                            @error('email') <p class="mt-1 text-sm text-brand-300">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-6">
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-slate-300 mb-2">Phone Number</label>
                            <input type="tel" id="phone" name="phone"
                                   class="w-full px-4 py-3 bg-white/[0.03] border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-brand-500/60 focus:ring-1 focus:ring-brand-500/30 transition-all"
                                   placeholder="+1 (555) 123-4567" value="{{ old('phone') }}">
                        </div>
                        <div>
                            <label for="company" class="block text-sm font-semibold text-slate-300 mb-2">Company Name</label>
                            <input type="text" id="company" name="company"
                                   class="w-full px-4 py-3 bg-white/[0.03] border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-brand-500/60 focus:ring-1 focus:ring-brand-500/30 transition-all"
                                   placeholder="Acme Corp" value="{{ old('company') }}">
                        </div>
                    </div>

                    <div>
                        <label for="service_interest" class="block text-sm font-semibold text-slate-300 mb-2">Service of Interest</label>
                        <select id="service_interest" name="service_interest"                                class="w-full px-4 py-3 bg-white/[0.03] border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-500/60 focus:ring-1 focus:ring-brand-500/30 transition-all">
                            <option value="" class="bg-[#030712]">Select a service category</option>
                            <option value="cybersecurity" class="bg-[#030712]">Cybersecurity & Penetration Testing</option>
                            <option value="managed-it" class="bg-[#030712]">Managed IT Support</option>
                            <option value="cloud" class="bg-[#030712]">Cloud Infrastructure & Migration</option>
                            <option value="web-dev" class="bg-[#030712]">Web Development</option>
                            <option value="software-dev" class="bg-[#030712]">Software Development</option>
                            <option value="crm-erp" class="bg-[#030712]">CRM / ERP Development</option>
                            <option value="mobile" class="bg-[#030712]">Mobile App Development</option>
                            <option value="consulting" class="bg-[#030712]">IT Consulting</option>
                            <option value="training" class="bg-[#030712]">IT Training</option>
                            <option value="other" class="bg-[#030712]">Other</option>
                        </select>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-6">
                        <div>
                            <label for="service_id" class="block text-sm font-semibold text-slate-300 mb-2">Specific Service (optional)</label>
                            <select id="service_id" name="service_id"
                                    class="w-full px-4 py-3 bg-white/[0.03] border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-500/60 focus:ring-1 focus:ring-brand-500/30 transition-all">
                                <option value="" class="bg-[#030712]">General enquiry</option>
                                @isset($services)
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}" class="bg-[#030712]" {{ old('service_id') == $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                            @error('service_id') <p class="mt-1 text-sm text-brand-300">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="country_id" class="block text-sm font-semibold text-slate-300 mb-2">Country (for local pricing &amp; tax)</label>
                            <select id="country_id" name="country_id"
                                    class="w-full px-4 py-3 bg-white/[0.03] border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-500/60 focus:ring-1 focus:ring-brand-500/30 transition-all">
                                <option value="" class="bg-[#030712]">Select country</option>
                                @isset($countries)
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" class="bg-[#030712]" {{ old('country_id') == $country->id ? 'selected' : '' }}>{{ $country->name }} ({{ $country->currency_code }})</option>
                                    @endforeach
                                @endisset
                            </select>
                            @error('country_id') <p class="mt-1 text-sm text-brand-300">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-6">
                        <div>
                            <label for="budget_range" class="block text-sm font-semibold text-slate-300 mb-2">Estimated Budget</label>
                            <select id="budget_range" name="budget_range"
                                    class="w-full px-4 py-3 bg-white/[0.03] border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-500/60 focus:ring-1 focus:ring-brand-500/30 transition-all">
                                <option value="" class="bg-[#030712]">Select budget range</option>
                                <option value="under-5k" class="bg-[#030712]">Under $5,000</option>
                                <option value="5k-15k" class="bg-[#030712]">$5,000 - $15,000</option>
                                <option value="15k-50k" class="bg-[#030712]">$15,000 - $50,000</option>
                                <option value="50k-100k" class="bg-[#030712]">$50,000 - $100,000</option>
                                <option value="100k-plus" class="bg-[#030712]">$100,000+</option>
                                <option value="not-sure" class="bg-[#030712]">Not sure yet</option>
                            </select>
                        </div>
                        <div>
                            <label for="timeline" class="block text-sm font-semibold text-slate-300 mb-2">Project Timeline</label>
                            <select id="timeline" name="timeline"
                                    class="w-full px-4 py-3 bg-white/[0.03] border border-white/10 rounded-xl text-white focus:outline-none focus:border-brand-500/60 focus:ring-1 focus:ring-brand-500/30 transition-all">
                                <option value="" class="bg-[#030712]">Select timeline</option>
                                <option value="urgent" class="bg-[#030712]">Urgent (ASAP)</option>
                                <option value="1-month" class="bg-[#030712]">Within 1 month</option>
                                <option value="3-months" class="bg-[#030712]">Within 3 months</option>
                                <option value="6-months" class="bg-[#030712]">Within 6 months</option>
                                <option value="exploring" class="bg-[#030712]">Just exploring</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-semibold text-slate-300 mb-2">Project Requirements *</label>
                        <textarea id="message" name="message" rows="6" required
                                  class="w-full px-4 py-3 bg-white/[0.03] border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-brand-500/60 focus:ring-1 focus:ring-brand-500/30 transition-all resize-none"
                                  placeholder="Please describe your project requirements, challenges, and any specific needs...">{{ old('message') }}</textarea>
                        @error('message') <p class="mt-1 text-sm text-brand-300">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit"
                            class="w-full sm:w-auto px-8 py-4 rounded-2xl text-white font-bold text-base transition-all duration-300 hover:scale-105"
                            style="background: linear-gradient(135deg, #16A34A, #2563EB); box-shadow: 0 10px 35px rgba(37,99,235,0.45);">
                        Submit Quote Request
                        <svg class="w-5 h-5 ml-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </button>
                </form>
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-5 space-y-6">
                {{-- What to expect --}}
                <div class="cosmic-glass p-8 rounded-3xl border border-white/10">
                    <h3 class="text-xl font-bold text-white mb-6">What Happens Next</h3>
                    <div class="space-y-5">
                        @foreach([
                            ['step' => '01', 'title' => 'Requirements Review', 'desc' => 'Our solutions team reviews your requirements within 24 hours.'],
                            ['step' => '02', 'title' => 'Free Consultation', 'desc' => 'We schedule a call to discuss your needs in detail and clarify scope.'],
                            ['step' => '03', 'title' => 'Custom Proposal', 'desc' => 'You receive a detailed proposal with transparent pricing and timeline.'],
                            ['step' => '04', 'title' => 'Project Kickoff', 'desc' => 'Once approved, we begin work with regular progress updates.'],
                        ] as $item)
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-xl bg-brand-600/10 border border-brand-500/25 flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-bold text-brand-400 font-mono">{{ $item['step'] }}</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-white">{{ $item['title'] }}</h4>
                                <p class="text-xs text-slate-400 mt-1">{{ $item['desc'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Contact info --}}
                <div class="cosmic-glass p-8 rounded-3xl border border-white/10">
                    <h3 class="text-xl font-bold text-white mb-6">Prefer to Talk?</h3>
                    <div class="space-y-4">
                        <a href="tel:+15551234567" class="flex items-center gap-3 text-slate-300 hover:text-brand-400 transition-colors">
                            <svg class="w-5 h-5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            +1 (555) 123-4567
                        </a>
                        <a href="mailto:info@techsupport.com" class="flex items-center gap-3 text-slate-300 hover:text-brand-400 transition-colors">
                            <svg class="w-5 h-5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            info@techsupport.com
                        </a>
                        <div class="flex items-center gap-3 text-slate-300">
                            <svg class="w-5 h-5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Response within 24 hours
                        </div>
                    </div>
                </div>

                {{-- Trust --}}
                <div class="cosmic-glass p-8 rounded-3xl border border-white/10">
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-black text-white">250+</div>
                            <div class="text-xs text-slate-400 mt-1">Clients Worldwide</div>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-brand-400">99.9%</div>
                            <div class="text-xs text-slate-400 mt-1">Uptime SLA</div>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-white">15min</div>
                            <div class="text-xs text-slate-400 mt-1">Critical Response</div>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-brand-400">24/7</div>
                            <div class="text-xs text-slate-400 mt-1">Emergency Support</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

@endsection
