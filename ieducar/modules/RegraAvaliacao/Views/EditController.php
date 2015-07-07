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
 * @package     RegraAvaliacao
 * @subpackage  Modules
 * @since       Arquivo dispon�vel desde a vers�o 1.1.0
 * @version     $Id$
 */

require_once 'Core/Controller/Page/EditController.php';
require_once 'RegraAvaliacao/Model/RegraDataMapper.php';
require_once 'RegraAvaliacao/Model/RegraRecuperacaoDataMapper.php';

/**
 * EditController class.
 *
 * @author      Eriksen Costa Paix�o <eriksen.paixao_bs@cobra.com.br>
 * @category    i-Educar
 * @license     @@license@@
 * @package     RegraAvaliacao
 * @subpackage  Modules
 * @since       Classe dispon�vel desde a vers�o 1.1.0
 * @version     @@package_version@@
 */
class EditController extends Core_Controller_Page_EditController
{
  protected $_dataMapper        = 'RegraAvaliacao_Model_RegraDataMapper';
  protected $_titulo            = 'Cadastro de regra de avalia��o';
  protected $_processoAp        = 947;
  protected $_nivelAcessoOption = App_Model_NivelAcesso::INSTITUCIONAL;
  protected $_saveOption        = TRUE;
  protected $_deleteOption      = FALSE;

  protected $_formMap = array(
    'instituicao' => array(
      'label'  => 'Institui��o',
      'help'   => '',
    ),
    'nome' => array(
      'label'  => 'Nome',
      'help'   => 'Nome por extenso do componente.',
    ),
    'tipoNota' => array(
      'label'  => 'Sistema de nota',
      'help'   => ''
    ),
    'tipoProgressao' => array(
      'label'  => 'Progress�o',
      'help'   => 'Selecione o m�todo de progress�o para a regra.'
    ),
    'tabelaArredondamento' => array(
      'label'  => 'Tabela de arredondamento de nota',
      'help'   => ''
    ),
    'media' => array(
      'label'  => 'M�dia final para promo��o',
      'help'   => 'Informe a m�dia necess�ria para promo��o<br />
                   do aluno, aceita at� 3 casas decimais. Exemplos: 5,00; 6,725, 6.<br >
                   Se o tipo de progress�o for <b>"Progressiva"</b>, esse<br />
                   valor n�o ser� considerado.'
    ),
    'mediaRecuperacao' => array(
      'label'  => 'M�dia exame final para promo��o',
      'help'   => 'Informe a m�dia necess�ria para promo��o<br />
                   do aluno, aceita at� 3 casas decimais. Exemplos: 5,00; 6,725, 6.<br >
                   Desconsidere esse campo caso selecione o tipo de nota "conceitual"'
    ),
    'formulaMedia' => array(
      'label'  => 'F�rmula de c�lculo da m�dia',
      'help'   => '',
    ),
    'formulaRecuperacao' => array(
      'label'  => 'F�rmula de c�lculo da m�dia de recupera��o',
      'help'   => '',
    ),
    'porcentagemPresenca' => array(
      'label'  => 'Porcentagem de presen�a',
      'help'   => 'A porcentagem de presen�a necess�ria para o aluno ser aprovado.<br />
                   Esse valor � desconsiderado caso o campo "Progress�o" esteja como<br />
                   "N�o progressiva autom�tica - Somente m�dia".<br />
                   Em porcentagem, exemplo: <b>75</b> ou <b>80,750</b>'
    ),
    'parecerDescritivo' => array(
      'label'  => 'Parecer descritivo',
      'help'   => '',
    ),
    'tipoPresenca' => array(
      'label'  => 'Apura��o de presen�a',
      'help'   => ''
    ),
    'tipoRecuperacaoParalela' => array(
      'label'   => 'Permitir recupera��o paralela',
      'help'    => ''
    ),
    'mediaRecuperacaoParalela' => array(
      'label'   => 'M�dia da recupera��o paralela',
      'help'    => ''
    ),
    'notaMaximaGeral' => array(
      'label'   => 'Nota m�xima geral',
      'help'    => 'Informe o valor m�ximo para notas no geral'
    ),
    'notaMaximaExameFinal' => array(
      'label'   => 'Nota m�xima exame final',
      'help'    => 'Informe o valor m�ximo para nota do exame final'
    ),
    'qtdCasasDecimais' => array(
      'label'   => 'Quantidade m�xima de casas decimais',
      'help'    => 'Informe o n�mero m�ximo de casas decimais'
    ),
    'recuperacaoDescricao' => array(
      'label'  => 'Descri��o do exame:',
      'help'   => 'Exemplo: Recupera��o semestral I'
    ),
    'recuperacaoEtapasRecuperadas' => array(
      'label'  => '<span style="padding-left: 10px"></span>Etapas:',
      'help'   => 'Separe as etapas com ponto e v�rgula. Exemplo: 1;2.'
    ),
    'recuperacaoSubstituiMenorNota' => array(
      'label'  => '<span style="padding-left: 10px"></span>Substitu� menor nota:',
      'help'   => 'Caso marcado ir� substituir menor nota.'
    ),
    'recuperacaoMedia' => array(
      'label'  => '<span style="padding-left: 10px"></span>M�dia:',
      'help'   => 'Abaixo de qual m�dia habilitar campo.'
    ),
    'recuperacaoNotaMaxima' => array(
      'label'  => '<span style="padding-left: 10px"></span>Nota m�x:',
      'help'   => 'Nota m�xima permitida para lan�amento.'
    ),
    'recuperacaoExcluir' => array(
      'label'  => '<span style="padding-left: 10px"></span>Excluir:'
    ),
    'notaGeralPorEtapa' => array(
      'label' => 'Utilizar uma nota geral por etapa'
    )
  );

