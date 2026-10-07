<?php

declare(strict_types=1);

namespace TrocaDeTurno\Controllers;

/**
 * Requisicao — lê os parâmetros de onde vierem: ?query-string, form ou corpo JSON.
 * Isso permite TESTAR NO NAVEGADOR (só URL) e o front usar POST com JSON,
 * sem os controllers saberem a diferença.
 */
final class Requisicao
{
    /** @return array<string, mixed> */
    public static function dados(): array
    {
        $json = json_decode((string) file_get_contents('php://input'), true);

        return array_merge($_GET, $_POST, is_array($json) ? $json : []);
    }

    public static function texto(array $dados, string $chave): string
    {
        $valor = trim((string) ($dados[$chave] ?? ''));
        if ($valor === '') {
            throw new \InvalidArgumentException("Parâmetro \"$chave\" é obrigatório.");
        }
        return $valor;
    }

    public static function inteiro(array $dados, string $chave): int
    {
        $valor = (int) ($dados[$chave] ?? 0);
        if ($valor <= 0) {
            throw new \InvalidArgumentException("Parâmetro \"$chave\" é obrigatório (inteiro > 0).");
        }
        return $valor;
    }

    public static function inteiroOpcional(array $dados, string $chave): ?int
    {
        $valor = $dados[$chave] ?? null;
        return ($valor === null || $valor === '' || (int) $valor === 0) ? null : (int) $valor;
    }

    public static function booleano(array $dados, string $chave): bool
    {
        return filter_var($dados[$chave] ?? false, FILTER_VALIDATE_BOOLEAN);
    }
}
