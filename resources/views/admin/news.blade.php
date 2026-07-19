@extends('admin.layouts.app')
@section('title', 'News Management')

@section('content')
<div class="page-header">
  <div class="breadcrumb-admin">
    <a href="{{ route('admin.dashboard') }}"><i class="bi bi-house-fill"></i></a>
    <i class="bi bi-chevron-right" style="font-size:10px;"></i><span>News</span>
  </div>
  <h1 class="page-title">News Management</h1>
  <p class="page-subtitle">Kelola artikel berita supply chain dari berbagai sumber</p>
</div>

<div class="admin-card">
  <div class="admin-card-body">
    <div class="table-responsive">
      <table id="tableNews" class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Title</th>
            <th>Country</th>
            <th>Source</th>
            <th>Sentiment</th>
            <th>Fetched</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($news as $i => $article)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td style="max-width:320px;">
                <div style="font-size:13px; font-weight:600; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $article->title }}">
                  {{ $article->title }}
                </div>
                @if($article->url)
                  <a href="{{ $article->url }}" target="_blank" style="font-size:11px; color:var(--primary); text-decoration:none;">
                    <i class="bi bi-box-arrow-up-right"></i> Read Article
                  </a>
                @endif
              </td>
              <td style="font-size:12px; color:var(--text-muted);">{{ $article->country_name ?? 'Global' }}</td>
              <td style="font-size:12px;">{{ Str::limit($article->source ?? 'Unknown', 20) }}</td>
              <td>
                @php
                  $score = $article->sentiment_score ?? 0;
                  $sentLabel = $score > 0.1 ? 'Positive' : ($score < -0.1 ? 'Negative' : 'Neutral');
                  $sentClass = $score > 0.1 ? 'badge-low' : ($score < -0.1 ? 'badge-high' : 'badge-medium');
                @endphp
                <span class="{{ $sentClass }}">{{ $sentLabel }}</span>
              </td>
              <td style="font-size:11px; color:var(--text-muted); white-space:nowrap;">
                {{ $article->fetched_at ? \Carbon\Carbon::parse($article->fetched_at)->format('d M Y H:i') : 'N/A' }}
              </td>
              <td>
                <form action="{{ route('admin.news.destroy', $article->id) }}" method="POST"
                  onsubmit="return confirm('Hapus artikel ini?');">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn-admin-danger"><i class="bi bi-trash-fill"></i></button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
$('#tableNews').DataTable({ order: [[5, 'desc']], columnDefs: [{ orderable: false, targets: 6 }] });
</script>
@endsection
