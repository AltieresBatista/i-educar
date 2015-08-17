<?php
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
 * @author    Lucas Schmoeller da Silva <lucas@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     ?
 * @version   $Id$
 */

require_once 'include/pmieducar/geral.inc.php';

/**
 * clsPmieducarDisciplinaDependencia class.
 *
 * @author    Lucas Schmoeller da Silva <lucas@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     ?
 * @version   @@package_version@@
 */
class clsPmieducarDisciplinaDependencia
{
  var $ref_cod_matricula;
  var $ref_cod_serie;
  var $ref_cod_escola;
  var $ref_cod_disciplina;
  var $observacao;
  var $cod_disciplina_dependencia;

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
  function clsPmieducarDisciplinaDependencia($ref_cod_matricula = NULL,
    $ref_cod_serie = NULL, $ref_cod_escola = NULL, $ref_cod_disciplina = NULL,
    $observacao = NULL, $cod_disciplina_dependencia = NULL)
  {
    $db = new clsBanco();
    $this->_schema = 'pmieducar.';
    $this->_tabela = $this->_schema . 'disciplina_dependencia';

    $this->_campos_lista = $this->_todos_campos = ' cod_disciplina_dependencia,ref_cod_matricula, ref_cod_serie,
        ref_cod_escola, ref_cod_disciplina, observacao';

    if (is_numeric($ref_cod_matricula)) {
      $matricula = new clsPmieducarMatricula($ref_cod_matricula);
      if ($matricula->existe()) {
        $this->ref_cod_matricula = $ref_cod_matricula;
      }
    }

    if (is_numeric($ref_cod_disciplina) && is_numeric($ref_cod_escola) &&
      is_numeric($ref_cod_serie)
    ) {
      require_once 'ComponenteCurricular/Model/AnoEscolarDataMapper.php';
      $anoEscolarMapper = new ComponenteCurricular_Model_AnoEscolarDataMapper();
      $componenteAnos = $anoEscolarMapper->findAll(array(), array(
        'componenteCurricular' => $ref_cod_disciplina,
        'anoEscolar'           => $ref_cod_serie
      ));

      if (1 == count($componenteAnos)) {
        $this->ref_cod_disciplina = $ref_cod_disciplina;
        $this->ref_cod_serie      = $ref_cod_serie;
        $this->ref_cod_escola     = $ref_cod_escola;
      }
    }

    if (is_string($observacao)) {
      $this->observacao = $observacao;
    }

    if (is_numeric($cod_disciplina_dependencia)) {
      $this->cod_disciplina_dependencia = $cod_disciplina_dependencia;
    }
  }

