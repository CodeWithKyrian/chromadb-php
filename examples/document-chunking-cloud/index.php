<?php

require __DIR__ . '/../../vendor/autoload.php';

use Codewithkyrian\ChromaDB\ChromaDB;
use Codewithkyrian\ChromaDB\Embeddings\JinaEmbeddingFunction;
use Codewithkyrian\ChromaDB\Types\Includes;
use Codewithkyrian\ChromaDB\Types\Record;

$config = parseConfig($argv);
$mode = $config['mode'];
$queryText = $config['query'];
$docPath = __DIR__ . '/document.txt';

echo "--- Chroma Cloud Example: $mode Mode ---\n";
echo "Tenant: {$config['tenant']}, Database: {$config['database']}\n";

$client = ChromaDB::cloud($config['api_key'], $config['tenant'], $config['database'])->connect();
echo "Connected to Chroma Cloud version: " . $client->version() . "\n";

$ef = new JinaEmbeddingFunction($config['jina_key']);

try {
    if ($mode === 'ingest') {
        echo "Starting Ingestion...\n";

        if (!file_exists($docPath))
            die("Document not found: $docPath\n");

        $records = chunkDocument($docPath);
        echo "Parsed " . count($records) . " chunks from document.\n";

        try {
            $client->deleteCollection($config['collection_name']);
        } catch (\Exception $e) {
        } // Clean start
        $collection = $client->createCollection($config['collection_name'], null, $ef);

        echo "Embedding and adding " . count($records) . " items...\n";
        $collection->add($records);

        echo "Ingestion Complete!\n";
    } elseif ($mode === 'query') {
        echo "Querying: \"$queryText\"\n";

        $collection = $client->getCollection($config['collection_name'], $ef);

        $response = $collection->query(
            queryTexts: [$queryText],
            include: [Includes::Documents, Includes::Metadatas, Includes::Distances],
            nResults: 3,
        );

        echo "\n--- Results ---\n";
        $resultRecords = $response->asRecords();

        foreach ($resultRecords[0] as $index => $record) {
            echo "[$index] (Distance: " . ($record->distance ?? 'N/A') . ")\n";
            echo "Location: {$record->metadata['chapter']}, {$record->metadata['page']}\n";
            echo "Content: " . substr($record->document, 0, 150) . "...\n";
            echo "---------------------------\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Parses CLI arguments into a configuration array.
 *
 * @param string[] $args
 * @return array{
 *     api_key: string,
 *     tenant: string,
 *     database: string,
 *     jina_key: string,
 *     collection_name: string,
 *     mode: string|null,
 *     query: string
 * }
 */
function parseConfig(array $args): array
{
    $config = [
        'api_key' => getenv('CHROMA_API_KEY') ?: '',
        'tenant' => 'default_tenant',
        'database' => 'default_database',
        'jina_key' => getenv('JINA_API_KEY') ?: '',
        'collection_name' => 'history_of_ai',
        'mode' => null,
        'query' => "Which event marked the birth of symbolic AI?",
    ];

    foreach ($args as $i => $arg) {
        if ($arg === '-mode')
            $config['mode'] = $args[$i + 1] ?? null;
        if ($arg === '--query')
            $config['query'] = $args[$i + 1] ?? $config['query'];
        if ($arg === '--api-key')
            $config['api_key'] = $args[$i + 1];
        if ($arg === '--tenant')
            $config['tenant'] = $args[$i + 1];
        if ($arg === '--database')
            $config['database'] = $args[$i + 1];
        if ($arg === '--jina-key')
            $config['jina_key'] = $args[$i + 1];
    }

    if (!$config['api_key']) {
        die("Error: Chroma Cloud API Key is required. Set CHROMA_API_KEY env var or pass --api-key.\n");
    }
    if (!$config['jina_key']) {
        die("Error: Jina API Key is required (for embeddings). Set JINA_API_KEY env var or pass --jina-key.\n");
    }
    if (!$config['mode'] || !in_array($config['mode'], ['ingest', 'query'])) {
        die("Usage: php chroma_cloud.php -mode [ingest|query] [--query \"text\"] [--api-key key] [--jina-key key] ...\n");
    }

    return $config;
}

/**
 * Reads the document and chunks it into Records.
 *
 * @param string $path
 * @return Record[]
 */
function chunkDocument(string $path): array
{
    $content = file_get_contents($path);
    $lines = explode("\n", $content);
    $records = [];
    $currentChapter = "Intro";
    $currentPage = "1";
    $buffer = "";

    $createRecord = function ($text, $chapter, $page) {
        return Record::make(uniqid("chunk_"))
            ->withDocument($text)
            ->withMetadata(['chapter' => $chapter, 'page' => $page]);
    };

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line))
            continue;

        if (str_starts_with($line, 'CHAPTER')) {
            if (!empty($buffer))
                $records[] = $createRecord($buffer, $currentChapter, $currentPage);
            $buffer = "";
            $currentChapter = $line;
        } elseif (str_starts_with($line, 'PAGE')) {
            if (!empty($buffer))
                $records[] = $createRecord($buffer, $currentChapter, $currentPage);
            $buffer = "";
            $currentPage = $line;
        } else {
            if (!empty($buffer))
                $buffer .= " ";
            $buffer .= $line;
        }
    }
    if (!empty($buffer))
        $records[] = $createRecord($buffer, $currentChapter, $currentPage);

    return $records;
}

// Suggested Queries:
// - "What happened at the Dartmouth Workshop?"
// - "Who proposed the Turing Test?"
// - "What was the significance of AlexNet in 2012?"
// - "How do Large Language Models and Generative AI work?"
// - "Who is considered the first computer programmer?"
