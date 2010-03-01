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
 * @author    Eriksen Costa Paix�o <eriksen.paixao_bs@cobra.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   Core_Controller
 * @since     Arquivo dispon�vel desde a vers�o 1.1.0
 * @version   $Id: /ieducar/branches/teste/ieducar/lib/Core/Controller/Page/Abstract.php 646 2009-11-12T21:54:25.107288Z eriksen  $
 */

require_once 'Core/View/Tabulable.php';
require_once 'include/clsListagem.inc.php';
require_once 'CoreExt/View/Helper/UrlHelper.php';

/**
 * Core_Controller_Page_ListController abstract class.
 *
 * Prov� um controller padr�o para listagem de registros.
 *
 * @author    Eriksen Costa Paix�o <eriksen.paixao_bs@cobra.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   Core_Controller
 * @since     Classe dispon�vel desde a vers�o 1.1.0
 * @version   @@package_version@@
 */
class Core_Controller_Page_ListController extends clsListagem implements Core_View_Tabulable
{
  /**
   * Mapeia um nome descritivo a um atributo de CoreExt_Entity retornado pela
   * inst�ncia CoreExt_DataMapper retornada por getDataMapper().
   *
   * Para uma inst�ncia de CoreExt_Entity que tenha os seguintes atributos:
   * <code>
   * <?php
   * $_data = array(
   *   'nome' => NULL
   *   'idade' => NULL,
   *   'data_validacao' => NULL
   * );
   * </code>
   *
   * O mapeamento poderia ser feito da seguinte forma:
   * <code>
   * <?php
   * $_tableMap = array(
   *   'Nome' => 'nome',
   *   'Idade (anos)' => 'idade'
   * );
   * </code>
   *
   * Se um atributo n�o for mapeado, ele n�o ser� exibido por padr�o durante
   * a gera��o de HTML na execu��o do m�todo Gerar().
   *
   * @var array
   */
  protected $_tableMap = array();

  /**
   * Getter.
   * @see Core_View_Tabulable#getTableMap()
   */
  public function getTableMap()
  {
    return $this->_tableMap;
  }

  /**
   * Implementa��o padr�o para as subclasses que estenderem essa classe. Cria
   * uma lista de apresenta��o de dados simples utilizando o mapeamento de
   * $_tableMap.
   *
   * @see Core_Controller_Page_ListController#$_tableMap
   * @see clsDetalhe#Gerar()
   */
  public function Gerar()
  {
    $headers = $this->getTableMap();

    $this->addCabecalhos(array_keys($headers));

    $mapper  = $this->getDataMapper();
    $entries = $mapper->findAll();

    // Paginador
    $this->limite = 20;
    $this->offset = ($_GET['pagina_' . $this->nome]) ?
      $_GET['pagina_' . $this->nome] * $this->limite - $this->limite
      : 0;

    foreach ($entries as $entry) {
      $item = array();
      $data = $entry->toArray();
      $options = array('query' => array('id' => $entry->id));

      foreach ($headers as $label => $attr) {
        $item[] = CoreExt_View_Helper_UrlHelper::l(
          $entry->$attr, 'view', $options
        );
      }

      $this->addLinhas($item);
    }

    $this->addPaginador2("", count($entries), $_GET, $this->nome, $this->limite);

    $this->acao = "go(\"edit\")";
    $this->nome_acao = "Novo";

    $this->largura = "100%";
  }
}