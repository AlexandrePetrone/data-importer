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

namespace IQ2i\DataImporter\Tests\fixtures\Processor;

use IQ2i\DataImporter\Exchange\Message;
use IQ2i\DataImporter\Processor\AsyncProcessorInterface;
use IQ2i\DataImporter\Processor\ProcessorInterface;

class AsyncProcessor implements ProcessorInterface, AsyncProcessorInterface
{
    public function begin(Message $message)
    {
    }

    public function item(Message $message)
    {
    }

    public function end(Message $message)
    {
    }
}
