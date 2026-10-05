<?php

declare(strict_types=1);

namespace TrocaDeTurno\Entities;

final readonly class Empregado
{
    public function __construct(
        public int $idEmpregado,
        public string $nome,
        public string $matricula,
        public string $cargo,
        public string $turno,
        public ?\DateTimeImmutable $dataHoraUltimaAtualizacao,
        public int $idSupervisao,
        public int $idTurno
    ) {
    }

    public function paraArray(): array
    {
        return [
            'idEmpregado' => $this->idEmpregado,
            'nome' => $this->nome,
            'matricula' => $this->matricula,
            'cargo' => $this->cargo,
            'turno' => $this->turno,
            'dataHoraUltimaAtualizacao' =>
                $this->dataHoraUltimaAtualizacao->format('Y-m-d H:i:s'),
            'idSupervisao' => $this->idSupervisao,
            'idTurno' => $this->idTurno
        ];
    }
}