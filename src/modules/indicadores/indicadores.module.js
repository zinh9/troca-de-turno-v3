import { container } from '../../core/di.js';
import { IndicadoresService } from './indicadores.service.js';
import { montarTelaIndicadores } from './indicadores.component.js';

export function registrarRota(router, di = container) {
  if (!di.has('indicadoresService')) {
    di.registerSingleton('indicadoresService', (c) => new IndicadoresService(c.resolve('apiService')));
  }
  router.registrar('/indicadores', (outlet) => montarTelaIndicadores(outlet, di));
}
