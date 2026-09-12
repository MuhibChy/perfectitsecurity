@extends('layouts.app')

@section('title', 'Customer Guide — TechSupport Platform')

@section('page-title', '📖 Customer Guide')

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- Print Button --}}
    <div class="no-print mb-6 flex justify-between items-center">
        <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-xl px-4 py-3 text-sm text-orange-700 dark:text-orange-400">
            <strong>📖 Customer Guide</strong> — Everything you need to use the platform effectively
        </div>
        <button onclick="window.print()" class="bg-primary-600 hover:bg-primary-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 transition-colors shadow-lg shadow-primary-500/20">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print / Save as PDF
        </button>
    </div>

    {{-- Welcome Banner --}}
    <div class="bg-gradient-to-r from-primary-500 to-blue-600 rounded-2xl p-8 mb-8 text-white shadow-xl shadow-primary-500/20">
        <h2 class="text-3xl font-bold mb-2">Welcome to TechSupport! 👋</h2>
        <p class="text-primary-100 text-lg">This guide will help you get the most out of your customer portal. Follow the sections below to learn how to manage tickets, view projects, pay invoices, and get instant support.</p>
    </div>

    {{-- Table of Contents --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
            <span class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center text-primary-600">📋</span>
            What's Inside
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <a href="#dashboard" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center text-blue-600 font-bold text-xs">1</span>
                Your Dashboard
            </a>
            <a href="#tickets" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center text-red-600 font-bold text-xs">2</span>
                Creating & Managing Tickets
            </a>
            <a href="#projects" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center text-indigo-600 font-bold text-xs">3</span>
                Tracking Your Projects
            </a>
            <a href="#invoices" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center text-green-600 font-bold text-xs">4</span>
                Invoices & Payments
            </a>
            <a href="#services" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center text-yellow-600 font-bold text-xs">5</span>
                Requesting Services
            </a>
            <a href="#quotations" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center text-purple-600 font-bold text-xs">6</span>
                Quotations & Proposals
            </a>
            <a href="#ai-chat" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center text-teal-600 font-bold text-xs">7</span>
                AI Chat Assistant
            </a>
            <a href="#tips" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm text-gray-700 dark:text-gray-300">
                <span class="w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center text-gray-600 font-bold text-xs">8</span>
                Tips & FAQ
            </a>
        </div>
    </div>

    {{-- Section 1: Dashboard --}}
    <div id="dashboard" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center text-blue-600">1</span>
            Your Dashboard
        </h2>

        <p class="text-gray-700 dark:text-gray-300 mb-6">When you log in, you land on your personal dashboard — a snapshot of everything happening with your account.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="p-5 rounded-xl bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800">
                <h4 class="font-bold text-blue-800 dark:text-blue-400 mb-2">🎫 My Tickets</h4>
                <p class="text-sm text-blue-700 dark:text-blue-300">See all your support tickets at a glance — new, in-progress, resolved, and closed. Click any ticket to view details or reply.</p>
            </div>
            <div class="p-5 rounded-xl bg-indigo-50 dark:bg-indigo-900/10 border border-indigo-200 dark:border-indigo-800">
                <h4 class="font-bold text-indigo-800 dark:text-indigo-400 mb-2">📁 Active Projects</h4>
                <p class="text-sm text-indigo-700 dark:text-indigo-300">Track ongoing projects, view progress percentages, milestones, and deadlines.</p>
            </div>
            <div class="p-5 rounded-xl bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800">
                <h4 class="font-bold text-green-800 dark:text-green-400 mb-2">💰 Outstanding Invoices</h4>
                <p class="text-sm text-green-700 dark:text-green-300">View unpaid invoices, due dates, and download PDF copies for your records.</p>
            </div>
            <div class="p-5 rounded-xl bg-orange-50 dark:bg-orange-900/10 border border-orange-200 dark:border-orange-800">
                <h4 class="font-bold text-orange-800 dark:text-orange-400 mb-2">📊 Recent Activity</h4>
                <p class="text-sm text-orange-700 dark:text-orange-300">See the latest updates, ticket replies, project changes, and invoice activity.</p>
            </div>
        </div>

        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4">
            <p class="text-sm text-yellow-700 dark:text-yellow-300"><strong>💡 Tip:</strong> Your dashboard updates in real-time. Bookmark it for quick access to everything you need.</p>
        </div>
    </div>

    {{-- Section 2: Tickets --}}
    <div id="tickets" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center text-red-600">2</span>
            Creating & Managing Tickets
        </h2>

        <p class="text-gray-700 dark:text-gray-300 mb-6">Tickets are how you request help. Every issue, question, or request becomes a trackable ticket.</p>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">How to Create a Ticket</h3>
        <div class="space-y-3 mb-6">
            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <span class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center text-red-600 font-bold text-sm flex-shrink-0">1</span>
                <div>
                    <strong class="text-gray-900 dark:text-white">Go to My Tickets</strong>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Click "My Tickets" in the left sidebar menu.</p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <span class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center text-red-600 font-bold text-sm flex-shrink-0">2</span>
                <div>
                    <strong class="text-gray-900 dark:text-white">Click "Create Ticket"</strong>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Find the create button (usually top-right) and click it.</p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <span class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center text-red-600 font-bold text-sm flex-shrink-0">3</span>
                <div>
                    <strong class="text-gray-900 dark:text-white">Fill in the Details</strong>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Provide a clear subject, select a category, set priority, and describe the issue in detail.</p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <span class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center text-red-600 font-bold text-sm flex-shrink-0">4</span>
                <div>
                    <strong class="text-gray-900 dark:text-white">Attach Files (Optional)</strong>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Upload screenshots, error logs, or any files that help explain the problem.</p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <span class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center text-red-600 font-bold text-sm flex-shrink-0">5</span>
                <div>
                    <strong class="text-gray-900 dark:text-white">Submit!</strong>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Click "Submit" and your ticket is created. You'll receive a confirmation and can track it from your dashboard.</p>
                </div>
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Priority Levels</h3>
        <p class="text-gray-700 dark:text-gray-300 mb-3">Choose the right priority so our team responds appropriately:</p>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
            <div class="text-center p-3 rounded-xl bg-gray-100 dark:bg-gray-800">
                <span class="text-sm font-bold text-gray-500">LOW</span>
                <p class="text-xs text-gray-500 mt-1">General questions</p>
            </div>
            <div class="text-center p-3 rounded-xl bg-blue-100 dark:bg-blue-900/20">
                <span class="text-sm font-bold text-blue-600">MEDIUM</span>
                <p class="text-xs text-blue-500 mt-1">Non-urgent issues</p>
            </div>
            <div class="text-center p-3 rounded-xl bg-orange-100 dark:bg-orange-900/20">
                <span class="text-sm font-bold text-orange-600">HIGH</span>
                <p class="text-xs text-orange-500 mt-1">Affecting work</p>
            </div>
            <div class="text-center p-3 rounded-xl bg-red-100 dark:bg-red-900/20">
                <span class="text-sm font-bold text-red-600">URGENT</span>
                <p class="text-xs text-red-500 mt-1">Need quick fix</p>
            </div>
            <div class="text-center p-3 rounded-xl bg-red-200 dark:bg-red-900/30">
                <span class="text-sm font-bold text-red-800">CRITICAL</span>
                <p class="text-xs text-red-600 mt-1">System down</p>
            </div>
        </div>

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

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Replying to a Ticket</h3>
        <ul class="space-y-2 text-gray-700 dark:text-gray-300">
            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Open the ticket and scroll to the message thread</li>
            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Type your reply in the message box</li>
            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Attach files if needed (screenshots, error logs, etc.)</li>
            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Click "Send Reply" to post your message</li>
        </ul>
    </div>

    {{-- Section 3: Projects --}}
    <div id="projects" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center text-indigo-600">3</span>
            Tracking Your Projects
        </h2>

        <p class="text-gray-700 dark:text-gray-300 mb-6">If you've requested a larger service (like a website build, cloud migration, or security audit), it becomes a project you can track.</p>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">What You Can See</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">📊 Progress Bar</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">See the overall completion percentage at a glance.</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">🏁 Milestones</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">View project phases, deadlines, and which ones are completed.</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">📅 Timeline</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">See start date, deadline, and current status.</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">👥 Team</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">See who's working on your project and their roles.</p>
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Project Statuses</h3>
        <div class="flex flex-wrap gap-3">
            <span class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-400 rounded-full text-sm font-medium">Planning</span>
            <span class="px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-full text-sm font-medium">In Progress</span>
            <span class="px-3 py-1.5 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-full text-sm font-medium">Review</span>
            <span class="px-3 py-1.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-sm font-medium">Completed</span>
        </div>
    </div>

    {{-- Section 4: Invoices --}}
    <div id="invoices" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center text-green-600">4</span>
            Invoices & Payments
        </h2>

        <p class="text-gray-700 dark:text-gray-300 mb-6">View, download, and manage all your invoices from the Invoices section.</p>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Invoice Statuses</h3>
        <div class="flex flex-wrap gap-3 mb-6">
            <span class="px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-full text-sm font-medium">Sent</span>
            <span class="px-3 py-1.5 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-full text-sm font-medium">Partially Paid</span>
            <span class="px-3 py-1.5 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 rounded-full text-sm font-medium">Overdue</span>
            <span class="px-3 py-1.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-sm font-medium">Paid</span>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">How to Download an Invoice</h3>
        <ol class="space-y-2 text-gray-700 dark:text-gray-300 mb-6">
            <li>1. Go to <strong>Invoices</strong> in the sidebar</li>
            <li>2. Find the invoice you need</li>
            <li>3. Click <strong>"View"</strong> to see details</li>
            <li>4. Click <strong>"Download PDF"</strong> to save a copy</li>
        </ol>

        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
            <h4 class="font-semibold text-blue-800 dark:text-blue-400 mb-1">💳 Making Payments</h4>
            <p class="text-sm text-blue-700 dark:text-blue-300">Invoices include payment instructions. You can pay via credit card or bank transfer. Once payment is received, the invoice status updates to "Paid" automatically.</p>
        </div>
    </div>

    {{-- Section 5: Services --}}
    <div id="services" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-xl flex items-center justify-center text-yellow-600">5</span>
            Requesting Services
        </h2>

        <p class="text-gray-700 dark:text-gray-300 mb-6">Need a new service? Use the Service Request feature to browse available services and submit a request.</p>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">How to Request a Service</h3>
        <ol class="space-y-3 text-gray-700 dark:text-gray-300 mb-6">
            <li class="flex items-start gap-3">
                <span class="w-7 h-7 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center text-yellow-600 font-bold text-sm flex-shrink-0">1</span>
                <span>Click <strong>"Request Service"</strong> in the sidebar</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="w-7 h-7 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center text-yellow-600 font-bold text-sm flex-shrink-0">2</span>
                <span>Browse available services or search by category</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="w-7 h-7 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center text-yellow-600 font-bold text-sm flex-shrink-0">3</span>
                <span>Click on a service to see details, pricing, and description</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="w-7 h-7 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center text-yellow-600 font-bold text-sm flex-shrink-0">4</span>
                <span>Click <strong>"Request This Service"</strong> and fill in any additional details</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="w-7 h-7 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center text-yellow-600 font-bold text-sm flex-shrink-0">5</span>
                <span>Submit the request — our team will review and send you a quotation</span>
            </li>
        </ol>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Available Service Categories</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 text-center"><span class="text-2xl">🖥️</span><p class="text-sm font-medium text-gray-900 dark:text-white mt-2">IT Support</p></div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 text-center"><span class="text-2xl">🛡️</span><p class="text-sm font-medium text-gray-900 dark:text-white mt-2">Cybersecurity</p></div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 text-center"><span class="text-2xl">☁️</span><p class="text-sm font-medium text-gray-900 dark:text-white mt-2">Cloud Services</p></div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 text-center"><span class="text-2xl">💻</span><p class="text-sm font-medium text-gray-900 dark:text-white mt-2">Software Dev</p></div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 text-center"><span class="text-2xl">🌐</span><p class="text-sm font-medium text-gray-900 dark:text-white mt-2">Network</p></div>
        </div>
    </div>

    {{-- Section 6: Quotations --}}
    <div id="quotations" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center text-purple-600">6</span>
            Quotations & Proposals
        </h2>

        <p class="text-gray-700 dark:text-gray-300 mb-6">When you request a service, our team sends you a quotation — a detailed proposal with pricing and terms.</p>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Quotation Lifecycle</h3>
        <div class="flex flex-wrap items-center gap-2 mb-6">
            <span class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-400 rounded-full text-sm font-medium">Draft</span>
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-full text-sm font-medium">Sent</span>
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="px-3 py-1.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-sm font-medium">Accepted</span>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">What's in a Quotation?</h3>
        <ul class="space-y-2 text-gray-700 dark:text-gray-300 mb-6">
            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> <strong>Line items:</strong> Each service or deliverable with its cost</li>
            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> <strong>Subtotal & tax:</strong> Breakdown of costs</li>
            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> <strong>Total:</strong> Final amount</li>
            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> <strong>Terms:</strong> Payment terms, validity period, and conditions</li>
            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> <strong>Notes:</strong> Additional details from our team</li>
        </ul>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">How to Accept a Quotation</h3>
        <ol class="space-y-2 text-gray-700 dark:text-gray-300">
            <li>1. Go to <strong>Quotations</strong> in the sidebar</li>
            <li>2. Click on the quotation to view details</li>
            <li>3. Review all line items, terms, and pricing</li>
            <li>4. Click <strong>"Accept"</strong> to approve the quotation</li>
            <li>5. The quotation is converted to an invoice automatically</li>
        </ol>

        <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl p-4 mt-4">
            <p class="text-sm text-purple-700 dark:text-purple-300"><strong>💡 Tip:</strong> You can also reject a quotation if the pricing or scope doesn't meet your needs. Our team will follow up with alternatives.</p>
        </div>
    </div>

    {{-- Section 7: AI Chat --}}
    <div id="ai-chat" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-teal-100 dark:bg-teal-900/30 rounded-xl flex items-center justify-center text-teal-600">7</span>
            AI Chat Assistant
        </h2>

        <p class="text-gray-700 dark:text-gray-300 mb-6">Our AI assistant is available 24/7 to help you with questions, create tickets, and find information.</p>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">How to Start a Chat</h3>
        <div class="space-y-3 mb-6">
            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <span class="text-xl">1️⃣</span>
                <div><strong class="text-gray-900 dark:text-white">Click the Chat Bubble</strong><p class="text-sm text-gray-600 dark:text-gray-400">Look for the blue chat icon in the bottom-right corner of any page.</p></div>
            </div>
            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <span class="text-xl">2️⃣</span>
                <div><strong class="text-gray-900 dark:text-white">Ask Your Question</strong><p class="text-sm text-gray-600 dark:text-gray-400">Type your question naturally — the AI understands conversational language.</p></div>
            </div>
            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <span class="text-xl">3️⃣</span>
                <div><strong class="text-gray-900 dark:text-white">Get Instant Answers</strong><p class="text-sm text-gray-600 dark:text-gray-400">The AI provides helpful responses based on our knowledge base and your account info.</p></div>
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">What the AI Can Do</h3>
        <ul class="space-y-2 text-gray-700 dark:text-gray-300 mb-6">
            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Answer questions about services, pricing, and support</li>
            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Help you create support tickets directly from chat</li>
            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Provide troubleshooting steps for common issues</li>
            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Escalate to a human agent when needed</li>
            <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Find relevant knowledge base articles</li>
        </ul>

        <div class="bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-800 rounded-xl p-4">
            <p class="text-sm text-teal-700 dark:text-teal-300"><strong>💡 Tip:</strong> If the AI can't help with your specific issue, it will automatically connect you with a human support agent. You can also type "speak to agent" to escalate at any time.</p>
        </div>
    </div>

    {{-- Section 8: Tips --}}
    <div id="tips" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 mb-8 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
            <span class="w-10 h-10 bg-gray-100 dark:bg-gray-800 rounded-xl flex items-center justify-center text-gray-600">8</span>
            Tips & FAQ
        </h2>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Quick Tips</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">🌓 Dark Mode</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Click the sun/moon icon in the top header to switch themes. Your preference is saved.</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">🔔 Notifications</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">You'll be notified of ticket replies, project updates, and invoice activity.</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">📎 Attach Files</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Screenshots and error logs help our team resolve issues faster.</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">📱 Mobile Friendly</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">The portal works on phones and tablets — manage support on the go.</p>
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Frequently Asked Questions</h3>
        <div class="space-y-4">
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">How do I change my password?</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Go to <strong>Settings</strong> → <strong>Security</strong> → <strong>Change Password</strong>.</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">How do I download invoices as PDF?</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Open any invoice and click the <strong>"Download PDF"</strong> button.</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">Can I update a ticket after submitting?</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Yes! Open the ticket and click <strong>"Reply"</strong> to add more information or follow up.</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">What if I need urgent help?</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Create a ticket with <strong>Critical</strong> priority, or use the AI chat and type "speak to agent" to escalate immediately.</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">How do I check my project progress?</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Go to <strong>Projects</strong> in the sidebar. Each project shows a progress bar, milestones, and timeline.</p>
            </div>
        </div>

        <div class="mt-8 p-6 bg-gradient-to-r from-primary-50 to-blue-50 dark:from-primary-900/20 dark:to-blue-900/20 rounded-xl border border-primary-200 dark:border-primary-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">🎉 Need More Help?</h3>
            <p class="text-gray-700 dark:text-gray-300 mb-3">We're here for you! You can:</p>
            <ul class="space-y-1 text-gray-700 dark:text-gray-300">
                <li>• Use the <strong>AI Chat Assistant</strong> for instant answers</li>
                <li>• Create a <strong>Support Ticket</strong> for personalized help</li>
                <li>• Browse the <strong>Knowledge Base</strong> for how-to guides</li>
                <li>• Check the <strong>Blog</strong> for tips and tutorials</li>
            </ul>
        </div>
    </div>

    {{-- Footer --}}
    <div class="text-center py-8 text-sm text-gray-500 dark:text-gray-400">
        <p>TechSupport Platform — Customer Guide v1.0</p>
        <p class="mt-1">Last updated: August 2026</p>
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
