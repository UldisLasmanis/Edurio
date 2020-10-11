<?php

namespace App\Controller;

use App\Repository\SourceRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

    /**
     * @Route("/dbs/foo/tables/{tableName}/csv", name="csv_api", methods={"GET"})
     * @param string $tableName
     * @return Response
     */
    public function csvApi(string $tableName): Response
    {
        ini_set('memory_limit', '512M');
        if (false === isset($tableName)) {
            return new JsonResponse('Missing table name definition in url path', 404);
        }

        $tableExists = $this->checkIfTableExists($tableName);
        if (false === $tableExists) {
            return new JsonResponse('Table `' . $tableName . '` does not exist!', 404);
        }

        /** @var JsonResponse $response */
        $response = new JsonResponse();

        /** @var Connection $conn */
        $conn = $this->getDoctrine()->getConnection();

        $query = "SELECT * FROM source ORDER BY a ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $records = $stmt->fetchAllNumeric();
        if (true === empty($records)) {
            $response->setData('No data');
            $response->setStatusCode(404);
            return $response;
        }

        $response->setProtocolVersion('1.1');
        //Transfer-Encoding: chunked is already added by Apache if response is > 4096 bytes
        $response->headers->set('Content-Encoding', 'chunked');
        $response->sendHeaders();

        // open the "output" stream
        $f = fopen('php://output', 'w');

        foreach ($records as $line) {
            fputcsv($f, $line);
        }
        fclose($f);

        return $response;
    }

    /**
     * @Route("/dbs/foo/tables/{tableName}/json", name="json_api", methods={"GET"})
     * @param Request $request
     * @param string $tableName
     * @return Response
     */
    public function jsonApi(Request $request, string $tableName): Response
    {
        $tableExists = $this->checkIfTableExists($tableName);
        if (false === $tableExists) {
            return new JsonResponse('Table `' . $tableName . '` does not exist!', 404);
        }

        $itemsPerPage = $request->query->get('page_size');
        $pageNr = $request->query->get('page');

        if (false === isset($itemsPerPage)) {
            return new JsonResponse('Missing `page_size` parameter', 400);
        }
        if ($itemsPerPage <= 0 || $itemsPerPage > 50000) {
            return new JsonResponse('Parameter `page_size` value should be between 1 and 50000', 422);
        }

        if (false === isset($pageNr)) {
            return new JsonResponse('Missing `page` parameter', 400);
        }

        $offset = ($itemsPerPage * $pageNr) - $itemsPerPage;
        $limit = $itemsPerPage;

        /** @var SourceRepository $sourceRepository */
        $sourceRepository = $this->getDoctrine()->getRepository('App:Source');

        $records = $sourceRepository->createQueryBuilder('s')
            ->orderBy('s.a', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        if (true === empty($records)) {
            return new JsonResponse('No data', 404);
        }

        return new JsonResponse($records);
    }

    public function checkIfTableExists(string $tableName): bool
    {
        /** @var AbstractSchemaManager $schemaManager */
        $schemaManager = $this->getDoctrine()->getConnection()->getSchemaManager();
        $tableExists = $schemaManager->tablesExist($tableName);

        return $tableExists;
    }
}