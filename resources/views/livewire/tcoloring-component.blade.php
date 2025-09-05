<div class="overflow-auto">
  @if (session()->has('message'))
  @include('modals.exito')
  @endif

  <div class="d-flex justify-content-between align-items-center w-100 py-3">
    <!-- Logo/Título -->
    <div class="text-primary fw-bold fs-5 d-flex align-items-center gap-2">
      <span class="d-none d-sm-inline">Coloring</span>
      <span class="d-sm-none">YOFC</span>
    </div>

    <!-- Botones alineados a la derecha -->
    <div class="d-flex gap-2 ms-3">
      <button type="submit" wire:click="save" class="btn btn-primary btn-sm text-nowrap">
        Guardar
      </button>
      <button wire:click="export" class="btn btn-success btn-sm text-nowrap">
        Exportar Excel
      </button>
    </div>
  </div>

  <!-- Contenedor principal para todas las tablas -->
  <div class="overflow-auto" style="max-height: 600px;">
    <form wire:submit.prevent="save">
      @foreach($equipos as $equipo)
      <!-- Contenedor de cada tabla de equipo con margen inferior -->
      <div class="mb-4" style="border: 1px solid #dee2e6; border-radius: 0.375rem;">
        <div style="min-width: 1000px;">
          <table class="table table-bordered table-sm text-center align-middle mb-0 w-100" style="table-layout: fixed;">
            <thead class="table-light">
              <tr>
                <th rowspan="2" style="width: 25px;">Equipo</th>
                <th rowspan="2" style="width: 30px;">Día</th>
                <th rowspan="2" style="width: 25px;">Turno</th>
                @foreach($tiposFibra as $fibra)
                <th colspan="3" class="fw-semibold text-black" style="background-color: {{ strtolower($fibra) }}; width: 50px; height: 50px;">
                  {{ $fibra }}
                </th>
                @endforeach
              </tr>
              <tr class="table-secondary">
                @foreach($tiposFibra as $fibra)
                <th style="width: 33px;">Plan</th>
                <th style="width: 33px;">C</th>
                <th style="width: 33px;">Type</th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              @foreach($dias as $dia)
              @foreach($turnos as $turno)
              <tr>
                {{-- Primera celda: Equipo --}}
                @if($dia === $dias[0] && $turno === $turnos[0])
                <td rowspan="{{ count($dias) * count($turnos) }}" class="align-middle bg-warning fw-semibold text-center">
                  {{ $equipo }}
                </td>
                @endif

                {{-- Segunda celda: Día --}}
                @if($turno === $turnos[0])
                <td rowspan="{{ count($turnos) }}" class="align-middle bg-warning fw-semibold text-center">
                  {{ $dia }}
                </td>
                @endif

                {{-- Tercera celda: Turno --}}
                <td class="align-middle bg-light fw-semibold text-center p-0">
                  @if($editable)
                  <input type="text"
                    class="form-control form-control-sm text-center border-0"
                    wire:model.defer="values.{{ $equipo }}.{{ $dia }}.{{ $turno }}.{{ $tiposFibra[0] }}.turno_actual">
                  @else
                  {{ $values[$equipo][$dia][$turno][$tiposFibra[0]]['turno_actual'] ?? $turno }}
                  @endif
                </td>

                {{-- Campos por fibra --}}
                @foreach($tiposFibra as $fibra)
                @php
                $val = $values[$equipo][$dia][$turno][$fibra] ?? ['plan'=>0, 'c'=>0, 'type'=>0];
                @endphp

                {{-- Plan --}}
                <td class="p-0">
                  <div class="d-flex flex-column gap-1">
                    <input
                      type="number"
                      wire:model.defer="values.{{ $equipo }}.{{ $dia }}.{{ $turno }}.{{ $fibra }}.plan"
                      class="form-control form-control-sm text-center p-1"
                      min="0"
                      placeholder="0"
                      @if(!$editable) disabled @endif />
                    <input
                      type="number"
                      wire:model.defer="values.{{ $equipo }}.{{ $dia }}.{{ $turno }}.{{ $fibra }}.planuser"
                      class="form-control form-control-sm text-center p-1"
                      placeholder="0" />
                  </div>
                </td>

                {{-- C --}}
                <td class="p-0">
                  <div class="d-flex flex-column gap-1">
                    <input
                      type="number"
                      wire:model.defer="values.{{ $equipo }}.{{ $dia }}.{{ $turno }}.{{ $fibra }}.c"
                      class="form-control form-control-sm text-center p-1"
                      min="0"
                      @if(!$editable) disabled @endif />
                    <input
                      type="number"
                      wire:model.defer="values.{{ $equipo }}.{{ $dia }}.{{ $turno }}.{{ $fibra }}.cuser"
                      class="form-control form-control-sm text-center p-1"
                      placeholder="0" />
                  </div>
                </td>

                {{-- Type --}}
                <td class="p-0">
                  <div class="d-flex flex-column gap-1">
                    <input
                      type="number"
                      wire:model.defer="values.{{ $equipo }}.{{ $dia }}.{{ $turno }}.{{ $fibra }}.type"
                      class="form-control form-control-sm text-center p-1"
                      min="0"
                      @if(!$editable) disabled @endif />
                    <input
                      type="number"
                      wire:model.defer="values.{{ $equipo }}.{{ $dia }}.{{ $turno }}.{{ $fibra }}.typeuser"
                      class="form-control form-control-sm text-center p-1"
                      placeholder="0" />
                  </div>
                </td>
                @endforeach
              </tr>
              @endforeach
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      @endforeach
    </form>
  </div>
</div>