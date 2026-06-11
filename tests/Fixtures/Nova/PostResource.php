<?php

namespace Marshmallow\NovaTotalsFooter\Tests\Fixtures\Nova;

use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Marshmallow\NovaTotalsFooter\Tests\Fixtures\Models\Post;

class PostResource extends Resource
{
    public static $model = Post::class;

    public static $title = 'title';

    public static function uriKey(): string
    {
        return 'posts';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Title', 'title'),

            Currency::make('Amount', 'amount')
                ->calculate(
                    method: 'sum',
                    title: 'Total',
                    prefix: '$',
                    decimals: 2,
                ),

            Number::make('Views', 'views')
                ->calculate(
                    method: 'avg',
                    title: 'Average',
                ),
        ];
    }
}
