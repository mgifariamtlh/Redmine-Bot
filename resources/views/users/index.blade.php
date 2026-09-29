@extends('layouts.app')

@section('title', 'User Mapping')

@php
    $statusMap = [
        'connected'    => ['success',   'Connected',    'fa-circle-check'],
        'pending'      => ['warning',   'Pending',      'fa-hourglass-half'],
        'disconnected' => ['secondary', 'Disconnected', 'fa-circle-xmark'],
    ];
@endphp

@section('content')
    {{-- Header halaman --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="h4 fw-bold mb-1">User Mapping</h2>
            <p class="text-muted mb-0">Hubungkan akun Redmine dengan akun Telegram pengguna.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">
            <i class="fa-solid fa-user-plus me-1"></i> Tambah / Link User
        </button>
    </div>

    <div class="card">
        <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h3 class="h6 fw-bold mb-0"><i class="fa-solid fa-users-gear text-primary me-2"></i>Daftar User Terhubung</h3>
            <div class="input-group input-group-sm" style="max-width:260px">
                <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="search" class="form-control border-start-0" placeholder="Cari user...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nama User Redmine</th>
                        <th>Telegram Username</th>
                        <th>Chat ID</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php [$color, $label, $icon] = $statusMap[$user['status']] ?? $statusMap['disconnected']; @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-semibold flex-shrink-0">
                                        {{ collect(explode(' ', $user['name']))->map(fn ($w) => $w[0])->take(2)->join('') }}
                                    </span>
                                    <div class="lh-sm">
                                        <div class="fw-semibold">{{ $user['name'] }}</div>
                                        <small class="text-muted">{{ $user['redmine_login'] }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($user['telegram'])
                                    <i class="fa-brands fa-telegram text-primary me-1"></i>{{ '@' . $user['telegram'] }}
                                @else
                                    <span class="text-muted fst-italic">Belum terhubung</span>
                                @endif
                            </td>
                            <td><code>{{ $user['chat_id'] }}</code></td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $color }}-subtle text-{{ $color }}-emphasis border border-{{ $color }}-subtle">
                                    <i class="fa-solid {{ $icon }} me-1"></i>{{ $label }}
                                </span>
                            </td>
                            <td class="text-end text-nowrap">
                                <button class="btn btn-sm btn-outline-primary" title="Edit"
                                        data-bs-toggle="modal" data-bs-target="#userModal">
                                    <i class="fa-solid fa-pen-to-square"></i><span class="d-none d-xl-inline ms-1">Edit</span>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" title="Disconnect"
                                        @disabled($user['status'] === 'disconnected')>
                                    <i class="fa-solid fa-link-slash"></i><span class="d-none d-xl-inline ms-1">Disconnect</span>
                                </button>
                                <button class="btn btn-sm btn-outline-success btn-test-send" title="Test Send"
                                        data-name="{{ $user['name'] }}"
                                        @disabled($user['status'] === 'disconnected')>
                                    <i class="fa-solid fa-paper-plane"></i><span class="d-none d-xl-inline ms-1">Test Send</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="fa-solid fa-user-slash fa-2x mb-2 d-block"></i>
                                Belum ada user yang terhubung.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white d-flex justify-content-between align-items-center small text-muted">
            <span>Menampilkan {{ count($users) }} user</span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
                </ul>
            </nav>
        </div>
    </div>

    {{-- ================= MODAL TAMBAH / LINK USER ================= --}}
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="#" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="userModalLabel">
                        <i class="fa-solid fa-link text-primary me-2"></i>Tambah / Link User Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="redmine_user" class="form-label fw-medium">User Redmine</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                            <input type="text" class="form-control" id="redmine_user" name="redmine_user"
                                   placeholder="contoh: budi.s" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="api_key" class="form-label fw-medium">API Key Redmine</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                            <input type="password" class="form-control" id="api_key" name="api_key"
                                   placeholder="Masukkan API Key" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleApiKey" title="Tampilkan">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Dapat dilihat di Redmine &rarr; My Account &rarr; API access key.</div>
                    </div>
                    <div class="mb-1">
                        <label for="chat_id" class="form-label fw-medium">Chat ID Telegram</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-brands fa-telegram"></i></span>
                            <input type="text" class="form-control" id="chat_id" name="chat_id"
                                   inputmode="numeric" placeholder="contoh: 512334871" required>
                        </div>
                        <div class="form-text">User dapat mengirim <code>/start</code> ke bot untuk mendapatkan Chat ID.</div>
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

    {{-- Toast notifikasi Test Send --}}
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="testToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"><i class="fa-solid fa-paper-plane me-2"></i><span id="testToastText"></span></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Tutup"></button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Tampilkan / sembunyikan API Key
    document.getElementById('toggleApiKey').addEventListener('click', function () {
        const input = document.getElementById('api_key');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        this.innerHTML = show ? '<i class="fa-regular fa-eye-slash"></i>' : '<i class="fa-regular fa-eye"></i>';
    });

    // Simulasi Test Send (UI saja)
    const toast = bootstrap.Toast.getOrCreateInstance(document.getElementById('testToast'));
    document.querySelectorAll('.btn-test-send').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('testToastText').textContent = `Pesan test dikirim ke ${btn.dataset.name}`;
            toast.show();
        });
    });
</script>
@endpush
