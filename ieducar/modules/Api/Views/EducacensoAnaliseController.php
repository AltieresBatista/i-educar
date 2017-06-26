<?php

#error_reporting(E_ALL);
#ini_set("display_errors", 1);

/**
 * i-Educar - Sistema de gestão escolar
 *
 * Copyright (C) 2006  Prefeitura Municipal de Itajaí
 *     <ctima@itajai.sc.gov.br>
 *
 * Este programa é software livre; você pode redistribuí-lo e/ou modificá-lo
 * sob os termos da Licença Pública Geral GNU conforme publicada pela Free
 * Software Foundation; tanto a versão 2 da Licença, como (a seu critério)
 * qualquer versão posterior.
 *
 * Este programa é distribuí­do na expectativa de que seja útil, porém, SEM
 * NENHUMA GARANTIA; nem mesmo a garantia implí­cita de COMERCIABILIDADE OU
 * ADEQUAÇÃO A UMA FINALIDADE ESPECÍFICA. Consulte a Licença Pública Geral
 * do GNU para mais detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral do GNU junto
 * com este programa; se não, escreva para a Free Software Foundation, Inc., no
 * endereço 59 Temple Street, Suite 330, Boston, MA 02111-1307 USA.
 *
 * @author    Caroline Salib <caroline@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   Api
 * @subpackage  Modules
 * @since   Arquivo disponível desde a versão ?
 * @version   $Id$
 */

require_once 'lib/Portabilis/Controller/ApiCoreController.php';
require_once 'intranet/include/clsBanco.inc.php';
require_once 'lib/Portabilis/Date/Utils.php';

class EducacensoAnaliseController extends ApiCoreController
{

  protected function analisaEducacensoRegistro00() {

    $escola = $this->getRequest()->escola;
    $ano    = $this->getRequest()->ano;

    $sql = "SELECT educacenso_cod_escola.cod_escola_inep AS inep,
                   fisica_gestor.cpf AS cpf_gestor_escolar,
                   pessoa_gestor.nome AS nome_gestor_escolar,
                   escola.cargo_gestor AS cargo_gestor_escolar,
                   pessoa_gestor.email AS email_gestor_escolar,
                   EXTRACT(YEAR FROM modulo1.data_inicio) AS data_inicio,
                   EXTRACT(YEAR FROM modulo2.data_fim) AS data_fim,
                   escola.latitude AS latitude,
                   escola.longitude AS longitude,
                   municipio.cod_ibge AS inep_municipio,
                   uf.cod_ibge AS inep_uf,
                   distrito.cod_ibge AS inep_distrito,
                   juridica.fantasia AS nome_escola,
                   escola.orgao_regional AS orgao_regional
              FROM pmieducar.escola
             INNER JOIN cadastro.juridica ON (juridica.idpes = escola.ref_idpes)
             INNER JOIN pmieducar.escola_ano_letivo ON (escola_ano_letivo.ref_cod_escola = escola.cod_escola)
             INNER JOIN pmieducar.ano_letivo_modulo modulo1 ON (modulo1.ref_ref_cod_escola = escola.cod_escola
                                                                AND modulo1.ref_ano = escola_ano_letivo.ano
                                                                AND modulo1.sequencial = 1)
             INNER JOIN pmieducar.ano_letivo_modulo modulo2 ON (modulo2.ref_ref_cod_escola = escola.cod_escola
                                                                AND modulo2.ref_ano = escola_ano_letivo.ano
                                                                AND modulo2.sequencial = (SELECT MAX(sequencial)
                                                                                            FROM pmieducar.ano_letivo_modulo
                                                                                           WHERE ref_ano = escola_ano_letivo.ano
                                                                                             AND ref_ref_cod_escola = escola.cod_escola))
              LEFT JOIN cadastro.pessoa pessoa_gestor ON (pessoa_gestor.idpes = escola.ref_idpes_gestor)
              LEFT JOIN cadastro.fisica fisica_gestor ON (fisica_gestor.idpes = escola.ref_idpes_gestor)
              LEFT JOIN modules.educacenso_cod_escola ON (educacenso_cod_escola.cod_escola = escola.cod_escola)
              LEFT JOIN cadastro.endereco_pessoa ON (endereco_pessoa.idpes = escola.ref_idpes)
              LEFT JOIN cadastro.endereco_externo ON (endereco_externo.idpes = escola.ref_idpes)
              LEFT JOIN public.bairro ON (bairro.idbai = COALESCE(endereco_pessoa.idbai, (SELECT b.idbai
                                                                                            FROM public.bairro b
                                                                                           INNER JOIN cadastro.endereco_externo ee ON (UPPER(ee.bairro) = UPPER(b.nome))
                                                                                           WHERE ee.idpes = escola.ref_idpes
                                                                                           LIMIT 1)))
              LEFT JOIN public.municipio ON (municipio.idmun = bairro.idmun)
              LEFT JOIN public.uf ON (uf.sigla_uf = COALESCE(municipio.sigla_uf, endereco_externo.sigla_uf))
              LEFT JOIN public.distrito ON (distrito.idmun = bairro.idmun)
             WHERE escola.cod_escola = $1
               AND escola_ano_letivo.ano = $2";

    $escola = $this->fetchPreparedQuery($sql, array($escola, $ano));

    if(empty($escola)){
      $this->messenger->append("O ano letivo {$ano} não foi definido.");
      return array('title' => "Análise exportação - Registro 00");
    }

    $escola       = $escola[0];
    $nomeEscola   = Portabilis_String_Utils::toUtf8(strtoupper($escola["nome_escola"]));
    $anoAtual     = date("Y");
    $anoAnterior  = $anoAtual-1;
    $anoPosterior = $anoAtual+1;

    $mensagem = array();

    if (!$escola["inep"]) {
      $mensagem[] = array("text" => "Dados para formular o registro 00 da escola {$nomeEscola} não encontrados. Verifique se a escola possui o código INEP cadastrado.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Dados gerais > Campo: Código INEP)",
                          "fail" => true);
    }
    if (!$escola["cpf_gestor_escolar"]) {
      $mensagem[] = array("text" => "Dados para formular o registro 00 da escola {$nomeEscola} não encontrados. Verifique se o(a) gestor(a) escolar possui o CPF cadastrado.",
                          "path" => "(Pessoa FJ > Pessoa física > Editar > Campo: CPF)",
                          "fail" => true);
    }
    if (!$escola["nome_gestor_escolar"]) {
      $mensagem[] = array("text" => "Dados para formular o registro 00 da escola {$nomeEscola} não encontrados. Verifique se o(a) gestor(a) escolar foi informado(a).",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Dados gerais > Campo: Gestor escolar)",
                          "fail" => true);
    }
    if (!$escola["email_gestor_escolar"] || empty($escola["email_gestor_escolar"])) {
      $mensagem[] = array("text" => "Dados para formular o registro 00 da escola {$nomeEscola} não encontrados. Verifique se o e-mail do(a) gestor(a) escolar foi informado.",
                          "path" => "(Pessoas > Cadastros > Pessoas físicas > Campo: E-mail)",
                          "fail" => true);
    }
    if (!$escola["cargo_gestor_escolar"]) {
      $mensagem[] = array("text" => "Dados para formular o registro 00 da escola {$nomeEscola} não encontrados. Verifique se o cargo do(a) gestor(a) escolar foi informado.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Campo: Cargo do gestor escolar)",
                          "fail" => true);
    }
    if ($escola["data_inicio"] != $anoAtual && $escola["data_inicio"] != $anoAnterior) {
      $mensagem[] = array("text" => "Dados para formular o registro 00 da escola {$nomeEscola} possui valor inválido. Verifique se a data inicial da primeira etapa foi cadastrada corretamente.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar ano letivo > Ok > Campo: Data inicial)",
                          "fail" => true);
    }
    if ($escola["data_fim"] != $anoAtual && $escola["data_fim"] != $anoPosterior) {
      $mensagem[] = array("text" => "Dados para formular o registro 00 da escola {$nomeEscola} possui valor inválido. Verifique se a data final da última etapa foi cadastrada corretamente.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar ano letivo > Ok > Campo: Data final)",
                          "fail" => true);
    }
    if ((!$escola["latitude"]) && $escola["longitude"]) {
      $mensagem[] = array("text" => "Dados para formular o registro 00 da escola {$nomeEscola} não encontrados. Verificamos que a longitude foi informada, portanto obrigatoriamente a latitude também deve ser informada.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Dados gerais > Campo: Latitude)",
                          "fail" => true);
    }
    if ((!$escola["longitude"]) && $escola["latitude"]) {
      $mensagem[] = array("text" => "Dados para formular o registro 00 da escola {$nomeEscola} não encontrados. Verificamos que a latitude foi informada, portanto obrigatoriamente a longitude também deve ser informada.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Dados gerais > Campo: Longitude)",
                          "fail" => true);
    }
    if (!$escola["inep_uf"]) {
      $mensagem[] = array("text" => "Dados para formular o registro 00 da escola {$nomeEscola} não encontrados. Verifique se o código da UF informada, foi cadastrado conforme a 'Tabela de UF'.",
                          "path" => "(Endereçamento > Estado > Editar > Campo: Código INEP)",
                          "fail" => true);
    }
    if (!$escola["inep_municipio"]) {
      $mensagem[] = array("text" => "Dados para formular o registro 00 da escola {$nomeEscola} não encontrados. Verifique se o código do município informado, foi cadastrado conforme a 'Tabela de Municípios'.",
                          "path" => "(Endereçamento > Município > Editar > Campo: Código INEP)",
                          "fail" => true);
    }
    if (!$escola["inep_distrito"]) {
      $mensagem[] = array("text" => "Dados para formular o registro 00 da escola {$nomeEscola} não encontrados. Verifique se o código do distrito informado, foi cadastrado conforme a 'Tabela de Distritos'.",
                          "path" => "(Endereçamento > Distrito > Editar > Campo: Código INEP)",
                          "fail" => true);
    }
    if (!$escola["orgao_regional"]) {
      $mensagem[] = array("text" => "Aviso: Dados para formular o registro 00 da escola {$nomeEscola} não encontrados. Verificamos que o código do órgão regional de ensino não foi preenchido, caso seu estado possua uma subdivisão e a escola {$nomeEscola} não for federal vinculada a Setec, o código deve ser inserido conforme a 'Tabela de Órgãos Regionais'.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Dados gerais > Campo: Código do órgão regional)",
                          "fail" => false);
    }

