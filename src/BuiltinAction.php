<?php declare(strict_types=1);

namespace Casoa\Yii\EditorPlus;

use Yii;
use yii\base\Model as BaseModel;
use yii\rest\Action;
use yii\web\UploadedFile;

class BuiltinAction extends Action
{
    /**
     * @var array $aliyunConfig
     * e.g. 'keyId', 'keySecret', 'endpoint', 'bucket', 'filedir', 'fileprefix'
     */
    public $aliyunConfig;
    /**
     * @var array $editorConfig
     */
    public $editorConfig = [];
    /**
     * @var array $editorConfig
     */
    public $editorConfigNot = [];

    private const ACTION_CONFIG = 'config';
    private const ACTION_UPLOADIMAGE = 'uploadimage';
    private const ACTION_UPLOADFILE = 'uploadfile';
    private const ACTION_UPLOADVIDEO = 'uploadvideo';
    private const ACTION_CATCHIMAGE = 'catchimage';
    private const ACTION_LISTIMAGE = 'listimage';
    private const ACTION_LISTFILE = 'listfile';

    private const CONFIG_URLPREFIX = 'https://assets-01-cdn.greekids.com/';

    /**
     * @var \Casoa\Yii\EditorPlus\InputModel $input 输入模型
     */
    private $input;

    public function run(): array
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        $this->input = new InputModel();
        $this->input->scenario = BaseModel::SCENARIO_DEFAULT;
        $this->input->attributes = Yii::$app->request->queryParams;

        if (!$this->input->validate()) {
            return ['err' => $this->input->errors];
        }

        if ($this->input->action === self::ACTION_CONFIG) {
            return $this->getConfig();
        }
        if ($this->input->action === self::ACTION_UPLOADIMAGE || $this->input->action === self::ACTION_CATCHIMAGE) {
            $model = new ImageModel();
            $model->imageFile = UploadedFile::getInstanceByName($this->getConfig()['imageFieldName']);
            return $model->save($this->aliyunConfig);
        }
        if ($this->input->action === self::ACTION_UPLOADFILE) {
            $model = new FileModel();
            $model->normalFile = UploadedFile::getInstanceByName($this->getConfig()['fileFieldName']);
            return $model->save($this->aliyunConfig);
        }

        return ['err' => '参数错误'];
    }

    private function getConfig(): array
    {
        return array_diff_key(array_merge([
            /* 本地上传图片 */
            'imageActionName' => self::ACTION_UPLOADIMAGE,
            'imageFieldName' => 'upfile',
            'imageMaxSize' => 1024 * 1024 * 8,
            'imageAllowFiles' => ['.png', '.jpg', '.jpeg', '.gif', '.bmp', '.webp'],
            'imageCompressEnable' => false,
            'imageCompressBorder' => 3840,
            'imageInsertAlign' => 'none',
            'imageUrlPrefix' => self::CONFIG_URLPREFIX,
            /* 抓取远程文件 */
            'catcherLocalDomain' => ['greekids.com'],
            'catcherActionName' => self::ACTION_CATCHIMAGE,
            'catcherFieldName' => 'source',
            'catcherUrlPrefix' => self::CONFIG_URLPREFIX,
            'catcherMaxSize' => 1024 * 1024 * 8,
            'catcherAllowFiles' => ['.png', '.jpg', '.jpeg', '.gif', '.bmp', '.webp'],
            /* 本地上传视频 */
            'videoActionName' => self::ACTION_UPLOADVIDEO,
            'videoFieldName' => 'upfile',
            'videoUrlPrefix' => self::CONFIG_URLPREFIX,
            'videoMaxSize' => 1024 * 1024 * 8,
            'videoAllowFiles' => ['3gp', 'asf', 'avi', 'dat', 'dv', 'flv', 'f4v', 'gif', 'm2t', 'm3u8', 'm4v', 'mj2', 'mjpeg', 'mkv', 'mov', 'mp4', 'mpe', 'mpg', 'mpeg', 'mts', 'ogg', 'qt', 'rm', 'rmvb', 'swf', 'ts', 'vob', 'wmv', 'webm'],
            /* 本地上传文件 */
            'fileActionName' => self::ACTION_UPLOADFILE,
            'fileFieldName' => 'upfile',
            'fileUrlPrefix' => self::CONFIG_URLPREFIX,
            'fileMaxSize' => 1024 * 1024 * 8,
            'fileAllowFiles' => ['.png', '.jpg', '.jpeg', '.gif', '.bmp', '.flv', '.swf', '.mkv', '.avi', '.rm', '.rmvb', '.mpeg', '.mpg', '.ogg', '.ogv', '.mov', '.wmv', '.mp4', '.webm', '.mp3', '.wav', '.mid', '.rar', '.zip', '.tar', '.gz', '.7z', '.bz2', '.cab', '.iso', '.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx', '.pdf', '.txt', '.md', '.xml'],
            /* 图片管理器 */
            'imageManagerActionName' => self::ACTION_LISTIMAGE,
            'imageManagerListSize' => 20,
            'imageManagerUrlPrefix' => self::CONFIG_URLPREFIX,
            'imageManagerInsertAlign' => 'none',
            'imageManagerAllowFiles' => ['.png', '.jpg', '.jpeg', '.gif', '.bmp', '.webp'],
            /* 文件管理器 */
            'fileManagerActionName' => self::ACTION_LISTFILE,
            'fileManagerUrlPrefix' => self::CONFIG_URLPREFIX,
            'fileManagerListSize' => 20,
            'fileManagerAllowFiles' => ['.png', '.jpg', '.jpeg', '.gif', '.bmp', '.flv', '.swf', '.mkv', '.avi', '.rm', '.rmvb', '.mpeg', '.mpg', '.ogg', '.ogv', '.mov', '.wmv', '.mp4', '.webm', '.mp3', '.wav', '.mid', '.rar', '.zip', '.tar', '.gz', '.7z', '.bz2', '.cab', '.iso', '.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx', '.pdf', '.txt', '.md', '.xml'],
        ], $this->editorConfig), $this->editorConfigNot);
    }
}
