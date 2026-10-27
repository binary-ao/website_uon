<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\ActivityLogger;
use App\Support\SafeUrl;
use App\Support\Settings;
use App\Support\SettingsSchema;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Header, Footer e configurações globais. Edita apenas CONTEÚDOS dos
 * componentes originais (textos, contactos, links, logótipo) — a composição
 * visual é definida pelo template e não é alterável no CMS.
 */
class SettingController extends Controller
{
    public function edit(?string $group = null)
    {
        $groups = SettingsSchema::groups();
        $group ??= array_key_first($groups);
        abort_unless(isset($groups[$group]), 404);

        $values = Settings::all();
        $media = Media::whereIn('id', collect($groups[$group]['fields'])
            ->filter(fn ($f) => $f['type'] === 'media')
            ->keys()->map(fn ($k) => (int) ($values[$k] ?? 0))->filter())->get()->keyBy('id');

        return view('admin.settings.edit', compact('groups', 'group', 'values', 'media'));
    }

    public function update(Request $request, string $group)
    {
        $groups = SettingsSchema::groups();
        abort_unless(isset($groups[$group]), 404);
        $fields = $groups[$group]['fields'];

        $rules = [];
        foreach ($fields as $key => $f) {
            $base = ! empty($f['required']) ? ['required'] : ['nullable'];
            $rules[$key] = match ($f['type']) {
                'email' => [...$base, 'email', 'max:190'],
                'url' => [...$base, 'string', 'max:2048', SafeUrl::rule()],
                'media' => [...$base, 'integer', Rule::exists('media', 'id')],
                'bool' => ['nullable', 'boolean'],
                'textarea', 'lines' => [...$base, 'string', 'max:5000'],
                default => [...$base, 'string', 'max:250'],
            };
        }
        if (isset($fields['map_embed_url'])) {
            $rules['map_embed_url'][] = 'regex:#^https://(www\.google\.com/maps/embed\?|www\.openstreetmap\.org/export/embed\.html\?)#';
        }
        $data = $request->validate($rules, [
            'map_embed_url.regex' => 'Use um URL de incorporação do Google Maps (https://www.google.com/maps/embed?...) ou do OpenStreetMap.',
        ], collect($fields)->map(fn ($f) => mb_strtolower($f['label']))->all());

        $values = [];
        foreach ($fields as $key => $f) {
            $value = $f['type'] === 'bool' ? ($request->boolean($key) ? '1' : '0') : ($data[$key] ?? null);
            $values[$key] = is_string($value) ? trim(strip_tags($value)) : $value;
        }
        Settings::set($values);
        ActivityLogger::log('settings.updated', null, 'Alterou as configurações: '.$groups[$group]['label'], ['keys' => array_keys($values)]);

        return redirect()->route('admin.settings.edit', $group)->with('success', 'Configurações guardadas.');
    }
}
