import { renderStatusBadge } from './status-badge.component.js';
import { COR_POR_STATUS, ETAPAS_JORNADA } from '../utils/enums.js';
import { formatarHora } from '../utils/formatters.js';

const RÓTULO_ETAPA = {
  apresentacao: 'Apres.',
  prontidao: 'Pront.',
  lanche: 'Lanche',
  refeicao: 'Refeição',
  fimJornada: 'Fim',
};

/**
 * StepperJornada — linha de acompanhamento de um empregado, com as 5
 * etapas em formato de stepper horizontal. Substitui a tabela "achatada"
 * atual por um componente que já expressa hierarquia e progresso.
 *
 * @param {import('../entities/empregado.entity.js').Empregado} empregado
 */
export function renderStepperJornada(empregado) {
  const linha = document.createElement('article');
  linha.className = 'stepper-jornada superficie-glass';
  linha.dataset.atrasado = String(empregado.estaAtrasado());

  const cabecalho = document.createElement('div');
  cabecalho.className = 'stepper-jornada__cabecalho';
  cabecalho.innerHTML = `
    <div class="stepper-jornada__pessoa">
      <span class="stepper-jornada__nome">${empregado.nome}</span>
      <span class="texto-terciario fonte-mono">${empregado.matricula} · ${empregado.cargo}</span>
    </div>
    <span class="texto-secundario">${empregado.turno}</span>
  `;

  const trilha = document.createElement('div');
  trilha.className = 'stepper-jornada__trilha';

  ETAPAS_JORNADA.forEach((chave, indice) => {
    const etapa = empregado.etapas[chave];
    const passo = document.createElement('div');
    passo.className = 'stepper-jornada__passo';
    passo.dataset.concluida = String(etapa.estaConcluida());
    passo.dataset.atrasada = String(etapa.estaAtrasado());

    const rotulo = document.createElement('span');
    rotulo.className = 'stepper-jornada__rotulo';
    rotulo.textContent = RÓTULO_ETAPA[chave];

    const marcador = document.createElement('span');
    marcador.className = 'stepper-jornada__marcador';

    const badge = renderStatusBadge({
      status: etapa.statusLabel(),
      cor: COR_POR_STATUS[etapa.statusLabel()] ?? 'neutro',
    });

    const horario = document.createElement('span');
    horario.className = 'stepper-jornada__horario fonte-mono texto-terciario';
    horario.textContent = formatarHora(etapa.dados.dataHora ?? etapa.dados.dataHoraInicio);

    passo.append(rotulo, marcador, badge, horario);
    trilha.appendChild(passo);

    if (indice < ETAPAS_JORNADA.length - 1) {
      const conector = document.createElement('span');
      conector.className = 'stepper-jornada__conector';
      conector.dataset.ativo = String(etapa.estaConcluida());
      trilha.appendChild(conector);
    }
  });

  linha.append(cabecalho, trilha);
  return linha;
}
