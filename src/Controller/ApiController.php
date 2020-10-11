<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends AbstractController
{

    public function test(): Response
    {
        return new JsonResponse('xss');
    }

    public function add(): Response
    {
        set_time_limit(60);
        /** @var Connection $conn */
        $conn = $this->getDoctrine()->getConnection();

        //dont commit on-the-fly, only when all INSERT queries are executed
        $conn->executeQuery('SET autocommit=0');

        $insertIntoString = 'INSERT INTO source(`a`, `b`, `c`) VALUES';
        $insertValuesString = "";
        for ($i = 1; $i <= 1000000; ++$i) {

            $valueArr = [
                $i,
                $i % 3,
                $i % 5
            ];
            $insertValuesString .= '(' . implode(',', $valueArr) . '),';

            if ($i % 10000 === 0) {
                $query = $insertIntoString . rtrim($insertValuesString, ',');
                $conn->executeQuery($query);
                $insertValuesString = "";
            }
        }

        $conn->executeQuery('COMMIT');

        return new JsonResponse('1M records added in table `source`');
    }
}