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
use Modules\Testimonials\Domain\Contracts\TestimonialRepositoryInterface;
use Modules\Testimonials\Domain\Models\Testimonial;
use Modules\Testimonials\Domain\Repositories\TestimonialRepository;

/**
 * Testimonials Module Service Provider.
 *
 * Registers and bootstraps the Testimonials module including:
 * - Repository bindings (Interface to Implementation)
 * - Testimonial management actions
 * - Query and Command services
 *
 * @package Modules\Testimonials\Providers
 * @author  CMS Development Team
 * @since   1.0.0
 */
class TestimonialsServiceProvider extends ServiceProvider
{
    /**
     * Module name for path resolution.
     *
     * @var string
     */
    protected string $moduleName = 'Testimonials';

    /**
     * Register module services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/config.php'), 'testimonials');

        $this->registerRepositories();
        $this->registerActions();
        $this->registerServices();
    }

    /**
     * Bootstrap module services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'Routes/api.php'));
    }

    /**
     * Register repository bindings.
     *
     * @return void
     */
    protected function registerRepositories(): void
    {
        // Bind interface to implementation
        $this->app->bind(
            TestimonialRepositoryInterface::class,
            TestimonialRepository::class
        );

        // Register concrete repository as singleton
        $this->app->singleton(TestimonialRepository::class, fn ($app) => 
            new TestimonialRepository(new Testimonial())
        );
    }

    /**
     * Register action classes.
     *
     * @return void
     */
    protected function registerActions(): void
    {
        $this->app->singleton(CreateTestimonialAction::class);
        $this->app->singleton(UpdateTestimonialAction::class);
        $this->app->singleton(DeleteTestimonialAction::class);
        $this->app->singleton(ApproveTestimonialAction::class);
    }

    /**
     * Register service classes.
     *
     * @return void
     */
    protected function registerServices(): void
    {
        $this->app->singleton(TestimonialQueryService::class);
        $this->app->singleton(TestimonialCommandService::class);
    }
}
