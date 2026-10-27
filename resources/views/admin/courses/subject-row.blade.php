<tr>
  <td class="text-muted small" data-row-number>{{ $n }}</td>
  <td><input type="number" min="1" max="10" class="form-control form-control-sm" name="subjects[{{ $i }}][year]" value="{{ $s['year'] ?? 1 }}" aria-label="Ano" data-copy-year></td>
  <td>
    <select class="form-select form-select-sm" name="subjects[{{ $i }}][semester]" aria-label="Semestre">
      <option value="">—</option>
      @foreach ([1 => '1º', 2 => '2º', 3 => 'Anual'] as $k => $l)<option value="{{ $k }}" @selected((string) ($s['semester'] ?? '') === (string) $k)>{{ $l }}</option>@endforeach
    </select>
  </td>
  <td><input type="text" class="form-control form-control-sm" name="subjects[{{ $i }}][code]" value="{{ $s['code'] ?? '' }}" aria-label="Código" maxlength="30"></td>
  <td><input type="text" class="form-control form-control-sm" name="subjects[{{ $i }}][name]" value="{{ $s['name'] ?? '' }}" aria-label="Unidade curricular" maxlength="200"></td>
  <td><input type="number" min="0" max="2000" class="form-control form-control-sm" name="subjects[{{ $i }}][hours]" value="{{ $s['hours'] ?? '' }}" aria-label="Horas"></td>
  <td><input type="number" min="0" max="100" step="0.5" class="form-control form-control-sm" name="subjects[{{ $i }}][credits]" value="{{ $s['credits'] ?? '' }}" aria-label="Créditos"></td>
  <td class="text-center"><input class="form-check-input" type="checkbox" name="subjects[{{ $i }}][is_optional]" value="1" @checked(! empty($s['is_optional'])) aria-label="Optativa"></td>
  <td><button type="button" class="u-iconbtn danger" data-repeater-remove title="Remover" aria-label="Remover linha"><i class="bi bi-x-lg"></i></button></td>
</tr>
