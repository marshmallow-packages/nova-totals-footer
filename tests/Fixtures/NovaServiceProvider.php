<?php

namespace Marshmallow\NovaTotalsFooter\Tests\Fixtures;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;
use Marshmallow\NovaTotalsFooter\Tests\Fixtures\Nova\AuthorResource;
use Marshmallow\NovaTotalsFooter\Tests\Fixtures\Nova\PostResource;

class NovaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Nova::auth(static fn ($request) => true);

        Nova::serving(static function () {
            Nova::resources([
                AuthorResource::class,
                PostResource::class,
            ]);
        });
    }
}
