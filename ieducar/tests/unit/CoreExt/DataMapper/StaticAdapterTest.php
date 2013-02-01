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
 * @package     CoreExt_DataMapper
 * @subpackage  IntegrationTests
 * @since       Arquivo dispon�vel desde a vers�o 1.1.0
 * @version     $Id$
 */

require_once 'CoreExt/_stub/EntityDataMapper.php';

/**
 * CoreExt_DataMapper_StaticAdapterTest class.
 *
 * Classe com testes para assegurar que a interface de configura��o de adapter
 * de banco de dados est�tica de CoreExt_DataMapper funciona.
 *
 * @author      Eriksen Costa Paix�o <eriksen.paixao_bs@cobra.com.br>
 * @category    i-Educar
 * @license     @@license@@
 * @package     CoreExt_DataMapper
 * @subpackage  IntegrationTests
 * @since       Classe dispon�vel desde a vers�o 1.1.0
 * @version     @@package_version@@
 */
class CoreExt_DataMapper_StaticAdapterTest extends IntegrationBaseTest
{
  protected function setUp()
  {
    parent::setUp();
    CoreExt_DataMapper::setDefaultDbAdapter($this->getDbAdapter());
  }

  protected function tearDown()
  {
    parent::tearDown();
    CoreExt_DataMapper::resetDefaultDbAdapter();
  }

  public function getSetUpOperation()
  {
    return PHPUnit_Extensions_Database_Operation_Factory::NONE();
  }

  /**
   * Esse m�todo precisa ser sobrescrito mas a utilidade dele nesse teste �
   * irrelevante.
   */
  public function getDataSet()
  {
    return $this->createXMLDataSet($this->getFixture('pessoa.xml'));
  }

  public function testAdapterParaNovaInstanciaDeDataMapperEOStatic()
  {
    $entityMapper = new CoreExt_EntityDataMapperStub();
    $this->assertSame($this->getDbAdapter(), $entityMapper->getDbAdapter());

    $entityMapper = new CoreExt_EntityDataMapperStub();
    $this->assertSame($this->getDbAdapter(), $entityMapper->getDbAdapter());
  }

  public function testAdapterNoConstrutorSobrescreveOAdapterStaticPadrao()
  {
    $db = new CustomPdo('sqlite::memory:');
    $entityMapper = new CoreExt_EntityDataMapperStub($db);
    $this->assertSame($db, $entityMapper->getDbAdapter());
  }

  public function testResetarAdapterFazComQueODataMapperInstancieUmNovoAdapter()
  {
    CoreExt_EntityDataMapperStub::resetDefaultDbAdapter();
    $entityMapper = new CoreExt_EntityDataMapperStub();
    $this->assertNotSame($this->getDbAdapter(), $entityMapper->getDbAdapter());
  }
}