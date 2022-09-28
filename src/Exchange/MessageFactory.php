<?php

/*
 * This file is part of the DataImporter package.
 *
 * (c) Loïc Sapone <loic@sapone.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IQ2i\DataImporter\Exchange;

use IQ2i\DataImporter\Reader\ReaderInterface;

class MessageFactory
{
    /**
     * @param mixed|null $data
     */
    public static function create(ReaderInterface $reader, $data = null, ?string $archiveFilePath = null): Message
    {
        return new Message(
            $reader->getFile()->getFilename(),
            $reader->getFile()->getPathname(),
            $reader->index(),
            $reader->count(),
            $data,
            $archiveFilePath
        );
    }
}
