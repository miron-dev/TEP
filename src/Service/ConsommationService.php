<?php

namespace App\Service;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ConsommationService
{
    public function getData()
    {
        $normalizer = [new ObjectNormalizer()];
        $encoders = [
            new CsvEncoder(),
            new XmlEncoder(),
            new YamlEncoder(),
        ];
        
        $delimiters = array( ',' => 0, ';' => 0, "\t" => 0, '|' => 0, );
        $delimiter = '';
        $firstLine = ''; 
        
        $ftp = "ftp://".$_ENV["ACCESS_CSV"]."/consommation.csv";
        $handle = fopen($ftp, "r");
        
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
        $content = file_get_contents($ftp);
        $data = $serializer->decode($content, 'csv', [CsvEncoder::DELIMITER_KEY => $delimiter ]);

        return new JsonResponse($data);
    }
}