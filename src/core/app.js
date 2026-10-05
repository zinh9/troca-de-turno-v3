/**
 * app.js — ponto de entrada da SPA. Carregado como
 *   <script type="module" src="/src/core/app.js"></script>
 *
 * Responsabilidades:
 *  1. Registrar services globais (compartilhados entre módulos) no DI.
 *  2. Registrar rotas — cada módulo expõe um `registrarRota(router, container)`.
 *  3. Iniciar o router.
 *
 * NÃO coloque regra de negócio aqui. Este arquivo só faz "fiação".
 *
 * Só existem rotas de TELA para Menu, Pátio, CCP, Histórico e
 * Indicadores — não há mais uma rota por etapa da jornada (prontidão,
 * lanche, refeição, fim de jornada). Essas etapas continuam existindo
 * como módulos de domínio (service/entity), só não são mais páginas
 * navegáveis: a jornada inteira aparece numa única tabela nas telas de
 * Pátio/CCP. Ver README, seção 2.
 */
import { container } from './di.js';
import { router } from './router.js';
import { ApiService } from '../shared/services/api.service.js';
import { TimeService } from '../shared/services/time.service.js';
import { SmsService } from '../shared/services/sms.service.js';

// --- módulos com tela própria (cada um exporta registrarRota) ---
import { registrarRota as rotaMenu } from '../modules/menu/menu.module.js';
import { registrarRota as rotaApresentacao } from '../modules/apresentacao/apresentacao.module.js';
import { registrarRota as rotaCcp } from '../modules/ccp/ccp.module.js';
import { registrarRota as rotaHistorico } from '../modules/historico/historico.module.js';
import { registrarRota as rotaIndicadores } from '../modules/indicadores/indicadores.module.js';

function registrarServicosGlobais() {
  container.registerSingleton('apiService', () => new ApiService({
    baseUrl: window.APP_CONFIG?.apiBaseUrl ?? '/api',
  }));
  container.registerSingleton('timeService', (c) => new TimeService(c.resolve('apiService')));
  container.registerSingleton('smsService', () => new SmsService());
}

function registrarRotas() {
  rotaMenu(router, container);
  rotaApresentacao(router, container);
  rotaCcp(router, container);
  rotaHistorico(router, container);
  rotaIndicadores(router, container);

  router.registrar('/nao-encontrado', (outlet) => {
    outlet.innerHTML = `<p class="texto-secundario" style="padding:var(--espaco-8)">Tela não encontrada.</p>`;
  });
}

function iniciar() {
  registrarServicosGlobais();
  registrarRotas();
  router.init('#app-outlet');

  // Relógio ao vivo sincronizado com serverTime, usado em todos os headers.
  const timeService = container.resolve('timeService');
  timeService.iniciarSincronizacao();
}

document.addEventListener('DOMContentLoaded', iniciar);
