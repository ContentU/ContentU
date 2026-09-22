<?php

it('carica la home senza errori', function () {
    $page = visit('/');

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});
