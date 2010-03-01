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
 * @author      Eriksen Costa Paix�o <eriksen.paixao_bs@cobra.com.br>
 * @category    i-Educar
 * @license     @@license@@
 * @package     TabelaArredondamento
 * @subpackage  Modules
 * @since       Arquivo dispon�vel desde a vers�o 1.1.0
 * @version     $Id$
 */

require_once 'CoreExt/Entity.php';
require_once 'App/Model/IedFinder.php';

/**
 * TabelaArredondamento_Model_Tabela class.
 *
 * @author      Eriksen Costa Paix�o <eriksen.paixao_bs@cobra.com.br>
 * @category    i-Educar
 * @license     @@license@@
 * @package     TabelaArredondamento
 * @subpackage  Modules
 * @since       Classe dispon�vel desde a vers�o 1.1.0
 * @version     @@package_version@@
 */
class TabelaArredondamento_Model_Tabela extends CoreExt_Entity
{
  protected $_data = array(
    'instituicao'    => NULL,
    'nome'           => NULL,
    'tipoNota'       => NULL
  );

  protected $_references = array(
    'tipoNota' => array(
      'value' => 1,
      'class' => 'RegraAvaliacao_Model_Nota_TipoValor',
      'file'  => 'RegraAvaliacao/Model/Nota/TipoValor.php'
    )
  );

  /**
   * Precis�o decimal do valor da nota.
   * @var int
   */
  protected $_precision = 3;

  /**
   * @var array
   */
  protected $_tabelaValores = array();

  /**
   * @see CoreExt_Entity#getDataMapper()
   */
  public function getDataMapper()
  {
    if (is_null($this->_dataMapper)) {
      require_once 'TabelaArredondamento/Model/TabelaDataMapper.php';
      $this->setDataMapper(new TabelaArredondamento_Model_TabelaDataMapper());
    }
    return parent::getDataMapper();
  }

  /**
   * @see CoreExt_Entity_Validatable#getDefaultValidatorCollection()
   */
  public function getDefaultValidatorCollection()
  {
    $instituicoes = array_keys(App_Model_IedFinder::getInstituicoes());

    // Tipo nota
    $tipoNota = RegraAvaliacao_Model_Nota_TipoValor::getInstance();

    return array(
      'instituicao' => new CoreExt_Validate_Choice(array('choices' => $instituicoes)),
      'nome'        => new CoreExt_Validate_String(array('min' => 5, 'max' => 50)),
      'tipoNota'    => new CoreExt_Validate_Choice(array('choices' => $tipoNota->getKeys()))
    );
  }

  /**
   * Arredonda a nota de acordo com a tabela de valores da inst�ncia atual.
   *
   * @param $value
   * @return mixed
   */
  public function round($value)
  {
    if (0 > $value || 10 < $value) {
      require_once 'CoreExt/Exception/InvalidArgumentException.php';
      throw new CoreExt_Exception_InvalidArgumentException('O valor para '
                . 'arredondamento deve estar entre 0 e 10.');
    }

    if (0 == count($this->_tabelaValores)) {
      $this->_tabelaValores = $this->getDataMapper()->findTabelaValor($this);
    }

    // Multiplicador para transformar os n�meros em uma escala inteira.
    $scale = pow(10, $this->_precision);

    // Escala o valor para se tornar compar�vel
    $value = $this->getFloat($value) * $scale;

    $return = 0;
    foreach ($this->_tabelaValores as $tabelaValor) {
      if ($value >= ($tabelaValor->valorMinimo * $scale) &&
          $value <= ($tabelaValor->valorMaximo * $scale)) {
        $return = $tabelaValor->nome;
        break;
      }
      $return = $tabelaValor->nome;
    }

    return $return;
  }

  /**
   * M�todo finder para TabelaArredondamento_Model_TabelaValor. Wrapper simples
   * para o mesmo m�todo de TabelaArredondamento_Model_TabelaDataMapper.
   *
   * @return array
   */
  public function findTabelaValor()
  {
    if (0 == count($this->_tabelaValores)) {
      $this->_tabelaValores = $this->getDataMapper()->findTabelaValor($this);
    }
    return $this->_tabelaValores;
  }

  /**
   * @see CoreExt_Entity#__toString()
   */
  public function __toString()
  {
    return $this->nome;
  }
}