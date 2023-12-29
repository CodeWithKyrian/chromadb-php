<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Embeddings;

use Codewithkyrian\ChromaDB\Embeddings\EmbeddingFunction;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class JinaEmbeddingFunction implements EmbeddingFunction
{

    private const API_URL = 'https://api.jina.ai/v1/embeddings';

    public function __construct(
        public readonly string $apiKey,
        public readonly string $model = 'jina-embeddings-v2-base-en',
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function generate(array $texts): array
    {
        $client = new Client([
            'headers' => [
                'Authorization' => "Bearer $this->apiKey",
                'Content-Type' => 'application/json',
                'Accept-Encoding' => 'identity',
            ]
        ]);

        try {
            $response = $client->post(self::API_URL, [
                'body' => json_encode([
                    'model' => $this->model,
                    'input' => $texts,
                ])
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            $embeddings = $result['data'];
            usort($embeddings, fn($a, $b) => $a['index'] <=> $b['index']);

            return array_map(fn($embedding) => $embedding['embedding'], $embeddings);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Failed to generate embeddings', 0, $e);
        }
    }
}