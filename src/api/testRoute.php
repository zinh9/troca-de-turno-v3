<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use TrocaDeTurno\Container\Container;
use TrocaDeTurno\Controllers\EmpregadoController;
use TrocaDeTurno\Data\Connection;
use TrocaDeTurno\Repositories\EmpregadoRepository;
use TrocaDeTurno\Services\EmpregadoService;

$container = new Container();

$container->registrarSingleton(
    'conexaoSql',
    fn() => new Connection(
        host: 'localhost',
        database: 'troca_de_turno'
    )
);

$container->registrarSingleton(
    'empregadoRepository',
    fn() => new EmpregadoRepository(
        $container->resolver('conexaoSql')
    )
);

$container->registrarSingleton(
    'empregadoService',
    fn() => new EmpregadoService(
        $container->resolver('empregadoRepository')
    )
);

$container->registrarSingleton(
    'empregadoController',
    fn() => new EmpregadoController(
        $container->resolver('empregadoService')
    )
);

$matricula = $_GET['matricula'] ?? null;
$acao      = $_GET['acao'] ?? null;

if ($acao === 'buscarEmpregado') {

    header('Content-Type: application/json');

    echo json_encode(
        $container
            ->resolver('empregadoController')
            ->buscar($matricula),
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );

    exit;
}