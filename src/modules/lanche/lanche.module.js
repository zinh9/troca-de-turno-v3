import { container } from '../../core/di.js';
import { LancheService } from './lanche.service.js';
import { montarTelaLanche } from './lanche.component.js';

/**
 * lanche.module.js — segue o mesmo padrão de apresentacao.module.js:
 * registra o service do módulo e a rota "/lanche".
 */
export function registrarRota(router, di = container) {
  if (!di.has('lancheService')) {
    di.registerSingleton('lancheService', (c) => new LancheService(c.resolve('apiService')));
  }
  router.registrar('/lanche', (outlet) => montarTelaLanche(outlet, di));
}
