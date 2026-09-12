<?php

namespace Database\Seeders;

use App\Models\UsefulLink;
use Illuminate\Database\Seeder;

class UsefulLinksSeeder extends Seeder
{
    public function run(): void
    {
        $links = [
            [
                'title' => 'Shodan',
                'url' => 'https://www.shodan.io/',
                'description' => 'The world\'s first search engine for internet-connected devices. Discover what\'s exposed on the internet.',
                'category' => 'monitoring',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Criminal IP',
                'url' => 'https://www.criminalip.io/',
                'description' => 'AI-based cyber threat intelligence search engine. Analyze IP addresses, domains, and URLs for potential threats.',
                'category' => 'cybersecurity',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'WiGLE',
                'url' => 'https://wigle.net/',
                'description' => 'Wireless network mapping and statistics. A comprehensive database of wireless networks worldwide.',
                'category' => 'monitoring',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'DeHashed',
                'url' => 'https://www.dehashed.com/',
                'description' => 'Search engine for leaked data. Find compromised credentials, breaches, and sensitive information.',
                'category' => 'cybersecurity',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'title' => 'Kaspersky Cybermap',
                'url' => 'https://cybermap.kaspersky.com/',
                'description' => 'Real-time cyber threat map showing attacks, threats, and vulnerabilities across the globe. Monitor live cybersecurity events.',
                'category' => 'cybersecurity',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'title' => 'ANY.RUN',
                'url' => 'https://any.run/',
                'description' => 'Interactive online malware analysis sandbox. Analyze suspicious files, URLs, and IP addresses in real time.',
                'category' => 'tools',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'title' => 'GreyNoise',
                'url' => 'https://www.greynoise.io/',
                'description' => 'Internet background noise analysis. Identify mass scanning, crawling, and brute-force activity on your network.',
                'category' => 'cybersecurity',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'title' => 'GreyNoise Visualizer',
                'url' => 'https://viz.greynoise.io/',
                'description' => 'Visual interface for exploring GreyNoise data. See IP address behavior patterns and threat intelligence.',
                'category' => 'monitoring',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($links as $link) {
            UsefulLink::updateOrCreate(
                ['url' => $link['url']],
                $link
            );
        }
    }
}
