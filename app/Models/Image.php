<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Elasticsearch\ClientBuilder;

class Image extends Model
{
    public static function getAllImages()
    {
        $hosts = [
            'https://elastic:10pQvoGxm8c7cLEcQkcDAVPk@a40bc65ea6d544069796f0f5935aed91.us-east-1.aws.found.io:9243'
        ];

        $es = ClientBuilder::create()
            ->setHosts($hosts)
            ->build();

        $params = [
            'index' => 'backoffice_api_images',
            'type'  => '_doc',
            'id'    => 1
        ];

        $response = $es->get($params);
        // $response = $this->es->get($params);

        return $response['_source'];
    }

    public static function getImage($id)
    {
        $hosts = [
            'https://elastic:10pQvoGxm8c7cLEcQkcDAVPk@a40bc65ea6d544069796f0f5935aed91.us-east-1.aws.found.io:9243'
        ];

        $es = ClientBuilder::create()
            ->setHosts($hosts)
            ->build();

        $params = [
            'index' => 'backoffice_api_images',
            'type'  => '_doc',
            'id'    => $id
        ];

        $response = $es->get($params);
        // $response = $this->es->get($params);

        return $response['_source'];
    }
}
