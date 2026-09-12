@extends('layouts.public')

@section('title', 'Direct Infrastructure & SOC Contact — TechSupport Solutions')
@section('description', 'Connect with our senior cybersecurity engineers, solutions architects, and 24/7 technical operations center.')

@section('content')

{{-- Hero --}}
<section class="relative w-full py-28 lg:py-36 bg-space-radial border-b border-white/10 overflow-hidden z-10">
    {{-- Global 3D hero scene (same implementation as Home) --}}
    <x-hero-scene />
    <div class="absolute inset-0 bg-cyber-grid opacity-15 pointer-events-none"></div>
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16 relative z-10">
        <div class="max-w-4xl">
            <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-xs font-semibold uppercase tracking-widest text-cyan-300 mb-6">
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                24/7 Global Communications Gateway
            </div>
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight leading-[1.05] mb-8">
                Connect Directly with <span class="gradient-text-cyber">Our Engineering Team.</span>
            </h1>
            <p class="text-lg sm:text-xl text-slate-300 leading-relaxed font-normal max-w-3xl">
                Whether requesting an immediate threat assessment, migrating cloud infrastructure, or configuring enterprise SLA support, our team is standing by.
            </p>
        </div>
    </div>
</section>

{{-- Contact Grid (Wide 12-Column Layout) --}}
<section class="relative w-full py-24 lg:py-32 bg-[#030712] border-b border-white/10 z-10">
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="grid lg:grid-cols-12 gap-12 lg:gap-16">

            {{-- Form Column --}}
            <div class="lg:col-span-8">
                <div class="cosmic-glass p-8 sm:p-12 rounded-3xl border border-white/10">
                    <h2 class="text-2xl sm:text-3xl font-black text-white mb-2">Initiate Technical Consultation</h2>
                    <p class="text-sm text-slate-400 mb-8">Fill out the parameters below and a principal architect will reach out within 2 hours.</p>

                    <form action="{{ route('contact.submit') }}" method="POST" class="space-y-6">
                        @csrf
                        <div class="grid sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-mono uppercase tracking-widest text-slate-300 mb-2">Full Name *</label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                       class="w-full px-4 py-3.5 rounded-xl border border-white/10 bg-black/40 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/20 text-sm">
                                @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-mono uppercase tracking-widest text-slate-300 mb-2">Corporate Email *</label>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                       class="w-full px-4 py-3.5 rounded-xl border border-white/10 bg-black/40 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/20 text-sm">
                                @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-mono uppercase tracking-widest text-slate-300 mb-2">Company / Organization</label>
                                <input type="text" name="company" value="{{ old('company') }}" placeholder="Acme Enterprise"
                                       class="w-full px-4 py-3.5 rounded-xl border border-white/10 bg-black/40 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/20 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-mono uppercase tracking-widest text-slate-300 mb-2">Direct Phone</label>
                                <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="+1 (555) 000-0000"
                                       class="w-full px-4 py-3.5 rounded-xl border border-white/10 bg-black/40 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/20 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-mono uppercase tracking-widest text-slate-300 mb-2">Primary Consultation Focus *</label>
                            <select name="subject" class="w-full px-4 py-3.5 rounded-xl border border-white/10 bg-[#040816] text-white focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/20 text-sm">
                                <option value="cybersecurity">Cybersecurity Architecture & Penetration Audit</option>
                                <option value="managed-it">Managed IT Infrastructure & 24/7 SOC</option>
                                <option value="cloud">Cloud Migration & Multi-Cloud DevOps</option>
                                <option value="general">Custom Enterprise Work Order / Quote</option>
                                <option value="support">Active Contract SLA Escalation</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-mono uppercase tracking-widest text-slate-300 mb-2">Project Scope & Technical Details *</label>
                            <textarea name="message" rows="5" required
                                      placeholder="Provide an overview of your current infrastructure, timeline, user count, or specific threat vectors..."
                                      class="w-full px-4 py-3.5 rounded-xl border border-white/10 bg-black/40 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/20 text-sm resize-none">{{ old('message') }}</textarea>
                            @error('message') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit"
                                class="btn btn-lg text-white font-bold text-sm px-8 py-4 rounded-xl transition-all shadow-lg shadow-cyan-500/30 hover:scale-[1.02]"
                                style="background: linear-gradient(135deg, #16A34A, #2563EB);">
                            Submit Request to Engineering
                            <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Sidebar Column --}}
            <div class="lg:col-span-4 space-y-6">
                <div class="cosmic-card p-8">
                    <div class="text-xs font-mono uppercase tracking-widest text-cyan-400 mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Emergency SOC Dispatch
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Critical Incident Response</h3>
                    <p class="text-xs text-slate-400 leading-relaxed mb-6">
                        Under active attack or experiencing severe downtime? Our SOC tier-1 dispatch hotline is active 24/7/365 with immediate engineer escalation.
                    </p>
                    <div class="p-4 rounded-xl bg-cyan-500/10 border border-cyan-500/20 font-mono text-cyan-300 text-sm font-bold">
                        +1 (800) 555-TECH-SOC
                    </div>
                </div>

                <div class="cosmic-card p-8 space-y-6">
                    <div>
                        <div class="text-xs font-mono text-slate-400 uppercase tracking-wider mb-1">Corporate Dispatch</div>
                        <div class="text-base font-bold text-white">ops@techsupportsolutions.com</div>
                    </div>
                    <div class="border-t border-white/10 pt-4">
                        <div class="text-xs font-mono text-slate-400 uppercase tracking-wider mb-1">Guaranteed Response</div>
                        <div class="text-sm font-bold text-emerald-400">Under 15 Minutes for P1 Tickets</div>
                    </div>
                    <div class="border-t border-white/10 pt-4">
                        <div class="text-xs font-mono text-slate-400 uppercase tracking-wider mb-1">Headquarters</div>
                        <div class="text-sm text-slate-300">Level 42, Cyber Tower One, Technology Corridor</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

@endsection
