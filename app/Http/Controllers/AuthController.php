<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\GoogleWorkspaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AuthController extends Controller
{
    public function redirect()
    {
        if (blank(config('services.google.client_id')) || blank(config('services.google.client_secret'))) {
            return redirect()
                ->route('home')
                ->with('error', 'Google OAuth belum dikonfigurasi. Isi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET di file .env, lalu jalankan php artisan config:clear.');
        }

        return Socialite::driver('google')
            ->scopes(config('services.google.scopes', []))
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent',
            ])
            ->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::query()
                ->where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first() ?? new User(['role' => 'teacher']);

            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Google User',
                'email' => $googleUser->getEmail(),
                'role' => $user->role ?: 'teacher',
            ])->save();

            $existingToken = $user->oauthToken;
            $refreshToken = $googleUser->refreshToken
                ? Crypt::encryptString($googleUser->refreshToken)
                : $existingToken?->refresh_token_encrypted;

            $user->oauthToken()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'access_token_encrypted' => Crypt::encryptString($googleUser->token),
                    'refresh_token_encrypted' => $refreshToken,
                    'expiry_date' => $googleUser->expiresIn ? now()->addSeconds($googleUser->expiresIn) : null,
                    'scopes' => config('services.google.scopes', []),
                    'token_type' => 'Bearer',
                    'revoked' => false,
                ],
            );

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'oauth.login',
                'resource' => 'google',
                'status' => 'SUCCESS',
                'message' => 'Google OAuth login berhasil dan token disimpan terenkripsi.',
            ]);

            Auth::login($user, true);
            request()->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        } catch (Throwable $exception) {
            $auditLog = AuditLog::create([
                'action' => 'oauth.login',
                'resource' => 'google',
                'status' => 'FAILED',
                'message' => $exception->getMessage(),
            ]);

            $message = 'Login Google gagal. Periksa konfigurasi OAuth dan coba lagi.';

            if (config('app.debug')) {
                $message .= ' Detail #'.$auditLog->id.': '.Str::limit($exception->getMessage(), 220);
            }

            return redirect()
                ->route('home')
                ->with('error', $message);
        }
    }

    public function logout(Request $request)
    {
        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'oauth.logout',
            'resource' => 'session',
            'status' => 'SUCCESS',
            'message' => 'User keluar dari dashboard.',
        ]);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function disconnect(Request $request, GoogleWorkspaceService $google)
    {
        $user = $request->user();

        $google->revoke($user);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'oauth.disconnect',
            'resource' => 'google',
            'status' => 'SUCCESS',
            'message' => 'Koneksi Google diputus dan token lokal dihapus.',
        ]);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Akun Google berhasil diputus. Login ulang diperlukan untuk sinkronisasi Classroom.');
    }
}
