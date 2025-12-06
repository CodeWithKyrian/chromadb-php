<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB\Tests\Feature;

use Codewithkyrian\ChromaDB\ChromaDB;
use Codewithkyrian\ChromaDB\Exceptions\ChromaException;
use Codewithkyrian\ChromaDB\Exceptions\InvalidArgumentException;
use Codewithkyrian\ChromaDB\Query\Where;
use Codewithkyrian\ChromaDB\Types\Record;

beforeEach(function () {
    $this->client = ChromaDB::factory()
        ->withHeader('X-Chroma-Token', 'test-token')
        ->withDatabase('test_database')
        ->withTenant('test_tenant')
        ->connect();

    $this->client->deleteAllCollections();

    $this->collection = $this->client->createCollection('filtering_test_collection');

    $this->collection->add([
        new Record('1', embedding: [0.1], metadata: ['cat' => 'A', 'val' => 10], document: 'php framework'),
        new Record('2', embedding: [0.2], metadata: ['cat' => 'B', 'val' => 20], document: 'laravel framework'),
        new Record('3', embedding: [0.3], metadata: ['cat' => 'A', 'val' => 30], document: 'symfony framework'),
        new Record('4', embedding: [0.4], metadata: ['cat' => 'C', 'val' => 40], document: 'react library'),
        new Record('5', embedding: [0.5], metadata: ['cat' => 'B', 'val' => 50], document: 'vue library'),
    ]);
});

afterEach(function () {
    $this->client->reset();
});

describe('metadata filtering operators', function () {
    it('filters by equality (eq)', function () {
        $res = $this->collection->get(where: Where::field('cat')->eq('A'));
        expect($res->ids)->toHaveCount(2)->toContain('1', '3');
    });

    it('filters by inequality (ne)', function () {
        $res = $this->collection->get(where: Where::field('cat')->ne('A'));
        expect($res->ids)->toHaveCount(3)->toContain('2', '4', '5');
    });

    it('filters by greater than (gt)', function () {
        $res = $this->collection->get(where: Where::field('val')->gt(30));
        expect($res->ids)->toHaveCount(2)->toContain('4', '5');
    });

    it('filters by greater than or equal (gte)', function () {
        $res = $this->collection->get(where: Where::field('val')->gte(30));
        expect($res->ids)->toHaveCount(3)->toContain('3', '4', '5');
    });

    it('filters by less than (lt)', function () {
        $res = $this->collection->get(where: Where::field('val')->lt(20));
        expect($res->ids)->toHaveCount(1)->toContain('1');
    });

    it('filters by less than or equal (lte)', function () {
        $res = $this->collection->get(where: Where::field('val')->lte(20));
        expect($res->ids)->toHaveCount(2)->toContain('1', '2');
    });

    it('filters by inclusion (in)', function () {
        $res = $this->collection->get(where: Where::field('cat')->in(['A', 'C']));
        expect($res->ids)->toHaveCount(3)->toContain('1', '3', '4');
    });

    it('filters by exclusion (nin)', function () {
        $res = $this->collection->get(where: Where::field('cat')->notIn(['A', 'C']));
        expect($res->ids)->toHaveCount(2)->toContain('2', '5');
    });

    it('filters by logical AND', function () {
        $res = $this->collection->get(where: Where::all(
            Where::field('cat')->eq('A'),
            Where::field('val')->gt(20)
        ));
        expect($res->ids)->toBe(['3']);
    });

    it('filters by logical OR', function () {
        $res = $this->collection->get(where: Where::any(
            Where::field('cat')->eq('C'),
            Where::field('val')->gt(40)
        ));
        expect($res->ids)->toHaveCount(2)->toContain('4', '5');
    });

    it('cannot get with an invalid operator', function () {
        $this->collection->get(where: ['cat' => ['$invalid' => 'A']]);
    })->throws(InvalidArgumentException::class, 'Invalid where clause');

    it('cannot get with a non-list to $and', function () {
        $this->collection->get(where: ['$and' => 'invalid']);
    })->throws(ChromaException::class, 'Invalid where clause');

    it('cannot get with a non-list to $or', function () {
        $this->collection->get(where: ['$or' => 'invalid']);
    })->throws(InvalidArgumentException::class, 'Invalid where clause');

    it('cannot get with a list of Where objects directly', function () {
        $this->collection->get(where: [
            Where::field('cat')->eq('A'),
            Where::field('val')->eq(10)
        ]);
    })->throws(InvalidArgumentException::class, 'Invalid where clause');

    it('cannot get with a random array structure', function () {
        $this->collection->get(where: ['random' => ['junk']]);
    })->throws(InvalidArgumentException::class, 'Invalid where clause');
});

