import { renderAppHeader } from '../../shared/components/app-header.component.js';
import { ICONES } from '../../shared/utils/icons.js';

/**
 * menu.component.js — tela de Menu, ponto de partida do sistema.
 *
 * Dois blocos de link por supervisão:
 *  - "Totens de pátio": um link por local/guarita (`#/apresentacao?...`),
 *    para o empregado abrir o totem certo — ou para o time de TI copiar
 *    o link e fixá-lo no totem físico daquela guarita.
 *  - "CCP": um único link por supervisão (`#/ccp?...`), já que o CPT
 *    supervisiona todas as guaritas daquela supervisão numa tela só —
 *    não faz sentido um link por guarita para o CCP.
 *
 * Esta tela não faz polling — a estrutura de supervisões/locais muda
 * raramente, então busca uma vez ao montar.
 */
export function montarTelaMenu(outlet, di) {
  const menuService = di.resolve('menuService');
  const timeService = di.resolve('timeService');

  outlet.dataset.contexto = 'totem';
  outlet.innerHTML = '';

  const tela = document.createElement('div');
  tela.className = 'tela-menu';
  outlet.appendChild(tela);

  tela.appendChild(renderAppHeader({
    variante: 'patio',
    ultimaAtualizacaoIso: new Date().toISOString(),
    timeService,
  }));

  const corpo = document.createElement('div');
  corpo.className = 'tela-menu__corpo';
  corpo.innerHTML = `<p class="texto-secundario">Carregando supervisões...</p>`;
  tela.appendChild(corpo);

  menuService.buscarEstrutura()
    .then((supervisoes) => renderLista(corpo, supervisoes))
    .catch(() => {
      corpo.innerHTML = `<p class="texto-secundario">Não foi possível carregar a lista de supervisões.</p>`;
    });
}

function renderLista(corpo, supervisoes) {
  corpo.innerHTML = '';

  supervisoes.forEach((supervisao) => {
    const bloco = document.createElement('section');
    bloco.className = 'tela-menu__supervisao superficie-glass';

    const cabecalho = document.createElement('div');
    cabecalho.className = 'tela-menu__supervisao-cabecalho';
    cabecalho.innerHTML = `
      <h2>${supervisao.label ?? supervisao.nome}</h2>
      <a class="tela-menu__link-ccp" href="#/ccp?supervisao=${encodeURIComponent(supervisao.nome)}">
        ${ICONES.trem}<span>Abrir CCP</span>
      </a>
    `;

    const listaLocais = document.createElement('div');
    listaLocais.className = 'tela-menu__locais';
    (supervisao.locais ?? []).forEach((local) => {
      const link = document.createElement('a');
      link.className = 'tela-menu__link-local';
      link.href = `#/apresentacao?supervisao=${encodeURIComponent(supervisao.nome)}&local=${encodeURIComponent(local)}`;
      link.innerHTML = `${ICONES.torre}<span>${local.replace('_', ' ')}</span>`;
      listaLocais.appendChild(link);
    });

    bloco.append(cabecalho, listaLocais);
    corpo.appendChild(bloco);
  });
}
