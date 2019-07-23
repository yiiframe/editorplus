<?php declare(strict_types=1);

namespace Casoa\Yii\EditorPlus;

use OSS\Core\OssException;
use Yii;
use OSS\OssClient;
use yii\base\Model;
use yii\helpers\StringHelper;

class ImageModel extends Model
{
    /**
     * @var \yii\web\UploadedFile
     */
    public $imageFile;

    public function rules(): array
    {
        return [
            [
                ['imageFile'],
                'file',
                'skipOnEmpty' => false,
                'minSize' => 1024, // 1k
                'maxSize' => 1024 * 1024 * 8, // 8M
                'extensions' => [
                    'png',
                    'jpg',
                    'jpeg',
                    'gif',
                    'bmp',
                    'webp',
                ],
            ],
        ];
    }

    /**
     * @param array $aliyunConfig
     * @return array
     */
    public function save(array $aliyunConfig): array
    {
        if ($this->validate()) {
            if (!StringHelper::endsWith($aliyunConfig['filedir'], '/')) {
                $aliyunConfig['filedir'] .= '/';
            }

            $filename = implode('', [
                $aliyunConfig['fileprefix'] ?? '',
                md5_file($this->imageFile->tempName),
                '.' . $this->imageFile->extension,
            ]);
            $runtimefile = Yii::getAlias('@runtime/tmpfiles_' . $filename);

            if (!$this->imageFile->saveAs($runtimefile)) {
                return ['err' => '图片暂存失败'];
            }

            try {
                $client = new OssClient($aliyunConfig['keyId'], $aliyunConfig['keySecret'], $aliyunConfig['endpoint']);
            } catch (OssException $e) {
                unlink($runtimefile);
                return ['err' => $e->getMessage()];
            }

            $client->putObject($aliyunConfig['bucket'], $aliyunConfig['filedir'] . $filename, $runtimefile);

            if (!$client->doesObjectExist($aliyunConfig['bucket'], $aliyunConfig['filedir'] . $filename)) {
                unlink($runtimefile);
                return ['err' => '图片转存失败'];
            }

            unlink($runtimefile);
            return [
                'endpoint' => $aliyunConfig['endpoint'],
                'bucket' => $aliyunConfig['bucket'],
                'filepath' => $aliyunConfig['filedir'] . $filename,
            ];
        }

        return ['err' => $this->errors];
    }
}
