<?php

namespace App\Controller;


use App\Entity\Location;
use App\Repository\MeasurementRepository;
use App\Service\WeatherUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WeatherController extends AbstractController
{
    #[Route('/weather/{city}/{country?}', name: 'app_weather_by_city')]
    public function city(
        #[MapEntity(mapping: ['city' => 'name', 'country' => 'country'])]
        Location $location,
        WeatherUtil $util,
    ): Response {

        if (!$location) {
            throw new NotFoundHttpException('Location not found.');
        }


        $measurements = $util->getWeatherForLocation($location);


        return $this->render('weather/city.html.twig', [
            'location' => $location,
            'measurements' => $measurements,
        ]);
    }
}
