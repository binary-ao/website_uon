<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

/**
 * Validação de ficheiros no servidor: extensão em lista branca + tipo MIME
 * detectado pelo conteúdo (fileinfo), nunca o tipo declarado pelo navegador.
 */
class UploadValidator
{
    /**
     * @return array{kind:string, extension:string, mime:string}
     */
    public static function inspect(UploadedFile $file, array $allowedKinds = ['image', 'document'], string $field = 'file'): array
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([$field => 'O carregamento do ficheiro falhou. Verifique o tamanho máximo permitido.']);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath()) ?: 'application/octet-stream';
        $config = config('uon.uploads');

        $kinds = [
            'image' => [$config['image_types'], $config['max_image_kb']],
            'document' => [$config['document_types'], $config['max_document_kb']],
        ];

        foreach ($allowedKinds as $kind) {
            [$types, $maxKb] = $kinds[$kind];
            if (isset($types[$extension]) && in_array($mime, $types[$extension], true)) {
                if ($file->getSize() > $maxKb * 1024) {
                    throw ValidationException::withMessages([$field => 'O ficheiro excede o tamanho máximo de '.round($maxKb / 1024, 1).' MB.']);
                }
                if ($kind === 'image' && @getimagesize($file->getRealPath()) === false) {
                    break;
                }

                return ['kind' => $kind, 'extension' => $extension === 'jpeg' ? 'jpg' : $extension, 'mime' => $mime];
            }
        }

        $allowed = collect($allowedKinds)->flatMap(fn ($k) => array_keys($kinds[$k][0]))->unique()->implode(', ');
        throw ValidationException::withMessages([$field => 'Tipo de ficheiro não permitido ou conteúdo inválido. Formatos aceites: '.$allowed.'.']);
    }

    /** Nome original limpo (sem caminhos nem caracteres de controlo), apenas para apresentação. */
    public static function cleanOriginalName(UploadedFile $file): string
    {
        $name = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $name = preg_replace('/[\x00-\x1F\x7F]+/u', '', $name);

        return mb_substr($name ?: 'ficheiro', 0, 200);
    }
}
