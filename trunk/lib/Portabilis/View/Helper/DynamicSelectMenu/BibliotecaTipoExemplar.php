<?php
#error_reporting(E_ALL);
#ini_set("display_errors", 1);
/**
 * i-Educar - Sistema de gestão escolar
 *
 * Copyright (C) 2006  Prefeitura Municipal de Itajaí
 *                     <ctima@itajai.sc.gov.br>
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
 * @author    Lucas D'Avila <lucasdavila@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   Portabilis
 * @since     Arquivo disponível desde a versão 1.1.0
 * @version   $Id$
 */

require_once 'lib/Portabilis/View/Helper/DynamicSelectMenu/Core.php';


/**
 * Portabilis_View_Helper_DynamicSelectMenu_BibliotecaTipoExemplar class.
 *
 * @author    Lucas D'Avila <lucasdavila@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   Portabilis
 * @since     Classe disponível desde a versão 1.1.0
 * @version   @@package_version@@
 */
class Portabilis_View_Helper_DynamicSelectMenu_BibliotecaTipoExemplar extends Portabilis_View_Helper_DynamicSelectMenu_Core {

  protected function getOptions($bibliotecaId, $tiposExemplar) {

    // se $tiposExemplar vazio, seleciona tipos de exemplares da biblioteca (caso haja bibliotecaId)
    if (empty($tiposExemplar)) {
      if (! $bibliotecaId)
        $bibliotecaId = $this->getBibliotecaId($bibliotecaId);

      if ($bibliotecaId) {
        $columns = array('cod_exemplar_tipo', 'nm_tipo');
        $where   = array('ref_cod_biblioteca' => $bibliotecaId, 'ativo' => '1');
        $orderBy = array('nm_tipo' => 'ASC');

        $tiposExemplar = $this->getDataMapperFor('tipoExemplar')->findAll($columns,
                                                                          $where,
                                                                          $orderBy,
                                                                          $addColumnIdIfNotSet = false);

        $tiposExemplar = Portabilis_Object_Utils::filterKeyValue($tiposExemplar,
                                                                   'cod_exemplar_tipo',
                                                                   'nm_tipo');
      }
    }

    return $this->insertInArray(null, "Selecione um tipo de exemplar", $tiposExemplar);
  }


  /* retornar um campo select com opções de tipos de exemplar, ex:

    // customizando opcoes
    $selectOptions = array('id' => 'html_element_id', 'value' => $this->ref_cod_exemplar_tipo));
    $helperOptions = array('bibliotecaId' => $this->ref_cod_biblioteca, 'options' => $selectOptions);
    $dynamicSelectMenusHelperInstance->tipoExemplar($helperOptions);


    // ou sem customizar opcoes, usando as opcoes padroes;
    $dynamicSelectMenusHelperInstance->tipoExemplar();
  */
  public function bibliotecaTipoExemplar($options = array()) {

    $defaultOptions           = array('bibliotecaId'  => null,
                                      'options'       => array(),
                                      'tiposExemplar' => array());

    $options                  = $this->mergeOptions($options, $defaultOptions);
    $options['tiposExemplar'] = $this->getOptions($options['bibliotecaId'], $options['tiposExemplar']);

    $defaultSelectOptions     = array('id'             => 'ref_cod_exemplar_tipo',
                                      'label'          => 'Tipo de exemplar',
                                      'tipos_exemplar' => $options['tiposExemplar'],
                                      'value'          => $this->viewInstance->ref_cod_exemplar_tipo,
                                      'callback'       => '',
                                      'duplo'          => false,
                                      'label_hint'     => '',
                                      'input_hint'     => '',
                                      'disabled'       => false,
                                      'required'       => true,
                                      'multiple'       => false);

    $selectOptions = $this->mergeOptions($options['options'], $defaultSelectOptions);
    call_user_func_array(array($this->viewInstance, 'campoLista'), $selectOptions);

    Portabilis_View_Helper_Application::loadJavascript($this->viewInstance, '/modules/DynamicSelectMenus/Assets/Javascripts/DynamicBibliotecaTiposExemplar.js');
  }

}
?>
