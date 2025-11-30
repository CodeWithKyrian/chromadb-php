<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB\Embeddings;

use Codewithkyrian\ChromaDB\Embeddings\EmbeddingFunction;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class OllamaEmbeddingFunction implements EmbeddingFunction
{
    private ClientInterface $httpClient;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    public function __construct(
        private readonly string $baseUrl = 'http://localhost:11434',
        private readonly string $model = 'all-minilm',
    ) {
        $this->httpClient = Psr18ClientDiscovery::find();
        $this->requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = Psr17FactoryDiscovery::findStreamFactory();
    }

    public function generate(array $texts): array
    {
        $embeddings = [];

        foreach ($texts as $text) {
            $request = $this->requestFactory->createRequest('POST', $this->baseUrl . '/api/embeddings')
                ->withHeader('Content-Type', 'application/json');

            $body = $this->streamFactory->createStream(json_encode([
                'prompt' => $text,
                'model' => $this->model,
            ]));

            $request = $request->withBody($body);

            $response = $this->httpClient->sendRequest($request);
            $result = json_decode($response->getBody()->getContents(), true);

            $embeddings[] = $result['embedding'];
        }

        return $embeddings;
    }
}