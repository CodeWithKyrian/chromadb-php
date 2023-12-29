<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Enums;

enum CollectionInclude: string
{
    case EMBEDDINGS = 'embeddings';
    case METADATAS = 'metadatas';
    case DOCUMENTS = 'documents';
    case IMAGES = 'images';

    public static function values(): array
    {
        return [
            self::EMBEDDINGS->value,
            self::METADATAS->value,
            self::DOCUMENTS->value,
            self::IMAGES->value,
        ];
    }
}
