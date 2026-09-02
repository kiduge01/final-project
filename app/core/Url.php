<?php

declare(strict_types=1);

namespace App\Core;

final class Route
{
    private static array $routes = [
        'home'            => '/',
        'login'           => '/login',
        'logout'          => '/logout',
        'forgot_password' => '/forgot-password',
        'reset_password'  => '/reset-password',
        'dashboard'       => '/',
        'members'         => '/members',
        'guests'          => '/guests',
        'attendance'      => '/attendance',
        'finance'         => '/finance',
        'assets'          => '/asset-center',
        'communication'   => '/communication',
        'ai'              => '/ai-assistant',
        'reports'         => '/reports',
        'settings'        => '/settings',
    ];

    public static function get(string $name, array $params = []): string
    {
        if (!isset(self::$routes[$name])) {
            return Url::app($name);
        }

        $path = self::$routes[$name];
        
        return Url::app($path);
    }
}

final class Url
{
    /**
     * Generate a full URL for a given path relative to the application root.
     * 
     * @param string $path The path relative to the root
     * @return string The full URL
     */
    public static function to(string $path = ''): string
    {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        
        // Normalize slashes
        $baseUrl = str_replace('\\', '/', $baseUrl);
        
        // BASE_URL is something like '/htdocs/public'
        // We want the root which is '/htdocs'
        $parts = explode('/', rtrim($baseUrl, '/'));
        if (end($parts) === 'public') {
            array_pop($parts);
        }
        $rootPath = implode('/', $parts);
        
        $path = ltrim($path, '/');
        return ($rootPath === '' ? '' : $rootPath) . '/' . $path;
    }

    /**
     * Generate a URL for the main application (via /public/index.php).
     * 
     * @param string $path The route path (e.g., 'login', 'members')
     * @return string The full URL
     */
    public static function app(string $path = ''): string
    {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $path = ltrim($path, '/');
        
        return rtrim($baseUrl, '/') . '/' . $path;
    }

}
