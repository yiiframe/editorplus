<?php declare(strict_types=1);

namespace Casoa\Yii\EditorPlus;

use OSS\Core\OssException;
use OSS\OssClient;
use yii\helpers\StringHelper;

class Util
{
    public static function normalizePath(string $path): string
    {
        if (!StringHelper::endsWith($path, '/')) {
            $path .= '/';
        }

        return $path;
    }

    public static function sendFile2Cloud(array $aliyunConfig, string $localfile, string $cloudfilename): array
    {
        $aliyunConfig['filedir'] = self::normalizePath($aliyunConfig['filedir']);

        try {
            $client = new OssClient($aliyunConfig['keyId'], $aliyunConfig['keySecret'], $aliyunConfig['endpoint']);
        } catch (OssException $e) {
            unlink($localfile);
            return ['state' => $e->getMessage()];
        }

        try {
            $client->uploadFile($aliyunConfig['bucket'], $aliyunConfig['filedir'] . $cloudfilename, $localfile);
        } catch (OssException $e) {
            unlink($localfile);
            return ['state' => $e->getMessage()];
        }

        if (!$client->doesObjectExist($aliyunConfig['bucket'], $aliyunConfig['filedir'] . $cloudfilename)) {
            unlink($localfile);
            return ['state' => '文件转存失败'];
        }

        $_filext = '.' . strtolower(pathinfo($localfile, PATHINFO_EXTENSION));
        $_filesize = filesize($localfile);

        unlink($localfile);
        return [
            'endpoint' => $aliyunConfig['endpoint'],
            'bucket' => $aliyunConfig['bucket'],
            'filepath' => $aliyunConfig['filedir'] . $cloudfilename,

            'state' => 'SUCCESS',
            'url' => $aliyunConfig['filedir'] . $cloudfilename,
            'title' => $cloudfilename,
            'original' => $cloudfilename,
            'type' => $_filext,
            'size' => $_filesize,
        ];
    }
}
