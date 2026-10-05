import { container } from '../../core/di.js';
import { CcpService } from './ccp.service.js';
import { montarTelaCcp } from './ccp.component.js';

export function registrarRota(router, di = container) {
  if (!di.has('ccpService')) {
    di.registerSingleton('ccpService', (c) => new CcpService(c.resolve('apiService')));
  }
  router.registrar('/ccp', (outlet, params) => montarTelaCcp(outlet, di, params));
}
