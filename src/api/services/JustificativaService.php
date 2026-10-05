<?php

declare(strict_types=1); 

namespace TrocaDeTurno\Services;

use TrocaDeTurno\Repositories\JustificativaRepository;

final class JustificativaService
{
    private const CHAVE_CACHE = 'justificativas_ativas';
    private const TTL_SEGUNDOS = 300;

    public function __construct(private readonly JustificativaRepository $repository)
    {}

    /** @return array<array{idJustificativa: int, descricao: string}> */
    public function listarAtivas(): array
    {
        $cache = apcu_fetch(self::CHAVE_CACHE, $encontrado);
        if ($encontrado) {
            return $cache;
        }

        $lista = $this->repository->listarAtivas();

        apcu_store(self::CHAVE_CACHE, $lista, self::TTL_SEGUNDOS);
        return $lista;
    }

    public function invalidarCache(): void
    {
        apcu_delete(self::CHAVE_CACHE);
    }
}