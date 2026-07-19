@extends('admin.layouts.app')
@section('title', 'Activity Logs')

@section('content')
<div class="page-header">
  <div class="breadcrumb-admin">
    <a href="{{ route('admin.dashboard') }}"><i class="bi bi-house-fill"></i></a>
    <i class="bi bi-chevron-right" style="font-size:10px;"></i><span>Activity Logs</span>
  </div>
  <h1 class="page-title">Activity Logs</h1>
  <p class="page-subtitle">Rekaman seluruh aktivitas administrator dalam sistem</p>
</div>

<div class="admin-card">
  <div class="admin-card-body">
    <div class="table-responsive">
      <table id="tableLogs" class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Action</th>
            <th>Description</th>
            <th>User</th>
            <th>Timestamp</th>
          </tr>
        </thead>
        <tbody>
          @foreach($logs as $i => $log)
            <tr>
              <td>{{ ($logs->currentPage() - 1) * $logs->perPage() + $i + 1 }}</td>
              <td>
                @php
                  $actionColors = [
                    'ADMIN_LOGIN'    => '#10b981',
                    'USER_CREATE'    => '#2563eb',
                    'USER_UPDATE'    => '#f59e0b',
                    'USER_DELETE'    => '#ef4444',
                    'COUNTRY_UPDATE' => '#0ea5e9',
                    'COUNTRY_DELETE' => '#dc2626',
                    'PORT_UPDATE'    => '#8b5cf6',
                    'PORT_DELETE'    => '#dc2626',
                    'NEWS_DELETE'    => '#f97316',
                    'SETTINGS_UPDATE'=> '#64748b',
                  ];
                  $ac = $actionColors[$log->action] ?? '#64748b';
                @endphp
                <span style="background:{{ $ac }}18; color:{{ $ac }}; padding:3px 10px; border-radius:99px; font-size:11px; font-weight:700;">
                  {{ $log->action }}
                </span>
              </td>
              <td style="font-size:13px; max-width:400px;">{{ $log->description }}</td>
              <td>
                <div style="font-size:13px; font-weight:600; color:var(--text);">{{ $log->user?->name ?? 'System' }}</div>
                <div style="font-size:11px; color:var(--text-muted);">{{ $log->user?->email ?? '' }}</div>
              </td>
              <td style="font-size:12px; color:var(--text-muted); white-space:nowrap;">
                {{ $log->created_at->format('d M Y H:i:s') }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Laravel Pagination --}}
    <div class="mt-3">{{ $logs->links() }}</div>
  </div>
</div>
@endsection

@section('scripts')
<script>
$('#tableLogs').DataTable({
  order: [[4, 'desc']],
  paging: false,
  info: false,
  buttons: ['excelHtml5', 'pdfHtml5', 'print'],
});
</script>
@endsection
