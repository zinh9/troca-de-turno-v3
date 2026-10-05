import { ICONES, dotStatus } from '../utils/icons.js';
import { renderCelulaIntervalo } from './celula-intervalo.component.js';
import { formatarHora } from '../utils/formatters.js';

/**
 * tabela-jornada.component.js — tabela densa de acompanhamento de
 * equipe, réplica fiel do layout em produção (ver prints anexados no
 * chat): uma linha por empregado, com a jornada inteira (apresentação →
 * prontidão → lanche/refeição → fim de jornada) em colunas.
 *
 * Duas variantes, mesmo componente:
 *   - 'totem'  → tela de pátio (menos colunas, botões de ação primária)
 *   - 'ccp'    → tela de supervisão (colunas extras Cargo/Local/Chamada,
 *                ações de liberar em lote em vez de o próprio empregado agir)
 *
 * Este componente só faz *composição visual* das etapas — a regra de
 * quando cada célula aparece em cada estado vive nas entidades
 * (Apresentacao/Prontidao/Intervalo em shared/entities) e no renderer
 * de célula compartilhado (celula-intervalo.component.js). Se no futuro
 * cada etapa precisar de uma tela própria isolada, essas mesmas funções
 * de célula podem ser movidas para dentro do respectivo módulo
 * (modules/prontidao, modules/lanche, ...) sem mudar a árvore de dados.
 *
 * @param {import('../entities/empregado.entity.js').Empregado[]} empregados
 * @param {{
 *   variante: 'totem'|'ccp',
 *   timeService: import('../services/time.service.js').TimeService,
 *   acoes: {
 *     aoEnviarJustificativaProntidao: (matricula: string, motivo: string) => void,
 *     aoAcionarLanche: (matricula: string) => void,
 *     aoEnviarJustificativaLanche: (matricula: string, motivo: string) => void,
 *     aoAcionarRefeicao: (matricula: string) => void,
 *     aoAcionarRadio: (matricula: string) => void,
 *     aoFinalizarJornada: (matricula: string) => void,
 *   }
 * }} opcoes
 */
export function renderTabelaJornada(empregados, opcoes) {
  const { variante, timeService, acoes = {} } = opcoes;
  const raiz = document.createElement('div');
  raiz.className = `tabela-jornada tabela-jornada--${variante} superficie-glass`;

  raiz.appendChild(renderCabecalho(variante));

  const corpo = document.createElement('div');
  corpo.className = 'tabela-jornada__corpo';
  empregados.forEach((empregado) => corpo.appendChild(renderLinha(empregado, { variante, timeService, acoes })));
  raiz.appendChild(corpo);

  return raiz;
}

function renderCabecalho(variante) {
  const linha = document.createElement('div');
  linha.className = 'tabela-jornada__linha tabela-jornada__linha--cabecalho';

  const colunas = variante === 'ccp'
    ? [
      { icone: ICONES.pessoa, texto: '| TAC' },
      { texto: 'Cargo' },
      { icone: `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s7-6.5 7-12a7 7 0 1 0-14 0c0 5.5 7 12 7 12Z"/><circle cx="12" cy="9" r="2.4"/></svg>`, texto: 'Local' },
      { icone: ICONES.relogio, texto: 'Apresentação' },
      { icone: ICONES.relogio, texto: 'Prontidão' },
      { texto: 'Chamada' },
      { icone: ICONES.sanduiche, texto: 'Lanche' },
      { icone: ICONES.garfoFaca, texto: 'Refeição' },
      { texto: 'Fim de Jornada' },
    ]
    : [
      { icone: ICONES.pessoa, texto: '| TAC' },
      { icone: ICONES.relogio, texto: 'Apres.' },
      { icone: ICONES.relogio, texto: 'Pront.' },
      { icone: ICONES.sanduiche, texto: 'Lanche' },
      { icone: ICONES.garfoFaca, texto: 'Refeição' },
      { icone: ICONES.relogio, texto: 'Fim Jornada', sub: '(18:10:00)' },
    ];

  colunas.forEach((col) => {
    const el = document.createElement('span');
    el.className = 'tabela-jornada__cabecalho-item';
    el.innerHTML = `${col.icone ?? ''}<span>${col.texto}</span>${col.sub ? `<small>${col.sub}</small>` : ''}`;
    linha.appendChild(el);
  });

  return linha;
}

