@extends('admin.layouts.app')
@section('title', 'Reports')

@section('content')
<div class="page-header">
  <div class="breadcrumb-admin">
    <a href="{{ route('admin.dashboard') }}"><i class="bi bi-house-fill"></i></a>
    <i class="bi bi-chevron-right" style="font-size:10px;"></i><span>Reports</span>
  </div>
  <h1 class="page-title">Reports & Export</h1>
  <p class="page-subtitle">Generate dan ekspor laporan sistem dalam berbagai format</p>
</div>

<div class="row g-3">
  @php
    $reportCards = [
      ['title' => 'User Report',       'desc' => 'Daftar lengkap semua pengguna sistem',           'icon' => 'bi-people-fill',       'color' => '#2563eb', 'href' => route('admin.users')],
      ['title' => 'Country Report',    'desc' => 'Data negara, ekonomi, dan indikator risiko',      'icon' => 'bi-globe2',            'color' => '#10b981', 'href' => route('admin.countries')],
      ['title' => 'Port Report',       'desc' => 'Daftar pelabuhan internasional dan koordinat',   'icon' => 'bi-anchor',            'color' => '#0ea5e9', 'href' => route('admin.ports')],
      ['title' => 'Risk Report',       'desc' => 'Distribusi dan skor risiko semua negara',         'icon' => 'bi-exclamation-diamond-fill', 'color' => '#ef4444', 'href' => route('admin.risks')],
      ['title' => 'News Report',       'desc' => 'Artikel berita supply chain dan sentimen',        'icon' => 'bi-newspaper',         'color' => '#f59e0b', 'href' => route('admin.news')],
      ['title' => 'Activity Logs',     'desc' => 'Log aktivitas administrator sistem',              'icon' => 'bi-list-columns-reverse', 'color' => '#8b5cf6', 'href' => route('admin.logs')],
    ];
  @endphp

  @foreach($reportCards as $card)
    <div class="col-md-6 col-xl-4">
      <div class="admin-card h-100">
        <div class="admin-card-body">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div style="width:48px;height:48px;border-radius:12px;background:{{ $card['color'] }}18;color:{{ $card['color'] }};display:flex;align-items:center;justify-content:center;font-size:22px;">
              <i class="bi {{ $card['icon'] }}"></i>
            </div>
            <div>
              <div style="font-weight:700; font-size:15px; color:var(--text);">{{ $card['title'] }}</div>
              <div style="font-size:12px; color:var(--text-muted);">{{ $card['desc'] }}</div>
            </div>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ $card['href'] }}" class="btn-admin-primary" style="flex:1; justify-content:center; text-decoration:none;">
              <i class="bi bi-eye"></i> View
            </a>
            <button class="btn-admin-edit" onclick="window.print()">
              <i class="bi bi-printer-fill"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  @endforeach

</div>

{{-- Export Summary --}}
<div class="admin-card mt-4">
  <div class="admin-card-header">
    <span class="admin-card-title"><i class="bi bi-cloud-download-fill text-primary"></i> Quick Export</span>
  </div>
  <div class="admin-card-body">
    <p style="font-size:13px; color:var(--text-muted); margin-bottom:16px;">
      Export data dari halaman masing-masing menggunakan tombol <strong>Excel</strong>, <strong>PDF</strong>, atau <strong>Print</strong> yang tersedia di atas tabel DataTables.
    </p>
    <div class="d-flex flex-wrap gap-3">
      <div class="d-flex align-items-center gap-2 p-3" style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;">
        <i class="bi bi-file-earmark-excel-fill" style="color:#10b981;font-size:20px;"></i>
        <div><strong style="font-size:13px;">Excel Export</strong><div style="font-size:11px;color:var(--text-muted);">Format .xlsx via jsZip</div></div>
      </div>
      <div class="d-flex align-items-center gap-2 p-3" style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;">
        <i class="bi bi-file-earmark-pdf-fill" style="color:#ef4444;font-size:20px;"></i>
        <div><strong style="font-size:13px;">PDF Export</strong><div style="font-size:11px;color:var(--text-muted);">Format .pdf via pdfMake</div></div>
      </div>
      <div class="d-flex align-items-center gap-2 p-3" style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;">
        <i class="bi bi-printer-fill" style="color:#2563eb;font-size:20px;"></i>
        <div><strong style="font-size:13px;">Print</strong><div style="font-size:11px;color:var(--text-muted);">Browser print dialog</div></div>
      </div>
    </div>
  </div>
</div>
@endsection
