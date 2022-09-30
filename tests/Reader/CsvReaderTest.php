<?php

declare(strict_types=1);

/*
 * This file is part of the DataImporter package.
 *
 * (c) Loïc Sapone <loic@sapone.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IQ2i\DataImporter\Tests\Reader;

use IQ2i\DataImporter\Reader\CsvReader;
use PHPUnit\Framework\TestCase;

class CsvReaderTest extends TestCase
{
    public function testReadCsvFileWithHeader()
    {
        // init reader
        $reader = new CsvReader(
            __DIR__.'/../fixtures/csv/books_with_headers.csv',
            null,
            [CsvReader::CONTEXT_DELIMITER => ';']
        );

        // test denormalization
        $this->assertFalse($reader->isDenormalizable());

        // test file
        $this->assertEquals(
            new \SplFileInfo(__DIR__.'/../fixtures/csv/books_with_headers.csv'),
            $reader->getFile()
        );

        // test count
        $this->assertCount(2, $reader);

        // test index
        $this->assertEquals(1, $reader->index());
        $this->assertEquals(1, $reader->key());

        // test headers
        $this->assertEquals(
            ['author', 'title', 'genre', 'price', 'description'],
            \array_keys($reader->current())
        );
        $this->assertArrayHasKey('author', $reader->current());
        $this->assertNotNull($reader->current()['author']);
        $this->assertArrayHasKey('title', $reader->current());
        $this->assertNotNull($reader->current()['title']);
        $this->assertArrayHasKey('genre', $reader->current());
        $this->assertNotNull($reader->current()['genre']);
        $this->assertArrayHasKey('price', $reader->current());
        $this->assertNotNull($reader->current()['price']);
        $this->assertArrayHasKey('description', $reader->current());
        $this->assertNotNull($reader->current()['description']);

        // test line
        $reader->next();
        $this->assertEquals(2, $reader->index());
        $this->assertEquals(
            [
                'author' => 'Ralls, Kim',
                'title' => 'Midnight Rain',
                'genre' => 'Fantasy',
                'price' => '5.95',
                'description' => 'A former architect battles corporate zombies, an evil sorceress, and her own childhood to become queen of the world.',
            ],
            $reader->current()
        );

        // test and of file
        $reader->next();
        $this->assertEquals([], $reader->current());
    }

    public function testReadCsvWithoutHeader()
    {
        // init reader
        $reader = new CsvReader(
            __DIR__.'/../fixtures/csv/books_without_headers.csv',
            null,
            [
                CsvReader::CONTEXT_DELIMITER => ';',
                CsvReader::CONTEXT_NO_HEADERS => true,
            ]
        );

        // test denormalization
        $this->assertFalse($reader->isDenormalizable());

        // test file
        $this->assertEquals(
            new \SplFileInfo(__DIR__.'/../fixtures/csv/books_without_headers.csv'),
            $reader->getFile()
        );

        // test count
        $this->assertCount(2, $reader);

        // test index
        $this->assertEquals(1, $reader->index());
        $this->assertEquals(0, $reader->key());

        // test headers
        $this->assertEquals(
            [0, 1, 2, 3, 4],
            \array_keys($reader->current())
        );

        // test content
        $reader->next();
        $this->assertEquals(2, $reader->index());
        $this->assertEquals(
            [
                'Ralls, Kim',
                'Midnight Rain',
                'Fantasy',
                '5.95',
                'A former architect battles corporate zombies, an evil sorceress, and her own childhood to become queen of the world.',
            ],
            $reader->current()
        );

        // test and of file
        $reader->next();
        $this->assertEquals([], $reader->current());
    }
}
