<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB;

use Codewithkyrian\ChromaDB\Generated\ChromaApiClient;

class Factory
{
    /**
     * The base url for the ChromaDB server.
     */
    protected string $baseUrl;

    /**
     * The host where the ChromaDB server is running.
     */
    protected string $host = 'http://localhost';

    /**
     * The port to send requests to.
     */
    protected int $port = 8000;

    /**
     * The database to use for the instance.
     */
    protected string $database = 'default_database';

    /**
     * The tenant to use for the instance.
     */
    protected string $tenant = 'default_tenant';

    /**
     * The http client to use for the requests.
     */
    protected \GuzzleHttp\Client $httpClient;


    /**
     * The ChromaDB api provider for the instance.
     */
    protected ChromaApiClient $apiClient;

    /**
     * The url of the client to use for the requests.
     */
    public function withHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    /**
     * The port of the client to use for the requests.
     */
    public function withPort(int $port): self
    {
        $this->port = $port;
        return $this;
    }

    /**
     * The database to use for the instance.
     */
    public function withDatabase(string $database): self
    {
        $this->database = $database;
        return $this;
    }

    /**
     * The tenant to use for the instance.
     */
    public function withTenant(string $tenant): self
    {
        $this->tenant = $tenant;
        return $this;
    }

    /**
     * The http client to use for the requests.
     */
    public function withHttpClient(\GuzzleHttp\Client $httpClient): self
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    public function connect(): Client
    {
        $this->apiClient = $this->createApiClient();

        return new Client($this->apiClient, $this->database, $this->tenant);
    }

    public function createApiClient() : ChromaApiClient
    {
        $this->baseUrl = $this->host . ':' . $this->port;

        $this->httpClient ??=  new \GuzzleHttp\Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
        ]);

        return new ChromaApiClient($this->httpClient);
    }
}