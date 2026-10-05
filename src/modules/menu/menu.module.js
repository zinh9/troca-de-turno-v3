import { container } from '../../core/di.js';
import { MenuService } from './menu.service.js';
import { montarTelaMenu } from './menu.component.js';

/**
 * menu.module.js — tela de entrada do sistema. Lista as supervisões e
 * seus locais (guaritas) para o empregado escolher o totem certo, e o
 * link único do CCP por supervisão. Ver README, seção 3.3.
 */
export function registrarRota(router, di = container) {
  if (!di.has('menuService')) {
    di.registerSingleton('menuService', (c) => new MenuService(c.resolve('apiService')));
  }
  router.registrar('/menu', (outlet) => montarTelaMenu(outlet, di));
}
