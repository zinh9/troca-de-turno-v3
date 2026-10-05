<?php

declare(strict_types=1);

namespace TrocaDeTurno\Container;

/**
 * Container — igualzinho ao `di.js` do front (mesma ideia, traduzida):
 * cada peça registra uma "receita" (uma closure) de como se cria; o
 * container garante que só existe UMA instância viva por nome
 * (singleton) e resolve dependências entre si passando a si mesmo pra
 * cada fábrica.
 *
 * Por que não pegar um pacote pronto (league/container, PHP-DI, etc)?
 * Pra um projeto deste tamanho, essas ~20 linhas resolvem o mesmo
 * problema sem você precisar aprender a API de mais uma biblioteca —
 * e mantém a mesma filosofia "vanilla" que o front já segue.
 *
 * Uso:
 *   $container->registrarSingleton('conexaoSql', fn() => new ConexaoSqlServer(...));
 *   $container->registrarSingleton('justificativaRepository',
 *     fn(Container $c) => new JustificativaRepository($c->resolver('conexaoSql')));
 *   ...
 *   $repo = $container->resolver('justificativaRepository');
 */
final class Container
{
    /** @var array<string, callable> */
    private array $fabricas = [];

    /** @var array<string, mixed> */
    private array $instancias = [];

    public function registrarSingleton(string $nome, callable $fabrica): void
    {
        $this->fabricas[$nome] = $fabrica;
    }

    public function resolver(string $nome): mixed
    {
        if (array_key_exists($nome, $this->instancias)) {
            return $this->instancias[$nome];
        }

        if (!isset($this->fabricas[$nome])) {
            throw new \RuntimeException("[container] Dependência não registrada: \"{$nome}\"");
        }

        $instancia = ($this->fabricas[$nome])($this);
        $this->instancias[$nome] = $instancia;
        return $instancia;
    }
}