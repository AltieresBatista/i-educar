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
 * @author      Tiago Oliveira Camargo <tiago.camargo@portabilis.com.br>
 * @category    i-Educar
 * @license     @@license@@
 * @package     Serieciasc
 * @subpackage  Modules
 * @since       Arquivo dispon�vel desde a vers�o 1.2.0
 * @version     $Id$
 */

require_once 'CoreExt/Entity.php';

class Ciasc_Model_CodigoAluno extends CoreExt_Entity
{
  protected $_data = array(
    'cod_ciasc'  => NULL,
    'cod_aluno'  => NULL,
    'user'       => NULL,
    'created_at' => NULL,
    'updated_at' => NULL
  );

  public function getDefaultValidatorCollection()
  {
    return array(
      'cod_ciasc' => new CoreExt_Validate_Numeric(),
      'cod_aluno' => new CoreExt_Validate_Numeric(),
      'user'      => new CoreExt_Validate_Numeric()
    );
  }

  public function __construct(array $options = array())
  {
    parent::__construct($options);
    unset($this->_data['id']);
  }
}