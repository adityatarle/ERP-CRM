<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Helpers\SecurityHelper;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Secure output directive
        Blade::directive('secure', function ($expression) {
            return "<?php echo App\Helpers\SecurityHelper::escape($expression); ?>";
        });

        // Secure attribute directive
        Blade::directive('secureAttr', function ($expression) {
            return "<?php echo App\Helpers\SecurityHelper::escapeAttribute($expression); ?>";
        });

        // Secure JavaScript directive
        Blade::directive('secureJs', function ($expression) {
            return "<?php echo App\Helpers\SecurityHelper::escapeJs($expression); ?>";
        });

        // Sanitize HTML directive
        Blade::directive('sanitizeHtml', function ($expression) {
            return "<?php echo App\Helpers\SecurityHelper::sanitizeHtml($expression); ?>";
        });
    }
}
