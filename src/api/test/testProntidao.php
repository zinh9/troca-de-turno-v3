<?php

declare(strict_types=1);

use TrocaDeTurno\Container\Container;
use TrocaDeTurno\Controllers\ApresentacaoController;
use TrocaDeTurno\Controllers\PainelController;
use TrocaDeTurno\Controllers\ProntidaoController;
use TrocaDeTurno\Data\Connection;
use TrocaDeTurno\Repositories\ApresentacaoRepository;
use TrocaDeTurno\Repositories\EmpregadoRepository;
use TrocaDeTurno\Repositories\HorarioReferenciaRepository;
use TrocaDeTurno\Repositories\LocalRepository;
use TrocaDeTurno\Repositories\ProntidaoRepository;
use TrocaDeTurno\Services\ApresentacaoService;
use TrocaDeTurno\Services\EmpregadoService;
use TrocaDeTurno\Services\HorarioReferenciaService;
use TrocaDeTurno\Services\PainelService;
use TrocaDeTurno\Services\ProntidaoService;

require_once __DIR__ . '/../vendor/autoload.php';

$container = new Container();

$container->registrarSingleton( 
    'conexao',
    fn() => new Connection(
        host: 'localhost',
        database: 'troca_de_turno'
    )
);

$container->registrarSingleton(
    'localRepository',
    fn(Container $c) => new LocalRepository(
        $c->resolver('conexao')
    )
);

$container->registrarSingleton(
    'apresentacaoRepository',
    fn(Container $c) => new ApresentacaoRepository(
        $c->resolver('conexao')
    )
);

$container->registrarSingleton(
    'empregadoRepository',
    fn(Container $c) => new EmpregadoRepository(
        $c->resolver('conexao')
    )
);

$container->registrarSingleton(
    'empregadoService',
    fn(Container $c) => new EmpregadoService(
        $c->resolver('empregadoRepository')
    )
);

$container->registrarSingleton(
    'horarioReferencia',
    fn(Container $c) => new HorarioReferenciaRepository(
        $c->resolver('conexao')
    )
);

$container->registrarSingleton(
    'horarioReferenciaRepository',
    fn(Container $c) => new HorarioReferenciaRepository(
        $c->resolver('conexao')
    )
);

$container->registrarSingleton(
    'horarioReferenciaService',
    fn(Container $c) => new HorarioReferenciaService(
        $c->resolver('horarioReferenciaRepository')
    )
);

$container->registrarSingleton(
    'apresentacaoService',
    fn(Container $c) => new ApresentacaoService(
        $c->resolver('apresentacaoRepository'),
        $c->resolver('localRepository'),
        $c->resolver('horarioReferenciaService'),
        $c->resolver('empregadoService')
    )
);

$container->registrarSingleton(
    'prontidaoRepository',
    fn(Container $c) => new ProntidaoRepository(
        $c->resolver('conexao')
    )
);

$container->registrarSingleton(
    'prontidaoService',
    fn(Container $c) => new ProntidaoService(
        $c->resolver('prontidaoRepository'),
        $c->resolver('apresentacaoService')
    )
);

$container->registrarSingleton(
    'prontidaoController',
    fn(Container $c) => new ProntidaoController(
        $c->resolver('prontidaoService')
    )
);

$container->registrarSingleton(
    'painelService',
    fn(Container $c) => new PainelService(
        $c->resolver('empregadoRepository'),
        $c->resolver('apresentacaoRepository'),
        $c->resolver('prontidaoRepository')
    )
);

$container->registrarSingleton(
    'painelController',
    fn(Container $c) => new PainelController(
        $c->resolver('painelService')
    )
);

header('Content-Type: application/json');

echo json_encode(
    $container
        ->resolver('painelController')
        ->obter()
);

exit;