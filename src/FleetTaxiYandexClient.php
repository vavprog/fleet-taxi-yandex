<?php

namespace Vavprog\FleetTaxiYandex;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\RequestOptions;
use phpDocumentor\Reflection\Types\Self_;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class FleetTaxiYandexClient
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    protected $baseUrl = 'https://fleet-api.taxi.yandex.net';

    protected $client_id;

    protected $api_key;

    protected $httpClient;

    protected $httpOptions = [];

    public function __construct()
    {
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
        ]);
        $this->config = config('fleet-taxi-yandex');

        foreach ($this->config['fleet_taxi_yandex_conf'] as $name => $value) {
            $this->$name = $value;
        }

    }

    private function query($url, array $params = [], $method = self::METHOD_POST)
    {
        if (empty($params['query'])) {
            throw new RuntimeException('Empty request');
        }
        try {
            $response = $this->httpClient->request($method, $url,[
                RequestOptions::JSON =>$params,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept'     => 'application/json',
                    'X-Api-Key' => $this->api_key,
                    'X-Client-ID'     => 'taxi/park/'.$this->client_id,
                ]
            ]);
        } catch (GuzzleException $guzzleException) {
            throw new RuntimeException('Http exception: ' . $guzzleException->getMessage());
        } catch (Exception $exception) {
            throw new RuntimeException('Exception: ' . $exception->getMessage());
        }

        switch ($response->getStatusCode()) {
            case 200:
                // успешный запрос
                $stream = Psr7\stream_for($response->getBody());
                $result = json_decode($stream->getContents());

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new RuntimeException('Error parsing response: ' . json_last_error_msg());
                }
                return $result;
                break;
            case 400:
                //некорректные параметры запроса
                throw new RuntimeException('Incorrect request');
                break;
            case 401:
                // отсутствуют параметры авторизации запроса
                throw new RuntimeException('Missing API key');
                break;
            case 403:
                // недостаточно прав для выполнения запроса
                throw new RuntimeException('Incorrect API key');
                break;
            case 500:
                // Произошла внутренняя ошибка сервиса во время обработки
                throw new RuntimeException('Server internal error');
                break;
            default:
                throw new RuntimeException('Unexpected error');
        }
    }

    public function getDriverProfiles($params=[])
    {
        $url = '/v1/parks/driver-profiles/list';
        return $this->query($url, $params);
    }

    public function getDriverWorkRules($park_id = null)
    {
        if(!$park_id){
            throw new RuntimeException('Empty request');
        }
        $params = ['query'=>['park_id' => $park_id]];
        $url = '/v1/parks/driver-work-rules?park_id='.$park_id;
        return $this->query($url, $params, Self::METHOD_GET);
    }

    public function getCarsList($params=[])
    {
        $url = '/v1/parks/cars/list';
        return $this->query($url, $params);
    }

    public function getOrdersList($params=[])
    {
        $url = '/v1/parks/orders/list';
        return $this->query($url, $params);
    }

    public function getTransactionCategories($params)
    {
        $url = '/v2/parks/transactions/categories/list';
        return $this->query($url, $params);
    }

    public function getDriverTransactions($params)
    {
        $url = '/v2/parks/driver-profiles/transactions/list';
        return $this->query($url, $params);
    }

    public function getParkTransactions($params)
    {
        $url = '/v2/parks/transactions/list';
        return $this->query($url, $params);
    }
    
    public function getOrderTransactions($params)
    {
        $url = '/v2/parks/orders/transactions/list';
        return $this->query($url, $params);
    }
}