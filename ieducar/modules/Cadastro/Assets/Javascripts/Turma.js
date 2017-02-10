//abas

$j('td .formdktd').append('<div id="tabControl"><ul><li><div id="tab1" class="turmaTab"> <span class="tabText">Dados gerais</span></div></li><li><div id="tab2" class="turmaTab"> <span class="tabText">Dados adicionais</span></div></li></ul></div>');
$j('td .formdktd b').remove();
$j('.tablecadastro td .formdktd div').remove();
$j('#tab1').addClass('turmaTab-active').removeClass('turmaTab');

// Atribui um id a linha, para identificar até onde/a partir de onde esconder os campos
$j('#codigo_inep_educacenso').closest('tr').attr('id','tr_codigo_inep_educacenso');

// Adiciona um ID à linha que termina o formulário para parar de esconder os campos
$j('.tableDetalheLinhaSeparador').closest('tr').attr('id','stop');

// Pega o número dessa linha
linha_inicial_tipo = $j('#tr_codigo_inep_educacenso').index()-2;

// hide nos campos das outras abas (deixando só os campos da primeira aba)
$j('.tablecadastro >tbody  > tr').each(function(index, row) {
  if (index>=linha_inicial_tipo){
    if (row.id!='stop')
      row.hide();    
    else{
      return false;
    }
  }
});

$j(document).ready(function() {

  // on click das abas

  // DADOS GERAIS
  $j('#tab1').click( 
    function(){

      $j('.turmaTab-active').toggleClass('turmaTab-active turmaTab');
      $j('#tab1').toggleClass('turmaTab turmaTab-active')
      $j('.tablecadastro >tbody  > tr').each(function(index, row) {
        if (index>=linha_inicial_tipo-1){
          if (row.id!='stop')
            row.hide();    
          else
            return false;
        }else{
          row.show();
        }
      });        
    }
  );  

  // Adicionais
  $j('#tab2').click( 
    function(){
      $j('.turmaTab-active').toggleClass('turmaTab-active turmaTab');
      $j('#tab2').toggleClass('turmaTab turmaTab-active')
      $j('.tablecadastro >tbody  > tr').each(function(index, row) {
        if (row.id!='stop'){
          if (index>=linha_inicial_tipo){
            if ((index - linha_inicial_tipo) % 2 == 0){
              $j('#'+row.id).find('td').removeClass('formlttd');
              $j('#'+row.id).find('td').addClass('formmdtd');
            }else{
              $j('#'+row.id).find('td').removeClass('formmdtd');
              $j('#'+row.id).find('td').addClass('formlttd');
              
            }

            row.show();
          }else if (index>0){
            row.hide();
          }
        }else
          return false;
      });
    });

  // fix checkboxs
  $j('.tablecadastro >tbody  > tr').each(function(index, row) {
    if (index>=linha_inicial_tipo){
      $j('#'+row.id).find('input:checked').val('on');
    }
  });

  $j("#etapa_educacenso").change(function() {
    changeEtapaTurmaField();
  });

  var changeEtapaTurmaField = function() {
    var etapa = $j("#etapa_educacenso").val();

    if (etapa == 12 || etapa == 13) {
      $j("#etapa_educacenso2 > option").each(function() {
        var etapasCorrespondentes = ['4','5','6','7','8','9','10','11'];
        if ($j.inArray(this.value, etapasCorrespondentes) !== -1){
          this.show();
        } else {
          this.hide();
        }
      });
    } else if (etapa == 22 || etapa == 23) {
      $j("#etapa_educacenso2 > option").each(function() {
        var etapasCorrespondentes = ['14','15','16','17','18','19','20','21','41'];
        if ($j.inArray(this.value, etapasCorrespondentes) !== -1){
          this.show();
        } else {
          this.hide();
        }
      });
    } else if (etapa == 24) {
      $j("#etapa_educacenso2 > option").each(function() {
        var etapasCorrespondentes = ['4','5','6','7','8','9','10','11','14','15','16','17','18','19','20','21','41'];
        if ($j.inArray(this.value, etapasCorrespondentes) !== -1){
          this.show();
        } else {
          this.hide();
        }
      });
    } else if (etapa == 72) {
      $j("#etapa_educacenso2 > option").each(function() {
        var etapasCorrespondentes = ['69','70'];
        if ($j.inArray(this.value, etapasCorrespondentes) !== -1){
          this.show();
        } else {
          this.hide();
        }
      });
    } else if (etapa == 56) {
      $j("#etapa_educacenso2 > option").each(function() {
        var etapasCorrespondentes = ['1','2','4','5','6','7','8','9','10','11','14','15','16','17','18','19','20','21','41'];
        if ($j.inArray(this.value, etapasCorrespondentes) !== -1){
          this.show();
        } else {
          this.hide();
        }
      });
    } else if (etapa == 64) {
      $j("#etapa_educacenso2 > option").each(function() {
        var etapasCorrespondentes = ['39','40'];
        if ($j.inArray(this.value, etapasCorrespondentes) !== -1){
          this.show();
        } else {
          this.hide();
        }
      });
    } else {
      $j("#etapa_educacenso2").prop('disabled', 'disabled');
      $j("#etapa_educacenso2").val(null);
      return;
    }
    $j("#etapa_educacenso2").prop('disabled', false);
  }

  changeEtapaTurmaField();

});