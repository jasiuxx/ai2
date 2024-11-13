<?php

namespace App\Command;

use App\Service\WeatherUtil;
use App\Entity\Location;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

class WeatherLocationCommand extends Command
{
    // Serwis WeatherUtil oraz EntityManagerInterface
    private $weatherUtil;
    private $entityManager;

    // Konstruktor z zależnościami
    public function __construct(WeatherUtil $weatherUtil, EntityManagerInterface $entityManager)
    {
        $this->weatherUtil = $weatherUtil;
        $this->entityManager = $entityManager;

        // Zawsze wywołuj konstruktor rodzica
        parent::__construct();
    }

    // Konfiguracja komendy
    protected function configure(): void
    {
        $this
            ->setName('weather:location')
            ->setDescription('Fetch weather forecast for a specific location')
            ->addArgument('id', InputArgument::REQUIRED, 'The ID of the location');
    }

    // Wykonanie komendy
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $locationId = $input->getArgument('id');

        $location = $this->entityManager->getRepository(Location::class)->find($locationId);

        if (!$location) {
            $io->error('Location not found');
            return Command::FAILURE;
        }
        $measurements = $this->weatherUtil->getWeatherForLocation($location);
        $io->writeln(sprintf('Location: %s', $location->getCity()));

        if (empty($measurements)) {
            $io->writeln('No weather data available for this location.');
            return Command::SUCCESS;
        }
        foreach ($measurements as $measurement) {
            $io->writeln(sprintf("\t%s: %s°C",
                $measurement->getDate()->format('Y-m-d'),
                $measurement->getCelsius()
            ));
        }

        return Command::SUCCESS;
    }
}
