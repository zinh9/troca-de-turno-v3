import { container } from '../../core/di.js';
import { ProntidaoService } from './prontidao.service.js';
import { montarTelaProntidao } from './prontidao.component.js';

/**
 * prontidao.module.js — segue o mesmo padrão de apresentacao.module.js:
 * registra o service do módulo e a rota "/prontidao".
 */
export function registrarRota(router, di = container) {
  if (!di.has('prontidaoService')) {
    di.registerSingleton('prontidaoService', (c) => new ProntidaoService(c.resolve('apiService')));
  }
  router.registrar('/prontidao', (outlet) => montarTelaProntidao(outlet, di));
}
