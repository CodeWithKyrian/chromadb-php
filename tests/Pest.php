<?php

use Codewithkyrian\ChromaDB\ChromaDB;
use Codewithkyrian\ChromaDB\Tests\ChromaServer;

uses()
    ->beforeAll(function () {
        ChromaServer::start();
        // ChromaDB::reset();
    })
    ->afterAll(function () {
        // ChromaDB::reset();
        ChromaServer::stop();
    })
    ->in(__DIR__);
