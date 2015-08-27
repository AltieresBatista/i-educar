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
 * @package     TabelaArredondamento
 * @subpackage  Modules
 * @since       Arquivo dispon�vel desde a vers�o 1.1.0
 * @version     $Id$
 */

require_once 'Core/Controller/Page/EditController.php';
require_once 'TabelaArredondamento/Model/TabelaDataMapper.php';
require_once 'TabelaArredondamento/Model/TabelaValor.php';
require_once 'TabelaArredondamento/Model/TipoArredondamentoMedia.php';

/**
 * EditController class.
 *
 * @author      Eriksen Costa Paix�o <eriksen.paixao_bs@cobra.com.br>
 * @category    i-Educar
 * @license     @@license@@
 * @package     TabelaArredondamento
 * @subpackage  Modules
 * @since       Classe dispon�vel desde a vers�o 1.1.0
 * @version     @@package_version@@
 */
class EditController extends Core_Controller_Page_EditController
{
  protected $_dataMapper        = 'TabelaArredondamento_Model_TabelaDataMapper';
  protected $_titulo            = 'Cadastro de tabela de arredondamento de notas';
  protected $_processoAp        = 949;
  protected $_nivelAcessoOption = App_Model_NivelAcesso::INSTITUCIONAL;
  protected $_saveOption        = TRUE;
  protected $_deleteOption      = FALSE;

  protected $_formMap = array(
    'instituicao' => array(
      'label' => 'Institui��o',
      'help'  => ''
    ),
    'nome' => array(
      'label'  => 'Nome',
      'help'   => 'Um nome para a tabela. Exemplo: "<em>Tabela gen�rica de conceitos</em>".'
    ),
    'tipoNota' => array(
      'label'  => 'Tipo de nota',
      'help'   => ''
    ),
    'valor_nome' => array(
      'label'  => 'R�tulo da nota:',
      'help'   => 'Exemplos: A, B, C (conceituais)<br />
                  <b>6,5<b>, <b>7,5<b> (num�ricas)'
    ),
    'valor_descricao' => array(
      'label'  => '<span style="padding-left: 10px"></span>Descri��o:',
      'help'   => 'Exemplos: Bom, Regular, Em Processo.'
    ),
    'valor_valor_minimo' => array(
      'label'  => '<span style="padding-left: 10px"></span>Valor m�nimo:',
      'help'   => 'O valor num�rico m�nimo da nota.'
    ),
    'valor_valor_maximo' => array(
      'label'  => '<span style="padding-left: 10px"></span>Valor m�ximo:',
      'help'   => 'O valor num�rico m�ximo da nota.'
    ),
    'acao' => array(
      'label'  => '<span style="padding-left: 10px"></span>A��o:',
      'help'   => 'A a��o de arredondamento da nota.'
    ),
    'casa_decimal' => array(
      'label'  => '<span style="padding-left: 10px"></span>Casa decimal:',
      'help'   => 'A casa decimal exata para qual a nota deve ser arredondada.'
    ),
    'casa_decimal_exata' => array(
      'label'  => '<span style="padding-left: 10px"></span>Casa decimal exata:',
      'help'   => 'A casa decimal a ser arredondada.'
    )
  );

  /**
   * Array de inst�ncias TabelaArredondamento_Model_TabelaValor.
   * @var array
   */
  protected $_valores = array();

  /**
   * Setter.
   * @param array $valores
   * @return Core_Controller_Page_Abstract Prov� interface flu�da
   */
  protected function _setValores(array $valores = array())
  {
    foreach ($valores as $key => $valor) {
      $this->_valores[$valor->id] = $valor;
    }
    return $this;
  }

  /**
   * Getter.
   * @return array
   */
  protected function _getValores()
  {
    return $this->_valores;
  }

  /**
   * Getter
   * @param int $id
   * @return TabelaArredondamento_Model_TabelaValor
   */
  protected function _getValor($id)
  {
    return isset($this->_valores[$id]) ? $this->_valores[$id] : NULL;
  }

  /**
   * @see Core_Controller_Page_EditController#_preConstruct()
   * @todo Intera��o com a API est� errada. Isso j� � feito em _initNovo()
   *   na superclasse. VER.
   */
  protected function _preConstruct()
  {
    if (isset($this->getRequest()->id) && 0 < $this->getRequest()->id) {
      $this->setEntity($this->getDataMapper()->find($this->getRequest()->id));
      $this->_setValores($this->getDataMapper()->findTabelaValor($this->getEntity()));
    }
  }

  function _preRender(){

    parent::_preRender();

    Portabilis_View_Helper_Application::loadJavascript($this, '/modules/RegraAvaliacao/Assets/Javascripts/TabelaArredondamento.js');

    Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

    $nomeMenu = $this->getRequest()->id == null ? "Cadastrar" : "Editar";
    $localizacao = new LocalizacaoSistema();
    $localizacao->entradaCaminhos( array(
         $_SERVER['SERVER_NAME']."/intranet" => "In&iacute;cio",
         "educar_index.php"                  => "i-Educar - Escola",
         ""        => "$nomeMenu tabela de arredondamento"
    ));
    $this->enviaLocalizacao($localizacao->montar());
  }

