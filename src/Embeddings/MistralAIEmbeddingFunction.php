<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Embeddings;

use Codewithkyrian\ChromaDB\Embeddings\EmbeddingFunction;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class MistralAIEmbeddingFunction implements EmbeddingFunction
{
    private ClientInterface $httpClient;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'mistral-embed'
    ) {
        $this->httpClient = Psr18ClientDiscovery::find();
        $this->requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = Psr17FactoryDiscovery::findStreamFactory();
    }

    /**
     * @inheritDoc
     */
    public function generate(array $texts): array
    {
        $request = $this->requestFactory->createRequest('POST', 'https://api.mistral.ai/v1/embeddings')
            ->withHeader('Authorization', 'Bearer ' . $this->apiKey)
            ->withHeader('Content-Type', 'application/json');

        $body = $this->streamFactory->createStream(json_encode([
            'model' => $this->model,
            'input' => $texts,
        ]));

        $request = $request->withBody($body);

        $response = $this->httpClient->sendRequest($request);
        $data = json_decode($response->getBody()->getContents(), true);

        return array_map(fn($item) => $item['embedding'], $data['data']);
    }
}
