<?php

namespace App\Controller;

use App\Entity\Test;
use App\Service\ConsommationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GetFileController extends AbstractController
{
    private $client;
    private $em;

    public function __construct(HttpClientInterface $client, EntityManagerInterface $em)
    {
        $this->client = $client;
        $this->em = $em;
    }

    /**
     * @Route("api/production", name="get_production")
     */
    public function getDataProduction()
    {        
        // $response = $this->client->request(
        //     'GET',
        //     'https://energie.tep.pf/production'
        // );

        $normalizer = [new ObjectNormalizer()];
        $encoders = [
            new CsvEncoder(),
            new XmlEncoder(),
            new YamlEncoder(),
        ];
        
        // Detect delimiter
        $delimiters = array( ',' => 0, ';' => 0, "\t" => 0, '|' => 0, );
        $delimiter = '';
        $firstLine = ''; 
        
        // $handle = fopen('https://127.0.0.1:8000/production', 'r');
        $handle = fopen("ftp://data@energie.tep.pf:XE2vxxaPc5SYSp4wewY4PEW6@66.70.177.29/production.csv", "r");
        
        if ($handle) { 
            $firstLine = fgets($handle);
            fclose($handle);
        } if ($firstLine) { 
            foreach ($delimiters as $delimiter => &$count) { 
                $count = count(str_getcsv($firstLine, $delimiter));
            } 
            $delimiter = array_search(max($delimiters), $delimiters);
        } else { 
            $delimiter = key($delimiters); 
        }

        $serializer = new Serializer($normalizer, $encoders);
        $content = file_get_contents("ftp://data@energie.tep.pf:XE2vxxaPc5SYSp4wewY4PEW6@66.70.177.29/production.csv"); // $content = '{"id":521583, "name":"symfony-docs", ...}'
        $data = $serializer->decode($content, 'csv', [CsvEncoder::DELIMITER_KEY => $delimiter ]); // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
        // dd($data);
        // $this->saveDataFromCsv($data);
        return new JsonResponse($data);
        // $statusCode = $response->getStatusCode(); // $statusCode = 200
        // $contentType = $response->getHeaders()['content-type'][0]; // $contentType = 'application/json'
        // // $content = $response->toArray(); // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
        // return new JsonResponse($content, $statusCode, array($contentType), true);
    }

    /**
     * @Route("api/consommation", name="get_consommation")
     */
    public function getDataConsommation(ConsommationService $conso)
    {
        return $conso->getData();
    }

    /**
     * Toujours valider avant d'enregistrer
     * $this->get('validator')->validate($data);
     * @Route("api/save-file", name="save_file", methods={"POST"})
     */
    public function saveDataFromCsv($content): void
    {
        // $content = $this->getDataInCsv();

        foreach ($content as $data) {
            $testeur = new Test();
            $testeur->setFirstname($data['first_name']);
            $testeur->setLastname($data['last_name']);
            $testeur->setEmail($data['email']);
            $testeur->setGender($data['gender']);
            $testeur->setIpadresse($data['ip_address']);

            $this->em->persist($testeur);
            $this->em->flush();
        }
    }

    /**
     * @Route("/api/send-data", name="send_data", methods={"GET"})
     */
    public function sendDataCsvFromDatabase(): Response
    {
        $data = $this->getDoctrine()->getRepository(Test::class)->findAll();
        $serializer = $this->container->get('serializer');
        $data = $serializer->serialize($data, 'json');
        $response = new Response($data, 200);
        $response->headers->set('Content-Type', 'application/json');
        // return new JsonResponse($data, Response::HTTP_OK, [], true);

        return $response;
    }
}
