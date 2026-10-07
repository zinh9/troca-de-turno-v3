<?php

declare(strict_types=1);

use TrocaDeTurno\Container\Container;
use TrocaDeTurno\Controllers\ApresentacaoController;
use TrocaDeTurno\Controllers\JustificativaController;
use TrocaDeTurno\Controllers\PainelController;
use TrocaDeTurno\Controllers\ProntidaoController;
use TrocaDeTurno\Data\Connection;
use TrocaDeTurno\Repositories\ApresentacaoRepository;
use TrocaDeTurno\Repositories\EmpregadoRepository;
use TrocaDeTurno\Repositories\HorarioReferenciaRepository;
use TrocaDeTurno\Repositories\JustificativaRepository;
use TrocaDeTurno\Repositories\LancheRepository;
use TrocaDeTurno\Repositories\LocalRepository;
use TrocaDeTurno\Repositories\ProntidaoRepository;
use TrocaDeTurno\Repositories\TurnoRepository;
use TrocaDeTurno\Services\ApresentacaoService;
use TrocaDeTurno\Services\EmpregadoService;
use TrocaDeTurno\Services\HorarioReferenciaService;
use TrocaDeTurno\Services\JustificativaService;
use TrocaDeTurno\Services\PainelService;
use TrocaDeTurno\Services\PollingEventoPublisher;
use TrocaDeTurno\Services\ProntidaoService;

/**
 * "Lista de compras" do back-end. Cada linha ensina o Container a montar UMA peça.
 * Ordem não importa: uma peça só é criada quando alguém pede (resolver).
 * Regra prática: peça nova (Service/Repository/Controller) => 1 linha aqui.
 */
$container = new Container();

$container->registrarSingleton('conexao', fn() => new Connection(host: 'localhost', database: 'troca_de_turno'));

// ---- Publisher: é ELE o "evento". Quem grava no banco chama ->publicar(). ----
$container->registrarSingleton('eventoPublisher', fn(Container $c) => new PollingEventoPublisher($c->resolver('conexao')));

// ---- Repositories ----
foreach ([
    'apresentacaoRepository' => ApresentacaoRepository::class,
    'empregadoRepository' => EmpregadoRepository::class,
    'horarioReferenciaRepository' => HorarioReferenciaRepository::class,
    'justificativaRepository' => JustificativaRepository::class,
    'lancheRepository' => LancheRepository::class,
    'localRepository' => LocalRepository::class,
    'prontidaoRepository' => ProntidaoRepository::class,
    'turnoRepository' => TurnoRepository::class,
] as $nome => $classe) {
    $container->registrarSingleton($nome, fn(Container $c) => new $classe($c->resolver('conexao')));
}

// ---- Services ----
$container->registrarSingleton('empregadoService', fn(Container $c) => new EmpregadoService($c->resolver('empregadoRepository')));
$container->registrarSingleton('horarioReferenciaService', fn(Container $c) => new HorarioReferenciaService($c->resolver('horarioReferenciaRepository')));
$container->registrarSingleton('justificativaService', fn(Container $c) => new JustificativaService($c->resolver('justificativaRepository')));

$container->registrarSingleton('apresentacaoService', fn(Container $c) => new ApresentacaoService(
    $c->resolver('apresentacaoRepository'),
    $c->resolver('localRepository'),
    $c->resolver('horarioReferenciaService'),
    $c->resolver('empregadoService'),
    $c->resolver('turnoRepository'),
    $c->resolver('eventoPublisher'),
));

$container->registrarSingleton('prontidaoService', fn(Container $c) => new ProntidaoService(
    $c->resolver('prontidaoRepository'),
    $c->resolver('apresentacaoRepository'),
    $c->resolver('lancheRepository'),
    $c->resolver('localRepository'),
    $c->resolver('eventoPublisher'),
));

$container->registrarSingleton('painelService', fn(Container $c) => new PainelService(
    $c->resolver('empregadoRepository'),
    $c->resolver('apresentacaoRepository'),
    $c->resolver('prontidaoRepository'),
    $c->resolver('lancheRepository'),
    $c->resolver('localRepository'),
));

// ---- Controllers ----
$container->registrarSingleton('apresentacaoController', fn(Container $c) => new ApresentacaoController($c->resolver('apresentacaoService')));
$container->registrarSingleton('prontidaoController', fn(Container $c) => new ProntidaoController($c->resolver('prontidaoService')));
$container->registrarSingleton('painelController', fn(Container $c) => new PainelController($c->resolver('painelService')));
$container->registrarSingleton('justificativaController', fn(Container $c) => new JustificativaController($c->resolver('justificativaService')));

return $container;
