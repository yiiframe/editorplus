<?php declare(strict_types=1);

namespace Casoa\Yii\EditorPlus;

use OSS\Core\OssException;
use Yii;
use OSS\OssClient;
use yii\base\Model;
use yii\helpers\StringHelper;

class FileModel extends Model
{
    /**
     * @var \yii\web\UploadedFile
     */
    public $normalFile;

    public function rules(): array
    {
        return [
            [
                ['normalFile'],
                'file',
                'skipOnEmpty' => false,
                'minSize' => 1024, // 1k
                'maxSize' => 1024 * 1024 * 8, // 8M
                'extensions' => ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'webp', 'flv', 'swf', 'mkv', 'avi', 'rmvb', 'mpeg', 'mpg', 'ogg', 'mov', 'wmv', 'mp4', 'mp3', 'wav', 'mid', 'rar', 'zip', 'tar', 'gz', '7z', 'bz2', 'cab', 'iso', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'txt', 'md', 'xml'],
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
                md5_file($this->normalFile->tempName),
                '.' . $this->normalFile->extension,
            ]);
            $runtimefile = Yii::getAlias('@runtime/tmpfiles_' . $filename);

            if (!$this->normalFile->saveAs($runtimefile)) {
                return ['state' => '文件暂存失败'];
            }

            try {
                $client = new OssClient($aliyunConfig['keyId'], $aliyunConfig['keySecret'], $aliyunConfig['endpoint']);
            } catch (OssException $e) {
                unlink($runtimefile);
                return ['state' => $e->getMessage()];
            }

            try {
                $client->uploadFile($aliyunConfig['bucket'], $aliyunConfig['filedir'] . $filename, $runtimefile);
            } catch (OssException $e) {
                unlink($runtimefile);
                return ['state' => $e->getMessage()];
            }

            if (!$client->doesObjectExist($aliyunConfig['bucket'], $aliyunConfig['filedir'] . $filename)) {
                unlink($runtimefile);
                return ['state' => '文件转存失败'];
            }

            unlink($runtimefile);
            return [
                'endpoint' => $aliyunConfig['endpoint'],
                'bucket' => $aliyunConfig['bucket'],
                'filepath' => $aliyunConfig['filedir'] . $filename,

                'state' => 'SUCCESS',
                'url' => $aliyunConfig['filedir'] . $filename,
                'title' => $filename,
                'original' => $filename,
                'type' => '.' . $this->normalFile->extension,
                'size' => $this->normalFile->size,
            ];
        }

        return ['state' => array_values($this->firstErrors)[0]];
    }
}
