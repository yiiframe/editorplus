<?php declare(strict_types=1);

namespace Casoa\Yii\EditorPlus;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use yii\httpclient\Exception as HttpException;

class CatchImageModel extends Model
{
    /**
     * @var string
     */
    public $imageUrl;

    public function rules(): array
    {
        return [
            [
                ['imageUrl'], 'url', 'skipOnEmpty' => false,
            ],
        ];
    }

    public function save(array $aliyunConfig): array
    {
        if (!$this->validate()) {
            return ['state' => array_values($this->firstErrors)[0]];
        }

        $headers = get_headers($this->imageUrl, 1);
        if (!isset($headers['Content-Type'])) {
            return ['state' => '无法获取文件类型'];
        }

        if ($headers['Content-Type'] === 'image/jpeg') {
            $extension = 'jpg';
        } else if ($headers['Content-Type'] === 'image/bmp') {
            $extension = 'bmp';
        } else if ($headers['Content-Type'] === 'image/gif') {
            $extension = 'gif';
        } else if ($headers['Content-Type'] === 'image/png') {
            $extension = 'png';
        } else {
            return ['state' => '不支持的图片格式'];
        }

        $filename = implode('', [
            $aliyunConfig['fileprefix'] ?? '',
            md5($this->imageUrl),
            '.' . $extension,
        ]);
        $localfile = Yii::getAlias('@runtime/tmpfiles_' . $filename);

        $fh = fopen($localfile, 'wb');
        $client = new Client(['transport' => CurlTransport::class]);
        try {
            $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl($this->imageUrl)
                ->setOutputFile($fh)
                ->send();
        } catch (InvalidConfigException $e) {
            fclose($fh);
            return ['state' => $e->getMessage()];
        } catch (HttpException $e) {
            fclose($fh);
            return ['state' => $e->getMessage()];
        }

        fclose($fh);

        if (!$response->isOk) {
            return ['state' => '发生未知错误'];
        }

        return Util::sendFile2Cloud($aliyunConfig, $localfile, $filename);
    }
}