    return array('mensagens' => $mensagem,
                 'title'     => "Análise exportação - Registro 00");
  }

  protected function analisaEducacensoRegistro10() {

    $escola = $this->getRequest()->escola;

    $sql = "SELECT escola.local_funcionamento AS local_funcionamento,
                   escola.condicao AS condicao,
                   escola.agua_consumida AS agua_consumida,
                   escola.agua_rede_publica AS agua_rede_publica,
                   escola.agua_poco_artesiano AS agua_poco_artesiano,
                   escola.agua_cacimba_cisterna_poco AS agua_cacimba_cisterna_poco,
                   escola.agua_fonte_rio AS agua_fonte_rio,
                   escola.agua_inexistente AS agua_inexistente,
                   escola.energia_rede_publica AS energia_rede_publica,
                   escola.energia_gerador AS energia_gerador,
                   escola.energia_outros AS energia_outros,
                   escola.energia_inexistente AS energia_inexistente,
                   escola.esgoto_rede_publica AS esgoto_rede_publica,
                   escola.esgoto_fossa AS esgoto_fossa,
                   escola.esgoto_inexistente AS esgoto_inexistente,
                   escola.lixo_coleta_periodica AS lixo_coleta_periodica,
                   escola.lixo_queima AS lixo_queima,
                   escola.lixo_joga_outra_area AS lixo_joga_outra_area,
                   escola.lixo_recicla AS lixo_recicla,
                   escola.lixo_enterra AS lixo_enterra,
                   escola.lixo_outros AS lixo_outros,
                   escola.dependencia_sala_diretoria AS dependencia_sala_diretoria,
                   escola.dependencia_sala_professores AS dependencia_sala_professores,
                   escola.dependencia_sala_secretaria AS dependncia_sala_secretaria,
                   escola.dependencia_laboratorio_informatica AS dependencia_laboratorio_informatica,
                   escola.dependencia_laboratorio_ciencias AS dependencia_laboratorio_ciencias,
                   escola.dependencia_sala_aee AS dependencia_sala_aee,
                   escola.dependencia_quadra_coberta AS dependencia_quadra_coberta,
                   escola.dependencia_quadra_descoberta AS dependencia_quadra_descoberta,
                   escola.dependencia_cozinha AS dependencia_cozinha,
                   escola.dependencia_biblioteca AS dependencia_biblioteca,
                   escola.dependencia_sala_leitura AS dependencia_sala_leitura,
                   escola.dependencia_parque_infantil AS dependencia_parque_infantil,
                   escola.dependencia_bercario AS dependencia_bercario,
                   escola.dependencia_banheiro_fora AS dependencia_banheiro_fora,
                   escola.dependencia_banheiro_dentro AS dependencia_banheiro_dentro,
                   escola.dependencia_banheiro_infantil AS dependencia_banheiro_infantil,
                   escola.dependencia_banheiro_deficiente AS dependencia_banheiro_deficiente,
                   escola.dependencia_banheiro_chuveiro AS dependencia_banheiro_chuveiro,
                   escola.dependencia_refeitorio AS dependencia_refeitorio,
                   escola.dependencia_dispensa AS dependencia_dispensa,
                   escola.dependencia_aumoxarifado AS dependencia_aumoxarifado,
                   escola.dependencia_auditorio AS dependencia_auditorio,
                   escola.dependencia_patio_coberto AS dependencia_patio_coberto,
                   escola.dependencia_patio_descoberto AS dependencia_patio_descoberto,
                   escola.dependencia_alojamento_aluno AS dependencia_alojamento_aluno,
                   escola.dependencia_alojamento_professor AS dependencia_alojamento_professor,
                   escola.dependencia_area_verde AS dependencia_area_verde,
                   escola.dependencia_lavanderia AS dependencia_lavanderia,
                   escola.dependencia_nenhuma_relacionada AS dependencia_nenhuma_relacionada,
                   escola.dependencia_numero_salas_existente AS dependencia_numero_salas_existente,
                   escola.dependencia_numero_salas_utilizadas AS dependencia_numero_salas_utilizadas,
                   escola.televisoes AS televisoes,
                   escola.videocassetes AS videocassetes,
                   escola.dvds AS dvds,
                   escola.antenas_parabolicas AS antenas_parabolicas,
                   escola.copiadoras AS copiadoras,
                   escola.retroprojetores AS retroprojetores,
                   escola.impressoras AS impressoras,
                   escola.aparelhos_de_som AS aparelhos_de_som,
                   escola.projetores_digitais AS projetores_digitais,
                   escola.faxs AS faxs,
                   escola.maquinas_fotograficas AS maquinas_fotograficas,
                   escola.computadores AS computadores,
                   escola.computadores_administrativo AS computadores_administrativo,
                   escola.computadores_alunos AS computadores_alunos,
                   escola.impressoras_multifuncionais AS impressoras_multifuncionais,
                   escola.total_funcionario AS total_funcionario,
                   escola.atendimento_aee AS atendimento_aee,
                   escola.atividade_complementar AS atividade_complementar,
                   escola.localizacao_diferenciada AS localizacao_diferenciada,
                   escola.didatico_nao_utiliza AS didatico_nao_utiliza,
                   escola.didatico_quilombola AS didatico_quilombola,
                   escola.didatico_indigena AS didatico_indigena,
                   escola.lingua_ministrada AS lingua_ministrada,
                   escola.educacao_indigena AS educacao_indigena,
                   juridica.fantasia AS nome_escola
              FROM pmieducar.escola
             INNER JOIN cadastro.juridica ON (juridica.idpes = escola.ref_idpes)
             WHERE escola.cod_escola = $1";

    $escola = $this->fetchPreparedQuery($sql, array($escola));

    if(empty($escola)){
      $this->messenger->append("Ocorreu algum problema ao decorrer da análise.");
      return array('title' => "Análise exportação - Registro 10");
    }

    $escola        = $escola[0];
    $nomeEscola    = Portabilis_String_Utils::toUtf8(strtoupper($escola["nome_escola"]));
    $predioEscolar = 3; //Valor fixo definido no cadastro de escola

    $existeAbastecimentoAgua = ($escola["agua_rede_publica"] ||
                                $escola["agua_poco_artesiano"] ||
                                $escola["agua_cacimba_cisterna_poco"] ||
                                $escola["agua_fonte_rio"] ||
                                $escola["agua_inexistente"]);

    $existeAbastecimentoEnergia = ($escola["energia_rede_publica"] ||
                                   $escola["energia_gerador"] ||
                                   $escola["energia_outros"] ||
                                   $escola["energia_inexistente"]);

    $existeEsgotoSanitario = ($escola["esgoto_rede_publica"] || $escola["esgoto_fossa"] || $escola["esgoto_inexistente"]);

    $existeDestinacaoLixo = ($escola["lixo_coleta_periodica"] ||
                             $escola["lixo_queima"] ||
                             $escola["lixo_joga_outra_area"] ||
                             $escola["lixo_recicla"] ||
                             $escola["lixo_enterra"] ||
                             $escola["lixo_outros"]);

    $existeDependencia = ($escola["dependencia_sala_diretoria"] || $escola["dependencia_sala_professores"] ||
                          $escola["dependncia_sala_secretaria"] || $escola["dependencia_laboratorio_informatica"] ||
                          $escola["dependencia_laboratorio_ciencias"] || $escola["dependencia_sala_aee"] ||
                          $escola["dependencia_quadra_coberta"] || $escola["dependencia_quadra_descoberta"] ||
                          $escola["dependencia_cozinha"] || $escola["dependencia_biblioteca"] ||
                          $escola["dependencia_sala_leitura"] || $escola["dependencia_parque_infantil"] ||
                          $escola["dependencia_bercario"] || $escola["dependencia_banheiro_fora"] ||
                          $escola["dependencia_banheiro_dentro"] || $escola["dependencia_banheiro_infantil"] ||
                          $escola["dependencia_banheiro_deficiente"] || $escola["dependencia_banheiro_chuveiro"] ||
                          $escola["dependencia_refeitorio"] || $escola["dependencia_dispensa"] ||
                          $escola["dependencia_aumoxarifado"] || $escola["dependencia_auditorio"] ||
                          $escola["dependencia_patio_coberto"] || $escola["dependencia_patio_descoberto"] ||
                          $escola["dependencia_alojamento_aluno"] || $escola["dependencia_alojamento_professor"] ||
                          $escola["dependencia_area_verde"] || $escola["dependencia_lavanderia"] ||
                          $escola["dependencia_nenhuma_relacionada"]);

    $existeEquipamentos = ($escola["televisoes"] || $escola["videocassetes"] ||
                           $escola["dvds"] || $escola["antenas_parabolicas"] ||
                           $escola["copiadoras"] || $escola["retroprojetores"] ||
                           $escola["impressoras"] || $escola["aparelhos_de_som"] ||
                           $escola["projetores_digitais"] || $escola["faxs"] ||
                           $escola["maquinas_fotograficas"] || $escola["computadores"] ||
                           $escola["computadores_administrativo"] || $escola["computadores_alunos"] ||
                           $escola["impressoras_multifuncionais"]);

    $existeMaterialDidatico = ($escola["didatico_nao_utiliza"] || $escola["didatico_quilombola"] || $escola["didatico_indigena"]);

    $mensagem = array();

    if (!$escola["local_funcionamento"]) {
      $mensagem[] = array("text" => "Dados para formular o registro 10 da escola {$nomeEscola} não encontrados. Verifique se o local de funcionamento da escola foi informado.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Infraestrutura > Campo: Local de funcionamento)",
                          "fail" => true);
    }
    if($escola["local_funcionamento"] == $predioEscolar && !$escola["condicao"]) {
      $mensagem[] = array("text" => "Dados para formular o registro 10 da escola {$nomeEscola} não encontrados. Verificamos que o local de funcionamento da escola é em um prédio escolar, portanto obrigatoriamente é necessário informar qual a forma de ocupação do prédio.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Infraestrutura > Campo: Condição)",
                          "fail" => true);
    }
    if (!$escola["agua_consumida"]) {
      $mensagem[] = array("text" => "Dados para formular o registro 10 da escola {$nomeEscola} não encontrados. Verifique se a água consumida pelos alunos foi informada.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Infraestrutura > Campo: Água consumida pelos alunos)",
                          "fail" => true);
    }
    if (!$existeAbastecimentoAgua) {
      $mensagem[] = array("text" => "Dados para formular o registro 10 da escola {$nomeEscola} não encontrados. Verifique se uma das formas do abastecimento de água foi informada.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Infraestrutura > Campos: Abastecimento de água)",
                          "fail" => true);
    }
    if (!$existeAbastecimentoEnergia) {
      $mensagem[] = array("text" => "Dados para formular o registro 10 da escola {$nomeEscola} não encontrados. Verifique se uma das formas do abastecimento de energia elétrica foi informada.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Infraestrutura > Campos: Abastecimento de energia elétrica)",
                          "fail" => true);
    }
    if (!$existeEsgotoSanitario) {
      $mensagem[] = array("text" => "Dados para formular o registro 10 da escola {$nomeEscola} não encontrados. Verifique se alguma opção de esgoto sanitário foi informada.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Infraestrutura > Campos: Esgoto sanitário)",
                          "fail" => true);
    }
    if (!$existeDestinacaoLixo) {
      $mensagem[] = array("text" => "Dados para formular o registro 10 da escola {$nomeEscola} não encontrados. Verifique se uma das formas da destinação do lixo foi informada.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Infraestrutura > Campos: Destinação do lixo)",
                          "fail" => true);
    }
    if (!$existeDependencia) {
      $mensagem[] = array("text" => "Aviso: Dados para formular o registro 10 da escola {$nomeEscola} não encontrados. Nenhum campo foi preenchido referente as dependências existentes na escola, portanto todos serão registrados como NÃO.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Dependências > Campos: Dependências existentes na escola)",
                          "fail" => false);
    }
    if($escola["local_funcionamento"] == $predioEscolar && !$escola["dependencia_numero_salas_existente"]) {
      $mensagem[] = array("text" => "Dados para formular o registro 10 da escola {$nomeEscola} não encontrados. Verificamos que o local de funcionamento da escola é em um prédio escolar, portanto obrigatoriamente é necessário informar o número de salas de aula existentes na escola.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Dependências > Campo: Dependências existentes na escola - Número de salas de aula existentes na escola)",
                          "fail" => true);
    }
    if (!$escola['dependencia_numero_salas_utilizadas']) {
      $mensagem[] = array("text" => "Dados para formular o registro 10 da escola {$nomeEscola} não encontrados. Verifique se o número de salas utilizadas como sala de aula foi informado.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Dependências > Campo: Dependências existentes na escola – Número de salas utilizadas como sala de aula)",
                          "fail" => true);
    }
    if (!$existeEquipamentos) {
      $mensagem[] = array("text" => "Aviso: Dados para formular o registro 10 da escola {$nomeEscola} não encontrados. Nenhum campo foi preenchido referente a quantidade de equipamentos existentes na escola, portanto todos serão registrados como NÃO.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Equipamentos > Campos: Quantidade de equipamentos)",
                          "fail" => false);
    }
    if (!$escola["total_funcionario"]) {
      $mensagem[] = array("text" => "Dados para formular o registro 10 da escola {$nomeEscola} não encontrados. Verifique se o total de funcionários da escola foi informado.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Dependências > Campo: Total de funcionários da escola)",
                          "fail" => true);
    }
    if ($escola["atendimento_aee"] < 0) {
      $mensagem[] = array("text" => "Dados para formular o registro 10 da escola {$nomeEscola} não encontrados. Verifique se o atendimento educacional especializado - AEE foi informado.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Dados do ensino > Campo: Atendimento educacional especializado - AEE)",
                          "fail" => true);
    }
    if ($escola["atividade_complementar"] < 0) {
      $mensagem[] = array("text" => "Dados para formular o registro 10 da escola {$nomeEscola} não encontrados. Verifique se a atividade complementar foi informada.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Dados do ensino > Campo: Atividade complementar)",
                          "fail" => true);
    }
    if (!$escola["localizacao_diferenciada"]) {
      $mensagem[] = array("text" => "Dados para formular o registro 10 da escola {$nomeEscola} não encontrados. Verifique se a localização diferenciada da escola foi informada.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Dados do ensino > Campo: Localização diferenciada da escola)",
                          "fail" => true);
    }
    if (!$existeMaterialDidatico) {
      $mensagem[] = array("text" => "Dados para formular o registro 10 da escola {$nomeEscola} não encontrados. Verifique se algum material didático específico para atendimento à diversidade sócio-cultural foi informado.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Dados do ensino > Campo: Materiais didáticos específicos para atendimento à diversidade sócio-cultural)",
                          "fail" => true);
    }
    if ($escola['educacao_indigena'] && !$escola["lingua_ministrada"]) {
      $mensagem[] = array("text" => "Dados para formular o registro 10 da escola {$nomeEscola} não encontrados. Verificamos que a escola trabalha com educação indígena, portanto obrigatoriamente é necessário informar a língua em que o ensino é ministrado.",
                          "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Dados do ensino > Campo: Língua em que o ensino é ministrado)",
                          "fail" => true);
    }

    return array('mensagens' => $mensagem,
                 'title'     => "Análise exportação - Registro 10");
  }

  protected function analisaEducacensoRegistro20() {

    $escola = $this->getRequest()->escola;
    $ano    = $this->getRequest()->ano;

    $sql = "SELECT turma.nm_turma AS nome_turma,
                   turma.hora_inicial AS hora_inicial,
                   turma.hora_final AS hora_final,
                   (SELECT TRUE
                      FROM pmieducar.turma_dia_semana
                     WHERE ref_cod_turma = turma.cod_turma LIMIT 1) AS dias_semana,
                   turma.tipo_atendimento AS tipo_atendimento,
                   turma.atividade_complementar_1 AS atividade_complementar_1,
                   turma.atividade_complementar_2 AS atividade_complementar_2,
                   turma.atividade_complementar_3 AS atividade_complementar_3,
                   turma.atividade_complementar_4 AS atividade_complementar_4,
                   turma.atividade_complementar_5 AS atividade_complementar_5,
                   turma.atividade_complementar_6 AS atividade_complementar_6,
                   turma.aee_braille AS aee_braille,
                   turma.aee_recurso_optico AS aee_recurso_optico ,
                   turma.aee_estrategia_desenvolvimento AS aee_estrategia_desenvolvimento,
                   turma.aee_tecnica_mobilidade AS aee_tecnica_mobilidade,
                   turma.aee_libras AS aee_libras,
                   turma.aee_caa AS aee_caa,
                   turma.aee_curricular AS aee_curricular,
                   turma.aee_soroban AS aee_soroban,
                   turma.aee_informatica AS aee_informatica,
                   turma.aee_lingua_escrita AS aee_lingua_escrita,
                   turma.aee_autonomia AS aee_autonomia,
                   turma.etapa_educacenso AS etapa_educacenso,
                   juridica.fantasia AS nome_escola
              FROM pmieducar.escola
             INNER JOIN cadastro.juridica ON (juridica.idpes = escola.ref_idpes)
             INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_escola = escola.cod_escola)
             WHERE escola.cod_escola = $1
               AND COALESCE(turma.nao_informar_educacenso, 0) = 0
               AND turma.ano = $2
               AND turma.ativo = 1
               AND turma.visivel = TRUE
               AND escola.ativo = 1";

    $turmas = $this->fetchPreparedQuery($sql, array($escola, $ano));

    if(empty($turmas)){
      $this->messenger->append("Ocorreu algum problema ao decorrer da análise.");
      return array('title' => "Análise exportação - Registro 20");
    }

    $mensagem = array();

    foreach ($turmas as $turma) {

      $nomeEscola = Portabilis_String_Utils::toUtf8(strtoupper($turma["nome_escola"]));
      $nomeTurma  = Portabilis_String_Utils::toUtf8(strtoupper($turma["nome_turma"]));
      $atividadeComplementar = ($turma["tipo_atendimento"] == 4); //Código 4 fixo no cadastro de turma
      $existeAtividadeComplementar = ($turma["atividade_complementar_1"] || $turma["atividade_complementar_2"] ||
                                      $turma["atividade_complementar_3"] || $turma["atividade_complementar_4"] ||
                                      $turma["atividade_complementar_5"] || $turma["atividade_complementar_6"]);
      $atendimentoAee = ($turma["tipo_atendimento"] == 5); //Código 5 fixo no cadastro de turma
      $existeAee = ($turma["aee_braille"] || $turma["aee_recurso_optico"] ||
                    $turma["aee_estrategia_desenvolvimento"] || $turma["aee_tecnica_mobilidade"] ||
                    $turma["aee_libras"] || $turma["aee_caa"] ||
                    $turma["aee_curricular"] || $turma["aee_soroban"] ||
                    $turma["aee_informatica"] || $turma["aee_lingua_escrita"] ||
                    $turma["aee_autonomia"]);

      switch ($turma['tipo_atendimento']) {
        case 0:
          $nomeAtendimento = 'Não sei aplica';
          break;
        case 1:
          $nomeAtendimento = 'Classe hospitalar';
          break;
        case 2:
          $nomeAtendimento = 'Unidade de internação socioeducativa';
          break;
        case 3:
          $nomeAtendimento = 'Unidade prisional';
          break;
      }

      if (!$turma["hora_inicial"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 20 da escola {$nomeEscola} não encontrados. Verifique se o horário inicial da turma {$nomeTurma} foi cadastrado.",
                            "path" => "(Cadastros > Turma > Cadastrar > Editar > Aba: Dados gerais > Campo: Hora inicial)",
                          "fail" => true);
      }
      if (!$turma["hora_final"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 20 da escola {$nomeEscola} não encontrados. Verifique se o horário final da turma {$nomeTurma} foi cadastrado.",
                            "path" => "(Cadastros > Turma > Cadastrar > Editar > Aba: Dados gerais > Campo: Hora final)",
                          "fail" => true);
      }
      if (!$turma["dias_semana"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 20 da escola {$nomeEscola} não encontrados. É necessário informar ao menos um dia da semana para a turma presencial {$nomeTurma}.",
                            "path" => "(Cadastros > Turma > Cadastrar > Editar > Aba: Dados gerais > Campos: Dia semana, Hora inicial e Hora final)",
                          "fail" => true);
      }
      if (is_null($turma["tipo_atendimento"]) || $turma["tipo_atendimento"] < 0) {
        $mensagem[] = array("text" => "Dados para formular o registro 20 da escola {$nomeEscola} não encontrados. Verifique se o tipo de atendimento da turma {$nomeTurma} foi cadastrado.",
                            "path" => "(Cadastros > Turma > Cadastrar > Editar > Aba: Dados adicionais > Campo: Tipo de atendimento)",
                          "fail" => true);
      }elseif ($turma["tipo_atendimento"] && $turma["tipo_atendimento"] != 4 && $turma["tipo_atendimento"] != 5) {
        $mensagem[] = array("text" => "Dados para formular o registro 20 da escola {$nomeEscola} não encontrados. Verificamos que o tipo de atendimento da turma {$nomeTurma} é {$nomeAtendimento}, portanto é necessário informar qual a etapa de ensino.",
                            "path" => "(Escola > Cadastros > Turmas > Aba: Dados adicionais > Campo: Etapa de ensino)",
                            "fail" => true);
      }
      if ($atividadeComplementar && !$existeAtividadeComplementar) {
        $mensagem[] = array("text" => "Dados para formular o registro 20 da escola {$nomeEscola} não encontrados. Verificamos que o tipo de atendimento da turma {$nomeTurma} é de atividade complementar, portanto obrigatoriamente é necessário informar o código de ao menos uma atividade conforme a 'Tabela de Tipo de Atividade Complementar'.",
                            "path" => "(Cadastros > Turma > Cadastrar > Editar > Aba: Dados adicionais > Campo: Código do tipo de atividade complementar)",
                          "fail" => true);
      }
      if (!$atividadeComplementar && !$atendimentoAee) {
        $mensagem[] = array("text" => "Dados para formular o registro 20 da escola {$nomeEscola} não encontrados. Verificamos que o tipo de atendimento da turma {$nomeTurma} é de educação especializada - AEE, portanto obrigatoriamente é necessário informar ao menos uma atividade realizada. ",
                            "path" => "(Cadastros > Turma > Cadastrar > Editar > Aba: Dados adicionais > Campos: De Ensino do sistema braille à Estratégias para autonomia no ambiente escolar)",
                          "fail" => true);
      }
    }

    return array('mensagens' => $mensagem,
                 'title'     => "Análise exportação - Registro 20");
  }

  protected function analisaEducacensoRegistro30() {

    $escola = $this->getRequest()->escola;
    $ano    = $this->getRequest()->ano;

    $sql = "SELECT juridica.fantasia AS nome_escola,
                   raca.raca_educacenso AS cor_raca,
                   fisica.nacionalidade AS nacionalidade,
                   uf.cod_ibge AS uf_inep,
                   municipio.cod_ibge AS municipio_inep,
                   pessoa.nome AS nome_servidor
              FROM modules.professor_turma
             INNER JOIN pmieducar.turma ON (turma.cod_turma = professor_turma.turma_id)
             INNER JOIN pmieducar.escola ON (escola.cod_escola = turma.ref_ref_cod_escola)
             INNER JOIN pmieducar.servidor ON (servidor.cod_servidor = professor_turma.servidor_id)
             INNER JOIN cadastro.juridica ON (juridica.idpes = escola.ref_idpes)
              LEFT JOIN cadastro.fisica_raca ON (fisica_raca.ref_idpes = professor_turma.servidor_id)
              LEFT JOIN cadastro.raca ON (raca.cod_raca = fisica_raca.ref_cod_raca)
             INNER JOIN cadastro.pessoa ON (pessoa.idpes = professor_turma.servidor_id)
             INNER JOIN cadastro.fisica ON (fisica.idpes = professor_turma.servidor_id)
              LEFT JOIN cadastro.endereco_pessoa ON (endereco_pessoa.idpes = professor_turma.servidor_id)
              LEFT JOIN public.municipio ON (municipio.idmun = fisica.idmun_nascimento)
              LEFT JOIN public.uf ON (uf.sigla_uf = municipio.sigla_uf)
             WHERE professor_turma.ano = $1
               AND turma.ativo = 1
               AND turma.visivel = TRUE
               AND COALESCE(turma.nao_informar_educacenso, 0) = 0
               AND turma.ano = professor_turma.ano
               AND escola.cod_escola = $2
               AND servidor.ativo = 1
             GROUP BY professor_turma.servidor_id,
                      juridica.fantasia,
                      raca.raca_educacenso,
                      fisica.nacionalidade,
                      uf.cod_ibge,
                      municipio.cod_ibge,
                      pessoa.nome
              ORDER BY nome_servidor";

    $servidores = $this->fetchPreparedQuery($sql, array($ano, $escola));

    if(empty($servidores)){
      $this->messenger->append("Nenhum servidor encontrado.");
      return array('title' => "Análise exportação - Registro 30");
    }

    $mensagem = array();
    $brasileiro = 1;

    foreach ($servidores as $servidor) {
      $nomeEscola   = Portabilis_String_Utils::toUtf8(strtoupper($servidor["nome_escola"]));
      $nomeServidor = Portabilis_String_Utils::toUtf8(strtoupper($servidor["nome_servidor"]));

      if (is_null($servidor["cor_raca"])) {
        $mensagem[] = array("text" => "Dados para formular o registro 30 da escola {$nomeEscola} não encontrados. Verifique se a raça do(a) servidor(a) {$nomeServidor} foi informada.",
                            "path" => "(Pessoa FJ > Pessoa física > Editar > Campo: Raça)",
                            "fail" => true);
      }
      if (!$servidor["nacionalidade"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 30 da escola {$nomeEscola} não encontrados. Verifique se a nacionalidade do(a) servidor(a) {$nomeServidor} foi informada.",
                            "path" => "(Pessoa FJ > Pessoa física > Editar > Campo: Nacionalidade)",
                            "fail" => true);
      } else {
        if ($servidor["nacionalidade"] == $brasileiro && !$servidor['uf_inep']) {
          $mensagem[] = array("text" => "Dados para formular o registro 30 da escola {$nomeEscola} não encontrados. Verificamos que a nacionalidade do(a) servidor(a) {$nomeServidor} é brasileiro(a), portanto é necessário preencher o código da UF de nascimento conforme a 'Tabela de UF'.",
                              "path" => "(Endereçamento > Estado > Editar > Campo: Código INEP)",
                              "fail" => true);
        }
        if ($servidor["nacionalidade"] == $brasileiro && !$servidor['municipio_inep']) {
          $mensagem[] = array("text" => "Dados para formular o registro 30 da escola {$nomeEscola} não encontrados. Verificamos que a nacionalidade do(a) servidor(a) {$nomeServidor} é brasileiro(a), portanto é necessário preencher o código do município de nascimento conforme a 'Tabela de Municípios'.",
                              "path" => "(Endereçamento > Município > Editar > Campo: Código INEP)",
                              "fail" => true);
        }
      }
    }
    return array('mensagens' => $mensagem,
                 'title'     => "Análise exportação - Registro 30");
  }

  protected function analisaEducacensoRegistro40() {

    $escola = $this->getRequest()->escola;
    $ano    = $this->getRequest()->ano;

    $sql = "SELECT juridica.fantasia AS nome_escola,
                   fisica.nacionalidade AS nacionalidade,
                   uf.cod_ibge AS uf_inep,
                   municipio.cod_ibge AS municipio_inep,
                   pessoa.nome AS nome_servidor,
                   fisica.cpf AS cpf,
                   endereco_pessoa.cep AS cep
             FROM modules.professor_turma
            INNER JOIN pmieducar.turma ON (turma.cod_turma = professor_turma.turma_id)
            INNER JOIN pmieducar.escola ON (escola.cod_escola = turma.ref_ref_cod_escola)
            INNER JOIN pmieducar.servidor ON (servidor.cod_servidor = professor_turma.servidor_id)
            INNER JOIN cadastro.juridica ON (juridica.idpes = escola.ref_idpes)
            INNER JOIN cadastro.pessoa ON (pessoa.idpes = professor_turma.servidor_id)
            INNER JOIN cadastro.fisica ON (fisica.idpes = professor_turma.servidor_id)
             LEFT JOIN cadastro.endereco_pessoa ON (endereco_pessoa.idpes = professor_turma.servidor_id)
             LEFT JOIN public.logradouro ON (logradouro.idlog = endereco_pessoa.idlog)
             LEFT JOIN public.municipio ON (municipio.idmun = logradouro.idmun)
             LEFT JOIN public.uf ON (uf.sigla_uf = municipio.sigla_uf)
            WHERE professor_turma.ano = $1
              AND turma.ativo = 1
              AND turma.visivel = TRUE
              AND COALESCE(turma.nao_informar_educacenso, 0) = 0
              AND turma.ano = professor_turma.ano
              AND escola.cod_escola = $2
              AND servidor.ativo = 1
            GROUP BY professor_turma.servidor_id,
                     juridica.fantasia,
                     fisica.nacionalidade,
                     uf.cod_ibge,
                     municipio.cod_ibge,
                     pessoa.nome,
                     fisica.cpf,
                     endereco_pessoa.cep
            ORDER BY pessoa.nome";

    $servidores = $this->fetchPreparedQuery($sql, array($ano, $escola));

    if(empty($servidores)){
      $this->messenger->append("Nenhum servidor encontrado.");
      return array('title' => "Análise exportação - Registro 40");
    }

    $mensagem = array();

    foreach ($servidores as $servidor) {
      $nomeEscola   = Portabilis_String_Utils::toUtf8(strtoupper($servidor["nome_escola"]));
      $nomeServidor = Portabilis_String_Utils::toUtf8(strtoupper($servidor["nome_servidor"]));
      $naturalidadeBrasileiro = ($servidor["nacionalidade"] == 1 || $servidor["nacionalidade"] == 2);

      if ($naturalidadeBrasileiro && !$servidor['cpf']) {
        $mensagem[] = array("text" => "Dados para formular o registro 40 da escola {$nomeEscola} não encontrados. Verificamos que a nacionalidade do(a) servidor(a) {$nomeServidor} é brasileiro(a)/naturalizado brasileiro(a), portanto é necessário informar seu CPF.",
                            "path" => "(Pessoa FJ > Pessoa física > Editar > Campo: CPF)",
                            "fail" => true);
      }
      if ($servidor["cep"] && !$servidor['uf_inep']) {
        $mensagem[] = array("text" => "Dados para formular o registro 40 da escola {$nomeEscola} não encontrados. Verificamos que no cadastro do(a) servidor(a) {$nomeServidor} o endereçamento foi informado, portanto é necessário cadastrar código da UF informada conforme a 'Tabela de UF'.",
                            "path" => "(Endereçamento > Estado > Editar > Campo: Código INEP)",
                            "fail" => true);
      }
      if ($servidor["cep"] && !$servidor['municipio_inep']) {
        $mensagem[] = array("text" => "Dados para formular o registro 40 da escola {$nomeEscola} não encontrados. Verificamos que no cadastro do(a) servidor(a) {$nomeServidor} o endereçamento foi informado, portanto é necessário cadastrar código do município informado conforme a 'Tabela de Municípios'.",
                            "path" => "(Endereçamento > Município > Editar > Campo: Código INEP)",
                            "fail" => true);
      }
    }

    return array('mensagens' => $mensagem,
                 'title'     => "Análise exportação - Registro 40");
  }

  protected function analisaEducacensoRegistro50() {

    $escola = $this->getRequest()->escola;
    $ano    = $this->getRequest()->ano;

    $sql = "SELECT juridica.fantasia AS nome_escola,
                   pessoa.nome AS nome_servidor,
                   escolaridade.escolaridade AS escolaridade,
                   escolaridade.descricao AS descricao_escolaridade,
                   servidor.ref_idesco AS codigo_escolaridade,
                   servidor.situacao_curso_superior_1 AS situacao_curso_superior_1,
                   servidor.codigo_curso_superior_1 AS codigo_curso_superior_1,
                   servidor.ano_inicio_curso_superior_1 AS ano_inicio_curso_superior_1,
                   servidor.ano_conclusao_curso_superior_1 AS ano_conclusao_curso_superior_1,
                   servidor.instituicao_curso_superior_1 AS instituicao_curso_superior_1,
                   servidor.situacao_curso_superior_2 AS situacao_curso_superior_2,
                   servidor.codigo_curso_superior_2 AS codigo_curso_superior_2,
                   servidor.ano_inicio_curso_superior_2 AS ano_inicio_curso_superior_2,
                   servidor.ano_conclusao_curso_superior_2 AS ano_conclusao_curso_superior_2,
                   servidor.instituicao_curso_superior_2 AS instituicao_curso_superior_2,
                   servidor.situacao_curso_superior_3 AS situacao_curso_superior_3,
                   servidor.codigo_curso_superior_3 AS codigo_curso_superior_3,
                   servidor.ano_inicio_curso_superior_3 AS ano_inicio_curso_superior_3,
                   servidor.ano_conclusao_curso_superior_3 AS ano_conclusao_curso_superior_3,
                   servidor.instituicao_curso_superior_3 AS instituicao_curso_superior_3,
                   servidor.pos_especializacao AS pos_especializacao,
                   servidor.pos_mestrado AS pos_mestrado,
                   servidor.pos_doutorado AS pos_doutorado,
                   servidor.pos_nenhuma AS pos_nenhuma,
                   servidor.curso_creche AS curso_creche,
                   servidor.curso_pre_escola AS curso_pre_escola,
                   servidor.curso_anos_iniciais AS curso_anos_iniciais,
                   servidor.curso_anos_finais AS curso_anos_finais,
                   servidor.curso_ensino_medio AS curso_ensino_medio,
                   servidor.curso_eja AS curso_eja,
                   servidor.curso_educacao_especial AS curso_educacao_especial,
                   servidor.curso_educacao_indigena AS curso_educacao_indigena,
                   servidor.curso_educacao_campo AS curso_educacao_campo,
                   servidor.curso_educacao_ambiental AS curso_educacao_ambiental,
                   servidor.curso_educacao_direitos_humanos AS curso_educacao_direitos_humanos,
                   servidor.curso_genero_diversidade_sexual AS curso_genero_diversidade_sexual,
                   servidor.curso_direito_crianca_adolescente AS curso_direito_crianca_adolescente,
                   servidor.curso_relacoes_etnicorraciais AS curso_relacoes_etnicorraciais,
                   servidor.curso_outros AS curso_outros,
                   servidor.curso_nenhum AS curso_nenhum
              FROM modules.professor_turma
             INNER JOIN pmieducar.servidor ON (servidor.cod_servidor = professor_turma.servidor_id)
             INNER JOIN pmieducar.turma ON (turma.cod_turma = professor_turma.turma_id)
             INNER JOIN pmieducar.escola ON (escola.cod_escola = turma.ref_ref_cod_escola)
             INNER JOIN cadastro.juridica ON (juridica.idpes = escola.ref_idpes)
              LEFT JOIN cadastro.escolaridade ON (escolaridade.idesco = servidor.ref_idesco)
             INNER JOIN cadastro.pessoa ON (pessoa.idpes = professor_turma.servidor_id)
             WHERE professor_turma.ano = $1
               AND turma.ativo = 1
               AND turma.visivel = TRUE
               AND COALESCE(turma.nao_informar_educacenso, 0) = 0
               AND turma.ano = professor_turma.ano
               AND escola.cod_escola = $2
               AND servidor.ativo = 1
             GROUP BY professor_turma.servidor_id,
                      juridica.fantasia,
                      pessoa.nome,
                      servidor.ref_idesco,
                      servidor.situacao_curso_superior_1,
                      servidor.codigo_curso_superior_1 ,
                      servidor.ano_inicio_curso_superior_1,
                      servidor.ano_conclusao_curso_superior_1,
                      servidor.instituicao_curso_superior_1,
                      servidor.situacao_curso_superior_2,
                      servidor.codigo_curso_superior_2,
                      servidor.ano_inicio_curso_superior_2,
                      servidor.ano_conclusao_curso_superior_2,
                      servidor.instituicao_curso_superior_2,
                      servidor.situacao_curso_superior_3,
                      servidor.codigo_curso_superior_3,
                      servidor.ano_inicio_curso_superior_3,
                      servidor.ano_conclusao_curso_superior_3,
                      servidor.instituicao_curso_superior_3,
                      servidor.pos_especializacao,
                      servidor.pos_mestrado,
                      servidor.pos_doutorado,
                      servidor.pos_nenhuma,
                      servidor.curso_creche,
                      servidor.curso_pre_escola,
                      servidor.curso_anos_iniciais,
                      servidor.curso_anos_finais,
                      servidor.curso_ensino_medio,
                      servidor.curso_eja,
                      servidor.curso_educacao_especial,
                      servidor.curso_educacao_indigena,
                      servidor.curso_educacao_campo,
                      servidor.curso_educacao_ambiental,
                      servidor.curso_educacao_direitos_humanos,
                      servidor.curso_genero_diversidade_sexual,
                      servidor.curso_direito_crianca_adolescente,
                      servidor.curso_relacoes_etnicorraciais,
                      servidor.curso_outros,
                      servidor.curso_nenhum,
                      escolaridade.escolaridade,
                      escolaridade.descricao
             ORDER BY pessoa.nome";

    $servidores = $this->fetchPreparedQuery($sql, array($ano, $escola));

    if(empty($servidores)){
      $this->messenger->append("Nenhum servidor encontrado.");
      return array('title' => "Análise exportação - Registro 50");
    }

    $mensagem = array();
    $superiorCompleto  = 6;
    $situacaoConcluido = 1;
    $situacaoCursando  = 2;

    foreach ($servidores as $servidor) {
      $nomeEscola   = Portabilis_String_Utils::toUtf8(strtoupper($servidor["nome_escola"]));
      $nomeServidor = Portabilis_String_Utils::toUtf8(strtoupper($servidor["nome_servidor"]));

      $existeCursoConcluido = ($servidor["situacao_curso_superior_1"] == $situacaoConcluido ||
                               $servidor["situacao_curso_superior_2"] == $situacaoConcluido ||
                               $servidor["situacao_curso_superior_3"] == $situacaoConcluido);
      $existePosGraduacao = ($servidor["pos_especializacao"] || $servidor["pos_mestrado"] ||
                             $servidor["pos_doutorado"] || $servidor["pos_nenhuma"]);

      $existeCursoFormacaoContinuada = ($servidor["curso_creche"] ||
                                        $servidor["curso_pre_escola"] ||
                                        $servidor["curso_anos_iniciais"] ||
                                        $servidor["curso_anos_finais"] ||
                                        $servidor["curso_ensino_medio"] ||
                                        $servidor["curso_eja"] ||
                                        $servidor["curso_educacao_especial"] ||
                                        $servidor["curso_educacao_indigena"] ||
                                        $servidor["curso_educacao_campo"] ||
                                        $servidor["curso_educacao_ambiental"] ||
                                        $servidor["curso_educacao_direitos_humanos"] ||
                                        $servidor["curso_genero_diversidade_sexual"] ||
                                        $servidor["curso_direito_crianca_adolescente"] ||
                                        $servidor["curso_relacoes_etnicorraciais"] ||
                                        $servidor["curso_outros"] ||
                                        $servidor["curso_nenhum"]);

      if (!$servidor["codigo_escolaridade"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 50 da escola {$nomeEscola} não encontrados. Verifique se a escolaridade do(a) servidor(a) {$nomeServidor} foi informada.",
                            "path" => "(Servidores > Cadastrar > Editar > Aba: Dados adicionais > Campo: Escolaridade)",
                            "fail" => true);
      }
      if ($servidor["codigo_escolaridade"] && !$servidor["escolaridade"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 50 da escola {$nomeEscola} não encontrados. Verifique se o campo escolaridade educacenso foi informado para a escolaridade {$servidor['descricao_escolaridade']}.",
                            "path" => "(Servidores > Escolaridade > Editar > Campo: Escolaridade Educacenso)",
                            "fail" => true);
      }
      if ($servidor["escolaridade"] == $superiorCompleto && !$servidor["situacao_curso_superior_1"]) {
          $mensagem[] = array("text" => "Dados para formular o registro 50 da escola {$nomeEscola} não encontrados. Verificamos que a escolaridade do(a) servidor(a) {$nomeServidor} é superior, portanto é necessário informar a situação do curso superior 1.",
                              "path" => "(Servidores > Cadastrar > Editar > Aba: Dados adicionais > Campo: Situação do curso superior 1)",
                              "fail" => true);
      }
      if ($servidor["situacao_curso_superior_1"] && !$servidor["codigo_curso_superior_1"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 50 da escola {$nomeEscola} não encontrados. Verificamos que a escolaridade do(a) servidor(a) {$nomeServidor} é superior, portanto é necessário informar o nome do curso superior 1.",
                            "path" => "(Servidores > Cadastrar > Editar > Aba: Dados adicionais > Campo: Curso superior 1)",
                            "fail" => true);
      }
      if ($servidor["situacao_curso_superior_1"] == $situacaoCursando && !$servidor["ano_inicio_curso_superior_1"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 50 da escola {$nomeEscola} não encontrados. Verificamos que o(a) servidor(a) {$nomeServidor} está cursando um curso superior, portanto é necessário informar o ano de início deste respectivo curso.",
                            "path" => "(Servidores > Cadastrar > Editar > Aba: Dados adicionais > Campo: Ano de início do curso superior 1)",
                            "fail" => true);
      }
      if ($servidor["situacao_curso_superior_1"] == $situacaoConcluido && !$servidor["ano_conclusao_curso_superior_1"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 50 da escola {$nomeEscola} não encontrados. Verificamos que o(a) servidor(a) {$nomeServidor} concluiu um curso superior, portanto é necessário informar o ano de conclusão deste respectivo curso.",
                            "path" => "(Servidores > Cadastrar > Editar > Aba: Dados adicionais > Campo: Ano de conclusão do curso superior 1)",
                            "fail" => true);
      }
      if ($servidor["situacao_curso_superior_1"] && !$servidor["instituicao_curso_superior_1"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 50 da escola {$nomeEscola} não encontrados. Verificamos que a escolaridade do(a) servidor(a) {$nomeServidor} é superior, portanto é necessário informar o nome da instituição do curso superior 1.",
                            "path" => "(Servidores > Cadastrar > Editar > Aba: Dados adicionais > Campo: Instituição do curso superior 1)",
                            "fail" => true);
      }
      if ($servidor["situacao_curso_superior_2"] && !$servidor["codigo_curso_superior_2"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 50 da escola {$nomeEscola} não encontrados. Verificamos que a situação do curso superior 2 do(a) servidor(a) {$nomeServidor} foi informada, portanto é necessário informar o nome do curso superior 2.",
                            "path" => "(Servidores > Cadastrar > Editar > Aba: Dados adicionais > Campo: Curso superior 2)",
                            "fail" => true);
      }
      if ($servidor["situacao_curso_superior_2"] == $situacaoCursando && !$servidor["ano_inicio_curso_superior_2"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 50 da escola {$nomeEscola} não encontrados. Verificamos que o(a) servidor(a) {$nomeServidor} está cursando um curso superior 2, portanto é necessário informar o ano de início deste respectivo curso.",
                            "path" => "(Servidores > Cadastrar > Editar > Aba: Dados adicionais > Campo: Ano de início do curso superior 2)",
                            "fail" => true);
      }
      if ($servidor["situacao_curso_superior_2"] == $situacaoConcluido && !$servidor["ano_conclusao_curso_superior_2"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 50 da escola {$nomeEscola} não encontrados. Verificamos que o(a) servidor(a) {$nomeServidor} concluiu um curso superior 2, portanto é necessário informar o ano de conclusão deste respectivo curso.",
                            "path" => "(Servidores > Cadastrar > Editar > Aba: Dados adicionais > Campo: Ano de conclusão do curso superior 2)",
                            "fail" => true);
      }
      if ($servidor["situacao_curso_superior_2"] && !$servidor["instituicao_curso_superior_2"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 50 da escola {$nomeEscola} não encontrados. Verificamos que a situação do curso superior 2 do(a) servidor(a) {$nomeServidor} foi informada, portanto é necessário informar também o nome da instituição deste respectivo curso. ",
                            "path" => "(Servidores > Cadastrar > Editar > Aba: Dados adicionais > Campo: Instituição do curso superior 2)",
                            "fail" => true);
      }
      if ($servidor["situacao_curso_superior_3"] && !$servidor["codigo_curso_superior_3"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 50 da escola {$nomeEscola} não encontrados. Verificamos que a situação do curso superior 3 do(a) servidor(a) {$nomeServidor} foi informada, portanto é necessário informar o nome do curso superior 3.",
                            "path" => "(Servidores > Cadastrar > Editar > Aba: Dados adicionais > Campo: Curso superior 3)",
                            "fail" => true);
      }
      if ($servidor["situacao_curso_superior_3"] == $situacaoCursando && !$servidor["ano_inicio_curso_superior_3"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 50 da escola {$nomeEscola} não encontrados. Verificamos que o(a) servidor(a) {$nomeServidor} está cursando um curso superior 3, portanto é necessário informar o ano de início deste respectivo curso.",
                            "path" => "(Servidores > Cadastrar > Editar > Aba: Dados adicionais > Campo: Ano de início do curso superior 3)",
                            "fail" => true);
      }
      if ($servidor["situacao_curso_superior_3"] == $situacaoConcluido && !$servidor["ano_conclusao_curso_superior_3"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 50 da escola {$nomeEscola} não encontrados. Verificamos que o(a) servidor(a) {$nomeServidor} concluiu um curso superior 3, portanto é necessário informar o ano de conclusão deste respectivo curso.",
                            "path" => "(Servidores > Cadastrar > Editar > Aba: Dados adicionais > Campo: Ano de conclusão do curso superior 3)",
                            "fail" => true);
      }
      if ($servidor["situacao_curso_superior_3"] && !$servidor["instituicao_curso_superior_3"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 50 da escola {$nomeEscola} não encontrados. Verificamos que a situação do curso superior 3 do(a) servidor(a) {$nomeServidor} foi informada, portanto é necessário informar também o nome da instituição deste respectivo curso. ",
                            "path" => "(Servidores > Cadastrar > Editar > Aba: Dados adicionais > Campo: Instituição do curso superior 3)",
                            "fail" => true);
      }
      if ($existeCursoConcluido && !$existePosGraduacao) {
        $mensagem[] = array("text" => "Dados para formular o registro 50 da escola {$nomeEscola} não encontrados. Verifique se alguma das opções de Pós-Graduação foi informada para o(a) servidor(a) {$nomeServidor}.",
                            "path" => "(Servidores > Cadastrar > Editar > Aba: Dados adicionais > Campos: Pós-Graduação)",
                            "fail" => true);
      }
      if (!$existeCursoFormacaoContinuada) {
        $mensagem[] = array("text" => "Dados para formular o registro 50 da escola {$nomeEscola} não encontrados. Verifique se alguma das opções de Curso de Formação Continuada foi informada para o(a) servidor(a) {$nomeServidor}.",
                            "path" => "(Servidores > Cadastrar > Editar > Aba: Dados adicionais > Campos: Curso de Formação Continuada)",
                            "fail" => true);
      }
    }

    return array('mensagens' => $mensagem,
                 'title'     => "Análise exportação - Registro 50");
  }

  protected function analisaEducacensoRegistro51() {

    $escola = $this->getRequest()->escola;
    $ano    = $this->getRequest()->ano;

    $sql = "SELECT juridica.fantasia AS nome_escola,
                   pessoa.nome AS nome_servidor,
                   professor_turma.tipo_vinculo AS tipo_vinculo,
                   professor_turma.funcao_exercida AS funcao_exercida
              FROM modules.professor_turma
             INNER JOIN pmieducar.turma ON (turma.cod_turma = professor_turma.turma_id)
             INNER JOIN pmieducar.escola ON (escola.cod_escola = turma.ref_ref_cod_escola)
             INNER JOIN pmieducar.servidor ON (servidor.cod_servidor = professor_turma.servidor_id)
              LEFT JOIN cadastro.fisica_raca ON (fisica_raca.ref_idpes = professor_turma.servidor_id)
             INNER JOIN cadastro.pessoa ON (pessoa.idpes = professor_turma.servidor_id)
             INNER JOIN cadastro.fisica ON (fisica.idpes = professor_turma.servidor_id)
             INNER JOIN cadastro.juridica ON (juridica.idpes = escola.ref_idpes)
             WHERE professor_turma.ano = $1
               AND turma.ativo = 1
               AND turma.visivel = TRUE
               AND COALESCE(turma.nao_informar_educacenso, 0) = 0
               AND turma.ano = professor_turma.ano
               AND escola.cod_escola = $2
               AND servidor.ativo = 1
             GROUP BY professor_turma.servidor_id,
                      juridica.fantasia,
                      pessoa.nome,
                      professor_turma.tipo_vinculo,
                      professor_turma.funcao_exercida
             ORDER BY pessoa.nome";

    $servidores = $this->fetchPreparedQuery($sql, array($ano, $escola));

    if(empty($servidores)){
      $this->messenger->append("Nenhum servidor encontrado.");
      return array('title' => "Análise exportação - Registro 51");
    }

    $mensagem = array();
    $docente = array(1,5,6);

    foreach ($servidores as $servidor) {
      $nomeEscola   = Portabilis_String_Utils::toUtf8(strtoupper($servidor["nome_escola"]));
      $nomeServidor = Portabilis_String_Utils::toUtf8(strtoupper($servidor["nome_servidor"]));

      $funcaoDocente = in_array($servidor["funcao_exercida"], $docente);

      if ($funcaoDocente && !$servidor["tipo_vinculo"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 51 da escola {$nomeEscola} não encontrados. Verificamos que o(a) servidor(a) {$nomeServidor} é docente e possui vínculo com turmas, portanto é necessário informar qual o seu tipo de vínculo.",
                            "path" => "(Servidores > Cadastrar > Vincular professor a turmas > Campo: Tipo do vínculo)",
                            "fail" => true);
      }
    }

    return array('mensagens' => $mensagem,
                 'title'     => "Análise exportação - Registro 51");
  }

  protected function analisaEducacensoRegistro60() {

    $escola   = $this->getRequest()->escola;
    $ano      = $this->getRequest()->ano;
    $data_ini = $this->getRequest()->data_ini;
    $data_fim = $this->getRequest()->data_fim;

    $sql = "SELECT juridica.fantasia AS nome_escola,
                   pessoa.nome AS nome_aluno,
                   raca.raca_educacenso AS cor_raca,
                   fisica.nacionalidade AS nacionalidade,
                   uf.cod_ibge AS uf_inep,
                   municipio.cod_ibge AS municipio_inep
              FROM pmieducar.aluno
             INNER JOIN pmieducar.matricula ON (matricula.ref_cod_aluno = aluno.cod_aluno)
             INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_matricula = matricula.cod_matricula)
             INNER JOIN pmieducar.turma ON (turma.cod_turma = matricula_turma.ref_cod_turma)
             INNER JOIN pmieducar.escola ON (escola.cod_escola = matricula.ref_ref_cod_escola)
             INNER JOIN cadastro.juridica ON (juridica.idpes = escola.ref_idpes)
             INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
             INNER JOIN cadastro.fisica ON (fisica.idpes = pessoa.idpes)
              LEFT JOIN cadastro.fisica_raca ON (fisica_raca.ref_idpes = fisica.idpes)
              LEFT JOIN cadastro.raca ON (raca.cod_raca = fisica_raca.ref_cod_raca)
              LEFT JOIN cadastro.endereco_pessoa ON (endereco_pessoa.idpes = fisica.idpes)
              LEFT JOIN public.municipio ON (municipio.idmun = fisica.idmun_nascimento)
              LEFT JOIN public.uf ON (uf.sigla_uf = municipio.sigla_uf)
             WHERE aluno.ativo = 1
               AND turma.ativo = 1
               AND turma.visivel = TRUE
               AND COALESCE(turma.nao_informar_educacenso, 0) = 0
               AND matricula.ativo = 1
               AND matricula_turma.ativo = 1
               AND matricula.ano = $1
               AND escola.cod_escola = $2
               AND COALESCE(matricula.data_matricula,matricula.data_cadastro) BETWEEN DATE($3) AND DATE($4)
               AND (matricula.aprovado = 3 OR DATE(COALESCE(matricula.data_cancel,matricula.data_exclusao)) > DATE($4))
             ORDER BY nome_aluno";

    $alunos = $this->fetchPreparedQuery($sql, array($ano,
                                                    $escola,
                                                    Portabilis_Date_Utils::brToPgSQL($data_ini),
                                                    Portabilis_Date_Utils::brToPgSQL($data_fim)));

    if(empty($alunos)){
      $this->messenger->append("Nenhum aluno encontrado.");
      return array('title' => "Análise exportação - Registro 60");
    }

    $mensagem = array();
    $brasileiro = 1;

    foreach ($alunos as $aluno) {
      $nomeEscola = Portabilis_String_Utils::toUtf8(strtoupper($aluno["nome_escola"]));
      $nomeAluno  = Portabilis_String_Utils::toUtf8(strtoupper($aluno["nome_aluno"]));

      if (is_null($aluno["cor_raca"])) {
        $mensagem[] = array("text" => "Dados para formular o registro 60 da escola {$nomeEscola} não encontrados. Verifique se a raça do(a) aluno(a) {$nomeAluno} foi informada.",
                            "path" => "(Pessoa FJ > Pessoa física > Editar > Campo: Raça)",
                            "fail" => true);
      }
      if (!$aluno["nacionalidade"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 30 da escola {$nomeEscola} não encontrados. Verifique se a nacionalidade do(a) aluno(a) {$nomeAluno} foi informada.",
                            "path" => "(Pessoa FJ > Pessoa física > Editar > Campo: Nacionalidade)",
                            "fail" => true);
      } else {
        if ($aluno["nacionalidade"] == $brasileiro && !$aluno['uf_inep']) {
          $mensagem[] = array("text" => "Dados para formular o registro 30 da escola {$nomeEscola} não encontrados. Verificamos que a nacionalidade do(a) aluno(a) {$nomeAluno} é brasileiro(a), portanto é necessário preencher o código da UF de nascimento conforme a 'Tabela de UF'.",
                              "path" => "(Endereçamento > Estado > Editar > Campo: Código INEP)",
                              "fail" => true);
        }
        if ($aluno["nacionalidade"] == $brasileiro && !$aluno['municipio_inep']) {
          $mensagem[] = array("text" => "Dados para formular o registro 30 da escola {$nomeEscola} não encontrados. Verificamos que a nacionalidade do(a) aluno(a) {$nomeAluno} é brasileiro(a), portanto é necessário preencher o código do município de nascimento conforme a 'Tabela de Municípios'.",
                              "path" => "(Endereçamento > Município > Editar > Campo: Código INEP)",
                              "fail" => true);
        }
      }
    }

    return array('mensagens' => $mensagem,
                 'title'     => "Análise exportação - Registro 60");
  }

  protected function analisaEducacensoRegistro70() {

    $escola   = $this->getRequest()->escola;
    $ano      = $this->getRequest()->ano;
    $data_ini = $this->getRequest()->data_ini;
    $data_fim = $this->getRequest()->data_fim;

    $sql = "SELECT juridica.fantasia AS nome_escola,
                   pessoa.nome AS nome_aluno,
                   documento.rg AS rg,
                   documento.sigla_uf_exp_rg AS sigla_uf_rg,
                   documento.idorg_exp_rg AS orgao_emissor_rg,
                   documento.tipo_cert_civil AS tipo_cert_civil,
                   documento.num_termo AS num_termo,
                   documento.sigla_uf_cert_civil AS uf_cartorio,
                   documento.cartorio_cert_civil_inep AS cod_cartorio,
                   uf.cod_ibge AS uf_inep,
                   municipio.cod_ibge AS municipio_inep,
                   uf_cartorio.cod_ibge AS uf_inep_cartorio,
                   uf_rg.cod_ibge AS uf_inep_rg,
                   fisica.nacionalidade AS nacionalidade,
                   endereco_pessoa.cep AS cep,
                   fisica.zona_localizacao_censo AS zona_localizacao
              FROM pmieducar.aluno
             INNER JOIN pmieducar.matricula ON (matricula.ref_cod_aluno = aluno.cod_aluno)
             INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_matricula = matricula.cod_matricula)
             INNER JOIN pmieducar.turma ON (turma.cod_turma = matricula_turma.ref_cod_turma)
             INNER JOIN pmieducar.escola ON (escola.cod_escola = matricula.ref_ref_cod_escola)
             INNER JOIN cadastro.juridica ON (juridica.idpes = escola.ref_idpes)
             INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
             INNER JOIN cadastro.fisica ON (fisica.idpes = pessoa.idpes)
              LEFT JOIN cadastro.documento ON (documento.idpes = pessoa.idpes)
              LEFT JOIN cadastro.endereco_pessoa ON (endereco_pessoa.idpes = pessoa.idpes)
              LEFT JOIN public.logradouro ON (logradouro.idlog = endereco_pessoa.idlog)
              LEFT JOIN public.municipio ON (municipio.idmun = logradouro.idmun)
              LEFT JOIN public.uf ON (uf.sigla_uf = municipio.sigla_uf)
              LEFT JOIN public.uf uf_cartorio ON (uf_cartorio.sigla_uf = documento.sigla_uf_cert_civil)
              LEFT JOIN public.uf uf_rg ON (uf_rg.sigla_uf = documento.sigla_uf_exp_rg)
              LEFT JOIN public.bairro ON (bairro.idbai = endereco_pessoa.idbai)
             WHERE aluno.ativo = 1
               AND turma.ativo = 1
               AND turma.visivel = TRUE
               AND COALESCE(turma.nao_informar_educacenso, 0) = 0
               AND matricula.ativo = 1
               AND matricula_turma.ativo = 1
               AND matricula.ano = $1
               AND escola.cod_escola = $2
               AND COALESCE(matricula.data_matricula,matricula.data_cadastro) BETWEEN DATE($3) AND DATE($4)
               AND (matricula.aprovado = 3 OR DATE(COALESCE(matricula.data_cancel,matricula.data_exclusao)) > DATE($4))
             ORDER BY nome_aluno";

    $alunos = $this->fetchPreparedQuery($sql, array($ano,
                                                    $escola,
                                                    Portabilis_Date_Utils::brToPgSQL($data_ini),
                                                    Portabilis_Date_Utils::brToPgSQL($data_fim)));

    if(empty($alunos)){
      $this->messenger->append("Nenhum aluno encontrado.");
      return array('title' => "Análise exportação - Registro 70");
    }

    $mensagem = array();
    $nascimentoAntigoFormato = 91;
    $casamentoAntigoFormato  = 92;
    $estrangeiro = 3;

    foreach ($alunos as $aluno) {
      $nomeEscola = Portabilis_String_Utils::toUtf8(strtoupper($aluno["nome_escola"]));
      $nomeAluno  = Portabilis_String_Utils::toUtf8(strtoupper($aluno["nome_aluno"]));

      if ($aluno["rg"]) {
        if (!$aluno["sigla_uf_rg"]) {
          $mensagem[] = array("text" => "Dados para formular o registro 70 da escola {$nomeEscola} não encontrados. Verificamos que o número da identidade do(a) aluno(a) {$nomeAluno} foi informada, portanto é necessário informar também o órgão emissor da identidade.",
                              "path" => "(Pessoa FJ > Pessoa física > Editar > Campo: RG / Data emissão)",
                              "fail" => true);
        }
        if (!$aluno["sigla_uf_rg"]) {
          $mensagem[] = array("text" => "Dados para formular o registro 70 da escola {$nomeEscola} não encontrados. Verificamos que o número da identidade do(a) aluno(a) {$nomeAluno} foi informada, portanto é necessário informar também o estado da identidade.",
                              "path" => "(Pessoa FJ > Pessoa física > Editar > Campo: RG / Data emissão)",
                              "fail" => true);
        }
        if ($aluno["sigla_uf_rg"] && !$aluno["uf_inep_rg"]) {
          $mensagem[] = array("text" => "Dados para formular o registro 70 da escola {$nomeEscola} não encontrados. Verificamos que o estado da identidade do(a) aluno(a) {$nomeAluno} foi informado, portanto é necessário preencher o código deste estado conforme a 'Tabela de UF'.",
                              "path" => "(Endereçamento > Estado > Editar > Campo: Código INEP)",
                              "fail" => true);
        }
      }
      $certidaoAntigoFormato = ($aluno["tipo_cert_civil"] == $nascimentoAntigoFormato || $aluno["tipo_cert_civil"] == $casamentoAntigoFormato);
      if ($certidaoAntigoFormato && $aluno["nacionalidade"] != $estrangeiro) {
        if (!$aluno["num_termo"]) {
          $mensagem[] = array("text" => "Dados para formular o registro 70 da escola {$nomeEscola} não encontrados. Verificamos que o tipo da certidão civil do(a) aluno(a) {$nomeAluno} foi informada, portanto é necessário informar também o número do termo da certidão.",
                              "path" => "(Pessoa FJ > Pessoa física > Editar > Campo: Termo)",
                              "fail" => true);
        }
        if (!$aluno["uf_cartorio"]) {
          $mensagem[] = array("text" => "Dados para formular o registro 70 da escola {$nomeEscola} não encontrados. Verificamos que o número do termo da certidão civil do(a) aluno(a) {$nomeAluno} foi informado, portanto é necessário informar também o estado de emissão.",
                              "path" => "(Pessoa FJ > Pessoa física > Editar > Campo: Estado emissão / Data emissão)",
                              "fail" => true);
        }
        if ($aluno["uf_cartorio"] && !$aluno["uf_inep_cartorio"]) {
          $mensagem[] = array("text" => "Dados para formular o registro 70 da escola {$nomeEscola} não encontrados. Verificamos que o estado do cartório do(a) aluno(a) {$nomeAluno} foi informado, portanto é necessário preencher o código deste estado conforme a 'Tabela de UF'.",
                              "path" => "(Endereçamento > Estado > Editar > Campo: Código INEP)",
                              "fail" => true);
        }
        if (!$aluno["cod_cartorio"]) {
          $mensagem[] = array("text" => "Dados para formular o registro 70 da escola {$nomeEscola} não encontrados. Verificamos que o número da certidão civil do(a) aluno(a) {$nomeAluno} foi informada, portanto é necessário informar também o código do cartório conforme a 'Tabela de Cartórios'.",
                              "path" => "(Pessoa FJ > Pessoa física > Editar > Campo: Estado emissão / Data emissão)",
                              "fail" => true);
        }
      }
      if ($aluno["cep"] && !$aluno['uf_inep']) {
        $mensagem[] = array("text" => "Dados para formular o registro 70 da escola {$nomeEscola} não encontrados. Verificamos que no cadastro do(a) aluno(a) {$nomeAluno} o endereçamento foi informado, portanto é necessário cadastrar código da UF informada conforme a 'Tabela de UF'.",
                            "path" => "(Endereçamento > Estado > Editar > Campo: Código INEP)",
                            "fail" => true);
      }
      if ($aluno["cep"] && !$aluno['municipio_inep']) {
        $mensagem[] = array("text" => "Dados para formular o registro 70 da escola {$nomeEscola} não encontrados. Verificamos que no cadastro do(a) aluno(a) {$nomeAluno} o endereçamento foi informado, portanto é necessário cadastrar código do município informado conforme a 'Tabela de Municípios'.",
                            "path" => "(Endereçamento > Município > Editar > Campo: Código INEP)",
                            "fail" => true);
      }
      if (!$aluno["zona_localizacao"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 70 da escola {$nomeEscola} não encontrados. Verifique se a zona/localização do (a) aluno(a) $nomeAluno foi informada.",
                            "path" => "(Pessoas > Cadastros > Pessoas físicas > Campo: Zona Localização)",
                            "fail" => true);}
    }

    return array('mensagens' => $mensagem,
                 'title'     => "Análise exportação - Registro 70");
  }

  protected function analisaEducacensoRegistro80() {

    $escola   = $this->getRequest()->escola;
    $ano      = $this->getRequest()->ano;
    $data_ini = $this->getRequest()->data_ini;
    $data_fim = $this->getRequest()->data_fim;

    $sql = "SELECT juridica.fantasia AS nome_escola,
                   pessoa.nome AS nome_aluno,
                   transporte_aluno.responsavel AS transporte_escolar,
                   aluno.veiculo_transporte_escolar AS veiculo_transporte_escolar,
                   turma.tipo_atendimento AS tipo_atendimento,
                   aluno.recebe_escolarizacao_em_outro_espaco AS recebe_escolarizacao_em_outro_espaco,
                   turma.etapa_educacenso AS etapa_ensino,
                   turma.etapa_educacenso2 AS etapa_turma
              FROM pmieducar.aluno
             INNER JOIN pmieducar.matricula ON (matricula.ref_cod_aluno = aluno.cod_aluno)
             INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_matricula = matricula.cod_matricula)
             INNER JOIN pmieducar.turma ON (turma.cod_turma = matricula_turma.ref_cod_turma)
             INNER JOIN pmieducar.escola ON (escola.cod_escola = matricula.ref_ref_cod_escola)
             INNER JOIN cadastro.juridica ON (juridica.idpes = escola.ref_idpes)
             INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
              LEFT JOIN modules.transporte_aluno ON (transporte_aluno.aluno_id = aluno.cod_aluno)
             WHERE aluno.ativo = 1
               AND turma.ativo = 1
               AND turma.visivel = TRUE
               AND COALESCE(turma.nao_informar_educacenso, 0) = 0
               AND matricula.ativo = 1
               AND matricula.ano = $1
               AND escola.cod_escola = $2
               AND COALESCE(matricula.data_matricula,matricula.data_cadastro) BETWEEN DATE($3) AND DATE($4)
               AND (matricula.aprovado = 3 OR DATE(COALESCE(matricula.data_cancel,matricula.data_exclusao)) > DATE($4))
             ORDER BY nome_aluno";

    $alunos = $this->fetchPreparedQuery($sql, array($ano,
                                                    $escola,
                                                    Portabilis_Date_Utils::brToPgSQL($data_ini),
                                                    Portabilis_Date_Utils::brToPgSQL($data_fim)));

    if(empty($alunos)){
      $this->messenger->append("Nenhum aluno encontrado.");
      return array('title' => "Análise exportação - Registro 80");
    }

    $mensagem = array();
    $transporteEstadual  = 1;
    $transporteMunicipal = 2;
    $atividadeComplementar = 4;
    $atendimentoEducEspecializado = 5;
    $etapasEnsinoCorrecao = array(12,13,22,23,24,72,56,64);

    foreach ($alunos as $aluno) {
      $nomeEscola = Portabilis_String_Utils::toUtf8(strtoupper($aluno["nome_escola"]));
      $nomeAluno  = Portabilis_String_Utils::toUtf8(strtoupper($aluno["nome_aluno"]));

      if (is_null($aluno["transporte_escolar"])) {
        $mensagem[] = array("text" => "Dados para formular o registro 80 da escola {$nomeEscola} não encontrados. Verifique se o transporte púlblico foi informado para o(a) aluno(a) {$nomeAluno}.",
                            "path" => "(Cadastros > Aluno > Alunos > Campo: Transporte público)",
                            "fail" => true);
      }
      if ($aluno["transporte_escolar"] == $transporteMunicipal || $aluno["transporte_escolar"] == $transporteEstadual) {
        if (!$aluno["veiculo_transporte_escolar"]) {
          $mensagem[] = array("text" => "Dados para formular o registro 80 da escola {$nomeEscola} não encontrados. Verificamos que o(a) aluno(a) {$nomeAluno} utiliza o transporte público, portanto é necessário informar qual o tipo de veículo utilizado.",
                              "path" => "(Cadastros > Aluno > Alunos > Campo: Veículo utilizado)",
                              "fail" => true);
        }
      }
      if ($aluno["tipo_atendimento"] != $atividadeComplementar &&
          $aluno["tipo_atendimento"] != $atendimentoEducEspecializado) {
        if (!$aluno["recebe_escolarizacao_em_outro_espaco"]) {
          $mensagem[] = array("text" => "Dados para formular o registro 80 da escola {$nomeEscola} não encontrados. Verificamos que a turma vinculada a este aluno(a) {$nomeAluno} não é de Atividade complementar e nem de AEE, portanto é necessário informar se o mesmo recebe escolarização em um espaço diferente da respectiva escola.",
                              "path" => "(Cadastros > Aluno > Alunos > Aba: Recursos prova INEP > Campo: Recebe escolarização em outro espaço (diferente da escola))",
                              "fail" => true);
        }
      }
      if (in_array($aluno["etapa_ensino"], $etapasEnsinoCorrecao)) {
        if (!$aluno["etapa_turma"]) {
          $mensagem[] = array("text" => "Dados para formular o registro 80 do(a) aluno(a) {$nomeAluno} não encontrados. Verificamos que a etapa da turma vinculada a este aluno(a) não foi informada.",
                              "path" => "(Cadastros > Turma > Cadastrar > Campo: Etapa da turma)",
                              "fail" => true);
        }
      }
    }

    return array('mensagens' => $mensagem,
                 'title'     => "Análise exportação - Registro 80");
  }

  protected function analisaEducacensoRegistro89() {

    $escola   = $this->getRequest()->escola;

    $sql = "SELECT DISTINCT j.fantasia AS nome_escola,
                            ece.cod_escola_inep AS inep,
                            gp.nome AS nome_gestor,
                            gf.cpf AS cpf_gestor,
                            e.cargo_gestor
              FROM pmieducar.escola e
             INNER JOIN cadastro.juridica j ON (j.idpes = e.ref_idpes)
              LEFT JOIN modules.educacenso_cod_escola ece ON (ece.cod_escola = e.cod_escola)
              LEFT JOIN cadastro.fisica gf ON (gf.idpes = e.ref_idpes_gestor)
              LEFT JOIN cadastro.pessoa gp ON (gp.idpes = e.ref_idpes_gestor)
             WHERE e.cod_escola = $1";

    $escolas = $this->fetchPreparedQuery($sql, array($escola));

    $mensagem = array();

    foreach ($escolas as $escola) {
      $nomeEscola = Portabilis_String_Utils::toUtf8(mb_strtoupper($escola["nome_escola"]));

      if (is_null($escola["inep"])){
        $mensagem[] = array("text" => "Dados para formular o registro 89 da escola {$nomeEscola} não encontrados. Verifique se a escola possui o código INEP cadastrado.",
                            "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Dados gerais > Campo: Código INEP)",
                            "fail" => true);
      }

      if ($escola["cpf_gestor"] <= 0){
        $mensagem[] = array("text" => "Dados para formular o registro 89 da escola {$nomeEscola} não encontrados. Verifique se o(a) gestor(a) escolar possui o CPF cadastrado.",
                            "path" => "(Pessoa FJ > Pessoa física > Editar > Campo: CPF)",
                            "fail" => true);
      }

      if (is_null($escola["nome_gestor"])){
        $mensagem[] = array("text" => "Dados para formular o registro 89 da escola {$nomeEscola} não encontrados. Verifique se o(a) gestor(a) escolar foi informado(a).",
                            "path" => "(Cadastros > Escola > Cadastrar > Editar > Aba: Dados gerais > Campo: Gestor escolar)",
                            "fail" => true);
      }

      if (is_null($escola["cargo_gestor"])){
        $mensagem[] = array("text" => "Dados para formular o registro 89 da escola {$nomeEscola} não encontrados. Verifique se o cargo do(a) gestor(a) escolar foi informado.",
                            "path" => "(Cadastros > Escola > Cadastrar > Editar > Campo: Cargo do gestor escolar)",
                            "fail" => true);
      }
    }

    return array('mensagens' => $mensagem,
                 'title'     => "Análise exportação - Registro 89");
  }

  protected function analisaEducacensoRegistro90() {

    $escola   = $this->getRequest()->escola;
    $ano      = $this->getRequest()->ano;
    $data_ini = $this->getRequest()->data_ini;
    $data_fim = $this->getRequest()->data_fim;

    $sql = "SELECT DISTINCT j.fantasia AS nome_escola,
                            t.nm_turma AS nome_turma,
                            ect.cod_turma_inep AS inep_turma,
                            p.nome AS nome_aluno,
                            eca.cod_aluno_inep AS inep_aluno
              FROM pmieducar.escola e
             INNER JOIN pmieducar.turma t ON (t.ref_ref_cod_escola = e.cod_escola)
             INNER JOIN pmieducar.matricula_turma mt ON (mt.ref_cod_turma = t.cod_turma)
             INNER JOIN pmieducar.matricula m ON (m.cod_matricula = mt.ref_cod_matricula)
             INNER JOIN pmieducar.aluno a ON (a.cod_aluno = m.ref_cod_aluno)
             INNER JOIN cadastro.pessoa p ON (p.idpes = a.ref_idpes)
             INNER JOIN cadastro.juridica j ON (j.idpes = e.ref_idpes)
              LEFT JOIN modules.educacenso_cod_aluno eca ON (eca.cod_aluno = a.cod_aluno)
              LEFT JOIN modules.educacenso_cod_turma ect ON (ect.cod_turma = t.cod_turma)
             WHERE e.cod_escola = $1
               AND COALESCE(m.data_matricula,m.data_cadastro) BETWEEN DATE($3) AND DATE($4)
               AND m.aprovado IN (1, 2, 3, 4, 6, 15)
               AND m.ano = $2
             ORDER BY nome_turma";

    $alunos = $this->fetchPreparedQuery($sql, array($escola,
                                                    $ano,
                                                    Portabilis_Date_Utils::brToPgSQL($data_ini),
                                                    Portabilis_Date_Utils::brToPgSQL($data_fim)));

    $mensagem = array();
    $ultimaTurmaVerificada;

    foreach ($alunos as $aluno) {
      $nomeEscola = Portabilis_String_Utils::toUtf8(mb_strtoupper($aluno["nome_escola"]));
      $nomeTurma = Portabilis_String_Utils::toUtf8(mb_strtoupper($aluno["nome_turma"]));
      $nomeAluno  = Portabilis_String_Utils::toUtf8(mb_strtoupper($aluno["nome_aluno"]));

      if (is_null($aluno["inep_turma"]) && $ultimaTurmaVerificada != $aluno["nome_turma"]) {
        $mensagem[] = array("text" => "Dados para formular o registro 90 da escola {$nomeEscola} não encontrados. Verifique se a turma {$nomeTurma} possui o código INEP cadastrado.",
                            "path" => "(Cadastros > Turma > Cadastrar > Editar > Aba: Dados adicionais > Campo: Código INEP)",
                            "fail" => true);
        $ultimaTurmaVerificada = $aluno["nome_turma"];
      }

      if (is_null($aluno["inep_aluno"])) {
        $mensagem[] = array("text" => "Dados para formular o registro 90 da escola {$nomeEscola} não encontrados. Verifique se o(a) aluno(a) {$nomeAluno} possui o código INEP cadastrado.",
                            "path" => "(Cadastros > Aluno > Alunos > Editar > Campo: Código INEP)",
                            "fail" => true);
      }
    }

    return array('mensagens' => $mensagem,
                 'title'     => "Análise exportação - Registro 90");

  }

  protected function analisaEducacensoRegistro91() {

    $escola   = $this->getRequest()->escola;
    $ano      = $this->getRequest()->ano;

    $sql = "SELECT DISTINCT pa.nome AS nome_aluno,
                            pe.fantasia AS nome_escola,
                            eca.cod_aluno_inep AS cod_inep
              FROM pmieducar.aluno a
              LEFT JOIN modules.educacenso_cod_aluno eca ON (eca.cod_aluno = a.cod_aluno)
             INNER JOIN pmieducar.matricula m ON (m.ref_cod_aluno = a.cod_aluno)
             INNER JOIN pmieducar.matricula_turma mt ON (mt.ref_cod_matricula = m.cod_matricula)
             INNER JOIN pmieducar.escola e ON (e.cod_escola = m.ref_ref_cod_escola)
             INNER JOIN cadastro.pessoa pa ON (pa.idpes = a.ref_idpes)
             INNER JOIN cadastro.juridica pe ON (pe.idpes = e.ref_idpes)
             INNER JOIN pmieducar.instituicao i ON (i.cod_instituicao = e.ref_cod_instituicao)
             WHERE e.cod_escola = $2
               AND m.aprovado IN (1, 2, 3, 4, 6, 15)
               AND m.ano = $1
               AND mt.data_enturmacao > i.data_educacenso
               AND i.data_educacenso IS NOT NULL
             ORDER BY nome_aluno";

    $alunos = $this->fetchPreparedQuery($sql, array($ano,
                                                    $escola));

    $mensagem = array();

    foreach ($alunos as $aluno) {
      $nomeEscola = Portabilis_String_Utils::toUtf8(mb_strtoupper($aluno["nome_escola"]));
      $nomeAluno  = Portabilis_String_Utils::toUtf8(mb_strtoupper($aluno["nome_aluno"]));

      if (is_null($aluno["cod_inep"])) {
        $mensagem[] = array("text" => "Dados para formular o registro 91 da escola {$nomeEscola} não encontrados. Verifique se o(a) aluno(a) {$nomeAluno} possui o código INEP cadastrado.",
                            "path" => "(Cadastros > Aluno > Alunos > Editar > Campo: Código INEP)",
                            "fail" => true);
      }
    }

    return array('mensagens' => $mensagem,
                 'title'     => "Análise exportação - Registro 91");

  }

  public function Gerar() {
    if ($this->isRequestFor('get', 'registro-00'))
      $this->appendResponse($this->analisaEducacensoRegistro00());
    else if ($this->isRequestFor('get', 'registro-10'))
      $this->appendResponse($this->analisaEducacensoRegistro10());
    else if ($this->isRequestFor('get', 'registro-20'))
      $this->appendResponse($this->analisaEducacensoRegistro20());
    else if ($this->isRequestFor('get', 'registro-30'))
      $this->appendResponse($this->analisaEducacensoRegistro30());
    else if ($this->isRequestFor('get', 'registro-40'))
      $this->appendResponse($this->analisaEducacensoRegistro40());
    else if ($this->isRequestFor('get', 'registro-50'))
      $this->appendResponse($this->analisaEducacensoRegistro50());
    else if ($this->isRequestFor('get', 'registro-51'))
      $this->appendResponse($this->analisaEducacensoRegistro51());
    else if ($this->isRequestFor('get', 'registro-60'))
      $this->appendResponse($this->analisaEducacensoRegistro60());
    else if ($this->isRequestFor('get', 'registro-70'))
      $this->appendResponse($this->analisaEducacensoRegistro70());
    else if ($this->isRequestFor('get', 'registro-80'))
      $this->appendResponse($this->analisaEducacensoRegistro80());
    else if ($this->isRequestFor('get', 'registro-89'))
      $this->appendResponse($this->analisaEducacensoRegistro89());
    else if ($this->isRequestFor('get', 'registro-90'))
      $this->appendResponse($this->analisaEducacensoRegistro90());
    else if ($this->isRequestFor('get', 'registro-91'))
      $this->appendResponse($this->analisaEducacensoRegistro91());
    else
      $this->notImplementedOperationError();
  }
}
