<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;

class PricingController extends Controller
{
    public function index()
    {
        $services = Service::where('is_active', true)->with('category')->orderBy('sort_order')->get();
        
        $plans = [
            (object)[
                'name' => 'Essential Support',
                'description' => 'Core IT infrastructure management and proactive helpdesk support for growing businesses.',
                'price' => 499,
                'billing_cycle' => 'mo',
                'is_popular' => false,
                'features' => [
                    '24/7 Server & Workstation Monitoring',
                    'Business Hours Helpdesk Support (8x5)',
                    'Patch Management & Antivirus',
                    'Cloud Backup Management',
                    'Monthly System Health Report',
                ]
            ],
            (object)[
                'name' => 'Professional Managed IT',
                'description' => 'Comprehensive IT operations, advanced security, and dedicated engineering for standard enterprises.',
                'price' => 1299,
                'billing_cycle' => 'mo',
                'is_popular' => true,
                'features' => [
                    'All Essential Support Features',
                    '24/7/365 Helpdesk & Critical Incident Response',
                    'Advanced Endpoint Detection & Response (EDR)',
                    'Cloud Infrastructure & Microsoft 365 Admin',
                    'Quarterly Strategic IT Review & vCIO',
                    'Guaranteed 1-Hour SLA Response Time',
                ]
            ],
            (object)[
                'name' => 'Enterprise Architecture',
                'description' => 'Dedicated engineering teams, round-the-clock SOC oversight, and bespoke hybrid-cloud architecture.',
                'price' => 2899,
                'billing_cycle' => 'mo',
                'is_popular' => false,
                'features' => [
                    'All Professional Managed IT Features',
                    'Dedicated Lead Systems Engineer',
                    '24/7 Managed Security Operations Center (SOC)',
                    'Disaster Recovery & Business Continuity',
                    'Compliance Audits (SOC 2, HIPAA, ISO 27001)',
                    'Custom SLA (15-Minute Critical Response)',
                ]
            ]
        ];

        return view('public.pricing', compact('services', 'plans'));
    }
}
