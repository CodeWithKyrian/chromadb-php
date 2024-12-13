<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Embeddings;

use GuzzleHttp\Client;
use Psr\Http\Client\ClientExceptionInterface;

class MistralAIEmbeddingFunction implements EmbeddingFunction
{
    private Client $client;

    public function __construct(
        public readonly string $apiKey,
        public readonly string $organization = '',
        public readonly string $model = 'mistral-embed',
    )
    {
        $headers = [
            'Authorization' => "Bearer $this->apiKey",
            'Content-Type' => 'application/json',
        ];


        $this->client = new Client([
            'base_uri' => 'https://api.mistral.ai/v1/',
            'headers' => $headers
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
            throw new \RuntimeException("Error calling MistralAI API: {$e->getMessage()}", 0, $e);
        }
    }
}
