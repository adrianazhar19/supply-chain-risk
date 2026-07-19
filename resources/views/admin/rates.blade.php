@extends('admin.layouts.app')
@section('title', 'Exchange Rates')

@section('content')
<div class="page-header">
  <div class="breadcrumb-admin">
    <a href="{{ route('admin.dashboard') }}"><i class="bi bi-house-fill"></i></a>
    <i class="bi bi-chevron-right" style="font-size:10px;"></i><span>Exchange Rates</span>
  </div>
  <h1 class="page-title">Exchange Rates</h1>
  <p class="page-subtitle">Data kurs mata uang terhadap USD dari API</p>
</div>

<div class="admin-card">
  <div class="admin-card-body">
    <div class="table-responsive">
      <table id="tableRates" class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Base Currency</th>
            <th>Target Currency</th>
            <th>Exchange Rate</th>
            <th>Last Updated</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach($rates as $i => $r)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td><span class="badge-user">{{ $r->base_currency ?? 'USD' }}</span></td>
              <td><strong style="font-size:13px;">{{ $r->target_currency }}</strong></td>
              <td><strong style="font-size:14px; color:var(--primary);">{{ number_format($r->exchange_rate, 6) }}</strong></td>
              <td style="font-size:12px; color:var(--text-muted);">{{ $r->updated_at->diffForHumans() }}</td>
              <td><span class="badge-online">Synchronized</span></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>$('#tableRates').DataTable({ order: [[2, 'asc']] });</script>
@endsection
