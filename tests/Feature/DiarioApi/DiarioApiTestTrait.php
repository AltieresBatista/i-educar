<?php

namespace Tests\Feature\DiarioApi;

use App\Models\LegacyEnrollment;
use CoreExt_Controller_Request;
use CoreExt_Session;
use DiarioApiController;

require_once __DIR__ . '/../../../ieducar/modules/Avaliacao/Views/DiarioApiController.php';

trait DiarioApiTestTrait
{
    /**
     * @param LegacyEnrollment $enrollment
     * @param $disciplineId
     * @param $stage
     * @param $score
     * @return array
     */
    public function postAbsence($enrollment, $disciplineId, $stage, $score)
    {
        $schoolClass = $enrollment->schoolClass;

        $data = [
            'resource' => 'falta',
            'oper' => 'post',
            'instituicao_id' => $schoolClass->school->institution->id,
            'escola_id' => $schoolClass->school_id,
            'curso_id' => $schoolClass->course_id,
            'serie_id' => $schoolClass->grade_id,
            'turma_id' => $schoolClass->id,
            'ano_escolar' => $schoolClass->year,
            'componente_curricular_id' => $disciplineId,
            'etapa' => $stage,
            'matricula_id' => $enrollment->registration->id,
            'att_value' => $score,
        ];

        // Necessário porque um lugar em Boletim.php pega o valor da global $_GET
        $_GET['etapa'] = $data['etapa'];

        $fakeRequest = new CoreExt_Controller_Request(['data' => $data]);

        $diarioApiController = new DiarioApiController();
        $diarioApiController->setRequest($fakeRequest);
        $session = new CoreExt_Session();
        $session->id_pessoa = 1;
        $diarioApiController->setSession($session);
        $diarioApiController->postFalta();

        return $diarioApiController->response;
    }
}