  /**
   * @see clsCadastro#Gerar()
   */
  public function Gerar()
  {
    $this->campoOculto('id', $this->getEntity()->id);

    // Institui��o
    $instituicoes = App_Model_IedFinder::getInstituicoes();
    $this->campoLista('instituicao', $this->_getLabel('instituicao'),
      $instituicoes, $this->getEntity()->instituicao);

    // Nome
    $this->campoTexto('nome', $this->_getLabel('nome'), $this->getEntity()->nome,
      40, 50, TRUE, FALSE, FALSE, $this->_getHelp('nome'));

    // Tipo de nota
    $notaTipoValor = RegraAvaliacao_Model_Nota_TipoValor::getInstance();
    $notaTipos = $notaTipoValor->getEnums();
    unset($notaTipos[RegraAvaliacao_Model_Nota_TipoValor::NENHUM]);

    if ($this->getEntity()->id!='')
      $this->campoTexto('tipNota',$this->_getLabel('tipoNota'),$notaTipos[$this->getEntity()->get('tipoNota')],40,40,false,false,false,'','','','',true);
    else
      $this->campoRadio('tipoNota', $this->_getLabel('tipoNota'), $notaTipos,
        $this->getEntity()->get('tipoNota'), '', $this->_getHelp('tipoNota'));

    // Parte condicional
    if (!$this->getEntity()->isNew()) {
      // Quebra
      $this->campoQuebra();
      if (RegraAvaliacao_Model_Nota_TipoValor::CONCEITUAL == $this->getEntity()->get('tipoNota')) {
        $this->carregaCamposNotasConceituais();
      }elseif(RegraAvaliacao_Model_Nota_TipoValor::NUMERICA == $this->getEntity()->get('tipoNota')){
        $this->carregaCamposNotasNumericas();
      }

      // Quebra
      $this->campoQuebra();
    }
  }

