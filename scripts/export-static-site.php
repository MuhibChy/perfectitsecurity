<?php

/**
 * Static Site Exporter for GitHub Pages
 * Generates standalone static HTML pages with CSS/JS assets from Laravel routes.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$outputDir = __DIR__ . '/../dist';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// 1. Collect all routes to export
$routes = [
    '/' => 'index.html',
    '/about' => 'about.html',
    '/pricing' => 'pricing.html',
    '/contact' => 'contact.html',
    '/services' => 'services.html',
    '/faq' => 'faq.html',
    '/careers' => 'careers.html',
    '/industries' => 'industries.html',
    '/offices' => 'offices.html',
    '/case-studies' => 'case-studies.html',
    '/portfolio' => 'portfolio.html',
    '/useful-links' => 'useful-links.html',
    '/blog' => 'blog.html',
    '/knowledge-base' => 'knowledge-base.html',
    '/get-quote' => 'get-quote.html',
    '/legal/privacy-policy' => 'legal/privacy-policy.html',
    '/legal/terms-of-service' => 'legal/terms-of-service.html',
    '/legal/cookie-policy' => 'legal/cookie-policy.html',
    '/legal/accessibility' => 'legal/accessibility.html',
    '/legal/refund-policy' => 'legal/refund-policy.html',
    '/legal/service-level-agreement' => 'legal/service-level-agreement.html',
    '/login' => 'login.html',
    '/register' => 'register.html',
];

// Add dynamic service pages
try {
    $services = App\Models\Service::pluck('slug')->all();
    foreach ($services as $slug) {
        $routes["/services/{$slug}"] = "services/{$slug}.html";
    }
} catch (\Throwable $e) {
    echo "Warning: could not query services: " . $e->getMessage() . "\n";
}

echo "Exporting " . count($routes) . " routes to {$outputDir}...\n";

// Ensure subdirectories exist
foreach (['services', 'legal'] as $sub) {
    $dir = $outputDir . '/' . $sub;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// 2. Render each page
foreach ($routes as $uri => $relativeFilePath) {
    try {
        $request = Illuminate\Http\Request::create($uri, 'GET');
        $response = $kernel->handle($request);
        
        if ($response->getStatusCode() !== 200) {
            echo "Skipping $uri (status {$response->getStatusCode()})\n";
            continue;
        }

        $html = $response->getContent();
        
        // Calculate relative prefix based on depth (e.g. services/slug.html -> '../')
        $depth = substr_count($relativeFilePath, '/');
        $prefix = $depth > 0 ? str_repeat('../', $depth) : './';

        // Rewrite localhost & absolute URLs to relative static paths
        $html = str_replace('http://localhost:8000/build/', $prefix . 'build/', $html);
        $html = str_replace('http://localhost/build/', $prefix . 'build/', $html);
        $html = str_replace('http://localhost:8000/favicon.ico', $prefix . 'favicon.ico', $html);
        $html = str_replace('http://localhost/favicon.ico', $prefix . 'favicon.ico', $html);
        $html = str_replace('href="http://localhost:8000"', 'href="' . $prefix . 'index.html"', $html);
        $html = str_replace('href="http://localhost:8000/"', 'href="' . $prefix . 'index.html"', $html);
        $html = str_replace('href="http://localhost"', 'href="' . $prefix . 'index.html"', $html);
        $html = str_replace('href="http://localhost/"', 'href="' . $prefix . 'index.html"', $html);

        // Rewrite public navigation links
        $linkReplacements = [
            'href="/"' => 'href="' . $prefix . 'index.html"',
            'href="/about"' => 'href="' . $prefix . 'about.html"',
            'href="/pricing"' => 'href="' . $prefix . 'pricing.html"',
            'href="/contact"' => 'href="' . $prefix . 'contact.html"',
            'href="/services"' => 'href="' . $prefix . 'services.html"',
            'href="/faq"' => 'href="' . $prefix . 'faq.html"',
            'href="/careers"' => 'href="' . $prefix . 'careers.html"',
            'href="/industries"' => 'href="' . $prefix . 'industries.html"',
            'href="/offices"' => 'href="' . $prefix . 'offices.html"',
            'href="/case-studies"' => 'href="' . $prefix . 'case-studies.html"',
            'href="/portfolio"' => 'href="' . $prefix . 'portfolio.html"',
            'href="/useful-links"' => 'href="' . $prefix . 'useful-links.html"',
            'href="/blog"' => 'href="' . $prefix . 'blog.html"',
            'href="/knowledge-base"' => 'href="' . $prefix . 'knowledge-base.html"',
            'href="/get-quote"' => 'href="' . $prefix . 'get-quote.html"',
            'href="/legal/privacy-policy"' => 'href="' . $prefix . 'legal/privacy-policy.html"',
            'href="/legal/terms-of-service"' => 'href="' . $prefix . 'legal/terms-of-service.html"',
            'href="/legal/cookie-policy"' => 'href="' . $prefix . 'legal/cookie-policy.html"',
            'href="/legal/accessibility"' => 'href="' . $prefix . 'legal/accessibility.html"',
            'href="/legal/refund-policy"' => 'href="' . $prefix . 'legal/refund-policy.html"',
            'href="/legal/service-level-agreement"' => 'href="' . $prefix . 'legal/service-level-agreement.html"',
            'href="/login"' => 'href="' . $prefix . 'login.html"',
            'href="/register"' => 'href="' . $prefix . 'register.html"',
            // Also handle full http://localhost:8000/... links if any
            'href="http://localhost:8000/about"' => 'href="' . $prefix . 'about.html"',
            'href="http://localhost:8000/pricing"' => 'href="' . $prefix . 'pricing.html"',
            'href="http://localhost:8000/contact"' => 'href="' . $prefix . 'contact.html"',
            'href="http://localhost:8000/services"' => 'href="' . $prefix . 'services.html"',
        ];

        foreach ($linkReplacements as $search => $replace) {
            $html = str_replace($search, $replace, $html);
        }

        // Regex replace /services/{slug} to services/{slug}.html
        $html = preg_replace_callback('/href=[\'"](?:\/|http:\/\/localhost:8000\/)services\/([a-zA-Z0-9\-_]+)[\'"]/', function ($m) use ($prefix) {
            return 'href="' . $prefix . 'services/' . $m[1] . '.html"';
        }, $html);

        // Replace any remaining root-relative build/asset links
        $html = str_replace('="/build/', '="' . $prefix . 'build/', $html);
        $html = str_replace('="/favicon.ico"', '="' . $prefix . 'favicon.ico"', $html);
        $html = str_replace('http://localhost/', $prefix, $html);
        $html = str_replace('http://localhost', $prefix . 'index.html', $html);

        // Make dummy action on forms for static hosting
        $html = preg_replace('/action=[\'"][^\'"]*\/contact[\'"]/', 'action="javascript:alert(\'Thank you! Your message has been received (Design Demo Mode).\');"', $html);
        $html = preg_replace('/action=[\'"][^\'"]*\/login[\'"]/', 'action="javascript:alert(\'Design demo mode: Authentication is simulated.\');"', $html);
        $html = preg_replace('/action=[\'"][^\'"]*\/register[\'"]/', 'action="javascript:alert(\'Design demo mode: Registration is simulated.\');"', $html);

        $dest = $outputDir . '/' . $relativeFilePath;
        file_put_contents($dest, $html);
        echo "✓ Exported: $relativeFilePath\n";
    } catch (\Throwable $e) {
        echo "✗ Failed to export $uri: " . $e->getMessage() . "\n";
    }
}

// 3. Copy build assets & public files
echo "Copying assets...\n";

function copyDir($src, $dst) {
    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
    }
    $dir = opendir($src);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                copyDir($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

copyDir(__DIR__ . '/../public/build', $outputDir . '/build');
if (file_exists(__DIR__ . '/../public/favicon.ico')) {
    copy(__DIR__ . '/../public/favicon.ico', $outputDir . '/favicon.ico');
}
if (file_exists(__DIR__ . '/../public/robots.txt')) {
    copy(__DIR__ . '/../public/robots.txt', $outputDir . '/robots.txt');
}

// 4. Create .nojekyll for GitHub Pages
file_put_contents($outputDir . '/.nojekyll', '');

// 5. Create 404.html (redirect to index or show 404)
if (file_exists($outputDir . '/index.html')) {
    copy($outputDir . '/index.html', $outputDir . '/404.html');
}

echo "=== Export Complete! Output available in {$outputDir} ===\n";
