import { container } from '../../core/di.js';
import { RefeicaoService } from './refeicao.service.js';
import { montarTelaRefeicao } from './refeicao.component.js';

/**
 * refeicao.module.js — segue o mesmo padrão de apresentacao.module.js:
 * registra o service do módulo e a rota "/refeicao".
 */
export function registrarRota(router, di = container) {
  if (!di.has('refeicaoService')) {
    di.registerSingleton('refeicaoService', (c) => new RefeicaoService(c.resolve('apiService')));
  }
  router.registrar('/refeicao', (outlet) => montarTelaRefeicao(outlet, di));
}
