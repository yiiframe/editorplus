<?php declare(strict_types=1);

namespace Casoa\Yii\EditorPlus;

use Yii;
use yii\base\BaseObject;
use yii\base\Model as BaseModel;
use yii\web\UploadedFile;

class Controller extends BaseObject
{
    /**
     * @var array $aliyunConfig
     * keyId、keySecret、endpoint、bucket、filedir
     */
    public $aliyunConfig;

    private const ACTION_CONFIG = 'config';
    private const ACTION_UPLOAD_IMAGE = 'uploadimage';
//    private const ACTION_UPLOAD_VIDEO = 'uploadvideo';
    private const ACTION_UPLOAD_FILE = 'uploadfile';
//    private const ACTION_LIST_IMAGE = 'listimage';
//    private const ACTION_LIST_FILE = 'listfile';
//    private const ACTION_CATCH_IMAGE = 'catchimage';

    private const FILETYPE_IMAGE = 'image';
    private const FILETYPE_FILE = 'file';

    /**
     * @var \Casoa\Yii\EditorPlus\InputModel $input 输入模型
     */
    private $input;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->input = new InputModel();
        $this->input->scenario = BaseModel::SCENARIO_DEFAULT;
        $this->input->attributes = Yii::$app->request->queryParams;
    }

    public function process(): array
    {
        if (!$this->input->validate()) {
            return ['err' => $this->input->errors];
        }

        if ($this->input === self::ACTION_CONFIG) {
            return $this->getConfig();
        }
        if ($this->input === self::ACTION_UPLOAD_IMAGE) {
            $this->createFile(self::FILETYPE_IMAGE);
        }
        if ($this->input === self::ACTION_UPLOAD_FILE) {
            $this->createFile(self::FILETYPE_FILE);
        }

        return ['err' => '参数错误'];
    }

    /**
     * @param string $fileType
     * @return array
     */
    private function createFile(string $fileType): array
    {
        $model = new ImageModel();
        $model->imageFile = UploadedFile::getInstance($model, [
            self::FILETYPE_IMAGE => $this->getConfig()['imageFieldName'],
            self::FILETYPE_FILE => $this->getConfig()['fileFieldName'],
        ][$fileType]);

        return $model->save($this->aliyunConfig);
    }

    private function getConfig(): array
    {
        return [
            'imageActionName' => 'uploadimage',
            'imageFieldName' => 'upfile',
            'imageMaxSize' => 2048000,
            'imageAllowFiles' => [
                '.png',
                '.jpg',
                '.jpeg',
                '.gif',
                '.bmp',
                '.webp'
            ],
            'imageCompressEnable' => true,
            'imageCompressBorder' => 1600,
            'imageInsertAlign' => 'none',
            'imageUrlPrefix' => '',
            'imagePathFormat' => '/ueditor/php/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}',
            'scrawlActionName' => 'uploadscrawl',
            'scrawlFieldName' => 'upfile',
            'scrawlPathFormat' => '/ueditor/php/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}',
            'scrawlMaxSize' => 2048000,
            'scrawlUrlPrefix' => '',
            'scrawlInsertAlign' => 'none',
            'snapscreenActionName' => 'uploadimage',
            'snapscreenPathFormat' => '/ueditor/php/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}',
            'snapscreenUrlPrefix' => '',
            'snapscreenInsertAlign' => 'none',
            'catcherLocalDomain' => [
                '127.0.0.1',
                'localhost',
                'img.baidu.com'
            ],
            'catcherActionName' => 'catchimage',
            'catcherFieldName' => 'source',
            'catcherPathFormat' => '/ueditor/php/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}',
            'catcherUrlPrefix' => '',
            'catcherMaxSize' => 2048000,
            'catcherAllowFiles' => [
                '.png',
                '.jpg',
                '.jpeg',
                '.gif',
                '.bmp'
            ],
            'videoActionName' => 'uploadvideo',
            'videoFieldName' => 'upfile',
            'videoPathFormat' => '/ueditor/php/upload/video/{yyyy}{mm}{dd}/{time}{rand:6}',
            'videoUrlPrefix' => '',
            'videoMaxSize' => 102400000,
            'videoAllowFiles' => [
                '.flv',
                '.swf',
                '.mkv',
                '.avi',
                '.rm',
                '.rmvb',
                '.mpeg',
                '.mpg',
                '.ogg',
                '.ogv',
                '.mov',
                '.wmv',
                '.mp4',
                '.webm',
                '.mp3',
                '.wav',
                '.mid'
            ],
            'fileActionName' => 'uploadfile',
            'fileFieldName' => 'upfile',
            'filePathFormat' => '/ueditor/php/upload/file/{yyyy}{mm}{dd}/{time}{rand:6}',
            'fileUrlPrefix' => '',
            'fileMaxSize' => 51200000,
            'fileAllowFiles' => [
                '.png',
                '.jpg',
                '.jpeg',
                '.gif',
                '.bmp',
                '.flv',
                '.swf',
                '.mkv',
                '.avi',
                '.rm',
                '.rmvb',
                '.mpeg',
                '.mpg',
                '.ogg',
                '.ogv',
                '.mov',
                '.wmv',
                '.mp4',
                '.webm',
                '.mp3',
                '.wav',
                '.mid',
                '.rar',
                '.zip',
                '.tar',
                '.gz',
                '.7z',
                '.bz2',
                '.cab',
                '.iso',
                '.doc',
                '.docx',
                '.xls',
                '.xlsx',
                '.ppt',
                '.pptx',
                '.pdf',
                '.txt',
                '.md',
                '.xml'
            ],
            'imageManagerActionName' => 'listimage',
            'imageManagerListPath' => '/ueditor/php/upload/image/',
            'imageManagerListSize' => 20,
            'imageManagerUrlPrefix' => '',
            'imageManagerInsertAlign' => 'none',
            'imageManagerAllowFiles' => [
                '.png',
                '.jpg',
                '.jpeg',
                '.gif',
                '.bmp'
            ],
            'fileManagerActionName' => 'listfile',
            'fileManagerListPath' => '/ueditor/php/upload/file/',
            'fileManagerUrlPrefix' => '',
            'fileManagerListSize' => 20,
            'fileManagerAllowFiles' => [
                '.png',
                '.jpg',
                '.jpeg',
                '.gif',
                '.bmp',
                '.flv',
                '.swf',
                '.mkv',
                '.avi',
                '.rm',
                '.rmvb',
                '.mpeg',
                '.mpg',
                '.ogg',
                '.ogv',
                '.mov',
                '.wmv',
                '.mp4',
                '.webm',
                '.mp3',
                '.wav',
                '.mid',
                '.rar',
                '.zip',
                '.tar',
                '.gz',
                '.7z',
                '.bz2',
                '.cab',
                '.iso',
                '.doc',
                '.docx',
                '.xls',
                '.xlsx',
                '.ppt',
                '.pptx',
                '.pdf',
                '.txt',
                '.md',
                '.xml'
            ]
        ];
    }
}
