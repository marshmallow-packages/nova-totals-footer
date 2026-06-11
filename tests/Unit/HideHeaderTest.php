<?php

use Marshmallow\NovaTotalsFooter\NovaTotalsFooter;

afterEach(function () {
    NovaTotalsFooter::$hideHeader = false;
});

it('defaults to showing the header', function () {
    expect(NovaTotalsFooter::$hideHeader)->toBeFalse();
});

it('can hide the header', function () {
    NovaTotalsFooter::hideHeader();

    expect(NovaTotalsFooter::$hideHeader)->toBeTrue();
});
