<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Repositories\AuditLogRepository;
use App\Repositories\OauthTokenRepository;
use App\Repositories\UserRepository;
use App\Security\Auth;
use App\Security\Crypto;
use App\Services\GoogleWorkspaceService;
use App\Support\HttpClient;
use Throwable;

final class AuthController
{
    private AuditLogRepository $auditLogs;

    public function __construct()
    {
        $this->auditLogs = new AuditLogRepository();
    }

    public function welcome(Request $request): void
    {
        if (Auth::check()) {
            redirect(route('dashboard'));
        }

        View::render('auth/welcome', [
            'title' => 'AutoGrade AI',
        ], 'layouts/guest');
    }

    public function redirect(Request $request): never
    {
        $clientId = (string) config('services.google.client_id');
        $clientSecret = (string) config('services.google.client_secret');

        if ($clientId === '' || $clientSecret === '') {
            flash('error', 'Google OAuth belum dikonfigurasi. Isi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET di file .env.');
            redirect(route('home'));
        }

        $state = bin2hex(random_bytes(32));
        $_SESSION['oauth_state'] = $state;

        $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => config('services.google.redirect'),
            'response_type' => 'code',
            'scope' => implode(' ', config('services.google.scopes', [])),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'include_granted_scopes' => 'true',
            'state' => $state,
        ]);

        redirect($url);
    }

    public function callback(Request $request): never
    {
        $state = (string) $request->query('state', '');
        $expectedState = (string) ($_SESSION['oauth_state'] ?? '');
        unset($_SESSION['oauth_state']);

        if ($state === '' || $expectedState === '' || ! hash_equals($expectedState, $state)) {
            flash('error', 'State OAuth tidak valid. Silakan coba login ulang.');
            redirect(route('home'));
        }

        $code = (string) $request->query('code', '');

        if ($code === '') {
            flash('error', 'Google tidak mengembalikan kode otorisasi.');
            redirect(route('home'));
        }

        try {
            $http = new HttpClient();
            $tokenResponse = $http->postForm('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => config('services.google.redirect'),
                'grant_type' => 'authorization_code',
            ]);

            if (! $tokenResponse['successful'] || ! is_array($tokenResponse['json'])) {
                throw new \RuntimeException('Token exchange gagal: ' . str_limit((string) $tokenResponse['body'], 180));
            }

            $tokenPayload = $tokenResponse['json'];
            $accessToken = (string) ($tokenPayload['access_token'] ?? '');

            if ($accessToken === '') {
                throw new \RuntimeException('Access token tidak ditemukan pada response Google.');
            }

            $profileResponse = $http->get('https://openidconnect.googleapis.com/v1/userinfo', headers: [
                'Authorization: Bearer ' . $accessToken,
            ]);

            if (! $profileResponse['successful'] || ! is_array($profileResponse['json'])) {
                throw new \RuntimeException('User profile Google gagal dimuat.');
            }

            $profile = $profileResponse['json'];
            $googleId = (string) ($profile['sub'] ?? '');
            $email = (string) ($profile['email'] ?? '');
            $name = (string) ($profile['name'] ?? $profile['given_name'] ?? 'Google User');

            if ($googleId === '' || $email === '') {
                throw new \RuntimeException('Profil Google tidak lengkap.');
            }

            $users = new UserRepository();
            $tokens = new OauthTokenRepository();
            $user = $users->saveGoogleUser($googleId, $name, $email);
            $scopes = isset($tokenPayload['scope']) ? explode(' ', (string) $tokenPayload['scope']) : config('services.google.scopes', []);
            $expiryDate = isset($tokenPayload['expires_in'])
                ? date('Y-m-d H:i:s', time() + (int) $tokenPayload['expires_in'])
                : null;

            $tokens->upsert(
                (int) $user['id'],
                Crypto::encrypt($accessToken),
                isset($tokenPayload['refresh_token']) ? Crypto::encrypt((string) $tokenPayload['refresh_token']) : null,
                $expiryDate,
                $scopes,
                (string) ($tokenPayload['token_type'] ?? 'Bearer')
            );

            $this->auditLogs->create((int) $user['id'], 'oauth.login', 'google', 'SUCCESS', 'Google OAuth login berhasil dan token disimpan terenkripsi.');

            Auth::login($user);
            flash('status', 'Login Google berhasil.');
            redirect(route('dashboard'));
        } catch (Throwable $exception) {
            $this->auditLogs->create(null, 'oauth.login', 'google', 'FAILED', $exception->getMessage());
            $message = 'Login Google gagal. Periksa konfigurasi OAuth dan coba lagi.';

            if (config('app.debug')) {
                $message .= ' Detail: ' . str_limit($exception->getMessage(), 220);
            }

            flash('error', $message);
            redirect(route('home'));
        }
    }

    public function logout(Request $request): never
    {
        $user = $request->user();

        if ($user) {
            $this->auditLogs->create((int) $user['id'], 'oauth.logout', 'session', 'SUCCESS', 'User keluar dari dashboard.');
        }

        Auth::logout();
        flash('status', 'Anda sudah keluar.');
        redirect(route('home'));
    }

    public function disconnect(Request $request): never
    {
        $user = $request->user();

        if (! $user) {
            redirect(route('home'));
        }

        (new GoogleWorkspaceService())->revoke($user);
        $this->auditLogs->create((int) $user['id'], 'oauth.disconnect', 'google', 'SUCCESS', 'Koneksi Google diputus dan token lokal dihapus.');

        flash('status', 'Akun Google berhasil diputus. Login ulang diperlukan untuk sinkronisasi Classroom.');
        redirect(route('dashboard'));
    }
}
