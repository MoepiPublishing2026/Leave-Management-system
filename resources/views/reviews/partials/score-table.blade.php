@php
    $inputName = $name ?? 'scores';
    $values = $values ?? [];
    $editable = $editable ?? false;
    $criteria = $criteria ?? [];
    $scoreLabels = $scoreLabels ?? [];
@endphp
<table class="pr-score-table{{ $editable ? '' : ' pr-score-readonly' }}">
    <thead>
    <tr>
        <th>Criteria</th>
        @foreach ($scoreLabels as $value => $label)
            <th title="{{ $label }}">{{ $value }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach ($criteria as $key => $label)
        @php $selected = (int) old($inputName.'.'.$key, $values[$key] ?? null); @endphp
        <tr>
            <td>{{ $label }}</td>
            @foreach ($scoreLabels as $value => $scoreLabel)
                <td>
                    @if ($editable)
                        <input type="radio" name="{{ $inputName }}[{{ $key }}]" value="{{ $value }}" @checked($selected === (int) $value) title="{{ $scoreLabel }}" required>
                    @else
                        <input type="radio" tabindex="-1" @checked($selected === (int) $value) title="{{ $scoreLabel }}">
                    @endif
                </td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
