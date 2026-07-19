@extends('admin.layouts.app')
@section('title', 'User Management')

@section('content')
<div class="page-header">
  <div class="breadcrumb-admin">
    <a href="{{ route('admin.dashboard') }}"><i class="bi bi-house-fill"></i></a>
    <i class="bi bi-chevron-right" style="font-size:10px;"></i>
    <span>Users</span>
  </div>
  <div class="d-flex justify-content-between align-items-start">
    <div>
      <h1 class="page-title">User Management</h1>
      <p class="page-subtitle">Kelola akun pengguna dan administrator sistem</p>
    </div>
    <button class="btn-admin-primary" data-bs-toggle="modal" data-bs-target="#modalAddUser">
      <i class="bi bi-person-plus-fill"></i> Add User
    </button>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-body">
    <div class="table-responsive">
      <table id="tableUsers" class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>User</th>
            <th>Email</th>
            <th>Role</th>
            <th>Registered</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($users as $i => $u)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#8b5cf6);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;">
                    {{ substr($u->name, 0, 1) }}
                  </div>
                  <strong style="font-size:13px;">{{ $u->name }}</strong>
                </div>
              </td>
              <td style="color:var(--text-muted); font-size:13px;">{{ $u->email }}</td>
              <td><span class="badge-{{ $u->role }}">{{ ucfirst($u->role) }}</span></td>
              <td style="font-size:12px; color:var(--text-muted);">{{ $u->created_at->format('d M Y') }}</td>
              <td>
                <div class="d-flex gap-2">
                  <button class="btn-admin-edit"
                    onclick="openEditUser({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->email }}', '{{ $u->role }}')">
                    <i class="bi bi-pencil-fill"></i>
                  </button>
                  @if($u->id !== auth()->id())
                    <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Hapus user {{ $u->name }}?');">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn-admin-danger"><i class="bi bi-trash-fill"></i></button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Modal: Add User --}}
<div class="modal fade admin-modal" id="modalAddUser" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title fw-bold">Add New User</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="admin-form-label">Full Name</label>
            <input type="text" name="name" class="admin-form-control" required placeholder="John Doe">
          </div>
          <div class="mb-3">
            <label class="admin-form-label">Email Address</label>
            <input type="email" name="email" class="admin-form-control" required placeholder="john@example.com">
          </div>
          <div class="mb-3">
            <label class="admin-form-label">Password</label>
            <input type="password" name="password" class="admin-form-control" required placeholder="Min 8 characters">
          </div>
          <div class="mb-3">
            <label class="admin-form-label">Role</label>
            <select name="role" class="admin-form-control">
              <option value="user">User</option>
              <option value="admin">Admin</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-admin-danger" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-admin-primary"><i class="bi bi-check-lg"></i> Create User</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Modal: Edit User --}}
<div class="modal fade admin-modal" id="modalEditUser" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title fw-bold">Edit User</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formEditUser" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="admin-form-label">Full Name</label>
            <input type="text" name="name" id="editName" class="admin-form-control" required>
          </div>
          <div class="mb-3">
            <label class="admin-form-label">Email Address</label>
            <input type="email" name="email" id="editEmail" class="admin-form-control" required>
          </div>
          <div class="mb-3">
            <label class="admin-form-label">New Password <small style="color:var(--text-muted);">(kosongkan jika tidak diubah)</small></label>
            <input type="password" name="password" class="admin-form-control" placeholder="Min 8 characters">
          </div>
          <div class="mb-3">
            <label class="admin-form-label">Role</label>
            <select name="role" id="editRole" class="admin-form-control">
              <option value="user">User</option>
              <option value="admin">Admin</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-admin-danger" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-admin-primary"><i class="bi bi-check-lg"></i> Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
$('#tableUsers').DataTable({
  order: [[4, 'desc']],
  columnDefs: [{ orderable: false, targets: 5 }],
});

function openEditUser(id, name, email, role) {
  document.getElementById('editName').value  = name;
  document.getElementById('editEmail').value = email;
  document.getElementById('editRole').value  = role;
  document.getElementById('formEditUser').action = '/admin/users/' + id + '/update';
  new bootstrap.Modal(document.getElementById('modalEditUser')).show();
}
</script>
@endsection
