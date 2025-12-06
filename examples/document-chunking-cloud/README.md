# Document Chunking and Embedding Example

This example demonstrates how to chunk a document, generate embeddings, and store them in Chroma Cloud for semantic search and retrieval.

## Overview

The example performs the following operations:

1. **Ingestion Mode**: Chunks a document (`document.txt`) into smaller pieces, generates embeddings using Jina AI, and stores them in Chroma Cloud
2. **Query Mode**: Performs semantic search on the stored documents using natural language queries

## Prerequisites

- PHP 8.1 or higher
- Chroma Cloud account with API key
- Jina AI API key (for embeddings)
- Composer dependencies installed (`composer install`)

## Setup

1. Set your API keys as environment variables:

```bash
export CHROMA_API_KEY="your-chroma-cloud-api-key"
export JINA_API_KEY="your-jina-api-key"
```

Or pass them via CLI arguments (see Usage below).

## Usage

### Ingest Mode

Chunk and store the document to Chroma Cloud:

```bash
php index.php -mode ingest
```

With custom options:

```bash
php index.php -mode ingest \
  --api-key "your-chroma-api-key" \
  --jina-key "your-jina-api-key" \
  --tenant "my-tenant" \
  --database "my-database"
```

### Query Mode

Search the stored documents:

```bash
php index.php -mode query --query "What happened at the Dartmouth Workshop?"
```

With custom options:

```bash
php index.php -mode query \
  --query "Who proposed the Turing Test?" \
  --api-key "your-chroma-api-key" \
  --jina-key "your-jina-api-key" \
  --tenant "my-tenant" \
  --database "my-database"
```

## CLI Arguments

| Argument | Description | Default | Required |
|----------|-------------|---------|----------|
| `-mode` | Operation mode: `ingest` or `query` | - | Yes |
| `--query` | Query text for search (query mode only) | "Which event marked the birth of symbolic AI?" | No |
| `--api-key` | Chroma Cloud API key | `CHROMA_API_KEY` env var | Yes |
| `--jina-key` | Jina AI API key for embeddings | `JINA_API_KEY` env var | Yes |
| `--tenant` | Chroma Cloud tenant name | `default_tenant` | No |
| `--database` | Chroma Cloud database name | `default_database` | No |
| `--collection-name` | Collection name to use | `history_of_ai` | No |

## Example Queries

Try these example queries to test the semantic search:

```bash
# Historical events
php index.php -mode query --query "What happened at the Dartmouth Workshop?"

# People and contributions
php index.php -mode query --query "Who proposed the Turing Test?"

# Technical breakthroughs
php index.php -mode query --query "What was the significance of AlexNet in 2012?"

# Concepts and explanations
php index.php -mode query --query "How do Large Language Models and Generative AI work?"

# Historical figures
php index.php -mode query --query "Who is considered the first computer programmer?"
```

## How It Works

### Document Chunking

The document is chunked based on:
- **CHAPTER markers**: New chapters create new chunks
- **PAGE markers**: New pages create new chunks
- **Text accumulation**: Text between markers is accumulated into chunks

Each chunk includes:
- Unique ID
- Document text
- Metadata (chapter and page information)

### Embedding Generation

- Uses Jina AI's embedding function to convert text chunks into vector embeddings
- Embeddings are generated in batch for efficiency
- All chunks are embedded before storage

### Storage

- Chunks are stored in a Chroma Cloud collection
- The collection is recreated on each ingestion (previous data is deleted)
- Each chunk maintains its metadata for filtering and context

### Querying

- Natural language queries are converted to embeddings using the same Jina AI function
- Vector similarity search finds the most relevant chunks
- Results include distance scores, documents, and metadata

## Output

### Ingest Mode

```
--- Chroma Cloud Example: ingest Mode ---
Tenant: default_tenant, Database: default_database
Connected to Chroma Cloud version: 0.1.0
Starting Ingestion...
Parsed 9 chunks from document.
Embedding and adding 9 items...
Ingestion Complete!
```

### Query Mode

```
--- Chroma Cloud Example: query Mode ---
Tenant: default_tenant, Database: default_database
Connected to Chroma Cloud version: 0.1.0
Querying: "What happened at the Dartmouth Workshop?"

--- Results ---
[0] (Distance: 0.123)
Location: CHAPTER 1: The Dawn of Thinking Machines, PAGE 3
Content: The 1956 Dartmouth Workshop is widely considered the founding event of AI as a field. John McCarthy, Marvin Minsky, Nathaniel Rochester, and Claude Shannon brought together...
---------------------------
```

## Customization

### Using a Different Document

Replace `document.txt` with your own document. The chunking logic will automatically process it based on CHAPTER and PAGE markers.

### Using a Different Embedding Function

Modify `index.php` to use a different embedding function:

```php
use Codewithkyrian\ChromaDB\Embeddings\OpenAIEmbeddingFunction;

$ef = new OpenAIEmbeddingFunction($config['openai_key']);
```

### Custom Chunking Strategy

Modify the `chunkDocument()` function to implement your own chunking logic (e.g., by sentence, by paragraph, fixed-size chunks, etc.).

## Troubleshooting

**Error: Chroma Cloud API Key is required**
- Set `CHROMA_API_KEY` environment variable or use `--api-key` argument

**Error: Jina API Key is required**
- Set `JINA_API_KEY` environment variable or use `--jina-key` argument

**Error: Collection not found**
- Run ingestion mode first to create and populate the collection

**No results returned**
- Ensure the collection was successfully ingested
- Try different query phrasings
- Check that the query is related to the document content

