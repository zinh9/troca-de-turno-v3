import { container } from '../../core/di.js';
import { ApresentacaoService } from './apresentacao.service.js';
import { montarTelaApresentacao } from './apresentacao.component.js';

/**
 * apresentacao.module.js — ponto único de entrada do módulo "Apresentação".
 * Registra o service específico do módulo e a rota. Todo módulo segue
 * exatamente este padrão de 3 métodos: registrarServico + registrarRota.
 */
export function registrarRota(router, di = container) {
  if (!di.has('apresentacaoService')) {
    di.registerSingleton('apresentacaoService', (c) => new ApresentacaoService(c.resolve('apiService')));
  }

  router.registrar('/apresentacao', (outlet, params) => montarTelaApresentacao(outlet, di, params));
}
