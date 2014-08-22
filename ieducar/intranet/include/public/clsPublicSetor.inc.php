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
 * @package   iEd_Public
 * @since     ?
 * @version   $Id$
 */

require_once 'include/public/geral.inc.php';

/**
 * clsPublicSetor class.
 *
 * @author    Lucas Schmoeller da Silva <lucas@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Public
 * @since     ?
 * @version   @@package_version@@
 */
class clsPublicSetor
{
  var $idsetorbai;
  var $nome;

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

  function clsPublicSetor($idsetorbai = NULL, $nome = NULL)
  {
    $db = new clsBanco();
    $this->_schema = 'public.';
    $this->_tabela = $this->_schema . 'setor ';

    $this->_campos_lista = $this->_todos_campos = ' idsetorbai, nome ';

    if (is_numeric($idsetorbai)) {
      $this->idsetorbai = $idsetorbai;
    }

    if (is_string($nome)) {
      $this->nome = $nome;
    }
  }

  /**
   * Cria um novo registro.
   * @return bool
   */
  function cadastra()
  {
    if (is_string($this->nome)) {
      $db = new clsBanco();

      $campos  = '';
      $valores = '';
      $gruda   = '';

      $campos  .= "{$gruda}nome";
      $valores .= "{$gruda}'{$this->nome}'";
      $gruda    = ', ';
 
      $db->Consulta(sprintf(
        "INSERT INTO %s (%s) VALUES (%s)",
        $this->_tabela, $campos, $valores
      ));

      return $db->InsertId('seq_setor');
    }

    return FALSE;
  }

  /**
   * Edita os dados de um registro.
   * @return bool
   */
  function edita()
  {
    if (is_numeric($this->idsetorbai)) {
      $db  = new clsBanco();
      $set = '';

      if (is_string($this->nome)) {
        $set  .= "{$gruda}nome = '{$this->nome}'";
        $gruda = ', ';
      }

      if ($set) {
        $db->Consulta(sprintf(
          'UPDATE %s SET %s WHERE idsetorbai = \'%d\'',
          $this->_tabela, $set, $this->idsetorbai
        ));

        return TRUE;
      }
    }

    return FALSE;
  }

  function lista($int_idsetorbai = NULL, $nome = NULL)
  {
    $select = 'SELECT {$this->_todos_campos}';
    $from   = ' {$this->_tabela}';

    $sql = $select . $from;

    $whereAnd = ' WHERE ';

    if (is_numeric($int_idsetorbai)) {
      $filtros .= "{$whereAnd} int_idsetorbai = '{$int_idsetorbai}'";      
      $whereAnd = ' AND ';
    }

    if (is_string($nome)) {
      $filtros .= "{$whereAnd} nome LIKE '%{$nome}%'";
      $whereAnd = ' AND ';
    }

    $db = new clsBanco();

    $countCampos = count(explode(', ', $this->_campos_lista));
    $resultado   = array();

    $sql .= $filtros . $this->getOrderby() . $this->getLimite();

    $this->_total = $db->CampoUnico(sprintf(
      'SELECT COUNT(0) FROM %s %s %s', $this->_tabela, $from, $filtros
    ));

    $db->Consulta($sql);

    if ($countCampos > 1) {
      while ($db->ProximoRegistro()) {
        $tupla           = $db->Tupla();
        $tupla['_total'] = $this->_total;
        $resultado[]     = $tupla;
      }
    }
    else {
      while ($db->ProximoRegistro()) {
        $tupla       = $db->Tupla();
        $resultado[] = $tupla[$this->_campos_lista];
      }
    }

    if (count($resultado)) {
      return $resultado;
    }

    return FALSE;
  }

  /**
   * Retorna um array com os dados de um registro
   * @return array
   */
  function detalhe()
  {
    if (is_numeric($this->idsetorbai)) {
      $db = new clsBanco();

      $sql = sprintf(
        'SELECT %s FROM %s WHERE idsetorbai = \'%d\'',
        $this->_todos_campos, $this->_tabela, $this->idsetorbai
      );

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
    if (is_numeric($this->idsetorbai)) {
      $db = new clsBanco();

      $sql = sprintf(
        'SELECT 1 FROM %s WHERE idsetorbai = \'%d\'',
        $this->_tabela, $this->idsetorbai
      );

      $db->Consulta($sql);

      if ($db->ProximoRegistro()) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Exclui um registro
   *
   * @return bool
   */
  function excluir()
  {
    if (is_numeric($this->idsetorbai)) {
      $db = new clsBanco();

      $sql = sprintf(
        'DELETE FROM %s WHERE idsetorbai = \'%d\'',
        $this->_tabela, $this->idsetorbai
      );

      $db->Consulta($sql);
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