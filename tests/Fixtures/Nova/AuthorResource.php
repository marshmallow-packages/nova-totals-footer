<?php

namespace Marshmallow\NovaTotalsFooter\Tests\Fixtures\Nova;

use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Marshmallow\NovaTotalsFooter\Tests\Fixtures\Models\Author;

class AuthorResource extends Resource
{
    public static $model = Author::class;

    public static $title = 'name';

    public static function uriKey(): string
    {
        return 'authors';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Name', 'name'),

            HasMany::make('Posts', 'posts', PostResource::class),
        ];
    }
}
