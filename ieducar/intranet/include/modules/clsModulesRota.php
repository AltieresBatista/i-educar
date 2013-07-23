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
 * @author    Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Arquivo dispon�vel desde a vers�o 1.0.0
 * @version   $Id$
 */

require_once 'include/pmieducar/geral.inc.php';

/**
 * clsPmieducarInstituicao class.
 *
 * @author    Lucas Schmoeller da Silva <lucas@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   Api
 * @subpackage  Modules
 * @since   07/2013
 * @version   $Id$
 */
class clsModulesRota
{
  var $cod_rota;
  var $descricao;
  var $ref_idpes_destino;
  var $ano;

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
  function clsModulesRota($cod_rota = NULL, $descricao = NULL, $ref_idpes_destino = NULL, $ano = NULL)
  {
    $db = new clsBanco();
    $this->_schema = "modules.";
    $this->_tabela = "{$this->_schema}rota";

    $this->_campos_lista = $this->_todos_campos = " cod_tipo_veiculo, descricao"; 

    if (is_numeric($cod_tipo_veiculo)) {
      $this->cod_tipo_veiculo = $cod_tipo_veiculo;
    }

    if (is_string($descricao)) {
      $this->descricao = $descricao;
    }

    if (is_numeric($ref_idpes_destino)) {
      $this->ref_idpes_destino = $ref_idpes_destino;
    }

    if (is_numeric($ano)) {
      $this->ano = $ano;
    }

  }

  /**
   * Cria um novo registro.
   * @return bool
   */
  function cadastra()
  {
    if (is_string($this->descricao) && is_numeric($ref_idpes_destino) && is_numeric($ano))
    {
      $db = new clsBanco();

      $campos  = '';
      $valores = '';
      $gruda   = '';

      
      $campos .= "{$gruda}descricao";
      $valores .= "{$gruda}'{$this->descricao}'";
      $gruda = ", ";

      $campos .= "{$gruda}ref_idpes_destino";
      $valores .= "{$gruda}'{$this->ref_idpes_destino}'";
      $gruda = ", ";

                  $campos .= "{$gruda}ano";
      $valores .= "{$gruda}'{$this->ano}'";
      $gruda = ", ";
      


      $db->Consulta("INSERT INTO {$this->_tabela} ( $campos ) VALUES( $valores )");
      return $db->InsertId("{$this->_tabela}_seq");
    }

    return FALSE;
  }

  /**
   * Edita os dados de um registro.
   * @return bool
   */
  function edita()
  {
    if (is_string($this->cod_rota)) {
      $db  = new clsBanco();
      $set = '';
      $gruda = '';

      if (is_numeric($this->ref_idpes_destino)) {
        $set .= "{$gruda}ref_idpes_destino = '{$this->ref_idpes_destino}'";
        $gruda = ", ";
      }

      if (is_string($this->descricao)) {
        $set .= "{$gruda}descricao = '{$this->descricao}'";
        $gruda = ", ";
      }

      if (is_numeric($this->ano)) {
        $set .= "{$gruda}ano = '{$this->ano}'";
        $gruda = ", ";
      }

      if ($set) {
        $db->Consulta("UPDATE {$this->_tabela} SET $set WHERE cod_rota = '{$this->cod_rota}'");
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Retorna uma lista de registros filtrados de acordo com os par�metros.
   * @return array
   */
  function lista($cod_rota = NULL, $descricao = NULL, $ref_idpes_destino = NULL , $nome_destino = NULL, $ano = NULL)
  {
    $sql = "SELECT {$this->_campos_lista}, (
          SELECT
            nome
          FROM
            cadastro.pessoa
          WHERE
            idpes = ref_idpes_destino
         ) AS nome_destino FROM {$this->_tabela}";
    $filtros = "";

    $whereAnd = " WHERE ";

    if (is_numeric($cod_rota)) {
      $filtros .= "{$whereAnd} cod_rota = '{$cod_rota}'";
      $whereAnd = " AND ";
    }

    if (is_string($descricao)) {
      $filtros .= "{$whereAnd} TO_ASCII(LOWER(descricao)) LIKE TO_ASCII(LOWER('%{$descricao}%'))";
      $whereAnd = " AND ";
    }

    if (is_numeric($ref_idpes_destino)) {
      $filtros .= "{$whereAnd} ref_idpes_destino = '{$ref_idpes_destino}'";
      $whereAnd = " AND ";
    }
    if (is_string($nome_destino)) {
      $filtros .= "
        {$whereAnd} exists (
          SELECT
            1
          FROM
            cadastro.pessoa
          WHERE
            cadastro.pessoa.idpes = ref_idpes_destino
            AND TO_ASCII(LOWER(nome)) LIKE TO_ASCII(LOWER('%{$nome_destino}%'))
        )";

      $whereAnd = ' AND ';
    }  

    if (is_numeric($ano)) {
      $filtros .= "{$whereAnd} ano = '{$ano}'";
      $whereAnd = " AND ";
    }




    $db = new clsBanco();
    $countCampos = count(explode(',', $this->_campos_lista))+2;
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
    if (is_numeric($this->cod_rota)) {
      $db = new clsBanco();
      $db->Consulta("SELECT {$this->_todos_campos}, (
          SELECT
            nome
          FROM
            cadastro.pessoa
          WHERE
            idpes = ref_idpes
         ) AS nome_empresa , (SELECT nome FROM cadastro.pessoa WHERE idpes = ref_resp_idpes) AS nome_responsavel FROM {$this->_tabela} WHERE cod_rota = '{$this->cod_rota}'");
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
    if (is_numeric($this->cod_rota)) {
      $db = new clsBanco();
      $db->Consulta("SELECT 1 FROM {$this->_tabela} WHERE cod_rota = '{$this->cod_rota}'");
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
    if (is_numeric($this->cod_rota)) {
      $sql = "DELETE FROM {$this->_tabela} WHERE cod_rota = '{$this->cod_rota}'";
      $db = new clsBanco();
      $db->Consulta($sql);
      return true;
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