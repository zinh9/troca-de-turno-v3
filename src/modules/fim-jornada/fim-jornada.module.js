import { container } from '../../core/di.js';
import { FimJornadaService } from './fim-jornada.service.js';
import { montarTelaFimJornada } from './fim-jornada.component.js';

/**
 * fim-jornada.module.js — segue o mesmo padrão de apresentacao.module.js:
 * registra o service do módulo e a rota "/fim-jornada".
 */
export function registrarRota(router, di = container) {
  if (!di.has('fimJornadaService')) {
    di.registerSingleton('fimJornadaService', (c) => new FimJornadaService(c.resolve('apiService')));
  }
  router.registrar('/fim-jornada', (outlet) => montarTelaFimJornada(outlet, di));
}
