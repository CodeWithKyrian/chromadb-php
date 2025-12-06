<?php

use Codewithkyrian\ChromaDB\Tests\Fixtures\ChromaServer;

uses()
    ->beforeAll(function () {
        ChromaServer::start();
    })
    ->afterAll(function () {
        ChromaServer::stop();
    })
    ->in('Feature');
