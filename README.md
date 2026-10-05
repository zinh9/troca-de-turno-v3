# Sistema Troca de Turno — Front-end (redesign)

Front-end reestruturado do sistema de controle de jornada ferroviária
("Troca de Turno") do pátio OP1/EFVM. Mantém a restrição original: **JS
puro em ES Modules, sem bundler, sem npm build step**, servido
diretamente por um servidor de arquivos estáticos (hoje IIS).

Este documento é o guia de handoff: o que cada pasta faz, como os dados
trafegam, e quais endpoints o back-end precisa expor.

---

## 1. Como rodar localmente

- `demo.html` — versão autocontida, usando `MockApiService`
  (`src/shared/services/api.service.mock-example.js`). Não precisa de
  back-end; basta abrir num navegador ou servir a pasta com qualquer
  servidor estático (`npx serve .`, por exemplo — não é dependência do
  projeto, só uma forma rápida de testar).
- `index.html` — versão real, aponta para `/api` (ver `window.APP_CONFIG`
  em `index.html`) e espera os endpoints da seção 4.

Três telas (ver seção 3): **Menu** (`#/menu`, tela de entrada — escolhe
o totem ou abre o CCP), **Pátio** (`#/apresentacao?...`, uso em totem
touch) e **CCP** (`#/ccp?...`, uso em telão de supervisão).

---

## 2. Arquitetura de pastas

```
/src
  /core            → bootstrap, DI leve e router (hash-based)
    di.js          → container de dependências (registerSingleton/resolve)
    router.js      → roteamento por #/rota?query=..., sem depender de config no servidor
    app.js         → fiação real (produção) — registra ApiService de verdade
    demo-bootstrap.js → mesma fiação, mas com MockApiService (só para demo.html)

  /modules/<etapa>
    <etapa>.module.js     → registra o service (e a rota, se a etapa tiver tela própria) no DI/router
    <etapa>.service.js    → chamadas de rede específicas da etapa
    <etapa>.entity.js     → reexport da entidade compartilhada (+ ViewModels locais futuros)
    <etapa>.component.js  → tela ou lógica de composição da etapa
    <etapa>.css           → estilo isolado por convenção de nome de classe

    Só têm ROTA registrada (tela navegável): menu, apresentacao, ccp,
    historico, indicadores. Os módulos prontidao/lanche/refeicao/
    fim-jornada existem só como esqueleto de domínio — ver "Por que a
    tela principal não é 'uma tela por etapa'" logo abaixo.

  /shared
    /components   → StatusBadge, Cronometro, Modal, AppHeader, TabelaJornada, CelulaIntervalo
    /services     → ApiService (fetch central), TimeService (relógio sincronizado), SmsService
    /entities     → Empregado, EtapaJornada (+ subclasses Apresentacao/Prontidao/Intervalo/FimJornada)
    /utils        → enums.js, formatters.js, icons.js

  /styles
    tokens.css     → design system (cores, tipografia, espaçamento, sombras)
    base.css       → reset + classes utilitárias (.superficie-glass, .fonte-mono, ...)
```

### Por que a tela principal não é "uma tela por etapa"

O briefing original imaginava uma tela por etapa da jornada. Mas o
sistema precisa ser **dinâmico e rápido para o empregado e para o
CPT** — e o sistema em produção reflete isso: **uma única tabela
mostra a jornada inteira** de cada empregado, linha a linha, como uma
linha do tempo (ver prints anexados no chat). Por isso:

- Não existem mais rotas/páginas separadas de apresentação, prontidão,
  lanche, refeição ou fim de jornada — só as telas de **Menu**, **Pátio**
  e **CCP** (ver seção 3), mais Histórico e Indicadores (que são visões
  complementares, não duplicatas de etapa).
- `modules/apresentacao/apresentacao.component.js` compõe a tela de
  **Pátio inteira** (cabeçalho + matrícula + tabela com as 5 etapas).
