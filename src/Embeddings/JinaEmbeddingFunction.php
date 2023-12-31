<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Embeddings;

use Codewithkyrian\ChromaDB\Embeddings\EmbeddingFunction;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Client\ClientExceptionInterface;

class JinaEmbeddingFunction implements EmbeddingFunction
{
    private Client $client;

    public function __construct(
        public readonly string $apiKey,
        public readonly string $model = 'jina-embeddings-v2-base-en',
    )
    {
        $this->client = new Client([
            'base_uri' => 'https://api.jina.ai/v1/',
            'headers' => [
                'Authorization' => "Bearer $this->apiKey",
                'Content-Type' => 'application/json',
                'Accept-Encoding' => 'identity',
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public function generate(array $texts): array
    {
        try {
            $response = $this->client->post('embeddings', [
                'json' => [
                    'model' => $this->model,
                    'input' => $texts,
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            $embeddings = $result['data'];
            usort($embeddings, fn($a, $b) => $a['index'] <=> $b['index']);

            return array_map(fn($embedding) => $embedding['embedding'], $embeddings);
        } catch (ClientExceptionInterface $e) {
            throw new \RuntimeException("Error calling Jina AI API: {$e->getMessage()}", 0, $e);
        }
    }
}