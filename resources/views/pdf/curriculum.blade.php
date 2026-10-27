<!doctype html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Plano Curricular — {{ $course->name }}</title>
<style>
  @page { margin: 26mm 16mm 20mm 16mm; }
  * { font-family: "DejaVu Sans", sans-serif; }
  body { font-size: 9.5pt; color: #2a2727; }
  header { position: fixed; top: -20mm; left: 0; right: 0; height: 16mm; border-bottom: 2px solid #d10000; }
  header img { height: 13mm; float: left; }
  header .inst { float: right; text-align: right; font-size: 8pt; color: #5f5a5a; padding-top: 2mm; }
  header .inst strong { display: block; color: #1d1b1b; font-size: 9.5pt; }
  footer { position: fixed; bottom: -14mm; left: 0; right: 0; height: 8mm; font-size: 7.5pt; color: #8a8484; border-top: 1px solid #e6e2e2; padding-top: 2mm; }
  footer .page:after { content: "Página " counter(page) " de " counter(pages); }
  h1 { font-size: 17pt; color: #1d1b1b; margin: 0 0 1mm; }
  .eyebrow { font-size: 8pt; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; color: #d10000; margin: 0; }
  .summary { color: #5f5a5a; margin: 1mm 0 4mm; }
  table.info { width: 100%; border-collapse: collapse; margin-bottom: 6mm; }
  table.info td { padding: 2mm 3mm; border: 1px solid #e6e2e2; vertical-align: top; width: 25%; }
  table.info .k { display: block; font-size: 7pt; text-transform: uppercase; letter-spacing: .5px; color: #8a8484; }
  table.info .v { font-weight: bold; color: #1d1b1b; }
  h2.year { font-size: 11.5pt; color: #fff; background: #1d1b1b; padding: 2mm 3mm; margin: 5mm 0 0; }
  h2.year span { color: #ff8080; font-weight: normal; font-size: 9pt; }
  h3.sem { font-size: 9.5pt; margin: 3mm 0 1mm; color: #1d1b1b; }
  h3.sem span { color: #d10000; font-weight: normal; }
  table.plan { width: 100%; border-collapse: collapse; page-break-inside: auto; }
  table.plan tr { page-break-inside: avoid; }
  table.plan th { font-size: 7pt; text-transform: uppercase; letter-spacing: .5px; color: #5f5a5a; text-align: left; padding: 1.6mm 2.5mm; background: #f7f6f6; border-bottom: 1px solid #cfc9c9; }
  table.plan td { padding: 1.6mm 2.5mm; border-bottom: 1px solid #eeeaea; }
  table.plan .num { text-align: right; width: 16mm; }
  table.plan .code { width: 20mm; color: #5f5a5a; }
  table.plan tfoot td { font-weight: bold; background: #f7f6f6; border-bottom: 0; }
  .op { font-size: 6.5pt; font-weight: bold; color: #a30000; background: #fff1f1; padding: 0 1mm; margin-left: 1mm; }
  .totals { margin-top: 6mm; padding: 3mm; border: 1px solid #e6e2e2; background: #fff1f1; }
  .note { margin-top: 4mm; color: #5f5a5a; font-size: 8.5pt; }
</style>
</head>
<body>
<header>
  @if ($logo)<img src="{{ $logo }}" alt="{{ $siteName }}">@endif
  <div class="inst"><strong>{{ $siteName }}</strong>{{ $course->unit?->parent?->name ?? $course->unit?->name }}<br>Plano Curricular</div>
</header>
<footer>
  <span>{{ $siteName }} · {{ $course->degree?->name }} em {{ $course->name }} · Gerado em {{ $generatedAt->format('d/m/Y H:i') }}</span>
  <span class="page" style="float:right"></span>
</footer>

<main>
  <p class="eyebrow">{{ $course->degree?->name }}</p>
  <h1>{{ $course->name }}</h1>
  @if ($course->summary)<p class="summary">{{ $course->summary }}</p>@endif

  @php
      $info = array_filter([
          'Grau' => $course->degree?->name,
          'Duração' => $course->duration,
          'Modalidade' => $course->modality,
          'Regime' => $course->regime,
          'Unidade orgânica' => $course->unit?->name,
          'Faculdade' => $course->unit?->parent?->name,
          'Coordenação' => $course->coordinator_name,
          'Contacto' => $course->contact_email,
      ]);
      $cells = array_chunk($info, 4, true);
  @endphp
  @if ($info)
    <table class="info">
      @foreach ($cells as $row)
        <tr>
          @foreach ($row as $k => $v)<td><span class="k">{{ $k }}</span><span class="v">{{ $v }}</span></td>@endforeach
          @for ($i = count($row); $i < 4; $i++)<td></td>@endfor
        </tr>
      @endforeach
    </table>
  @endif

  @foreach ($plan as $year)
    <h2 class="year">{{ $year['label'] }} <span>· {{ $year['hours'] }} horas{{ $withCredits ? ' · '.\App\Support\Curriculum::number($year['credits']).' créditos' : '' }}</span></h2>
    @foreach ($year['semesters'] as $sem)
      <h3 class="sem">{{ $sem['label'] }}</h3>
      <table class="plan">
        <thead><tr>@if ($withCodes)<th class="code">Código</th>@endif<th>Unidade curricular</th><th class="num">CH</th>@if ($withCredits)<th class="num">Créditos</th>@endif</tr></thead>
        <tbody>
          @foreach ($sem['subjects'] as $s)
            <tr>
              @if ($withCodes)<td class="code">{{ $s->code ?? '—' }}</td>@endif
              <td>{{ $s->name }}@if ($s->is_optional)<span class="op">OP</span>@endif</td>
              <td class="num">{{ $s->hours ?? '—' }}</td>
              @if ($withCredits)<td class="num">{{ \App\Support\Curriculum::number($s->credits) }}</td>@endif
            </tr>
          @endforeach
        </tbody>
        <tfoot><tr>@if ($withCodes)<td></td>@endif<td>Total</td><td class="num">{{ $sem['hours'] }}</td>@if ($withCredits)<td class="num">{{ \App\Support\Curriculum::number($sem['credits']) }}</td>@endif</tr></tfoot>
      </table>
    @endforeach
  @endforeach

  <div class="totals">
    <strong>Total do curso:</strong> {{ collect($plan)->sum('hours') }} horas{{ $withCredits ? ' · '.\App\Support\Curriculum::number(collect($plan)->sum('credits')).' créditos' : '' }}
    · {{ $course->subjects->count() }} unidades curriculares
  </div>
  <p class="note">CH = carga horária total (horas).@if ($course->subjects->contains('is_optional', true)) OP = unidade curricular optativa.@endif @if ($course->curriculum_note) {{ $course->curriculum_note }}@endif Documento informativo; prevalece o plano de estudos aprovado pelos órgãos competentes.</p>
</main>
</body>
</html>
