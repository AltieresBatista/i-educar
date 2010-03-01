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
 * CoreExt_DataMapper_IntegrationTest class.
 *
 * @author      Eriksen Costa Paix�o <eriksen.paixao_bs@cobra.com.br>
 * @category    i-Educar
 * @license     @@license@@
 * @package     CoreExt_DataMapper
 * @subpackage  IntegrationTests
 * @since       Classe dispon�vel desde a vers�o 1.1.0
 * @version     @@package_version@@
 */
class CoreExt_DataMapper_IntegrationTest extends IntegrationBaseTest
{
  /**
   * Cria a tabela do objeto CoreExt_DataMapper para testes.
   */
  public function __construct()
  {
    parent::__construct();
    CoreExt_EntityDataMapperStub::createTable($this->getDbAdapter());
  }

  protected function setUp()
  {
    parent::setUp();
    CoreExt_DataMapper::resetDefaultDbAdapter();
  }

  public function getDataSet()
  {
    return $this->createXMLDataSet($this->getFixture('pessoa.xml'));
  }

  public function testInicializaInstanciaDefaultDeClsbanco()
  {
    $mapper = new CoreExt_EntityDataMapperStub();
    $this->assertType('clsBanco', $mapper->getDbAdapter());
  }

  public function testRecuperaTodosOsRegistros()
  {
    $mapper = new CoreExt_EntityDataMapperStub($this->getDbAdapter());
    $found = $mapper->findAll();

    $this->assertTablesEqual(
      $this->getDataSet()
           ->getTable('pessoa'),
      $this->getConnection()
           ->createDataSet()
           ->getTable('pessoa')
    );
  }

  public function testRecuperaTodosOsRegistrosSelecionandoColunasExplicitamente()
  {
    $expected = array(
      new CoreExt_EntityStub(array('id' => 1, 'nome' => 'Arnaldo Antunes')),
      new CoreExt_EntityStub(array('id' => 2, 'nome' => 'Marvin Gaye'))
    );

    // Marca como se tivesse sido carregado, para garantir a compara��o
    $expected[0]->markOld();
    $expected[1]->markOld();

    $mapper = new CoreExt_EntityDataMapperStub($this->getDbAdapter());
    $found = $mapper->findAll(array('nome'));

    $this->assertEquals($expected, $found);
  }

  public function testRecuperaRegistroUnico()
  {
    $mapper = new CoreExt_EntityDataMapperStub($this->getDbAdapter());
    $found = $mapper->find(1);

    $expected = new CoreExt_EntityStub(array(
      'id' => 1,
      'nome' => 'Arnaldo Antunes',
      'estadoCivil' => 'solteiro',
      'doador' => TRUE
    ));

    // Marca como se tivesse sido carregado, para garantir a compara��o
    $expected->markOld();

    $this->assertEquals($expected, $found);
    $this->assertFalse($found->isNew());
  }

  public function testRecuperaRegistroComParametrosWhere()
  {
    $mapper = new CoreExt_EntityDataMapperStub($this->getDbAdapter());
    $found = $mapper->findAll(array(), array('estadoCivil' => 'solteiro'));

    $expected = new CoreExt_EntityStub(array(
      'id' => 1,
      'nome' => 'Arnaldo Antunes',
      'estadoCivil' => 'solteiro',
      'doador' => TRUE
    ));

    // Marca como se tivesse sido carregado, para garantir a compara��o
    $expected->markOld();

    $this->assertEquals($expected, $found[0]);
  }

  public function testCadastraNovoRegistro()
  {
    $mapper = new CoreExt_EntityDataMapperStub($this->getDbAdapter());

    $entity = new CoreExt_EntityStub(array(
      'nome' => 'Fernando Nascimento',
      'estadoCivil' => 'solteiro',
      'doador' => TRUE
    ));

    $mapper->save($entity);

    $this->assertTablesEqual(
      $this->createXMLDataSet($this->getFixture('pessoa-depois-salvo.xml'))
           ->getTable('pessoa'),
      $this->getConnection()
           ->createDataSet()
           ->getTable('pessoa')
    );
  }

  public function testAtualizaRegistroCasoAChavePrimariaEstejaSetada()
  {
    $mapper = new CoreExt_EntityDataMapperStub($this->getDbAdapter());

    $entity = new CoreExt_EntityStub(array(
      'id' => 2,
      'nome' => 'Marvin Gaye',
      'estadoCivil' => 'solteiro',
      'doador' => NULL
    ));

    $mapper->save($entity);

    $this->assertTablesEqual(
      $this->createXMLDataSet($this->getFixture('pessoa-depois-atualizado.xml'))
           ->getTable('pessoa'),
      $this->getConnection()
           ->createDataSet()
           ->getTable('pessoa')
    );
  }

  /**
   * @expectedException Exception
   */
  public function testCadastraNovoRegistroLancaExcecaoNaoVerificadaNosErrosDeValidacao()
  {
    $mapper = new CoreExt_EntityDataMapperStub($this->getDbAdapter());

    $entity = new CoreExt_EntityStub(array(
      'nome' => '',
      'estadoCivil' => 'solteiro',
      'doador' => TRUE
    ));

    $mapper->save($entity);
  }

  public function testAtualizaRegistro()
  {
    $mapper = new CoreExt_EntityDataMapperStub($this->getDbAdapter());
    $entity = $mapper->find(2);
    $entity->estadoCivil = 'solteiro';
    $entity->doador = '';

    $mapper->save($entity);

    $this->assertTablesEqual(
      $this->createXMLDataSet($this->getFixture('pessoa-depois-atualizado.xml'))
           ->getTable('pessoa'),
      $this->getConnection()
           ->createDataSet()
           ->getTable('pessoa')
    );
  }

  public function testApagaRegistroUsandoInstanciaDeEntity()
  {
    $mapper = new CoreExt_EntityDataMapperStub($this->getDbAdapter());
    $entity = $mapper->find(1);

    $mapper->delete($entity);

    $this->assertTablesEqual(
      $this->createXMLDataSet($this->getFixture('pessoa-depois-removido.xml'))
           ->getTable('pessoa'),
      $this->getConnection()
           ->createDataSet()
           ->getTable('pessoa')
    );
  }

  public function testApagaRegistroUsandoValorInteiro()
  {
    $mapper = new CoreExt_EntityDataMapperStub($this->getDbAdapter());
    $mapper->delete(1);

    $this->assertTablesEqual(
      $this->createXMLDataSet($this->getFixture('pessoa-depois-removido.xml'))
           ->getTable('pessoa'),
      $this->getConnection()
           ->createDataSet()
           ->getTable('pessoa')
    );
  }
}