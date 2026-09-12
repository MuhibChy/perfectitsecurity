<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\ServiceCategory;
use App\Models\Service;
use App\Models\ServiceCountryPrice;

class ServiceCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        // ─── COUNTRIES ───────────────────────────────────────────────────────────
        $countries = [
            Country::create(['name' => 'United Kingdom', 'code' => 'UK', 'currency_code' => 'GBP', 'currency_symbol' => '£', 'currency_name' => 'British Pound', 'sort_order' => 1]),
            Country::create(['name' => 'United States', 'code' => 'US', 'currency_code' => 'USD', 'currency_symbol' => '$', 'currency_name' => 'US Dollar', 'sort_order' => 2]),
            Country::create(['name' => 'Bangladesh', 'code' => 'BD', 'currency_code' => 'BDT', 'currency_symbol' => '৳', 'currency_name' => 'Bangladeshi Taka', 'sort_order' => 3]),
        ];
        $uk = $countries[0]->id;
        $us = $countries[1]->id;
        $bd = $countries[2]->id;

        // ─── CATEGORIES ──────────────────────────────────────────────────────────
        $cats = [];
        $catData = [
            ['name' => 'Cybersecurity Services', 'slug' => 'cybersecurity', 'description' => 'Professional cybersecurity assessment, testing, and hardening services to protect your digital infrastructure.', 'icon' => '🛡️', 'color' => '#DC2626', 'sort_order' => 1],
            ['name' => 'Web Development & Design', 'slug' => 'web-development', 'description' => 'Custom website and web application development, from business sites to SaaS platforms and e-commerce solutions.', 'icon' => '🌐', 'color' => '#2563EB', 'sort_order' => 2],
            ['name' => 'Digital Marketing', 'slug' => 'digital-marketing', 'description' => 'SEO, social media, paid advertising, and marketing strategy services to grow your online presence.', 'icon' => '📈', 'color' => '#059669', 'sort_order' => 3],
            ['name' => 'Managed IT Support', 'slug' => 'managed-it-support', 'description' => 'Comprehensive IT support services including remote, on-site, helpdesk, and managed infrastructure.', 'icon' => '💻', 'color' => '#7C3AED', 'sort_order' => 4],
            ['name' => 'Cloud, Server & Network Services', 'slug' => 'cloud-server-network', 'description' => 'Cloud migration, server administration, network infrastructure, and monitoring services.', 'icon' => '☁️', 'color' => '#0891B2', 'sort_order' => 5],
            ['name' => 'Business Technology & Automation', 'slug' => 'business-technology', 'description' => 'CRM, ERP, workflow automation, AI integration, and business process optimization.', 'icon' => '⚙️', 'color' => '#D97706', 'sort_order' => 6],
        ];

        foreach ($catData as $cd) {
            $cats[$cd['slug']] = ServiceCategory::create($cd);
        }

        // ─── HELPER: Create service with country prices ──────────────────────────
        $createService = function (array $data, array $prices) use ($cats, $uk, $us, $bd) {
            $cat = $cats[$data['category']];
            unset($data['category']);

            if (!isset($data['slug'])) {
                $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
            }
            $data['category_id'] = $cat->id;

            // Set default pricing from first country
            $data['price_type'] = $prices[0]['pricing_type'] ?? 'custom';
            $data['starting_price'] = $prices[0]['price'] ?? 0;

            $service = Service::create($data);

            // Create country-specific prices
            $countryPrices = [
                ['country_id' => $uk, 'pricing_type' => $prices[0]['pricing_type'], 'price' => $prices[0]['price']],
                ['country_id' => $us, 'pricing_type' => $prices[1]['pricing_type'], 'price' => $prices[1]['price']],
                ['country_id' => $bd, 'pricing_type' => $prices[2]['pricing_type'], 'price' => $prices[2]['price']],
            ];

            foreach ($countryPrices as $cp) {
                ServiceCountryPrice::create(array_merge($cp, ['service_id' => $service->id, 'is_active' => true]));
            }

            return $service;
        };

        // ══════════════════════════════════════════════════════════════════════════
        //  CATEGORY 1: CYBERSECURITY SERVICES (~30)
        // ══════════════════════════════════════════════════════════════════════════

        $createService([
            'name' => 'Vulnerability Assessment',
            'category' => 'cybersecurity',
            'short_description' => 'Identify security vulnerabilities across your IT infrastructure with automated and manual assessment techniques.',
            'description' => 'Our vulnerability assessment service provides a comprehensive scan of your IT environment to identify known vulnerabilities, misconfigurations, and security weaknesses. We use industry-standard tools combined with manual verification to deliver actionable results.',
            'complexity_level' => 'standard',
            'estimated_completion' => '5-7 business days',
            'allows_custom_quote' => true,
            'is_featured' => true,
            'deliverables' => ['Vulnerability scan report', 'Risk prioritization matrix', 'Remediation recommendations', 'Executive summary'],
            'features' => ['Automated scanning', 'Manual verification', 'Risk scoring (CVSS)', 'Remediation guidance'],
            'tags' => ['security', 'vulnerability', 'assessment', 'scan'],
        ], [
            ['pricing_type' => 'fixed', 'price' => 750],
            ['pricing_type' => 'fixed', 'price' => 950],
            ['pricing_type' => 'fixed', 'price' => 45000],
        ]);

        $createService([
            'name' => 'Web Application Security Assessment',
            'category' => 'cybersecurity',
            'short_description' => 'In-depth security testing of web applications against OWASP Top 10 and modern attack vectors.',
            'description' => 'Comprehensive security assessment of your web applications covering authentication, authorization, input validation, session management, and business logic flaws. We test against the OWASP Top 10 and emerging threats.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '7-14 business days',
            'allows_custom_quote' => true,
            'is_featured' => true,
            'deliverables' => ['Detailed vulnerability report', 'Proof of concept for each finding', 'Risk ratings', 'Remediation roadmap'],
            'features' => ['OWASP Top 10 testing', 'Business logic testing', 'Authentication testing', 'API testing included'],
            'tags' => ['web security', 'owasp', 'penetration testing', 'web app'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 1500],
            ['pricing_type' => 'starting_from', 'price' => 1900],
            ['pricing_type' => 'starting_from', 'price' => 95000],
        ]);

        $createService([
            'name' => 'API Security Testing',
            'category' => 'cybersecurity',
            'short_description' => 'Specialized security testing for REST, GraphQL, and SOAP APIs to identify injection, authentication, and data exposure risks.',
            'description' => 'Our API security testing covers authentication bypass, injection attacks, broken object-level authorization (BOLA), rate limiting, and data exposure. We test REST, GraphQL, and SOAP endpoints.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '5-10 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['API security report', 'Endpoint-by-endpoint analysis', 'Risk prioritization', 'Fix recommendations'],
            'features' => ['REST/GraphQL/SOAP testing', 'BOLA/IDOR detection', 'Authentication bypass testing', 'Rate limit analysis'],
            'tags' => ['api', 'security', 'rest', 'graphql'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 1200],
            ['pricing_type' => 'starting_from', 'price' => 1500],
            ['pricing_type' => 'starting_from', 'price' => 75000],
        ]);

        $createService([
            'name' => 'Mobile Application Security Testing',
            'category' => 'cybersecurity',
            'short_description' => 'Security assessment of iOS and Android mobile applications covering data storage, network, and platform-specific risks.',
            'description' => 'Comprehensive mobile application security testing for iOS and Android platforms. We evaluate data storage security, network communication, platform-specific controls, and authentication mechanisms.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '7-14 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Mobile app security report', 'Platform-specific findings', 'Remediation guide', 'Executive summary'],
            'features' => ['iOS and Android testing', 'Data storage analysis', 'Network security review', 'Certificate pinning check'],
            'tags' => ['mobile', 'ios', 'android', 'security'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 1800],
            ['pricing_type' => 'starting_from', 'price' => 2200],
            ['pricing_type' => 'starting_from', 'price' => 110000],
        ]);

        $createService([
            'name' => 'Network Security Assessment',
            'category' => 'cybersecurity',
            'short_description' => 'Evaluate your network infrastructure for vulnerabilities, misconfigurations, and unauthorized access points.',
            'description' => 'Our network security assessment covers external and internal network scanning, firewall rule analysis, wireless network security, and segmentation testing. We identify exposed services, weak credentials, and lateral movement risks.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '7-10 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Network topology map', 'Vulnerability report', 'Risk assessment', 'Hardening recommendations'],
            'features' => ['External and internal scanning', 'Firewall rule review', 'Wireless security testing', 'Segmentation analysis'],
            'tags' => ['network', 'security', 'infrastructure', 'firewall'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 2000],
            ['pricing_type' => 'starting_from', 'price' => 2500],
            ['pricing_type' => 'starting_from', 'price' => 125000],
        ]);

        $createService([
            'name' => 'External Security Assessment',
            'category' => 'cybersecurity',
            'short_description' => 'Test your external-facing infrastructure from the perspective of an external attacker.',
            'description' => 'We simulate an external attacker to identify vulnerabilities in your public-facing infrastructure, including web servers, email servers, VPN endpoints, and DNS configurations.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '5-10 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['External assessment report', 'Attack surface mapping', 'Risk ratings', 'Remediation plan'],
            'features' => ['Black-box testing', 'Attack surface discovery', 'Service enumeration', 'Certificate analysis'],
            'tags' => ['external', 'perimeter', 'security', 'infrastructure'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 1500],
            ['pricing_type' => 'starting_from', 'price' => 1900],
            ['pricing_type' => 'starting_from', 'price' => 90000],
        ]);

        $createService([
            'name' => 'Internal Security Assessment',
            'category' => 'cybersecurity',
            'short_description' => 'Evaluate internal network security, Active Directory configurations, and privilege escalation paths.',
            'description' => 'Internal security assessment covers Active Directory review, privilege escalation testing, lateral movement analysis, and internal network vulnerability scanning.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '7-14 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Internal assessment report', 'AD configuration review', 'Privilege escalation paths', 'Remediation guide'],
            'features' => ['Active Directory audit', 'Privilege escalation testing', 'Lateral movement analysis', 'Internal scanning'],
            'tags' => ['internal', 'active directory', 'privilege escalation'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 2500],
            ['pricing_type' => 'starting_from', 'price' => 3000],
            ['pricing_type' => 'starting_from', 'price' => 150000],
        ]);

        $createService([
            'name' => 'Penetration Testing',
            'category' => 'cybersecurity',
            'short_description' => 'Full-scope penetration testing simulating real-world attacks across your infrastructure, applications, and social engineering.',
            'description' => 'Our penetration testing service provides a comprehensive simulated attack on your infrastructure, web applications, mobile applications, and optionally social engineering. We follow PTES and OSSTMM methodologies.',
            'complexity_level' => 'enterprise',
            'estimated_completion' => '10-21 business days',
            'allows_custom_quote' => true,
            'is_featured' => true,
            'deliverables' => ['Comprehensive pentest report', 'Attack narratives', 'Proof of concept exploits', 'Risk prioritization', 'Executive briefing'],
            'features' => ['Full-scope testing', 'Infrastructure + application', 'Social engineering option', 'Remediation support'],
            'tags' => ['penetration testing', 'full scope', 'attack simulation'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 4000],
            ['pricing_type' => 'starting_from', 'price' => 5000],
            ['pricing_type' => 'starting_from', 'price' => 250000],
        ]);

        $createService([
            'name' => 'Infrastructure Security Review',
            'category' => 'cybersecurity',
            'short_description' => 'Comprehensive review of server, network, and cloud infrastructure security configurations.',
            'description' => 'We conduct a thorough review of your infrastructure security including server configurations, network devices, cloud configurations, and security controls against CIS benchmarks and industry best practices.',
            'complexity_level' => 'standard',
            'estimated_completion' => '5-10 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Infrastructure review report', 'Configuration findings', 'Benchmark comparison', 'Hardening guide'],
            'features' => ['CIS benchmark comparison', 'Cloud config review', 'Server hardening review', 'Network device audit'],
            'tags' => ['infrastructure', 'review', 'configuration', 'hardening'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 1800],
            ['pricing_type' => 'starting_from', 'price' => 2200],
            ['pricing_type' => 'starting_from', 'price' => 100000],
        ]);

        $createService([
            'name' => 'Cloud Security Assessment',
            'category' => 'cybersecurity',
            'short_description' => 'Security review of AWS, Azure, and GCP environments including IAM, networking, and data protection.',
            'description' => 'Comprehensive cloud security assessment covering identity and access management, network security groups, storage configurations, logging, and compliance with cloud security best practices.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '7-14 business days',
            'allows_custom_quote' => true,
            'is_featured' => true,
            'deliverables' => ['Cloud security report', 'IAM review', 'Configuration findings', 'Remediation roadmap'],
            'features' => ['AWS/Azure/GCP support', 'IAM analysis', 'Network security review', 'Compliance mapping'],
            'tags' => ['cloud', 'aws', 'azure', 'gcp', 'security'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 2500],
            ['pricing_type' => 'starting_from', 'price' => 3000],
            ['pricing_type' => 'starting_from', 'price' => 140000],
        ]);

        $createService([
            'name' => 'Security Configuration Review',
            'category' => 'cybersecurity',
            'short_description' => 'Audit security configurations across servers, firewalls, and applications against best practices.',
            'description' => 'We review security configurations of your critical systems including firewalls, routers, servers, databases, and applications against vendor recommendations and CIS benchmarks.',
            'complexity_level' => 'standard',
            'estimated_completion' => '3-7 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Configuration review report', 'Finding summary', 'Remediation steps'],
            'features' => ['Firewall config review', 'Server configuration audit', 'Database security check', 'Application config review'],
            'tags' => ['configuration', 'review', 'hardening', 'best practices'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 800],
            ['pricing_type' => 'starting_from', 'price' => 1000],
            ['pricing_type' => 'starting_from', 'price' => 50000],
        ]);

        $createService([
            'name' => 'Security Hardening',
            'category' => 'cybersecurity',
            'short_description' => 'Implement security hardening measures across servers, endpoints, and network devices.',
            'description' => 'Our security hardening service implements industry-standard hardening configurations across your infrastructure including OS hardening, service configuration, and security control implementation.',
            'complexity_level' => 'standard',
            'estimated_completion' => '5-10 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Hardening implementation', 'Configuration documentation', 'Verification report'],
            'features' => ['OS hardening', 'Service configuration', 'Access control', 'Logging setup'],
            'tags' => ['hardening', 'security', 'configuration'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 1000],
            ['pricing_type' => 'starting_from', 'price' => 1200],
            ['pricing_type' => 'starting_from', 'price' => 60000],
        ]);

        $createService([
            'name' => 'Security Monitoring Setup',
            'category' => 'cybersecurity',
            'short_description' => 'Deploy and configure security monitoring, SIEM, and intrusion detection systems.',
            'description' => 'We design and implement security monitoring solutions including SIEM deployment, log aggregation, alert rules, and intrusion detection/prevention systems.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '7-14 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Monitoring architecture', 'SIEM configuration', 'Alert rule set', 'Runbook documentation'],
            'features' => ['SIEM deployment', 'Log aggregation', 'Alert configuration', 'IDS/IPS setup'],
            'tags' => ['monitoring', 'siem', 'ids', 'ips', 'detection'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 3000],
            ['pricing_type' => 'starting_from', 'price' => 3800],
            ['pricing_type' => 'starting_from', 'price' => 175000],
        ]);

        $createService([
            'name' => 'Incident Response Support',
            'category' => 'cybersecurity',
            'short_description' => 'Rapid response to security incidents including containment, investigation, and recovery.',
            'description' => 'Our incident response team provides rapid response to security breaches, malware infections, unauthorized access, and data exposure incidents. We follow NIST SP 800-61 guidelines.',
            'complexity_level' => 'enterprise',
            'estimated_completion' => 'Varies by incident',
            'allows_custom_quote' => true,
            'deliverables' => ['Incident report', 'Timeline analysis', 'Root cause analysis', 'Recovery plan', 'Lessons learned'],
            'features' => ['24/7 rapid response', 'Forensic investigation', 'Containment strategy', 'Recovery support'],
            'tags' => ['incident response', 'forensics', 'breach', 'recovery'],
        ], [
            ['pricing_type' => 'hourly', 'price' => 200],
            ['pricing_type' => 'hourly', 'price' => 250],
            ['pricing_type' => 'hourly', 'price' => 12000],
        ]);

        $createService([
            'name' => 'Malware Incident Investigation',
            'category' => 'cybersecurity',
            'short_description' => 'Investigate malware infections, identify scope, remove threats, and prevent recurrence.',
            'description' => 'We investigate malware incidents to determine the infection vector, scope of compromise, data exposure, and provide complete remediation including malware removal and system restoration.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '3-10 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Malware analysis report', 'Scope determination', 'Remediation plan', 'Prevention recommendations'],
            'features' => ['Malware analysis', 'Root cause identification', 'System restoration', 'Prevention strategies'],
            'tags' => ['malware', 'investigation', 'remediation', 'forensics'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 1500],
            ['pricing_type' => 'starting_from', 'price' => 1900],
            ['pricing_type' => 'starting_from', 'price' => 90000],
        ]);

        $createService([
            'name' => 'Security Awareness Training',
            'category' => 'cybersecurity',
            'short_description' => 'Interactive security awareness training programs for employees at all levels.',
            'description' => 'Comprehensive security awareness training covering phishing recognition, password security, social engineering, data protection, and security best practices. Available as live sessions or e-learning modules.',
            'complexity_level' => 'basic',
            'estimated_completion' => '1-5 business days',
            'allows_custom_quote' => false,
            'deliverables' => ['Training materials', 'Assessment quizzes', 'Certificate of completion', 'Training report'],
            'features' => ['Interactive sessions', 'Real-world scenarios', 'Phishing awareness', 'Assessment included'],
            'tags' => ['training', 'awareness', 'phishing', 'employees'],
        ], [
            ['pricing_type' => 'fixed', 'price' => 500],
            ['pricing_type' => 'fixed', 'price' => 650],
            ['pricing_type' => 'fixed', 'price' => 35000],
        ]);

        $createService([
            'name' => 'Phishing Simulation Program',
            'category' => 'cybersecurity',
            'short_description' => 'Run realistic phishing simulations to test and improve employee security awareness.',
            'description' => 'We design and execute phishing simulation campaigns with realistic scenarios tailored to your industry. Includes detailed reporting on click rates, reporting rates, and training recommendations.',
            'complexity_level' => 'standard',
            'estimated_completion' => '2-3 weeks (campaign)',
            'allows_custom_quote' => true,
            'deliverables' => ['Campaign setup', 'Simulation reports', 'User risk assessment', 'Training recommendations'],
            'features' => ['Custom phishing scenarios', 'Campaign management', 'Detailed analytics', 'Follow-up training'],
            'tags' => ['phishing', 'simulation', 'training', 'awareness'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 800],
            ['pricing_type' => 'starting_from', 'price' => 1000],
            ['pricing_type' => 'starting_from', 'price' => 50000],
        ]);

        $createService([
            'name' => 'Risk Assessment',
            'category' => 'cybersecurity',
            'short_description' => 'Comprehensive IT risk assessment to identify, analyze, and prioritize security risks.',
            'description' => 'Our risk assessment service identifies and evaluates security risks across your organization. We use industry frameworks like ISO 27005 and NIST RMF to provide a structured risk analysis.',
            'complexity_level' => 'standard',
            'estimated_completion' => '5-10 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Risk register', 'Risk assessment report', 'Risk treatment plan', 'Executive summary'],
            'features' => ['Risk identification', 'Impact analysis', 'Risk scoring', 'Treatment recommendations'],
            'tags' => ['risk', 'assessment', 'iso 27005', 'nist'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 1500],
            ['pricing_type' => 'starting_from', 'price' => 1900],
            ['pricing_type' => 'starting_from', 'price' => 85000],
        ]);

        $createService([
            'name' => 'Cybersecurity Consulting',
            'category' => 'cybersecurity',
            'short_description' => 'Expert cybersecurity consulting for strategy, architecture, and security program development.',
            'description' => 'Our cybersecurity consultants provide strategic guidance on security architecture, program development, technology selection, and regulatory compliance.',
            'complexity_level' => 'enterprise',
            'estimated_completion' => 'Ongoing',
            'allows_custom_quote' => true,
            'is_featured' => true,
            'deliverables' => ['Consulting report', 'Security strategy', 'Technology recommendations', 'Implementation roadmap'],
            'features' => ['Strategic consulting', 'Architecture review', 'Technology selection', 'Program development'],
            'tags' => ['consulting', 'strategy', 'architecture', 'advisory'],
        ], [
            ['pricing_type' => 'hourly', 'price' => 175],
            ['pricing_type' => 'hourly', 'price' => 220],
            ['pricing_type' => 'hourly', 'price' => 10000],
        ]);

        $createService([
            'name' => 'Security Policy Development',
            'category' => 'cybersecurity',
            'short_description' => 'Develop comprehensive information security policies, procedures, and standards.',
            'description' => 'We develop tailored information security policies aligned with ISO 27001, NIST, and industry best practices. Includes acceptable use policies, data classification, incident response procedures, and more.',
            'complexity_level' => 'standard',
            'estimated_completion' => '7-14 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Security policy suite', 'Procedures documentation', 'Standards guide', 'Implementation guide'],
            'features' => ['ISO 27001 aligned', 'Custom policies', 'Procedure development', 'Review and updates'],
            'tags' => ['policy', 'iso 27001', 'compliance', 'governance'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 2000],
            ['pricing_type' => 'starting_from', 'price' => 2500],
            ['pricing_type' => 'starting_from', 'price' => 110000],
        ]);

        $createService([
            'name' => 'Compliance Readiness Support',
            'category' => 'cybersecurity',
            'short_description' => 'Prepare your organization for compliance with GDPR, ISO 27001, Cyber Essentials, and other frameworks.',
            'description' => 'We help organizations prepare for compliance with GDPR, ISO 27001, Cyber Essentials, PCI DSS, and other regulatory frameworks through gap analysis, remediation support, and documentation.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '2-8 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Gap analysis report', 'Remediation plan', 'Documentation templates', 'Compliance roadmap'],
            'features' => ['Framework gap analysis', 'Remediation support', 'Documentation review', 'Pre-assessment'],
            'tags' => ['compliance', 'gdpr', 'iso 27001', 'cyber essentials'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 2500],
            ['pricing_type' => 'starting_from', 'price' => 3000],
            ['pricing_type' => 'starting_from', 'price' => 140000],
        ]);

        $createService([
            'name' => 'OWASP Security Review',
            'category' => 'cybersecurity',
            'short_description' => 'Security review of web applications based on OWASP Top 10 and ASVS standards.',
            'description' => 'Targeted security review covering the OWASP Top 10 vulnerabilities and Application Security Verification Standard (ASVS) requirements for web applications.',
            'complexity_level' => 'standard',
            'estimated_completion' => '5-7 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['OWASP assessment report', 'Finding details', 'Fix recommendations'],
            'features' => ['OWASP Top 10 coverage', 'ASVS verification', 'Remediation guidance'],
            'tags' => ['owasp', 'web security', 'application security'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 1200],
            ['pricing_type' => 'starting_from', 'price' => 1500],
            ['pricing_type' => 'starting_from', 'price' => 70000],
        ]);

        $createService([
            'name' => 'WordPress Security Audit',
            'category' => 'cybersecurity',
            'short_description' => 'Security audit specifically tailored for WordPress websites including plugins, themes, and hosting.',
            'description' => 'Comprehensive security audit for WordPress installations covering core files, plugins, themes, user accounts, hosting configuration, and security headers.',
            'complexity_level' => 'basic',
            'estimated_completion' => '2-4 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['WordPress security report', 'Plugin/theme audit', 'Hardening checklist', 'Fix recommendations'],
            'features' => ['Core file integrity check', 'Plugin vulnerability scan', 'Theme security review', 'Security header analysis'],
            'tags' => ['wordpress', 'cms', 'security audit'],
        ], [
            ['pricing_type' => 'fixed', 'price' => 350],
            ['pricing_type' => 'fixed', 'price' => 450],
            ['pricing_type' => 'fixed', 'price' => 22000],
        ]);

        $createService([
            'name' => 'Website Malware Cleanup',
            'category' => 'cybersecurity',
            'short_description' => 'Remove malware, backdoors, and malicious code from compromised websites.',
            'description' => 'Complete malware removal service including identification of malicious code, backdoor removal, database cleanup, file restoration, and security hardening to prevent reinfection.',
            'complexity_level' => 'standard',
            'estimated_completion' => '1-3 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Malware removal', 'Cleanup report', 'Security hardening', 'Monitoring setup'],
            'features' => ['Malware identification', 'Backdoor removal', 'Database cleanup', 'Reinfection prevention'],
            'tags' => ['malware', 'cleanup', 'wordpress', 'website'],
        ], [
            ['pricing_type' => 'fixed', 'price' => 500],
            ['pricing_type' => 'fixed', 'price' => 650],
            ['pricing_type' => 'fixed', 'price' => 30000],
        ]);

        $createService([
            'name' => 'Security Patch Management',
            'category' => 'cybersecurity',
            'short_description' => 'Identify, test, and apply security patches across your IT infrastructure.',
            'description' => 'We manage the complete patch lifecycle from vulnerability identification through testing to deployment, ensuring your systems are protected against known vulnerabilities.',
            'complexity_level' => 'standard',
            'estimated_completion' => 'Ongoing service',
            'allows_custom_quote' => true,
            'deliverables' => ['Patch assessment report', 'Deployment plan', 'Testing results', 'Compliance report'],
            'features' => ['Vulnerability tracking', 'Patch testing', 'Scheduled deployment', 'Rollback capability'],
            'tags' => ['patch management', 'updates', 'vulnerability', 'maintenance'],
        ], [
            ['pricing_type' => 'monthly', 'price' => 400],
            ['pricing_type' => 'monthly', 'price' => 500],
            ['pricing_type' => 'monthly', 'price' => 25000],
        ]);

        $createService([
            'name' => 'Identity and Access Review',
            'category' => 'cybersecurity',
            'short_description' => 'Review and optimize identity management, access controls, and authentication mechanisms.',
            'description' => 'Comprehensive review of identity and access management including user account inventory, access rights analysis, privileged account review, and authentication mechanism assessment.',
            'complexity_level' => 'standard',
            'estimated_completion' => '5-7 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['IAM review report', 'Access rights matrix', 'Privileged account audit', 'Remediation plan'],
            'features' => ['User access review', 'Privileged account audit', 'Authentication analysis', 'Access control optimization'],
            'tags' => ['identity', 'access', 'iam', 'authentication'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 1000],
            ['pricing_type' => 'starting_from', 'price' => 1300],
            ['pricing_type' => 'starting_from', 'price' => 60000],
        ]);

        $createService([
            'name' => 'Backup and Disaster Recovery Review',
            'category' => 'cybersecurity',
            'short_description' => 'Assess backup strategies and disaster recovery readiness for critical IT systems.',
            'description' => 'We review your backup infrastructure, disaster recovery plans, and business continuity procedures to ensure resilience against data loss and system failures.',
            'complexity_level' => 'standard',
            'estimated_completion' => '3-7 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['DR assessment report', 'Backup strategy review', 'Recovery time analysis', 'Improvement plan'],
            'features' => ['Backup validation', 'DR plan review', 'RTO/RPO analysis', 'Testing recommendations'],
            'tags' => ['backup', 'disaster recovery', 'business continuity'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 800],
            ['pricing_type' => 'starting_from', 'price' => 1000],
            ['pricing_type' => 'starting_from', 'price' => 50000],
        ]);

        $createService([
            'name' => 'Email Security Configuration',
            'category' => 'cybersecurity',
            'short_description' => 'Configure email security including SPF, DKIM, DMARC, and anti-phishing protection.',
            'description' => 'We configure and optimize email security measures including SPF records, DKIM signing, DMARC policies, email filtering, and anti-phishing protection to prevent email-based attacks.',
            'complexity_level' => 'basic',
            'estimated_completion' => '1-3 business days',
            'allows_custom_quote' => false,
            'deliverables' => ['Email security configuration', 'DNS record setup', 'DMARC report', 'Testing results'],
            'features' => ['SPF/DKIM/DMARC setup', 'Email filtering config', 'Anti-phishing rules', 'Deliverability optimization'],
            'tags' => ['email', 'security', 'spf', 'dkim', 'dmarc'],
        ], [
            ['pricing_type' => 'fixed', 'price' => 350],
            ['pricing_type' => 'fixed', 'price' => 450],
            ['pricing_type' => 'fixed', 'price' => 20000],
        ]);

        $createService([
            'name' => 'Endpoint Security Support',
            'category' => 'cybersecurity',
            'short_description' => 'Deploy, configure, and manage endpoint protection across workstations and laptops.',
            'description' => 'We deploy and configure endpoint security solutions including antivirus, EDR, host-based firewalls, and device encryption across your fleet of workstations and laptops.',
            'complexity_level' => 'standard',
            'estimated_completion' => '3-7 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Endpoint security deployment', 'Configuration documentation', 'Policy setup', 'Deployment report'],
            'features' => ['EDR deployment', 'Policy configuration', 'Centralized management', 'Threat detection setup'],
            'tags' => ['endpoint', 'edr', 'antivirus', 'device security'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 1000],
            ['pricing_type' => 'starting_from', 'price' => 1200],
            ['pricing_type' => 'starting_from', 'price' => 55000],
        ]);

        $createService([
            'name' => 'Firewall Security Review',
            'category' => 'cybersecurity',
            'short_description' => 'Audit firewall rules, configurations, and policies for optimal security posture.',
            'description' => 'Comprehensive review of firewall rules, access control lists, NAT configurations, and logging to ensure optimal security with minimal unnecessary exposure.',
            'complexity_level' => 'standard',
            'estimated_completion' => '3-5 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Firewall audit report', 'Rule optimization', 'Unused rule identification', 'Hardening guide'],
            'features' => ['Rule analysis', 'Shadow rule detection', 'Unused rule cleanup', 'Configuration hardening'],
            'tags' => ['firewall', 'network security', 'rules', 'configuration'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 600],
            ['pricing_type' => 'starting_from', 'price' => 750],
            ['pricing_type' => 'starting_from', 'price' => 35000],
        ]);

        // ══════════════════════════════════════════════════════════════════════════
        //  CATEGORY 2: WEB DEVELOPMENT & DESIGN (~30)
        // ══════════════════════════════════════════════════════════════════════════

        $createService([
            'name' => 'Business Website Development',
            'category' => 'web-development',
            'short_description' => 'Professional business websites designed to establish credibility and drive conversions.',
            'description' => 'Custom business website development with responsive design, SEO optimization, and conversion-focused layouts. Perfect for establishing a professional online presence.',
            'complexity_level' => 'standard',
            'estimated_completion' => '3-6 weeks',
            'allows_custom_quote' => true,
            'is_featured' => true,
            'deliverables' => ['Custom responsive design', 'Content management system', 'Contact forms', 'SEO setup', 'Analytics integration'],
            'features' => ['Responsive design', 'CMS integration', 'SEO optimized', 'Fast loading'],
            'tags' => ['business', 'website', 'responsive', 'cms'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 800],
            ['pricing_type' => 'starting_from', 'price' => 1000],
            ['pricing_type' => 'starting_from', 'price' => 50000],
        ]);

        $createService([
            'name' => 'Corporate Website Development',
            'category' => 'web-development',
            'short_description' => 'Enterprise-grade corporate websites with multi-department support and advanced functionality.',
            'description' => 'Large-scale corporate website development with multiple page templates, investor relations sections, careers portals, and enterprise CMS integration.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '6-12 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Custom corporate design', 'Multi-page architecture', 'CMS setup', 'Document management', 'Multilingual support'],
            'features' => ['Enterprise architecture', 'Multi-department support', 'Document management', 'Multilingual ready'],
            'tags' => ['corporate', 'enterprise', 'business', 'multi-page'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 3000],
            ['pricing_type' => 'starting_from', 'price' => 3800],
            ['pricing_type' => 'starting_from', 'price' => 175000],
        ]);

        $createService([
            'name' => 'Landing Page Development',
            'category' => 'web-development',
            'short_description' => 'High-converting landing pages for campaigns, products, and lead generation.',
            'description' => 'Custom landing page design and development optimized for conversions. Includes A/B testing setup, analytics tracking, and responsive design for all devices.',
            'complexity_level' => 'basic',
            'estimated_completion' => '3-7 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Custom landing page', 'Mobile responsive', 'Analytics setup', 'A/B testing ready'],
            'features' => ['Conversion optimized', 'Mobile first', 'Fast loading', 'A/B test ready'],
            'tags' => ['landing page', 'conversion', 'lead generation'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 400],
            ['pricing_type' => 'starting_from', 'price' => 500],
            ['pricing_type' => 'starting_from', 'price' => 25000],
        ]);

        $createService([
            'name' => 'E-commerce Website Development',
            'category' => 'web-development',
            'short_description' => 'Full-featured online stores with product management, payments, and inventory control.',
            'description' => 'Complete e-commerce solution with product catalog, shopping cart, secure checkout, payment gateway integration, inventory management, and order tracking.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '6-12 weeks',
            'allows_custom_quote' => true,
            'is_featured' => true,
            'deliverables' => ['Custom e-commerce design', 'Product management', 'Payment integration', 'Inventory system', 'Order management'],
            'features' => ['Multi-payment support', 'Inventory tracking', 'Order management', 'SEO optimized'],
            'tags' => ['ecommerce', 'online store', 'payments', 'shopify'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 2500],
            ['pricing_type' => 'starting_from', 'price' => 3000],
            ['pricing_type' => 'starting_from', 'price' => 140000],
        ]);

        $createService([
            'name' => 'Multi-Vendor Marketplace Development',
            'category' => 'web-development',
            'short_description' => 'Build multi-vendor marketplace platforms like Amazon or Etsy for your niche.',
            'description' => 'Custom marketplace development with vendor management, commission systems, product listings, order processing, and vendor dashboards.',
            'complexity_level' => 'enterprise',
            'estimated_completion' => '12-20 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Marketplace platform', 'Vendor dashboard', 'Commission system', 'Order management', 'Payment splits'],
            'features' => ['Multi-vendor support', 'Commission management', 'Vendor analytics', 'Automated payouts'],
            'tags' => ['marketplace', 'multi-vendor', 'ecommerce', 'platform'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 8000],
            ['pricing_type' => 'starting_from', 'price' => 10000],
            ['pricing_type' => 'starting_from', 'price' => 450000],
        ]);

        $createService([
            'name' => 'Custom Web Application Development',
            'category' => 'web-development',
            'short_description' => 'Tailored web applications built to your exact business requirements and workflows.',
            'description' => 'Full-stack web application development using modern frameworks. We build custom solutions for unique business needs that off-the-shelf software cannot address.',
            'complexity_level' => 'enterprise',
            'estimated_completion' => '8-20 weeks',
            'allows_custom_quote' => true,
            'is_featured' => true,
            'deliverables' => ['Custom web application', 'Source code', 'API documentation', 'Deployment', 'User documentation'],
            'features' => ['Tailored solution', 'Scalable architecture', 'Modern tech stack', 'API ready'],
            'tags' => ['custom', 'web app', 'full stack', 'laravel'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 5000],
            ['pricing_type' => 'starting_from', 'price' => 6000],
            ['pricing_type' => 'starting_from', 'price' => 275000],
        ]);

        $createService([
            'name' => 'CRM Development',
            'category' => 'web-development',
            'short_description' => 'Custom CRM systems to manage customer relationships, sales pipelines, and communications.',
            'description' => 'Build a custom CRM tailored to your sales process with lead management, deal tracking, communication history, reporting, and workflow automation.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '8-16 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Custom CRM platform', 'Lead management', 'Sales pipeline', 'Reporting dashboard', 'Integration APIs'],
            'features' => ['Custom workflows', 'Sales pipeline', 'Contact management', 'Reporting'],
            'tags' => ['crm', 'sales', 'customer management'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 5000],
            ['pricing_type' => 'starting_from', 'price' => 6000],
            ['pricing_type' => 'starting_from', 'price' => 275000],
        ]);

        $createService([
            'name' => 'Customer Portal Development',
            'category' => 'web-development',
            'short_description' => 'Self-service customer portals for account management, support, and service access.',
            'description' => 'Custom customer portal development allowing customers to manage accounts, submit support requests, track orders, view invoices, and access services.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '6-12 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Customer portal', 'Account management', 'Support ticketing', 'Invoice access', 'Mobile responsive'],
            'features' => ['Self-service', 'Real-time updates', 'Secure authentication', 'Mobile ready'],
            'tags' => ['portal', 'customer self-service', 'account management'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 4000],
            ['pricing_type' => 'starting_from', 'price' => 5000],
            ['pricing_type' => 'starting_from', 'price' => 225000],
        ]);

        $createService([
            'name' => 'SaaS Application Development',
            'category' => 'web-development',
            'short_description' => 'Build scalable SaaS products with multi-tenancy, subscription billing, and admin dashboards.',
            'description' => 'End-to-end SaaS application development including multi-tenant architecture, subscription management, admin panels, API development, and deployment.',
            'complexity_level' => 'enterprise',
            'estimated_completion' => '12-24 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['SaaS platform', 'Multi-tenant architecture', 'Billing integration', 'Admin dashboard', 'API documentation'],
            'features' => ['Multi-tenancy', 'Subscription billing', 'Scalable architecture', 'White-label ready'],
            'tags' => ['saas', 'subscription', 'multi-tenant', 'platform'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 15000],
            ['pricing_type' => 'starting_from', 'price' => 18000],
            ['pricing_type' => 'starting_from', 'price' => 750000],
        ]);

        $createService([
            'name' => 'Booking System Development',
            'category' => 'web-development',
            'short_description' => 'Custom appointment and booking systems with calendar integration and automated reminders.',
            'description' => 'Build a custom booking system with real-time availability, calendar sync, automated confirmations, payment integration, and multi-provider support.',
            'complexity_level' => 'standard',
            'estimated_completion' => '4-8 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Booking platform', 'Calendar integration', 'Payment processing', 'Notifications', 'Admin panel'],
            'features' => ['Real-time availability', 'Calendar sync', 'Automated reminders', 'Payment integration'],
            'tags' => ['booking', 'appointment', 'calendar', 'scheduling'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 2000],
            ['pricing_type' => 'starting_from', 'price' => 2500],
            ['pricing_type' => 'starting_from', 'price' => 110000],
        ]);

        $createService([
            'name' => 'Learning Management System Development',
            'category' => 'web-development',
            'short_description' => 'Custom LMS platforms for online courses, training, and certifications.',
            'description' => 'Complete learning management system with course creation, student enrollment, progress tracking, quizzes, certificates, and payment integration.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '10-16 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['LMS platform', 'Course builder', 'Student dashboard', 'Quiz engine', 'Certificate system'],
            'features' => ['Course management', 'Progress tracking', 'Quiz engine', 'Certificate generation'],
            'tags' => ['lms', 'elearning', 'courses', 'training'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 5000],
            ['pricing_type' => 'starting_from', 'price' => 6000],
            ['pricing_type' => 'starting_from', 'price' => 275000],
        ]);

        $createService([
            'name' => 'API Development',
            'category' => 'web-development',
            'short_description' => 'Design and build RESTful and GraphQL APIs for your applications and integrations.',
            'description' => 'Custom API development including RESTful APIs, GraphQL endpoints, authentication, rate limiting, documentation, and SDK generation.',
            'complexity_level' => 'standard',
            'estimated_completion' => '2-6 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['API endpoints', 'Documentation (Swagger/OpenAPI)', 'Authentication system', 'Rate limiting', 'Testing suite'],
            'features' => ['RESTful + GraphQL', 'Auto-documentation', 'Authentication', 'Rate limiting'],
            'tags' => ['api', 'rest', 'graphql', 'backend'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 1500],
            ['pricing_type' => 'starting_from', 'price' => 1900],
            ['pricing_type' => 'starting_from', 'price' => 85000],
        ]);

        $createService([
            'name' => 'API Integration',
            'category' => 'web-development',
            'short_description' => 'Integrate third-party APIs including payment gateways, CRMs, ERPs, and SaaS platforms.',
            'description' => 'Seamless integration with third-party services including payment gateways, CRM/ERP systems, email services, analytics platforms, and custom APIs.',
            'complexity_level' => 'standard',
            'estimated_completion' => '1-4 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['API integration', 'Error handling', 'Documentation', 'Testing'],
            'features' => ['Multi-platform support', 'Error handling', 'Logging', 'Monitoring'],
            'tags' => ['api', 'integration', 'third-party', 'saas'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 800],
            ['pricing_type' => 'starting_from', 'price' => 1000],
            ['pricing_type' => 'starting_from', 'price' => 45000],
        ]);

        $createService([
            'name' => 'Website Redesign',
            'category' => 'web-development',
            'short_description' => 'Modernize your existing website with updated design, improved UX, and better performance.',
            'description' => 'Complete website redesign including UX audit, modern visual design, responsive implementation, content migration, and performance optimization.',
            'complexity_level' => 'standard',
            'estimated_completion' => '4-8 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Design mockups', 'Responsive implementation', 'Content migration', 'Performance optimization'],
            'features' => ['Modern design', 'UX improvement', 'Mobile responsive', 'Performance boost'],
            'tags' => ['redesign', 'ui/ux', 'modernization'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 1500],
            ['pricing_type' => 'starting_from', 'price' => 1900],
            ['pricing_type' => 'starting_from', 'price' => 85000],
        ]);

        $createService([
            'name' => 'Website Maintenance',
            'category' => 'web-development',
            'short_description' => 'Ongoing website maintenance including updates, security patches, backups, and support.',
            'description' => 'Regular website maintenance covering CMS updates, security patches, plugin updates, backups, uptime monitoring, and content updates.',
            'complexity_level' => 'basic',
            'estimated_completion' => 'Ongoing',
            'allows_custom_quote' => true,
            'deliverables' => ['Regular updates', 'Security patches', 'Backups', 'Uptime monitoring', 'Monthly reports'],
            'features' => ['Monthly updates', 'Security patches', 'Daily backups', 'Uptime monitoring'],
            'tags' => ['maintenance', 'updates', 'security', 'support'],
        ], [
            ['pricing_type' => 'monthly', 'price' => 150],
            ['pricing_type' => 'monthly', 'price' => 190],
            ['pricing_type' => 'monthly', 'price' => 9000],
        ]);

        $createService([
            'name' => 'Website Performance Optimization',
            'category' => 'web-development',
            'short_description' => 'Optimize website speed, Core Web Vitals, and loading performance for better user experience.',
            'description' => 'Comprehensive website performance optimization including image optimization, code minification, caching strategies, CDN setup, and Core Web Vitals improvement.',
            'complexity_level' => 'standard',
            'estimated_completion' => '3-7 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Performance audit', 'Optimization implementation', 'Speed test results', 'Monitoring setup'],
            'features' => ['Core Web Vitals', 'Image optimization', 'Caching setup', 'CDN configuration'],
            'tags' => ['performance', 'speed', 'optimization', 'core web vitals'],
        ], [
            ['pricing_type' => 'fixed', 'price' => 500],
            ['pricing_type' => 'fixed', 'price' => 650],
            ['pricing_type' => 'fixed', 'price' => 32000],
        ]);

        $createService([
            'name' => 'WordPress Development',
            'category' => 'web-development',
            'short_description' => 'Custom WordPress development with themes, plugins, and advanced functionality.',
            'description' => 'Expert WordPress development including custom theme development, plugin development, WooCommerce customization, and performance optimization.',
            'complexity_level' => 'standard',
            'estimated_completion' => '2-8 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Custom WordPress site', 'Theme development', 'Plugin customization', 'Content setup'],
            'features' => ['Custom themes', 'Plugin development', 'WooCommerce', 'SEO optimized'],
            'tags' => ['wordpress', 'themes', 'plugins', 'woocommerce'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 800],
            ['pricing_type' => 'starting_from', 'price' => 1000],
            ['pricing_type' => 'starting_from', 'price' => 50000],
        ]);

        $createService([
            'name' => 'Laravel Development',
            'category' => 'web-development',
            'short_description' => 'Enterprise Laravel application development with scalable architecture and clean code.',
            'description' => 'Custom Laravel application development using best practices including testing, clean architecture, API development, and deployment automation.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '4-16 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Laravel application', 'Source code', 'API documentation', 'Test suite', 'Deployment setup'],
            'features' => ['Clean architecture', 'Tested code', 'API ready', 'Scalable'],
            'tags' => ['laravel', 'php', 'backend', 'full stack'],
        ], [
            ['pricing_type' => 'hourly', 'price' => 75],
            ['pricing_type' => 'hourly', 'price' => 95],
            ['pricing_type' => 'hourly', 'price' => 4500],
        ]);

        $createService([
            'name' => 'Database Development and Optimization',
            'category' => 'web-development',
            'short_description' => 'Database design, optimization, migration, and performance tuning for MySQL and PostgreSQL.',
            'description' => 'Expert database services including schema design, query optimization, index management, data migration, and performance tuning for MySQL and PostgreSQL.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '2-6 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Database design', 'Query optimization', 'Migration scripts', 'Performance report'],
            'features' => ['Schema design', 'Query optimization', 'Data migration', 'Performance tuning'],
            'tags' => ['database', 'mysql', 'postgresql', 'optimization'],
        ], [
            ['pricing_type' => 'hourly', 'price' => 80],
            ['pricing_type' => 'hourly', 'price' => 100],
            ['pricing_type' => 'hourly', 'price' => 5000],
        ]);

        $createService([
            'name' => 'Website Bug Fixing',
            'category' => 'web-development',
            'short_description' => 'Quick resolution of website bugs, errors, and functionality issues.',
            'description' => 'Rapid bug fixing service for websites and web applications. We diagnose and fix functionality issues, broken features, display problems, and errors.',
            'complexity_level' => 'basic',
            'estimated_completion' => '1-5 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Bug identification', 'Fix implementation', 'Testing', 'Documentation'],
            'features' => ['Quick turnaround', 'Root cause analysis', 'Prevention advice', 'Documentation'],
            'tags' => ['bug fixing', 'debugging', 'repair', 'support'],
        ], [
            ['pricing_type' => 'hourly', 'price' => 60],
            ['pricing_type' => 'hourly', 'price' => 75],
            ['pricing_type' => 'hourly', 'price' => 3500],
        ]);

        $createService([
            'name' => 'Website Migration',
            'category' => 'web-development',
            'short_description' => 'Safe migration of websites between hosting providers, platforms, and servers.',
            'description' => 'Complete website migration service including content transfer, database migration, DNS configuration, SSL setup, and post-migration testing.',
            'complexity_level' => 'standard',
            'estimated_completion' => '1-5 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Migration plan', 'Content transfer', 'DNS configuration', 'SSL setup', 'Testing report'],
            'features' => ['Zero downtime migration', 'Data integrity check', 'SSL migration', 'DNS management'],
            'tags' => ['migration', 'hosting', 'transfer', 'dns'],
        ], [
            ['pricing_type' => 'fixed', 'price' => 300],
            ['pricing_type' => 'fixed', 'price' => 375],
            ['pricing_type' => 'fixed', 'price' => 18000],
        ]);

        $createService([
            'name' => 'Web Hosting Setup',
            'category' => 'web-development',
            'short_description' => 'Professional web hosting setup with security, backups, and performance optimization.',
            'description' => 'Complete hosting setup including server configuration, security hardening, backup setup, SSL installation, and performance optimization.',
            'complexity_level' => 'basic',
            'estimated_completion' => '1-3 business days',
            'allows_custom_quote' => false,
            'deliverables' => ['Hosting setup', 'Security configuration', 'Backup setup', 'SSL installation'],
            'features' => ['Server hardening', 'Automated backups', 'SSL setup', 'Performance tuning'],
            'tags' => ['hosting', 'setup', 'server', 'ssl'],
        ], [
            ['pricing_type' => 'fixed', 'price' => 200],
            ['pricing_type' => 'fixed', 'price' => 250],
            ['pricing_type' => 'fixed', 'price' => 12000],
        ]);

        $createService([
            'name' => 'Domain and DNS Configuration',
            'category' => 'web-development',
            'short_description' => 'Domain registration, DNS configuration, and email routing setup.',
            'description' => 'Complete domain management including registration assistance, DNS record configuration, email routing, and domain transfer support.',
            'complexity_level' => 'basic',
            'estimated_completion' => '1-2 business days',
            'allows_custom_quote' => false,
            'deliverables' => ['DNS configuration', 'Email routing', 'SSL setup', 'Documentation'],
            'features' => ['DNS management', 'Email setup', 'SSL configuration', 'Transfer support'],
            'tags' => ['domain', 'dns', 'email', 'hosting'],
        ], [
            ['pricing_type' => 'fixed', 'price' => 100],
            ['pricing_type' => 'fixed', 'price' => 125],
            ['pricing_type' => 'fixed', 'price' => 6000],
        ]);

        $createService([
            'name' => 'Progressive Web Application Development',
            'category' => 'web-development',
            'short_description' => 'Build PWA solutions that work offline and provide native app-like experiences on the web.',
            'description' => 'Custom Progressive Web App development with offline capability, push notifications, app-like navigation, and home screen installation.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '6-12 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['PWA application', 'Service worker', 'Offline capability', 'Push notifications', 'App manifest'],
            'features' => ['Offline support', 'Push notifications', 'Home screen install', 'App-like UX'],
            'tags' => ['pwa', 'progressive web app', 'offline', 'mobile'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 3000],
            ['pricing_type' => 'starting_from', 'price' => 3800],
            ['pricing_type' => 'starting_from', 'price' => 170000],
        ]);

        // ══════════════════════════════════════════════════════════════════════════
        //  CATEGORY 3: DIGITAL MARKETING (~25)
        // ══════════════════════════════════════════════════════════════════════════

        $createService([
            'name' => 'SEO Audit',
            'category' => 'digital-marketing',
            'short_description' => 'Comprehensive SEO audit covering technical, on-page, and off-page factors affecting search rankings.',
            'description' => 'In-depth SEO audit analyzing your website\'s technical health, on-page optimization, backlink profile, content quality, and competitive positioning.',
            'complexity_level' => 'standard',
            'estimated_completion' => '3-5 business days',
            'allows_custom_quote' => true,
            'is_featured' => true,
            'deliverables' => ['SEO audit report', 'Technical analysis', 'Competitor comparison', 'Action plan'],
            'features' => ['Technical SEO check', 'Content analysis', 'Backlink audit', 'Competitor analysis'],
            'tags' => ['seo', 'audit', 'technical seo', 'analysis'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 500],
            ['pricing_type' => 'starting_from', 'price' => 650],
            ['pricing_type' => 'starting_from', 'price' => 30000],
        ]);

        $createService([
            'name' => 'Technical SEO',
            'category' => 'digital-marketing',
            'short_description' => 'Fix technical SEO issues including crawlability, indexation, site speed, and structured data.',
            'description' => 'Technical SEO optimization covering site architecture, crawl budget, indexation, Core Web Vitals, structured data, and mobile optimization.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '2-4 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Technical SEO fixes', 'Schema markup', 'Site speed optimization', 'Crawl report'],
            'features' => ['Crawl optimization', 'Core Web Vitals', 'Schema markup', 'Mobile optimization'],
            'tags' => ['technical seo', 'crawl', 'indexation', 'schema'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 1000],
            ['pricing_type' => 'starting_from', 'price' => 1300],
            ['pricing_type' => 'starting_from', 'price' => 60000],
        ]);

        $createService([
            'name' => 'On-Page SEO',
            'category' => 'digital-marketing',
            'short_description' => 'Optimize page content, meta tags, headings, internal linking, and keyword targeting.',
            'description' => 'Comprehensive on-page SEO optimization including keyword research, content optimization, meta tag optimization, heading structure, and internal linking strategy.',
            'complexity_level' => 'standard',
            'estimated_completion' => '1-3 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Keyword strategy', 'Content optimization', 'Meta tag updates', 'Internal linking plan'],
            'features' => ['Keyword research', 'Content optimization', 'Meta optimization', 'Internal linking'],
            'tags' => ['on-page seo', 'keywords', 'content', 'meta tags'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 600],
            ['pricing_type' => 'starting_from', 'price' => 750],
            ['pricing_type' => 'starting_from', 'price' => 35000],
        ]);

        $createService([
            'name' => 'Off-Page SEO',
            'category' => 'digital-marketing',
            'short_description' => 'Build high-quality backlinks, improve domain authority, and strengthen off-page signals.',
            'description' => 'Strategic off-page SEO including link building, digital PR, guest posting, brand mention outreach, and domain authority improvement.',
            'complexity_level' => 'advanced',
            'estimated_completion' => 'Ongoing (3-6 month initial)',
            'allows_custom_quote' => true,
            'deliverables' => ['Link building strategy', 'Outreach campaigns', 'Backlink report', 'Monthly reporting'],
            'features' => ['Quality link building', 'Digital PR', 'Guest posting', 'Brand mentions'],
            'tags' => ['off-page seo', 'link building', 'backlinks', 'da'],
        ], [
            ['pricing_type' => 'monthly', 'price' => 800],
            ['pricing_type' => 'monthly', 'price' => 1000],
            ['pricing_type' => 'monthly', 'price' => 50000],
        ]);

        $createService([
            'name' => 'Local SEO',
            'category' => 'digital-marketing',
            'short_description' => 'Optimize local search presence including Google Business Profile, local citations, and reviews.',
            'description' => 'Local SEO optimization including Google Business Profile management, local citation building, review strategy, and local keyword targeting.',
            'complexity_level' => 'standard',
            'estimated_completion' => '2-4 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['GBP optimization', 'Local citation setup', 'Review strategy', 'Local keyword plan'],
            'features' => ['Google Business Profile', 'Local citations', 'Review management', 'Local keywords'],
            'tags' => ['local seo', 'google business', 'citations', 'reviews'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 400],
            ['pricing_type' => 'starting_from', 'price' => 500],
            ['pricing_type' => 'starting_from', 'price' => 25000],
        ]);

        $createService([
            'name' => 'Keyword Research',
            'category' => 'digital-marketing',
            'short_description' => 'Data-driven keyword research to identify high-value search opportunities for your business.',
            'description' => 'Comprehensive keyword research including search volume analysis, competition assessment, keyword mapping, and content gap analysis.',
            'complexity_level' => 'standard',
            'estimated_completion' => '3-5 business days',
            'allows_custom_quote' => false,
            'deliverables' => ['Keyword research report', 'Search volume data', 'Keyword mapping', 'Content recommendations'],
            'features' => ['Search volume analysis', 'Competition analysis', 'Long-tail keywords', 'Topic clustering'],
            'tags' => ['keyword research', 'seo', 'search volume', 'content'],
        ], [
            ['pricing_type' => 'fixed', 'price' => 350],
            ['pricing_type' => 'fixed', 'price' => 450],
            ['pricing_type' => 'fixed', 'price' => 20000],
        ]);

        $createService([
            'name' => 'Content Strategy',
            'category' => 'digital-marketing',
            'short_description' => 'Develop a comprehensive content strategy aligned with business goals and search intent.',
            'description' => 'Strategic content planning including audience analysis, content calendar creation, topic cluster development, and content performance framework.',
            'complexity_level' => 'standard',
            'estimated_completion' => '1-2 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Content strategy document', 'Editorial calendar', 'Topic clusters', 'KPI framework'],
            'features' => ['Audience analysis', 'Topic research', 'Editorial planning', 'Performance metrics'],
            'tags' => ['content strategy', 'planning', 'editorial', 'content marketing'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 600],
            ['pricing_type' => 'starting_from', 'price' => 750],
            ['pricing_type' => 'starting_from', 'price' => 35000],
        ]);

        $createService([
            'name' => 'Social Media Marketing',
            'category' => 'digital-marketing',
            'short_description' => 'Strategic social media marketing across Facebook, Instagram, LinkedIn, and Twitter.',
            'description' => 'Full-service social media marketing including strategy development, content creation, community management, and performance reporting.',
            'complexity_level' => 'standard',
            'estimated_completion' => 'Ongoing',
            'allows_custom_quote' => true,
            'is_featured' => true,
            'deliverables' => ['Social media strategy', 'Content calendar', 'Community management', 'Monthly reports'],
            'features' => ['Multi-platform management', 'Content creation', 'Community engagement', 'Analytics reporting'],
            'tags' => ['social media', 'marketing', 'facebook', 'instagram', 'linkedin'],
        ], [
            ['pricing_type' => 'monthly', 'price' => 600],
            ['pricing_type' => 'monthly', 'price' => 750],
            ['pricing_type' => 'monthly', 'price' => 35000],
        ]);

        $createService([
            'name' => 'Social Media Management',
            'category' => 'digital-marketing',
            'short_description' => 'Complete social media account management including posting, engagement, and analytics.',
            'description' => 'End-to-end social media management covering content scheduling, community interaction, comment management, DM responses, and performance tracking.',
            'complexity_level' => 'basic',
            'estimated_completion' => 'Ongoing',
            'allows_custom_quote' => true,
            'deliverables' => ['Content scheduling', 'Community management', 'Engagement reports', 'Monthly analytics'],
            'features' => ['Scheduled posting', 'Community management', 'DM handling', 'Performance tracking'],
            'tags' => ['social media', 'management', 'community', 'engagement'],
        ], [
            ['pricing_type' => 'monthly', 'price' => 400],
            ['pricing_type' => 'monthly', 'price' => 500],
            ['pricing_type' => 'monthly', 'price' => 25000],
        ]);

        $createService([
            'name' => 'Paid Advertising Management',
            'category' => 'digital-marketing',
            'short_description' => 'Manage paid ad campaigns across Google Ads, Facebook Ads, LinkedIn Ads, and more.',
            'description' => 'Professional paid advertising management including campaign strategy, ad creation, A/B testing, bid management, and ROI reporting.',
            'complexity_level' => 'advanced',
            'estimated_completion' => 'Ongoing',
            'allows_custom_quote' => true,
            'is_featured' => true,
            'deliverables' => ['Ad strategy', 'Campaign setup', 'Ad creative', 'A/B testing', 'ROI reports'],
            'features' => ['Multi-platform ads', 'A/B testing', 'Bid optimization', 'ROI tracking'],
            'tags' => ['paid ads', 'google ads', 'facebook ads', 'ppc'],
        ], [
            ['pricing_type' => 'monthly', 'price' => 800],
            ['pricing_type' => 'monthly', 'price' => 1000],
            ['pricing_type' => 'monthly', 'price' => 50000],
        ]);

        $createService([
            'name' => 'Email Marketing Setup',
            'category' => 'digital-marketing',
            'short_description' => 'Set up email marketing systems with automation, templates, and subscriber management.',
            'description' => 'Complete email marketing setup including platform configuration, template design, automation workflows, subscriber segmentation, and deliverability optimization.',
            'complexity_level' => 'standard',
            'estimated_completion' => '1-2 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Email platform setup', 'Template designs', 'Automation workflows', 'Subscriber segments'],
            'features' => ['Automation setup', 'Template design', 'Segmentation', 'Deliverability optimization'],
            'tags' => ['email marketing', 'automation', 'templates', 'newsletter'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 500],
            ['pricing_type' => 'starting_from', 'price' => 650],
            ['pricing_type' => 'starting_from', 'price' => 30000],
        ]);

        $createService([
            'name' => 'Marketing Automation',
            'category' => 'digital-marketing',
            'short_description' => 'Automate marketing workflows including lead nurture sequences, triggers, and scoring.',
            'description' => 'Implement marketing automation including lead nurture sequences, behavioral triggers, lead scoring, CRM integration, and campaign automation.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '2-4 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Automation workflows', 'Lead scoring setup', 'CRM integration', 'Performance dashboards'],
            'features' => ['Lead nurture', 'Behavioral triggers', 'Lead scoring', 'CRM integration'],
            'tags' => ['marketing automation', 'lead nurture', 'crm', 'workflows'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 1200],
            ['pricing_type' => 'starting_from', 'price' => 1500],
            ['pricing_type' => 'starting_from', 'price' => 70000],
        ]);

        $createService([
            'name' => 'Lead Generation',
            'category' => 'digital-marketing',
            'short_description' => 'Generate qualified leads through targeted campaigns, landing pages, and funnel optimization.',
            'description' => 'Data-driven lead generation using targeted advertising, landing page optimization, lead magnets, and conversion funnel analysis.',
            'complexity_level' => 'standard',
            'estimated_completion' => 'Ongoing',
            'allows_custom_quote' => true,
            'deliverables' => ['Lead gen strategy', 'Landing pages', 'Funnel optimization', 'Lead reports'],
            'features' => ['Targeted campaigns', 'Landing page creation', 'Funnel optimization', 'Lead tracking'],
            'tags' => ['lead generation', 'funnel', 'conversion', 'leads'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 700],
            ['pricing_type' => 'starting_from', 'price' => 900],
            ['pricing_type' => 'starting_from', 'price' => 40000],
        ]);

        $createService([
            'name' => 'Analytics and Tracking Setup',
            'category' => 'digital-marketing',
            'short_description' => 'Set up Google Analytics, Tag Manager, conversion tracking, and custom dashboards.',
            'description' => 'Complete analytics setup including Google Analytics 4, Google Tag Manager, conversion tracking, custom events, and dashboard configuration.',
            'complexity_level' => 'standard',
            'estimated_completion' => '2-5 business days',
            'allows_custom_quote' => false,
            'deliverables' => ['GA4 setup', 'GTM configuration', 'Conversion tracking', 'Custom dashboard'],
            'features' => ['GA4 configuration', 'Event tracking', 'Conversion tracking', 'Custom reports'],
            'tags' => ['analytics', 'google analytics', 'tag manager', 'tracking'],
        ], [
            ['pricing_type' => 'fixed', 'price' => 350],
            ['pricing_type' => 'fixed', 'price' => 450],
            ['pricing_type' => 'fixed', 'price' => 20000],
        ]);

        // ══════════════════════════════════════════════════════════════════════════
        //  CATEGORY 4: MANAGED IT SUPPORT (~14)
        // ══════════════════════════════════════════════════════════════════════════

        $createService([
            'name' => 'Remote IT Support',
            'category' => 'managed-it-support',
            'short_description' => 'Fast remote IT support for desktop, software, and connectivity issues.',
            'description' => 'Expert remote IT support for troubleshooting software issues, connectivity problems, email configuration, and general desktop support.',
            'complexity_level' => 'basic',
            'estimated_completion' => 'Same day',
            'allows_custom_quote' => true,
            'deliverables' => ['Remote troubleshooting', 'Issue resolution', 'Support documentation'],
            'features' => ['Same-day response', 'Remote access', 'Multi-platform support', 'Escalation path'],
            'tags' => ['remote support', 'it support', 'helpdesk', 'troubleshooting'],
        ], [
            ['pricing_type' => 'hourly', 'price' => 50],
            ['pricing_type' => 'hourly', 'price' => 65],
            ['pricing_type' => 'hourly', 'price' => 2500],
        ]);

        $createService([
            'name' => 'On-site IT Support',
            'category' => 'managed-it-support',
            'short_description' => 'Professional on-site IT support for hardware installation, network setup, and complex issues.',
            'description' => 'Physical on-site IT support for hardware installation, network configuration, server setup, cabling, and issues that cannot be resolved remotely.',
            'complexity_level' => 'standard',
            'estimated_completion' => '1-2 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['On-site visit', 'Issue resolution', 'Setup documentation', 'Follow-up'],
            'features' => ['On-site presence', 'Hardware support', 'Network setup', 'Same-day available'],
            'tags' => ['on-site', 'hardware', 'network', 'installation'],
        ], [
            ['pricing_type' => 'hourly', 'price' => 80],
            ['pricing_type' => 'hourly', 'price' => 100],
            ['pricing_type' => 'hourly', 'price' => 4500],
        ]);

        $createService([
            'name' => 'Managed IT Support',
            'category' => 'managed-it-support',
            'short_description' => 'Complete managed IT support with proactive monitoring, maintenance, and helpdesk.',
            'description' => 'Full managed IT support service including proactive monitoring, preventive maintenance, helpdesk support, and strategic IT planning.',
            'complexity_level' => 'enterprise',
            'estimated_completion' => 'Ongoing',
            'allows_custom_quote' => true,
            'is_featured' => true,
            'deliverables' => ['Proactive monitoring', 'Preventive maintenance', 'Helpdesk support', 'Monthly reports'],
            'features' => ['24/7 monitoring', 'Proactive maintenance', 'Helpdesk', 'Strategic planning'],
            'tags' => ['managed services', 'proactive', 'monitoring', 'helpdesk'],
        ], [
            ['pricing_type' => 'monthly', 'price' => 500],
            ['pricing_type' => 'monthly', 'price' => 650],
            ['pricing_type' => 'monthly', 'price' => 30000],
        ]);

        $createService([
            'name' => 'Desktop Support',
            'category' => 'managed-it-support',
            'short_description' => 'Windows and Mac desktop support for software, configuration, and troubleshooting.',
            'description' => 'Expert desktop support for Windows and Mac environments covering software installation, configuration, troubleshooting, and optimization.',
            'complexity_level' => 'basic',
            'estimated_completion' => 'Same day',
            'allows_custom_quote' => true,
            'deliverables' => ['Desktop troubleshooting', 'Software setup', 'Configuration', 'Documentation'],
            'features' => ['Windows and Mac', 'Software support', 'Configuration', 'Quick resolution'],
            'tags' => ['desktop', 'windows', 'mac', 'software'],
        ], [
            ['pricing_type' => 'hourly', 'price' => 45],
            ['pricing_type' => 'hourly', 'price' => 55],
            ['pricing_type' => 'hourly', 'price' => 2200],
        ]);

        $createService([
            'name' => 'Laptop Troubleshooting',
            'category' => 'managed-it-support',
            'short_description' => 'Diagnose and fix laptop hardware and software issues including screen, battery, and performance.',
            'description' => 'Expert laptop troubleshooting covering hardware diagnostics, software issues, performance optimization, and component replacement recommendations.',
            'complexity_level' => 'basic',
            'estimated_completion' => '1-2 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Diagnostic report', 'Issue resolution', 'Repair recommendations', 'Data backup'],
            'features' => ['Hardware diagnostics', 'Software troubleshooting', 'Performance optimization', 'Data protection'],
            'tags' => ['laptop', 'hardware', 'troubleshooting', 'repair'],
        ], [
            ['pricing_type' => 'fixed', 'price' => 75],
            ['pricing_type' => 'fixed', 'price' => 95],
            ['pricing_type' => 'fixed', 'price' => 4500],
        ]);

        $createService([
            'name' => 'Software Troubleshooting',
            'category' => 'managed-it-support',
            'short_description' => 'Fix software conflicts, installation issues, and application errors across all platforms.',
            'description' => 'Professional software troubleshooting for installation errors, compatibility issues, license management, and application performance problems.',
            'complexity_level' => 'basic',
            'estimated_completion' => 'Same day',
            'allows_custom_quote' => true,
            'deliverables' => ['Issue diagnosis', 'Resolution steps', 'Documentation', 'Prevention tips'],
            'features' => ['Multi-platform', 'Quick diagnosis', 'Compatibility fixes', 'License management'],
            'tags' => ['software', 'troubleshooting', 'installation', 'configuration'],
        ], [
            ['pricing_type' => 'hourly', 'price' => 40],
            ['pricing_type' => 'hourly', 'price' => 50],
            ['pricing_type' => 'hourly', 'price' => 2000],
        ]);

        $createService([
            'name' => 'IT Health Check',
            'category' => 'managed-it-support',
            'short_description' => 'Comprehensive IT health assessment covering infrastructure, security, and best practices.',
            'description' => 'Thorough IT health check evaluating your entire technology infrastructure, identifying risks, and providing actionable recommendations.',
            'complexity_level' => 'standard',
            'estimated_completion' => '3-7 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Health check report', 'Risk assessment', 'Recommendations', 'Priority roadmap'],
            'features' => ['Infrastructure review', 'Security assessment', 'Best practices audit', 'Risk identification'],
            'tags' => ['health check', 'assessment', 'audit', 'infrastructure'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 500],
            ['pricing_type' => 'starting_from', 'price' => 650],
            ['pricing_type' => 'starting_from', 'price' => 30000],
        ]);

        $createService([
            'name' => 'IT Infrastructure Review',
            'category' => 'managed-it-support',
            'short_description' => 'Evaluate your IT infrastructure for performance, security, and optimization opportunities.',
            'description' => 'Complete IT infrastructure review covering servers, workstations, networking, storage, and cloud resources with optimization recommendations.',
            'complexity_level' => 'standard',
            'estimated_completion' => '5-10 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Infrastructure assessment', 'Performance analysis', 'Optimization plan', 'Budget recommendations'],
            'features' => ['Full infrastructure audit', 'Performance analysis', 'Cost optimization', 'Upgrade planning'],
            'tags' => ['infrastructure', 'review', 'optimization', 'planning'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 1200],
            ['pricing_type' => 'starting_from', 'price' => 1500],
            ['pricing_type' => 'starting_from', 'price' => 70000],
        ]);

        $createService([
            'name' => 'Patch Management',
            'category' => 'managed-it-support',
            'short_description' => 'Keep all systems updated with the latest security patches and software updates.',
            'description' => 'Systematic patch management service covering vulnerability assessment, patch testing, deployment, and compliance reporting.',
            'complexity_level' => 'standard',
            'estimated_completion' => 'Ongoing',
            'allows_custom_quote' => true,
            'deliverables' => ['Patch assessment', 'Deployment reports', 'Compliance reports', 'Rollback procedures'],
            'features' => ['Automated patching', 'Testing pipeline', 'Scheduled deployments', 'Rollback support'],
            'tags' => ['patch management', 'updates', 'security', 'compliance'],
        ], [
            ['pricing_type' => 'monthly', 'price' => 300],
            ['pricing_type' => 'monthly', 'price' => 375],
            ['pricing_type' => 'monthly', 'price' => 18000],
        ]);

        $createService([
            'name' => 'Backup Management',
            'category' => 'managed-it-support',
            'short_description' => 'Set up and manage reliable backup systems with regular testing and recovery procedures.',
            'description' => 'Complete backup management including backup strategy design, implementation, monitoring, testing, and disaster recovery procedures.',
            'complexity_level' => 'standard',
            'estimated_completion' => '2-5 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Backup strategy', 'Implementation', 'Testing schedule', 'Recovery procedures'],
            'features' => ['Automated backups', 'Regular testing', 'Off-site storage', 'Recovery procedures'],
            'tags' => ['backup', 'recovery', 'disaster recovery', 'data protection'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 400],
            ['pricing_type' => 'starting_from', 'price' => 500],
            ['pricing_type' => 'starting_from', 'price' => 22000],
        ]);

        $createService([
            'name' => 'Disaster Recovery Support',
            'category' => 'managed-it-support',
            'short_description' => 'Plan, implement, and test disaster recovery procedures for business continuity.',
            'description' => 'Comprehensive disaster recovery planning and implementation including DR strategy, failover setup, testing, and business continuity procedures.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '2-6 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['DR plan', 'Failover setup', 'Testing results', 'BDR procedures', 'Training'],
            'features' => ['DR planning', 'Failover setup', 'Regular testing', 'Business continuity'],
            'tags' => ['disaster recovery', 'business continuity', 'failover', 'resilience'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 2000],
            ['pricing_type' => 'starting_from', 'price' => 2500],
            ['pricing_type' => 'starting_from', 'price' => 115000],
        ]);

        // ══════════════════════════════════════════════════════════════════════════
        //  CATEGORY 5: CLOUD, SERVER & NETWORK (~15)
        // ══════════════════════════════════════════════════════════════════════════

        $createService([
            'name' => 'Cloud Migration',
            'category' => 'cloud-server-network',
            'short_description' => 'Migrate your infrastructure, applications, and data to AWS, Azure, or GCP.',
            'description' => 'Complete cloud migration service from planning and assessment through migration execution to post-migration optimization for AWS, Azure, and GCP.',
            'complexity_level' => 'enterprise',
            'estimated_completion' => '4-16 weeks',
            'allows_custom_quote' => true,
            'is_featured' => true,
            'deliverables' => ['Migration plan', 'Cloud architecture', 'Migration execution', 'Optimization report'],
            'features' => ['Multi-cloud support', 'Zero downtime options', 'Data migration', 'App modernization'],
            'tags' => ['cloud migration', 'aws', 'azure', 'gcp'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 5000],
            ['pricing_type' => 'starting_from', 'price' => 6000],
            ['pricing_type' => 'starting_from', 'price' => 275000],
        ]);

        $createService([
            'name' => 'Cloud Infrastructure Setup',
            'category' => 'cloud-server-network',
            'short_description' => 'Design and deploy cloud infrastructure with best practices for security and scalability.',
            'description' => 'Cloud infrastructure setup including VPC design, compute instances, storage configuration, networking, and security groups.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '1-3 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Cloud architecture', 'Infrastructure deployment', 'Security configuration', 'Documentation'],
            'features' => ['IaC templates', 'Security groups', 'Auto-scaling', 'Monitoring setup'],
            'tags' => ['cloud', 'infrastructure', 'devops', 'aws'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 2000],
            ['pricing_type' => 'starting_from', 'price' => 2500],
            ['pricing_type' => 'starting_from', 'price' => 115000],
        ]);

        $createService([
            'name' => 'Cloud Security Configuration',
            'category' => 'cloud-server-network',
            'short_description' => 'Configure cloud security including IAM, security groups, encryption, and compliance.',
            'description' => 'Cloud security configuration including identity and access management, encryption setup, network security, logging, and compliance alignment.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '1-2 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Security configuration', 'IAM setup', 'Encryption config', 'Compliance report'],
            'features' => ['IAM configuration', 'Encryption setup', 'Network security', 'Logging & monitoring'],
            'tags' => ['cloud security', 'iam', 'encryption', 'compliance'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 1500],
            ['pricing_type' => 'starting_from', 'price' => 1900],
            ['pricing_type' => 'starting_from', 'price' => 85000],
        ]);

        $createService([
            'name' => 'Virtual Server Setup',
            'category' => 'cloud-server-network',
            'short_description' => 'Deploy and configure virtual servers with proper security and monitoring.',
            'description' => 'Virtual server deployment including VM provisioning, OS installation, security hardening, monitoring setup, and backup configuration.',
            'complexity_level' => 'standard',
            'estimated_completion' => '1-3 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Server deployment', 'OS configuration', 'Security hardening', 'Monitoring setup'],
            'features' => ['Multi-hypervisor support', 'OS hardening', 'Monitoring', 'Backup setup'],
            'tags' => ['virtual server', 'vm', 'deployment', 'configuration'],
        ], [
            ['pricing_type' => 'fixed', 'price' => 500],
            ['pricing_type' => 'fixed', 'price' => 650],
            ['pricing_type' => 'fixed', 'price' => 30000],
        ]);

        $createService([
            'name' => 'Linux Server Administration',
            'category' => 'cloud-server-network',
            'short_description' => 'Expert Linux server management including setup, security, optimization, and troubleshooting.',
            'description' => 'Professional Linux server administration covering Ubuntu, CentOS, Debian, and other distributions. Services include setup, configuration, security hardening, and ongoing management.',
            'complexity_level' => 'standard',
            'estimated_completion' => '1-5 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Server setup', 'Configuration', 'Security hardening', 'Documentation'],
            'features' => ['Multi-distro support', 'Security hardening', 'Performance tuning', 'Automation'],
            'tags' => ['linux', 'server', 'ubuntu', 'centos'],
        ], [
            ['pricing_type' => 'hourly', 'price' => 70],
            ['pricing_type' => 'hourly', 'price' => 85],
            ['pricing_type' => 'hourly', 'price' => 4000],
        ]);

        $createService([
            'name' => 'Windows Server Administration',
            'category' => 'cloud-server-network',
            'short_description' => 'Windows Server management including Active Directory, Group Policy, and IIS configuration.',
            'description' => 'Expert Windows Server administration including Active Directory management, Group Policy configuration, IIS setup, and server maintenance.',
            'complexity_level' => 'standard',
            'estimated_completion' => '1-5 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Server setup', 'AD configuration', 'Group Policy', 'Documentation'],
            'features' => ['Active Directory', 'Group Policy', 'IIS configuration', 'PowerShell automation'],
            'tags' => ['windows server', 'active directory', 'iis', 'group policy'],
        ], [
            ['pricing_type' => 'hourly', 'price' => 75],
            ['pricing_type' => 'hourly', 'price' => 95],
            ['pricing_type' => 'hourly', 'price' => 4500],
        ]);

        $createService([
            'name' => 'Server Monitoring Setup',
            'category' => 'cloud-server-network',
            'short_description' => 'Deploy comprehensive server monitoring with alerts, dashboards, and uptime tracking.',
            'description' => 'Complete server monitoring setup with Prometheus/Grafana or Zabbix, custom dashboards, alert rules, and uptime monitoring.',
            'complexity_level' => 'standard',
            'estimated_completion' => '2-5 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Monitoring deployment', 'Custom dashboards', 'Alert configuration', 'Uptime monitoring'],
            'features' => ['Real-time monitoring', 'Custom dashboards', 'Alert rules', 'Historical data'],
            'tags' => ['monitoring', 'grafana', 'prometheus', 'zabbix'],
        ], [
            ['pricing_type' => 'fixed', 'price' => 600],
            ['pricing_type' => 'fixed', 'price' => 750],
            ['pricing_type' => 'fixed', 'price' => 35000],
        ]);

        $createService([
            'name' => 'Network Setup',
            'category' => 'cloud-server-network',
            'short_description' => 'Design and deploy office network infrastructure including switches, routers, and access points.',
            'description' => 'Complete network setup service including network design, switch configuration, router setup, wireless access points, and VLAN configuration.',
            'complexity_level' => 'standard',
            'estimated_completion' => '2-5 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Network design', 'Equipment setup', 'VLAN configuration', 'Documentation'],
            'features' => ['Network design', 'Switch configuration', 'Wireless setup', 'VLAN management'],
            'tags' => ['network', 'infrastructure', 'switches', 'wifi'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 800],
            ['pricing_type' => 'starting_from', 'price' => 1000],
            ['pricing_type' => 'starting_from', 'price' => 45000],
        ]);

        $createService([
            'name' => 'VPN Setup',
            'category' => 'cloud-server-network',
            'short_description' => 'Configure secure VPN solutions for remote access and site-to-site connectivity.',
            'description' => 'VPN deployment and configuration including client VPN for remote workers, site-to-site VPN, and split tunneling configuration.',
            'complexity_level' => 'standard',
            'estimated_completion' => '1-3 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['VPN deployment', 'Client configuration', 'Site-to-site setup', 'Documentation'],
            'features' => ['Multiple VPN protocols', 'Client setup', 'Site-to-site', 'Split tunneling'],
            'tags' => ['vpn', 'remote access', 'security', 'connectivity'],
        ], [
            ['pricing_type' => 'fixed', 'price' => 400],
            ['pricing_type' => 'fixed', 'price' => 500],
            ['pricing_type' => 'fixed', 'price' => 22000],
        ]);

        $createService([
            'name' => 'Wi-Fi Infrastructure Setup',
            'category' => 'cloud-server-network',
            'short_description' => 'Enterprise Wi-Fi deployment with coverage planning, security, and management.',
            'description' => 'Professional Wi-Fi deployment including site survey, coverage planning, enterprise AP installation, security configuration, and central management.',
            'complexity_level' => 'standard',
            'estimated_completion' => '2-5 business days',
            'allows_custom_quote' => true,
            'deliverables' => ['Site survey', 'Wi-Fi design', 'AP installation', 'Security config', 'Management setup'],
            'features' => ['Coverage planning', 'Enterprise-grade APs', 'WPA3 security', 'Central management'],
            'tags' => ['wifi', 'wireless', 'enterprise', 'networking'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 600],
            ['pricing_type' => 'starting_from', 'price' => 750],
            ['pricing_type' => 'starting_from', 'price' => 35000],
        ]);

        // ══════════════════════════════════════════════════════════════════════════
        //  CATEGORY 6: BUSINESS TECHNOLOGY & AUTOMATION (~12)
        // ══════════════════════════════════════════════════════════════════════════

        $createService([
            'name' => 'CRM Setup',
            'category' => 'business-technology',
            'short_description' => 'Set up and configure CRM platforms like HubSpot, Salesforce, or custom solutions.',
            'description' => 'Complete CRM setup including platform selection, configuration, data migration, user training, and integration with existing tools.',
            'complexity_level' => 'standard',
            'estimated_completion' => '1-3 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['CRM configuration', 'Data migration', 'User training', 'Integration setup'],
            'features' => ['Multi-platform support', 'Data migration', 'Custom fields', 'Reporting setup'],
            'tags' => ['crm', 'hubspot', 'salesforce', 'customer management'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 1000],
            ['pricing_type' => 'starting_from', 'price' => 1250],
            ['pricing_type' => 'starting_from', 'price' => 60000],
        ]);

        $createService([
            'name' => 'CRM Customization',
            'category' => 'business-technology',
            'short_description' => 'Customize CRM workflows, automations, reports, and integrations for your business process.',
            'description' => 'CRM customization including workflow automation, custom fields, report creation, dashboard setup, and third-party integrations.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '2-6 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Custom workflows', 'Automation setup', 'Custom reports', 'Integration development'],
            'features' => ['Workflow automation', 'Custom dashboards', 'API integrations', 'Custom reports'],
            'tags' => ['crm', 'customization', 'automation', 'workflows'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 1500],
            ['pricing_type' => 'starting_from', 'price' => 1900],
            ['pricing_type' => 'starting_from', 'price' => 85000],
        ]);

        $createService([
            'name' => 'ERP Implementation',
            'category' => 'business-technology',
            'short_description' => 'Implement ERP systems to streamline business operations across finance, inventory, and HR.',
            'description' => 'End-to-end ERP implementation including requirements analysis, system configuration, data migration, user training, and go-live support.',
            'complexity_level' => 'enterprise',
            'estimated_completion' => '8-24 weeks',
            'allows_custom_quote' => true,
            'is_featured' => true,
            'deliverables' => ['ERP deployment', 'Data migration', 'User training', 'Go-live support', 'Documentation'],
            'features' => ['Multi-module support', 'Data migration', 'User training', 'Post-go-live support'],
            'tags' => ['erp', 'business systems', 'finance', 'inventory'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 10000],
            ['pricing_type' => 'starting_from', 'price' => 12000],
            ['pricing_type' => 'starting_from', 'price' => 500000],
        ]);

        $createService([
            'name' => 'Business Process Automation',
            'category' => 'business-technology',
            'short_description' => 'Automate repetitive business processes to improve efficiency and reduce manual work.',
            'description' => 'Analyze and automate business processes using tools like Zapier, Power Automate, or custom solutions to eliminate manual tasks and reduce errors.',
            'complexity_level' => 'standard',
            'estimated_completion' => '2-6 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Process analysis', 'Automation design', 'Implementation', 'Testing', 'Training'],
            'features' => ['Process mapping', 'Multi-tool support', 'Error handling', 'Monitoring'],
            'tags' => ['automation', 'business process', 'workflow', 'zapier'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 1200],
            ['pricing_type' => 'starting_from', 'price' => 1500],
            ['pricing_type' => 'starting_from', 'price' => 70000],
        ]);

        $createService([
            'name' => 'AI Automation',
            'category' => 'business-technology',
            'short_description' => 'Implement AI-powered automation for document processing, data entry, and decision support.',
            'description' => 'Deploy AI automation solutions including document processing, intelligent data extraction, predictive analytics, and decision support systems.',
            'complexity_level' => 'enterprise',
            'estimated_completion' => '6-16 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['AI solution design', 'Model training', 'Integration', 'Monitoring dashboard'],
            'features' => ['Document processing', 'Data extraction', 'Predictive analytics', 'Decision support'],
            'tags' => ['ai', 'machine learning', 'automation', 'intelligence'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 5000],
            ['pricing_type' => 'starting_from', 'price' => 6000],
            ['pricing_type' => 'starting_from', 'price' => 275000],
        ]);

        $createService([
            'name' => 'AI Chatbot Integration',
            'category' => 'business-technology',
            'short_description' => 'Integrate AI chatbots for customer support, lead qualification, and internal assistance.',
            'description' => 'Custom AI chatbot development and integration for customer support, lead qualification, FAQ handling, and internal helpdesk automation.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '4-8 weeks',
            'allows_custom_quote' => true,
            'is_featured' => true,
            'deliverables' => ['Chatbot design', 'Knowledge base setup', 'Integration', 'Training', 'Analytics'],
            'features' => ['Natural language processing', 'Multi-channel support', 'Knowledge base', 'Escalation to humans'],
            'tags' => ['chatbot', 'ai', 'customer support', 'automation'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 2500],
            ['pricing_type' => 'starting_from', 'price' => 3000],
            ['pricing_type' => 'starting_from', 'price' => 140000],
        ]);

        $createService([
            'name' => 'Workflow Automation',
            'category' => 'business-technology',
            'short_description' => 'Design and implement automated workflows for approvals, notifications, and data flow.',
            'description' => 'Custom workflow automation design and implementation using modern platforms. Automate approvals, notifications, data routing, and inter-system communication.',
            'complexity_level' => 'standard',
            'estimated_completion' => '2-4 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Workflow design', 'Implementation', 'Testing', 'Documentation', 'Training'],
            'features' => ['Custom workflows', 'Approval chains', 'Conditional logic', 'Integration support'],
            'tags' => ['workflow', 'automation', 'approvals', 'processes'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 1000],
            ['pricing_type' => 'starting_from', 'price' => 1250],
            ['pricing_type' => 'starting_from', 'price' => 60000],
        ]);

        $createService([
            'name' => 'Reporting Dashboard Development',
            'category' => 'business-technology',
            'short_description' => 'Build custom reporting dashboards for real-time business intelligence and KPI tracking.',
            'description' => 'Custom dashboard development for real-time KPI tracking, business intelligence, data visualization, and automated reporting.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '3-8 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Custom dashboard', 'Data connections', 'KPI widgets', 'Automated reports', 'Mobile view'],
            'features' => ['Real-time data', 'Interactive charts', 'Drill-down capability', 'Export options'],
            'tags' => ['dashboard', 'reporting', 'business intelligence', 'kpi'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 2000],
            ['pricing_type' => 'starting_from', 'price' => 2500],
            ['pricing_type' => 'starting_from', 'price' => 115000],
        ]);

        $createService([
            'name' => 'Data Integration',
            'category' => 'business-technology',
            'short_description' => 'Connect and synchronize data across multiple business systems and databases.',
            'description' => 'Data integration services including ETL pipelines, API connections, database synchronization, and data quality management across systems.',
            'complexity_level' => 'advanced',
            'estimated_completion' => '3-8 weeks',
            'allows_custom_quote' => true,
            'deliverables' => ['Integration architecture', 'ETL pipelines', 'Data mapping', 'Quality validation'],
            'features' => ['Multi-source integration', 'Real-time sync', 'Error handling', 'Data quality checks'],
            'tags' => ['data integration', 'etl', 'synchronization', 'api'],
        ], [
            ['pricing_type' => 'starting_from', 'price' => 2000],
            ['pricing_type' => 'starting_from', 'price' => 2500],
            ['pricing_type' => 'starting_from', 'price' => 115000],
        ]);

        // ─── COUNT SERVICES ──────────────────────────────────────────────────────
        $totalServices = Service::count();
        $totalCategories = ServiceCategory::count();
        $totalPrices = ServiceCountryPrice::count();

        $this->command->info("✅ Service catalogue seeded successfully!");
        $this->command->info("   📂 Categories: {$totalCategories}");
        $this->command->info("   🛠️  Services: {$totalServices}");
        $this->command->info("   💰 Country prices: {$totalPrices}");
    }
}
