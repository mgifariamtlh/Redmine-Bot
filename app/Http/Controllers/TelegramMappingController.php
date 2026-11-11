<?php

namespace App\Http\Controllers;

use App\Models\TelegramMapping;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TelegramMappingController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $mappings = TelegramMapping::query()
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('redmine_user_id', 'like', "%{$search}%")
                    ->orWhere('telegram_username', 'like', "%{$search}%")
                    ->orWhere('chat_id', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('user-mappings.index', compact('mappings', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        TelegramMapping::create($this->validated($request));

        return redirect()->route('user-mappings.index')->with('success', 'Mapping berhasil ditambahkan.');
    }

    public function update(Request $request, TelegramMapping $userMapping): RedirectResponse
    {
        $userMapping->update($this->validated($request, $userMapping));

        return redirect()->route('user-mappings.index')->with('success', 'Mapping berhasil diperbarui.');
    }

    public function destroy(TelegramMapping $userMapping): RedirectResponse
    {
        $userMapping->delete();

        return redirect()->route('user-mappings.index')->with('success', 'Mapping berhasil dihapus.');
    }

    private function validated(Request $request, ?TelegramMapping $mapping = null): array
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'redmine_user_id'   => ['required', 'string', 'max:50', Rule::unique('telegram_mappings')->ignore($mapping)],
            'telegram_username' => ['nullable', 'string', 'max:64', 'regex:/^@?[A-Za-z0-9_]{3,}$/'],
            'chat_id'           => ['required', 'regex:/^-?\d+$/', 'max:30', Rule::unique('telegram_mappings')->ignore($mapping)],
        ], [
            'telegram_username.regex' => 'Username Telegram hanya boleh huruf, angka, dan underscore (min. 3 karakter).',
            'chat_id.regex'           => 'Chat ID harus berupa angka.',
            'redmine_user_id.unique'  => 'Redmine User ID sudah terdaftar.',
            'chat_id.unique'          => 'Chat ID sudah terdaftar.',
        ]);

        // Simpan username tanpa awalan "@"; tampilan menambahkannya.
        $data['telegram_username'] = filled($data['telegram_username'] ?? null)
            ? ltrim($data['telegram_username'], '@')
            : null;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
