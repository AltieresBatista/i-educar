<?php
//error_reporting(E_ERROR);
//ini_set("display_errors", 1);


/**
 * i-Educar - Sistema de gest�o escolar
 *
 * Copyright (C) 2006  Prefeitura Municipal de Itaja�
 *                     <ctima@itajai.sc.gov.br>
 *
 * Este programa � software livre; voc� pode redistribu�-lo e/ou modific�-lo
 * sob os termos da Licen�a P�blica Geral GNU conforme publicada pela Free
 * Software Foundation; tanto a vers�o 2 da Licen�a, como (a seu crit�rio)
 * qualquer vers�o posterior.
 *
 * Este programa � distribu�do na expectativa de que seja �til, por�m, SEM
 * NENHUMA GARANTIA; nem mesmo a garantia impl�cita de COMERCIABILIDADE OU
 * ADEQUA��O A UMA FINALIDADE ESPEC�FICA. Consulte a Licen�a P�blica Geral
 * do GNU para mais detalhes.
 *
 * Voc� deve ter recebido uma c�pia da Licen�a P�blica Geral do GNU junto
 * com este programa; se n�o, escreva para a Free Software Foundation, Inc., no
 * endere�o 59 Temple Street, Suite 330, Boston, MA 02111-1307 USA.
 *
 * @author    Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Arquivo dispon�vel desde a vers�o 1.0.0
 * @version   $Id$
 */

require_once 'include/pmieducar/geral.inc.php';

/**
 * clsPmieducarMatriculaDependencia class.
 *
 * @author    Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Classe dispon�vel desde a vers�o 1.0.0
 * @version   @@package_version@@
 */
class clsPmieducarMatriculaDependencia
{
  var $cod_matricula_dependencia;
  var $ano;
  var $ref_cod_aluno;
  var $ref_cod_matricula;
  var $ref_cod_instituicao;
  var $ref_cod_escola;
  var $ref_cod_curso;
  var $ref_cod_serie;
  var $componente_curricular_id;
  var $aprovado;

  /**
   * Armazena o total de resultados obtidos na �ltima chamada ao m�todo lista().
   * @var int
   */
  var $_total;

  /**
   * Nome do schema.
   * @var string
   */
  var $_schema;

  /**
   * Nome da tabela.
   * @var string
   */
  var $_tabela;

  /**
   * Lista separada por v�rgula, com os campos que devem ser selecionados na
   * pr�xima chamado ao m�todo lista().
   * @var string
   */
  var $_campos_lista;

  /**
   * Lista com todos os campos da tabela separados por v�rgula, padr�o para
   * sele��o no m�todo lista.
   * @var string
   */
  var $_todos_campos;

  /**
   * Valor que define a quantidade de registros a ser retornada pelo m�todo lista().
   * @var int
   */
  var $_limite_quantidade;

  /**
   * Define o valor de offset no retorno dos registros no m�todo lista().
   * @var int
   */
  var $_limite_offset;

  /**
   * Define o campo para ser usado como padr�o de ordena��o no m�todo lista().
   * @var string
   */
  var $_campo_order_by;

  /**
   * Construtor.
   */
  function clsPmieducarMatriculaDependencia($cod_matricula_dependencia = NULL, $ano = NULL, $ref_cod_aluno = NULL,
      $ref_cod_matricula = NULL, $ref_cod_instituicao = NULL, $ref_cod_escola = NULL, $ref_cod_curso = NULL,
      $ref_cod_serie = NULL, $componente_curricular_id = NULL, $aprovado = NULL) {

    $db = new clsBanco();
    $this->_schema = 'pmieducar.';
    $this->_tabela = $this->_schema . 'matricula_dependencia';

    $this->_campos_lista = $this->_todos_campos = " cod_matricula_dependencia, ano, ref_cod_aluno, ref_cod_matricula,
              ref_cod_instituicao, ref_cod_escola, ref_cod_curso, ref_cod_serie, componente_curricular_id, aprovado";

    if (is_numeric($cod_matricula_dependencia)) {
      $this->cod_matricula_dependencia = $cod_matricula_dependencia;
    }

    if (is_numeric($ano)) {
      $this->ano = $ano;
    }

    if (is_numeric($ref_cod_aluno)) {
      $this->ref_cod_aluno = $ref_cod_aluno;
    }

    if (is_numeric($ref_cod_matricula)) {
      $this->ref_cod_matricula = $ref_cod_matricula;
    }

    if (is_numeric($ref_cod_instituicao)) {
      $this->ref_cod_instituicao = $ref_cod_instituicao;
    }

    if (is_numeric($ref_cod_escola)) {
      $this->ref_cod_escola = $ref_cod_escola;
    }

    if (is_numeric($ref_cod_curso)) {
      $this->ref_cod_curso = $ref_cod_curso;
    }

    if (is_numeric($ref_cod_serie)) {
      $this->ref_cod_serie = $ref_cod_serie;
    }

    if (is_numeric($componente_curricular_id)) {
      $this->componente_curricular_id = $componente_curricular_id;
    }

    if (is_numeric($aprovado)) {
      $this->aprovado = $aprovado;
    }

  }

