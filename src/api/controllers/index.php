<?php

declare(strict_types=1);

//require __DIR__ . '/../vendor/autoload.php';

use TrocaDeTurno\Container\Container;

use TrocaDeTurno\Controllers\EventosController;
use TrocaDeTurno\Controllers\JustificativaController;
use TrocaDeTurno\Controllers\ProntidaoController;

use TrocaDeTurno\Data\Connection;

use TrocaDeTurno\Events\EventDispatcher;
use TrocaDeTurno\Events\ProntidaoRegistradaEvent;

use TrocaDeTurno\Repositories\JustificativaRepository;
use TrocaDeTurno\Repositories\ProntidaoRepository;

use TrocaDeTurno\Services\JustificativaService;
use TrocaDeTurno\Services\PollingEventoPublisher;
use TrocaDeTurno\Services\ProntidaoService;

require_once '../container/Container.php';
require_once '../controllers/EventosController.php';
require_once '../controllers/JustificativaController.php';
require_once '../controllers/ProntidaoController.php';
require_once '../data/Connection.php';
require_once '../events/EventDispatcher.php';
require_once '../events/ProntidaoRegistradaEvent.php';
require_once '../repositories/JustificativaRepository.php';
require_once '../repositories/ProntidaoRepository.php';
require_once '../services/JustificativaService.php';
require_once '../services/PollingEventoPublisher.php';
require_once '../services/ProntidaoService.php';

/**
 * index.php — o "app.js" deste back-end. Só faz fiação: registra as
 * dependências no Container e as rotas no FastRoute. Nenhuma lógica de
 * negócio deve morar aqui — se você se pegar escrevendo um `if`
 * complicado neste arquivo, ele pertence a algum Service.
 */

// ---- 1. Container: registra as dependências (equivalente ao app.js) ----
$container = new Container();

$container->registrarSingleton('conexaoSql', fn() => new Connection(
    host: 'localhost',
    database: 'troca_de_turno',
));

$container->registrarSingleton('dispatcher', fn() => new EventDispatcher());

// Troque esta linha por RedisEventoPublisher quando/se Redis entrar
// em cena — nenhum outro arquivo muda por causa disso.
$container->registrarSingleton(
    'eventoPublisher',
    fn(Container $c) => new PollingEventoPublisher($c->resolver('conexaoSql')),
);

$container->registrarSingleton(
    'justificativaRepository',
    fn(Container $c) => new JustificativaRepository($c->resolver('conexaoSql')),
);
$container->registrarSingleton(
    'justificativaService',
    fn(Container $c) => new JustificativaService($c->resolver('justificativaRepository')),
);

$container->registrarSingleton(
    'prontidaoRepository',
    fn(Container $c) => new ProntidaoRepository($c->resolver('conexaoSql')),
);
$container->registrarSingleton(
    'prontidaoService',
    fn(Container $c) => new ProntidaoService($c->resolver('prontidaoRepository'), $c->resolver('dispatcher')),
);

// ---- 2. Liga o evento de domínio ao publicador (a "cola" do padrão orientado a eventos) ----
$container->resolver('dispatcher')->inscrever(
    ProntidaoRegistradaEvent::class,
    fn(ProntidaoRegistradaEvent $evento) => $container->resolver('eventoPublisher')
        ->publicar($evento->supervisao, $evento->local),
);

// ---- 3. Rotas (equivalente ao router.js, mas HTTP de verdade) ----
/**$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
    $r->addRoute('GET', '/api/eventos', 'eventos');
    $r->addRoute('GET', '/api/justificativas', 'justificativas');
    $r->addRoute('POST', '/api/prontidao/justificativa', 'prontidao.justificativa');
});*/
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$metodo = $_SERVER['REQUEST_METHOD'];

/**$rotaInfo = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($rotaInfo[0] !== FastRoute\Dispatcher::FOUND) {
    http_response_code(404);
    echo json_encode(['success' => false, 'mensagem' => 'Rota não encontrada']);
    exit;
}

[, $nomeRota] = $rotaInfo;
header('Content-Type: application/json');

match ($nomeRota) {
    'eventos' => (function () use ($container) {
        $controller = new EventosController($container->resolver('eventoPublisher'));
        $controller->atender($_GET['supervisao'] ?? '', $_GET['local'] ?? null);
    })(),

    'justificativas' => (function () use ($container) {
        $controller = new JustificativaController($container->resolver('justificativaService'));
        echo json_encode($controller->listar());
    })(),

    'prontidao.justificativa' => (function () use ($container) {
        $controller = new ProntidaoController($container->resolver('prontidaoService'));
        $corpo = json_decode(file_get_contents('php://input'), true) ?? [];
        echo json_encode($controller->enviarJustificativa($corpo));
    })(),
};*/

if ($uri === '/teste/conexao') {

    $controller = new EventosController(
        $container->resolver('eventoPublisher')
    );

    $controller->atender(
        $_GET['supervisao'] ?? '',
        $_GET['local'] ?? null
    );

} elseif ($uri === '/api/justificativas' && $metodo === 'GET') {

    $controller = new JustificativaController(
        $container->resolver('justificativaService')
    );

    echo json_encode(
        $controller->listar()
    );

} elseif (
    $uri === '/api/prontidao/justificativa'
    && $metodo === 'POST'
) {

    $controller = new ProntidaoController(
        $container->resolver('prontidaoService')
    );

    $corpo = json_decode(
        file_get_contents('php://input'),
        true
    ) ?? [];

    echo json_encode(
        $controller->enviarJustificativa($corpo)
    );

} else {

    http_response_code(404);

    echo json_encode([
        'success' => false,
        'mensagem' => 'Rota não encontrada'
    ]);
}