<?php declare(strict_types=1);

namespace Casoa\Yii\EditorPlus;

use Casoa\Yii\Utility\ModelRuleUtility;
use yii\base\Model;

class InputModel extends Model
{
    /**
     * @var string
     */
    public $action;

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            'action' => '动作',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        return [
            'default' => ['action'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [
                'action',
                'filter',
                'filter' => static function ($value): ?string {
                    return ModelRuleUtility::filterString($value);
                },
            ],
            [
                'action',
                'required',
                'message' => '{attribute}不能为空',
            ],
        ]);
    }
}
