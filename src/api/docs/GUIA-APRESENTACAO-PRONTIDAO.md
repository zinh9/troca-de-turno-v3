# Guia: Apresentação + Prontidão (explicado para o macaco)

## 1. A ideia em uma imagem

```
Totem (front)  ──POST──►  index.php ──► Controller ──► Service ──► Repository ──► SQL Server
                                                         │  ▲
                                                         │  └── consulta Regra*  (cérebro puro, testável)
                                                         └──► Publisher.publicar()  ──► SSE "jornada-atualizada"
Todas as telas abertas ◄── SSE avisa ──► front chama GET /api/painel ──► PainelService monta o JSON
```

* **Regra\*** = cérebro. Só recebe números/datas e responde sim/não. Não conhece banco. (`RegraApresentacao`, `RegraProntidao`, `RegraLanche`)
* **Service** = gerente. Busca dados, pergunta ao cérebro, manda gravar, avisa o publisher.
* **Publisher** = o "evento". Chamou `publicar($idSupervisao,$idLocal)` → o SSE acorda todas as telas daquela supervisão.
  Você **não** precisa de `Events/Dispatcher/Redis` nisso. (Os arquivos em `events/` ficam sem uso; pode apagar depois.)
* **PainelService** = tradutor. Lê Apresentacao + Prontidao + Lanche + Empregado e monta o JSON que o front desenha.

## 2. Como ligar uma regra nova (receita de 6 passos)
1. Regra pura em `services/RegraXxx.php` + teste em `test/`.
2. Método no Service que usa a regra e, **depois de gravar**, chama `$this->eventoPublisher->publicar(...)`.
3. Método no Repository (SQL).
4. Método fino no Controller (`Requisicao::dados()` → service → array).
5. Uma linha no `match` do `controllers/index.php` e uma em `container/dependencias.php`.
6. Se a tela precisa ver o resultado: campo novo em `PainelService::montar...`.

## 3. Endpoints (testáveis direto na URL do navegador)
| Rota | Parâmetros | Respostas possíveis (`status`) |
|---|---|---|
| `/api/apresentacao` | `matricula, idLocal, confirmarSupervisao?, confirmarTurno?` | `PRECISA_CONFIRMACAO` (com `confirmacoes[]`) ou `OK` (`idApresentacao, atrasado, turnoAtualizado`) |
| `/api/apresentacao/justificativa` | `idApresentacao, idJustificativa` | `OK` |
| `/api/prontidao` | `idApresentacao, idJustificativa?, escolhaLanche?` (`CEDO`/`TARDE`) | `PRECISA_JUSTIFICATIVA`, `PRECISA_ESCOLHA_LANCHE` (com `opcoes[]`), `BLOQUEADO`, `OK` |
| `/api/ccp/chamada` | `idApresentacao` | `OK` (só após 15 min, sem prontidão) |
| `/api/painel` | `idSupervisao, idLocal?` (sem `idLocal` = CCP) | JSON do contrato |
| `/api/eventos` | `idSupervisao, idLocal?` | SSE `jornada-atualizada` |

Padrão "tudo ou nada": quando a resposta é `PRECISA_*`, **nada foi gravado**. O front mostra o diálogo e **repete a mesma
chamada** com o campo extra. Isso evita estado "meio gravado" no banco.

Rodar: `cd src/api` → `composer dump-autoload` → (Windows) `set PHP_CLI_SERVER_WORKERS=4` → `php -S localhost:8000 -t controllers controllers/index.php`.
(Os workers são necessários porque o SSE prende uma conexão aberta.)

## 4. Roteiro de teste no navegador
1. `/api/apresentacao?matricula=SUA_MATRICULA&idLocal=1` → se vier `PRECISA_CONFIRMACAO`, repita acrescentando `&confirmarTurno=1&confirmarSupervisao=1`.
2. `/api/painel?idSupervisao=1&idLocal=1` → o empregado aparece com `prontidao.fase = TOLERANCIA_INICIAL`.
3. Antes dos 5 min `/api/prontidao?idApresentacao=ID` → erro 409 "Aguarde". Depois: `PRECISA_ESCOLHA_LANCHE` → repita com `&escolhaLanche=CEDO`.
4. Em outra aba, deixe `/api/eventos?idSupervisao=1` aberto: a cada gravação aparece `event: jornada-atualizada`.
5. Para testar atraso sem esperar: no SSMS, `UPDATE apresentacao SET data_hora_apresentacao = DATEADD(MINUTE,-20,SYSDATETIME()) WHERE id_apresentacao = ID`.

