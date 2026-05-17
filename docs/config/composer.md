# Config: composer.json

`composer.json` memakai Laravel 11 dan Laravel Socialite.

## Dependency utama
- `laravel/framework`
- `laravel/socialite`
- `laravel/tinker`

## Autoload
Namespace aplikasi tetap `App\\`.

`vendor/laravel/pint/app/` dikecualikan dari classmap untuk mencegah peringatan ambiguous class saat Pint terpasang dari source cache.
