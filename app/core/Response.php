<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function json(array $payload, int $status = 200): void
    {
        // Clear any buffered output (in case of warnings/notices)
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function view(string $viewPath, array $data = []): void
    {
        $data['viewPath'] = $viewPath;
        $data['baseUrl']  = defined('BASE_URL') ? BASE_URL : '';

        // Inject church branding into every view
        if (!isset($data['churchName'])) {
            $brand = self::loadChurchBranding();
            $data['churchName'] = $brand['church_name'];
            $data['churchLogo'] = $brand['church_logo'];
        }

        extract($data, EXTR_SKIP);
        require __DIR__ . '/../views/layouts/app.php';
        exit;
    }

    public static function redirect(string $to): void
    {
        header('Location: ' . Url::app($to));
        exit;
    }

    /** Load church name + logo from database (cached per request). */
    public static function loadChurchBranding(): array
    {
        static $cache = null;
        if ($cache !== null) return $cache;

        $default = ['church_name' => 'Church CMS', 'church_logo' => ''];
        try {
            $pdo = \App\Core\Database::getConnection();
            if (!$pdo) return $cache = $default;
            $stmt = $pdo->query(
                "SELECT setting_key, setting_value FROM church_settings WHERE setting_key IN ('church_name', 'church_logo')"
            );
            $rows = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
            
            // Ensure both keys exist in the array, even if they're empty
            $churchName = isset($rows['church_name']) && !empty(trim((string)$rows['church_name'])) 
                ? trim((string)$rows['church_name'])
                : $default['church_name'];
            $churchLogo = isset($rows['church_logo']) ? (string)$rows['church_logo'] : '';
            
            $cache = [
                'church_name' => $churchName,
                'church_logo' => $churchLogo,
            ];
        } catch (\Throwable $e) {
            $cache = $default;
        }
        return $cache;
    }
}
