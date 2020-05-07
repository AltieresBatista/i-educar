<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegacyStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StudentRotatePictureController extends Controller
{
    /**
     * Rotaciona uma imagem e a substitui no storage.
     *
     * @param Request       $request
     * @param LegacyStudent $student
     *
     * @return array
     */
    public function rotate(Request $request, LegacyStudent $student)
    {
        $url = $request->input('url');
        $angle = $request->input('angle', 90);

        $picture = $student->individual->picture;

        $segments = explode('/', $picture->url);
        $filename = end($segments);

        // http://image.intervention.io/
        $image = (string) Image::make($url)->rotate($angle)->encode();

        $filename = config('legacy.app.database.dbname') . '/' . $filename;

        // Salva a imagem no storage e então atualiza a URL do arquivo
        if (Storage::put($filename, $image)) {
            $url = Storage::url($filename);

            $picture->url = $url;
            $picture->saveOrFail();

            return [
                'url' => Storage::url($filename),
            ];
        }

        throw new HttpException(422, 'Ocorreu um erro no servidor ao girar a enviar foto. Tente novamente.');
    }
}
