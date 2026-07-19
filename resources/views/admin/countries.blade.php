@extends('admin.layouts.app')
@section('title', 'Countries')

@section('content')
<div class="page-header">
  <div class="breadcrumb-admin">
    <a href="{{ route('admin.dashboard') }}"><i class="bi bi-house-fill"></i></a>
    <i class="bi bi-chevron-right" style="font-size:10px;"></i><span>Countries</span>
  </div>
  <h1 class="page-title">Country Management</h1>
  <p class="page-subtitle">Database negara, data ekonomi, dan informasi geografis</p>
</div>

<div class="admin-card">
  <div class="admin-card-body">
    <div class="table-responsive">
      <table id="tableCountries" class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Flag</th>
            <th>Country</th>
            <th>ISO Code</th>
            <th>Region</th>
            <th>GDP (est.)</th>
            <th>Inflation</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($countries as $i => $c)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>
                <img src="https://flagcdn.com/w28/{{ strtolower($c->code) }}.png"
                  onerror="this.style.display='none'"
                  style="height:18px; border-radius:2px; box-shadow:0 1px 3px rgba(0,0,0,.2);">
              </td>
              <td><strong style="font-size:13px;">{{ $c->name }}</strong></td>
              <td><span class="badge-user">{{ $c->code }}</span></td>
              <td style="color:var(--text-muted); font-size:13px;">{{ $c->region }}</td>
              <td style="font-size:12px;">
                @if($c->economic?->gdp)
                  ${{ number_format($c->economic->gdp / 1e12, 2) }}T
                @else
                  <span class="text-muted">N/A</span>
                @endif
              </td>
              <td style="font-size:12px;">
                @if($c->economic?->inflation)
                  {{ number_format($c->economic->inflation, 1) }}%
                @else
                  <span class="text-muted">N/A</span>
                @endif
              </td>
              <td>
                <div class="d-flex gap-2">
                  <button class="btn-admin-edit"
                    onclick="openEditCountry({{ $c->id }}, '{{ addslashes($c->name) }}', '{{ $c->code }}', '{{ addslashes($c->region) }}')">
                    <i class="bi bi-pencil-fill"></i>
                  </button>
                  <form action="{{ route('admin.countries.destroy', $c->id) }}" method="POST"
                    onsubmit="return confirm('Hapus {{ $c->name }}?');">
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

{{-- Modal: Edit Country --}}
<div class="modal fade admin-modal" id="modalEditCountry" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title fw-bold">Edit Country</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formEditCountry" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="admin-form-label">Country Name</label>
            <input type="text" name="name" id="editCName" class="admin-form-control" required>
          </div>
          <div class="mb-3">
            <label class="admin-form-label">ISO Code</label>
            <input type="text" name="code" id="editCCode" class="admin-form-control" required maxlength="5">
          </div>
          <div class="mb-3">
            <label class="admin-form-label">Region</label>
            <input type="text" name="region" id="editCRegion" class="admin-form-control" required>
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
$('#tableCountries').DataTable({ order: [[2, 'asc']] });
function openEditCountry(id, name, code, region) {
  document.getElementById('editCName').value   = name;
  document.getElementById('editCCode').value   = code;
  document.getElementById('editCRegion').value = region;
  document.getElementById('formEditCountry').action = '/admin/countries/' + id + '/update';
  new bootstrap.Modal(document.getElementById('modalEditCountry')).show();
}
</script>
@endsection
