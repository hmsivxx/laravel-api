<?php

namespace Database\Elasticsearch;

use Elasticsearch\ClientBuilder;

class ElasticSearchHelper
{
    static $es;

    public function __construct()
    {
        static::$es = ClientBuilder::fromConfig([
            'hosts' => [
                'https://elastic:10pQvoGxm8c7cLEcQkcDAVPk@a40bc65ea6d544069796f0f5935aed91.us-east-1.aws.found.io:9243'
            ]
        ]);
    }
}
