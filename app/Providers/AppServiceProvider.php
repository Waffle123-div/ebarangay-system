<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Generate a cache-busted asset URL so CDN/browser caches always fetch the latest file after deploys.
     */
    public static function versionedAsset(string $path): string
    {
        $cleanPath = ltrim($path, '/');
        $fullPath = public_path($cleanPath);
        $version = is_file($fullPath) ? (string) (@filemtime($fullPath) ?: '1') : '1';

        return asset($cleanPath) . '?v=' . $version;
    }

    /**
     * Define a foreign key column matching users.id on both fresh installs and legacy SQL dumps.
     */
    public static function userForeignKeyColumn(Blueprint $table, string $column, bool $nullable = false): ColumnDefinition
    {
        $definition = null;

        if (DB::getDriverName() === 'mysql' && Schema::hasTable('users')) {
            $columnInfo = DB::selectOne("SHOW COLUMNS FROM `users` WHERE Field = 'id'");
            $type = strtolower((string) ($columnInfo->Type ?? ''));

            if ($type !== '') {
                if (str_contains($type, 'bigint')) {
                    $definition = str_contains($type, 'unsigned')
                        ? $table->unsignedBigInteger($column)
                        : $table->bigInteger($column);
                } elseif (str_contains($type, 'int')) {
                    $definition = str_contains($type, 'unsigned')
                        ? $table->unsignedInteger($column)
                        : $table->integer($column);
                }
            }
        }

        $definition ??= $table->unsignedBigInteger($column);

        return $nullable ? $definition->nullable() : $definition;
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS URL generation behind SSL reverse proxies (Laravel Cloud, Render, Cloudflare, etc.)
        $isHttpsProxy = request()->isSecure()
            || request()->header('x-forwarded-proto') === 'https'
            || str_contains((string) request()->header('cf-visitor'), '"https"')
            || str_starts_with((string) config('app.url'), 'https://')
            || (bool) config('app.force_https', false);

        if (app()->environment('production') || $isHttpsProxy) {
            URL::forceScheme('https');
        }

        // Register custom Resend HTTPS mail transport (works on Render free tier where SMTP ports are blocked)
        \Illuminate\Support\Facades\Mail::extend('resend_http', function (array $config = []) {
            $key = $config['key'] ?? config('services.resend.key') ?? env('RESEND_API_KEY') ?? env('RESEND_KEY');
            return new \App\Mail\Transports\ResendHttpTransport($key);
        });

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
           return (new MailMessage)
               ->subject('Action Required: Verify Your E-Barangay Account')
               ->greeting('Welcome to Barangay Cogon East!')
               ->line('You are receiving this email because an account registration was requested for the E-Barangay Document Request and Tracking System using this email address.')
               ->line('To securely verify your identity and unlock your Resident Dashboard, please click the button below:')
               ->action('Verify Email Address', $url)
               ->line('If you did not request this registration, no further action is required and your email remains secure.')
               ->salutation('Respectfully, The E-Barangay Administration');
       });
    }
}
