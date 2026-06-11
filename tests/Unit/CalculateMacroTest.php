<?php

use Laravel\Nova\Fields\Currency;

it('registers the calculate macro on Nova fields', function () {
    expect(Currency::hasMacro('calculate'))->toBeTrue();
});

it('stores calculation settings in the field meta', function () {
    $field = Currency::make('Amount', 'amount')->calculate(
        method: 'sum',
        title: 'Total',
        prefix: '$',
        postfix: ',-',
        align: 'left',
        titleAlign: 'center',
        hideTitle: true,
        decimals: 2,
    );

    expect($field->meta())->toMatchArray([
        'calculate_method' => 'sum',
        'title' => 'Total',
        'prefix' => '$',
        'postfix' => ',-',
        'totalsAlignment' => 'left',
        'totalsTitleAlignment' => 'center',
        'totalsHideTitle' => true,
        'decimals' => 2,
    ]);
});

it('defaults alignment, title visibility and decimals', function () {
    $field = Currency::make('Amount', 'amount')->calculate(
        method: 'sum',
        title: 'Total',
    );

    expect($field->meta())->toMatchArray([
        'totalsAlignment' => 'right',
        'totalsTitleAlignment' => 'right',
        'totalsHideTitle' => false,
        'decimals' => null,
    ]);
});
