<?php

declare(strict_types=1);

namespace TrocaDeTurno\Repositories;

use TrocaDeTurno\Data\Connection;
use TrocaDeTurno\Entities\Justificativa;

final class JustificativaRepository
{
    public function __construct(private readonly Connection $conn)
    {
    }

    /** @return Justificativa[] */
    public function listarAtivas(): array
    {
        $stmt = $this->conn->pdo()->query("
            SELECT
                id_justificativa,
                descricao
            FROM justificativa
            WHERE ativo = 1
            ORDER BY descricao
        ");

        return $stmt->fetchAll();
    }
}