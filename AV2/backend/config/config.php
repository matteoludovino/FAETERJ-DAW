<?php
define('APP_ENV',  getenv('APP_ENV')  ?: 'development');
define('APP_NAME', 'Falls Car');

define('API_BASE_PATH', getenv('API_BASE_PATH') ?: '/falls-car/backend/api');

define('TOKEN_EXPIRY_HOURS', 24);

define('PERIODOS_PERMITIDOS', [7, 15, 30]);

define('CANCELAMENTO_LIMITE_HORAS', 24);

define('REEMBOLSO_PERCENTUAL', 0.80);

if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

date_default_timezone_set('America/Sao_Paulo');
