<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB;

use Codewithkyrian\ChromaDB\Api;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;

class Factory
{
    /**
     * The host where the ChromaDB server is running.
     */
    protected string $host = 'http://localhost';

    /**
     * The port to send requests to.
     */
    protected ?int $port = 8000;

    /**
     * The database to use for the instance.
     */
    protected string $database = 'default_database';

    /**
     * The tenant to use for the instance.
     */
    protected string $tenant = 'default_tenant';

    /**
     * The headers to be sent with the requests.
     *
     * @var array<string, string>
     */
    protected array $headers = [];

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
    public function withPort(?int $port): self
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
     * The bearer token used to authenticate requests.
     * 
     * @deprecated Use withHeader('X-Chroma-Token', $authToken) instead.
     */
    public function withAuthToken(string $authToken): self
    {
        return $this->withHeader('X-Chroma-Token', $authToken);
    }

    /**
     * Add a header to the requests.
     */
    public function withHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Add multiple headers to the requests.
     * 
     * @param array<string, string> $headers
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function connect(): Client
    {
        $baseUrl = $this->port ? "$this->host:$this->port" : $this->host;

        $httpClient = Psr18ClientDiscovery::find();
        $requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $streamFactory = Psr17FactoryDiscovery::findStreamFactory();

        $api = new Api(
            $httpClient,
            $requestFactory,
            $streamFactory,
            $baseUrl,
            $this->headers
        );

        return new Client($api, $this->database, $this->tenant);
    }
}