- `modules/ccp/ccp.component.js` compõe a tela de **CCP inteira**
  (mesma tabela, colunas extras, ações de supervisor).
- `shared/components/tabela-jornada.component.js` é o componente que
  desenha a tabela em si, reutilizado pelas duas telas com uma prop
  `variante: 'totem' | 'ccp'` que troca as colunas e os rótulos de ação.
- Os módulos `prontidao/lanche/refeicao/fim-jornada` continuam
  existindo em `/src/modules`, mas só como esqueleto de domínio
  (service/entity) — não são mais páginas navegáveis nem estão
  registrados no router. Servem de lugar para regra de negócio futura
  (ex.: uma tela de **detalhe**, tipo um modal ao clicar numa célula da
  tabela com o histórico daquela etapa), sem duplicar lógica que já
  mora nas entidades compartilhadas.

---

## 3. Telas

### 3.1 Menu — `#/menu` (rota padrão)

- Tela de entrada do sistema. Lista cada supervisão com:
  - um **link por local/guarita** para o totem de Pátio daquele local
    (`#/apresentacao?supervisao=VPN&local=Guarita_2`);
  - um **único link de CCP** para toda a supervisão
    (`#/ccp?supervisao=VPN`) — o CPT não precisa de um link por
    guarita, já que ele supervisiona todas de uma vez.
- Serve tanto para o empregado escolher seu totem quanto para o time
  de TI copiar o link certo e fixá-lo permanentemente no totem físico
  daquela guarita (ou no monitor do CPT).
- Dados vêm de `GET /api/estrutura` (ver seção 4.3 — endpoint novo).

### 3.2 Pátio (totem) — `#/apresentacao?supervisao=<sup>&local=<local>`

- Marca + título em âmbar, relógio ao vivo, campo de matrícula +
  **Apresentar** (ação azul) + **Assinar DSS** (desabilitado até o
  fluxo de DSS ser definido — hoje é só visual).
- Tabela: `TAC | Apres. | Pront. | Lanche | Refeição | Fim Jornada`.
- O próprio empregado interage (toque): justifica atraso de prontidão,
  inicia lanche/refeição quando liberado, finaliza a jornada.