function renderLinha(empregado, { variante, timeService, acoes }) {
  const linha = document.createElement('div');
  linha.className = 'tabela-jornada__linha';
  linha.dataset.atrasado = String(empregado.estaAtrasado());

  const { apresentacao, prontidao, lanche, refeicao, fimJornada } = empregado.etapas;

  linha.appendChild(celNome(empregado, apresentacao, prontidao));
  if (variante === 'ccp') {
    linha.appendChild(celTexto(empregado.cargo));
    linha.appendChild(celTexto(empregado.local?.replace('_', ' ') ?? '—'));
  }
  linha.appendChild(celApresentacao(apresentacao));
  linha.appendChild(celProntidao(prontidao, variante, acoes, empregado.matricula));
  if (variante === 'ccp') {
    linha.appendChild(celChamada(acoes, empregado.matricula));
  }
  linha.appendChild(celIntervalo(lanche, {
    timeService, rotuloAcao: variante === 'ccp' ? 'Liberar Lanche' : 'Start Lanche',
    mostrarContagem: variante === 'ccp', duracaoMin: 15,
    aoAcionar: () => acoes.aoAcionarLanche?.(empregado.matricula),
    aoEnviar: (motivo) => acoes.aoEnviarJustificativaLanche?.(empregado.matricula, motivo),
  }));
  linha.appendChild(celIntervalo(refeicao, {
    timeService, rotuloAcao: variante === 'ccp' ? 'Liberar Refeição' : 'Start Refeição',
    mostrarContagem: false, duracaoMin: 60,
    aoAcionar: () => acoes.aoAcionarRefeicao?.(empregado.matricula),
    aoEnviar: (motivo) => acoes.aoEnviarJustificativaLanche?.(empregado.matricula, motivo),
  }));
  linha.appendChild(celFimJornada(fimJornada, acoes, empregado.matricula));

  return linha;
}

function celNome(empregado, apresentacao, prontidao) {
  const el = document.createElement('div');
  el.className = 'tabela-jornada__nome';
  el.innerHTML = `
    <button class="tabela-jornada__reenviar" title="Reenviar/registrar novamente">${ICONES.reenviar}</button>
    <span class="tabela-jornada__nome-texto">${empregado.nome}</span>
    ${dotStatus(apresentacao.estaConcluida())}
    ${dotStatus(prontidao.estaConcluida())}
  `;
  return el;
}

function celTexto(texto) {
  const el = document.createElement('span');
  el.className = 'tabela-jornada__texto';
  el.textContent = texto;
  return el;
}

function celApresentacao(apresentacao) {
  const el = document.createElement('span');
  el.className = 'tabela-jornada__hora fonte-mono';
  el.dataset.atencao = String(apresentacao.estaAtrasado());
  el.textContent = formatarHora(apresentacao.dados.dataHora);
  return el;
}

function celProntidao(prontidao, variante, acoes, matricula) {
  const el = document.createElement('div');

  if (variante === 'ccp' && prontidao.estaExcedido()) {
    el.className = 'tabela-jornada__excedeu';
    el.textContent = 'EXCEDEU';
    return el;
  }

  if (prontidao.estaConcluida()) {
    el.className = 'tabela-jornada__hora fonte-mono';
    el.dataset.atencao = String(prontidao.estaAtrasado());
    el.textContent = formatarHora(prontidao.dados.dataHora);
    return el;
  }

  if (variante === 'totem') {
    // Aguardando confirmação e dentro do fluxo que exige justificativa do atraso.
    el.className = 'celula-intervalo';
    el.innerHTML = '';
    const wrap = document.createElement('div');
    wrap.className = 'celula-intervalo__justificativa';
    wrap.innerHTML = `
      <select aria-label="Motivo do atraso na prontidão">
        <option value="">Justificativa...</option>
        <option value="AGUARDANDO_LIBERACAO">Aguardando liberação</option>
        <option value="ATIVIDADE_OPERACIONAL">Atividade operacional</option>
      </select>
      <button type="button" class="celula-intervalo__enviar" title="Enviar justificativa">${ICONES.enviar}</button>
    `;
    wrap.querySelector('button').addEventListener('click', () => {
      const valor = wrap.querySelector('select').value;
      if (valor) acoes.aoEnviarJustificativaProntidao?.(matricula, valor);
    });
    el.appendChild(wrap);
    return el;
  }

  el.className = 'tabela-jornada__hora tabela-jornada__hora--muda fonte-mono';
  el.textContent = '--:--';
  return el;
}

function celChamada(acoes, matricula) {
  const botao = document.createElement('button');
  botao.className = 'tabela-jornada__chamada';
  botao.innerHTML = `${ICONES.radio}<span>Acionar via Rádio</span>`;
  botao.addEventListener('click', () => acoes.aoAcionarRadio?.(matricula));
  return botao;
}

function celIntervalo(etapa, { timeService, rotuloAcao, mostrarContagem, duracaoMin, aoAcionar, aoEnviar }) {
  return renderCelulaIntervalo(etapa, {
    rotuloAcao,
    timeService,
    mostrarContagem,
    duracaoMin,
    aoAcionar,
    aoEnviarJustificativa: aoEnviar,
  });
}

function celFimJornada(fimJornada, acoes, matricula) {
  const botao = document.createElement('button');
  botao.className = 'tabela-jornada__bom-descanso';
  botao.textContent = 'Bom Descanso';
  botao.disabled = !fimJornada.estaConcluida() && false; // habilitado sempre por ora — ver README (regra de negócio a definir)
  botao.addEventListener('click', () => acoes.aoFinalizarJornada?.(matricula));
  return botao;
}
