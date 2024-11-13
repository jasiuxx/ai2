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

class WeatherCityCommand extends Command
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
            ->setName('weather:city')
            ->setDescription('Fetch weather forecast for a city based on country code and city name')
            ->addArgument('countryCode', InputArgument::REQUIRED, 'The country code')
            ->addArgument('city', InputArgument::REQUIRED, 'The name of the city');
    }

    // Wykonanie komendy
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $countryCode = $input->getArgument('countryCode');
        $city = $input->getArgument('city');

        $location = $this->entityManager->getRepository(Location::class)->findOneBy([
            'country' => $countryCode,
            'city' => $city,
        ]);

        if (!$location) {
            $io->error('Location not found for the given country code and city.');
            return Command::FAILURE;
        }

        $measurements = $this->weatherUtil->getWeatherForLocation($location);
        $io->writeln(sprintf('Location: %s, %s', $city, $countryCode));

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
