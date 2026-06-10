<?php

namespace Marshmallow\NovaTotalsFooter\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Inertia\ServiceProvider as InertiaServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\NovaCoreServiceProvider;
use Marshmallow\NovaTotalsFooter\Tests\Fixtures\Models\User;
use Marshmallow\NovaTotalsFooter\Tests\Fixtures\NovaServiceProvider;
use Marshmallow\NovaTotalsFooter\ToolServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->bootNova();
    }

    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            InertiaServiceProvider::class,
            NovaCoreServiceProvider::class,
            ToolServiceProvider::class,
            NovaServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->nullable();
            $table->string('title');
            $table->decimal('amount', 10, 2)->default(0);
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Fire Nova's serving event so resources and the calculate() macro register.
     */
    protected function bootNova(): void
    {
        ServingNova::dispatch($this->app, $this->app['request']);
    }

    /**
     * Authenticate as a Nova user so the nova.auth middleware passes.
     */
    protected function actingAsNovaUser(): static
    {
        return $this->actingAs(new User(['id' => 1, 'email' => 'test@example.com']));
    }
}
