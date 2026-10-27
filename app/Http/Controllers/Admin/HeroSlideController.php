<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Models\Media;
use App\Services\ActivityLogger;
use App\Support\SafeUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Banner (carrossel) da página inicial — permissão banners.manage. */
class HeroSlideController extends Controller
{
    public function index()
    {
        return view('admin.hero-slides.index', ['items' => HeroSlide::with('media')->orderBy('sort_order')->orderBy('id')->get()]);
    }

    public function create()
    {
        return view('admin.hero-slides.form', ['slide' => new HeroSlide(['is_active' => true, 'image_position' => 'center'])]);
    }

    public function store(Request $request)
    {
        $slide = new HeroSlide($this->validated($request));
        $slide->sort_order = (int) HeroSlide::max('sort_order') + 1;
        $slide->save();
        ActivityLogger::log('banners.created', $slide, 'Criou o diapositivo do banner "'.$slide->title.'"');

        return redirect()->route('admin.hero-slides.index')->with('success', 'Diapositivo criado.');
    }

    public function edit(HeroSlide $heroSlide)
    {
        return view('admin.hero-slides.form', ['slide' => $heroSlide]);
    }

    public function update(Request $request, HeroSlide $heroSlide)
    {
        $heroSlide->fill($this->validated($request))->save();
        ActivityLogger::log('banners.updated', $heroSlide, 'Editou o diapositivo do banner "'.$heroSlide->title.'"');

        return redirect()->route('admin.hero-slides.edit', $heroSlide)->with('success', 'Diapositivo guardado.');
    }

    public function destroy(HeroSlide $heroSlide)
    {
        $heroSlide->delete();
        ActivityLogger::log('banners.deleted', null, 'Eliminou o diapositivo do banner "'.$heroSlide->title.'"');

        return redirect()->route('admin.hero-slides.index')->with('success', 'Diapositivo eliminado (a imagem continua na biblioteca).');
    }

    /** Reordenação acessível (subir/descer). */
    public function move(Request $request, HeroSlide $heroSlide)
    {
        $dir = $request->input('direction') === 'up' ? -1 : 1;
        DB::transaction(function () use ($heroSlide, $dir) {
            $all = HeroSlide::orderBy('sort_order')->orderBy('id')->lockForUpdate()->get()->values();
            $i = $all->search(fn ($s) => $s->id === $heroSlide->id);
            $j = $i + $dir;
            if ($i === false || $j < 0 || $j >= $all->count()) {
                return;
            }
            $list = $all->all();
            [$list[$i], $list[$j]] = [$list[$j], $list[$i]];
            foreach ($list as $pos => $s) {
                $s->forceFill(['sort_order' => $pos])->saveQuietly();
            }
        });

        return back()->with('success', 'Ordem actualizada.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'eyebrow' => ['nullable', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:200'],
            'title_highlight' => ['nullable', 'string', 'max:120'],
            'text' => ['nullable', 'string', 'max:500'],
            'media_id' => ['required', 'integer', Rule::exists('media', 'id')],
            'image_position' => ['required', Rule::in(array_keys(HeroSlide::POSITIONS))],
            'primary_label' => ['nullable', 'required_with:primary_url', 'string', 'max:60'],
            'primary_url' => ['nullable', 'required_with:primary_label', 'string', 'max:2048', SafeUrl::rule()],
            'secondary_label' => ['nullable', 'required_with:secondary_url', 'string', 'max:60'],
            'secondary_url' => ['nullable', 'required_with:secondary_label', 'string', 'max:2048', SafeUrl::rule()],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ], ['media_id.required' => 'Escolha a imagem de fundo do diapositivo.'], [
            'title' => 'título', 'text' => 'texto', 'primary_label' => 'texto do botão principal', 'primary_url' => 'destino do botão principal',
            'secondary_label' => 'texto do botão secundário', 'secondary_url' => 'destino do botão secundário', 'ends_at' => 'fim', 'starts_at' => 'início',
        ]);

        if (! Media::find($data['media_id'])?->isImage()) {
            throw ValidationException::withMessages(['media_id' => 'O ficheiro escolhido não é uma imagem.']);
        }

        foreach (['eyebrow', 'title', 'title_highlight', 'text', 'primary_label', 'secondary_label'] as $k) {
            if (isset($data[$k])) {
                $data[$k] = strip_tags($data[$k]);
            }
        }

        return $data + ['is_active' => $request->boolean('is_active')];
    }
}
