<?php

namespace Marshmallow\NovaTotalsFooter;

use Laravel\Nova\Nova;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Marshmallow\NovaTotalsFooter\Http\Middleware\Authorize;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            Nova::mix('nova-totals-footer', __DIR__ . '/../dist/mix-manifest.json');
            Field::macro('calculate', function (string $method, string $title, string $postfix = '', string $prefix = '', string $align = 'right', string $titleAlign = 'right', bool $hideTitle = false) {

                /** @var Field $field */
                $field = $this;

                return $field->withMeta(['calculate_method' => $method])
                    ->withMeta(['title' => $title])
                    ->withMeta([
                        'postfix' => $postfix,
                        'prefix' => $prefix,
                        'totalsAlignment' => $align,
                        'totalsTitleAlignment' => $titleAlign,
                        'totalsHideTitle' => $hideTitle,
                    ]);
            });
        });
    }

    /**
     * Register the tool's routes.
     */
    protected function routes(): void
    {
        /** @var Application $app */
        $app = $this->app;
        if ($app->routesAreCached()) {
            return;
        }

        Nova::router(['nova', 'nova.auth', Authorize::class], 'nova-totals-footer')
            ->group(__DIR__ . '/../routes/inertia.php');

        Route::middleware(['nova', 'nova.auth', Authorize::class])
            ->prefix('nova-vendor/nova-totals-footer')
            ->group(__DIR__ . '/../routes/api.php');
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
