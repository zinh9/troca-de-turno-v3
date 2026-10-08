<?php

declare(strict_types=1);

/**
 * index.php — a "recepção" do back-end: recebe a URL e entrega pro controller certo.
 * NENHUMA regra de negócio aqui.
 *
 * Rodar (na pasta src/api):
 *   set PHP_CLI_SERVER_WORKERS=4          (Windows; o SSE segura 1 worker enquanto aberto)
 *   php -S localhost:8000 -t controllers controllers/index.php
 *
 * Testar no navegador (tudo aceita GET + query-string):
 *   /api/painel?idSupervisao=1&idLocal=7
 *   /api/apresentacao?matricula=123456&idLocal=7
 *   /api/prontidao?idApresentacao=10
 */

use TrocaDeTurno\Controllers\EventosController;

require __DIR__ . '/../bootstrap.php';

/** @var \TrocaDeTurno\Container\Container $container */
$container = require __DIR__ . '/../container/dependencias.php';

// TODO(Zenzo): em produção, restrinja os POSTs a $metodo === 'POST'. Em desenvolvimento
// aceitamos GET também para você testar escrevendo a URL no navegador.
const PERMITIR_GET_NAS_ESCRITAS = true;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Funciona em qualquer pasta/site do IIS: descarta tudo antes de "/api/".
//   /troca-de-turno/controllers/index.php/api/painel  ->  /api/painel
//   /api/painel                                        ->  /api/painel
$posApi = strpos($uri, '/api/');
if ($posApi !== false) {
    $uri = substr($uri, $posApi);
}
$metodo = $_SERVER['REQUEST_METHOD'];
$escrita = PERMITIR_GET_NAS_ESCRITAS ? in_array($metodo, ['GET', 'POST'], true) : $metodo === 'POST';

header('Access-Control-Allow-Origin: *'); // TODO(Zenzo): trocar pelo domínio do front em produção

try {
    // SSE não é JSON: tem o próprio formato, então trata antes.
    if ($uri === '/api/eventos' && $metodo === 'GET') {
        (new EventosController($container->resolver('eventoPublisher')))->atender(
            (int) ($_GET['idSupervisao'] ?? 0),
            isset($_GET['idLocal']) && $_GET['idLocal'] !== '' ? (int) $_GET['idLocal'] : null,
        );
        exit;
    }

    header('Content-Type: application/json; charset=utf-8');

    $resposta = match (true) {
        $uri === '/api/painel' && $metodo === 'GET' => $container->resolver('painelController')->obter(),
        $uri === '/api/justificativas' && $metodo === 'GET' => $container->resolver('justificativaController')->listar(),
        $uri === '/api/apresentacao' && $escrita => $container->resolver('apresentacaoController')->registrar(),
        $uri === '/api/apresentacao/justificativa' && $escrita => $container->resolver('apresentacaoController')->justificar(),
        $uri === '/api/prontidao' && $escrita => $container->resolver('prontidaoController')->registrar(),
        $uri === '/api/ccp/chamada' && $escrita => $container->resolver('prontidaoController')->acionarRadio(),
        default => throw new \DomainException('Rota não encontrada', 404),
    };

    echo json_encode($resposta, JSON_UNESCAPED_UNICODE);

} catch (\DomainException $e) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (\InvalidArgumentException $e) {      // dado faltando/errado  -> 400
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (\RuntimeException $e) {              // regra de negócio violada -> 409
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {                     // bug/banco fora -> 500
    http_response_code(500);
    // TODO(Zenzo): em produção NÃO devolva $e->getMessage(); grave em log.
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
