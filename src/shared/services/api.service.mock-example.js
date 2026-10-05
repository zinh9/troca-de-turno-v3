import { mapearEmpregados } from '../entities/empregado.entity.js';

/**
 * api.service.mock-example.js — EXEMPLO de implementação que respeita a
 * mesma interface pública do ApiService real (buscarPainel,
 * buscarEstrutura, registrar*, escolherIntervaloLanche, iniciarIntervalo,
 * enviarJustificativa, acionarChamadaRadio, finalizarJornada), mas
 * devolve dados fixos no formato do contrato (ver README, seção 4).
 *
 * Uso (por exemplo em demo.html, ou temporariamente em app.js durante
 * o desenvolvimento sem a API real disponível):
 *
 *   import { MockApiService } from './shared/services/api.service.mock-example.js';
 *   container.registerSingleton('apiService', () => new MockApiService());
 *
 * NÃO usar em produção — é só para prototipar o front sem depender do
 * back-end (C# .NET) estar de pé.
 */
export class MockApiService {
  async buscarPainel() {
    const json = gerarPainelExemplo();
    return {
      emManutencao: false,
      info: json.info,
      empregados: mapearEmpregados(json.empregados),
    };
  }

  /** Estrutura usada pela tela de Menu — ver README seção 4.3. */
  async buscarEstrutura() {
    return {
      success: true,
      supervisoes: [
        { nome: 'VPN', label: 'VPN - Porto Velho', locais: ['Guarita_2', 'Guarita_3', 'Guarita_4'] },
        { nome: 'TORRE_A', label: 'Torre A - Guarita 7', locais: ['Guarita_7'] },
        { nome: 'TORRE_B', label: 'Torre B', locais: ['Guarita_2', 'Guarita_3', 'Guarita_4'] },
      ],
    };
  }

  async registrarApresentacao(matricula) {
    console.info('[MockApiService] registrarApresentacao', matricula);
    return { success: true };
  }

  async registrarProntidao(matricula) {
    console.info('[MockApiService] registrarProntidao', matricula);
    return { success: true };
  }

  async escolherIntervaloLanche(matricula, intervaloEscolhido) {
    console.info('[MockApiService] escolherIntervaloLanche', matricula, intervaloEscolhido);
    return { success: true };
  }

  async iniciarIntervalo(etapa, matricula) {
    console.info('[MockApiService] iniciarIntervalo', etapa, matricula);
    return { success: true };
  }

  async enviarJustificativa(etapa, matricula, texto) {
    console.info('[MockApiService] enviarJustificativa', etapa, matricula, texto);
    return { success: true };
  }

  async acionarChamadaRadio(matricula) {
    console.info('[MockApiService] acionarChamadaRadio', matricula);
    return { success: true };
  }

  async finalizarJornada(matricula) {
    console.info('[MockApiService] finalizarJornada', matricula);
    return { success: true };
  }
}

