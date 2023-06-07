<?php

declare(strict_types=1);

namespace Team64j\LaravelAmqp\Commands;

use Anik\Amqp\ConsumableMessage;
use Anik\Laravel\Amqp\Facades\Amqp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Team64j\LaravelAmqp\MessageHandler\MessageHandlerInterface;
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
     * @var MessageHandlerInterface|null
     */
    protected ?MessageHandlerInterface $messageHandler;

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
        $configuration = config('amqp.connections.' . $name);

        if (empty($configuration)) {
            $output->writeln('<error>The message queue is not configured for the consumer "' . $name . '".</error>');

            return 1;
        }

        if (empty($configuration['handler'])) {
            $output->writeln('<error>Handler is not configured for the consumer "' . $name . '".</error>');

            return 1;
        }

        $this->messageHandler = !$configuration['handler'] instanceof MessageHandlerInterface
            ? app($configuration['handler'])
            : $configuration['handler'];

        Amqp::connection($name)
            ->consume(function (ConsumableMessage $message) use ($configuration) {
                try {
                    $this->validate($message);
                    $this->messageHandler->handle($message);

                    $message->ack();
                } catch (UnacknowledgableQueueMessageExceptionInterface $e) {
                    $message->reject(false);

                    Log::error($e->getMessage(), [
                        'payload' => $message->getMessageBody(),
                    ]);
                } catch (UndecodablePayloadExceptionInterface $e) {
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
}
