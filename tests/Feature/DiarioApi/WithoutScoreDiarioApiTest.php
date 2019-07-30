<?php

namespace Tests\Feature\DiarioApi;

use App\Models\LegacyEvaluationRule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;


class WithoutScoreDiarioApiTest extends TestCase
{
    use DatabaseTransactions, DiarioApiRequestTestTrait, DiarioApiFakeDataTestTrait;

    /**
     * @var LegacyEvaluationRule
     */
    private $evaluationRule;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluationRule = factory(LegacyEvaluationRule::class, 'without-score')->create();
    }

    /**
     * O aluno deve ser aprovado ao lançar todas as faltas
     */
    public function testPostAbsenceShouldReturnsApproved()
    {
        $enrollment = $this->getCommonFakeData($this->evaluationRule);
        $discipline = $enrollment->schoolClass->disciplines()->first();
        $registration = $enrollment->registration;

        $response = $this->postAbsence($enrollment, $discipline->id, 1, 10);

        $this->assertEquals('Aprovado', $response->situacao);
        $this->assertEquals(1, $registration->refresh()->aprovado);
    }

    /**
     * O aluno deve continuar cursando quando não forem lançadas as faltas de todas as etapas
     */
    public function testPostAPartOfAbsenceShouldReturnsStudying()
    {
        $enrollment = $this->getCommonFakeData($this->evaluationRule);
        $discipline = $enrollment->schoolClass->disciplines()->first();
        $registration = $enrollment->registration;

        $schoolClass = $enrollment->schoolClass;

        $this->addAcademicYearStage($schoolClass, 2);

        $response = $this->postAbsence($enrollment, $discipline->id, 1, 10);

        $this->assertEquals('Cursando', $response->situacao);
        $this->assertEquals(3, $registration->refresh()->aprovado);
    }
}
