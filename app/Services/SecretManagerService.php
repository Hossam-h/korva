<?php

namespace App\Services;

use Aws\SecretsManager\SecretsManagerClient;
use Illuminate\Support\Facades\Cache;

class SecretManagerService
{
    public static function getSecret($secretName)
    {

        return Cache::remember('aws_secret_'.$secretName, 3600, function () use ($secretName) {

        $client = new SecretsManagerClient([
            'version' => 'latest',
            'region'  => env('AWS_DEFAULT_REGION'),
        ]);

        $result = $client->getSecretValue([
            'SecretId' => $secretName,
        ]);

        return json_decode($result['SecretString'], true);
        });
    }
}