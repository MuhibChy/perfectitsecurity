<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\ServiceCategory;
use App\Models\Service;
use App\Models\ServiceCountryPrice;

class ExpandedServiceCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        $countries = Country::all();
        $uk = $countries->where('code', 'UK')->first()->id;
        $us = $countries->where('code', 'US')->first()->id;
        $bd = $countries->where('code', 'BD')->first()->id;

        // ─── NEW CATEGORIES ─────────────────────────────────────────────────────
        $newCats = [
            ServiceCategory::create(['name' => 'Microsoft 365 & Business IT', 'slug' => 'microsoft-365-business-it', 'description' => 'Microsoft 365 deployment, management, and business IT solutions for organizations of all sizes.', 'icon' => '🏢', 'color' => '#0078D4', 'sort_order' => 7]),
            ServiceCategory::create(['name' => 'Data & Business Intelligence', 'slug' => 'data-business-intelligence', 'description' => 'Data analytics, business intelligence, database management, and data-driven decision support.', 'icon' => '📊', 'color' => '#FF6B35', 'sort_order' => 8]),
            ServiceCategory::create(['name' => 'E-commerce & Online Business', 'slug' => 'ecommerce-online-business', 'description' => 'Online store setup, payment integration, marketplace development, and e-commerce optimization.', 'icon' => '🛒', 'color' => '#10B981', 'sort_order' => 9]),
            ServiceCategory::create(['name' => 'IT Training & Consulting', 'slug' => 'it-training-consulting', 'description' => 'IT training programs, technical consulting, digital transformation, and technology strategy.', 'icon' => '🎓', 'color' => '#8B5CF6', 'sort_order' => 10]),
        ];

        $createService = function (array $data, array $prices) use ($uk, $us, $bd) {
            $cat = ServiceCategory::where('slug', $data['category'])->first();
            if (!$cat) return null;
            unset($data['category']);

            if (!isset($data['slug'])) {
                $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
            }
            $data['category_id'] = $cat->id;
            $data['price_type'] = $prices[0]['pricing_type'] ?? 'custom';
            $data['starting_price'] = $prices[0]['price'] ?? 0;

            $service = Service::create($data);

            foreach ([
                ['country_id' => $uk, 'pricing_type' => $prices[0]['pricing_type'], 'price' => $prices[0]['price']],
                ['country_id' => $us, 'pricing_type' => $prices[1]['pricing_type'], 'price' => $prices[1]['price']],
                ['country_id' => $bd, 'pricing_type' => $prices[2]['pricing_type'], 'price' => $prices[2]['price']],
            ] as $cp) {
                ServiceCountryPrice::create(array_merge($cp, ['service_id' => $service->id, 'is_active' => true]));
            }

            return $service;
        };

        // ══════════════════════════════════════════════════════════════════════════
        //  CATEGORY 7: MICROSOFT 365 & BUSINESS IT
        // ══════════════════════════════════════════════════════════════════════════

        $createService([
            'name' => 'Microsoft 365 Deployment', 'category' => 'microsoft-365-business-it',
            'short_description' => 'Complete Microsoft 365 deployment including Exchange, Teams, SharePoint, and OneDrive.',
            'description' => 'End-to-end Microsoft 365 deployment covering tenant setup, domain configuration, mailbox migration, Teams deployment, SharePoint configuration, and user training.',
            'complexity_level' => 'standard', 'estimated_completion' => '1-2 weeks', 'allows_custom_quote' => true, 'is_featured' => true,
            'deliverables' => ['Tenant setup', 'Email migration', 'Teams deployment', 'User training'],
            'features' => ['Exchange Online', 'Teams', 'SharePoint', 'OneDrive'],
        ], [['pricing_type' => 'starting_from', 'price' => 1500], ['pricing_type' => 'starting_from', 'price' => 1900], ['pricing_type' => 'starting_from', 'price' => 85000]]);

        $createService([
            'name' => 'Microsoft 365 Administration', 'category' => 'microsoft-365-business-it',
            'short_description' => 'Ongoing M365 administration including user management, security, and compliance.',
            'complexity_level' => 'standard', 'estimated_completion' => 'Ongoing', 'allows_custom_quote' => true,
            'deliverables' => ['User management', 'Security config', 'Compliance setup', 'Monthly reports'],
            'features' => ['Azure AD', 'Conditional Access', 'DLP', 'Retention policies'],
        ], [['pricing_type' => 'monthly', 'price' => 500], ['pricing_type' => 'monthly', 'price' => 650], ['pricing_type' => 'monthly', 'price' => 30000]]);

        $createService([
            'name' => 'Email Migration to M365', 'category' => 'microsoft-365-business-it',
            'short_description' => 'Migrate email from any platform to Microsoft 365 Exchange Online.',
            'complexity_level' => 'standard', 'estimated_completion' => '3-7 business days', 'allows_custom_quote' => true,
            'deliverables' => ['Migration plan', 'Data transfer', 'DNS configuration', 'Verification'],
            'features' => ['IMAP/POP migration', 'Calendar migration', 'Contact migration', 'Zero downtime'],
        ], [['pricing_type' => 'starting_from', 'price' => 800], ['pricing_type' => 'starting_from', 'price' => 1000], ['pricing_type' => 'starting_from', 'price' => 45000]]);

        $createService([
            'name' => 'Teams Phone System Setup', 'category' => 'microsoft-365-business-it',
            'short_description' => 'Deploy Microsoft Teams Phone System with calling plans and auto-attendant.',
            'complexity_level' => 'advanced', 'estimated_completion' => '1-2 weeks', 'allows_custom_quote' => true,
            'deliverables' => ['Teams Phone setup', 'Call plans', 'Auto-attendant', 'User training'],
            'features' => ['Direct Routing', 'Calling Plans', 'Auto-attendant', 'Call queues'],
        ], [['pricing_type' => 'starting_from', 'price' => 2000], ['pricing_type' => 'starting_from', 'price' => 2500], ['pricing_type' => 'starting_from', 'price' => 115000]]);

        $createService([
            'name' => 'SharePoint Development', 'category' => 'microsoft-365-business-it',
            'short_description' => 'Custom SharePoint sites, workflows, and intranet portals.',
            'complexity_level' => 'advanced', 'estimated_completion' => '4-8 weeks', 'allows_custom_quote' => true,
            'deliverables' => ['Custom site', 'Workflows', 'Document libraries', 'Training'],
            'features' => ['Power Automate', 'Custom web parts', 'Document management', 'Search configuration'],
        ], [['pricing_type' => 'starting_from', 'price' => 3000], ['pricing_type' => 'starting_from', 'price' => 3800], ['pricing_type' => 'starting_from', 'price' => 170000]]);

        $createService([
            'name' => 'Azure AD & Identity Management', 'category' => 'microsoft-365-business-it',
            'short_description' => 'Configure Azure Active Directory, SSO, MFA, and conditional access policies.',
            'complexity_level' => 'advanced', 'estimated_completion' => '3-7 business days', 'allows_custom_quote' => true,
            'deliverables' => ['Azure AD setup', 'SSO configuration', 'MFA deployment', 'Conditional access'],
            'features' => ['Single Sign-On', 'Multi-Factor Auth', 'Conditional Access', 'Hybrid identity'],
        ], [['pricing_type' => 'starting_from', 'price' => 1200], ['pricing_type' => 'starting_from', 'price' => 1500], ['pricing_type' => 'starting_from', 'price' => 70000]]);

        $createService([
            'name' => 'Microsoft 365 Security & Compliance', 'category' => 'microsoft-365-business-it',
            'short_description' => 'Implement M365 security features including Defender, DLP, and compliance center.',
            'complexity_level' => 'advanced', 'estimated_completion' => '1-2 weeks', 'allows_custom_quote' => true,
            'deliverables' => ['Security assessment', 'Defender setup', 'DLP policies', 'Compliance config'],
            'features' => ['Microsoft Defender', 'DLP', 'Information Protection', 'Compliance Manager'],
        ], [['pricing_type' => 'starting_from', 'price' => 1800], ['pricing_type' => 'starting_from', 'price' => 2200], ['pricing_type' => 'starting_from', 'price' => 100000]]);

        $createService([
            'name' => 'Business IT Consultation', 'category' => 'microsoft-365-business-it',
            'short_description' => 'Expert IT consultation for technology strategy and digital transformation.',
            'complexity_level' => 'standard', 'estimated_completion' => '1-5 days', 'allows_custom_quote' => true, 'is_featured' => true,
            'deliverables' => ['IT assessment', 'Strategy roadmap', 'Budget planning', 'Vendor recommendations'],
            'features' => ['Technology audit', 'Gap analysis', 'ROI assessment', 'Implementation planning'],
        ], [['pricing_type' => 'hourly', 'price' => 150], ['pricing_type' => 'hourly', 'price' => 190], ['pricing_type' => 'hourly', 'price' => 9000]]);

        // ══════════════════════════════════════════════════════════════════════════
        //  CATEGORY 8: DATA & BUSINESS INTELLIGENCE
        // ══════════════════════════════════════════════════════════════════════════

        $createService([
            'name' => 'Business Intelligence Dashboard', 'category' => 'data-business-intelligence',
            'short_description' => 'Interactive BI dashboards with real-time data visualization and KPI tracking.',
            'complexity_level' => 'advanced', 'estimated_completion' => '3-6 weeks', 'allows_custom_quote' => true, 'is_featured' => true,
            'deliverables' => ['Custom dashboard', 'Data connections', 'KPI widgets', 'Automated reports'],
            'features' => ['Power BI / Tableau', 'Real-time data', 'Drill-down', 'Mobile view'],
        ], [['pricing_type' => 'starting_from', 'price' => 3000], ['pricing_type' => 'starting_from', 'price' => 3800], ['pricing_type' => 'starting_from', 'price' => 170000]]);

        $createService([
            'name' => 'Data Warehouse Setup', 'category' => 'data-business-intelligence',
            'short_description' => 'Design and build data warehouses for consolidated business reporting.',
            'complexity_level' => 'enterprise', 'estimated_completion' => '6-12 weeks', 'allows_custom_quote' => true,
            'deliverables' => ['Data model', 'ETL pipelines', 'Warehouse setup', 'Documentation'],
            'features' => ['Star schema', 'Data quality', 'Incremental loading', 'Historical tracking'],
        ], [['pricing_type' => 'starting_from', 'price' => 8000], ['pricing_type' => 'starting_from', 'price' => 10000], ['pricing_type' => 'starting_from', 'price' => 450000]]);

        $createService([
            'name' => 'Power BI Development', 'category' => 'data-business-intelligence',
            'short_description' => 'Custom Power BI reports, dashboards, and data modeling.',
            'complexity_level' => 'standard', 'estimated_completion' => '2-4 weeks', 'allows_custom_quote' => true,
            'deliverables' => ['Power BI reports', 'Data model', 'DAX measures', 'Training'],
            'features' => ['Interactive reports', 'Paginated reports', 'Row-level security', 'Gateway setup'],
        ], [['pricing_type' => 'starting_from', 'price' => 2000], ['pricing_type' => 'starting_from', 'price' => 2500], ['pricing_type' => 'starting_from', 'price' => 115000]]);

        $createService([
            'name' => 'Database Design & Development', 'category' => 'data-business-intelligence',
            'short_description' => 'Custom database design, optimization, and development for MySQL, PostgreSQL, SQL Server.',
            'complexity_level' => 'advanced', 'estimated_completion' => '2-6 weeks', 'allows_custom_quote' => true,
            'deliverables' => ['Database schema', 'Stored procedures', 'Indexes', 'Documentation'],
            'features' => ['Schema design', 'Query optimization', 'Data migration', 'Backup strategy'],
        ], [['pricing_type' => 'hourly', 'price' => 80], ['pricing_type' => 'hourly', 'price' => 100], ['pricing_type' => 'hourly', 'price' => 5000]]);

        $createService([
            'name' => 'Data Migration Services', 'category' => 'data-business-intelligence',
            'short_description' => 'Safe data migration between databases, platforms, and cloud services.',
            'complexity_level' => 'advanced', 'estimated_completion' => '1-4 weeks', 'allows_custom_quote' => true,
            'deliverables' => ['Migration plan', 'Data mapping', 'Transfer execution', 'Validation report'],
            'features' => ['Schema migration', 'Data cleansing', 'Validation', 'Rollback plan'],
        ], [['pricing_type' => 'starting_from', 'price' => 2500], ['pricing_type' => 'starting_from', 'price' => 3000], ['pricing_type' => 'starting_from', 'price' => 140000]]);

        $createService([
            'name' => 'Excel & Spreadsheet Automation', 'category' => 'data-business-intelligence',
            'short_description' => 'Automate Excel workflows, macros, and data processing with VBA and Power Query.',
            'complexity_level' => 'basic', 'estimated_completion' => '1-5 business days', 'allows_custom_quote' => true,
            'deliverables' => ['Automated spreadsheets', 'Macros', 'Power Query setups', 'Documentation'],
            'features' => ['VBA macros', 'Power Query', 'Data validation', 'Template creation'],
        ], [['pricing_type' => 'hourly', 'price' => 50], ['pricing_type' => 'hourly', 'price' => 65], ['pricing_type' => 'hourly', 'price' => 2500]]);

        // ══════════════════════════════════════════════════════════════════════════
        //  CATEGORY 9: E-COMMERCE & ONLINE BUSINESS
        // ══════════════════════════════════════════════════════════════════════════

        $createService([
            'name' => 'Shopify Store Setup', 'category' => 'ecommerce-online-business',
            'short_description' => 'Professional Shopify store setup with theme customization and product configuration.',
            'complexity_level' => 'standard', 'estimated_completion' => '1-2 weeks', 'allows_custom_quote' => true, 'is_featured' => true,
            'deliverables' => ['Store setup', 'Theme customization', 'Product listing', 'Payment setup'],
            'features' => ['Custom theme', 'Product catalog', 'Payment gateway', 'Shipping setup'],
        ], [['pricing_type' => 'starting_from', 'price' => 1200], ['pricing_type' => 'starting_from', 'price' => 1500], ['pricing_type' => 'starting_from', 'price' => 70000]]);

        $createService([
            'name' => 'WooCommerce Development', 'category' => 'ecommerce-online-business',
            'short_description' => 'Custom WooCommerce store development with WordPress integration.',
            'complexity_level' => 'standard', 'estimated_completion' => '2-4 weeks', 'allows_custom_quote' => true,
            'deliverables' => ['WooCommerce setup', 'Custom theme', 'Plugin configuration', 'Training'],
            'features' => ['Product management', 'Payment gateways', 'Shipping zones', 'Tax configuration'],
        ], [['pricing_type' => 'starting_from', 'price' => 1500], ['pricing_type' => 'starting_from', 'price' => 1900], ['pricing_type' => 'starting_from', 'price' => 85000]]);

        $createService([
            'name' => 'Payment Gateway Integration', 'category' => 'ecommerce-online-business',
            'short_description' => 'Integrate Stripe, PayPal, Square, and other payment gateways.',
            'complexity_level' => 'standard', 'estimated_completion' => '1-3 business days', 'allows_custom_quote' => true,
            'deliverables' => ['Gateway setup', 'Checkout integration', 'Testing', 'Documentation'],
            'features' => ['Stripe', 'PayPal', 'Square', 'Multi-currency'],
        ], [['pricing_type' => 'fixed', 'price' => 500], ['pricing_type' => 'fixed', 'price' => 650], ['pricing_type' => 'fixed', 'price' => 30000]]);

        $createService([
            'name' => 'Amazon Seller Account Setup', 'category' => 'ecommerce-online-business',
            'short_description' => 'Set up and optimize your Amazon seller account for FBA or FBM.',
            'complexity_level' => 'standard', 'estimated_completion' => '1-2 weeks', 'allows_custom_quote' => true,
            'deliverables' => ['Account setup', 'Product listings', 'FBA configuration', 'PPC campaigns'],
            'features' => ['FBA/FBM setup', 'Listing optimization', 'A+ content', 'Advertising'],
        ], [['pricing_type' => 'starting_from', 'price' => 800], ['pricing_type' => 'starting_from', 'price' => 1000], ['pricing_type' => 'starting_from', 'price' => 45000]]);

        $createService([
            'name' => 'E-commerce SEO', 'category' => 'ecommerce-online-business',
            'short_description' => 'SEO optimization specifically for online stores and product pages.',
            'complexity_level' => 'standard', 'estimated_completion' => '2-4 weeks', 'allows_custom_quote' => true,
            'deliverables' => ['Product SEO', 'Category optimization', 'Schema markup', 'Technical audit'],
            'features' => ['Product schema', 'Category structure', 'Image optimization', 'Site speed'],
        ], [['pricing_type' => 'starting_from', 'price' => 800], ['pricing_type' => 'starting_from', 'price' => 1000], ['pricing_type' => 'starting_from', 'price' => 45000]]);

        $createService([
            'name' => 'Inventory Management System', 'category' => 'ecommerce-online-business',
            'short_description' => 'Custom inventory management with stock tracking, alerts, and multi-channel sync.',
            'complexity_level' => 'advanced', 'estimated_completion' => '4-8 weeks', 'allows_custom_quote' => true,
            'deliverables' => ['Custom system', 'Stock tracking', 'Alerts', 'Multi-channel sync'],
            'features' => ['Real-time stock', 'Low stock alerts', 'Barcode scanning', 'Channel sync'],
        ], [['pricing_type' => 'starting_from', 'price' => 4000], ['pricing_type' => 'starting_from', 'price' => 5000], ['pricing_type' => 'starting_from', 'price' => 225000]]);

        // ══════════════════════════════════════════════════════════════════════════
        //  CATEGORY 10: IT TRAINING & CONSULTING
        // ══════════════════════════════════════════════════════════════════════════

        $createService([
            'name' => 'Cybersecurity Awareness Training', 'category' => 'it-training-consulting',
            'short_description' => 'Comprehensive cybersecurity training for employees at all levels.',
            'complexity_level' => 'basic', 'estimated_completion' => '1-5 days', 'allows_custom_quote' => true, 'is_featured' => true,
            'deliverables' => ['Training materials', 'Live sessions', 'Assessment', 'Certificate'],
            'features' => ['Phishing awareness', 'Password security', 'Social engineering', 'Data protection'],
        ], [['pricing_type' => 'starting_from', 'price' => 500], ['pricing_type' => 'starting_from', 'price' => 650], ['pricing_type' => 'starting_from', 'price' => 30000]]);

        $createService([
            'name' => 'Microsoft 365 User Training', 'category' => 'it-training-consulting',
            'short_description' => 'Training on Outlook, Teams, SharePoint, Excel, and other M365 apps.',
            'complexity_level' => 'basic', 'estimated_completion' => '1-3 days', 'allows_custom_quote' => true,
            'deliverables' => ['Custom curriculum', 'Hands-on labs', 'Reference guides', 'Q&A sessions'],
            'features' => ['Outlook', 'Teams', 'Excel', 'SharePoint'],
        ], [['pricing_type' => 'hourly', 'price' => 100], ['pricing_type' => 'hourly', 'price' => 125], ['pricing_type' => 'hourly', 'price' => 6000]]);

        $createService([
            'name' => 'IT Infrastructure Consulting', 'category' => 'it-training-consulting',
            'short_description' => 'Expert consulting on IT infrastructure design, optimization, and modernization.',
            'complexity_level' => 'advanced', 'estimated_completion' => '1-4 weeks', 'allows_custom_quote' => true,
            'deliverables' => ['Infrastructure audit', 'Architecture design', 'Migration plan', 'ROI analysis'],
            'features' => ['Server assessment', 'Network design', 'Cloud strategy', 'Cost optimization'],
        ], [['pricing_type' => 'hourly', 'price' => 175], ['pricing_type' => 'hourly', 'price' => 220], ['pricing_type' => 'hourly', 'price' => 10000]]);

        $createService([
            'name' => 'Digital Transformation Consulting', 'category' => 'it-training-consulting',
            'short_description' => 'Guide your organization through digital transformation initiatives.',
            'complexity_level' => 'enterprise', 'estimated_completion' => '4-12 weeks', 'allows_custom_quote' => true, 'is_featured' => true,
            'deliverables' => ['Assessment', 'Strategy', 'Roadmap', 'Implementation support'],
            'features' => ['Process analysis', 'Technology selection', 'Change management', 'ROI tracking'],
        ], [['pricing_type' => 'starting_from', 'price' => 5000], ['pricing_type' => 'starting_from', 'price' => 6000], ['pricing_type' => 'starting_from', 'price' => 275000]]);

        $createService([
            'name' => 'ISO 27001 Implementation', 'category' => 'it-training-consulting',
            'short_description' => 'Implement ISO 27001 Information Security Management System from scratch.',
            'complexity_level' => 'enterprise', 'estimated_completion' => '3-6 months', 'allows_custom_quote' => true,
            'deliverables' => ['ISMS setup', 'Policies', 'Risk assessment', 'Internal audit'],
            'features' => ['Gap analysis', 'Policy development', 'Staff training', 'Certification prep'],
        ], [['pricing_type' => 'starting_from', 'price' => 8000], ['pricing_type' => 'starting_from', 'price' => 10000], ['pricing_type' => 'starting_from', 'price' => 450000]]);

        $createService([
            'name' => 'GDPR Compliance Consulting', 'category' => 'it-training-consulting',
            'short_description' => 'Achieve and maintain GDPR compliance with expert guidance.',
            'complexity_level' => 'advanced', 'estimated_completion' => '4-8 weeks', 'allows_custom_quote' => true,
            'deliverables' => ['Data audit', 'Privacy policy', 'DPA templates', 'Staff training'],
            'features' => ['Data mapping', 'Privacy impact assessment', 'Consent management', 'Breach procedures'],
        ], [['pricing_type' => 'starting_from', 'price' => 3000], ['pricing_type' => 'starting_from', 'price' => 3800], ['pricing_type' => 'starting_from', 'price' => 170000]]);

        $total = Service::count();
        $totalCats = ServiceCategory::count();
        $totalPrices = ServiceCountryPrice::count();

        $this->command->info("✅ Expanded catalogue complete!");
        $this->command->info("   📂 Categories: {$totalCats}");
        $this->command->info("   🛠️  Services: {$total}");
        $this->command->info("   💰 Country prices: {$totalPrices}");
    }
}
