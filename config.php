<?php
/**
 * FILE: config.php
 * Core environment configuration for the AEP Legal Intelligence
 * Platform: database location, timezone, and API/environment settings.
 */

date_default_timezone_set(getenv('AEP_TIMEZONE') ?: 'Europe/London');

define('AEP_ENV', getenv('AEP_ENV') ?: 'production');
define('AEP_DB_PATH', __DIR__ . '/data/aep.sqlite');

if (!function_exists('aep_api_config')) {
    /**
     * Optional AI/counsel-engine API configuration. Disabled by default;
     * set the AEP_AI_API_KEY environment variable to enable AI assist
     * features without hardcoding any credentials in source control.
     */
    function aep_api_config(): array
    {
        $apiKey = getenv('AEP_AI_API_KEY') ?: '';
        return [
            'enabled' => $apiKey !== '',
            'api_key' => $apiKey,
            'model' => getenv('AEP_AI_MODEL') ?: 'gpt-4o-mini',
            'endpoint' => getenv('AEP_AI_ENDPOINT') ?: '',
        ];
    }
}
