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
 * @author    Lucas Schmoeller da Silva <lucas@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Arquivo dispon�vel desde a vers�o 1.0.0
 * @version   $Id$
 */

require_once "include/clsBase.inc.php";
require_once "include/clsDetalhe.inc.php";
require_once "include/clsBanco.inc.php";
require_once "include/pmieducar/geral.inc.php";

/**
 * clsIndexBase class.
 *
 * @author    Lucas Schmoeller da Silva <lucas@portabilis.com.br>
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
    $this->SetTitulo($this->_instituicao . ' i-Educar - Vagas por s�rie');
    $this->processoAp = 21253;
    $this->addEstilo("localizacaoSistema");
  }
}

/**
 * indice class.
 *
 * @author    Lucas Schmoeller da Silva <lucas@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Classe dispon�vel desde a vers�o 1.0.0
 * @version   @@package_version@@
 */
class indice extends clsDetalhe
{
  var $titulo;

  var $cod_serie_vaga;

  function Gerar()
  {
    @session_start();
    $this->pessoa_logada = $_SESSION['id_pessoa'];
    session_write_close();

    $this->titulo = 'Vagas por s�rie - Detalhe';
    $this->addBanner('imagens/nvp_top_intranet.jpg', 'imagens/nvp_vert_intranet.jpg',
      'Intranet');

    $this->cod_serie_vaga = $_GET['cod_serie_vaga'];

    $tmp_obj = new clsPmieducarSerieVaga($this->cod_serie_vaga);

    $registro = $tmp_obj->detalhe();

    if (!$registro) {
      header('Location: educar_serie_vaga_lst.php');
      die();
    }

    if (class_exists('clsPmieducarSerie')) {
      $obj_serie = new clsPmieducarSerie($registro['ref_cod_serie']);
      $det_serie = $obj_serie->detalhe();
      $registro['ref_ref_cod_serie'] = $det_serie['nm_serie'];
    }
    else {
      $registro['ref_ref_cod_serie'] = 'Erro na geracao';
      echo "<!--\nErro\nClasse nao existente: clsPmieducarSerie\n-->";
    }

    // Dados do curso
    $obj_ref_cod_curso = new clsPmieducarCurso($registro['ref_cod_curso']);
    $det_ref_cod_curso = $obj_ref_cod_curso->detalhe();
    $registro['ref_cod_curso'] = $det_ref_cod_curso['nm_curso'];

    // Dados da escola
    $obj_ref_cod_escola = new clsPmieducarEscola($registro['ref_cod_escola']);
    $det_ref_cod_escola = $obj_ref_cod_escola->detalhe();
    $registro['ref_cod_escola'] = $det_ref_cod_escola['nome'];


    if ($registro['ano']) {
      $this->addDetalhe(array('Ano', $registro['ano']));
    }

    if ($registro['ref_cod_escola']) {
      $this->addDetalhe(array('Escola', $registro['ref_cod_escola']));
    }

    if ($registro['ref_cod_curso']) {
      $this->addDetalhe(array('Curso', $registro['ref_cod_curso']));
    }

    if ($registro['ref_ref_cod_serie']) {
      $this->addDetalhe(array('S�rie', $registro['ref_ref_cod_serie']));
    }

    if ($registro['vagas']) {
      $this->addDetalhe(array('Vagas', $registro['vagas']));
    }

    $obj_permissoes = new clsPermissoes();

    if ($obj_permissoes->permissao_cadastra(21253, $this->pessoa_logada, 7)) {
      $this->url_novo   = 'educar_serie_vaga_cad.php';
      $this->url_editar = sprintf('educar_serie_vaga_cad.php?cod_serie_vaga=%d', $this->cod_serie_vaga);
    }

    $this->url_cancelar = 'educar_serie_vaga_lst.php';
    $this->largura      = '100%';

    $localizacao = new LocalizacaoSistema();
    $localizacao->entradaCaminhos( array(
         $_SERVER['SERVER_NAME']."/intranet" => "In&iacute;cio",
         "educar_index.php"                  => "i-Educar - Escola",
         ""                                  => "Detalhe de vagas da s�rie"
    ));
    $this->enviaLocalizacao($localizacao->montar());
  }
}

// Instancia objeto de p�gina
$pagina = new clsIndexBase();

// Instancia objeto de conte�do
$miolo = new indice();

// Atribui o conte�do �  p�gina
$pagina->addForm($miolo);

// Gera o c�digo HTML
$pagina->MakeAll();