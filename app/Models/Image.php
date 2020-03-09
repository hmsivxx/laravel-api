<?php

namespace App\Models;

use Elasticsearch\ClientBuilder;
use Illuminate\Database\Eloquent\Model;
use JsonSchema\Validator;
use App\Util\UtilTrait;

class Image extends Model
{
    use UtilTrait;
    protected $es;

    public function __construct()
    {
        $hosts = [
            'https://elastic:10pQvoGxm8c7cLEcQkcDAVPk@a40bc65ea6d544069796f0f5935aed91.us-east-1.aws.found.io:9243'
        ];

        $this->es = ClientBuilder::create()
            ->setHosts($hosts)
            ->build();
    }

    public function getAllImages($queryParams)
    {
        if (@$queryParams['from']) {
            $from = $queryParams['from'];
        } else {
            $from = 0;
        }

        if (@$queryParams['size']) {
            $size = $queryParams['size'];

            if ($size > 50) {
                $size = 50;
            }
        } else {
            $size = 50;
        }

        $params = [
            'index' => "backoffice_api_images",
            'type' => '_doc',
            'from' => $from,
            'size' => $size,
            'body' => [
                'size' => 100,
                'query' => [
                    'match_all' => (object) []
                ],
                'sort' => [
                    'created' => 'desc'
                ]
            ]
        ];

        $result = [
            'data' => [],
            'info' => [
                'offset'         => (int) $params['from'],
                'limit'          => (int) $params['size'],
                'totalItemsCount' => null
            ]
        ];

        try {
            $response = $this->es->search($params);
            foreach ($response['hits']['hits'] as $item) {
                array_push($result['data'], $item['_source']);
            }
            $result['info']['totalItemsCount'] = $response['hits']['total']['value'];
            return $result;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getImagesFiltered($queryParams)
    {
        $result = [];
        $query = [
            'index' => 'backoffice_api_images',
            'type' => '_doc',
            'client' => ['ignore' => 404],
            'body' => [
                'sort' => [
                    'created' => 'desc'
                ],
                'query' => [
                    'bool' => [
                        'should' => []
                    ]
                ],
                'sort' => [
                    'created' => 'desc'
                ]
            ]
        ];

        $builder = [
            'bool' => [
                'must' => []
            ]
        ];

        foreach ($queryParams as $k => $item) {
            if ($item) {
                $temp = [
                    'term' => [$k => $item]
                ];
                array_push($builder['bool']['must'], $temp);
            }
        }

        $query['body']['query']['bool']['should'] += $builder;

        try {
            $response = $this->es->search($query);

            foreach ($response['hits']['hits'] as $item) {
                array_push($result, $item['_source']);
            }
            return $result;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    public function getImage($id)
    {

        $params = [
            'index'  => 'backoffice_api_images',
            'type'   => '_doc',
            'id'     => $id,
            'client' => ['ignore' => 404]
        ];

        try {

            $response = $this->es->get($params);
            return $response['_source'];
        } catch (\Exception $e) {

            return json_decode($e->getMessage());
        }
    }

    public function addImage($payload)
    {
        $id = uniqid();
        $timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time() - 10800);

        if (@$payload['id']) {
            unset($payload['id']);
        }

        if (@$payload['created']) {
            unset($payload['created']);
        }

        if (@$payload['removed']) {
            unset($payload['removed']);
        }

        if (@$payload['updated']) {
            unset($payload['updated']);
        }

        $params = [
            'index' => 'backoffice_api_images',
            'type'  => '_doc',
            'id'    => $id,
            'client' => ['ignore' => 400],
            'body'  => $payload
        ];

        $params['body']['id'] = $id;
        $params['body']['created'] = $timestamp;

        $imagesCount = count($params['body']['images']);
        for ($i = 0; $i < $imagesCount; $i++) {
            $params['body']['images'][$i]['id'] = uniqid();
        }

        $validatePayload = $this->arrayToObject($params['body']);

        // Validate
        $validator = new Validator;
        $validator->validate($validatePayload, (object) ['$ref' => 'file://' . __DIR__ . '/../../database/schemas/images.json']);

        if ($validator->isValid()) {
            try {
                $response = $this->es->index($params);
                if (@$response['error']) {
                    return $response;
                } else {
                    return $params['body'];
                }
            } catch (\Exception $e) {

                return $e->getMessage();
            }
        } else {
            $arr = [
                'error' => [
                    'message' => "Wrong payload, please verify",
                    'reason' => []
                ]
            ];

            foreach ($validator->getErrors() as $error) {
                array_push($arr['error']['reason'], $error);
            }

            return $arr;
        }
    }

    public function deleteImage($id)
    {
        $params = [
            'index' => 'backoffice_api_images',
            'type'  => '_doc',
            'id'    => $id,
            'client' => ['ignore' => 404]
        ];

        try {

            $response = $this->es->delete($params);
            return $response;
        } catch (\Exception $e) {

            return $e->getMessage();
        }
    }

    public function updateImage($payload, $id)
    {

        //add ID to new images
        if (@$payload['images']) {
            foreach ($payload['images'] as $k => $item) {
                if (!@$item['id']) {
                    $payload['images'][$k]['id'] = uniqid();
                }
            }
        }

        $validatePayload = $this->arrayToObject($payload);


        // Validate
        $validator = new Validator;
        $validator->validate($validatePayload, (object) ['$ref' => 'file://' . __DIR__ . '/../../database/schemas/put-image.json']);

        if ($validator->isValid()) {
            try {

                $timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time() - 10800);

                if (@$payload['created']) {
                    unset($payload['created']);
                }
                if (@$payload['removed']) {
                    unset($payload['removed']);
                }

                $params = [
                    'index' => 'backoffice_api_images',
                    'type'  => '_doc',
                    'id'    => $id,
                    'client' => ['ignore' => 400],
                    'body'  => [
                        'doc' => $payload
                    ]
                ];

                $params['body']['doc']['updated'] = $timestamp;


                $response = $this->es->update($params);
                return $response;
            } catch (\Exception $e) {

                return $e->getMessage();
            }
        } else {
            $arr = [
                'error' => [
                    'message' => "Wrong payload, please verify",
                    'reason' => $validator->getErrors()
                ]
            ];

            return $arr;
        }
    }
}
