<div class="overflow-auto">
@if (session()->has('message'))
@include('modals.exito')
@endif

<!-- Barra superior -->
<div class="d-flex justify-content-between align-items-center w-100 py-3">
<div class="d-flex align-items-center gap-2">
<span class="d-none d-sm-inline h5 fw-bold text-primary">Stranding</span>
<span class="d-inline d-sm-none h5 fw-bold text-primary">YOFC</span>
</div>

<div class="d-flex gap-2 ms-3">
<button type="submit" wire:click="save" class="btn btn-primary btn-sm text-nowrap">Guardar</button>
<button wire:click="export" class="btn btn-success btn-sm text-nowrap">Exportar Excel</button>
</div>
</div>
<style>
.input-plan {
background-color: transparent;
/* o usa tu color dinámico con var() */
height: 30px;
line-height: 1;
padding: 0;
font-size: 0.8rem;
border: none;
/* sin borde */
text-align: center;
/* centrar texto */
width: 100%;
}
</style>
@php
$colors = [
'naranja' => 'rgb(238,149,76)',
'azulClaro' => 'rgb(180,198,231)',
'azulPastel' => 'rgb(221,235,247)',
'azulMedio' => 'rgb(155,194,230)',
'verdeClaro' => 'rgb(226,239,218)',
'grisOscuro' => 'rgb(51,63,79)',
'white' => 'rgb(255,255,255)',
'placeholder' => '---'
];

$coloresRMK = [
'SZ-01' => $colors['white'],
'SZ-02' => $colors['white'],
'SZ-03' => $colors['white'],
];
@endphp

<div class="overflow-auto border border-secondary rounded" style="max-height: calc(80vh - 150px);">
<div style="min-width: 1200px;">
@foreach ($equipos as $equipo)
<div class="mb-4">
<table class="table table-bordered table-sm text-center align-middle mb-3">
<thead>
<tr style="background-color: {{ $colors['naranja'] }}; color:#fff; font-weight:700;">
  <th rowspan="2" style="width:50px; height:120px; background-color: {{ $colors['naranja'] }}; color:#fff; text-align:center; vertical-align:middle;">
    <div style="writing-mode: vertical-rl; transform: rotate(180deg); display:flex; justify-content:center; align-items:center; height:100%;">
      Equipo
    </div>
  </th>

  <th rowspan="2" style="width:40px; height:120px; background-color: {{ $colors['naranja'] }}; color:#fff; text-align:center; vertical-align:middle;">
    <div style="writing-mode: vertical-rl; transform: rotate(180deg); display:flex; justify-content:center; align-items:center; height:100%;">
      Turno
    </div>
  </th>

  <th rowspan="2" style="width:40px; height:120px; background-color: {{ $colors['naranja'] }}; color:#fff; text-align:center; vertical-align:middle;">
    <div style="writing-mode: vertical-rl; transform: rotate(180deg); display:flex; justify-content:center; align-items:center; height:100%;">
      Prioridad
    </div>
  </th>
@foreach ($diasSemana as $index => $dia)
    <th colspan="2" style="width:120px; background-color: {{ $colors['naranja'] }}; color:#fff;">
        <div class="d-flex flex-column align-items-center">
            {{-- Nombre del día --}}
            <span class="fw-bold">{{ $dia }}</span>

            {{-- Número del día: editable o fijo --}}
            @if($editable)
                <input 
                    type="number" 
                    wire:model="values.diaajustable.{{ $index }}" 
                    min="1" 
                    max="31" 
                    class="form-control text-center fw-semibold mt-1"
                    style="width:70px;"
                >
            @else
                <span class="mt-1">
                    {{ $values['diaajustable'][$index] ?? '' }}
                </span>
            @endif
        </div>
    </th>
@endforeach
</tr>
<tr style="background-color: {{ $colors['azulClaro'] }}; color:#fff;">
  @foreach ($diasSemana as $dia)
  <th style="width:60px; background-color: {{ $colors['azulPastel'] }}; color:#fff;">Plan</th>
  <th style="width:60px; background-color: {{ $colors['azulPastel'] }}; color:#fff;">C</th>
  @endforeach
</tr>
</thead>

<tbody>
@php
$numberText = [1 => 'Mor', 2 => 'Mid', 3 => 'Nig'];
$tiposCount = count($tipos);
$currentEquipo = null;
$groupCounter = 0;
$number = 1;
@endphp

@foreach ($turnos as $turno)
@foreach ($tipos as $tipo)
@php
if ($currentEquipo !== $equipo) {
$currentEquipo = $equipo;
$groupCounter = 0;
$number = 1;
}

$groupCounter++;
if ($groupCounter > $tiposCount) {
$groupCounter = 1;
$number++;
if ($number > 3) $number = 1;
}

