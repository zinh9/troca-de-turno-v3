import { container } from '../../core/di.js';
import { HistoricoService } from './historico.service.js';
import { montarTelaHistorico } from './historico.component.js';

export function registrarRota(router, di = container) {
  if (!di.has('historicoService')) {
    di.registerSingleton('historicoService', (c) => new HistoricoService(c.resolve('apiService')));
  }
  router.registrar('/historico', (outlet) => montarTelaHistorico(outlet, di));
}
