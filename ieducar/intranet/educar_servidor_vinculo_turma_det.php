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
 * @author    Lucas Schmoeller das Silva <lucas@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Arquivo dispon�vel desde a vers�o 1.0.0
 * @version   $Id$
 */

require_once 'include/clsBase.inc.php';
require_once 'include/clsDetalhe.inc.php';
require_once 'include/clsBanco.inc.php';
require_once 'include/pmieducar/geral.inc.php';
require_once 'include/modules/clsModulesProfessorTurma.inc.php';

/**
 * clsIndexBase class.
 *
 * @author    Lucas Schmoeller das Silva <lucas@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Classe dispon�vel desde a vers�o 1.0.0
 * @version   @@package_version@@
 */
class clsIndexBase extends clsBase
{
  function Formular()
  {
    $this->SetTitulo($this->_instituicao . ' i-Educar - Servidor Forma��o');
    $this->processoAp = 635;
  }
}

/**
 * indice class.
 *
 * @author    Lucas Schmoeller das Silva <lucas@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Classe dispon�vel desde a vers�o 1.0.0
 * @version   @@package_version@@
 */
class indice extends clsDetalhe
{
  var $titulo;

  var $id;
  var $ano;
  var $servidor_id;
  var $funcao_exercida;
  var $tipo_vinculo;
  
  var $ref_cod_instituicao;
  var $ref_cod_escola;
  var $ref_cod_curso;
  var $ref_cod_serie;
  var $ref_cod_turma;

  function Gerar()
  {
    @session_start();
    $this->pessoa_logada = $_SESSION['id_pessoa'];
    session_write_close();

    $this->titulo = 'Servidor V�nculo Turma - Detalhe';
    

    $this->id = $_GET['id'];

    $tmp_obj = new clsModulesProfessorTurma($this->id);
    $registro = $tmp_obj->detalhe();

    if (! $registro) {
      header('Location: educar_servidor_professor_vinculo_lst.php');
      die();
    }    

    $resources_funcao = array(  null => 'Selecione',
                                1    => 'Docente',
                                2    => 'Auxiliar/Assistente educacional',
                                3    => 'Profissional/Monitor de atividade complementar',
                                4    => 'Tradutor Int�rprete de LIBRAS',
                                5    => 'Docente titular - coordenador de tutoria (de m�dulo ou disciplina) - EAD',
                                6    => 'Docente tutor (de m�dulo ou disciplina)');

    $resources_tipo = array(  null => 'Selecione',
                              1    => 'Concursado/efetivo/est�vel',
                              2    => 'Contrato tempor�rio',
                              3    => 'Contrato terceirizado',
                              4    => 'Contrato CLT');    

    if ($registro['nm_escola']) {
      $this->addDetalhe(array('Escola', $registro['nm_escola']));
    }

    if ($registro['nm_curso']) {
      $this->addDetalhe(array('Curso', $registro['nm_curso']));
    }

    if ($registro['nm_serie']) {
      $this->addDetalhe(array('S�rie', $registro['nm_serie']));
    }

    if ($registro['nm_turma']) {
      $this->addDetalhe(array('Turma', $registro['nm_turma']));
    }

    if ($registro['funcao_exercida']) {
      $this->addDetalhe(array('Fun��o exercida', $resources_funcao[$registro['funcao_exercida']]));
    }

    if ($registro['tipo_vinculo']) {
      $this->addDetalhe(array('Tipo de v�nculo', $resources_tipo[$registro['tipo_vinculo']]));
    }

    $sql = 'SELECT nome 
            FROM modules.professor_turma_disciplina 
            INNER JOIN modules.componente_curricular cc ON (cc.id = componente_curricular_id)
            WHERE professor_turma_id = $1
            ORDER BY nome';

    $disciplinas = '';
    
    $resources = Portabilis_Utils_Database::fetchPreparedQuery($sql, array( 'params' => array($this->id) ));

    foreach ($resources as $reg) {      
        $disciplinas .= '<span style="background-color: #ccdce6; padding: 2px; border-radius: 3px;"><b>'.$reg['nome'].'</b></span> ';
    }

    if ($disciplinas != '') {
      $this->addDetalhe(array('Disciplinas', $disciplinas));
    }
    
    $obj_permissoes = new clsPermissoes();

    if ($obj_permissoes->permissao_cadastra(635, $this->pessoa_logada, 7)) {
      $this->url_novo = sprintf(
        'educar_servidor_vinculo_turma_cad.php?ref_cod_instituicao=%d&ref_cod_servidor=%d',
        $registro['instituicao_id'], $registro['servidor_id']
      );

      $this->url_editar = sprintf(
        'educar_servidor_vinculo_turma_cad.php?id=%d&ref_cod_instituicao=%d&ref_cod_servidor=%d',
        $registro['id'], $registro['instituicao_id'], $registro['servidor_id']
      );

      $this->array_botao[] = 'Copiar v�nculo';
      $this->array_botao_url_script[] = sprintf(
        'go("educar_servidor_vinculo_turma_cad.php?id=%d&ref_cod_instituicao=%d&ref_cod_servidor=%d&copia");',
        $registro['id'], $registro['instituicao_id'], $registro['servidor_id']
      );

      "go(\"educar_servidor_vinculo_turma_copia_cad.php?{$get_padrao}\");"; 
    }

    $this->url_cancelar = sprintf(
      'educar_servidor_vinculo_turma_lst.php?ref_cod_servidor=%d&ref_cod_instituicao=%d',
      $registro['servidor_id'], $registro['instituicao_id']
    );

    $this->largura = '100%';
  }
}

// Instancia objeto de p�gina
$pagina = new clsIndexBase();

// Instancia objeto de conte�do
$miolo = new indice();

// Atribui o conte�do � p�gina
$pagina->addForm($miolo);

// Gera o c�digo HTML
$pagina->MakeAll();