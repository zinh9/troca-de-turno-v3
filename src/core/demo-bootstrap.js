/**
 * demo-bootstrap.js — idêntico a app.js, mas registra o MockApiService
 * no lugar do ApiService real. Usado só por demo.html para visualizar
 * o design system e os componentes sem precisar de back-end no ar.
 * NÃO referenciar este arquivo a partir de index.html/produção.
 */
import { container } from './di.js';
import { router } from './router.js';
import { MockApiService } from '../shared/services/api.service.mock-example.js';
import { TimeService } from '../shared/services/time.service.js';
import { SmsService } from '../shared/services/sms.service.js';

import { registrarRota as rotaMenu } from '../modules/menu/menu.module.js';
import { registrarRota as rotaApresentacao } from '../modules/apresentacao/apresentacao.module.js';
import { registrarRota as rotaCcp } from '../modules/ccp/ccp.module.js';
import { registrarRota as rotaHistorico } from '../modules/historico/historico.module.js';
import { registrarRota as rotaIndicadores } from '../modules/indicadores/indicadores.module.js';

function iniciar() {
  container.registerSingleton('apiService', () => new MockApiService());
  container.registerSingleton('timeService', (c) => new TimeService(c.resolve('apiService')));
  container.registerSingleton('smsService', () => new SmsService());

  rotaMenu(router, container);
  rotaApresentacao(router, container);
  rotaCcp(router, container);
  rotaHistorico(router, container);
  rotaIndicadores(router, container);

  router.registrar('/nao-encontrado', (outlet) => {
    outlet.innerHTML = `<p class="texto-secundario" style="padding:var(--espaco-8)">Tela não encontrada.</p>`;
  });

  router.init('#app-outlet');
  container.resolve('timeService').iniciarSincronizacao();
}

document.addEventListener('DOMContentLoaded', iniciar);
