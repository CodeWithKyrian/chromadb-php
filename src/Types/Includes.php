<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB\Types;

enum Includes: string
{
    case Distances = 'distances';
    case Documents = 'documents';
    case Embeddings = 'embeddings';
    case Metadatas = 'metadatas';
    case Uris = 'uris';
}
