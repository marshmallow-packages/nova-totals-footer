<?php

use Illuminate\Testing\TestResponse;
use Marshmallow\NovaTotalsFooter\Tests\Fixtures\Models\Author;
use Marshmallow\NovaTotalsFooter\Tests\Fixtures\Models\Post;

beforeEach(function () {
    $this->actingAsNovaUser();

    $this->authorOne = Author::create(['name' => 'Author One']);
    $this->authorTwo = Author::create(['name' => 'Author Two']);

    // Author One: amount 100.00 + 200.50 = 300.50, views 10 + 20 (avg 15)
    Post::create(['author_id' => $this->authorOne->id, 'title' => 'A1-1', 'amount' => 100.00, 'views' => 10]);
    Post::create(['author_id' => $this->authorOne->id, 'title' => 'A1-2', 'amount' => 200.50, 'views' => 20]);

    // Author Two: amount 1000.00, views 100
    Post::create(['author_id' => $this->authorTwo->id, 'title' => 'A2-1', 'amount' => 1000.00, 'views' => 100]);

    // Whole table: amount 1300.50, views avg (10+20+100)/3 = 43.33
});

function calculate(array $params): TestResponse
{
    return test()->getJson(
        '/nova-vendor/nova-totals-footer/calculate/posts?'.http_build_query($params)
    );
}

it('sums a column across the whole table when not scoped to a relation', function () {
    $response = calculate([
        'calculate' => [
            ['indexName' => 'amount', 'method' => 'sum', 'decimals' => 2],
        ],
    ]);

    $response->assertOk()
        ->assertJsonPath('totals.amount', '1,300.50');
});

it('sums only the related records when scoped through a relation', function () {
    $response = calculate([
        'calculate' => [
            ['indexName' => 'amount', 'method' => 'sum', 'decimals' => 2],
        ],
        'viaResource' => 'authors',
        'viaResourceId' => $this->authorOne->id,
        'viaRelationship' => 'posts',
    ]);

    // Author One only: 100.00 + 200.50 = 300.50, NOT the whole-table 1,300.50.
    $response->assertOk()
        ->assertJsonPath('totals.amount', '300.50');
});

it('formats with the requested number of decimals (default zero)', function () {
    $response = calculate([
        'calculate' => [
            ['indexName' => 'amount', 'method' => 'sum'], // no decimals
        ],
    ]);

    $response->assertOk()
        ->assertJsonPath('totals.amount', '1,301'); // 1300.50 rounded, 0 decimals
});

it('supports the avg method scoped to a relation', function () {
    $response = calculate([
        'calculate' => [
            ['indexName' => 'views', 'method' => 'avg'],
        ],
        'viaResource' => 'authors',
        'viaResourceId' => $this->authorOne->id,
        'viaRelationship' => 'posts',
    ]);

    // Author One views avg: (10 + 20) / 2 = 15
    $response->assertOk()
        ->assertJsonPath('totals.views', '15');
});

it('returns the hideHeader setting', function () {
    $response = calculate([
        'calculate' => [
            ['indexName' => 'amount', 'method' => 'sum'],
        ],
    ]);

    $response->assertOk()
        ->assertJsonPath('settings.hideHeader', false);
});
