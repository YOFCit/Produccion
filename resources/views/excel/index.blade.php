@extends('welcome')
@section('title', 'Plan de Producción')
@section('datos')

<div class="container-fluid">

  <!-- Luckysheet CSS -->
  <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/luckysheet@latest/dist/plugins/css/pluginsCss.css' />
  <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/luckysheet@latest/dist/plugins/plugins.css' />
  <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/luckysheet@latest/dist/css/luckysheet.css' />
  <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/luckysheet@latest/dist/assets/iconfont/iconfont.css' />
  <!-- Luckysheet JS -->
  <script src="https://cdn.jsdelivr.net/npm/luckysheet@latest/dist/plugins/js/plugin.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/luckysheet@latest/dist/luckysheet.umd.js"></script>
  <style>
    .luckysheet-modal-dialog-slider {
      margin-top: 55px;
      margin-bottom: 55px;
    }
  </style>
  {{-- HEADER --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold text-primary">📊 Plan de Producción</h2>
    <div>
      <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">📁 Upload / Update Excel</button>
      <button id="saveBtn" class="btn btn-success btn-sm">💾 Save Changes</button>
    </div>
  </div>

  {{-- CONTENEDOR --}}
  <div id="excelContainer" class="border rounded shadow-sm" style="width:100%; height:80vh;"></div>

</div>

{{-- MODAL UPLOAD --}}
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="uploadModalLabel">Upload Excel File</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formUpload" enctype="multipart/form-data">
          @csrf
          <input type="file" name="file" id="excelFileInput" accept=".xlsx,.xls" class="form-control">
        </form>
        <div id="uploadStatus" class="mt-2 text-center text-info"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button id="uploadBtn" class="btn btn-primary btn-sm">Upload</button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    let isUploading = false;
    let roleAccess = {};

    // Datos iniciales del backend
    let initialData = JSON.parse('{!! addslashes(json_encode($excelData ?? [])) !!}');

    function initLuckysheet(data = null) {
      document.getElementById("excelContainer").innerHTML = "";

      // Si no hay datos, crear hoja básica
      if (!data || data.length === 0) {
        data = [{
          name: 'Producción',
          color: '#2196F3',
          index: 0,
          status: 1,
          order: 0,
          row: 50,
          column: 15,
          config: {},
          celldata: [{
              r: 0,
              c: 0,
              v: {
                m: "Línea",
                v: "Línea",
                ct: {
                  fa: "General",
                  t: "g"
                }
              }
            },
            {
              r: 0,
              c: 1,
              v: {
                m: "Turno",
                v: "Turno",
                ct: {
                  fa: "General",
                  t: "g"
                }
              }
            },
            {
              r: 0,
              c: 2,
              v: {
                m: "Cantidad",
                v: "Cantidad",
                ct: {
                  fa: "General",
                  t: "g"
                }
              }
            },
            {
              r: 0,
              c: 3,
              v: {
                m: "Fecha",
                v: "Fecha",
                ct: {
                  fa: "dd/mm/yyyy",
                  t: "d"
                }
              }
            },
          ],
          protectionPassword: "",
          roles: ["admin", "supervisor"]
        }];
      }

      // Configuración completa de Luckysheet
      luckysheet.create({
        container: 'excelContainer',
        lang: 'es',
        showinfobar: true,
        showtoolbar: true,
        showsheetbar: true,
        showstatisticbar: true,
        showcontextmenu: true,
        showInsertRow: true,
        showInsertColumn: true,
        showChart: true,
        showconfigration: true,
        allowEdit: true,
        enableAddRow: true,
        enableAddBackTop: true,
        enableAddChart: true,
        data: data,
        plugins: ['chart'],
        hook: {
          cellRightClick: function(r, c, cell, sheet) {
            if (cell && cell.v && cell.v.locked) {
              return false;
            }
          },
          cellEdit: function(r, c, cell, sheet) {
            if (cell && cell.v && cell.v.locked) {
              alert("Esta celda está protegida");
              return false;
            }
          }
        }
      });
    }

    // Inicializar Luckysheet
    initLuckysheet(initialData);

    // Guardar Excel
    document.getElementById('saveBtn').addEventListener('click', function() {
      if (isUploading) {
        alert("Espere a que termine la subida.");
        return;
      }

      // Obtener todos los datos de Luckysheet incluyendo formatos
      let data = luckysheet.getAllSheets().map(sheet => {
        return {
          ...sheet,
          protectionPassword: sheet.protectionPassword || "",
          roles: sheet.roles || ["admin", "supervisor"]
        };
      });

      fetch('{{ route("excel.save") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({
            excel: data
          })
        })
        .then(res => res.json())
        .then(res => {
          if (res.success) {
            alert("✅ Guardado correctamente");
            // Recargar los datos actualizados
            location.reload();
          } else {
            alert("❌ Error al estar guardando");
          }
        })
        .catch((error) => {
          console.error('Error:', error);
          alert("❌ Error al estar guardando");
        });
    });

    // Subir Excel
    document.getElementById('uploadBtn').addEventListener('click', function() {
      const file = document.getElementById('excelFileInput').files[0];
      const status = document.getElementById('uploadStatus');

      if (!file) {
        alert("Selecciona un archivo primero");
        return;
      }

      const allowed = [
        "application/vnd.ms-excel",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
      ];

      if (!allowed.includes(file.type)) {
        alert("Tipo de archivo inválido");
        return;
      }

      if (file.size > 10 * 1024 * 1024) {
        alert("Archivo demasiado grande");
        return;
      }

      isUploading = true;
      status.textContent = "Subiendo...";

      let formData = new FormData();
      formData.append("file", file);
      formData.append("_token", "{{ csrf_token() }}");

      fetch('{{ route("excel.upload") }}', {
          method: "POST",
          body: formData
        })
        .then(res => res.json())
        .then(res => {
          if (res.success) {
            status.textContent = "✅ Subida completada";
            initLuckysheet(res.data);
            setTimeout(() => {
              document.querySelector("#uploadModal .btn-close").click();
            }, 1000);
          } else {
            status.textContent = "❌ Error al estar subiendo";
          }
        })
        .catch((error) => {
          console.error('Error:', error);
          status.textContent = "❌ Error al estar subiendo";
        })
        .finally(() => isUploading = false);
    });

  });
</script>

@endsection