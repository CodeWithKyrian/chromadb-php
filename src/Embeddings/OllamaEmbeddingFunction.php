<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Embeddings;

use GuzzleHttp\Client;

class OllamaEmbeddingFunction implements EmbeddingFunction
{
    private Client $client;

    public function __construct(
        public readonly string $baseUrl = 'http://localhost:11434',
        public readonly string $model = 'all-minilm',
    )
    {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public function generate(array $texts): array
    {
        try {
            $embeddings = [];

            foreach ($texts as $text) {
                $response = $this->client->post('api/embeddings', [
                    'json' => [
                        'prompt' => $text,
                        'model' => $this->model,
                    ]
                ]);

                $result = json_decode($response->getBody()->getContents(), true);

                $embeddings[] = $result['embedding'];
            }

            return $embeddings;
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to generate embeddings', 0, $e);
        }
    }
}