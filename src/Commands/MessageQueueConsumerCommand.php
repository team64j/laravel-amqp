<?php

declare(strict_types=1);

namespace Team64j\LaravelAmqp\Commands;

use Anik\Amqp\ConsumableMessage;
use Anik\Laravel\Amqp\Facades\Amqp;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Team64j\LaravelAmqp\Interfaces\InvalidPayloadExceptionInterface;
use Team64j\LaravelAmqp\Interfaces\MessageHandlerInterface;
use Team64j\LaravelAmqp\Interfaces\UnacknowledgableQueueMessageExceptionInterface;
use Team64j\LaravelAmqp\Interfaces\UndecodablePayloadExceptionInterface;
use Throwable;

class MessageQueueConsumerCommand extends Command
{
    /**
     * @var string
     */
    protected $name = 'mq:consume';

    /**
     * @var string
     */
    protected $description = 'Consumes messages from the specified queue.';

    /**
     * @var array
     */
    protected array $configuration = [];

    /**
     * @var MessageHandlerInterface|null
     */
    protected ?MessageHandlerInterface $messageHandler = null;

    /**
     * @var Validator|null
     */
    protected ?Validator $validatorHandler = null;

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('from', InputArgument::OPTIONAL);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('from');
        $this->configuration = config('amqp.connections.' . $name) ?? [];

        if (empty($this->configuration)) {
            $output->writeln('<error>The message queue is not configured for the consumer "' . $name . '".</error>');

            return 1;
        }

        Amqp::connection($name)
            ->consume(function (ConsumableMessage $message) {
                try {
                    $this->processMessage($message);
                    $message->ack();
                } catch (UnacknowledgableQueueMessageExceptionInterface|UndecodablePayloadExceptionInterface $e) {
                    $message->reject(false);

                    Log::error($e->getMessage(), [
                        'payload' => $message->getMessageBody(),
                    ]);
                } catch (InvalidPayloadExceptionInterface $e) {
                    $message->reject(false);

                    Log::error($e->getMessage(), [
                        'errors' => $e->getErrors(),
                        'payload' => $message->getMessageBody(),
                    ]);
                } catch (Throwable $e) {
                    $message->nack();

                    Log::error($e->getMessage(), [
                        'exception' => $e,
                        'payload' => $message->getMessageBody(),
                    ]);
                }
            });

        return 0;
    }

    /**
     * @param ConsumableMessage $message
     * @return void
     * @throws Exception
     */
    protected function processMessage(ConsumableMessage $message): void
    {
        $handler = $this->configuration['message']['handler'] ?? null;

        if ($handler && is_null($this->messageHandler)) {
            $this->messageHandler = is_string($handler) ? app($handler) : $handler;
        }

        if ($this->messageHandler instanceof MessageHandlerInterface) {
            $this->messageHandler->handle($message);
        }
    }
}
