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
 * @since     ?
 * @version   $Id$
 */

require_once 'include/clsBase.inc.php';
require_once 'include/clsListagem.inc.php';
require_once 'include/clsBanco.inc.php';
require_once 'include/pmieducar/geral.inc.php';
require_once 'include/modules/clsModulesProfessorTurma.inc.php';
require_once 'lib/Portabilis/String/Utils.php';

require_once 'CoreExt/View/Helper/UrlHelper.php';

/**
 * clsIndexBase class.
 *
 * @author    Lucas Schmoeller das Silva <lucas@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     ?
 * @version   @@package_version@@
 */
class clsIndexBase extends clsBase
{
  function Formular()
  {
    $this->SetTitulo($this->_instituicao . ' i-Educar - Servidor V�nculo Turma');
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
 * @since     ?
 * @version   @@package_version@@
 */
class indice extends clsListagem
{
  var $pessoa_logada;
  var $titulo;
  var $limite;
  var $offset;

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

    $this->servidor_id    = $_GET['ref_cod_servidor'];
    $this->ref_cod_instituicao = $_GET['ref_cod_instituicao'];

    $this->titulo = 'Servidor V�nculo Turma - Listagem';

    // passa todos os valores obtidos no GET para atributos do objeto
    foreach( $_GET AS $var => $val ) {
      $this->$var = ($val === '') ? NULL : $val;
    }

    

    $this->addCabecalhos(array(
      'Ano',
      'Escola',
      'Curso',
      'S�rie',
      'Turma',
      'Fun��o exercida',
      'Tipo de v�nculo'
    ));

    $this->campoOculto('ref_cod_servidor', $this->servidor_id);

    $this->inputsHelper()->dynamic(array('ano', 'instituicao','escola','curso','serie', 'turma'), array('required' => false));

    $resources_funcao = array(  null => 'Selecione',
                                1    => 'Docente',
                                2    => 'Auxiliar/Assistente educacional',
                                3    => 'Profissional/Monitor de atividade complementar',
                                4    => 'Tradutor Int�rprete de LIBRAS',
                                5    => 'Docente titular - coordenador de tutoria (de m�dulo ou disciplina) - EAD',
                                6    => 'Docente tutor (de m�dulo ou disciplina)');

    $options = array('label' => Portabilis_String_Utils::toLatin1('Fun��o exercida'), 'resources' => $resources_funcao, 'value' => $this->funcao_exercida);
    $this->inputsHelper()->select('funcao_exercida', $options);   

    $resources_tipo = array(  null => 'Nenhum',
                              1    => Portabilis_String_Utils::toLatin1('Concursado/efetivo/est�vel'),
                              2    => Portabilis_String_Utils::toLatin1('Contrato tempor�rio'),
                              3    => 'Contrato terceirizado',
                              4    => 'Contrato CLT');

    $options = array('label' => Portabilis_String_Utils::toLatin1('Tipo do v�nculo'), 'resources' => $resources_tipo, 'value' => $this->tipo_vinculo);
    $this->inputsHelper()->select('tipo_vinculo', $options);

    // Paginador
    $this->limite = 20;
    $this->offset = ($_GET['pagina_' . $this->nome]) ?
      $_GET['pagina_' . $this->nome] * $this->limite - $this->limite : 0;

    $obj_vinculo = new clsModulesProfessorTurma();
    $obj_vinculo->setOrderby(' nm_escola, nm_curso, nm_serie, nm_turma ASC');
    $obj_vinculo->setLimite($this->limite, $this->offset);

    if (! isset($this->tipo)) {
      $this->tipo = NULL;
    }

    $lista = $obj_vinculo->lista(
      $this->servidor_id,
      $this->ref_cod_instituicao,
      $this->ano,
      $this->ref_cod_escola,
      $this->ref_cod_curso,
      $this->ref_cod_serie,
      $this->ref_cod_turma,
      $this->funcao_exercida,
      $this->tipo_vinculo
    );

    $total = $obj_vinculo->_total;

    // UrlHelper
    $url  = CoreExt_View_Helper_UrlHelper::getInstance();
    $path = 'educar_servidor_vinculo_turma_det.php';

    // Monta a lista
    if (is_array($lista) && count($lista)) {
      foreach ($lista as $registro) {

        $options = array(
          'query' => array(
            'id' => $registro['id']
        ));

        $this->addLinhas(array(
          $url->l($registro['ano'], $path, $options),
          $url->l($registro['nm_escola'], $path, $options),
          $url->l($registro['nm_curso'], $path, $options),
          $url->l($registro['nm_serie'], $path, $options),
          $url->l($registro['nm_turma'], $path, $options),
          $url->l($resources_funcao[$registro['funcao_exercida']], $path, $options),
          $url->l($resources_tipo[$registro['tipo_vinculo']], $path, $options)
        ));
      }
    }

    $this->addPaginador2('educar_servidor_vinculo_turma_lst.php', $total, $_GET, $this->nome, $this->limite);
    $obj_permissoes = new clsPermissoes();

    if ($obj_permissoes->permissao_cadastra(635, $this->pessoa_logada, 7)) {
      $this->array_botao[]     = 'Novo';
      $this->array_botao_url[] = sprintf(
        'educar_servidor_vinculo_turma_cad.php?ref_cod_servidor=%d&ref_cod_instituicao=%d',
        $this->servidor_id, $this->ref_cod_instituicao
      );
    }

    $this->array_botao[]     = 'Voltar';
    $this->array_botao_url[] = sprintf(
      'educar_servidor_det.php?cod_servidor=%d&ref_cod_instituicao=%d',
      $this->servidor_id, $this->ref_cod_instituicao
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