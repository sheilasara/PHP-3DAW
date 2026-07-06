<?php

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/ClienteController.php';
require_once __DIR__ . '/../controllers/VeiculoController.php';
require_once __DIR__ . '/../controllers/ReservaController.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/api';
$path = substr($uri, strpos($uri, $basePath) !== false ? strpos($uri, $basePath) + strlen($basePath) : 0);
$path = '/' . trim($path, '/');
$metodo = $_SERVER['REQUEST_METHOD'];

$corpo = json_decode(file_get_contents('php://input'), true) ?? [];
$query = $_GET;

$segmentos = explode('/', trim($path, '/'));

try
    if ($metodo === 'POST' && $path === '/cadastro') {
        (new AuthController())->cadastrar($corpo);
        exit;
    }

    if ($metodo === 'POST' && $path === '/login') {
        (new AuthController())->login($corpo);
        exit;
    }

    if ($metodo === 'GET' && $segmentos[0] === 'veiculos' && count($segmentos) === 1) {
        (new VeiculoController())->listar($query);
        exit;
    }

    if ($metodo === 'GET' && $segmentos[0] === 'veiculos' && count($segmentos) === 2) {
        (new VeiculoController())->detalhar((int) $segmentos[1]);
        exit;
    }

    if ($metodo === 'POST' && $path === '/logout') {
        (new AuthController())->logout();
        exit;
    }

    $idClienteAutenticado = Auth::clienteAutenticado();

    if ($segmentos[0] === 'cliente' && ($segmentos[1] ?? '') === 'perfil') {
        $controller = new ClienteController();
        if ($metodo === 'GET') {
            $controller->perfil($idClienteAutenticado);
        } elseif ($metodo === 'PUT') {
            $controller->atualizarPerfil($idClienteAutenticado, $corpo);
        }
        exit;
    }

    if ($segmentos[0] === 'reservas') {
        $controller = new ReservaController();

        if ($metodo === 'POST' && count($segmentos) === 1) {
            $controller->criar($idClienteAutenticado, $corpo);
            exit;
        }

        if ($metodo === 'GET' && ($segmentos[1] ?? '') === 'cliente') {
            $controller->listarDoCliente($idClienteAutenticado, $query);
            exit;
        }

        if ($metodo === 'GET' && count($segmentos) === 2 && is_numeric($segmentos[1])) {
            $controller->detalhar($idClienteAutenticado, (int) $segmentos[1]);
            exit;
        }

        if ($metodo === 'POST' && count($segmentos) === 3 && $segmentos[2] === 'pagamento') {
            $controller->pagar($idClienteAutenticado, (int) $segmentos[1], $corpo);
            exit;
        }

        if ($metodo === 'PUT' && count($segmentos) === 3 && $segmentos[2] === 'cancelar') {
            $controller->cancelar($idClienteAutenticado, (int) $segmentos[1], $corpo);
            exit;
        }

        if ($metodo === 'PUT' && count($segmentos) === 3 && $segmentos[2] === 'retirada') {
            $controller->confirmarRetirada($idClienteAutenticado, (int) $segmentos[1], $corpo);
            exit;
        }

        if ($metodo === 'PUT' && count($segmentos) === 3 && $segmentos[2] === 'devolucao') {
            $controller->confirmarDevolucao($idClienteAutenticado, (int) $segmentos[1], $corpo);
            exit;
        }
    }

    Response::erro('Rota não encontrada.', 404);
} catch (Throwable $e) {
    Response::erro('Erro interno no servidor.', 500, $e->getMessage());
}
