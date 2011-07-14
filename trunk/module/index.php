<?php

#error_reporting(E_ALL);
#ini_set("display_errors", 1);

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
 * Cria e configura um front controller para encaminhar as requisi��es para
 * page controllers especializados no diret�rio modules/.
 *
 * @author    Eriksen Costa Paix�o <eriksen.paixao_bs@cobra.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   Modules
 * @since     Arquivo dispon�vel desde a vers�o 1.1.0
 * @version   $Id$
 */

require_once '../includes/bootstrap.php';
require_once 'include/clsBanco.inc.php';
require_once 'App/Model/IedFinder.php';
require_once 'CoreExt/View/Helper/UrlHelper.php';
require_once 'CoreExt/Controller/Request.php';
require_once 'CoreExt/Controller/Front.php';
require_once 'CoreExt/DataMapper.php';

try
{
  // Objeto de requisi��o
  $request = new CoreExt_Controller_Request();

  // Helper de URL. Auxilia para criar uma URL no formato http://www.example.org/module
  $url = CoreExt_View_Helper_UrlHelper::getInstance();
  $url = $url->url($request->get('REQUEST_URI'), array('components' => CoreExt_View_Helper_UrlHelper::URL_HOST));

  // Configura o baseurl da request
  $request->setBaseurl(sprintf('%s/module', $url));

  // Configura o DataMapper para usar uma inst�ncia de clsBanco com fetch de resultados
  // usando o tipo FETCH_ASSOC
  CoreExt_DataMapper::setDefaultDbAdapter(new clsBanco(array('fetchMode' => clsBanco::FETCH_ASSOC)));

  // Inicia o Front Controller
  $frontController = CoreExt_Controller_Front::getInstance();
  $frontController->setRequest($request);

  // Configura o caminho aonde os m�dulos est�o instalados
  $frontController->setOptions(
    array('basepath' => PROJECT_ROOT . DS . 'modules')
  );
  $frontController->dispatch();

  // Resultado
  print $frontController->getViewContents();
}
catch (Exception $e) 
{
  echo "<html><head><link rel='stylesheet' type='text/css' href='styles/reset.css'><link rel='stylesheet' type='text/css' href='styles/portabilis.css'><link rel='stylesheet' type='text/css' href='styles/min-portabilis.css'></head>";
  echo "<body><div id='error'><h1>Erro inesperado</h1><p class='explication'>Descupe-nos ocorreu algum erro no sistema, <strong>por favor tente novamente mais tarde</strong></p><ul class='unstyled'><li><a href='/'>- Voltar para o sistema</a></li><li>- Tentou mais de uma vez e o erro persiste ? Por favor, <a target='_blank' href='http://www.portabilis.com.br/site/suporte'>solicite suporte</a> ou envie um email para suporte@portabilis.com.br</li></ul><div id='detail'><p><strong>Detalhes:</strong> {$e->getMessage()}</p></div></div></body></html>";
}
