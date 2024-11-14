<?php

namespace App\Controller;

use App\Service\WeatherUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class WeatherApiController extends AbstractController
{
    private WeatherUtil $weatherUtil;

    public function __construct(WeatherUtil $weatherUtil)
    {
        $this->weatherUtil = $weatherUtil;
    }

    #[Route('/api/v1/weather', name: 'api_weather', methods: ['GET'])]
    public function getWeather(Request $request): Response
    {
        $country = $request->query->get('country');
        $city = $request->query->get('city');
        $format = $request->query->get('format', 'json'); // Domyślnie `json`
        $twig = filter_var($request->query->get('twig', 'true'), FILTER_VALIDATE_BOOLEAN); // Wartość true/false z zapytania

        $measurements = $this->weatherUtil->getWeatherForCountryAndCity($country, $city);

        // Obsługa renderowania za pomocą Twig
        if ($twig) {
            $template = $format === 'csv' ? 'weather_api/index.csv.twig' : 'weather_api/index.json.twig';
            return $this->render($template, [
                'city' => $city,
                'country' => $country,
                'measurements' => $measurements,
            ]);
        }

        // Obsługa odpowiedzi bez Twig (JSON, CSV)
        if ($format === 'json') {
            return $this->json([
                'country' => $country,
                'city' => $city,
                'measurements' => array_map(fn($m) => [
                    'date' => $m->getDate()->format('Y-m-d'),
                    'celsius' => $m->getCelsius(),
                    'fahrenheit' => $m->getFahrenheit(),
                ], $measurements),
            ]);
        } elseif ($format === 'csv') {
            $csvContent = "city,country,date,celsius,fahrenheit\n";
            foreach ($measurements as $measurement) {
                $csvContent .= sprintf(
                    "%s,%s,%s,%s,%s\n",
                    $city,
                    $country,
                    $measurement->getDate()->format('Y-m-d'),
                    $measurement->getCelsius(),
                    $measurement->getFahrenheit()
                );
            }

            return new Response($csvContent, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="weather.csv"',
            ]);
        }

        return new JsonResponse(['error' => 'Invalid format specified. Use "json" or "csv".'], 400);
    }
}
