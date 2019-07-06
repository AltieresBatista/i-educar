<?php

use App\Models\LegacyAverageFormula;
use App\Models\LegacyEvaluationRule;
use App\Models\LegacyInstitution;
use Faker\Generator as Faker;

require_once __DIR__ . '/../../ieducar/modules/RegraAvaliacao/Model/Nota/TipoValor.php';
require_once __DIR__ . '/../../ieducar/modules/RegraAvaliacao/Model/TipoProgressao.php';
require_once __DIR__ . '/../../ieducar/modules/RegraAvaliacao/Model/TipoPresenca.php';

$factory->define(LegacyEvaluationRule::class, function (Faker $faker) {
    return [
        'formula_media_id' => factory(LegacyAverageFormula::class)->create(),
        'instituicao_id' => factory(LegacyInstitution::class)->states('unique')->make(),
        'nome' => $faker->words(3, true),
        'tipo_nota' => $faker->randomElement([1, 2, 3, 4]),
        'tipo_progressao' => $faker->randomElement([1, 2, 3, 4]),
        'tipo_presenca' => $faker->randomElement([1, 2]),
    ];
});

$factory->defineAs(LegacyEvaluationRule::class, 'without-score', function (Faker $faker) use ($factory) {
    $evaluationRule = $factory->raw(LegacyEvaluationRule::class);

    return array_merge($evaluationRule, [
        'tipo_nota' => RegraAvaliacao_Model_Nota_TipoValor::NENHUM,
        'tipo_progressao' => RegraAvaliacao_Model_TipoProgressao::CONTINUADA,
        'tipo_presenca' => RegraAvaliacao_Model_TipoPresenca::POR_COMPONENTE,
    ]);
});