  /**
   * Cria um novo registro.
   * @return bool
   */
  function cadastra()
  {

    if (is_numeric($this->ano) && is_numeric($this->ref_cod_aluno) && is_numeric($this->ref_cod_matricula)
          && is_numeric($this->ref_cod_instituicao) && is_numeric($this->ref_cod_escola) && is_numeric($this->ref_cod_curso)
          && is_numeric($this->ref_cod_serie) && is_numeric($this->componente_curricular_id) && is_numeric($this->aprovado))
    {
      $db = new clsBanco();

      $campos = "";
      $valores = "";
      $gruda = "";

      $campos .= "{$gruda}ref_cod_aluno";
      $valores .= "{$gruda}'{$this->ref_cod_aluno}'";
      $gruda = ", ";

      $campos .= "{$gruda}ano";
      $valores .= "{$gruda}'{$this->ano}'";
      $gruda = ", ";

      $campos .= "{$gruda}ref_cod_matricula";
      $valores .= "{$gruda}'{$this->ref_cod_matricula}'";
      $gruda = ", ";

      $campos .= "{$gruda}ref_cod_instituicao";
      $valores .= "{$gruda}'{$this->ref_cod_instituicao}'";
      $gruda = ", ";

      $campos .= "{$gruda}ref_cod_escola";
      $valores .= "{$gruda}'{$this->ref_cod_escola}'";
      $gruda = ", ";

      $campos .= "{$gruda}ref_cod_curso";
      $valores .= "{$gruda}'{$this->ref_cod_curso}'";
      $gruda = ", ";

      $campos .= "{$gruda}ref_cod_serie";
      $valores .= "{$gruda}'{$this->ref_cod_serie}'";
      $gruda = ", ";

      $campos .= "{$gruda}componente_curricular_id";
      $valores .= "{$gruda}'{$this->componente_curricular_id}'";
      $gruda = ", ";

      $campos .= "{$gruda}aprovado";
      $valores .= "{$gruda}'{$this->aprovado}'";
      $gruda = ", ";

      $db->Consulta("INSERT INTO {$this->_tabela} ($campos) VALUES ($valores)");
      return $db->InsertId('pmieducar.matricula_dependencia_id_seq');
    }

    return FALSE;
  }

