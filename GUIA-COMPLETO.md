# Guia Completo do Front-end — Sistema Troca de Turno

> Este documento existe para uma única razão: se daqui a 6 meses você (ou
> outra pessoa) abrir essa pasta e não lembrar de nada, dá para ler este
> arquivo do começo ao fim e entender **tudo** — sem pular etapas, sem
> assumir que você já sabe o que é "ES Module" ou "injeção de dependência".
> Vou explicar como se você nunca tivesse visto esse projeto na vida.
>
> Sempre que eu disser "abra o arquivo X", é porque vale a pena ter ele
> aberto do lado enquanto lê o parágrafo — o texto faz muito mais sentido
> olhando o código junto.

---

## Índice

1. [A ideia em uma frase](#1-a-ideia-em-uma-frase)
2. [Antes de tudo: por que não tem "npm install"?](#2-antes-de-tudo-por-que-não-tem-npm-install)
3. [O mapa da cidade (visão geral das pastas)](#3-o-mapa-da-cidade-visão-geral-das-pastas)
4. [Os três "funcionários" que fazem tudo rodar](#4-os-três-funcionários-que-fazem-tudo-rodar)
5. [A viagem completa de um dado: da API até a tela](#5-a-viagem-completa-de-um-dado-da-api-até-a-tela)
6. [O Design System: como as cores e tamanhos funcionam](#6-o-design-system-como-as-cores-e-tamanhos-funcionam)
7. [As telas, uma por uma](#7-as-telas-uma-por-uma)
8. [O coração visual: a Tabela de Jornada](#8-o-coração-visual-a-tabela-de-jornada)
9. [Onde mexer quando você precisar mudar alguma coisa (guia prático)](#9-onde-mexer-quando-você-precisar-mudar-alguma-coisa-guia-prático)
10. [Como testar sem precisar do back-end (o "modo mentira")](#10-como-testar-sem-precisar-do-back-end-o-modo-mentira)
11. [Glossário — palavras estranhas explicadas](#11-glossário--palavras-estranhas-explicadas)
12. [O que ainda falta / pontas soltas](#12-o-que-ainda-falta--pontas-soltas)

---

## 1. A ideia em uma frase

> **Uma página HTML carrega um monte de arquivos `.js` pequenos, cada um
> com uma responsabilidade só, e eles conversam entre si trocando
> objetos JavaScript — sem nenhuma ferramenta de build no meio.**

Isso é tudo. O resto deste documento é só detalhar essa frase.

Pense assim: imagine uma linha de montagem de fábrica. Cada estação da
linha faz **uma coisa só** (uma estação parafusa, outra pinta, outra
embala). Ninguém tenta fazer tudo numa estação só, porque aí vira bagunça
e ninguém entende o que está acontecendo. Este projeto é essa linha de
montagem, só que em código: cada arquivo é uma "estação" com um trabalho
bem definido.

---

## 2. Antes de tudo: por que não tem "npm install"?

Se você já mexeu com projetos React/Vue modernos, deve estranhar que não
tem `package.json` com dependências, não tem Webpack, Vite, nada disso.
Isso é **proposital**, não preguiça:

- O servidor (IIS) só sabe servir arquivos estáticos — HTML, CSS, JS,
  imagens — do jeito que eles estão na pasta. Ele não "compila" nada.
- Navegadores modernos (Chrome, Edge) já sabem ler um tipo especial de
  JavaScript chamado **ES Module** direto, sem precisar de nenhuma
  ferramenta. Isso é o que a tag abaixo, que está no `index.html`, faz:

```html
<script type="module" src="./src/core/app.js"></script>
```

O `type="module"` é a mágica: ele diz ao navegador "esse arquivo pode
usar `import` e `export` para puxar outros arquivos `.js`, sem precisar
de bundler nenhum". É por isso que você vê `import { algumaCoisa } from
'./outro-arquivo.js'` no topo de quase todo arquivo do projeto — é o
navegador buscando aquele arquivo sozinho, na hora, via rede.

**Vantagem:** zero passo de build, zero coisa para "quebrar" entre o
código e o que roda no navegador. O que você escreve é literalmente o
que roda.
**Desvantagem:** você não tem as firulas de frameworks modernos (JSX,
hot-reload chique, etc.) — mas para um totem de pátio que precisa ser
robusto e simples de dar manutenção via um servidor IIS velho, isso é
uma vantagem disfarçada de limitação.

---

## 3. O mapa da cidade (visão geral das pastas)

```
troca-turno-v2/
├── index.html          ← a porta de entrada REAL (usa a API de verdade)
├── demo.html           ← a porta de entrada de TESTE (usa dados fake)
├── README.md           ← documentação técnica mais enxuta (endpoints, contrato)
└── src/
    ├── core/           ← o "motor" da aplicação (não tem nada de visual aqui)
    ├── modules/        ← uma pasta por "assunto" (menu, apresentação, ccp, ...)
    ├── shared/         ← peças reutilizáveis por todo mundo
    └── styles/         ← as cores, fontes e espaçamentos do sistema
```

Pense em `core` como a **sala de máquinas de um navio**: ninguém vê ela,
mas é o que faz o navio andar. `modules` são os **cômodos do navio** (a
cozinha, o convés, a cabine do capitão) — cada um serve um propósito
diferente. `shared` é a **caixa de ferramentas comum** que qualquer
cômodo pode pegar emprestado (uma chave de fenda que tanto a cozinha
quanto o convés usam). `styles` é a **pintura e decoração** — a mesma em
todo o navio, para tudo parecer consistente.

### 3.1 Dentro de `core/`

| Arquivo | O que faz | Analogia |
|---|---|---|
| `di.js` | Guarda "receitas" de como criar cada serviço, e entrega o mesmo objeto toda vez que alguém pedir | Uma **despensa**: você não recria o pote de sal toda vez que precisa de sal, você vai lá e pega o mesmo pote |
| `router.js` | Olha o que está escrito depois do `#` na URL e decide qual tela mostrar | Uma **plaquinha de "Você está aqui"** num shopping — olha o texto e te manda pro corredor certo |
| `app.js` | O "liga tudo" — registra os serviços e as rotas, e manda o `router` começar a funcionar | O **maestro** que dá o sinal pra orquestra começar a tocar |
| `demo-bootstrap.js` | Igual o `app.js`, mas troca a API de verdade por uma API de mentirinha | O mesmo maestro, só que ensaiando com uma orquestra de brinquedo |

### 3.2 Dentro de `modules/`

Cada subpasta (`menu/`, `apresentacao/`, `ccp/`, `historico/`,
`indicadores/`, e mais 4 que existem mas **não têm tela própria hoje**:
`prontidao/`, `lanche/`, `refeicao/`, `fim-jornada/`) segue **sempre o
mesmo padrão de 5 arquivos**:

```
<nome>.module.js     → "eu existo, aqui está minha rota e meu serviço"
<nome>.service.js     → "eu sei conversar com a API sobre esse assunto"
<nome>.entity.js       → "eu sei o que é um objeto desse assunto (classe)"
<nome>.component.js  → "eu sei desenhar a tela/pedaço desse assunto"
<nome>.css            → "eu sei deixar isso bonito"
```

Isso é o mesmo padrão que o **Angular** usa (só que sem o Angular de
verdade rodando por baixo — copiamos só a ideia de organização).

### 3.3 Dentro de `shared/`

| Pasta | O que tem lá |
|---|---|
| `components/` | Pedaços de tela reutilizáveis: `TabelaJornada` (a tabela gigante), `Cronometro`, `Modal`, `AppHeader`, etc. |
| `services/` | `ApiService` (fala com a rede), `TimeService` (o relógio), `SmsService` (avisos) |
| `entities/` | As "classes" que representam um Empregado e uma Etapa da Jornada |
| `utils/` | Funções pequenas de apoio: formatar hora, ícones SVG, os "enums" (listas de valores fixos) |

---

## 4. Os três "funcionários" que fazem tudo rodar

Antes de mergulhar em telas, você precisa entender 3 peças que aparecem
**em praticamente todo arquivo** do projeto. Sem entender essas três,
o resto não faz sentido.

### 4.1 O Container de Injeção de Dependência (`di.js`)

"Injeção de dependência" parece um nome assustador, mas é uma ideia bem
simples: **em vez de cada pedaço de código criar suas próprias
ferramentas toda hora, existe um lugar central que cria uma vez e
empresta pra quem precisar.**

```js
// alguém registra a "receita" de como criar o ApiService
container.registerSingleton('apiService', () => new ApiService({...}));

// depois, qualquer arquivo do projeto pode pedir o MESMO ApiService
const apiService = container.resolve('apiService');
```

Por que isso importa? Porque assim só existe **um** `ApiService` vivo
na aplicação inteira, com uma configuração só, em vez de cada tela criar
o seu (o que ia desperdiçar memória e complicar se um dia você quiser
mudar a URL da API — mudaria só num lugar).

Analogia: é tipo a **recepção de um prédio** guardando as chaves. Você
não faz uma cópia da chave da sala de reunião toda vez que precisa
usá-la — você pede na recepção, que sempre te entrega a mesma chave.

### 4.2 O Roteador (`router.js`)

O roteador olha o que está escrito na URL **depois do `#`** e decide
qual "tela" (função) chamar. Por exemplo:

```
#/menu                                     → chama a tela de Menu
#/apresentacao?supervisao=VPN&local=Guarita_2  → chama a tela de Pátio, avisando qual guarita
#/ccp?supervisao=VPN                       → chama a tela de CCP, avisando qual supervisão
```

Por que usamos `#` (chamado de "hash") em vez de uma URL "normal" tipo
`/apresentacao/VPN/Guarita_2`? Porque o servidor IIS, quando você aperta
F5 numa URL "normal" dessas, tentaria achar uma **pasta de verdade**
chamada `apresentacao` no disco — e não existe, então dava erro 404. Com
`#`, o navegador **nunca manda essa parte pro servidor** — só o próprio
JavaScript lê aquilo. Então dá pra apertar F5 à vontade que nunca quebra.

E o que vem depois do `?` (a "querystring", tipo
`supervisao=VPN&local=Guarita_2`)? São **parâmetros** — como se fossem
os campos de um formulário, só que escritos na própria URL. O roteador
transforma isso num objeto (`URLSearchParams`) e entrega pra tela, que
lê assim:

```js
// dentro de apresentacao.component.js
const supervisao = params?.get('supervisao') ?? 'VPN'; // "VPN" é o valor padrão se não vier nada
```

### 4.3 O `app.js` (o maestro)

Esse arquivo só faz uma coisa: **liga as duas peças acima**. Ele diz
"ei, `di`, guarda a receita do `ApiService`" e depois "ei, `router`,
aqui estão todas as rotas que existem". Depois manda o `router` começar
a prestar atenção na URL.

**Regra de ouro deste arquivo: ele nunca tem lógica de negócio.** Se
você um dia abrir o `app.js` e ver um `if` complicado calculando algo,
está no lugar errado — essa lógica devia estar em algum `.service.js`
ou `.entity.js`.

---

## 5. A viagem completa de um dado: da API até a tela

Vou seguir **um dado só** — o horário de apresentação de um empregado —
desde que ele nasce numa resposta JSON até aparecer coloridinho na tela.
Isso é o exercício mais importante deste documento, porque depois disso
você consegue seguir qualquer outro dado sozinho.

### Passo 1 — A API devolve um JSON "burro"

```json
{
  "matricula": "81053394",
  "nome": "ISMAYLER TAVARES",
  "jornada": {
    "apresentacao": { "dataHora": "2026-09-15T18:06:00", "status": "OK" }
  }
}
```

Isso é só texto. Não sabe "o que é", não tem métodos, não sabe se está
atrasado ou não. É só dado cru.

### Passo 2 — O `ApiService` busca esse JSON

Arquivo: `src/shared/services/api.service.js`

```js
async buscarPainel({ supervisao, local }) {
  const json = await this.#request(`/painel?...`);
  return {
    empregados: mapearEmpregados(json.empregados), // ← aqui a mágica começa
  };
}
```

Repare: **nenhum outro arquivo do projeto tem permissão de chamar
`fetch()` diretamente.** Só o `ApiService`. Isso é de propósito: se um
dia a URL da API mudar, ou precisar adicionar um cabeçalho de
autenticação, só existe **um lugar** para mexer.

### Passo 3 — O JSON burro vira uma "classe" esperta

Arquivo: `src/shared/entities/empregado.entity.js` e
`src/shared/entities/etapa-jornada.entity.js`

```js
export class Apresentacao extends EtapaJornada {
  estaAtrasado() {
    return this.dados.status === STATUS_APRESENTACAO.JUSTIFICAR;
  }
}
```

Isso é o pulo do gato de programação orientada a objetos: em vez de
toda hora escrever `if (empregado.jornada.apresentacao.status ===
'JUSTIFICAR')` espalhado em 10 lugares do código, você escreve **uma
vez** o método `estaAtrasado()`, e daí em qualquer lugar do projeto você
só chama `apresentacao.estaAtrasado()` e pronto — a regra mora num
lugar só.

Analogia: é a diferença entre você explicar pra 10 pessoas diferentes
"o forno está quente quando o número mostra mais de 200°C" toda vez que
perguntam, contra ter um único termômetro com uma luzinha vermelha que
acende sozinha. Você constrói o termômetro (a classe) uma vez, e todo
mundo só olha a luz.

### Passo 4 — O componente da tabela pergunta pra classe, não pro JSON

Arquivo: `src/shared/components/tabela-jornada.component.js`

```js
function celApresentacao(apresentacao) {
  const el = document.createElement('span');
  el.dataset.atencao = String(apresentacao.estaAtrasado()); // ← usa o método, não o JSON cru
  el.textContent = formatarHora(apresentacao.dados.dataHora);
  return el;
}
```

### Passo 5 — O CSS decide a cor com base nesse atributo

Arquivo: `src/shared/components/tabela-jornada.css`

```css
.tabela-jornada__hora[data-atencao='true'] { color: var(--cor-alerta-500); }
```

**Resumo da viagem:** `API → JSON cru → classe esperta (entity) →
componente que pergunta pra classe → CSS que decide a cor`. Nenhum
componente decide sozinho se algo está atrasado — ele sempre pergunta
pra classe. Isso significa que se um dia a regra de "o que é atraso"
mudar, você muda **um método, em um arquivo**, e toda a tela se
atualiza sozinha, em todo lugar que usa aquele método.

---

## 6. O Design System: como as cores e tamanhos funcionam

Abra `src/styles/tokens.css`. Você vai ver um monte de linhas assim:

```css
:root {
  --cor-ambar-500: #ecb11f;
  --cor-critico-500: #ef4444;
  --espaco-4: 16px;
}
```

Isso se chama **CSS Custom Properties** (ou "variáveis CSS" ou
"tokens"). Funciona basicamente como uma variável de programação, só que
dentro do CSS: você define o valor **uma vez**, com um nome, e usa esse
nome em vários lugares:

```css
.badge--critico { color: var(--cor-critico-500); }
.tabela-jornada__excedeu { color: var(--cor-alerta-500); }
```

**Por que isso é bom:** se um dia o pessoal do design decidir que o
vermelho institucional mudou de tom, você troca **uma linha** no
`tokens.css` (`--cor-critico-500: #ef4444;` → outro valor) e **toda a
tela inteira** já usa a cor nova, automaticamente, sem precisar caçar
"vermelho" em 40 arquivos CSS diferentes.

### Os "dois tamanhos" do sistema

Tem uma pegadinha interessante no `tokens.css`:

```css
[data-contexto='totem'] { --texto-md: 1.0625rem; }
[data-contexto='painel'] { --texto-md: 1.125rem; --texto-2xl: 3.25rem; }
```

Isso quer dizer: **as mesmas variáveis mudam de valor dependendo de um
atributo `data-contexto` no HTML**. A tela de Pátio marca
`outlet.dataset.contexto = 'totem'` (letras um pouco maiores, alvo de
toque maior, porque alguém vai tocar com o dedo). A tela de CCP marca
`'painel'` (letras bem maiores ainda, porque é lido de longe, numa TV).
É o mesmo sistema de cor e espaçamento, só que "escalado" diferente
conforme o contexto de uso — sem duplicar nenhum CSS.

---

## 7. As telas, uma por uma

### 7.1 Menu (`#/menu`) — a "portaria"

Arquivo principal: `src/modules/menu/menu.component.js`

É a tela mais simples: ela busca (via `MenuService` →
`ApiService.buscarEstrutura()`) uma lista de supervisões, e para cada
uma desenha:
- um botão por guarita (`local`), que é um link para
  `#/apresentacao?supervisao=X&local=Y`;
- um botão único "Abrir CCP", que é um link para `#/ccp?supervisao=X`.

Ela **não faz polling** (não fica recarregando sozinha) porque a lista
de supervisões/guaritas não muda a cada segundo como o painel de
empregados muda.

### 7.2 Pátio (`#/apresentacao?supervisao=...&local=...`) — o totem

Arquivo principal: `src/modules/apresentacao/apresentacao.component.js`

O que essa função faz, em ordem:
1. Lê `supervisao` e `local` da URL (ou usa um valor padrão de exemplo).
2. Busca o painel na API a cada 15 segundos (`setInterval`).
3. Se a API disser que está em manutenção, mostra um modal de
   manutenção e para por aí.
4. Senão, desenha: o cabeçalho (`AppHeader`), a barra com nome da
   guarita + relógio ao vivo, o formulário de matrícula, e por fim a
   tabela inteira (`TabelaJornada`, variante `'totem'`).

### 7.3 CCP (`#/ccp?supervisao=...`) — a supervisão

Arquivo principal: `src/modules/ccp/ccp.component.js`

Muito parecido com o Pátio, só que:
- não tem campo de matrícula (quem usa é o supervisor, não o empregado);
- a tabela usa a variante `'ccp'`, que tem colunas a mais (Cargo, Local,
  Chamada) e troca os rótulos dos botões ("Liberar Refeição" em vez de
  "Start Refeição");
- não filtra por `local` — pede o painel **da supervisão inteira**.

### 7.4 Histórico e Indicadores

Essas duas ainda são telas mais simples/esqueleto — `historico` é
onde entraria uma consulta de jornadas passadas (filtro por
matrícula/data), e `indicadores` já tem um painel funcional com 3
gráficos de exemplo usando **Chart.js** (`src/modules/indicadores/`).

---

## 8. O coração visual: a Tabela de Jornada

Este é, de longe, o componente mais importante do projeto, então merece
uma seção só pra ele.

Arquivo: `src/shared/components/tabela-jornada.component.js`

A ideia central: **a mesma função de tabela é usada tanto no Pátio
quanto no CCP** — só muda um parâmetro chamado `variante` (`'totem'`
ou `'ccp'`), que decide quais colunas aparecem e que texto os botões
mostram.

```js
export function renderTabelaJornada(empregados, { variante, timeService, acoes }) {
  // ...monta cabeçalho de acordo com a variante...
  // ...monta uma linha por empregado...
}
```

Cada **linha** da tabela é montada célula por célula, e cada célula
"pergunta" pro objeto de domínio (a entity) em que estado ela está,
igual explicamos na seção 5. As células mais complexas (Lanche e
Refeição) têm **seu próprio arquivo dedicado**, porque elas têm vários
estados visuais possíveis:

Arquivo: `src/shared/components/celula-intervalo.component.js`

| Estado (do enum `STATUS_INTERVALO`) | O que aparece na tela |
|---|---|
| `AGUARDANDO_JANELA` / `AGUARDANDO_ESCOLHA` | Texto da janela de horário (ex: "02:00 às 04:30") |
| `ATRASADO` | Um `<select>` "Justificativa..." + botão vermelho de enviar |
| `LIBERADO_PARA_ACAO` | Um botão verde ("Start Lanche" / "Liberar Lanche") |
| `EM_ANDAMENTO` | "início→agora" (dois horários com uma seta no meio) |
| `CONCLUIDO` | O horário final, em cinza |

Isso é o que se chama de **máquina de estados**: um valor (o `status`)
decide qual "modo" a célula está, e cada modo tem uma aparência e um
comportamento diferentes. Se um dia precisar de um estado novo (o
README já aponta um: o "15:21→ACIONAR" do CCP que ainda não existe),
é aqui — `celula-intervalo.component.js` — que você adiciona um novo
`case` no `switch`.

---

## 9. Onde mexer quando você precisar mudar alguma coisa (guia prático)

Essa é a parte "me dá o mapa do tesouro". Cada linha abaixo é: **"eu
quero fazer X" → "abra o arquivo Y, procure por Z"**.

### 9.1 "Quero mudar a URL da API (endpoint)"
→ `src/shared/services/api.service.js`, a linha
`constructor({ baseUrl = '/api', ... })`. Ou, mais fácil ainda, sem
mexer em código: `index.html`, no `<script>` que define
`window.APP_CONFIG = { apiBaseUrl: '/api' }` — troque o valor ali.

### 9.2 "Quero adicionar um novo campo que vem da API (ex: `local.endereco`)"
1. Adicione o campo no JSON que a API devolve.
2. Se for um campo de uma etapa (apresentação, prontidão, etc), adicione
   um método em `src/shared/entities/etapa-jornada.entity.js` pra ler
   ele (ex: `enderecoDaGuarita() { return this.dados.endereco; }`).
3. Use esse método onde precisar aparecer — normalmente em
   `src/shared/components/tabela-jornada.component.js`.

### 9.3 "Quero adicionar uma ação nova (um botão que chama a API)"
1. Adicione o método em `src/shared/services/api.service.js` (o de
   verdade) **e** em
   `src/shared/services/api.service.mock-example.js` (o de mentira, pra
   não quebrar o `demo.html`).
2. Se o botão mora na tabela, adicione a função de renderizar o botão
   em `tabela-jornada.component.js` (procure `celFimJornada` como
   exemplo de uma célula com botão simples).
3. Ligue o clique do botão a uma função dentro do objeto `acoes` que é
   passado lá de `apresentacao.component.js` ou `ccp.component.js`.

### 9.4 "Quero mudar uma cor (ex: o âmbar do título)"
→ `src/styles/tokens.css`, procure `--cor-ambar-500`. É só isso — o
resto do sistema usa essa variável, então muda em todo lugar sozinho.

### 9.5 "Quero mudar o tamanho de letra no modo totem/painel"
→ `src/styles/tokens.css`, procure `[data-contexto='totem']` e
`[data-contexto='painel']` (perto do fim do arquivo).

### 9.6 "Quero adicionar uma supervisão/guarita nova no Menu"
→ Não precisa mexer em código nenhum — isso vem do endpoint
`GET /api/estrutura` (documentado no `README.md`, seção 4.2). Só
precisa que a API devolva a supervisão nova na lista. Se quiser testar
antes de o back-end estar pronto, edite o retorno de
`buscarEstrutura()` em
`src/shared/services/api.service.mock-example.js`.

### 9.7 "Quero adicionar um novo estado visual pra uma célula (Lanche/Refeição)"
1. Adicione o novo valor no enum `STATUS_INTERVALO`, em
   `src/shared/utils/enums.js`.
2. Adicione um `case` novo no `switch` de
   `src/shared/components/celula-intervalo.component.js`.

### 9.8 "Quero mudar o texto de um botão"
Procure o texto literal (ex: `"Start Refeição"`) dentro de
`tabela-jornada.component.js` — os rótulos ficam ali, passados como
`rotuloAcao` dependendo da `variante`.

### 9.9 "Quero mudar de quanto em quanto tempo a tela atualiza sozinha"
→ Procure `setInterval(carregar, 15_000)` em
`apresentacao.component.js` ou `ccp.component.js` — o número é em
milissegundos (15\_000 = 15 segundos).

---

## 10. Como testar sem precisar do back-end (o "modo mentira")

Tem dois arquivos "porta de entrada":

- **`index.html`** → usa `src/core/app.js` → usa o `ApiService` de
  verdade, que faz `fetch()` pra API real (`/api/...`).
- **`demo.html`** → usa `src/core/demo-bootstrap.js` → usa o
  `MockApiService` (arquivo
  `src/shared/services/api.service.mock-example.js`), que **nunca
  toca a rede** — ele só devolve um JSON fixo, escrito à mão, que já
  vem cobrindo os principais estados visuais (atrasado, em andamento,
  etc).

Isso é extremamente útil pra você mexer no visual **sem precisar que o
back-end em C# já esteja pronto**. Se quiser testar um cenário novo
(por exemplo, um empregado com prontidão excedida), edite os dados
dentro da função `gerarPainelExemplo()` nesse mesmo arquivo mock.

Para abrir: é só dar duplo clique no `demo.html` (ou servir a pasta com
qualquer servidor estático) — não precisa instalar nada.

---

## 11. Glossário — palavras estranhas explicadas

| Termo | O que significa, sem enrolação |
|---|---|
| **ES Module** | Um jeito de escrever JS onde um arquivo pode "importar" pedaços de outro arquivo, sem precisar de ferramenta nenhuma — só o navegador |
| **Injeção de Dependência (DI)** | Em vez de cada pedaço de código criar suas próprias ferramentas, existe um lugar central (o `container`) que cria uma vez e empresta |
| **Singleton** | Um objeto que só existe **uma vez** na aplicação inteira, não importa quantas vezes você "peça" ele |
| **Enum** | Uma lista fixa de valores possíveis (ex: `STATUS_PRONTIDAO` só pode ser `AGUARDANDO`, `PRONTO` ou `PRONTO_COM_ATRASO`, nunca outra coisa) — evita erro de digitação tipo `"pronto"` vs `"Pronto"` |
| **Entity / Entidade** | Uma classe que representa "uma coisa do mundo real" do sistema (um Empregado, uma Etapa da Jornada) com métodos que sabem responder perguntas sobre ela |
| **Contrato de API / JSON contract** | O "formato combinado" de como os dados vão e voltam entre o front e o back — documentado no `README.md` |
| **CSS Custom Property (token)** | Uma variável dentro do CSS, escrita como `--nome: valor;` e usada como `var(--nome)` |
| **Query string** | A parte de uma URL depois do `?`, tipo `?supervisao=VPN&local=Guarita_2` — pares de chave e valor |
| **Hash routing** | Guardar a "página atual" na parte da URL depois do `#`, porque essa parte nunca é enviada pro servidor (evita erro 404 ao dar F5) |
| **Mock** | Uma versão "de mentira" de alguma coisa, só para testar sem depender do real (aqui, o `MockApiService`) |
| **Polling** | Ficar perguntando "tem novidade?" de tempos em tempos (aqui, a cada 15 segundos), em vez de esperar um aviso |

---

## 12. O que ainda falta / pontas soltas

Isso já está no `README.md`, mas vale repetir aqui num resumo rápido,
porque são as coisas mais prováveis de você (ou eu) esquecer:

- A coluna Lanche do CCP tem um estado ("15:21→ACIONAR") que ainda não
  foi modelado — hoje cai em cima de outro estado parecido.
- O botão "Bom Descanso" está sempre clicável — falta decidir a regra
  de quando ele deveria travar.
- As duas bolinhas verdes do nome do empregado hoje representam
  "apresentação confirmada" e "prontidão confirmada" — se o significado
  real for outro, é só mudar em `tabela-jornada.component.js`, função
  `celNome()`.
- O endpoint `GET /api/estrutura` (que alimenta o Menu) ainda não existe
  no back-end de verdade — só no mock.
- O back-end vai ser C# .NET — o front não precisa mudar nada por
  causa disso, **desde que o formato do JSON continue o mesmo** (ver
  `README.md`, seção 6).
