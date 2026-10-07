<?php

declare(strict_types=1);

namespace TrocaDeTurno\Repositories;

use TrocaDeTurno\Data\Connection;
use TrocaDeTurno\Entities\Apresentacao;

final class ApresentacaoRepository
{
    public function __construct(private readonly Connection $conn) {}

    /** @return array<Apresentacao> */
    public function listarTodos(): array
    {
        $stmt = $this->conn->pdo()->prepare(
            'SELECT a.*
            FROM apresentacao a
            JOIN empregado e ON a.id_empregado = e.id_empregado
            ORDER BY a.data_hora_apresentacao, e.cargo DESC');
        $stmt->execute();
        $linhas = $stmt->fetchAll(); 

        if (!$linhas) {
            return [];
        }

        return $this->mapearTodasApresentacoes($linhas);
    }

    public function listarTodasApresentacoesTurno(): array
    {
        $stmt = $this->conn->pdo()->prepare(
            'SELECT a.*
            FROM apresentacao a
            JOIN empregado e ON a.id_empregado = e.id_empregado
            WHERE DATEDIFF(MINUTE, a.data_hora_apresentacao, SYSDATETIME()) <= 780
            ORDER BY a.data_hora_apresentacao, e.cargo DESC');
        $stmt->execute();
        $linhas = $stmt->fetchAll();

        if (!$linhas) {
            return [];
        }

        return $this->mapearTodasApresentacoes($linhas);
    }

    public function buscarPorId(int $idApresentacao): ?Apresentacao
    {
        $stmt = $this->conn->pdo()->prepare('SELECT * FROM apresentacao WHERE id_apresentacao = :idApresentacao');
        $stmt->execute(['idApresentacao' => $idApresentacao]);
        $linha = $stmt->fetch();

        if (!$linha) {
            return null;
        }

        return $this->mapearApresentacao($linha);
    }

    public function buscarApresentacoesPorIdEmpregado(int $idEmpregado): ?array
    {
        $stmt = $this->conn->pdo()->prepare(
            'SELECT * 
            FROM apresentacao 
            WHERE id_empregado = :idEmpregado
            ORDER BY data_hora_apresentacao DESC'
        );
        $stmt->execute(['idEmpregado' => $idEmpregado]);
        $linhas = $stmt->fetchAll();

        if (!$linhas) {
            return null;
        }

        return $this->mapearTodasApresentacoes($linhas);
    }

    public function buscarApresentacaoHojePorIdEmpregado(int $idEmpregado): ?Apresentacao
    {
        $stmt = $this->conn->pdo()->prepare(
            // Janela de 13h (780 min) em vez de "mesma data": assim o turno
            // 18x06 não "esquece" a apresentação depois da meia-noite.
            'SELECT TOP 1 *
            FROM apresentacao
            WHERE id_empregado = :idEmpregado
            AND DATEDIFF(MINUTE, data_hora_apresentacao, SYSDATETIME()) <= 780
            ORDER BY data_hora_apresentacao DESC');

        $stmt->execute([
            'idEmpregado' => $idEmpregado
        ]);
        $linha = $stmt->fetch();

        if (!$linha) {
            return null;
        }

        return $this->mapearApresentacao($linha);
    }

    /** Grava e DEVOLVE o id novo (OUTPUT INSERTED é o jeito do SQL Server). */
    public function registrar(int $idEmpregado, int $idLocal, string $status): int
    {
        $stmt = $this->conn->pdo()->prepare(
            'INSERT INTO apresentacao (data_hora_apresentacao, id_empregado, id_local, status)
            OUTPUT INSERTED.id_apresentacao
            VALUES (SYSDATETIME(), :idEmpregado, :idLocal, :status)'
        );
        $stmt->execute([
            'idEmpregado' => $idEmpregado,
            'idLocal' => $idLocal,
            'status' => $status
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Todas as apresentações "ativas" (últimas 13h) de uma supervisão.
     * $idLocal = null  -> CCP (todos os locais da supervisão)
     * $idLocal = 7     -> totem/painel de um local só
     * @return array<Apresentacao>
     */
    public function listarAtivasPorSupervisao(int $idSupervisao, ?int $idLocal): array
    {
        $sql = 'SELECT a.*
            FROM apresentacao a
            JOIN local l ON l.id_local = a.id_local
            WHERE l.id_supervisao = :idSupervisao
            AND DATEDIFF(MINUTE, a.data_hora_apresentacao, SYSDATETIME()) <= 780';
        $params = ['idSupervisao' => $idSupervisao];

        if ($idLocal !== null) {
            $sql .= ' AND a.id_local = :idLocal';
            $params['idLocal'] = $idLocal;
        }

        $stmt = $this->conn->pdo()->prepare($sql . ' ORDER BY a.data_hora_apresentacao');
        $stmt->execute($params);

        return $this->mapearTodasApresentacoes($stmt->fetchAll());
    }

    /** Tamanho da equipe do local = quantos se apresentaram lá nas últimas 13h. */
    public function contarAtivasPorLocal(int $idLocal): int
    {
        $stmt = $this->conn->pdo()->prepare(
            'SELECT COUNT(*) FROM apresentacao
            WHERE id_local = :idLocal
            AND DATEDIFF(MINUTE, data_hora_apresentacao, SYSDATETIME()) <= 780'
        );
        $stmt->execute(['idLocal' => $idLocal]);

        return (int) $stmt->fetchColumn();
    }

    public function atualizarStatus(int $idApresentacao, string $novoStatus, ?int $idJustificativa): void
    {
        $stmt = $this->conn->pdo()->prepare(
            'UPDATE apresentacao 
            SET status = :novoStatus,
            id_justificativa = :idJustificativa
            WHERE id_apresentacao = :idApresentacao'
        );
        $stmt->execute([
            'novoStatus' => $novoStatus,
            'idJustificativa' => $idJustificativa,
            'idApresentacao' => $idApresentacao
        ]);
    }

    private function mapearApresentacao(array $linha): Apresentacao
    {
        return new Apresentacao(
            idApresentacao: (int) $linha['id_apresentacao'],
            dataHoraApresentacao: new \DateTimeImmutable($linha['data_hora_apresentacao']),
            idEmpregado: (int) $linha['id_empregado'],
            idLocal: (int) $linha['id_local'],
            status: $linha['status'] ?? '',
            idJustificativa: $linha['id_justificativa'] !== null ? (int) $linha['id_justificativa'] : null,
        );
    }

    private function mapearTodasApresentacoes(array $linhas): array
    {
        $apresentacoes = [];

        foreach($linhas as $linha) {
            $apresentacoes[] = $this->mapearApresentacao($linha);
        }

        return $apresentacoes;
    }
}
