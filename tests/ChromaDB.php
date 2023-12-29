<?php

declare(strict_types=1);

use Codewithkyrian\ChromaDB\Client;
use Codewithkyrian\ChromaDB\ChromaDB;

it('can create a client instance', function () {
    $client = ChromaDB::client();

    expect($client)->toBeInstanceOf(Client::class);
});

it('can create a client instance via factory', function () {
    $client = ChromaDB::factory()
        ->withHost('http://localhost')
        ->withPort(8000)
        ->connect();

    expect($client)->toBeInstanceOf(Client::class);
});
