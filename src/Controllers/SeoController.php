<?php

declare(strict_types=1);

namespace Vendor\NeoPHP\SeoPackage\Controllers;

use Neo\Core\Controller\AbstractController;
use Neo\Core\Http\Response\Types\Response;
use Neo\Core\Routing\Attribute\MainRoute;
use Neo\Core\Routing\Attribute\Route;
use Neo\Core\Routing\RouterManager;
use Neo\Core\Utils\Config\ConfigManager;

#[MainRoute(path: '/', name: 'seo')]
class SeoController extends AbstractController
{
    #[Route(path: '/sitemap.xml', name: 'sitemap', methods: ['GET'])]
    public function sitemap(RouterManager $router, ConfigManager $config): Response
    {
        $seoConfig = $config->from('seo')->get('sitemap', []);

        if (!($seoConfig['enabled'] ?? true)) {
            return $this->make()->setStatusCode(404);
        }

        $excludePrefixes = $seoConfig['exclude_prefixes'] ?? [];
        $changefreq = $seoConfig['default_changefreq'] ?? 'weekly';
        $priority = $seoConfig['default_priority'] ?? 0.5;
        $baseUrl = $this->getRequestBaseUrl();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($this->collectPublicUrls($router, $excludePrefixes) as $url) {
            $loc = htmlspecialchars($baseUrl . $url, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $xml .= "    <url>\n";
            $xml .= "        <loc>{$loc}</loc>\n";
            $xml .= "        <changefreq>{$changefreq}</changefreq>\n";
            $xml .= "        <priority>{$priority}</priority>\n";
            $xml .= "    </url>\n";
        }

        $xml .= '</urlset>';

        return $this->make()
            ->setHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->setContent($xml);
    }

    #[Route(path: '/robots.txt', name: 'robots', methods: ['GET'])]
    public function robots(ConfigManager $config): Response
    {
        $robotsConfig = $config->from('seo')->get('robots', []);

        if (!($robotsConfig['enabled'] ?? true)) {
            return $this->make()->setStatusCode(404);
        }

        $lines = ['User-agent: *'];

        foreach ($robotsConfig['disallow'] ?? [] as $path) {
            $lines[] = "Disallow: {$path}";
        }

        $lines[] = '';
        $lines[] = 'Sitemap: ' . $this->getRequestBaseUrl() . ($robotsConfig['sitemap_url'] ?? '/sitemap.xml');

        return $this->make()
            ->setHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->setContent(implode("\n", $lines));
    }

    private function collectPublicUrls(RouterManager $router, array $excludePrefixes): array
    {
        $urls = [];

        foreach ($router->getRoutes()->all() as $method => $methodRoutes) {
            if ($method !== 'GET') {
                continue;
            }

            foreach (array_keys($methodRoutes) as $path) {
                if (str_contains($path, '{')) {
                    continue;
                }

                if ($path === '/sitemap.xml' || $path === '/robots.txt') {
                    continue;
                }

                $excluded = false;
                foreach ($excludePrefixes as $prefix) {
                    if (str_starts_with($path, $prefix)) {
                        $excluded = true;
                        break;
                    }
                }

                if (!$excluded) {
                    $urls[] = $path;
                }
            }
        }

        return array_values(array_unique($urls));
    }

    private function getRequestBaseUrl(): string
    {
        $scheme = ($_SERVER['HTTPS'] ?? 'off') !== 'off' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return "{$scheme}://{$host}";
    }
}