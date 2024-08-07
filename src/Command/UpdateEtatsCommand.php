<?php

namespace App\Command;

use AllowDynamicProperties;
use App\Service\MajEtatSortie;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;

#[AllowDynamicProperties]
#[AsCommand(
    name: 'app:update-etats',
    description: 'Met à jour les états des sorties automatiquement',
)]
#[AsPeriodicTask(frequency: 60)]
class UpdateEtatsCommand extends Command
{
    public function __construct(MajEtatSortie $majEtatSortie)
    {
        $this->majEtatSortie = $majEtatSortie;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->majEtatSortie->mettreAjourEtatSortie();
        $io->success('Les états des sorties ont été mis à jour avec succès');
        return Command::SUCCESS;
    }


}
