<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\ServiceCategory;
use App\Models\Service;
use App\Models\ServiceCountryPrice;

class ExtraServicesSeeder extends Seeder
{
    public function run(): void
    {
        $uk = Country::where('code', 'UK')->first()->id;
        $us = Country::where('code', 'US')->first()->id;
        $bd = Country::where('code', 'BD')->first()->id;

        $extras = [
            // More Cybersecurity
            ['name' => 'DDoS Protection Setup', 'slug' => 'ddos-protection-setup', 'cat' => 'cybersecurity', 'desc' => 'Configure DDoS protection and mitigation for web applications and infrastructure.', 'short' => 'DDoS protection and mitigation setup.', 'pricing_type' => 'starting_from', 'uk' => 1200, 'us' => 1500, 'bd' => 70000],
            ['name' => 'SSL Certificate Management', 'slug' => 'ssl-certificate-management', 'cat' => 'cybersecurity', 'desc' => 'SSL certificate procurement, installation, and renewal management.', 'short' => 'SSL certificate setup and management.', 'pricing_type' => 'fixed', 'uk' => 200, 'us' => 250, 'bd' => 12000],
            ['name' => 'Penetration Testing Report', 'slug' => 'pentest-report', 'cat' => 'cybersecurity', 'desc' => 'Detailed penetration testing report with executive summary and remediation roadmap.', 'short' => 'Professional pentest reporting service.', 'pricing_type' => 'fixed', 'uk' => 500, 'us' => 650, 'bd' => 30000],
            ['name' => 'SOC Setup & Monitoring', 'slug' => 'soc-setup-monitoring', 'cat' => 'cybersecurity', 'desc' => 'Set up a Security Operations Center with 24/7 monitoring and incident response.', 'short' => 'Security Operations Center setup.', 'pricing_type' => 'starting_from', 'uk' => 5000, 'us' => 6000, 'bd' => 275000],

            // More Web Dev
            ['name' => 'React.js Development', 'slug' => 'reactjs-development', 'cat' => 'web-development', 'desc' => 'Custom React.js single-page application development.', 'short' => 'Modern React.js application development.', 'pricing_type' => 'starting_from', 'uk' => 3000, 'us' => 3800, 'bd' => 170000],
            ['name' => 'Vue.js Development', 'slug' => 'vuejs-development', 'cat' => 'web-development', 'desc' => 'Custom Vue.js application development with Vuex and Vue Router.', 'short' => 'Vue.js application development.', 'pricing_type' => 'starting_from', 'uk' => 3000, 'us' => 3800, 'bd' => 170000],
            ['name' => 'Node.js Backend Development', 'slug' => 'nodejs-backend', 'cat' => 'web-development', 'desc' => 'RESTful API and backend development with Node.js and Express.', 'short' => 'Node.js backend and API development.', 'pricing_type' => 'starting_from', 'uk' => 2500, 'us' => 3000, 'bd' => 140000],
            ['name' => 'Python Django Development', 'slug' => 'python-django', 'cat' => 'web-development', 'desc' => 'Full-stack web application development with Python Django.', 'short' => 'Django web application development.', 'pricing_type' => 'starting_from', 'uk' => 3500, 'us' => 4500, 'bd' => 200000],
            ['name' => 'Shopify Theme Customization', 'slug' => 'shopify-theme', 'cat' => 'web-development', 'desc' => 'Custom Shopify theme development and Liquid template customization.', 'short' => 'Custom Shopify theme work.', 'pricing_type' => 'starting_from', 'uk' => 800, 'us' => 1000, 'bd' => 45000],
            ['name' => 'Flutter Mobile App Development', 'slug' => 'flutter-mobile-app', 'cat' => 'web-development', 'desc' => 'Cross-platform mobile app development with Flutter for iOS and Android.', 'short' => 'Flutter cross-platform mobile apps.', 'pricing_type' => 'starting_from', 'uk' => 4000, 'us' => 5000, 'bd' => 225000],

            // More Digital Marketing
            ['name' => 'YouTube Marketing', 'slug' => 'youtube-marketing', 'cat' => 'digital-marketing', 'desc' => 'YouTube channel optimization, video SEO, and advertising campaigns.', 'short' => 'YouTube marketing and video SEO.', 'pricing_type' => 'starting_from', 'uk' => 600, 'us' => 750, 'bd' => 35000],
            ['name' => 'TikTok Marketing', 'slug' => 'tiktok-marketing', 'cat' => 'digital-marketing', 'desc' => 'TikTok content strategy, creation, and advertising for brand growth.', 'short' => 'TikTok marketing strategy.', 'pricing_type' => 'starting_from', 'uk' => 500, 'us' => 650, 'bd' => 30000],
            ['name' => 'Conversion Rate Optimization', 'slug' => 'cro-optimization', 'cat' => 'digital-marketing', 'desc' => 'Optimize website conversion rates through A/B testing and UX improvements.', 'short' => 'CRO through testing and UX.', 'pricing_type' => 'starting_from', 'uk' => 1000, 'us' => 1300, 'bd' => 60000],
            ['name' => 'Influencer Marketing Campaign', 'slug' => 'influencer-marketing', 'cat' => 'digital-marketing', 'desc' => 'Plan and execute influencer marketing campaigns across social platforms.', 'short' => 'Influencer campaign management.', 'pricing_type' => 'starting_from', 'uk' => 1500, 'us' => 1900, 'bd' => 85000],

            // More M365
            ['name' => 'OneDrive Migration', 'slug' => 'onedrive-migration', 'cat' => 'microsoft-365-business-it', 'desc' => 'Migrate file shares and cloud storage to OneDrive for Business.', 'short' => 'Migrate files to OneDrive.', 'pricing_type' => 'starting_from', 'uk' => 600, 'us' => 750, 'bd' => 35000],
            ['name' => 'Intune MDM Setup', 'slug' => 'intune-mdm', 'cat' => 'microsoft-365-business-it', 'desc' => 'Deploy Microsoft Intune for mobile device and application management.', 'short' => 'Microsoft Intune device management.', 'pricing_type' => 'starting_from', 'uk' => 1500, 'us' => 1900, 'bd' => 85000],

            // More Cloud
            ['name' => 'AWS Well-Architected Review', 'slug' => 'aws-well-architected', 'cat' => 'cloud-server-network', 'desc' => 'Review AWS infrastructure against the Well-Architected Framework.', 'short' => 'AWS architecture review.', 'pricing_type' => 'starting_from', 'uk' => 2000, 'us' => 2500, 'bd' => 115000],
            ['name' => 'Docker Containerization', 'slug' => 'docker-containerization', 'cat' => 'cloud-server-network', 'desc' => 'Containerize applications with Docker and set up orchestration.', 'short' => 'Docker container setup.', 'pricing_type' => 'starting_from', 'uk' => 1200, 'us' => 1500, 'bd' => 70000],
            ['name' => 'Kubernetes Deployment', 'slug' => 'kubernetes-deployment', 'cat' => 'cloud-server-network', 'desc' => 'Deploy and manage containerized applications with Kubernetes.', 'short' => 'Kubernetes cluster setup.', 'pricing_type' => 'starting_from', 'uk' => 3000, 'us' => 3800, 'bd' => 170000],

            // More Business Tech
            ['name' => 'Zapier Automation Setup', 'slug' => 'zapier-automation', 'cat' => 'business-technology', 'desc' => 'Set up automated workflows connecting your apps with Zapier.', 'short' => 'Zapier workflow automation.', 'pricing_type' => 'fixed', 'uk' => 400, 'us' => 500, 'bd' => 22000],
            ['name' => 'Power Automate Workflows', 'slug' => 'power-automate', 'cat' => 'business-technology', 'desc' => 'Build automated workflows with Microsoft Power Automate.', 'short' => 'Power Automate automation.', 'pricing_type' => 'starting_from', 'uk' => 800, 'us' => 1000, 'bd' => 45000],
            ['name' => 'ChatGPT Integration', 'slug' => 'chatgpt-integration', 'cat' => 'business-technology', 'desc' => 'Integrate OpenAI ChatGPT into your applications and workflows.', 'short' => 'AI chatbot integration.', 'pricing_type' => 'starting_from', 'uk' => 2000, 'us' => 2500, 'bd' => 115000],

            // More Data/BI
            ['name' => 'Tableau Dashboard Development', 'slug' => 'tableau-dashboard', 'cat' => 'data-business-intelligence', 'desc' => 'Custom Tableau dashboards and visualizations for business insights.', 'short' => 'Tableau BI dashboards.', 'pricing_type' => 'starting_from', 'uk' => 2500, 'us' => 3000, 'bd' => 140000],
            ['name' => 'SQL Query Optimization', 'slug' => 'sql-optimization', 'cat' => 'data-business-intelligence', 'desc' => 'Optimize slow SQL queries and improve database performance.', 'short' => 'Database query optimization.', 'pricing_type' => 'hourly', 'uk' => 80, 'us' => 100, 'bd' => 5000],
            ['name' => 'Data Cleansing Service', 'slug' => 'data-cleansing', 'cat' => 'data-business-intelligence', 'desc' => 'Clean, deduplicate, and standardize business data.', 'short' => 'Data quality improvement.', 'pricing_type' => 'starting_from', 'uk' => 1000, 'us' => 1300, 'bd' => 60000],

            // More E-commerce
            ['name' => 'Magento Development', 'slug' => 'magento-development', 'cat' => 'ecommerce-online-business', 'desc' => 'Custom Magento/Adobe Commerce store development.', 'short' => 'Magento e-commerce development.', 'pricing_type' => 'starting_from', 'uk' => 5000, 'us' => 6000, 'bd' => 275000],
            ['name' => 'Stripe Integration', 'slug' => 'stripe-integration', 'cat' => 'ecommerce-online-business', 'desc' => 'Integrate Stripe payment processing with subscription billing.', 'short' => 'Stripe payment integration.', 'pricing_type' => 'fixed', 'uk' => 600, 'us' => 750, 'bd' => 35000],

            // More Training
            ['name' => 'ITIL Framework Training', 'slug' => 'itil-training', 'cat' => 'it-training-consulting', 'desc' => 'ITIL 4 foundation and practitioner training for IT teams.', 'short' => 'ITIL training programs.', 'pricing_type' => 'starting_from', 'uk' => 1500, 'us' => 1900, 'bd' => 85000],
            ['name' => 'Cloud Computing Training', 'slug' => 'cloud-training', 'cat' => 'it-training-consulting', 'desc' => 'AWS, Azure, and GCP training for your technical team.', 'short' => 'Cloud platform training.', 'pricing_type' => 'hourly', 'uk' => 120, 'us' => 150, 'bd' => 7000],

            // More Managed IT
            ['name' => 'Antivirus Deployment', 'slug' => 'antivirus-deployment', 'cat' => 'managed-it-support', 'desc' => 'Deploy and manage enterprise antivirus across all endpoints.', 'short' => 'Enterprise antivirus setup.', 'pricing_type' => 'starting_from', 'uk' => 500, 'us' => 650, 'bd' => 30000],
            ['name' => 'CCTV & Surveillance Setup', 'slug' => 'cctv-surveillance', 'cat' => 'managed-it-support', 'desc' => 'IP camera installation and surveillance system configuration.', 'short' => 'CCTV installation service.', 'pricing_type' => 'starting_from', 'uk' => 1500, 'us' => 1900, 'bd' => 85000],
        ];

        foreach ($extras as $s) {
            $cat = ServiceCategory::where('slug', $s['cat'])->first();
            if (!$cat) continue;
            if (Service::where('slug', $s['slug'])->exists()) continue;

            $svc = Service::create([
                'name' => $s['name'],
                'slug' => $s['slug'],
                'category_id' => $cat->id,
                'short_description' => $s['short'],
                'description' => $s['desc'],
                'price_type' => $s['pricing_type'],
                'complexity_level' => 'standard',
                'estimated_completion' => 'Varies',
                'allows_custom_quote' => true,
                'is_active' => true,
            ]);

            ServiceCountryPrice::create(['service_id' => $svc->id, 'country_id' => $uk, 'pricing_type' => $s['pricing_type'], 'price' => $s['uk'], 'is_active' => true]);
            ServiceCountryPrice::create(['service_id' => $svc->id, 'country_id' => $us, 'pricing_type' => $s['pricing_type'], 'price' => $s['us'], 'is_active' => true]);
            ServiceCountryPrice::create(['service_id' => $svc->id, 'country_id' => $bd, 'pricing_type' => $s['pricing_type'], 'price' => $s['bd'], 'is_active' => true]);
        }

        $this->command->info('Extra services seeded!');
    }
}
