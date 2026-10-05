<?php

declare(strict_types = 1);

namespace TrocaDeTurno\Repositories;

use TrocaDeTurno\Data\Connection;
use TrocaDeTurno\Entities\Local;

final class LocalRepository
{
    public function __construct(private readonly Connection $conn)
    {}

    public function listarTodos(): array
    {
        $stmt = $this->conn->pdo()->query(
            'SELECT *
            FROM local
            ORDER BY local'
        );
        
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $idLocal): ?Local
    {
        $stmt = $this->conn->pdo()->prepare(
            'SELECT *
            FROM local
            WHERE id_local = :idLocal'
        );
        $stmt->execute(['idLocal' => $idLocal]);
        $linha = $stmt->fetch();

        if (!$linha) {
            return null;
        }

        $local = new Local(
            (int) $linha['id_local'],
            $linha['local'],
            $linha['id_supervisao']
        );

        return $local;
    }
}