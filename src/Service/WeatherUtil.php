<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Location;
use App\Entity\Measurement;
use Doctrine\ORM\EntityManagerInterface;

class WeatherUtil
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return Measurement[]
     */
    public function getWeatherForLocation(Location $location): array
    {
        $measurementRepository = $this->entityManager->getRepository(Measurement::class);

        return $measurementRepository->findBy(['location' => $location]);
    }

    /**
     * @return Measurement[]
     */
    public function getWeatherForCountryAndCity(string $countryCode, string $city): array
    {
        $locationRepository = $this->entityManager->getRepository(Location::class);

        $location = $locationRepository->findOneBy(['country' => $countryCode, 'city' => $city]);

        if ($location) {
            return $this->getWeatherForLocation($location);
        }

        return [];
    }
}
