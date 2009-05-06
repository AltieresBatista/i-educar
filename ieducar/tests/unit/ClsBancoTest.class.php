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
 * clsBancoTest class.
 *
 * @author      Eriksen Costa Paix�o <eriksen.paixao_bs@cobra.com.br>
 * @license     http://creativecommons.org/licenses/GPL/2.0/legalcode.pt  CC GNU GPL
 * @package     Test
 * @subpackage  UnitTest
 * @since       Classe dispon�vel desde a vers�o 1.0.1
 * @version     $Id$
 */

require_once realpath(dirname(__FILE__) . '/../') . '/UnitBaseTest.class.php';
require_once 'include/pmieducar/clsPmieducarClienteSuspensao.inc.php';

class ClsBancoTest extends UnitBaseTest {

  public function testDoCountFromObj() {
    $db = new clsBanco();
    $db->Conecta();

    $obj = new clsPmieducarClienteSuspensao();
    $this->assertNotEquals(TRUE, is_null($db->doCountFromObj($obj)));
  }

  public function testConexao() {
    $string = 'host=localhost dbname=ieducardb user=ieducaruser password=password port=5432';

    $db = new clsBanco();
    $db->setHost('localhost');
    $db->setDbname('ieducardb');
    $db->setUser('ieducaruser');
    $db->setPassword('password');
    $db->setPort('5432');

    $db->FraseConexao();
    $stringCompare = $db->getFraseConexao();
    $this->assertEquals($string, $stringCompare);

    $db->Conecta();
    $this->assertTrue((bool)$db->bLink_ID);
  }

}