- `supervisao`/`local` vêm da querystring (ver seção "Por que
  querystring" no topo de `core/router.js`); sem eles, cai num
  contexto de exemplo (`VPN`/`Guarita_2`).

### 3.3 CCP (supervisão) — `#/ccp?supervisao=<sup>`

- Título em vermelho (`--cor-titulo-ccp`), trilho ferroviário animado
  abaixo do cabeçalho (identidade visual do pátio ferroviário).
- **Um único link por supervisão** — sem seletor de torre na tela; o
  CPT abre sempre o mesmo link, fixo, que já mostra todas as guaritas
  daquela supervisão. Se o CPT supervisiona mais de uma supervisão,
  ele usa o link de Menu para trocar.
- Tabela: `TAC | Cargo | Local | Apresentação | Prontidão | Chamada |
  Lanche | Refeição | Fim de Jornada`.
- O supervisor age **em nome** do empregado: aciona chamada via rádio
  quando a prontidão excede a tolerância (rótulo **EXCEDEU**, âmbar),
  libera lanche/refeição em lote.

### Diferenças conhecidas vs. o sistema em produção (não implementadas ainda)

- A coluna Lanche do CCP no print de produção tem um estado adicional
  ("15:21→ACIONAR", contagem decorrida + chamada à ação) que não foi
  modelado separadamente — hoje ele cai no mesmo estado `EM_ANDAMENTO`/
  `ATRASADO` da coluna Refeição. Se esse estado for importante,
  formalize-o como um valor novo do enum `STATUS_INTERVALO`
  (`shared/utils/enums.js`) e trate em `celula-intervalo.component.js`.
- O botão **Bom Descanso** hoje está sempre habilitado — a regra de
  quando ele deveria habilitar (ex.: só após refeição concluída) ainda
  não foi confirmada com o negócio.
- Os dois indicadores (bolinhas) ao lado do nome hoje mapeiam
  "apresentação confirmada" e "prontidão confirmada". Se o significado
  real for outro (ex.: TAC assinado + presença), ajuste em
  `tabela-jornada.component.js` → `celNome()`.
- `GET /api/painel` para o CCP hoje é chamado só com `supervisao` (sem
  `local`) — o back-end precisa devolver os empregados de **todas** as
  guaritas daquela supervisão quando `local` não vier na query.

---

## 4. Contrato de API (JSON)

### 4.1 Painel (leitura)

```
GET /api/painel?supervisao=<TORRE_A>&local=<Guarita_7>
```

Resposta — mesmo shape do briefing original, sem mudanças:

```json
{
  "success": true,
  "info": {
    "emManutencao": false,
    "ultimaAtualizacao": "2026-09-15T18:06:53",
    "serverTime": "2026-09-15T18:06:55",
    "supervisao": "VPN",
    "local": "Porto Velho",
    "horarioReferencia": { "chegada": "18:00", "saida": "06:00" }
  },
  "empregados": [
    {
      "matricula": "81053394",
      "nome": "ISMAYLER TAVARES",
      "cargo": "MAQ",
      "supervisao": "VPN",
      "local": "Guarita_2",
      "turno": "18x06",
      "jornada": {
        "apresentacao": { "dataHora": "2026-09-15T18:06:00", "status": "OK", "justificativa": null },
        "prontidao": {
          "dataHora": null, "status": "AGUARDANDO", "justificativa": null,
          "tempoApresentacaoProntidaoMin": null, "tempoHorarioExatoProntidaoMin": null,
          "fase": "ATRASADO_JUSTIFICAR"
        },
        "tac": { "realizado": true },
        "lanche": {
          "intervaloEscolhido": null, "janela": "02:00 às 04:30", "status": "AGUARDANDO_JANELA",
          "dataHoraInicio": null, "dataHoraProntidao": null, "justificativa": null, "liberadoCCP": null
        },
        "refeicao": {
          "janela": "00:00 às 01:00", "status": "LIBERADO_PARA_ACAO", "dataHoraInicio": null,
          "dataHoraProntidao": null, "justificativa": null, "liberadoCCP": true
        },
        "fimJornada": { "dataHora": null, "atrasado": false, "justificativa": null, "chamadaCPT": null, "fimJornadaCPT": null }
      }
    }
  ]
}
```

Enum de status de Lanche/Refeição (unificado — ver `enums.js`):
`AGUARDANDO_ESCOLHA | AGUARDANDO_JANELA | LIBERADO_PARA_ACAO |
EM_ANDAMENTO | AGUARDANDO_REFEICAO | ATRASADO | CONCLUIDO`.

No CCP, a mesma rota é chamada só com `supervisao` (sem `local`) —
`GET /api/painel?supervisao=VPN` deve devolver os empregados de
**todas** as guaritas daquela supervisão.

### 4.2 Estrutura (leitura — alimenta a tela de Menu)

```
GET /api/estrutura
```

```json
{
  "success": true,
  "supervisoes": [
    { "nome": "VPN", "label": "VPN - Porto Velho", "locais": ["Guarita_2", "Guarita_3", "Guarita_4"] },
    { "nome": "TORRE_A", "label": "Torre A - Guarita 7", "locais": ["Guarita_7"] }
  ]
}
```

`nome` é o valor usado nas querystrings (`?supervisao=VPN`); `label` é
só o texto de exibição na tela de Menu. **Endpoint novo — ainda não
existe no back-end.**

### 4.3 Ações (escrita)

Todas via `ApiService` (`shared/services/api.service.js`) — nenhum
component chama `fetch` diretamente.

| Método | Endpoint                       | Body                                           | Usado por                    |
|--------|--------------------------------|-------------------------------------------------|-------------------------------|
| POST   | `/api/apresentacao`            | `{ matricula }`                                 | Pátio — botão Apresentar      |
| POST   | `/api/prontidao`                | `{ matricula }`                                 | (confirmação automática/manual) |
| POST   | `/api/lanche/escolha`           | `{ matricula, intervaloEscolhido }`             | Escolha CEDO/TARDE             |
| POST   | `/api/lanche/iniciar`           | `{ matricula }`                                 | Pátio (Start Lanche) / CCP (Liberar Lanche) |
| POST   | `/api/refeicao/iniciar`         | `{ matricula }`                                 | Pátio (Start Refeição) / CCP (Liberar Refeição) |
| POST   | `/api/prontidao/justificativa`  | `{ matricula, texto }`                          | Dropdown de justificativa (Pront.) |
| POST   | `/api/lanche/justificativa`     | `{ matricula, texto }`                          | Dropdown de justificativa (Lanche) |
| POST   | `/api/refeicao/justificativa`   | `{ matricula, texto }`                          | Dropdown de justificativa (Refeição) |
| POST   | `/api/ccp/chamada`              | `{ matricula }`                                 | CCP — botão "Acionar via Rádio" (**novo**, ainda não implementado no back-end) |
| POST   | `/api/fim-jornada`              | `{ matricula }`                                 | Botão "Bom Descanso" (**novo**, ainda não implementado no back-end) |
| GET    | `/api/indicadores?...`          | —                                                | Painel de indicadores/Chart.js — hoje calculado no cliente a partir do painel; recomendo agregar no servidor quando o volume crescer |

Todas as respostas de escrita devem seguir `{ "success": true|false,
"mensagem"?: string }` — é o shape que `ApiService.#request` já espera
para lançar `ApiError` em caso de falha.

---

## 5. Design system

`src/styles/tokens.css` — paleta azul-petróleo (`--cor-fundo-0` a
`--cor-fundo-3`) + âmbar institucional (`--cor-ambar-500 = #ECB11F`,
título do Pátio) + vermelho de supervisão (`--cor-titulo-ccp`, título do
CCP) + semânticos ok/alerta/crítico/info. Dois contextos de escala via
`[data-contexto="totem"|"painel"]` no elemento raiz de cada tela —
totem prioriza alvo de toque (`--alvo-toque-min`), painel prioriza
tamanho de fonte para leitura a distância (telão de CCP).

---

## 6. Notas sobre a evolução do stack (para não esquecer)

- **Back-end em C# .NET** — decisão tomada: o back-end do ASP Classic
  vai ser reescrito em C# .NET (não PHP, como era a ideia anterior).
  Como todo o acesso a rede do front passa por `ApiService`, a troca de
  linguagem/framework é transparente para o front **desde que o shape
  JSON do contrato (seção 4) continue o mesmo** — se mudar algo durante
  a migração, o único arquivo a ajustar é `shared/services/api.service.js`.
- **SQL Server Express 2025** já está no servidor — o contrato JSON
  não muda nada por causa disso; é só a fonte de dados atrás do
  endpoint `/api/painel` migrar de Access para SQL Server.
- **Alertas de cada etapa da jornada nativos no C#**: como o back-end
  em C# .NET vai servir tanto a API quanto (possivelmente) o serviço de
  notificação, dá para nascer isso já integrado, sem depender de um
  serviço externo fazendo polling:
  1. O mesmo processo/API que responde `/api/painel` já sabe quando um
     empregado está atrasado (a lógica de "quando é atraso" hoje vive
     no `TimeService`/entidades do front — replique essa mesma regra
     no C#, não reinvente); ao detectar, ele dispara a notificação
     nativa do Windows diretamente no CPT correspondente; ou
  2. Se preferir manter os dois desacoplados, o back-end expõe um
     webhook/fila (SignalR, ou um endpoint de long-polling) e um
     serviço Windows separado só consome isso e notifica.
  Como o back-end já vai ser C#, a opção 1 tende a ser mais simples de
  manter do que ter uma linguagem a mais só para o alerta.
