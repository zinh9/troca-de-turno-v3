<?php

namespace TrocaDeTurno\Repositories;

use TrocaDeTurno\Data\Connection;
use TrocaDeTurno\Entities\Supervisao;

final class SupervisaoRepository {
    public function __construct(private readonly Connection $conn)
    {}

    /** @return Supervisao[] */
    public function listarTodos() {
        $stmt = $this->conn->pdo()->query("
            SELECT *
            FROM supervisao
            ORDER BY supervisao
        ");

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $idSupervisao): ?Supervisao {
        $stmt = $this->conn->pdo()->prepare("
            SELECT *
            FROM supervisao
            WHERE id_supervisao = :idSupervisao
        ");
        $stmt->execute(['idSupervisao' => $idSupervisao]);
        $linha = $stmt->fetch();

        if (!$linha) {
            return null;
        }

        $supervisao = new Supervisao(
            $linha['id_supervisao'],
            $linha['supervisao']
        );

        return $supervisao;
    }
}

?>