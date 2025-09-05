<div class="overflow-auto">
  @if (session()->has('message'))
  @include('modals.exito')
  @endif

  <!-- Barra superior -->
  <div class="d-flex justify-content-between align-items-center w-100 py-3">
    <div class="fs-4 fw-bold text-primary d-flex align-items-center gap-2">
      <span class="d-none d-sm-inline">Sheating</span>
      <span class="d-sm-none">YOFC</span>
    </div>

    <div class="d-flex gap-2 ms-3">
      <button type="submit" wire:click="save" class="btn btn-primary btn-sm text-nowrap">Guardar</button>
      <button wire:click="export" class="btn btn-success btn-sm text-nowrap">Exportar Excel</button>
    </div>
  </div>
  <style>
    .input-clean {
      border: none;
      outline: none;
      padding: 0;
      margin: 0;
      width: 100%;
      height: 30px;
      /* altura fija */
      line-height: 1;
      /* centrar vertical */
      text-align: center;
      font-size: 0.8rem;
      color: #000;
      background-color: transparent;
      /* se sobreescribe inline si se necesita */
    }
  </style>

  @php
  /* Paleta de colores */
  $colors = [
  'header' => '#00c894', // Team / Shift / Priority / Día
  'planHeader' => '#b4c6e7', // Encabezado Plan / C
  'planDefault' => '#ededed', // Plan por defecto
  'cDefault' => '#c9c9c9', // C por defecto
  'rmk' => '#ffffcc', // RMK
  'placeholder' => '---',
  'green' => '#00c894',
  'white' => '#ffffff',
  ];

  $coloresRMK = [
  'SC-01' => $colors['rmk'],
  'SC-02' => $colors['rmk'],
  'SC-03' => $colors['rmk'],
  ];

  $defaultRMK = $colors['rmk'];
  @endphp

  <div class="overflow-auto border border-secondary rounded" style="max-height: calc(80vh - 150px);">
    <div style="min-width: 1200px;">
      @foreach($equipos as $equipo)
      <div class="mb-4">
        <table class="table table-bordered table-sm text-center align-middle mb-4" style="table-layout: fixed;">
          <thead>
            <tr>
              <!-- Team girado 90° y centrado -->
              <th rowspan="2" style="width: 50px; height: 120px; background-color: {{ $colors['header'] }}; color: #fff; text-align: center; vertical-align: middle;">
                <div style="writing-mode: vertical-rl; transform: rotate(180deg); display: flex; justify-content: center; align-items: center; height: 100%;">
                  Team
                </div>
              </th>

              <!-- Shift girado 90° y centrado -->
              <th rowspan="2" style="width: 40px; height: 120px; background-color: {{ $colors['header'] }}; color: #fff; text-align: center; vertical-align: middle;">
                <div style="writing-mode: vertical-rl; transform: rotate(180deg); display: flex; justify-content: center; align-items: center; height: 100%;">
                  Shift
                </div>
              </th>

              <!-- Priority girado 90° y centrado -->
              <th rowspan="2" style="width: 40px; height: 120px; background-color: {{ $colors['header'] }}; color: #fff; text-align: center; vertical-align: middle;">
                <div style="writing-mode: vertical-rl; transform: rotate(180deg); display: flex; justify-content: center; align-items: center; height: 100%;">
                  Priority
                </div>
              </th>
              @foreach($diasSemana as $dia)
              <th colspan="2" style="width:75px; background-color: {{ $colors['header'] }}; color:#fff;">{{ $dia }}</th>
              @endforeach
            </tr>
            <tr>
              @foreach($diasSemana as $dia)
              <th style="width:37px; background-color: {{ $colors['planHeader'] }}; color:#fff;">Plan</th>
              <th style="width:37px; background-color: {{ $colors['planHeader'] }}; color:#fff;">C</th>
              @endforeach
            </tr>
          </thead>

          <tbody>
            @php
            $numberText = [1=>'Mor', 2=>'Mid', 3=>'Nig'];
            $tiposCount = count($tipos);
            $currentEquipo = null;
            $groupCounter = 0;
            $number = 1;
            @endphp

            @foreach($turnos as $turno)
            @foreach($tipos as $tipo)
            @php
            if ($currentEquipo !== $equipo) {
            $currentEquipo = $equipo;
            $groupCounter = 0;
            $number = 1;
            }

            $groupCounter++;
            if ($groupCounter > 3) {
            $groupCounter = 1;
            $number++;
            if ($number > 3) $number = 1;
            }

            $bgEquipo = $coloresRMK[$equipo] ?? $defaultRMK;
            $currentPriority = $values[$equipo][$diasSemana[0]][$turno][$tipo]['priority']
            ?? ($this->getPriorityMap()[$turno][$tipo] ?? 1);
            @endphp

            <tr>
              {{-- Equipo --}}
              @if($loop->parent->first && $loop->first)
              <td rowspan="{{ count($turnos)*count($tipos) }}" class="fw-semibold text-center align-middle">
                {{ $equipo }}
              </td>
              @endif

              {{-- Turno --}}
              <td class="fw-semibold" style="{{ in_array($tipo,['MTTO','RMK']) ? "background-color:$colors[rmk];" : '' }}">
                {{ $groupCounter===1 ? $numberText[$number] : $tipo }}
              </td>

              {{-- Prioridad --}}
              <td class="p-0" style="background-color: {{ $colors['white'] }};">
                <div class="d-flex justify-content-center align-items-center text-center text-xs px-1 py-1">
                  @if($editable)
                  <input type="number"
                    wire:model="values.{{ $equipo }}.{{ $diasSemana[0] }}.{{ $turno }}.{{ $tipo }}.priority"
                    class="form-control form-control-sm text-center p-0 mb-1"
                    min="1" max="9"
                    style="width:40px;"
                    value="{{ $currentPriority }}" />
                  @else
                  <span class="fw-bold">{{ $currentPriority }}</span>
                  @endif
                </div>
              </td>

              {{-- Plan y C --}}
              @foreach($diasSemana as $dia)
              @php
              $val = $values[$equipo][$dia][$turno][$tipo] ?? ['plan'=>null,'c'=>null];

              if ($tipo === 'RMK') {
              $bgPlan = $colors['rmk'];
              $bgC = $colors['rmk'];
              } elseif ($groupCounter === 1) {
              $bgPlan = $number % 2 == 1 ? $colors['green'] : $colors['white'];
              $bgC = $number % 2 == 1 ? $colors['white'] : $colors['green'];
              } elseif ($tipo === 'MTTO') {
              $bgPlan = $colors['planDefault'];
              $bgC = $colors['cDefault'];
              } else {
              $bgPlan = $colors['planDefault'];
              $bgC = $colors['cDefault'];
              }

              $placeholder = $colors['placeholder'];
              @endphp

              {{-- Plan --}}
              @php
              $isRMK = $tipo === 'RMK';
              $isMtto = $tipo === 'MTTO';
              $isAdmin = $editable; // Admin full
              $userNormal = !$editable; // Usuario normal
              $isNig = $numberText[$number] === 'Nig';

              $planValue = (!empty($val['plan']) && $val['plan'] != 0) ? $val['plan'] : $placeholder;
              $cValue = (!empty($val['c']) && $val['c'] != 0) ? $val['c'] : $placeholder;

              // Filtrado de edición
              $canEditPlan = ($isAdmin) || ($userNormal && ($isRMK || $isMtto));
              $canEditC = ($isAdmin) || ($userNormal && $isRMK);
              @endphp

              <td class="p-0" style="background-color: {{ $bgPlan }};">
                @if($canEditPlan && !$isNig)
                <input type="{{ $isRMK || $isMtto ? 'text' : 'number' }}"
                  wire:model.defer="values.{{ $equipo }}.{{ $dia }}.{{ $turno }}.{{ $tipo }}.plan"
                  class="form-control form-control-sm text-center p-0 input-clean"
                  placeholder="{{ $placeholder }}"
                  value="{{ $planValue }}"
                  style="background-color: {{ $bgPlan }};" />
                @else
                <span>{{ $planValue }}</span>
                @endif
              </td>

              <td class="p-0" style="background-color: {{ $bgC }};">
                @if($canEditC && !$isNig)
                <input type="text"
                  wire:model.defer="values.{{ $equipo }}.{{ $dia }}.{{ $turno }}.{{ $tipo }}.c"
                  class="form-control form-control-sm text-center p-0 input-clean"
                  placeholder="{{ $placeholder }}"
                  value="{{ $cValue }}"
                  style="background-color: {{ $bgC }};" />
                @else
                <span>{{ $cValue }}</span>
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