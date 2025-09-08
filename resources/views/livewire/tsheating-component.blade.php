<div class="overflow-auto">
  @if (session()->has('message'))
  @include('modals.exito')
  @endif

  <!-- Barra superior -->
  <div class="d-flex justify-content-between align-items-center py-3">
    <div class="d-flex align-items-center gap-2">
      <h4 class="text-primary fw-bold d-none d-sm-inline">Sheating</h4>
      <h4 class="text-primary fw-bold d-inline d-sm-none">YOFC</h4>
    </div>

    <div class="d-flex gap-2 ms-3">
      <button type="submit" wire:click="save" class="btn btn-primary btn-sm text-nowrap">Guardar</button>
      <button wire:click="export" class="btn btn-success btn-sm text-nowrap">Exportar Excel</button>
    </div>
  </div>
  <style>
    .input-clean {
      height: 30px;
      line-height: 1;
      padding: 0;
      font-size: 0.8rem;
      border: none;
      text-align: center;
      width: 100%;
      outline: none;
      color: #000;
    }
  </style>
  @php
  /* ==========================
  PALETA DE COLORES
  ========================== */
  $placeholder='---';
  $colors = [
  'violetaClaro' => '#7E57C2',
  'violetaOscuro' => '#333F4F',
  'amarillo' => '#FFF59D',
  'rmkDefault' => 'rgb(235,201,220)',
  'mttoBg' => 'rgb(252,228,214)',
  'mttoC' => 'rgb(217,225,242)',
  'white' => 'rgb(255,255,255)',
  ];

  /* Colores por equipo */
  $coloresRMK = [
  'SH-01' => $colors['white'],
  'SH-02' => $colors['white'],
  'SH-03' => $colors['white'],
  'SH-04' => $colors['white'],
  'SH-05' => $colors['white'],
  ];
  @endphp

  <div class="overflow-auto border rounded" style="max-height: calc(80vh - 150px);">
    <div style="min-width: 1200px;">
      @foreach ($equipos as $equipo)
      <div class="mb-4">
        <table class="table table-bordered table-sm text-nowrap text-center align-middle mb-3">
          <thead>
            <tr style="background-color: rgb(153,102,255); color: #fff; font-weight: 700;">
              <!-- Team girado 90° y centrado -->
              <th rowspan="2" style="width: 50px; height: 120px; background-color: rgb(153,102,255); color: #fff; text-align: center; vertical-align: middle;">
                <div style="writing-mode: vertical-rl; transform: rotate(180deg); display: flex; justify-content: center; align-items: center; height: 100%;">
                  Team
                </div>
              </th>

              <!-- Shift girado 90° y centrado -->
              <th rowspan="2" style="width: 40px; height: 120px; background-color: rgb(153,102,255); color: #fff; text-align: center; vertical-align: middle;">
                <div style="writing-mode: vertical-rl; transform: rotate(180deg); display: flex; justify-content: center; align-items: center; height: 100%;">
                  Shift
                </div>
              </th>

              <!-- Priority girado 90° y centrado -->
              <th rowspan="2" style="width: 40px; height: 120px; background-color: rgb(153,102,255); color: #fff; text-align: center; vertical-align: middle;">
                <div style="writing-mode: vertical-rl; transform: rotate(180deg); display: flex; justify-content: center; align-items: center; height: 100%;">
                  Priority
                </div>
              </th>


              @foreach ($diasSemana as $index => $dia)
              <th colspan="2" style="width: 120px; background-color: rgb(153,102,255); color: #fff;">
                {{ $dia }}
                @if($editable)
                <input
                  type="number"
                  wire:model="values.{{ $equipo }}.{{ $dia }}.1.1.diaajustable"
                  min="1"
                  max="31"
                  class="form-control form-control-sm text-center mt-1">
                @else
                <span class="d-block text-center mt-1">
                  {{ $values[$equipo][$dia]['1']['1']['diaajustable'] ?? \Carbon\Carbon::now()->startOfWeek()->addDays($index)->day }}
                </span>
                @endif
              </th>
              @endforeach

            </tr>
            <tr style="background-color: #B39DDB; color: #fff;">
              @foreach ($diasSemana as $dia)
              <th style="width: 60px; background-color: rgb(180,198,231); color: #fff;">Plan</th>
              <th style="width: 60px; background-color: rgb(180,198,231); color: #fff;">C</th>
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
            if ($tipo === 'RMK' && $numberText[$number] !== 'Nig') {
            $bgFila = $colors['amarillo'];
            } elseif ($numberText[$number] === 'Nig') {
            $bgFila = $colors['violetaOscuro'];
            } elseif ($numberText[$number] === 'Mor' || $numberText[$number] === 'Mid') {
            $bgFila = ($groupCounter % 2 == 0) ? $colors['white'] : $colors['violetaClaro'];
            }

            $bgEquipo = $coloresRMK[$equipo] ?? $colors['rmkDefault'];
            $currentPriority = $values[$equipo][$diasSemana[0]][$turno][$tipo]['priority']
            ?? ($this->getPriorityMap()[$turno][$tipo] ?? 1);
            @endphp

            <tr style="border-top: 2px solid #d9d9d9; background-color: {{ $bgFila }};">
              {{-- Equipo --}}
              @if ($loop->parent->first && $loop->first)
              <td rowspan="{{ count($turnos) * count($tipos) }}" class="fw-semibold" style="background-color: {{ $bgEquipo }};">
                {{ $equipo }}
              </td>
              @endif

              {{-- Turno --}}
              <td class="fw-semibold"
                style="@if(in_array($tipo, ['MTTO', 'RMK'])) background-color: {{ $colors['mttoBg'] }}; @endif">
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

              {{-- Celdas Plan y C --}}
              @foreach ($diasSemana as $dia)
              @php
              $val = $values[$equipo][$dia][$turno][$tipo] ?? ['plan' => '', 'c' => ''];

              // Colores según tipo
              if ($tipo === 'RMK' || $numberText[$number] === 'Nig') {
              $bgPlan = $bgFila;
              $bgC = $bgFila;
              } elseif($tipo === 'MTTO') {
              $bgPlan = $colors['mttoBg'];
              $bgC = $colors['mttoC'];
              } else {
              $bgPlan = $bgFila;
              $bgC = $colors['white'];
              }


              // Roles y edición
              $isRMK = $tipo === 'RMK';
              $isMtto = $tipo === 'MTTO';
              $isAdmin = $editable; // admin full
              $userNormal = !$editable; // usuario normal
              $isNig = ($numberText[$number] === 'Nig'); // turno de noche

              // Condiciones de edición
              $canEditPlan = !$isNig && ($isAdmin || ($userNormal && ($isMtto || $isRMK)));
              $canEditC = !$isNig && ($isAdmin || ($userNormal && $isRMK));
              @endphp

              {{-- Plan --}}
              <td class="p-0" style="background-color: {{ $bgPlan }};">
                @if($canEditPlan)
                <input type="text"
                  wire:model.defer="values.{{ $equipo }}.{{ $dia }}.{{ $turno }}.{{ $tipo }}.plan"
                  class="form-control-sm input-clean"
                  placeholder="{{ $placeholder }}"
                  value="{{ (!empty($val['plan']) && $val['plan'] != 0) ? $val['plan'] : '' }}"
                  style="background-color: {{ $bgPlan }};">
                @else
                <span>{{ (!empty($val['plan']) && $val['plan'] != 0) ? $val['plan'] : $placeholder }}</span>
                @endif
              </td>

              {{-- C --}}
              <td class="p-0" style="background-color: {{ $bgC }};">
                @if($canEditC)
                <input type="text"
                  wire:model.defer="values.{{ $equipo }}.{{ $dia }}.{{ $turno }}.{{ $tipo }}.c"
                  class="form-control-sm input-clean"
                  placeholder="{{ $placeholder }}"
                  value="{{ (!empty($val['c']) && $val['c'] != 0) ? $val['c'] : '' }}"
                  style="background-color: {{ $bgC }};">
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