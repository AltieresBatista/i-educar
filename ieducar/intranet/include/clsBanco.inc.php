<?php

/*
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
 */

/**
 * clsBanco class.
 *
 * @author   Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
 * @license  http://creativecommons.org/licenses/GPL/2.0/legalcode.pt  CC GNU GPL
 * @package  Core
 * @since    Classe dispon�vel desde a vers�o 1.0.0
 * @version  $Id$
 */

if (!class_exists('clsBancoSql_')) {
  require_once 'include/clsBancoPgSql.inc.php';
}

class clsBanco extends clsBancoSQL_ {

  protected $strHost       = 'localhost';    // Nome ou endere�o IP do servidor do banco de dados
  protected $strBanco      = 'ieducardb';    // Nome do banco de dados
  protected $strUsuario    = 'ieducaruser';  // Usu�rio devidamente autorizado a acessar o banco
  protected $strSenha      = 'ieducar';      // Senha do usu�rio do banco
  protected $strPort       = NULL;           // Porta do servidor de banco de dados

  public $bLink_ID         = 0;              // Identificador da conex�o
  public $bConsulta_ID     = 0;              // Identificador do resultado da consulta
  public $arrayStrRegistro = array();        // Tupla resultante de uma consulta
  public $iLinha           = 0;              // Ponteiro interno para a tupla atual da consulta

  public $bErro_no         = 0;              // Se ocorreu erro na consulta, retorna FALSE
  public $strErro          = "";             // Frase de descri��o do erro retornado
  public $bDepurar         = FALSE;          // Ativa ou desativa fun��es de depura��o

  public $bAuto_Limpa      = FALSE;          // '1' para limpar o resultado assim que chegar ao �ltimo registro

  public $strStringSQL     = '';

  var $strType         = '';
  var $arrayStrFields  = array();
  var $arrayStrFrom    = array();
  var $arrayStrWhere   = array();
  var $arrayStrOrderBy = array();
  var $arrayStrGroupBy = array();
  var $iLimitInicio;
  var $iLimitQtd;
  var $arrayStrArquivo = '';



  /**
   * Construtor (PHP 4).
   */
  public function clsBanco($strDataBase = FALSE) {}



  /**
   * Retorna a quantidade de registros de uma tabela baseado no objeto que a
   * abstrai. Este deve ter um atributo p�blico Object->_tabela.
   *
   * @param   mixed   Objeto que abstrai a tabela
   * @param   string  Nome da coluna para c�lculo COUNT()
   * @return  int     Quantidade de registros da tabela
   */
  public function doCountFromObj($obj, $column = '*') {
    if ($obj->_tabela == NULL) {
      return FALSE;
    }

    $sql = sprintf('SELECT COUNT(%s) FROM %s', $column, $obj->_tabela);
    $this->Consulta($sql);

    return (int)$this->UnicoCampo($sql);
  }

}