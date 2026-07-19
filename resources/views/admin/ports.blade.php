@extends('admin.layouts.app')
@section('title', 'Ports')

@section('content')
<div class="page-header">
  <div class="breadcrumb-admin">
    <a href="{{ route('admin.dashboard') }}"><i class="bi bi-house-fill"></i></a>
    <i class="bi bi-chevron-right" style="font-size:10px;"></i><span>Ports</span>
  </div>
  <h1 class="page-title">Port Management</h1>
  <p class="page-subtitle">Database pelabuhan internasional dan koordinat geografis</p>
</div>

<div class="admin-card">
  <div class="admin-card-body">
    <div class="table-responsive">
      <table id="tablePorts" class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Port Name</th>
            <th>Country</th>
            <th>Harbor Type</th>
            <th>Harbor Size</th>
            <th>Latitude</th>
            <th>Longitude</th>
            <th>WPI Code</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($ports as $i => $p)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td><strong style="font-size:13px;">{{ $p->name }}</strong></td>
              <td style="font-size:13px; color:var(--text-muted);">{{ $p->country?->name ?? 'N/A' }}</td>
              <td>
                @php
                  $typeColors = ['Container Port' => '#8b5cf6', 'Oil Terminal' => '#0f172a', 'Major Port' => '#2563eb'];
                  $tc = $typeColors[$p->harbor_type] ?? '#64748b';
                @endphp
                <span style="background:{{ $tc }}20; color:{{ $tc }}; padding:2px 8px; border-radius:99px; font-size:11px; font-weight:600;">
                  {{ $p->harbor_type ?? 'Port' }}
                </span>
              </td>
              <td>
                @php
                  $sizeColors = ['Very Large' => '#ef4444', 'Large' => '#f97316', 'Medium' => '#f59e0b', 'Small' => '#10b981', 'Very Small' => '#64748b'];
                  $sc = $sizeColors[$p->harbor_size] ?? '#64748b';
                @endphp
                <span style="background:{{ $sc }}18; color:{{ $sc }}; padding:2px 8px; border-radius:99px; font-size:11px; font-weight:600;">
                  {{ $p->harbor_size ?? 'N/A' }}
                </span>
              </td>
              <td style="font-size:12px; color:var(--text-muted);">{{ number_format($p->latitude, 4) }}</td>
              <td style="font-size:12px; color:var(--text-muted);">{{ number_format($p->longitude, 4) }}</td>
              <td style="font-size:12px;"><span class="badge-user">{{ $p->wpi_code ?? 'N/A' }}</span></td>
              <td>
                <div class="d-flex gap-2">
                  <button class="btn-admin-edit"
                    onclick="openEditPort({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->latitude }}, {{ $p->longitude }})">
                    <i class="bi bi-pencil-fill"></i>
                  </button>
                  <form action="{{ route('admin.ports.destroy', $p->id) }}" method="POST"
                    onsubmit="return confirm('Hapus port ini?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-admin-danger"><i class="bi bi-trash-fill"></i></button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Modal: Edit Port --}}
<div class="modal fade admin-modal" id="modalEditPort" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title fw-bold">Edit Port</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formEditPort" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="admin-form-label">Port Name</label>
            <input type="text" name="name" id="editPName" class="admin-form-control" required>
          </div>
          <div class="mb-3">
            <label class="admin-form-label">Latitude</label>
            <input type="number" step="any" name="latitude" id="editPLat" class="admin-form-control" required>
          </div>
          <div class="mb-3">
            <label class="admin-form-label">Longitude</label>
            <input type="number" step="any" name="longitude" id="editPLng" class="admin-form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-admin-danger" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-admin-primary"><i class="bi bi-check-lg"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
$('#tablePorts').DataTable({ order: [[1, 'asc']] });
function openEditPort(id, name, lat, lng) {
  document.getElementById('editPName').value = name;
  document.getElementById('editPLat').value  = lat;
  document.getElementById('editPLng').value  = lng;
  document.getElementById('formEditPort').action = '/admin/ports/' + id + '/update';
  new bootstrap.Modal(document.getElementById('modalEditPort')).show();
}
</script>
@endsection
