<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Service;
use App\Models\KbArticle;

class SitemapController extends Controller
{
    public function index()
    {
        $url = config('app.url', 'https://techsupport.com');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Static pages
        $staticPages = [
            ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => '/about', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => '/services', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => '/pricing', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => '/contact', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/industries', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/offices', 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => '/careers', 'priority' => '0.6', 'changefreq' => 'weekly'],
            ['loc' => '/case-studies', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/blog', 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => '/knowledge-base', 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => '/legal/privacy-policy', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => '/legal/terms-of-service', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => '/legal/cookie-policy', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => '/legal/refund-policy', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => '/legal/service-level-agreement', 'priority' => '0.4', 'changefreq' => 'yearly'],
        ];

        foreach ($staticPages as $page) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . $url . $page['loc'] . '</loc>' . "\n";
            $xml .= '    <changefreq>' . $page['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $page['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        // Dynamic service pages
        $services = Service::where('is_active', true)->select('slug', 'updated_at')->get();
        foreach ($services as $service) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . $url . '/services/' . $service->slug . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $service->updated_at->format('Y-m-d') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>0.7</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        // Dynamic blog pages
        $posts = BlogPost::where('status', 'published')->select('slug', 'updated_at')->get();
        foreach ($posts as $post) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . $url . '/blog/' . $post->slug . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $post->updated_at->format('Y-m-d') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>monthly</changefreq>' . "\n";
            $xml .= '    <priority>0.6</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        // Dynamic KB pages
        $articles = KbArticle::where('status', 'published')->select('slug', 'updated_at')->get();
        foreach ($articles as $article) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . $url . '/knowledge-base/' . $article->slug . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $article->updated_at->format('Y-m-d') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>monthly</changefreq>' . "\n";
            $xml .= '    <priority>0.5</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }
}