  /**
   * Cria um novo registro.
   * @return bool
   */
  function cadastra()
  {
    if (is_numeric($this->ref_cod_matricula) && is_numeric($this->ref_cod_serie) &&
      is_numeric($this->ref_cod_escola) && is_numeric($this->ref_cod_disciplina) &&
      is_numeric($this->cod_disciplina_dependencia))
    {
      $db = new clsBanco();

      $campos  = '';
      $valores = '';
      $gruda   = '';

      if (is_numeric($this->ref_cod_matricula)) {
        $campos  .= "{$gruda}ref_cod_matricula";
        $valores .= "{$gruda}'{$this->ref_cod_matricula}'";
        $gruda    = ', ';
      }

      if (is_numeric($this->ref_cod_serie)) {
        $campos  .= "{$gruda}ref_cod_serie";
        $valores .= "{$gruda}'{$this->ref_cod_serie}'";
        $gruda    = ', ';
      }

      if (is_numeric($this->ref_cod_escola)) {
        $campos  .= "{$gruda}ref_cod_escola";
        $valores .= "{$gruda}'{$this->ref_cod_escola}'";
        $gruda    = ', ';
      }

      if (is_numeric($this->ref_cod_disciplina)) {
        $campos  .= "{$gruda}ref_cod_disciplina";
        $valores .= "{$gruda}'{$this->ref_cod_disciplina}'";
        $gruda    = ', ';
      }

      if (is_string($this->observacao)) {
        $campos  .= "{$gruda}observacao";
        $valores .= "{$gruda}'{$this->observacao}'";
        $gruda    = ', ';
      }

      if (is_numeric($this->cod_disciplina_dependencia)) {
        $campos  .= "{$gruda}cod_disciplina_dependencia";
        $valores .= "{$gruda}'{$this->cod_disciplina_dependencia}'";
        $gruda    = ', ';
      }

      $sql = "INSERT INTO {$this->_tabela} ($campos) VALUES ($valores)";
      $db->Consulta($sql);
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Edita os dados de um registro.
   * @return bool
   */
  function edita()
  {
    if (is_numeric($this->ref_cod_matricula) && is_numeric($this->ref_cod_serie) &&
      is_numeric($this->ref_cod_escola) && is_numeric($this->ref_cod_disciplina)
    ) {
      $db  = new clsBanco();
      $set = '';

      if (is_string($this->observacao)) {
        $set  .= "{$gruda}observacao = '{$this->observacao}'";
        $gruda = ', ';
      }

      if ($set) {
        $db->Consulta("UPDATE {$this->_tabela} SET $set WHERE ref_cod_matricula = '{$this->ref_cod_matricula}' AND ref_cod_serie = '{$this->ref_cod_serie}' AND ref_cod_escola = '{$this->ref_cod_escola}' AND ref_cod_disciplina = '{$this->ref_cod_disciplina}'");
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Retorna uma lista de registros filtrados de acordo com os par�metros.
   * @return array
   */
  function lista($int_ref_cod_matricula = NULL, $int_ref_cod_serie = NULL,
    $int_ref_cod_escola = NULL, $int_ref_cod_disciplina = NULL, $str_observacao = NULL)
  {
    $sql     = "SELECT {$this->_campos_lista} FROM {$this->_tabela}";
    $filtros = '';

    $whereAnd = ' WHERE ';

    if (is_numeric($int_ref_cod_matricula)) {
      $filtros .= "{$whereAnd} ref_cod_matricula = '{$int_ref_cod_matricula}'";
      $whereAnd = ' AND ';
    }

    if (is_numeric($int_ref_cod_serie)) {
      $filtros .= "{$whereAnd} ref_cod_serie = '{$int_ref_cod_serie}'";
      $whereAnd = ' AND ';
    }

    if (is_numeric($int_ref_cod_escola)) {
      $filtros .= "{$whereAnd} ref_cod_escola = '{$int_ref_cod_escola}'";
      $whereAnd = ' AND ';
    }

    if (is_numeric($int_ref_cod_disciplina)) {
      $filtros .= "{$whereAnd} ref_cod_disciplina = '{$int_ref_cod_disciplina}'";
      $whereAnd = ' AND ';
    }

    if (is_string($str_observacao)) {
      $filtros .= "{$whereAnd} observacao LIKE '%{$str_observacao}%'";
      $whereAnd = ' AND ';
    }

    $db = new clsBanco();
    $countCampos = count(explode(',', $this->_campos_lista));
    $resultado   = array();

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
    if (is_numeric($this->ref_cod_matricula) && is_numeric($this->ref_cod_serie) &&
      is_numeric($this->ref_cod_escola) && is_numeric($this->ref_cod_disciplina)
    ) {
      $db = new clsBanco();
      $db->Consulta("SELECT {$this->_todos_campos} FROM {$this->_tabela} WHERE ref_cod_matricula = '{$this->ref_cod_matricula}' AND ref_cod_serie = '{$this->ref_cod_serie}' AND ref_cod_escola = '{$this->ref_cod_escola}' AND ref_cod_disciplina = '{$this->ref_cod_disciplina}'");
      $db->ProximoRegistro();
      return $db->Tupla();
    }
    return FALSE;
  }

  /**
   * Retorna um array com os dados de um registro
   *
   * @return array
   */
  function existe()
  {
    if (is_numeric($this->ref_cod_matricula) && is_numeric($this->ref_cod_serie) &&
      is_numeric($this->ref_cod_escola) && is_numeric($this->ref_cod_disciplina)
    ) {
      $db = new clsBanco();
      $db->Consulta( "SELECT 1 FROM {$this->_tabela} WHERE ref_cod_matricula = '{$this->ref_cod_matricula}' AND ref_cod_serie = '{$this->ref_cod_serie}' AND ref_cod_escola = '{$this->ref_cod_escola}' AND ref_cod_disciplina = '{$this->ref_cod_disciplina}'" );
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
    if (is_numeric($this->ref_cod_matricula) && is_numeric($this->ref_cod_serie) &&
      is_numeric($this->ref_cod_escola) && is_numeric($this->ref_cod_disciplina)
    ) {
      $db = new clsBanco();
      $db->Consulta( "DELETE FROM {$this->_tabela} WHERE ref_cod_matricula = '{$this->ref_cod_matricula}' AND ref_cod_serie = '{$this->ref_cod_serie}' AND ref_cod_escola = '{$this->ref_cod_escola}' AND ref_cod_disciplina = '{$this->ref_cod_disciplina}'" );
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