  private $_tipoNotaJs = '
var tipo_nota = new function() {
  this.isNenhum = function(docObj, formId, fieldsName) {
    var regex = new RegExp(fieldsName);
    var form  = docObj.getElementById(formId);

    for (var i = 0; i < form.elements.length; i++) {
      var elementName = form.elements[i].name;
      if (null !== elementName.match(regex)) {
        if (form.elements[i].checked == false) {
          continue;
        }

        docObj.getElementById(\'tabelaArredondamento\').disabled = false;
        docObj.getElementById(\'media\').disabled = false;
        docObj.getElementById(\'formulaMedia\').disabled = false;
        docObj.getElementById(\'formulaRecuperacao\').disabled = false;

        if (form.elements[i].value == 0) {
          docObj.getElementById(\'tabelaArredondamento\').disabled = true;
          docObj.getElementById(\'media\').disabled = true;
          docObj.getElementById(\'formulaMedia\').disabled = true;
          docObj.getElementById(\'formulaRecuperacao\').disabled = true;
        }

        break;
      }
    }
  };
};

var tabela_arredondamento = new function() {
  this.docObj = null;

  this.getTabelasArredondamento = function(docObj, tipoNota) {
    tabela_arredondamento.docObj = docObj;
    var xml = new ajax(tabela_arredondamento.parseResponse);
    xml.envia("/modules/TabelaArredondamento/Views/TabelaTipoNotaAjax.php?tipoNota=" + tipoNota);
  };

  this.parseResponse = function() {
    if (arguments[0] === null) {
      return;
    }

    docObj = tabela_arredondamento.docObj;

    tabelas = arguments[0].getElementsByTagName(\'tabela\');
    docObj.options.length = 0;
    for (var i = 0; i < tabelas.length; i++) {
      docObj[docObj.options.length] = new Option(
        tabelas[i].firstChild.nodeValue, tabelas[i].getAttribute(\'id\'), false, false
      );
    }

    if (tabelas.length == 0) {
      docObj.options[0] = new Option(
        \'O tipo de nota n�o possui tabela de arredondamento.\', \'\', false, false
      );
    }
  }
}
';

  /**
   * Array de inst�ncias RegraAvaliacao_Model_RegraRecuperacao.
   * @var array
   */
  protected $_recuperacoes = array();

  /**
   * Setter.
   * @param array $recuperacoes
   * @return Core_Controller_Page_Abstract Prov� interface flu�da
   */
  protected function _setRecuperacoes(array $recuperacoes = array())
  {
    foreach ($recuperacoes as $key => $recuperacao) {
      $this->_recuperacoes[$recuperacao->id] = $recuperacao;
    }
    return $this;
  }

  /**
   * Getter.
   * @return array
   */
  protected function _getRecuperacoes()
  {
    return $this->_recuperacoes;
  }

  /**
   * Getter
   * @param int $id
   * @return RegraAvaliacao_Model_RegraRecuperacao
   */
  protected function _getRecuperacao($id)
  {
    return isset($this->_recuperacoes[$id]) ? $this->_recuperacoes[$id] : NULL;
  }

  /**
   * @see Core_Controller_Page_EditController#_preConstruct()
   * @todo Intera��o com a API est� errada. Isso j� � feito em _initNovo()
   *   na superclasse. VER.
   */
  protected function _preConstruct()
  {
    if (isset($this->getRequest()->id) && 0 < $this->getRequest()->id) {
      //$this->setEntity($this->getDataMapper()->find($this->getRequest()->id));
      //$this->_setRecuperacoes($this->getDataMapper()->findRegraRecuperacao($this->getEntity()));
    }
  }

  protected function _preRender()
  {
    parent::_preRender();

    // Adiciona o c�digo Javascript de controle do formul�rio.
    $js = sprintf('
      <script type="text/javascript">
        %s

        window.onload = function() {
          // Desabilita os campos relacionados caso o tipo de nota seja "nenhum".
          new tipo_nota.isNenhum(document, \'formcadastro\', \'tipoNota\');

          // Faz o binding dos eventos isNenhum e getTabelasArredondamento nos
          // campos radio de tipo de nota.
          var events = function() {
            new tipo_nota.isNenhum(document, \'formcadastro\', \'tipoNota\');
            new tabela_arredondamento.getTabelasArredondamento(
              document.getElementById(\'tabelaArredondamento\'),
              this.value
            );
          }

          new ied_forms.bind(document, \'formcadastro\', \'tipoNota\', \'click\', events);
        }
      </script>',
      $this->_tipoNotaJs
    );

    $this->prependOutput($js);

    Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');
    Portabilis_View_Helper_Application::loadJavascript($this, '/modules/RegraAvaliacao/Assets/Javascripts/RegraAvaliacao.js');

    $nomeMenu = $this->getRequest()->id == null ? "Cadastrar" : "Editar";
    $localizacao = new LocalizacaoSistema();
    $localizacao->entradaCaminhos( array(
         $_SERVER['SERVER_NAME']."/intranet" => "In&iacute;cio",
         "educar_index.php"                  => "i-Educar - Escola",
         ""        => "$nomeMenu regra de avalia&ccedil;&atilde;o"
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
    $this->campoLista('instituicao', $this->_getLabel('instituicao'), $instituicoes,
      $this->getEntity()->instituicao);

    // Nome
    $this->campoTexto('nome', $this->_getLabel('nome'), $this->getEntity()->nome,
      50, 50, TRUE, FALSE, FALSE, $this->_getHelp('nome'));

    // Nota tipo valor
    $notaTipoValor = RegraAvaliacao_Model_Nota_TipoValor::getInstance();
    $this->campoRadio('tipoNota', $this->_getLabel('tipoNota'), $notaTipoValor->getEnums(),
      $this->getEntity()->get('tipoNota'), '', $this->_getHelp('tipoNota'));

    // Tabela de arredondamento
    $tabelaArredondamento = $this->getDataMapper()->findTabelaArredondamento($this->getEntity());
    $tabelaArredondamento = CoreExt_Entity::entityFilterAttr($tabelaArredondamento, 'id', 'nome');

    if (empty($tabelaArredondamento)) {
      $tabelaArredondamento = array(0 => 'O tipo de nota n�o possui tabela de arredondamento.');
    }

    $this->campoLista('tabelaArredondamento', $this->_getLabel('tabelaArredondamento'),
      $tabelaArredondamento, $this->getEntity()->get('tabelaArredondamento'), '',
      FALSE, $this->_getHelp('tabelaArredondamento'), '', FALSE, FALSE);

    // Tipo progress�o
    $tipoProgressao = RegraAvaliacao_Model_TipoProgressao::getInstance();
    $this->campoRadio('tipoProgressao', $this->_getLabel('tipoProgressao'),
      $tipoProgressao->getEnums(), $this->getEntity()->get('tipoProgressao'), '',
      $this->_getHelp('tipoProgressao'));

    // M�dia
    $this->campoTexto('media', $this->_getLabel('media'), $this->getEntity()->media,
      5, 50, FALSE, FALSE, FALSE, $this->_getHelp('media'));

    $this->campoTexto('mediaRecuperacao', $this->_getLabel('mediaRecuperacao'), $this->getEntity()->mediaRecuperacao, 5, 50, FALSE, FALSE, FALSE, $this->_getHelp('mediaRecuperacao'));

    // C�lculo m�dia
    $formulas = $this->getDataMapper()->findFormulaMediaFinal();
    $formulas = CoreExt_Entity::entityFilterAttr($formulas, 'id', 'nome');
    $this->campoLista('formulaMedia', $this->_getLabel('formulaMedia'),
      $formulas, $this->getEntity()->get('formulaMedia'), '', FALSE,
      $this->_getHelp('formulaMedia'), '', FALSE, FALSE);

    // C�lculo m�dia recupera��o
    $formulas = $this->getDataMapper()->findFormulaMediaRecuperacao();
    $formulasArray = array(0 => 'N�o usar recupera��o');
    $formulasArray = $formulasArray + CoreExt_Entity::entityFilterAttr($formulas, 'id', 'nome');

    $this->campoLista('formulaRecuperacao', $this->_getLabel('formulaRecuperacao'),
      $formulasArray, $this->getEntity()->get('formulaRecuperacao'), '', FALSE,
      $this->_getHelp('formulaRecuperacao'), '', FALSE, FALSE);

    // Porcentagem presen�a
    $this->campoTexto('porcentagemPresenca', $this->_getLabel('porcentagemPresenca'),
      $this->getEntity()->porcentagemPresenca, 5, 50, TRUE, FALSE, FALSE,
      $this->_getHelp('porcentagemPresenca'));

    // Parecer descritivo
    $parecerDescritivo = RegraAvaliacao_Model_TipoParecerDescritivo::getInstance();
    $this->campoRadio('parecerDescritivo', $this->_getLabel('parecerDescritivo'),
      $parecerDescritivo->getEnums(), $this->getEntity()->get('parecerDescritivo'), '',
      $this->_getHelp('parecerDescritivo'));

    // Presen�a
    $tipoPresenca = RegraAvaliacao_Model_TipoPresenca::getInstance();
    $this->campoRadio('tipoPresenca', $this->_getLabel('tipoPresenca'),
      $tipoPresenca->getEnums(), $this->getEntity()->get('tipoPresenca'), '',
      $this->_getHelp('tipoPresenca'));

    $this->campoNumero('notaMaximaGeral', $this->_getLabel('notaMaximaGeral'), $this->getEntity()->notaMaximaGeral,
      3, 3, TRUE, FALSE, FALSE, $this->_getHelp('notaMaximaGeral'));

    $this->campoNumero('notaMaximaExameFinal', $this->_getLabel('notaMaximaExameFinal'), $this->getEntity()->notaMaximaExameFinal,
      3, 3, TRUE, FALSE, FALSE, $this->_getHelp('notaMaximaExameFinal'));

    $this->campoNumero('qtdCasasDecimais', $this->_getLabel('qtdCasasDecimais'), $this->getEntity()->qtdCasasDecimais,
      3, 3, TRUE, FALSE, FALSE, $this->_getHelp('qtdCasasDecimais'));

    // Nota geral por etapa
    $this->campoCheck('notaGeralPorEtapa', $this->_getLabel('notaGeralPorEtapa'),
        $this->getEntity()->notaGeralPorEtapa, '', FALSE, FALSE, FALSE, $this->_getHelp('notaGeralPorEtapa'));

    $tipoRecuperacaoParalela = RegraAvaliacao_Model_TipoRecuperacaoParalela::getInstance();

    $this->campoLista('tipoRecuperacaoParalela', $this->_getLabel('tipoRecuperacaoParalela'),
      $tipoRecuperacaoParalela->getEnums(), $this->getEntity()->get('tipoRecuperacaoParalela'), '', FALSE,
      $this->_getHelp('tipoRecuperacaoParalela'), '', FALSE, FALSE);


    $this->campoTexto('mediaRecuperacaoParalela', $this->_getLabel('mediaRecuperacaoParalela'),
                       $this->getEntity()->mediaRecuperacaoParalela, 5, 50, FALSE, FALSE,
                       FALSE, $this->_getHelp('mediaRecuperacaoParalela'));

    // Parte condicional
    if (!$this->getEntity()->isNew()) {
      // Quebra
      $this->campoQuebra();

      // Ajuda
      $help = 'Caso seja necess�rio adicionar mais etapas, '
            . 'salve o formul�rio. Automaticamente 3 campos '
            . 'novos ficar�o dispon�veis.<br /> '
            . 'As etapas devem ser separadas por ponto e v�rgula(;). <br /><br />';

      $this->campoRotulo('__help1', '<strong>Recupera��es espec�ficas</strong><br />', $help, FALSE, '', '');

      // Cria campos para a postagem de notas
      $recuperacoes = $this->getDataMapper()->findRegraRecuperacao($this->getEntity());

      for ($i = 0, $loop = count($recuperacoes); $i < ($loop == 0 ? 5 : $loop + 3); $i++) {
        $recuperacao = $recuperacoes[$i];

        $recuperacaoLabel        = sprintf("recuperacao[label][%d]", $i);
        $recuperacaoId           = sprintf("recuperacao[id][%d]", $i);
        $recuperacaoDescricao    = sprintf("recuperacao[descricao][%d]", $i);
        $recuperacaoEtapasRecuperadas    = sprintf("recuperacao[etapas_recuperadas][%d]", $i);
        $recuperacaoSubstituiMenorNota = sprintf("recuperacao[substitui_menor_nota][%d]", $i);
        $recuperacaoMedia = sprintf("recuperacao[media][%d]", $i);
        $recuperacaoNotaMaxima = sprintf("recuperacao[nota_maxima][%d]", $i);
        $recuperacaoExcluir = sprintf("recuperacao[excluir][%d]", $i);

        $this->campoRotulo($recuperacaoLabel, 'Recupera��o ' . ($i + 1),
          $this->_getLabel(''), TRUE);

        // Id
        $this->campoOculto($recuperacaoId, $recuperacao->id);

        // Nome
        $this->campoTexto($recuperacaoDescricao, $this->_getLabel('recuperacaoDescricao'),
          $recuperacao->descricao, 10, 25, FALSE, FALSE, TRUE, $this->_getHelp('recuperacaoDescricao'));

        // Etapas recuperadas
        $this->campoTexto($recuperacaoEtapasRecuperadas, $this->_getLabel('recuperacaoEtapasRecuperadas'),
        $recuperacao->etapasRecuperadas, 5, 25, FALSE, FALSE, TRUE, $this->_getHelp('recuperacaoEtapasRecuperadas'));

        // Substitu� menor nota
        $this->campoCheck($recuperacaoSubstituiMenorNota, $this->_getLabel('recuperacaoSubstituiMenorNota'),
        $recuperacao->substituiMenorNota, '', TRUE, FALSE, FALSE, $this->_getHelp('recuperacaoSubstituiMenorNota'));

        // M�dia
        $this->campoTexto($recuperacaoMedia, $this->_getLabel('recuperacaoMedia'),
          $recuperacao->media, 4, 4, FALSE, FALSE, TRUE, $this->_getHelp('recuperacaoMedia'));

        // Nota m�xima
        $this->campoTexto($recuperacaoNotaMaxima, $this->_getLabel('recuperacaoNotaMaxima'),
          $recuperacao->notaMaxima, 4, 4, FALSE, FALSE, TRUE,
          $this->_getHelp('recuperacaoNotaMaxima'));

        // Exclus�o
        $this->campoCheck($recuperacaoExcluir, $this->_getLabel('recuperacaoExcluir'),
        FALSE, '', FALSE, FALSE, FALSE);
      }

      // Quebra
      $this->campoQuebra();
    }
  }

  protected function _save()
  {

    $data = array();

    foreach ($_POST as $key => $val) {
      if (array_key_exists($key, $this->_formMap)) {
        $data[$key] = $val;
      }
    }

    // Verifica pela exist�ncia do field identity
    if (isset($this->getRequest()->id) && 0 < $this->getRequest()->id) {
      $this->setEntity($this->getDataMapper()->find($this->getRequest()->id));
      $entity = $this->getEntity();
    }
    //fixup for checkbox nota geral
    if(!isset($data['notaGeralPorEtapa'])){
      $data['notaGeralPorEtapa'] = '0';
    }

    if (isset($entity)) {
      $this->getEntity()->setOptions($data);
    }
    else {
      $this->setEntity($this->getDataMapper()->createNewEntityInstance($data));
    }

    // Processa os dados da requisi��o, apenas os valores para a tabela de valores.
    $recuperacoes = $this->getRequest()->recuperacao;

    // A contagem usa um dos �ndices do formul�rio, sen�o ia contar sempre 4.
    $loop    = count($recuperacoes['id']);

    // Array de objetos a persistir
    $insert  = array();

    // Cria um array de objetos a persistir
    for ($i = 0; $i < $loop; $i++) {
      $id = $recuperacoes['id'][$i];

      // N�o atribui a inst�ncia de $entity sen�o n�o teria sucesso em verificar
      // se a inst�ncia � isNull().
      $data = array(
        'id' => $id,
        'descricao' => $recuperacoes['descricao'][$i],
        'etapasRecuperadas' => $recuperacoes['etapas_recuperadas'][$i],
        'substituiMenorNota' => $recuperacoes['substitui_menor_nota'][$i],
        'media' => $recuperacoes['media'][$i],
        'notaMaxima' => $recuperacoes['nota_maxima'][$i]
      );

      // Se a inst�ncia j� existir, use-a para garantir UPDATE
      if (NULL != ($instance = $this->_getRecuperacao($id))) {
          $insert[$id] = $instance->setOptions($data);
      }
      else {
        $instance = new RegraAvaliacao_Model_RegraRecuperacao($data);
        if (!$instance->isNull()) {
          if($recuperacoes['excluir'][$i] && is_numeric($id)){
            $this->getDataMapper()->getRegraRecuperacaoDataMapper()->delete($instance);
          }
          else
            $insert['new_' . $i] = $instance;
        }
      }
    }

    // Persiste
    foreach ($insert as $regraRecuperacao) {
      // Atribui uma tabela de arredondamento a inst�ncia de tabela valor
      $regraRecuperacao->regraAvaliacao = $entity;

      if ($regraRecuperacao->isValid()) {
        $this->getDataMapper()->getRegraRecuperacaoDataMapper()->save($regraRecuperacao);
      }
      else {
        $this->mensagem .= 'Erro no formul�rio';
        return FALSE;
      }
    }

    try {
      $entity = $this->getDataMapper()->save($this->getEntity());
    }
    catch (Exception $e) {
      // TODO: ver @todo do docblock
      $this->mensagem .= 'Erro no preenchimento do formul�rio. ';
      return FALSE;
    }

    return TRUE;
  }
}
