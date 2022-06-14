<?php

namespace App\Command;

use App\Repository\OrderRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\Attribute\Required;

#[AsCommand(
    name: 'app:update-orders',
    description: 'Updates Submitted orders to Delayed if past delivery date'
)]
class ProcessDelayedOrdersCommand extends Command
{
    #[Required]
    public OrderRepository $orderRepository;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
       $this->orderRepository->updateDelayedOrders();

        return 0;
    }
}