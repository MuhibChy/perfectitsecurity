@extends('layouts.app')

@section('title', 'User Manual — TechSupport Platform')

@section('page-title', '📖 User Manual & Complete Guide')

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- Print Button --}}
    <div class="no-print mb-6 flex justify-between items-center">
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl px-4 py-3 text-sm text-blue-700 dark:text-blue-400">
            <strong>📖 Complete User Manual</strong> — Read online or click Print to save as PDF
        </div>
        <button onclick="window.print()" class="bg-primary-600 hover:bg-primary-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 transition-colors shadow-lg shadow-primary-500/20">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print / Save as PDF
        </button>
    </div>

    {{-- Table of Contents --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
            <span class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center text-primary-600">📋</span>
            Table of Contents
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <a href="#overview" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center text-blue-600 font-bold text-xs">1</span>
                Platform Overview & Purpose
            </a>
            <a href="#getting-started" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center text-green-600 font-bold text-xs">2</span>
                Getting Started & Login
            </a>
            <a href="#roles" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center text-purple-600 font-bold text-xs">3</span>
                User Roles & Permissions
            </a>
            <a href="#customer" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center text-orange-600 font-bold text-xs">4</span>
                Customer Portal Guide
            </a>
            <a href="#admin" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center text-red-600 font-bold text-xs">5</span>
                Admin Panel Guide
            </a>
            <a href="#tickets" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center text-yellow-600 font-bold text-xs">6</span>
                Ticketing System
            </a>
            <a href="#projects" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center text-indigo-600 font-bold text-xs">7</span>
                Projects & Tasks
            </a>
            <a href="#invoicing" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center text-teal-600 font-bold text-xs">8</span>
                Invoicing & Payments
            </a>
            <a href="#financials" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-pink-100 dark:bg-pink-900/30 rounded-lg flex items-center justify-center text-pink-600 font-bold text-xs">9</span>
                Financials & Expenses
            </a>
            <a href="#commissions" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center text-amber-600 font-bold text-xs">10</span>
                Commissions & Payouts
            </a>
            <a href="#reports" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-cyan-100 dark:bg-cyan-900/30 rounded-lg flex items-center justify-center text-cyan-600 font-bold text-xs">11</span>
                Reports & Analytics
            </a>
            <a href="#services" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-lime-100 dark:bg-lime-900/30 rounded-lg flex items-center justify-center text-lime-600 font-bold text-xs">12</span>
                Services & Knowledge Base
            </a>
            <a href="#system" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-rose-100 dark:bg-rose-900/30 rounded-lg flex items-center justify-center text-rose-600 font-bold text-xs">13</span>
                System Health & Settings
            </a>
            <a href="#ai" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-violet-100 dark:bg-violet-900/30 rounded-lg flex items-center justify-center text-violet-600 font-bold text-xs">14</span>
                AI Assistant
            </a>
            <a href="#tips" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center text-gray-600 font-bold text-xs">15</span>
                Tips, Shortcuts & FAQ
            </a>
        </div>
    </div>

    {{-- Section 1: Platform Overview --}}
    <div id="overview" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center text-blue-600">1</span>
            Platform Overview & Purpose
        </h2>

        <div class="bg-gradient-to-r from-primary-50 to-blue-50 dark:from-primary-900/20 dark:to-blue-900/20 rounded-xl p-6 mb-6 border border-primary-200 dark:border-primary-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">🎯 What is TechSupport Platform?</h3>
            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                <strong>TechSupport Platform</strong> is an all-in-one <strong>IT Service Management & Freelance Workforce</strong> platform designed for technology service companies. It combines <strong>ticketing</strong>, <strong>project management</strong>, <strong>invoicing</strong>, <strong>financial tracking</strong>, <strong>freelancer commission management</strong>, and <strong>AI-powered customer support</strong> into a single, unified dashboard.
            </p>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Core Capabilities</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <span class="text-2xl">🎫</span>
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white">Ticketing System</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Create, assign, and track support tickets with SLA policies, priorities, and real-time status updates.</p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <span class="text-2xl">📁</span>
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white">Project Management</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Manage projects with milestones, tasks, budgets, and team collaboration. Assign work to freelancers.</p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <span class="text-2xl">💰</span>
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white">Invoicing & Payments</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Generate invoices, track payments, manage quotations, and monitor outstanding balances.</p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <span class="text-2xl">🤖</span>
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white">AI Chat Assistant</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">AI-powered chatbot for customers to get instant support, create tickets, and find answers.</p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <span class="text-2xl">💳</span>
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white">Commission Tracking</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Automatically track freelancer commissions based on completed tasks, with payout management.</p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <span class="text-2xl">📊</span>
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white">Financial Reports</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Real-time financial dashboards, profit/loss reports, expense tracking, and data export.</p>
                </div>
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Who Is This Platform For?</h3>
        <ul class="space-y-2 text-gray-700 dark:text-gray-300">
            <li class="flex items-start gap-2"><span class="text-green-500 mt-1">✓</span> <strong>IT Service Companies</strong> — Manage support tickets, projects, and client relationships</li>
            <li class="flex items-start gap-2"><span class="text-green-500 mt-1">✓</span> <strong>Managed Service Providers (MSPs)</strong> — Track SLAs, monitor system health, and manage clients</li>
            <li class="flex items-start gap-2"><span class="text-green-500 mt-1">✓</span> <strong>Cybersecurity Firms</strong> — Manage security assessments and vulnerability reports</li>
            <li class="flex items-start gap-2"><span class="text-green-500 mt-1">✓</span> <strong>Freelance IT Teams</strong> — Track tasks, commissions, and payouts for distributed teams</li>
            <li class="flex items-start gap-2"><span class="text-green-500 mt-1">✓</span> <strong>Cloud Service Providers</strong> — Manage migrations, infrastructure, and client billing</li>
        </ul>
    </div>

    {{-- Section 2: Getting Started --}}
    <div id="getting-started" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center text-green-600">2</span>
            Getting Started & Login
        </h2>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Accessing the Platform</h3>
        <ol class="space-y-3 text-gray-700 dark:text-gray-300 mb-6">
            <li class="flex items-start gap-3">
                <span class="w-7 h-7 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center text-primary-600 font-bold text-sm flex-shrink-0">1</span>
                <span>Open your browser and navigate to the platform URL (e.g., <code class="bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded text-sm">http://localhost:8000</code>)</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="w-7 h-7 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center text-primary-600 font-bold text-sm flex-shrink-0">2</span>
                <span>Click the <strong>"Log in"</strong> button in the top navigation bar</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="w-7 h-7 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center text-primary-600 font-bold text-sm flex-shrink-0">3</span>
                <span>Enter your <strong>email address</strong> and <strong>password</strong></span>
            </li>
            <li class="flex items-start gap-3">
                <span class="w-7 h-7 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center text-primary-600 font-bold text-sm flex-shrink-0">4</span>
                <span>Click <strong>"Sign in"</strong> to access your dashboard</span>
            </li>
        </ol>

        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 mb-6">
            <h4 class="font-semibold text-yellow-800 dark:text-yellow-400 flex items-center gap-2 mb-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                New to the platform?
            </h4>
            <p class="text-sm text-yellow-700 dark:text-yellow-300">Click <strong>"Register"</strong> on the login page to create a new customer account. Fill in your name, email, company name, and password to get started.</p>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Password Reset</h3>
        <p class="text-gray-700 dark:text-gray-300 mb-4">If you forgot your password:</p>
        <ol class="space-y-2 text-gray-700 dark:text-gray-300">
            <li>1. Go to the <strong>Login</strong> page</li>
            <li>2. Click <strong>"Forgot your password?"</strong></li>
            <li>3. Enter your email address and click <strong>"Send Reset Link"</strong></li>
            <li>4. Check your email for the reset link (check spam folder too)</li>
            <li>5. Click the link and set a new password</li>
        </ol>
    </div>

    {{-- Section 3: User Roles --}}
    <div id="roles" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center text-purple-600">3</span>
            User Roles & Permissions
        </h2>

        <p class="text-gray-700 dark:text-gray-300 mb-6">The platform has different user roles, each with specific access levels and capabilities.</p>

        <div class="space-y-4">
            {{-- Super Admin --}}
            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <div class="bg-red-50 dark:bg-red-900/10 px-6 py-4 flex items-center gap-3">
                    <span class="text-2xl">👑</span>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white">Super Admin</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Full access to everything</p>
                    </div>
                </div>
                <div class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                    <p class="mb-2"><strong>Can:</strong> Access all admin features, manage users, settings, system health, audit logs, financials, and AI configuration.</p>
                    <p><strong>Dashboard:</strong> Admin Panel → <code class="bg-gray-100 dark:bg-gray-800 px-1.5 rounded">/admin</code></p>
                </div>
            </div>

            {{-- Finance Manager --}}
            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <div class="bg-green-50 dark:bg-green-900/10 px-6 py-4 flex items-center gap-3">
                    <span class="text-2xl">💰</span>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white">Finance Manager</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Financial operations & reporting</p>
                    </div>
                </div>
                <div class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                    <p><strong>Can:</strong> Manage invoices, payments, expenses, commissions, quotations, financial reports, and view profit/loss statements.</p>
                </div>
            </div>

            {{-- Support Manager --}}
            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <div class="bg-blue-50 dark:bg-blue-900/10 px-6 py-4 flex items-center gap-3">
                    <span class="text-2xl">🎯</span>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white">Support Manager</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Support team oversight</p>
                    </div>
                </div>
                <div class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                    <p><strong>Can:</strong> Manage all tickets, assign to agents, view SLA compliance, manage ticket categories, and access support reports.</p>
                </div>
            </div>

            {{-- Support Agent --}}
            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <div class="bg-cyan-50 dark:bg-cyan-900/10 px-6 py-4 flex items-center gap-3">
                    <span class="text-2xl">🎧</span>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white">Support Agent</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Ticket handling & resolution</p>
                    </div>
                </div>
                <div class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                    <p><strong>Can:</strong> View and respond to assigned tickets, update ticket status, add notes, and track time entries.</p>
                </div>
            </div>

            {{-- Project Manager --}}
            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <div class="bg-indigo-50 dark:bg-indigo-900/10 px-6 py-4 flex items-center gap-3">
                    <span class="text-2xl">📋</span>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white">Project Manager</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Project & task oversight</p>
                    </div>
                </div>
                <div class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                    <p><strong>Can:</strong> Create and manage projects, assign tasks, track progress, manage milestones, and coordinate with freelancers.</p>
                </div>
            </div>

            {{-- Freelancer --}}
            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <div class="bg-amber-50 dark:bg-amber-900/10 px-6 py-4 flex items-center gap-3">
                    <span class="text-2xl">🔧</span>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white">Freelancer</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Task execution & commission tracking</p>
                    </div>
                </div>
                <div class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                    <p><strong>Can:</strong> View assigned tasks, submit work, track commissions, and view payout history.</p>
                </div>
            </div>

            {{-- Customer --}}
            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <div class="bg-orange-50 dark:bg-orange-900/10 px-6 py-4 flex items-center gap-3">
                    <span class="text-2xl">👤</span>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white">Customer</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Service consumer</p>
                    </div>
                </div>
                <div class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                    <p><strong>Can:</strong> Create tickets, view projects, download invoices, request services, view quotations, and use the AI chat assistant.</p>
                    <p class="mt-1"><strong>Dashboard:</strong> Customer Portal → <code class="bg-gray-100 dark:bg-gray-800 px-1.5 rounded">/portal</code></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Section 4: Customer Portal --}}
    <div id="customer" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center text-orange-600">4</span>
            Customer Portal Guide
        </h2>

        <p class="text-gray-700 dark:text-gray-300 mb-6">The Customer Portal is your personal hub for managing all interactions with TechSupport.</p>

        <div class="space-y-4">
            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-5">
                <h4 class="font-bold text-gray-900 dark:text-white mb-2">🏠 Dashboard Overview</h4>
                <p class="text-sm text-gray-700 dark:text-gray-300">Your dashboard shows key metrics at a glance: total tickets, open tickets, active projects, outstanding invoices, and recent activity.</p>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-5">
                <h4 class="font-bold text-gray-900 dark:text-white mb-2">🎫 My Tickets</h4>
                <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">View and manage all your support tickets. You can:</p>
                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 ml-4 list-disc">
                    <li>Create new tickets with category, priority, and description</li>
                    <li>Reply to existing tickets with messages and attachments</li>
                    <li>Track ticket status (New → In Progress → Resolved → Closed)</li>
                    <li>View SLA response and resolution deadlines</li>
                </ul>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-5">
                <h4 class="font-bold text-gray-900 dark:text-white mb-2">🛠️ Request Service</h4>
                <p class="text-sm text-gray-700 dark:text-gray-300">Browse available IT services and submit a service request. Choose from categories like IT Support, Cybersecurity, Cloud Services, and more.</p>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-5">
                <h4 class="font-bold text-gray-900 dark:text-white mb-2">📋 Projects</h4>
                <p class="text-sm text-gray-700 dark:text-gray-300">Track the progress of ongoing projects. View milestones, timelines, budgets, and team members assigned to your projects.</p>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-5">
                <h4 class="font-bold text-gray-900 dark:text-white mb-2">💳 Invoices</h4>
                <p class="text-sm text-gray-700 dark:text-gray-300">View and download invoices. Check payment status, due dates, and download PDF copies for your records.</p>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-5">
                <h4 class="font-bold text-gray-900 dark:text-white mb-2">💬 AI Chat Assistant</h4>
                <p class="text-sm text-gray-700 dark:text-gray-300">Click the chat bubble in the bottom-right corner to start a conversation with our AI assistant. It can help you create tickets, answer questions, and escalate to a human agent.</p>
            </div>
        </div>
    </div>

    {{-- Section 5: Admin Panel --}}
    <div id="admin" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center text-red-600">5</span>
            Admin Panel Guide
        </h2>

        <p class="text-gray-700 dark:text-gray-300 mb-6">The Admin Panel provides comprehensive tools for managing your entire IT service operation.</p>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Section</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Description</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Roles</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr><td class="py-3 px-4 font-medium text-gray-900 dark:text-white">Dashboard</td><td class="py-3 px-4 text-gray-600 dark:text-gray-400">Overview of key metrics, recent activity, and quick actions</td><td class="py-3 px-4"><span class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">All Staff</span></td></tr>
                    <tr><td class="py-3 px-4 font-medium text-gray-900 dark:text-white">Tickets</td><td class="py-3 px-4 text-gray-600 dark:text-gray-400">Manage all support tickets, assign agents, track SLAs</td><td class="py-3 px-4"><span class="text-xs bg-blue-100 dark:bg-blue-900/30 px-2 py-1 rounded text-blue-700 dark:text-blue-400">Support</span></td></tr>
                    <tr><td class="py-3 px-4 font-medium text-gray-900 dark:text-white">Projects</td><td class="py-3 px-4 text-gray-600 dark:text-gray-400">Create and manage projects, assign tasks, track budgets</td><td class="py-3 px-4"><span class="text-xs bg-indigo-100 dark:bg-indigo-900/30 px-2 py-1 rounded text-indigo-700 dark:text-indigo-400">PM / Admin</span></td></tr>
                    <tr><td class="py-3 px-4 font-medium text-gray-900 dark:text-white">Tasks</td><td class="py-3 px-4 text-gray-600 dark:text-gray-400">Manage individual tasks, assign to freelancers, track progress</td><td class="py-3 px-4"><span class="text-xs bg-indigo-100 dark:bg-indigo-900/30 px-2 py-1 rounded text-indigo-700 dark:text-indigo-400">PM / Admin</span></td></tr>
                    <tr><td class="py-3 px-4 font-medium text-gray-900 dark:text-white">Invoices</td><td class="py-3 px-4 text-gray-600 dark:text-gray-400">Create, send, and track invoices. Generate PDFs.</td><td class="py-3 px-4"><span class="text-xs bg-green-100 dark:bg-green-900/30 px-2 py-1 rounded text-green-700 dark:text-green-400">Finance</span></td></tr>
                    <tr><td class="py-3 px-4 font-medium text-gray-900 dark:text-white">Payments</td><td class="py-3 px-4 text-gray-600 dark:text-gray-400">Record and track customer payments</td><td class="py-3 px-4"><span class="text-xs bg-green-100 dark:bg-green-900/30 px-2 py-1 rounded text-green-700 dark:text-green-400">Finance</span></td></tr>
                    <tr><td class="py-3 px-4 font-medium text-gray-900 dark:text-white">Quotations</td><td class="py-3 px-4 text-gray-600 dark:text-gray-400">Create and send quotations to customers. Convert to invoices.</td><td class="py-3 px-4"><span class="text-xs bg-green-100 dark:bg-green-900/30 px-2 py-1 rounded text-green-700 dark:text-green-400">Finance</span></td></tr>
                    <tr><td class="py-3 px-4 font-medium text-gray-900 dark:text-white">Expenses</td><td class="py-3 px-4 text-gray-600 dark:text-gray-400">Track and approve business expenses</td><td class="py-3 px-4"><span class="text-xs bg-green-100 dark:bg-green-900/30 px-2 py-1 rounded text-green-700 dark:text-green-400">Finance</span></td></tr>
                    <tr><td class="py-3 px-4 font-medium text-gray-900 dark:text-white">Commissions</td><td class="py-3 px-4 text-gray-600 dark:text-gray-400">Track freelancer commissions and process payouts</td><td class="py-3 px-4"><span class="text-xs bg-green-100 dark:bg-green-900/30 px-2 py-1 rounded text-green-700 dark:text-green-400">Finance</span></td></tr>
                    <tr><td class="py-3 px-4 font-medium text-gray-900 dark:text-white">Financials</td><td class="py-3 px-4 text-gray-600 dark:text-gray-400">Financial overview, profit/loss, transaction history, export</td><td class="py-3 px-4"><span class="text-xs bg-green-100 dark:bg-green-900/30 px-2 py-1 rounded text-green-700 dark:text-green-400">Finance</span></td></tr>
                    <tr><td class="py-3 px-4 font-medium text-gray-900 dark:text-white">Reports</td><td class="py-3 px-4 text-gray-600 dark:text-gray-400">Financial, ticket, employee, SLA, and profitability reports</td><td class="py-3 px-4"><span class="text-xs bg-green-100 dark:bg-green-900/30 px-2 py-1 rounded text-green-700 dark:text-green-400">Finance</span></td></tr>
                    <tr><td class="py-3 px-4 font-medium text-gray-900 dark:text-white">Users</td><td class="py-3 px-4 text-gray-600 dark:text-gray-400">Manage user accounts, roles, and permissions</td><td class="py-3 px-4"><span class="text-xs bg-red-100 dark:bg-red-900/30 px-2 py-1 rounded text-red-700 dark:text-red-400">Admin</span></td></tr>
                    <tr><td class="py-3 px-4 font-medium text-gray-900 dark:text-white">Services</td><td class="py-3 px-4 text-gray-600 dark:text-gray-400">Manage service offerings and categories</td><td class="py-3 px-4"><span class="text-xs bg-red-100 dark:bg-red-900/30 px-2 py-1 rounded text-red-700 dark:text-red-400">Admin</span></td></tr>
                    <tr><td class="py-3 px-4 font-medium text-gray-900 dark:text-white">Companies</td><td class="py-3 px-4 text-gray-600 dark:text-gray-400">Manage client companies and their information</td><td class="py-3 px-4"><span class="text-xs bg-red-100 dark:bg-red-900/30 px-2 py-1 rounded text-red-700 dark:text-red-400">Admin</span></td></tr>
                    <tr><td class="py-3 px-4 font-medium text-gray-900 dark:text-white">Settings</td><td class="py-3 px-4 text-gray-600 dark:text-gray-400">Platform configuration, company info, preferences</td><td class="py-3 px-4"><span class="text-xs bg-red-100 dark:bg-red-900/30 px-2 py-1 rounded text-red-700 dark:text-red-400">Admin</span></td></tr>
                    <tr><td class="py-3 px-4 font-medium text-gray-900 dark:text-white">Blog</td><td class="py-3 px-4 text-gray-600 dark:text-gray-400">Create and manage blog posts and articles</td><td class="py-3 px-4"><span class="text-xs bg-red-100 dark:bg-red-900/30 px-2 py-1 rounded text-red-700 dark:text-red-400">Admin</span></td></tr>
                    <tr><td class="py-3 px-4 font-medium text-gray-900 dark:text-white">Knowledge Base</td><td class="py-3 px-4 text-gray-600 dark:text-gray-400">Create help articles and documentation</td><td class="py-3 px-4"><span class="text-xs bg-red-100 dark:bg-red-900/30 px-2 py-1 rounded text-red-700 dark:text-red-400">Admin</span></td></tr>
                    <tr><td class="py-3 px-4 font-medium text-gray-900 dark:text-white">System Health</td><td class="py-3 px-4 text-gray-600 dark:text-gray-400">Monitor website health, page checks, error logs</td><td class="py-3 px-4"><span class="text-xs bg-red-100 dark:bg-red-900/30 px-2 py-1 rounded text-red-700 dark:text-red-400">Admin</span></td></tr>
                    <tr><td class="py-3 px-4 font-medium text-gray-900 dark:text-white">AI Assistant</td><td class="py-3 px-4 text-gray-600 dark:text-gray-400">Configure AI chatbot, view conversations, manage knowledge</td><td class="py-3 px-4"><span class="text-xs bg-red-100 dark:bg-red-900/30 px-2 py-1 rounded text-red-700 dark:text-red-400">Admin</span></td></tr>
                    <tr><td class="py-3 px-4 font-medium text-gray-900 dark:text-white">Audit Logs</td><td class="py-3 px-4 text-gray-600 dark:text-gray-400">Track all user actions and system changes</td><td class="py-3 px-4"><span class="text-xs bg-red-100 dark:bg-red-900/30 px-2 py-1 rounded text-red-700 dark:text-red-400">Admin</span></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Section 6: Ticketing System --}}
    <div id="tickets" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-xl flex items-center justify-center text-yellow-600">6</span>
            Ticketing System
        </h2>

        <p class="text-gray-700 dark:text-gray-300 mb-6">The ticketing system is the core of customer support. Every issue, request, or inquiry starts as a ticket.</p>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Ticket Lifecycle</h3>
        <div class="flex flex-wrap items-center gap-2 mb-6">
            <span class="px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-full text-sm font-medium">New</span>
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="px-3 py-1.5 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-full text-sm font-medium">In Progress</span>
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="px-3 py-1.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-sm font-medium">Resolved</span>
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-400 rounded-full text-sm font-medium">Closed</span>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Priority Levels</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
            <div class="text-center p-3 rounded-xl bg-gray-100 dark:bg-gray-800"><span class="text-sm font-bold text-gray-500">LOW</span><p class="text-xs text-gray-500 mt-1">4h response</p></div>
            <div class="text-center p-3 rounded-xl bg-blue-100 dark:bg-blue-900/20"><span class="text-sm font-bold text-blue-600">MEDIUM</span><p class="text-xs text-blue-500 mt-1">2h response</p></div>
            <div class="text-center p-3 rounded-xl bg-orange-100 dark:bg-orange-900/20"><span class="text-sm font-bold text-orange-600">HIGH</span><p class="text-xs text-orange-500 mt-1">1h response</p></div>
            <div class="text-center p-3 rounded-xl bg-red-100 dark:bg-red-900/20"><span class="text-sm font-bold text-red-600">URGENT</span><p class="text-xs text-red-500 mt-1">30min response</p></div>
            <div class="text-center p-3 rounded-xl bg-red-200 dark:bg-red-900/30"><span class="text-sm font-bold text-red-800">CRITICAL</span><p class="text-xs text-red-600 mt-1">15min response</p></div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Creating a Ticket (Customer)</h3>
        <ol class="space-y-2 text-gray-700 dark:text-gray-300 mb-6">
            <li class="flex items-start gap-2"><span class="text-primary-600 font-bold">1.</span> Go to <strong>My Tickets</strong> in the Customer Portal</li>
            <li class="flex items-start gap-2"><span class="text-primary-600 font-bold">2.</span> Click <strong>"Create Ticket"</strong></li>
            <li class="flex items-start gap-2"><span class="text-primary-600 font-bold">3.</span> Fill in Subject, Category, Priority, and Description</li>
            <li class="flex items-start gap-2"><span class="text-primary-600 font-bold">4.</span> Optionally attach files or screenshots</li>
            <li class="flex items-start gap-2"><span class="text-primary-600 font-bold">5.</span> Click <strong>"Submit"</strong></li>
        </ol>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Managing Tickets (Admin/Support)</h3>
        <ul class="space-y-2 text-gray-700 dark:text-gray-300">
            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> <strong>Assign</strong> tickets to specific agents or teams</li>
            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> <strong>Reply</strong> to customers with messages and notes</li>
            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> <strong>Update status</strong> as the ticket progresses</li>
            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> <strong>Add internal notes</strong> visible only to staff</li>
            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> <strong>Track SLA</strong> compliance and response times</li>
            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> <strong>View history</strong> of all messages and actions</li>
        </ul>
    </div>

    {{-- Section 7: Projects & Tasks --}}
    <div id="projects" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center text-indigo-600">7</span>
            Projects & Tasks
        </h2>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Project Lifecycle</h3>
        <div class="flex flex-wrap items-center gap-2 mb-6">
            <span class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-400 rounded-full text-sm font-medium">Planning</span>
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-full text-sm font-medium">In Progress</span>
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="px-3 py-1.5 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-full text-sm font-medium">Review</span>
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="px-3 py-1.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-sm font-medium">Completed</span>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Key Features</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">📊 Budget Tracking</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Set budgets, track estimated vs actual costs, and monitor profitability per project.</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">🏁 Milestones</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Break projects into phases with deadlines and completion tracking.</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">✅ Task Management</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Create tasks, assign to team members or freelancers, set priorities and deadlines.</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">📁 File Sharing</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Upload and share project files, documents, and deliverables.</p>
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">Task Types</h3>
        <ul class="space-y-2 text-gray-700 dark:text-gray-300">
            <li><strong>Assigned Tasks:</strong> Given to a specific team member or freelancer with a reward amount</li>
            <li><strong>Open Tasks:</strong> Available for freelancers to apply and compete for</li>
            <li><strong>Task Rewards:</strong> Commission amounts paid to freelancers upon task completion</li>
        </ul>
    </div>

    {{-- Section 8: Invoicing --}}
    <div id="invoicing" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-teal-100 dark:bg-teal-900/30 rounded-xl flex items-center justify-center text-teal-600">8</span>
            Invoicing & Payments
        </h2>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Invoice Statuses</h3>
        <div class="flex flex-wrap gap-3 mb-6">
            <span class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-400 rounded-full text-sm font-medium">Draft</span>
            <span class="px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-full text-sm font-medium">Sent</span>
            <span class="px-3 py-1.5 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-full text-sm font-medium">Partially Paid</span>
            <span class="px-3 py-1.5 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 rounded-full text-sm font-medium">Overdue</span>
            <span class="px-3 py-1.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-sm font-medium">Paid</span>
            <span class="px-3 py-1.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-full text-sm font-medium">Cancelled</span>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Creating an Invoice</h3>
        <ol class="space-y-2 text-gray-700 dark:text-gray-300 mb-6">
            <li>1. Go to <strong>Admin → Invoices → Create Invoice</strong></li>
            <li>2. Select the customer and add line items (description, quantity, unit price)</li>
            <li>3. Set tax rate and any discounts</li>
            <li>4. Add payment terms and notes</li>
            <li>5. Save as <strong>Draft</strong> or <strong>Send</strong> immediately</li>
        </ol>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Quotations → Invoices</h3>
        <p class="text-gray-700 dark:text-gray-300 mb-4">You can create quotations first, then convert them to invoices once the customer accepts:</p>
        <ol class="space-y-2 text-gray-700 dark:text-gray-300">
            <li>1. Create a quotation with line items</li>
            <li>2. Send it to the customer for review</li>
            <li>3. Customer accepts → Click "Convert to Invoice"</li>
            <li>4. Invoice is created automatically with all items</li>
        </ol>

        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 mt-4">
            <h4 class="font-semibold text-blue-800 dark:text-blue-400 mb-1">💡 PDF Export</h4>
            <p class="text-sm text-blue-700 dark:text-blue-300">Click the <strong>"Download PDF"</strong> button on any invoice to generate a professional PDF for your records or to send to customers.</p>
        </div>
    </div>

    {{-- Section 9: Financials --}}
    <div id="financials" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-pink-100 dark:bg-pink-900/30 rounded-xl flex items-center justify-center text-pink-600">9</span>
            Financials & Expenses
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="p-5 rounded-xl bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800">
                <h4 class="font-bold text-green-800 dark:text-green-400 mb-2">📈 Income Tracking</h4>
                <ul class="text-sm text-green-700 dark:text-green-300 space-y-1">
                    <li>• Customer payments from invoices</li>
                    <li>• Monthly retainer fees</li>
                    <li>• Service fees and project payments</li>
                    <li>• Real-time income dashboard</li>
                </ul>
            </div>
            <div class="p-5 rounded-xl bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800">
                <h4 class="font-bold text-red-800 dark:text-red-400 mb-2">📉 Expense Tracking</h4>
                <ul class="text-sm text-red-700 dark:text-red-300 space-y-1">
                    <li>• Software subscriptions</li>
                    <li>• Hardware purchases</li>
                    <li>• Contractor/freelancer payments</li>
                    <li>• Marketing and office expenses</li>
                </ul>
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Expense Categories</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800 text-center"><span class="text-2xl">💻</span><p class="text-sm font-medium text-gray-900 dark:text-white mt-1">Software</p></div>
            <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800 text-center"><span class="text-2xl">☁️</span><p class="text-sm font-medium text-gray-900 dark:text-white mt-1">Hosting</p></div>
            <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800 text-center"><span class="text-2xl">📢</span><p class="text-sm font-medium text-gray-900 dark:text-white mt-1">Marketing</p></div>
            <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800 text-center"><span class="text-2xl">🖥️</span><p class="text-sm font-medium text-gray-900 dark:text-white mt-1">Hardware</p></div>
            <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800 text-center"><span class="text-2xl">📄</span><p class="text-sm font-medium text-gray-900 dark:text-white mt-1">Office</p></div>
            <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800 text-center"><span class="text-2xl">🤝</span><p class="text-sm font-medium text-gray-900 dark:text-white mt-1">Contractor</p></div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">Financial Reports</h3>
        <ul class="space-y-2 text-gray-700 dark:text-gray-300">
            <li><strong>Profit & Loss:</strong> See income vs expenses over any time period</li>
            <li><strong>Transaction History:</strong> Detailed log of all financial transactions</li>
            <li><strong>Export:</strong> Download financial data as CSV for external analysis</li>
        </ul>
    </div>

    {{-- Section 10: Commissions --}}
    <div id="commissions" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center text-amber-600">10</span>
            Commissions & Payouts
        </h2>

        <p class="text-gray-700 dark:text-gray-300 mb-6">The commission system automatically tracks earnings for freelancers based on completed tasks.</p>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">How It Works</h3>
        <div class="space-y-3 mb-6">
            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <span class="text-xl">1️⃣</span>
                <div><strong class="text-gray-900 dark:text-white">Task Completion</strong><p class="text-sm text-gray-600 dark:text-gray-400">Freelancer completes an assigned task in a project.</p></div>
            </div>
            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <span class="text-xl">2️⃣</span>
                <div><strong class="text-gray-900 dark:text-white">Commission Calculation</strong><p class="text-sm text-gray-600 dark:text-gray-400">System calculates commission based on the rule (e.g., 10% Standard, 15% Premium).</p></div>
            </div>
            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <span class="text-xl">3️⃣</span>
                <div><strong class="text-gray-900 dark:text-white">Approval</strong><p class="text-sm text-gray-600 dark:text-gray-400">Manager reviews and approves the commission.</p></div>
            </div>
            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <span class="text-xl">4️⃣</span>
                <div><strong class="text-gray-900 dark:text-white">Payout</strong><p class="text-sm text-gray-600 dark:text-gray-400">Finance processes batch payouts via bank transfer or PayPal.</p></div>
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Commission Rules</h3>
        <ul class="space-y-2 text-gray-700 dark:text-gray-300">
            <li><strong>Standard Commission (10%):</strong> Applied to most tasks</li>
            <li><strong>Premium Commission (15%):</strong> Applied to high-value or specialized tasks</li>
        </ul>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">Commission Statuses</h3>
        <div class="flex flex-wrap gap-3">
            <span class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-400 rounded-full text-sm font-medium">Pending</span>
            <span class="px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-full text-sm font-medium">Submitted</span>
            <span class="px-3 py-1.5 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-full text-sm font-medium">Under Review</span>
            <span class="px-3 py-1.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-sm font-medium">Approved</span>
            <span class="px-3 py-1.5 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 rounded-full text-sm font-medium">Payable</span>
            <span class="px-3 py-1.5 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full text-sm font-medium">Paid</span>
        </div>
    </div>

    {{-- Section 11: Reports --}}
    <div id="reports" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-cyan-100 dark:bg-cyan-900/30 rounded-xl flex items-center justify-center text-cyan-600">11</span>
            Reports & Analytics
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-5 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-bold text-gray-900 dark:text-white mb-2">💰 Financial Report</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Revenue, expenses, profit margins, and financial trends over time.</p>
            </div>
            <div class="p-5 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-bold text-gray-900 dark:text-white mb-2">🎫 Ticket Report</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Ticket volume, resolution times, agent performance, and category breakdowns.</p>
            </div>
            <div class="p-5 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-bold text-gray-900 dark:text-white mb-2">👥 Employee Report</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Staff productivity, ticket assignments, and workload distribution.</p>
            </div>
            <div class="p-5 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-bold text-gray-900 dark:text-white mb-2">⏱️ SLA Report</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">SLA compliance rates, breach analysis, and response time metrics.</p>
            </div>
            <div class="p-5 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-bold text-gray-900 dark:text-white mb-2">📊 Profitability Report</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Per-project profitability, cost analysis, and margin tracking.</p>
            </div>
            <div class="p-5 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-bold text-gray-900 dark:text-white mb-2">📤 Data Export</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Export any report data as CSV for external analysis or accounting.</p>
            </div>
        </div>
    </div>

    {{-- Section 12: Services & KB --}}
    <div id="services" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-lime-100 dark:bg-lime-900/30 rounded-xl flex items-center justify-center text-lime-600">12</span>
            Services & Knowledge Base
        </h2>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Service Categories</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-6">
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 text-center"><span class="text-2xl">🖥️</span><p class="text-sm font-medium text-gray-900 dark:text-white mt-2">IT Support</p></div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 text-center"><span class="text-2xl">🛡️</span><p class="text-sm font-medium text-gray-900 dark:text-white mt-2">Cybersecurity</p></div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 text-center"><span class="text-2xl">☁️</span><p class="text-sm font-medium text-gray-900 dark:text-white mt-2">Cloud Services</p></div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 text-center"><span class="text-2xl">💻</span><p class="text-sm font-medium text-gray-900 dark:text-white mt-2">Software Dev</p></div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 text-center"><span class="text-2xl">🌐</span><p class="text-sm font-medium text-gray-900 dark:text-white mt-2">Network</p></div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Knowledge Base</h3>
        <p class="text-gray-700 dark:text-gray-300 mb-4">The Knowledge Base provides self-help articles organized by category:</p>
        <ul class="space-y-2 text-gray-700 dark:text-gray-300">
            <li><strong>Getting Started:</strong> Platform guides and tutorials</li>
            <li><strong>Troubleshooting:</strong> Solutions for common technical issues</li>
            <li><strong>Security:</strong> Best practices for staying secure</li>
            <li><strong>Account & Billing:</strong> Invoice and account management help</li>
        </ul>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">Public Website</h3>
        <p class="text-gray-700 dark:text-gray-300">The platform includes a public-facing website with:</p>
        <ul class="space-y-2 text-gray-700 dark:text-gray-300">
            <li>• <strong>Homepage:</strong> Company overview and featured services</li>
            <li>• <strong>Services:</strong> Detailed service descriptions and pricing</li>
            <li>• <strong>Blog:</strong> Articles, tutorials, and industry news</li>
            <li>• <strong>About:</strong> Company information and team</li>
            <li>• <strong>Pricing:</strong> Service pricing and plans</li>
            <li>• <strong>Contact:</strong> Contact form and inquiry submission</li>
        </ul>
    </div>

    {{-- Section 13: System Health --}}
    <div id="system" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-rose-100 dark:bg-rose-900/30 rounded-xl flex items-center justify-center text-rose-600">13</span>
            System Health & Settings
        </h2>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">System Health Dashboard</h3>
        <p class="text-gray-700 dark:text-gray-300 mb-4">Monitor the health and performance of your website and infrastructure:</p>
        <ul class="space-y-2 text-gray-700 dark:text-gray-300 mb-6">
            <li><strong>Page Health:</strong> Automated checks on all public pages (response time, status codes)</li>
            <li><strong>Error Logs:</strong> Frontend JavaScript errors captured in real-time</li>
            <li><strong>Check History:</strong> Historical health check data and trends</li>
            <li><strong>Maintenance Mode:</strong> Enable/disable maintenance mode with one click</li>
        </ul>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Admin Settings</h3>
        <ul class="space-y-2 text-gray-700 dark:text-gray-300">
            <li><strong>Company Info:</strong> Name, email, phone, address, website</li>
            <li><strong>General Settings:</strong> Currency, tax rate, timezone, date format</li>
            <li><strong>AI Configuration:</strong> Chatbot settings, escalation rules</li>
        </ul>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-3">Audit Logs</h3>
        <p class="text-gray-700 dark:text-gray-300">Every significant action in the system is logged for security and compliance:</p>
        <ul class="space-y-2 text-gray-700 dark:text-gray-300">
            <li>• User logins and logouts</li>
            <li>• Ticket and project modifications</li>
            <li>• Financial transaction changes</li>
            <li>• Settings updates</li>
            <li>• IP address tracking</li>
        </ul>
    </div>

    {{-- Section 14: AI Assistant --}}
    <div id="ai" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-violet-100 dark:bg-violet-900/30 rounded-xl flex items-center justify-center text-violet-600">14</span>
            AI Assistant
        </h2>

        <p class="text-gray-700 dark:text-gray-300 mb-6">The AI Chat Assistant provides instant support to customers, powered by the Knowledge Base and configured responses.</p>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">For Customers</h3>
        <ul class="space-y-2 text-gray-700 dark:text-gray-300 mb-6">
            <li>• Click the <strong>chat bubble</strong> in the bottom-right corner</li>
            <li>• Ask questions about services, support, or your account</li>
            <li>• The AI can help you <strong>create support tickets</strong> directly</li>
            <li>• If the AI can't help, it will <strong>escalate to a human agent</strong></li>
            <li>• Rate your experience after the conversation ends</li>
        </ul>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">For Admins</h3>
        <ul class="space-y-2 text-gray-700 dark:text-gray-300">
            <li>• <strong>View conversations:</strong> See all AI chat sessions and transcripts</li>
            <li>• <strong>Knowledge gaps:</strong> Identify questions the AI couldn't answer</li>
            <li>• <strong>Update knowledge:</strong> Add new articles and responses to improve AI accuracy</li>
            <li>• <strong>Usage analytics:</strong> Track AI performance and customer satisfaction</li>
            <li>• <strong>Settings:</strong> Configure escalation rules, welcome messages, and more</li>
        </ul>
    </div>

    {{-- Section 15: Tips --}}
    <div id="tips" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-gray-100 dark:bg-gray-800 rounded-xl flex items-center justify-center text-gray-600">15</span>
            Tips, Shortcuts & FAQ
        </h2>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Keyboard Shortcuts</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800">
                <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs font-mono">Ctrl + K</kbd>
                <span class="text-sm text-gray-700 dark:text-gray-300">Quick search (when available)</span>
            </div>
            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800">
                <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs font-mono">Ctrl + P</kbd>
                <span class="text-sm text-gray-700 dark:text-gray-300">Print page / Save as PDF</span>
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Theme Toggle</h3>
        <p class="text-gray-700 dark:text-gray-300 mb-6">Click the <strong>sun/moon icon</strong> in the top header to switch between Light and Dark mode. Your preference is saved automatically.</p>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Frequently Asked Questions</h3>
        <div class="space-y-4">
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">How do I change my password?</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Go to <strong>Settings</strong> in your dashboard → <strong>Security</strong> → <strong>Change Password</strong>. Enter your current password and the new one.</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">How do I enable 2FA?</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Go to <strong>Settings</strong> → <strong>Security</strong> → <strong>Enable Two-Factor Authentication</strong>. Scan the QR code with your authenticator app.</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">Can I download invoices as PDF?</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Yes! Open any invoice and click the <strong>"Download PDF"</strong> button to get a professional PDF copy.</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">How do I contact support?</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Create a ticket from the <strong>My Tickets</strong> page, or use the <strong>AI Chat Assistant</strong> for instant help. For urgent issues, create a ticket with <strong>Critical</strong> priority.</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">How do I view my commissions (Freelancers)?</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Go to <strong>Admin → Commissions</strong> to view all your commission records, approval status, and payout history.</p>
            </div>
        </div>

        <div class="mt-8 p-6 bg-gradient-to-r from-primary-50 to-blue-50 dark:from-primary-900/20 dark:to-blue-900/20 rounded-xl border border-primary-200 dark:border-primary-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">🎉 Need More Help?</h3>
            <p class="text-gray-700 dark:text-gray-300 mb-3">This manual covers all major features. For specific questions:</p>
            <ul class="space-y-1 text-gray-700 dark:text-gray-300">
                <li>• Check the <strong>Knowledge Base</strong> for detailed how-to guides</li>
                <li>• Use the <strong>AI Chat Assistant</strong> for instant answers</li>
                <li>• Create a <strong>Support Ticket</strong> for personalized help</li>
                <li>• Visit the <strong>Blog</strong> for tips, tutorials, and best practices</li>
            </ul>
        </div>
    </div>

    {{-- Footer --}}
    <div class="text-center py-8 text-sm text-gray-500 dark:text-gray-400">
        <p>TechSupport Platform — User Manual v1.0</p>
        <p class="mt-1">Last updated: August 2026 • Generated by TechSupport Admin</p>
    </div>

</div>

<style>
    @media print {
        .no-print { display: none !important; }
        body { background: white !important; }
        .dark .bg-gray-900, .dark .bg-gray-800 { background: white !important; color: #111 !important; }
        .dark .text-white, .dark .text-gray-300, .dark .text-gray-400 { color: #333 !important; }
        .dark .border-gray-800, .dark .border-gray-700 { border-color: #ddd !important; }
        .rounded-2xl, .rounded-xl { border-radius: 8px !important; }
        .shadow-sm { box-shadow: none !important; }
        main { padding: 1rem !important; }
        .bg-white { background: white !important; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }
</style>
@endsection
