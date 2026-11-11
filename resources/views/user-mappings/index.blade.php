@extends('layouts.app')

@section('title', 'User Mapping')

@section('content')
    {{-- Header halaman --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="h4 fw-bold mb-1">User Mapping</h2>
            <p class="text-muted mb-0">Hubungkan akun Redmine dengan akun Telegram pengguna.</p>
        </div>
        <button class="btn btn-primary" id="btnAdd" data-bs-toggle="modal" data-bs-target="#mappingModal">
            <i class="fa-solid fa-user-plus me-1"></i> Tambah Mapping
        </button>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h3 class="h6 fw-bold mb-0"><i class="fa-solid fa-users-gear text-primary me-2"></i>Daftar User Terhubung</h3>
            <form method="GET" action="{{ route('user-mappings.index') }}" class="input-group input-group-sm" style="max-width:260px">
                <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="search" name="q" value="{{ $search }}" class="form-control border-start-0" placeholder="Cari user...">
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Redmine User ID</th>
                        <th>Telegram Username</th>
                        <th>Chat ID</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mappings as $m)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-semibold flex-shrink-0">
                                        {{ strtoupper(collect(preg_split('/\s+/', trim($m->name)))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->join('')) }}
                                    </span>
                                    <span class="fw-semibold">{{ $m->name }}</span>
                                </div>
                            </td>
                            <td><code>{{ $m->redmine_user_id }}</code></td>
                            <td>
                                @if ($m->telegram_username)
                                    <i class="fa-brands fa-telegram text-primary me-1"></i>{{ '@' . $m->telegram_username }}
                                @else
                                    <span class="text-muted fst-italic">-</span>
                                @endif
                            </td>
                            <td><code>{{ $m->chat_id }}</code></td>
                            <td>
                                @if ($m->is_active)
                                    <span class="badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle">
                                        <i class="fa-solid fa-circle-check me-1"></i>Aktif
                                    </span>
                                @else
                                    <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                        <i class="fa-solid fa-circle-xmark me-1"></i>Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <button class="btn btn-sm btn-outline-primary btn-edit" title="Edit"
                                        data-bs-toggle="modal" data-bs-target="#mappingModal"
                                        data-action="{{ route('user-mappings.update', $m) }}"
                                        data-name="{{ $m->name }}"
                                        data-redmine="{{ $m->redmine_user_id }}"
                                        data-telegram="{{ $m->telegram_username }}"
                                        data-chat="{{ $m->chat_id }}"
                                        data-active="{{ $m->is_active ? 1 : 0 }}">
                                    <i class="fa-solid fa-pen-to-square"></i><span class="d-none d-xl-inline ms-1">Edit</span>
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-delete" title="Hapus"
                                        data-bs-toggle="modal" data-bs-target="#deleteModal"
                                        data-action="{{ route('user-mappings.destroy', $m) }}"
                                        data-name="{{ $m->name }}">
                                    <i class="fa-solid fa-trash"></i><span class="d-none d-xl-inline ms-1">Delete</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fa-solid fa-user-slash fa-2x mb-2 d-block"></i>
                                {{ $search !== '' ? 'Tidak ada data yang cocok.' : 'Belum ada user yang terhubung.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center small text-muted">
            <span>Menampilkan {{ $mappings->count() }} dari {{ $mappings->total() }} user</span>
            @if ($mappings->hasPages())
                {{ $mappings->links('pagination::bootstrap-5') }}
            @endif
        </div>
    </div>

    {{-- ================= MODAL TAMBAH / EDIT ================= --}}
    <div class="modal fade" id="mappingModal" tabindex="-1" aria-labelledby="mappingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="mappingForm" action="{{ route('user-mappings.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST" disabled>
                <input type="hidden" name="_mode" id="formMode" value="{{ old('_mode', 'create') }}">
                <input type="hidden" name="_action" id="formAction" value="{{ old('_action') }}">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="mappingModalLabel">
                        <i class="fa-solid fa-link text-primary me-2"></i><span id="modalTitle">Tambah Mapping</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    @if ($errors->any())
                        <div class="alert alert-danger py-2 small">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="name" class="form-label fw-medium">Nama</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="{{ old('name') }}" placeholder="contoh: Budi Santoso" required>
                    </div>
                    <div class="mb-3">
                        <label for="redmine_user_id" class="form-label fw-medium">Redmine User ID</label>
                        <input type="text" class="form-control" id="redmine_user_id" name="redmine_user_id"
                               value="{{ old('redmine_user_id') }}" placeholder="contoh: 12" required>
                    </div>
                    <div class="mb-3">
                        <label for="telegram_username" class="form-label fw-medium">Telegram Username</label>
                        <div class="input-group">
                            <span class="input-group-text">@</span>
                            <input type="text" class="form-control" id="telegram_username" name="telegram_username"
                                   value="{{ old('telegram_username') }}" placeholder="username">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="chat_id" class="form-label fw-medium">Chat ID Telegram</label>
                        <input type="text" class="form-control" id="chat_id" name="chat_id" inputmode="numeric"
                               value="{{ old('chat_id') }}" placeholder="contoh: 512334871" required>
                        <div class="form-text">User dapat mengirim <code>/start</code> ke bot untuk mendapatkan Chat ID.</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                               @checked(old('_mode') ? old('is_active') : true)>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= MODAL HAPUS ================= --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <form class="modal-content" id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body text-center p-4">
                    <i class="fa-solid fa-triangle-exclamation fa-2x text-danger mb-3"></i>
                    <p class="mb-0">Hapus mapping <strong id="deleteName"></strong>?</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const form = document.getElementById('mappingForm');
    const storeUrl = @json(route('user-mappings.store'));
    const fields = ['name', 'redmine_user_id', 'telegram_username', 'chat_id'];

    function setMode(mode, action) {
        const edit = mode === 'edit';
        form.action = edit ? action : storeUrl;
        document.getElementById('formMethod').disabled = !edit;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('formMode').value = mode;
        document.getElementById('formAction').value = edit ? action : '';
        document.getElementById('modalTitle').textContent = edit ? 'Edit Mapping' : 'Tambah Mapping';
    }

    document.getElementById('btnAdd').addEventListener('click', () => {
        setMode('create');
        form.querySelectorAll('.alert').forEach(a => a.remove());
        fields.forEach(f => form.elements[f].value = '');
        form.elements.is_active.checked = true;
    });

    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = btn.dataset;
            setMode('edit', d.action);
            form.querySelectorAll('.alert').forEach(a => a.remove());
            form.elements.name.value = d.name;
            form.elements.redmine_user_id.value = d.redmine;
            form.elements.telegram_username.value = d.telegram;
            form.elements.chat_id.value = d.chat;
            form.elements.is_active.checked = d.active === '1';
        });
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('deleteForm').action = btn.dataset.action;
            document.getElementById('deleteName').textContent = btn.dataset.name;
        });
    });

    // Buka kembali modal beserta mode-nya jika validasi gagal
    @if ($errors->any())
        setMode(@json(old('_mode', 'create')), @json(old('_action')));
        bootstrap.Modal.getOrCreateInstance(document.getElementById('mappingModal')).show();
    @endif
</script>
@endpush
