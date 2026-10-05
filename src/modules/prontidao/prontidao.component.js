import { abrirModal } from '../../shared/components/modal.component.js';

/**
 * prontidao.component.js — ESQUELETO da tela de "Prontidão".
 *
 * Onde entra a lógica de negócio desta etapa:
 *  1. Buscar o painel via service (mesmo padrão de apresentacao.component.js).
 *  2. Renderizar os empregados com renderStepperJornada (import de
 *     '../../shared/components/stepper-jornada.component.js') OU um
 *     componente mais específico se esta tela precisar de um layout
 *     próprio (ex.: lanche/refeição podem querer destacar o Cronômetro
 *     — ver '../../shared/components/cronometro.component.js').
 *  3. Ligar as ações da etapa (ex.: escolher intervalo, iniciar,
 *     enviar justificativa) aos métodos do service correspondente.
 *  4. Tratar erro de rede com abrirModal({ tipo: 'erro', ... }) e
 *     modo manutenção com abrirModal({ tipo: 'manutencao', ... }),
 *     igual à tela de apresentação.
 */
export function montarTelaProntidao(outlet, di) {
  outlet.innerHTML = `
    <div class="tela-prontidao superficie-glass" style="margin:var(--espaco-6);padding:var(--espaco-8);text-align:center;">
      <h2>Prontidão</h2>
      <p class="texto-secundario">Esqueleto do módulo — implementar seguindo o padrão de apresentacao.component.js.</p>
    </div>
  `;

  return function desmontar() {
    // limpar listeners/timers se este módulo vier a criar algum
  };
}
