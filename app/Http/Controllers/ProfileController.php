<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'alpha_dash:ascii', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($request, $user, $validated): void {
            $before = ['name' => $user->name, 'username' => $user->username, 'email' => $user->email];
            $attributes = ['name' => $validated['name'], 'username' => $validated['username'], 'email' => $validated['email']];
            if (filled($validated['password'] ?? null)) {
                $attributes['password'] = $validated['password'];
            }
            $user->update($attributes);

            AuditLog::create([
                'user_id' => $user->id,
                'module' => 'profile',
                'action' => 'updated',
                'record_id' => $user->id,
                'description' => 'Updated own profile.',
                'changes' => [
                    'before' => $before,
                    'after' => ['name' => $user->name, 'username' => $user->username, 'email' => $user->email],
                ],
            ]);
        });

        return redirect()->route('profile.edit')->with('status', 'Profile updated.');
    }
}
