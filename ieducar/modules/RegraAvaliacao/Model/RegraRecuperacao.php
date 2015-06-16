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
 * @author      Lucas Schmoeller da Silva <lucas@portabilis.com.br>
 * @category    i-Educar
 * @license     @@license@@
 * @package     RegraAvaliacao
 * @subpackage  Modules
 * @since       ?
 * @version     $Id$
 */

require_once 'CoreExt/Entity.php';
require_once 'App/Model/IedFinder.php';

/**
 * RegraAvaliacao_Model_RegraRecuperacao class.
 *
 * @author      Lucas Schmoeller da Silva <lucas@portabilis.com.br>
 * @category    i-Educar
 * @license     @@license@@
 * @package     RegraAvaliacao
 * @subpackage  Modules
 * @since       ?
 * @version     @@package_version@@
 */
class RegraAvaliacao_Model_RegraRecuperacao extends CoreExt_Entity
{
  protected $_data = array(
    'regraAvaliacao' => NULL,
    'descricao'            => NULL,
    'etapasRecuperadas'          => NULL,
    'substituiMenorNota'          => NULL,
    'media'          => NULL,
    'notaMaxima'          => NULL
  );

  protected $_dataTypes = array(
    'substituiMenorNota' => 'boolean',
    'media' => 'numeric',
    'nota_maxima' => 'numeric'
  );

  protected $_references = array(
    'regraAvaliacao' => array(
      'value' => NULL,
      'class' => 'RegraAvaliacao_Model_RegraDataMapper',
      'file'  => 'RegraAvaliacao/Model/RegraDataMapper.php'
    )
  );

  /**
   * @see CoreExt_Entity#getDataMapper()
   */
  public function getDataMapper()
  {
    if (is_null($this->_dataMapper)) {
      require_once 'RegraAvaliacao/Model/RegraRecuperacaoDataMapper.php';
      $this->setDataMapper(new RegraAvaliacao_Model_RegraRecuperacaoDataMapper());
    }
    return parent::getDataMapper();
  }

  public function getEtapas(){
    return explode(';', $this->get('etapasRecuperadas'));
  }

  public function getLastEtapa(){
    return max($this->getEtapas());
  }

  /**
   * @see CoreExt_Entity_Validatable#getDefaultValidatorCollection()
   * @todo Implementar validador que retorne um String ou Numeric, dependendo
   *   do valor do atributo (assim como validateIfEquals().
   * @todo Implementar validador que aceite um valor de compara��o como
   *   alternativa a uma chave de atributo. (COMENTADO ABAIXO)
   */
  public function getDefaultValidatorCollection()
  {
    return array(
      'descricao' => new CoreExt_Validate_String(array('min' => 1, 'max' => 25)),
      'etapasRecuperadas' => new CoreExt_Validate_String(array('min' => 1, 'max' => 25)),
      'media' => new CoreExt_Validate_Numeric(array('min' => 0.001, 'max' => 9999.0)),
      'notaMaxima' => new CoreExt_Validate_Numeric(array('min' => 0.001, 'max' => 9999.0))
    );
  }

  public function __toString()
  {
    return $this->descricao;
  }
}