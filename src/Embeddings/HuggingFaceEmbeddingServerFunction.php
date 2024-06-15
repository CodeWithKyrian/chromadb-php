<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Embeddings;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class HuggingFaceEmbeddingServerFunction implements EmbeddingFunction
{

    public function __construct(
        public readonly string $baseUrl = 'http://localhost:8080',
    )
    {
    }

    public function generate(array $texts): array
    {
        $client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ]);

        try {
            $response = $client->post('embed', [
                'json' => [
                    'inputs' => $texts,
                ]
            ]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Failed to generate embeddings', 0, $e);
        }

        return json_decode($response->getBody()->getContents(), true);
    }
}