  function edita()
  {
    if (is_numeric($this->cod_matricula))
    {

      $db = new clsBanco();
      $set = "";

      // �NICO REGISTRO QUE PODE SER ALTERADO NESSA TABELA � A SITUA��O DO ALUNO.
      // CASO NECESS�RIO IMPLEMENTAR OUTRAS EDI��ES
      if (is_numeric($this->aprovado)) {
        $set .= "{$gruda}aprovado = '{$this->aprovado}'";
        $gruda = ", ";
      }

      if ($set) {
        $db->Consulta("UPDATE {$this->_tabela} SET $set WHERE cod_matricula_dependencia = '{$this->cod_matricula_dependencia}'");
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Retorna uma lista de registros filtrados de acordo com os par�metros.
   * @return array
   */
  function lista($ref_cod_matricula = NULL, $componente_curricular_id = NULL)
  {

    $sql = "SELECT {$this->_campos_lista} FROM {$this->_tabela} ";

    $whereAnd = " WHERE ";

    if (is_numeric($ref_cod_matricula)) {
      $filtros .= "{$whereAnd} ref_cod_matricula = '{$ref_cod_matricula}'";
      $whereAnd = " AND ";
    }

    if (is_numeric($componente_curricular_id)) {
      $filtros .= "{$whereAnd} componente_curricular_id = '{$componente_curricular_id}'";
      $whereAnd = " AND ";
    }

    $db = new clsBanco();
    $countCampos = count(explode(',', $this->_campos_lista));
    $resultado = array();

    $sql .= $filtros . $this->getOrderby() . $this->getLimite();

    $this->_total = $db->CampoUnico("SELECT COUNT(0) FROM {$this->_tabela} {$filtros}");

    $db->Consulta($sql);

    if ($countCampos > 1) {
      while ($db->ProximoRegistro()) {
        $tupla = $db->Tupla();

        $tupla["_total"] = $this->_total;
        $resultado[] = $tupla;
      }
    }
    else {
      while ($db->ProximoRegistro()) {
        $tupla = $db->Tupla();
        $resultado[] = $tupla[$this->_campos_lista];
      }
    }

    if (count($resultado)) {
      return $resultado;
    }

    return FALSE;
  }

  /**
   * Retorna um array com os dados de um registro.
   * @return array
   */
  function detalhe()
  {
    if (is_numeric($this->cod_matricula_dependencia)) {
      $sql = "SELECT {$this->_todos_campos}, p.nome,(p.nome) as nome_upper, md.ref_cod_instituicao FROM {$this->_tabela} md, {$this->_schema}aluno a, cadastro.pessoa p WHERE md.cod_matricula_dependencia = '{$this->cod_matricula_dependencia}' AND a.cod_aluno = md.ref_cod_aluno AND p.idpes = a.ref_idpes ";

      $db = new clsBanco();
      $db->Consulta($sql);
      $db->ProximoRegistro();

      return $db->Tupla();
    }

    return FALSE;
  }

  /**
   * Retorna um array com os dados de um registro.
   * @return array
   */
  function existe()
  {
    if (is_numeric($this->cod_matricula_dependencia)) {
      $db = new clsBanco();
      $db->Consulta("SELECT 1 FROM {$this->_tabela} WHERE cod_matricula_dependencia = '{$this->cod_matricula_dependencia}'");
      $db->ProximoRegistro();
      return $db->Tupla();
    }

    return FALSE;
  }

  /**
   * Exclui um registro.
   * @return bool
   */
  function excluir()
  {
    if (is_numeric($this->cod_matricula_dependencia)) {
      $db = new clsBanco();
      $db->Consulta("DELETE FROM {$this->_tabela} WHERE cod_matricula_dependencia = '{$this->cod_matricula_dependencia}'");
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Define quais campos da tabela ser�o selecionados no m�todo Lista().
   */
  function setCamposLista($str_campos)
  {
    $this->_campos_lista = $str_campos;
  }

  /**
   * Define que o m�todo Lista() deverpa retornar todos os campos da tabela.
   */
  function resetCamposLista()
  {
    $this->_campos_lista = $this->_todos_campos;
  }

  /**
   * Define limites de retorno para o m�todo Lista().
   */
  function setLimite($intLimiteQtd, $intLimiteOffset = NULL)
  {
    $this->_limite_quantidade = $intLimiteQtd;
    $this->_limite_offset = $intLimiteOffset;
  }

  /**
   * Retorna a string com o trecho da query respons�vel pelo limite de
   * registros retornados/afetados.
   *
   * @return string
   */
  function getLimite()
  {
    if (is_numeric($this->_limite_quantidade)) {
      $retorno = " LIMIT {$this->_limite_quantidade}";
      if (is_numeric($this->_limite_offset)) {
        $retorno .= " OFFSET {$this->_limite_offset} ";
      }
      return $retorno;
    }
    return '';
  }

  /**
   * Define o campo para ser utilizado como ordena��o no m�todo Lista().
   */
  function setOrderby($strNomeCampo)
  {
    if (is_string($strNomeCampo) && $strNomeCampo ) {
      $this->_campo_order_by = $strNomeCampo;
    }
  }

  /**
   * Retorna a string com o trecho da query respons�vel pela Ordena��o dos
   * registros.
   *
   * @return string
   */
  function getOrderby()
  {
    if (is_string($this->_campo_order_by)) {
      return " ORDER BY {$this->_campo_order_by} ";
    }
    return '';
  }
}