/* Fondo de fila según tipo y turno */
if ($tipo === 'RMK' && $numberText[$number]) {
$bgFila = $colors['verdeClaro'];
} elseif ($numberText[$number] === 'Nig') {
$bgFila = $colors['naranja'];
} elseif ($numberText[$number] === 'Mor' || $numberText[$number] === 'Mid') {
$bgFila = ($groupCounter % 2 == 0) ? $colors['white'] : $colors['naranja'];
}
$bgEquipo = $coloresRMK[$equipo] ?? $colors['naranja'];
$currentPriority = $values[$equipo][$diasSemana[0]][$turno][$tipo]['priority']
?? ($this->getPriorityMap()[$turno][$tipo] ?? 1);
@endphp

<tr style="background-color: {{ $bgFila }};">
  {{-- Equipo --}}
  @if ($loop->parent->first && $loop->first)
  <td rowspan="{{ count($turnos) * count($tipos) }}" class="fw-semibold" style="background-color: {{ $bgEquipo }};">
    {{ $equipo }}
  </td>
  @endif

  {{-- Turno --}}
  <td class="fw-semibold"
    style="@if(in_array($tipo, ['MTTO', 'RMK'])) background-color: {{ $colors['azulMedio'] }}; @endif">
    {{ $groupCounter === 1 ? $numberText[$number] : $tipo }}
  </td>

  {{-- Prioridad --}}
  <td class="p-0" style="background-color: {{ $colors['white'] }};">
    <div class="d-flex flex-column justify-content-center align-items-center text-center text-xs px-1 py-1">
      @if ($editable)
      <input type="text"
        wire:model="values.{{ $equipo }}.{{ $diasSemana[0] }}.{{ $turno }}.{{ $tipo }}.priority"
        class="form-control form-control-sm text-center p-0 mb-1"
        min="1" max="9"
        value="{{ $currentPriority }}" />
      @else
      <span class="fw-bold">{{ $currentPriority }}</span>
      @endif
    </div>
  </td>
  @foreach ($diasSemana as $dia)
  @php
  $val = $values[$equipo][$dia][$turno][$tipo] ?? ['plan' => '', 'c' => ''];

  // Definir colores
  if ($numberText[$number] === 'Nig' && $dia === 'Sábado') {
  $bgPlan = $colors['grisOscuro'];
  $bgC = $colors['grisOscuro'];
  } elseif($tipo === 'MTTO') {
  $bgPlan = $colors['azulPastel'];
  $bgC = $colors['azulMedio'];
  } elseif($tipo === 'RMK') {
  $bgPlan = $bgFila;
  $bgC = $bgFila;
  } else {
  $bgPlan = $bgFila;
  $bgC = $colors['white'];
  }

  $placeholder = $colors['placeholder'];

  // Roles
  $isAdmin = $editable;
  $isUser = !$editable;

  // Sábado Nig nunca editable
  $isBlocked = ($numberText[$number] === 'Nig' && $dia === 'Sábado');

  // Control de edición según tipo y rol
  $canEditPlan = $isAdmin || (!$isAdmin && ($tipo === 'MTTO' || $tipo === 'RMK'));
  $canEditC = $isAdmin || (!$isAdmin && $tipo === 'RMK');

  // Bloquear sábado Nig
  if ($isBlocked) {
  $canEditPlan = false;
  $canEditC = false;
  }

  $isRMK = $tipo === 'RMK';
  $isMTTO = $tipo === 'MTTO';
  @endphp

  {{-- Plan --}}
  <td class="p-0" style="background-color: {{ $bgPlan }};">
    @if ($canEditPlan)
    <input type="{{ in_array($tipo, ['MTTO','RMK']) ? 'text' : 'number' }}"
      wire:model.defer="values.{{ $equipo }}.{{ $dia }}.{{ $turno }}.{{ $tipo }}.plan"
      class="form-control form-control-sm text-center p-0 input-plan"
      placeholder="{{ $placeholder }}"
      value="{{ (!empty($val['plan']) && $val['plan'] != 0) ? $val['plan'] : '' }}"
      style="background-color: {{ $bgPlan }};" />
    @else
    <span>{{ (!empty($val['plan']) && $val['plan'] != 0) ? $val['plan'] : $placeholder }}</span>
    @endif
  </td>

  {{-- C --}}
  <td class="p-0" style="background-color: {{ $bgC }};">
    @if ($canEditC)
    <input type="{{ $isRMK ? 'text' : 'number' }}"
      wire:model.defer="values.{{ $equipo }}.{{ $dia }}.{{ $turno }}.{{ $tipo }}.c"
      class="form-control form-control-sm text-center p-0 input-plan"
      placeholder="{{ $placeholder }}"
      value="{{ (!empty($val['c']) && $val['c'] != 0) ? $val['c'] : '' }}"
      style="background-color: {{ $bgC }};" />
    @else
    <span>{{ (!empty($val['c']) && $val['c'] != 0) ? $val['c'] : $placeholder }}</span>
    @endif
  </td>
  @endforeach
</tr>
@endforeach
@endforeach
</tbody>
</table>
</div>
@endforeach
</div>
</div>
</div>
