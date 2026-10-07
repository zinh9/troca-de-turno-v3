# Troca de Turno v3 — contexto para o Claude Code

Sistema de troca de turno ferroviário (Vale, OP1/EFVM, Vitória-ES). Refatoração de ASP Classic + Access
para **PHP 8.5 + SQL Server** (back, `src/api`) e **JS vanilla ES Modules sem bundler** (front, repo/zip `troca-turno-v2`).
Autor: Zenzo (estagiário de TI). **Ele quer aprender**: explique como "para um macaco aprendendo a usar martelo e prego",
dê 1–2 arquivos de exemplo e deixe `TODO(Zenzo)` onde a regra de negócio é dúvida. Prazo curto.

## Arquitetura (back, `src/api`)
entities (espelham tabelas, `final readonly`) → repositories (PDO sqlsrv) → services (orquestram) → controllers (finos) →
`controllers/index.php` (rotas `match`). Montagem das peças em `container/dependencias.php`.
Regras de negócio **puras** (sem banco, relógio entra por parâmetro) ficam em `services/Regra*.php` e têm teste em `test/`.
Tempo real: **somente SSE** (`GET /api/eventos`). Todo service que grava chama `EventoPublisherInterface::publicar($idSupervisao,$idLocal)`.
Sem Redis, sem EventDispatcher (o publisher *é* o evento). `PainelService` monta o JSON da tela (`GET /api/painel`).
Autoload PSR-4 por pasta em `composer.json`; após mexer: `composer dump-autoload`. Testes: `vendor/bin/phpunit -c phpunit.xml`.

## Regras de negócio já implementadas
- **Apresentação**: matrícula + local. Supervisão do local ≠ cadastro → pede confirmação. Turno pela hora ≠ cadastro → pede
  confirmação e atualiza `empregado.id_turno`. Atrasado = depois de `horario_referencia.chegada` (local+turno) → select de
  justificativa. Fluxo em 2 passos: resposta `PRECISA_CONFIRMACAO`, front repete com `confirmarSupervisao/confirmarTurno=true`.
- **Prontidão**: 0–5 min mensagem "boa jornada, TAC e DSS" (backend recusa); 5–15 min botão verde; >15 min exige justificativa
  (status `PRONTO_COM_ATRASO_JUSTIFICADO`, hora vermelha). CCP vê "ACIONAR VIA RÁDIO" após 15 min (`POST /api/ccp/chamada`).
- **Lanche** (no clique da prontidão): só metade da equipe do local pode escolher `CEDO`; o resto é forçado `TARDE`.
- Respostas de escrita têm `status`: `OK | PRECISA_CONFIRMACAO | PRECISA_JUSTIFICATIVA | PRECISA_ESCOLHA_LANCHE | BLOQUEADO`.

## Pendências (buscar `TODO(Zenzo)` no código)
API "prontos" (liberação para atividade), arredondamento da metade do lanche, turnos que cruzam a meia-noite,
transações (turno+apresentação / contagem+lanche), nomes reais das colunas de `horario_referencia`, refeição e fim de jornada.

## Convenções
Português nos nomes de domínio; camelCase no JSON; colunas snake_case; comentários didáticos; sem lógica em controller/index.php;
nunca commitar credenciais (a senha antiga de `data/Connection.php` estava comentada no repo — **troque-a**).
