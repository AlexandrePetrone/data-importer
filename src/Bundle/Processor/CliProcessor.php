<?php

/*
 * This file is part of the DataImporter package.
 *
 * (c) Loïc Sapone <loic@sapone.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IQ2i\DataImporter\Bundle\Processor;

use IQ2i\DataImporter\Bundle\Exception\ItemHandlingException;
use IQ2i\DataImporter\Exchange\Message;
use IQ2i\DataImporter\Processor\BatchProcessorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CliProcessor implements BatchProcessorInterface
{
    private OutputInterface $output;
    private \Closure $handleItem;
    private \Closure $handleBatch;

    private SymfonyStyle $io;
    private ProgressBar $progressBar;
    private bool $stepByStep;
    private bool $pauseOnError;
    private int $batchSize;

    private array $errors;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        callable $handleItem,
        callable $handleBatch
    ) {
        $this->output = $output;
        $this->handleItem = $handleItem;
        $this->handleBatch = $handleBatch;

        $this->io = new SymfonyStyle($input, $output);
        $this->progressBar = new ProgressBar($output);

        $this->stepByStep = (bool) $input->getOption('step');
        if ($this->stepByStep && $output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
            $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        }
        $this->pauseOnError = (bool) $input->getOption('pause-on-error');
        $this->batchSize = (int) $input->getOption('batch-size');
    }

    public function begin()
    {
        if (OutputInterface::VERBOSITY_NORMAL === $this->output->getVerbosity()) {
            $this->progressBar->start();
        }
    }

    public function item(Message $message)
    {
        try {
            ($this->handleItem)($message);
        } catch (\Exception $exception) {
            if ($this->pauseOnError) {
                throw new ItemHandlingException('Error during item handling', Command::FAILURE, $exception);
            }

            $this->errors[$message->getCurrentIteration()] = $exception->getMessage();
        }

        switch ($this->output->getVerbosity()) {
            case OutputInterface::VERBOSITY_NORMAL:
                $this->progressBar->setMaxSteps($message->getTotalIteration());
                $this->progressBar->advance();
                break;

            case OutputInterface::VERBOSITY_VERBOSE:
            case OutputInterface::VERBOSITY_VERY_VERBOSE:
            case OutputInterface::VERBOSITY_DEBUG:
                $this->io->comment(sprintf('Row %d/%d',
                    $message->getCurrentIteration(),
                    $message->getTotalIteration()
                ));
                $this->io->definitionList(...$this->formatDataToDebug($message->getData()));
                break;
        }

        if ($this->stepByStep && $message->getCurrentIteration() < $message->getTotalIteration()) {
            if (!$this->io->confirm('Continue?')) {
                $this->io->error('Import cancelled');
                exit;
            }
        }
    }

    public function end()
    {
        if (OutputInterface::VERBOSITY_NORMAL === $this->output->getVerbosity()) {
            $this->progressBar->finish();
            $this->io->newLine(2);
        }

        if (!empty($this->errors)) {
            $elements = [];
            foreach ($this->errors as $key => $value) {
                $elements[] = sprintf('Line #%d: %s', $key, $value);
            }
            $this->io->error('Errors occured during import:');
            $this->io->listing($elements);
        }
    }

    public function batch()
    {
        ($this->handleBatch)();
    }

    public function getBatchSize()
    {
        return $this->batchSize;
    }

    private function formatDataToDebug($data): array
    {
        $result = [];

        if (\is_array($data)) {
            foreach ($data as $key => $value) {
                $result[] = [$key => $value];
            }
        }

        return $result;
    }
}
