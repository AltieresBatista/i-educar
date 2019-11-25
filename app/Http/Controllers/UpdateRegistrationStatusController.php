<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateRegistrationStatusRequest;
use App\Models\LegacyRegistration;
use App\Process;
use App\Services\RegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UpdateRegistrationStatusController extends Controller
{
    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $this->breadcrumb('Atualização da situação de matrículas em lote', [
            url('intranet/educar_configuracoes_index.php') => 'Configurações',
        ]);

        $this->menu(Process::UPDATE_REGISTRATION_STATUS);

        return view('registration.update-registration-status.index');
    }

    /**
     * Atualiza a situação das matrículas de acordo com o filtro
     *
     * @param UpdateRegistrationStatusRequest $request
     * @param RegistrationService $registrationService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(UpdateRegistrationStatusRequest $request, RegistrationService $registrationService)
    {
        $query = LegacyRegistration::active();

        if ($request->get('ano')) {
            $query->where('ano', $request->get('ano'));
        }

        if ($request->get('ref_cod_escola')) {
            $query->where('ref_ref_cod_escola', $request->get('ref_cod_escola'));
        }

        if ($request->get('ref_cod_curso')) {
            $query->where('ref_cod_curso', $request->get('ref_cod_curso'));
        }

        if ($request->get('ref_cod_turma')) {
            $schoolClassId = $request->get('ref_cod_turma');
            $query->whereHas('activeEnrollments', function ($enrollmentQuery) use ($schoolClassId) {
                $enrollmentQuery->where('ref_cod_turma', $schoolClassId);
            });
        }

        if ($request->get('ref_cod_serie')) {
            $query->where('ref_ref_cod_serie', $request->get('ref_cod_serie'));
        }

        $registrations = $query->get();

        DB::beginTransaction();

        foreach ($registrations as $registration) {
            $registrationService->updateStatus($registration, $request->get('nova_situacao'));
        }

        DB::commit();

        return redirect()->route('update-registration-status.index')->with('success', count($registrations) . ' matrículas atualizadas com sucesso');
    }
}
