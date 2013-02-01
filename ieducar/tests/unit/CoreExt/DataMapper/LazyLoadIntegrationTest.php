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

require_once 'CoreExt/_stub/ParentEntityDataMapper.php';
require_once 'CoreExt/_stub/ChildEntityDataMapper.php';

/**
 * CoreExt_DataMapper_LazyLoadIntegrationTest class.
 *
 * @author      Eriksen Costa Paix�o <eriksen.paixao_bs@cobra.com.br>
 * @category    i-Educar
 * @license     @@license@@
 * @package     CoreExt_DataMapper
 * @subpackage  IntegrationTests
 * @since       Classe dispon�vel desde a vers�o 1.1.0
 * @version     @@package_version@@
 */
class CoreExt_DataMapper_LazyLoadIntegrationTest extends IntegrationBaseTest
{
  /**
   * Cria as tabelas parent e child.
   */
  public function __construct()
  {
    parent::__construct();
    CoreExt_DataMapper::setDefaultDbAdapter($this->getDbAdapter());
    CoreExt_ParentEntityDataMapperStub::createTable($this->getDbAdapter());
    CoreExt_ChildEntityDataMapperStub::createTable($this->getDbAdapter());
  }

  public function setUp()
  {
    parent::setUp();
    CoreExt_DataMapper::setDefaultDbAdapter($this->getDbAdapter());
  }

  public function getDataSet()
  {
    return $this->createXMLDataSet($this->getFixture('parent-child.xml'));
  }

  public function testRecuperaTodosOsRegistros()
  {
    $mapper = new CoreExt_ParentEntityDataMapperStub();
    $found = $mapper->findAll();

    $this->assertTablesEqual(
      $this->getDataSet()
           ->getTable('parent'),
      $this->getConnection()
           ->createDataSet()
           ->getTable('parent')
    );

    $this->assertTablesEqual(
      $this->getDataSet()
           ->getTable('child'),
      $this->getConnection()
           ->createDataSet()
           ->getTable('child')
    );
  }

  public function testLazyLoadUsandoDefinicaoDeDataMapper()
  {
    $definition = array(
      'class' => 'CoreExt_ChildEntityDataMapperStub',
      'file'  => 'CoreExt/_stub/ChildEntityDataMapper.php'
    );

    $parentMapper = new CoreExt_ParentEntityDataMapperStub();
    $parent = $parentMapper->find(1);
    $parent->setReference('filho', $definition);

    $this->assertEquals(1, $parent->filho->id);
    $this->assertEquals('Antunes Jr.', $parent->filho->nome);
  }

  /**
   * Uma refer�ncia NULL para CoreExt_Enum retorna NULL logo no in�cio da
   * l�gica de CoreExt_Entity::_loadReference().
   * @group CoreExt_Entity
   */
  public function testLazyLoadUsandoDefinicaoDeEnumComReferenciaNula()
  {
    $child = new CoreExt_ChildEntityStub(
      array('id' => 3, 'sexo' => 1, 'tipoSanguineo' => NULL)
    );
    $this->assertNull($child->tipoSanguineo);
  }

  /**
   * Refer�ncia 0 � perfeitamente v�lido para um CoreExt_Enum. Se n�o existir o
   * offset no Enum, retorna NULL
   * @group CoreExt_Entity
   */
  public function testLazyLoadUsandoDefinicaoDeEnumComReferenciaZero()
  {
    $child = new CoreExt_ChildEntityStub(
      array('id' => 3, 'sexo' => 1, 'tipoSanguineo' => 0)
    );
    $this->assertNull($child->tipoSanguineo);
  }

  /**
   * Uma refer�ncia NULL � v�lida para as refer�ncias que explicitam a chave
   * null = TRUE.
   * @group CoreExt_Entity
   */
  public function testLazyLoadUsandoDefinicaoDeDataMapperComReferenciaNula()
  {
    $parent = new CoreExt_ParentEntityStub(
      array('id' => 3, 'nome' => 'Paul M.', 'filho' => NULL)
    );
    $this->assertNull($parent->filho);
  }

  /**
   * Refer�ncia 0 em DataMapper for�a o retorno de NULL. Isso � feito em
   * raz�o do HTML n�o suportar um valor NULL e por conta dos validadores
   * client-side legados do i-Educar n�o considerarem "" (string vazia) um
   * valor v�lido para submit.
   * @group CoreExt_Entity
   */
  public function testLazyLoadUsandoDefinicaoDeDataMapperComReferenciaZero()
  {
    $parent = new CoreExt_ParentEntityStub(
      array('id' => 3, 'nome' => 'Paul M.', 'filho' => 0)
    );
    $this->assertNull($parent->filho);
  }

  public function testInsereRegistros()
  {
    $child = new CoreExt_ChildEntityStub(array('nome' => 'Nascimento Jr.'));
    $childMapper = new CoreExt_ChildEntityDataMapperStub();
    $childMapper->save($child);

    $parent = new CoreExt_ParentEntityStub(array('nome' => 'Fernando Nascimento', 'filho' => 3));
    $parentMapper = new CoreExt_ParentEntityDataMapperStub();
    $parentMapper->save($parent);

    $parent = $parentMapper->find(3);
    $child  = $childMapper->find(3);

    $this->assertEquals($child, $parent->filho);
  }

  /**
   * Testa se um CoreExt_Entity retornado por um CoreExt_DataMapper configura
   * a reference e o atributo, com um valor refer�ncia e a inst�ncia,
   * respectivamente.
   * @group Overload
   */
  public function testRegistroRecuperadoConfiguraReferenceParaLazyLoadPosterior()
  {
    $parentMapper = new CoreExt_ParentEntityDataMapperStub();
    $parent = $parentMapper->find(1);
    $this->assertEquals(1, $parent->get('filho'));
    $this->assertType('CoreExt_ChildEntityStub', $parent->filho);
  }
}