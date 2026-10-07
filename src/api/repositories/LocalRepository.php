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
        $linhas = $stmt->fetchAll();

        return $this->mapearTodosLocais($linhas);
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

        return $this->mapearLocal($linha);
    }

    private function mapearLocal(array $linha): Local
    {
        return new Local(
            idLocal: (int) $linha['id_local'],
            local: $linha['local'],
            idSupervisao: (int) $linha['id_supervisao']
        );
    }

    private function mapearTodosLocais(array $linhas): array
    {
        $locais = [];

        foreach($linhas as $linha) {
            $locais = $this->mapearLocal($linha);
        }

        return $locais;
    }
}