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

require_once 'CoreExt/DataMapper.php';
require_once 'RegraAvaliacao/Model/RegraRecuperacao.php';
require_once 'App/Model/IedFinder.php';

/**
 * RegraAvaliacao_Model_RegraRecuperacaoDataMapper class.
 *
 * @author      Lucas Schmoeller da Silva <lucas@portabilis.com.br>
 * @category    i-Educar
 * @license     @@license@@
 * @package     RegraAvaliacao
 * @subpackage  Modules
 * @since       ?
 * @version     @@package_version@@
 */
class RegraAvaliacao_Model_RegraRecuperacaoDataMapper extends CoreExt_DataMapper
{
  protected $_entityClass = 'RegraAvaliacao_Model_RegraRecuperacao';
  protected $_tableName   = 'regra_avaliacao_recuperacao';
  protected $_tableSchema = 'modules';

  protected $_attributeMap = array(
    'regraAvaliacao'       => 'regra_avaliacao_id',
    'etapasRecuperadas'    => 'etapas_recuperadas',
    'substituiMenorNota'   => 'substitui_menor_nota',
    'notaMaxima'            => 'nota_maxima'
  );
}