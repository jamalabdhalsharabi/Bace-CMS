<?php

declare(strict_types=1);

namespace Modules\Testimonials\Providers;

use Illuminate\Support\ServiceProvider;

class TestimonialsServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Testimonials';

    public function register(): void
    {
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/config.php'), 'testimonials');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
    }
}
