<?php

declare(strict_types=1);

namespace Modules\Testimonials\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Testimonials\Application\Actions\ApproveTestimonialAction;
use Modules\Testimonials\Application\Actions\CreateTestimonialAction;
use Modules\Testimonials\Application\Actions\DeleteTestimonialAction;
use Modules\Testimonials\Application\Actions\UpdateTestimonialAction;
use Modules\Testimonials\Application\Services\TestimonialCommandService;
use Modules\Testimonials\Application\Services\TestimonialQueryService;
use Modules\Testimonials\Domain\Models\Testimonial;
use Modules\Testimonials\Domain\Repositories\TestimonialRepository;

class TestimonialsServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Testimonials';

    public function register(): void
    {
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/config.php'), 'testimonials');

        $this->registerRepositories();
        $this->registerActions();
        $this->registerServices();
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
    }

    protected function registerRepositories(): void
    {
        $this->app->singleton(TestimonialRepository::class, fn ($app) => new TestimonialRepository(new Testimonial()));
    }

    protected function registerActions(): void
    {
        $this->app->singleton(CreateTestimonialAction::class);
        $this->app->singleton(UpdateTestimonialAction::class);
        $this->app->singleton(DeleteTestimonialAction::class);
        $this->app->singleton(ApproveTestimonialAction::class);
    }

    protected function registerServices(): void
    {
        $this->app->singleton(TestimonialQueryService::class);
        $this->app->singleton(TestimonialCommandService::class);
    }
}