## 5. O que o front precisa mudar (ApiService)
* `registrarApresentacao(matricula, idLocal, {confirmarSupervisao, confirmarTurno})` → se `status==='PRECISA_CONFIRMACAO'`, abrir modal com `confirmacoes[].mensagem` e repetir.
* `registrarProntidao(idApresentacao, {idJustificativa, escolhaLanche})` → tratar `PRECISA_JUSTIFICATIVA` (abrir select) e `PRECISA_ESCOLHA_LANCHE` (modal manhã/tarde com `opcoes`).
* Prontidão no totem: usar `jornada.prontidao.liberaEm`/`atrasaEm` para ligar **timers locais** (mensagem → botão verde → exige justificativa). O servidor reconfere tudo, o timer é só visual.
* CCP: botão "ACIONAR VIA RÁDIO" quando `prontidao.podeAcionarRadio === true` → `POST /api/ccp/chamada {idApresentacao}`.
* Apresentação: `status JUSTIFICAR` → select na coluna; hora branca (`OK`) ou amarela (`JUSTIFICAR`/`JUSTIFICATIVA_OK`). Tooltip do nome = `matricula`. Prontidão: verde (`PRONTO`) / vermelho (`PRONTO_COM_ATRASO`).

## 6. Onde estão as dúvidas (procure `TODO(Zenzo)`)
* Janelas de horário dos turnos (`RegraApresentacao::turnoPelaHora`) e turnos que viram a noite.
* "Metade" do lanche: arredonda pra cima (padrão) ou baixo? "Equipe" = quem se apresentou no local (padrão) ou os cadastrados?
* API "prontos": `ProntidaoService::estaLiberadoParaAtividade()` devolve `true` por enquanto.
* `prontidao.data_hora_prontidao` precisa aceitar NULL (veja `docs/SQL-MUDANCAS.sql`) para o "ACIONAR VIA RÁDIO".
* Nomes das colunas de `horario_referencia` (corrigi o typo `horario_referecia`).
* Transações e cliques simultâneos.

## 7. O que eu corrigi no seu código (para você entender os diffs)
`buscarApresentacaoHojePorIdEmpregado` selecionava só o id e quebrava o mapper · `(int) null` virava 0 em justificativas ·
mappers que sobrescreviam o array (`$x = ...` em vez de `$x[] = ...`) em Empregado/Local/Turno · `LancheRepository` mapeava
ids como `DateTimeImmutable` · a verificação de supervisão/turno estava **invertida** (barrava quem estava certo) ·
`DATEDIFF(HOURS` → `MINUTE` · namespaces em minúsculo · `EventosController` recebia `string` e a interface `int`
(TypeError com `strict_types`) · `paraArray()` devolvia objetos de data · `index.php` chamava método comentado ·
PSR-4 agora por pasta (funciona em Linux e Windows). Os scripts soltos `testRoute.php`, `test/testApresentacao.php`
e `test/testProntidao.php` usam os construtores antigos: apague ou atualize (use `container/dependencias.php`).

## 8. Testes
`vendor/bin/phpunit -c phpunit.xml` (15 testes das regras puras). Padrão: ARRANGE (monta) → ACT (executa) → ASSERT (confere).
Services dependem de classes `final` (não dá para "mockar"); quando quiser testá-los sem banco, crie interfaces para os repositories.

## 9. Usar o Claude Code na sua máquina SEM perder o contexto deste chat
1. Instale e abra na raiz do repo: `cd troca-de-turno-v3` → `claude`.
2. O arquivo **`CLAUDE.md`** (já criado na raiz) é lido automaticamente em toda sessão: ele resume arquitetura, regras, pendências e o seu pedido de "explicar como pra um macaco". Mantenha-o atualizado — é a "memória" do projeto. No fim de cada sessão peça: "atualize o CLAUDE.md com o que decidimos hoje".
3. Comece cada sessão com: *"leia o CLAUDE.md e src/api/docs/GUIA-APRESENTACAO-PRONTIDAO.md e me diga o próximo passo"*.
4. `/clear` entre tarefas diferentes; `claude --continue` (ou `/resume`) retoma a última sessão.
5. Coloque em `docs/` o que não está no código (UML em PDF, prints das telas, regras do negócio como você me passou) e referencie com `@docs/arquivo`.
6. Peça sempre: "implemente a regra X, deixe TODO(Zenzo) nas dúvidas e explique o que mudou", e faça `git commit` por tarefa para poder voltar atrás.