  private function carregaCamposNotasConceituais(){
    // Ajuda
    $help = 'Caso seja necess�rio adicionar mais notas, '
          . 'salve o formul�rio. Automaticamente 3 campos '
          . 'novos ficar�o dispon�veis.<br /><br />';

    $this->campoRotulo('__help1', '<strong>Notas para arredondamento</strong><br />', $help, FALSE, '', '');

    // Cria campos para a postagem de notas
    $valores = $this->getDataMapper()->findTabelaValor($this->getEntity());

    for ($i = 0, $loop = count($valores); $i < ($loop == 0 ? 5 : $loop + 3); $i++) {
      $valorNota = $valores[$i];

      $valor_label            = sprintf("valor[label][%d]", $i);
      $valor_id               = sprintf("valor[id][%d]", $i);
      $valor_nome             = sprintf("valor[nome][%d]", $i);
      $valor_descricao        = sprintf("valor[descricao][%d]", $i);
      $valor_valor_minimo     = sprintf("valor[valor_minimo][%d]", $i);
      $valor_valor_maximo     = sprintf("valor[valor_maximo][%d]", $i);
      $valor_tipo_recuperacao = sprintf("valor[acao][%d]", $i);

      $this->campoRotulo($valor_label, 'Arredondamento ' . ($i + 1),
        $this->_getLabel(''), TRUE);

      // Id
      $this->campoOculto($valor_id, $valorNota->id);

      // Nome
      $this->campoTexto($valor_nome, $this->_getLabel('valor_nome'),
        $valorNota->nome, 5, 5, FALSE, FALSE, TRUE, $this->_getHelp('valor_nome'));

      // Descri��o (se conceitual)
      $this->campoTexto($valor_descricao, $this->_getLabel('valor_descricao'),
        $valorNota->descricao, 15, 25, FALSE, FALSE, TRUE,
        $this->_getHelp('valor_descricao'));

      // Valor m�nimo
      $this->campoTexto($valor_valor_minimo, $this->_getLabel('valor_valor_minimo'),
        $valorNota->valorMinimo, 6, 6, FALSE, FALSE, TRUE,
        $this->_getHelp('valor_valor_minimo'));

      // Valor m�ximo
      $this->campoTexto($valor_valor_maximo, $this->_getLabel('valor_valor_maximo'),
        $valorNota->valorMaximo, 6, 6, FALSE, FALSE, FALSE,
        $this->_getHelp('valor_valor_maximo'));

    }
  }
  private function carregaCamposNotasNumericas(){
    // Ajuda
    // $help = 'Caso seja necess�rio adicionar mais notas, '
    //       . 'salve o formul�rio. Automaticamente 3 campos '
    //       . 'novos ficar�o dispon�veis.<br /><br />';

    $this->campoRotulo('__help1', '<strong>Notas para arredondamento de m�dias</strong><br />', $help, FALSE, '', '');

    // Cria campos para a postagem de notas
    $valores = $this->getDataMapper()->findTabelaValor($this->getEntity());

    for ($i = 0; $i <= 9; $i++) {
      $valorNota = $valores[$i];

      $valor_label              = sprintf("valor[label][%d]", $i);
      $valor_id                 = sprintf("valor[id][%d]", $i);
      $valor_nome               = sprintf("valor[nome][%d]", $i);
      $valor_nome_fake          = sprintf("valor[nome_fake][%d]", $i);
      $valor_tipo_recuperacao   = sprintf("valor[acao][%d]", $i);
      $valor_casa_decimal_exata = sprintf("valor[casaDecimalExata][%d]", $i);
      $valor_valor_minimo       = sprintf("valor[valor_minimo][%d]", $i);
      $valor_valor_maximo       = sprintf("valor[valor_maximo][%d]", $i);

      $this->campoRotulo($valor_label, 'Arredondamento ' . ($i + 1),
        $this->_getLabel(''), TRUE);

      // Id
      $this->campoOculto($valor_id, $valorNota->id);


      // Foi feito um campo oculto com a informa��o a ser gravada pois o framework n�o grava informa��es de campos desabilitados
      $this->campoOculto($valor_nome, $i);

      // Este campo serve apenas para ser exibido ao usu�rio, ele n�o grava a informa��o no banco, pois o framework n�o grava campos desabilitados
      $this->campoTexto($valor_nome_fake, $this->_getLabel('casa_decimal'),
        $i, 1, 1, FALSE, FALSE, TRUE, '', '', '', 'onKeyUp', TRUE);

      // Tipo de arredondamento de m�dia (ou a��o)
      $tipoArredondamentoMedia = TabelaArredondamento_Model_TipoArredondamentoMedia::getInstance();
      $this->campoLista($valor_tipo_recuperacao, $this->_getLabel('acao'),
        $tipoArredondamentoMedia->getEnums(), $valorNota->get('acao'), '', TRUE,
        $this->_getHelp('tipoRecuperacaoParalela'), '', FALSE, FALSE);

      // Casa decimal exata para o caso de arredondamento deste tipo
      $this->campoTexto($valor_casa_decimal_exata, $this->_getLabel('casa_decimal_exata'),
        $valorNota->casaDecimalExata, 1, 1, FALSE, FALSE, FALSE, '', '', '', 'onKeyUp', FALSE);

    }
  }
  protected function _save()
  {
    // Verifica pela exist�ncia do field identity
    if (isset($this->getRequest()->id) && 0 < $this->getRequest()->id) {
      $this->setEntity($this->getDataMapper()->find($this->getRequest()->id));
      $entity = $this->getEntity();
    }

    // Se existir, chama _save() do parent
    if (!isset($entity)) {
      return parent::_save();
    }

    // Processa os dados da requisi��o, apenas os valores para a tabela de valores.
    $valores = $this->getRequest()->valor;

    // A contagem usa um dos �ndices do formul�rio, sen�o ia contar sempre 4.
    $loop    = count($valores['id']);

    // Array de objetos a persistir
    $insert  = array();

    // Cria um array de objetos a persistir
    for ($i = 0; $i < $loop; $i++) {
      $id = $valores['id'][$i];

      // N�o atribui a inst�ncia de $entity sen�o n�o teria sucesso em verificar
      // se a inst�ncia � isNull().
      $data = array(
        'id'               => $id,
        'nome'             => $valores['nome'][$i],
        'descricao'        => $valores['descricao'][$i],
        'valorMinimo'      => $valores['valor_minimo'][$i],
        'valorMaximo'      => $valores['valor_maximo'][$i],
        'acao'             => $valores['acao'][$i],
        'casaDecimalExata' => $valores['casaDecimalExata'][$i]
      );

      // Se a inst�ncia j� existir, use-a para garantir UPDATE
      if (NULL != ($instance = $this->_getValor($id))) {
        $insert[$id] = $instance->setOptions($data);
      }
      else {
        $instance = new TabelaArredondamento_Model_TabelaValor($data);
        if (!$instance->isNull()) {
          $insert['new_' . $i] = $instance;
        }
      }
    }

    // Persiste
    foreach ($insert as $tabelaValor) {
      // Atribui uma tabela de arredondamento a inst�ncia de tabela valor
      $tabelaValor->tabelaArredondamento = $entity;

      // Se n�o tiver nome, passa para o pr�ximo
      if ($tabelaValor->isValid()) {
        $this->getDataMapper()->getTabelaValorDataMapper()->save($tabelaValor);
      }
      else {
        $this->mensagem = 'Erro no formul�rio';
        return FALSE;
      }
    }

    return TRUE;
  }
}