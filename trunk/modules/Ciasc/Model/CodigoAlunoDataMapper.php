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
 * @author     Tiago Oliveira Camargo <tiago.camargo@portabilis.com.br>
 * @category    i-Educar
 * @license     @@license@@
 * @package     serieciasc
 * @subpackage  Modules
 * @since       Arquivo dispon�vel desde a vers�o 1.2.0
 * @version     $Id$
 */

require_once 'Ciasc/Model/CodigoAluno.php';
require_once 'CoreExt/DataMapper.php';

class Ciasc_Model_CodigoAlunoDataMapper extends CoreExt_DataMapper
{
  protected $_entityClass = 'Ciasc_Model_CodigoAluno';
  protected $_tableSchema = 'serieciasc';
  protected $_tableName   = 'aluno_cod_aluno';

  protected $_primaryKey = array(
    'cod_aluno', 'cod_ciasc'
  );

  public function __construct(clsBanco $db = NULL)
  {
    $this->_attributeMap = array(
        'cod_ciasc'  => 'cod_ciasc',
        'cod_aluno'  => 'cod_aluno',
        'user'    => 'user_id',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at'
    );
    parent::__construct($db);
  }
}