<?php

namespace App\Command;

use App\Kernel;
use Psr\Log\LoggerInterface;
use App\Controller\GetFileController;
use App\Service\ConsommationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SendDataCsvCommand extends Command
{
    protected $conso;
    protected $logger;
    protected static $defaultName = 'test:cron';

    public function __construct(LoggerInterface $logger, ConsommationService $conso)
    {
        $this->conso = $conso;
        $this->logger = $logger;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
        ->setDescription('get data in csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->conso->getData();
        return Command::SUCCESS;
    }
}