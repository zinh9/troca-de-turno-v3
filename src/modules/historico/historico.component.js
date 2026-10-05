/**
 * historico.component.js — ESQUELETO da tela "Histórico".
 * 
 * Pensada para consulta de jornadas passadas, com filtro por matrícula/data/supervisão — provavelmente com uma tabela densa (ver design system) em vez do stepper.
 */
export function montarTelaHistorico(outlet, di) {
  outlet.innerHTML = `
    <div class="tela-historico superficie-glass" style="margin:var(--espaco-6);padding:var(--espaco-8);text-align:center;">
      <h2>Histórico</h2>
      <p class="texto-secundario">Esqueleto do módulo.</p>
    </div>
  `;
}