function gerarPainelExemplo() {
  const agora = new Date();
  const isoAgora = agora.toISOString();
  const hoje = isoAgora.slice(0, 10);

  const info = {
    emManutencao: false,
    ultimaAtualizacao: isoAgora,
    serverTime: isoAgora,
    supervisao: 'VPN',
    local: 'Porto Velho',
    horarioReferencia: { chegada: '18:00', saida: '06:00' },
  };

  // Conjunto pensado para exercitar todos os estados visuais das células
  // (ver comentários em tabela-jornada.component.js e celula-intervalo.component.js).
  const empregados = [
    {
      matricula: '81053394', nome: 'ISMAYLER TAVARES', cargo: 'MAQ', supervisao: 'VPN', local: 'Guarita_2', turno: '18x06',
      jornada: {
        apresentacao: { dataHora: `${hoje}T18:06:00`, status: 'OK', justificativa: null },
        prontidao: { dataHora: null, status: 'AGUARDANDO', justificativa: null, tempoApresentacaoProntidaoMin: null, tempoHorarioExatoProntidaoMin: null, fase: 'ATRASADO_JUSTIFICAR' },
        tac: { realizado: true },
        lanche: { intervaloEscolhido: null, janela: '02:00 às 04:30', status: 'AGUARDANDO_JANELA', dataHoraInicio: null, dataHoraProntidao: null, justificativa: null, liberadoCCP: null },
        refeicao: { janela: '00:00 às 01:00', status: 'LIBERADO_PARA_ACAO', dataHoraInicio: null, dataHoraProntidao: null, justificativa: null, liberadoCCP: true },
        fimJornada: { dataHora: null, atrasado: false, justificativa: null, chamadaCPT: null, fimJornadaCPT: null },
      },
    },
    {
      matricula: '81022190', nome: 'WEVERSON SIAN', cargo: 'MAQ', supervisao: 'VPN', local: 'Guarita_2', turno: '18x06',
      jornada: {
        apresentacao: { dataHora: `${hoje}T18:01:00`, status: 'OK', justificativa: null },
        prontidao: { dataHora: null, status: 'AGUARDANDO', justificativa: null, tempoApresentacaoProntidaoMin: null, tempoHorarioExatoProntidaoMin: null, fase: 'ATRASADO_JUSTIFICAR' },
        tac: { realizado: true },
        lanche: { intervaloEscolhido: null, janela: '02:00 às 04:30', status: 'AGUARDANDO_JANELA', dataHoraInicio: null, dataHoraProntidao: null, justificativa: null, liberadoCCP: null },
        refeicao: { janela: '00:00 às 01:00', status: 'LIBERADO_PARA_ACAO', dataHoraInicio: null, dataHoraProntidao: null, justificativa: null, liberadoCCP: true },
        fimJornada: { dataHora: null, atrasado: false, justificativa: null, chamadaCPT: null, fimJornadaCPT: null },
      },
    },
    {
      matricula: '81047702', nome: 'LEANDRO BARROZO', cargo: 'OOF', supervisao: 'VPN', local: 'Guarita_3', turno: '18x06',
      jornada: {
        apresentacao: { dataHora: `${hoje}T13:11:00`, status: 'JUSTIFICATIVA_OK', justificativa: 'Atividade operacional' },
        prontidao: { dataHora: null, status: 'AGUARDANDO', justificativa: null, tempoApresentacaoProntidaoMin: null, tempoHorarioExatoProntidaoMin: null, fase: 'ATRASADO_JUSTIFICAR' },
        tac: { realizado: true },
        lanche: { intervaloEscolhido: null, janela: null, status: 'ATRASADO', dataHoraInicio: null, dataHoraProntidao: null, justificativa: null, liberadoCCP: null },
        refeicao: { janela: '19:35 às 20:37', status: 'EM_ANDAMENTO', dataHoraInicio: `${hoje}T19:35:00`, dataHoraProntidao: null, justificativa: null, liberadoCCP: true },
        fimJornada: { dataHora: null, atrasado: false, justificativa: null, chamadaCPT: null, fimJornadaCPT: null },
      },
    },
    {
      matricula: '81031850', nome: 'MATHEUS AZEVEDO', cargo: 'MAQ', supervisao: 'VPN', local: 'Guarita_2', turno: '18x06',
      jornada: {
        apresentacao: { dataHora: `${hoje}T18:03:00`, status: 'OK', justificativa: null },
        prontidao: { dataHora: null, status: 'AGUARDANDO', justificativa: null, tempoApresentacaoProntidaoMin: null, tempoHorarioExatoProntidaoMin: null, fase: 'ATRASADO_JUSTIFICAR' },
        tac: { realizado: true },
        lanche: { intervaloEscolhido: null, janela: '02:00 às 04:30', status: 'AGUARDANDO_JANELA', dataHoraInicio: null, dataHoraProntidao: null, justificativa: null, liberadoCCP: null },
        refeicao: { janela: '00:00 às 01:00', status: 'LIBERADO_PARA_ACAO', dataHoraInicio: null, dataHoraProntidao: null, justificativa: null, liberadoCCP: true },
        fimJornada: { dataHora: null, atrasado: false, justificativa: null, chamadaCPT: null, fimJornadaCPT: null },
      },
    },
    {
      matricula: '81065521', nome: 'ELIZIANE FIOROTTI', cargo: 'OOF', supervisao: 'VPN', local: 'Guarita_4', turno: '18x06',
      jornada: {
        apresentacao: { dataHora: `${hoje}T13:03:00`, status: 'JUSTIFICATIVA_OK', justificativa: 'Atividade operacional' },
        prontidao: { dataHora: null, status: 'AGUARDANDO', justificativa: null, tempoApresentacaoProntidaoMin: null, tempoHorarioExatoProntidaoMin: null, fase: 'ATRASADO_JUSTIFICAR' },
        tac: { realizado: true },
        lanche: { intervaloEscolhido: null, janela: null, status: 'ATRASADO', dataHoraInicio: null, dataHoraProntidao: null, justificativa: null, liberadoCCP: null },
        refeicao: { janela: '19:35 às 20:37', status: 'EM_ANDAMENTO', dataHoraInicio: `${hoje}T19:35:00`, dataHoraProntidao: null, justificativa: null, liberadoCCP: true },
        fimJornada: { dataHora: null, atrasado: false, justificativa: null, chamadaCPT: null, fimJornadaCPT: null },
      },
    },
  ];

  return { success: true, info, empregados };
}
