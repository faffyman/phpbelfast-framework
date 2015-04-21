<?php
$app->get('/', '\PhpBelfast\Controllers\BaseController:home')
    ->name('home');

$app->get('/hello/:name', '\PhpBelfast\Controllers\BaseController:hello');

$app->group('/posts', function() use ($app){

    $app->get('/','\PhpBelfast\Controllers\PostController:index')
        ->name('posts.index');

    $app->get('/:id','\PhpBelfast\Controllers\PostController:item')
        ->conditions(['id' => '[0-9]+'])
        ->name('posts.item');

});

$app->group('/events', function() use ($app){

    $app->get('/','\PhpBelfast\Controllers\EventController:index')
        ->name('events.index');

    $app->get('/:id','\PhpBelfast\Controllers\EventController:item')
        ->conditions(['id' => '[0-9]+'])
        ->name('events.item');

});


$app->group('/neo4j', function() use ($app){

    $app->get('/','\PhpBelfast\Controllers\GraphDBController:index')
        ->name('neo.index');

    $app->get('/places','\PhpBelfast\Controllers\GraphDBController:places')
        ->name('neo.places');
    $app->get('/places/load','\PhpBelfast\Controllers\GraphDBController:loadplaces')
        ->name('neo.loadplaces');

    $app->get('/people','\PhpBelfast\Controllers\GraphDBController:people')
        ->name('neo.people');

    $app->get('/people/:num','\PhpBelfast\Controllers\GraphDBController:createpeople')
        ->conditions(['num' => '[0-9]+'])
        ->name('neo.createpeople');

    $app->get('/peopletoplaces','\PhpBelfast\Controllers\GraphDBController:peopletoplaces')
        ->name('neo.peopleplaces');

    $app->get('/relatepeople','\PhpBelfast\Controllers\GraphDBController:relatepeople')
        ->name('neo.relatepeople');


    $app->get('/people/:name','\PhpBelfast\Controllers\GraphDBController:person')
        ->name('neo.person');

});




$app->group('/url', function() use ($app){

    $app->get('/', '\PhpBelfast\Controllers\UrlController:index')
        ->name('url.index');

    $app->post('/', '\PhpBelfast\Controllers\UrlController:store');

    $app->get('/show/', '\PhpBelfast\Controllers\UrlController:show')
        ->name('url.show');
});

$app->get('/:short', '\PhpBelfast\Controllers\UrlController:check')
    ->conditions(array('short' => '[0-9a-z]+'));