/**
 * indicadores.component.js — painel de indicadores para telão de CCP.
 * Usa Chart.js (importado via CDN no index.html, ver seção "Chart.js"
 * do README) para manter consistência visual com o tema através de
 * `criarOpcoesChart()`, que lê as cores diretamente dos CSS custom
 * properties — assim os gráficos acompanham o tema sem duplicar hex.
 */
function lerVariavelCss(nome) {
  return getComputedStyle(document.documentElement).getPropertyValue(nome).trim();
}

function criarOpcoesChart(overrides = {}) {
  const corTexto = lerVariavelCss('--cor-texto-secundario');
  const corGrade = lerVariavelCss('--cor-borda-sutil');

  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { labels: { color: corTexto, font: { family: 'Inter' } } },
    },
    scales: {
      x: { ticks: { color: corTexto }, grid: { color: corGrade } },
      y: { ticks: { color: corTexto }, grid: { color: corGrade }, beginAtZero: true },
    },
    ...overrides,
  };
}

function renderKpiCard(rotulo, valor, cor) {
  const card = document.createElement('div');
  card.className = 'kpi-card superficie-glass';
  card.innerHTML = `
    <span class="kpi-card__rotulo">${rotulo}</span>
    <span class="kpi-card__valor" style="color:${cor}">${valor}</span>
  `;
  return card;
}

export function montarTelaIndicadores(outlet, di) {
  const indicadoresService = di.resolve('indicadoresService');
  const contexto = { supervisao: 'TORRE_A', local: 'Guarita_7' };

  outlet.dataset.contexto = 'painel';
  outlet.innerHTML = `
    <div class="tela-indicadores">
      <div class="tela-indicadores__kpis" id="kpis"></div>
      <div class="tela-indicadores__graficos">
        <div class="grafico-card superficie-glass">
          <h3>Tempo médio apresentação → prontidão (min)</h3>
          <div class="grafico-card__area"><canvas id="grafico-linha"></canvas></div>
        </div>
        <div class="grafico-card superficie-glass">
          <h3>Empregados por status de etapa</h3>
          <div class="grafico-card__area"><canvas id="grafico-coluna"></canvas></div>
        </div>
        <div class="grafico-card superficie-glass">
          <h3>Conformidade por turno</h3>
          <div class="grafico-card__area"><canvas id="grafico-barra"></canvas></div>
        </div>
      </div>
    </div>
  `;

  let graficos = [];

  async function carregar() {
    const kpis = await indicadoresService.buscarIndicadores(contexto);
    const areaKpis = outlet.querySelector('#kpis');
    areaKpis.innerHTML = '';
    areaKpis.append(
      renderKpiCard('Empregados no pátio', kpis.totalEmpregados, lerVariavelCss('--cor-info-500')),
      renderKpiCard('Jornadas concluídas', kpis.totalConcluidos, lerVariavelCss('--cor-ok-400')),
      renderKpiCard('Em atraso', kpis.totalAtrasados, lerVariavelCss('--cor-critico-400')),
      renderKpiCard('Taxa de conformidade', `${kpis.taxaConformidade}%`, lerVariavelCss('--cor-ambar-400')),
    );

    // Dados de exemplo — substituir por séries reais assim que o endpoint
    // agregado de indicadores existir (ver comentário em indicadores.service.js).
    renderGraficos();
  }

  function renderGraficos() {
    graficos.forEach((g) => g.destroy());
    graficos = [];

    const ctxLinha = outlet.querySelector('#grafico-linha');
    graficos.push(new Chart(ctxLinha, {
      type: 'line',
      data: {
        labels: ['06h', '07h', '08h', '09h', '10h', '11h'],
        datasets: [{
          label: 'Tempo médio (min)',
          data: [18, 22, 15, 12, 20, 17],
          borderColor: lerVariavelCss('--cor-ambar-500'),
          backgroundColor: lerVariavelCss('--cor-ambar-glow'),
          tension: 0.35,
          fill: true,
        }],
      },
      options: criarOpcoesChart(),
    }));

    const ctxColuna = outlet.querySelector('#grafico-coluna');
    graficos.push(new Chart(ctxColuna, {
      type: 'bar',
      data: {
        labels: ['Apresentação', 'Prontidão', 'Lanche', 'Refeição', 'Fim Jornada'],
        datasets: [{
          label: 'Concluídos',
          data: [42, 38, 30, 22, 10],
          backgroundColor: lerVariavelCss('--cor-ok-500'),
          borderRadius: 6,
        }],
      },
      options: criarOpcoesChart(),
    }));

    const ctxBarra = outlet.querySelector('#grafico-barra');
    graficos.push(new Chart(ctxBarra, {
      type: 'bar',
      data: {
        labels: ['06x18', '18x06', '00x12', '12x00'],
        datasets: [{
          label: 'Conformidade (%)',
          data: [94, 88, 97, 91],
          backgroundColor: lerVariavelCss('--cor-info-500'),
          borderRadius: 6,
        }],
      },
      options: { ...criarOpcoesChart(), indexAxis: 'y' },
    }));
  }

  carregar();

  return function desmontar() {
    graficos.forEach((g) => g.destroy());
  };
}
