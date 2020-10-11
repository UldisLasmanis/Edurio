<?php


namespace App\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiControllerTest extends WebTestCase
{
    public function testNonExistingTableNameJsonApi()
    {
        $client = static::createClient();
        $params = ['page_size' => 100, 'page' => 5];

        $client->request('GET', '/dbs/foo/tables/sometable/json', $params);

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testPageSizeParamRestrictionJsonApi()
    {
        $client = static::createClient();
        $paramsArray = [
            ['page_size' => -5, 'page' => 10, 'expected_response_code' => 422],
            ['page_size' => 0, 'page' => 50, 'expected_response_code' => 422],
            ['page_size' => 50005, 'page' => 5, 'expected_response_code' => 422],
            ['page_size' => 1, 'page' => 100, 'expected_response_code' => 200],
            ['page_size' => 50000, 'page' => 6, 'expected_response_code' => 200],
            ['page_size' => 360, 'page' => 1000, 'expected_response_code' => 200]
        ];

        foreach ($paramsArray as $params) {
            $getParams = ['page_size' => $params['page_size'], 'page' => $params['page']];
            $client->request('GET', '/dbs/foo/tables/source/json', $getParams);
            $this->assertEquals($params['expected_response_code'], $client->getResponse()->getStatusCode());
        }
    }

    public function testPageParamRestrictionJsonApi()
    {
        $client = static::createClient();
        $paramsArray = [
            ['page_size' => 100, 'page' => 50000, 'expected_response_code' => 404],
            ['page_size' => 50000, 'page' => 22, 'expected_response_code' => 404],
            ['page_size' => 10, 'page' => 5, 'expected_response_code' => 200],
        ];

        foreach ($paramsArray as $params) {
            $getParams = ['page_size' => $params['page_size'], 'page' => $params['page']];
            $client->request('GET', '/dbs/foo/tables/source/json', $getParams);
            $this->assertEquals($params['expected_response_code'], $client->getResponse()->getStatusCode());
        }
    }

    public function testGetParamPassingJsonApi()
    {
        $client = static::createClient();
        $paramsArray = [
            ['page_size' => null, 'page' => 100, 'expected_response_code' => 400],
            ['page_size' => 5, 'page' => null, 'expected_response_code' => 400],
            ['page_size' => 10, 'page' => 5, 'expected_response_code' => 200],
        ];

        foreach ($paramsArray as $params) {
            $getParams = ['page_size' => $params['page_size'], 'page' => $params['page']];
            $client->request('GET', '/dbs/foo/tables/source/json', $getParams);
            $this->assertEquals($params['expected_response_code'], $client->getResponse()->getStatusCode());
        }
    }

    public function testJsonFormatJsonApi()
    {
        $client = static::createClient();
        $params = ['page_size' => 100, 'page' => 5];
        $client->request('GET', '/dbs/foo/tables/source/json', $params);

        $responseContent = $client->getResponse()->getContent();
        json_decode($responseContent);

        $this->assertEquals(true, json_last_error() == JSON_ERROR_NONE);
    }

    public function testResponseObjectCountJsonApi()
    {
        $client = static::createClient();
        $paramsArray = [
            ['page_size' => 1, 'page' => 1000, 'expected_count' => 1],
            ['page_size' => 100, 'page' => 1, 'expected_count' => 100],
            ['page_size' => 30, 'page' => 33334, 'expected_count' => 10]
        ];

        foreach ($paramsArray as $params) {
            $getParams = ['page_size' => $params['page_size'], 'page' => $params['page']];
            $client->request('GET', '/dbs/foo/tables/source/json', $getParams);

            $receivedObjectCount = count(json_decode($client->getResponse()->getContent()));

            $this->assertEquals($receivedObjectCount, $params['expected_count']);
        }
    }
}