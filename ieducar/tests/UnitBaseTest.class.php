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


/*
 * Faz require do bootstrap para que as mesmas configura��es do ambiente
 * (conex�o com o banco de dados, t�tulos, configura��es do PHP), sejam
 * utilizadas pelos unit test para evitar discrep�ncias no comportamento.
 */
require_once realpath(dirname(__FILE__) . '/../') . '/includes/bootstrap.php';

chdir(realpath(dirname(__FILE__) . '/../') . '/intranet');
require_once 'PHPUnit/Framework.php';
require_once 'include/clsBanco.inc.php';


/**
 * UnitBaseTest abstract class.
 *
 * Muda o diret�rio atual para que os testes possam ser facilmente invocados
 * em qualquer subdiret�rio do sistema.
 *
 * Abstrai o PHPUnit, diminuindo a depend�ncia de seu uso.
 *
 * @author   Eriksen Costa Paix�o <eriksen.paixao_bs@cobra.com.br>
 * @license  http://creativecommons.org/licenses/GPL/2.0/legalcode.pt  CC GNU GPL
 * @package  Test
 * @since    Classe dispon�vel desde a vers�o 1.0.1
 * @version  $Id$
 */
abstract class UnitBaseTest extends PHPUnit_Framework_TestCase {}