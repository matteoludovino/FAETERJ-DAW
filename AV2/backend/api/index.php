<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Auth.php';

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/VeiculoController.php';
require_once __DIR__ . '/../controllers/ClienteController.php';
require_once __DIR__ . '/../controllers/LojaController.php';
require_once __DIR__ . '/../controllers/ReservaController.php';

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base   = rtrim(API_BASE_PATH, '/');
$path   = ltrim(str_replace($base, '', $uri), '/');
$parts  = array_values(array_filter(explode('/', $path)));
$method = $_SERVER['REQUEST_METHOD'];

$body = [];
$raw  = file_get_contents('php://input');
if (!empty($raw)) {
    $decoded = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $body = $decoded;
    }
}

$query = $_GET;

$recurso = $parts[0] ?? '';
$seg1    = $parts[1] ?? null;
$seg2    = $parts[2] ?? null;

$id = (is_numeric($seg1)) ? (int)$seg1 : null;
$subRota = !is_numeric($seg1) ? $seg1 : $seg2;

switch ($recurso) {

    case 'auth':
        $ctrl = new AuthController();
        match(true) {
            $method === 'POST' && $seg1 === 'login'    => $ctrl->login($body),
            $method === 'POST' && $seg1 === 'register' => $ctrl->register($body),
            $method === 'POST' && $seg1 === 'logout'   => $ctrl->logout(),
            $method === 'GET'  && $seg1 === 'me'       => $ctrl->me(),
            default => Response::error('Rota de auth não encontrada.', 404)
        };
        break;

    case 'veiculos':
        $ctrl = new VeiculoController();
        match(true) {
            $method === 'GET'  && $id === null                    => $ctrl->index($query),
            $method === 'GET'  && $id !== null && $subRota === null=> $ctrl->show($id),
            $method === 'POST' && $id === null                    => $ctrl->store($body),
            $method === 'PUT'  && $id !== null && $subRota === null=> $ctrl->update($id, $body),
            $method === 'PUT'  && $id !== null && $subRota === 'status' => $ctrl->updateStatus($id, $body),
            $method === 'DELETE' && $id !== null                  => $ctrl->destroy($id),
            default => Response::error('Rota de veículos não encontrada.', 404)
        };
        break;

    case 'clientes':
        $ctrl = new ClienteController();
        match(true) {
            $method === 'GET'    && $id === null  => $ctrl->index($query),
            $method === 'GET'    && $id !== null  => $ctrl->show($id),
            $method === 'POST'   && $id === null  => $ctrl->store($body),
            $method === 'PUT'    && $id !== null  => $ctrl->update($id, $body),
            $method === 'DELETE' && $id !== null  => $ctrl->destroy($id),
            default => Response::error('Rota de clientes não encontrada.', 404)
        };
        break;

    case 'lojas':
        $ctrl = new LojaController();
        match(true) {
            $method === 'GET'    && $id === null  => $ctrl->index($query),
            $method === 'GET'    && $id !== null  => $ctrl->show($id),
            $method === 'POST'   && $id === null  => $ctrl->store($body),
            $method === 'PUT'    && $id !== null  => $ctrl->update($id, $body),
            $method === 'DELETE' && $id !== null  => $ctrl->destroy($id),
            default => Response::error('Rota de lojas não encontrada.', 404)
        };
        break;

    case 'reservas':
        $ctrl = new ReservaController();
        match(true) {
            $method === 'GET'  && $id === null               => $ctrl->index($query),
            $method === 'GET'  && $id !== null && !$subRota  => $ctrl->show($id),
            $method === 'POST' && $id === null               => $ctrl->store($body),
            $method === 'PUT'  && $id !== null && !$subRota  => $ctrl->update($id, $body),
            $method === 'POST' && $id !== null && $subRota === 'cancelar'  => $ctrl->cancelar($id, $body),
            $method === 'POST' && $id !== null && $subRota === 'retirar'   => $ctrl->retirar($id, $body),
            $method === 'POST' && $id !== null && $subRota === 'devolver'  => $ctrl->devolver($id, $body),
            default => Response::error('Rota de reservas não encontrada.', 404)
        };
        break;

    case 'pagamentos':
        $ctrl = new PagamentoController();
        match(true) {
            $method === 'GET'  && $id === null  => $ctrl->index($query),
            $method === 'GET'  && $id !== null  => $ctrl->show($id),
            $method === 'POST' && $id === null  => $ctrl->store($body),
            default => Response::error('Rota de pagamentos não encontrada.', 404)
        };
        break;

    case 'motoristas':
        $ctrl = new MotoristaController();
        match(true) {
            $method === 'GET'    && $id === null  => $ctrl->index($query),
            $method === 'GET'    && $id !== null  => $ctrl->show($id),
            $method === 'POST'   && $id === null  => $ctrl->store($body),
            $method === 'PUT'    && $id !== null  => $ctrl->update($id, $body),
            $method === 'DELETE' && $id !== null  => $ctrl->destroy($id),
            default => Response::error('Rota de motoristas não encontrada.', 404)
        };
        break;

    case 'relatorios':
        $ctrl = new RelatorioController();
        match(true) {
            $method === 'GET' && $seg1 === 'dashboard'   => $ctrl->dashboard(),
            $method === 'GET' && $seg1 === 'reservas'    => $ctrl->reservas($query),
            $method === 'GET' && $seg1 === 'veiculos'    => $ctrl->veiculos($query),
            $method === 'GET' && $seg1 === 'financeiro'  => $ctrl->financeiro($query),
            default => Response::error('Rota de relatórios não encontrada.', 404)
        };
        break;

    default:
        Response::error("Recurso '$recurso' não encontrado.", 404);
        break;
}