describe('document content filtering', function () {
    it('filters by contains', function () {
        $res = $this->collection->get(whereDocument: Where::document()->contains('framework'));
        expect($res->ids)->toHaveCount(3)->toContain('1', '2', '3');
    });

    it('filters by not contains', function () {
        $res = $this->collection->get(whereDocument: Where::document()->notContains('framework'));
        expect($res->ids)->toHaveCount(2)->toContain('4', '5');
    });

    it('filters by regex matches', function () {
        $res = $this->collection->get(whereDocument: Where::document()->matches('^php'));
        expect($res->ids)->toBe(['1']);
    });

    it('filters by regex not matches', function () {
        $res = $this->collection->get(whereDocument: Where::document()->notMatches('framework$'));
        expect($res->ids)->toHaveCount(2)->toContain('4', '5');
    });

    it('filters by logical OR with document', function () {
        $res = $this->collection->get(whereDocument: Where::any(
            Where::document()->contains('laravel'),
            Where::document()->contains('vue')
        ));
        expect($res->ids)->toHaveCount(2)->toContain('2', '5');
    });

    it('filters by logical AND with document', function () {
        $res = $this->collection->get(whereDocument: Where::all(
            Where::document()->contains('framework'),
            Where::document()->contains('php')
        ));
        expect($res->ids)->toBe(['1']);
    });

    it('cannot get with an invalid document operator', function () {
        $this->collection->get(whereDocument: ['$invalid' => 'A']);
    })->throws(ChromaException::class, 'Invalid where document clause');

    it('cannot get with a non-list to $and in whereDocument', function () {
        $this->collection->get(whereDocument: ['$and' => 'invalid']);
    })->throws(InvalidArgumentException::class, 'Invalid where document clause');

    it('cannot get with a non-list to $or in whereDocument', function () {
        $this->collection->get(whereDocument: ['$or' => 'invalid']);
    })->throws(InvalidArgumentException::class, 'Invalid where document clause');

    it('cannot get with a list of Where objects directly in whereDocument', function () {
        $this->collection->get(whereDocument: [
            Where::document()->contains('A'),
            Where::document()->contains('B')
        ]);
    })->throws(ChromaException::class, 'Invalid where document clause');

    it('cannot get with a random array structure in whereDocument', function () {
        $this->collection->get(whereDocument: ['random' => ['junk']]);
    })->throws(InvalidArgumentException::class, 'Invalid where document clause');
});

it('can query with filtering', function () {
    $res = $this->collection->query(
        queryEmbeddings: [[0.1]],
        nResults: 5,
        where: Where::field('cat')->eq('A')
    );
    expect($res->ids[0])->toHaveCount(2)->toContain('1', '3');
});

it('can delete with filtering', function () {
    $this->collection->delete(where: Where::field('cat')->eq('B'));

    $res = $this->collection->get();
    expect($res->ids)->not->toContain('2', '5')
        ->and($res->ids)->toHaveCount(3);
});

it('can delete with document filtering', function () {
    $this->collection->delete(whereDocument: Where::document()->contains('library'));

    $res = $this->collection->get();
    expect($res->ids)->not->toContain('4', '5');
});
