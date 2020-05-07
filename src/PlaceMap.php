<?php

namespace iAvatar777\widgets\PlaceMapYandex;

use common\services\Security;
use cs\Application;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\widgets\InputWidget;
use yii\web\UploadedFile;
use yii\web\JsExpression;
use yii\imagine\Image;
use Imagine\Image\ManipulatorInterface;
use cs\base\BaseForm;

/**
 *
 * Использование:
 *
 * ```php
 * $form->input($model, 'name')->widget(PlaceMap::className(), $options)
 * ```
 */
class PlaceMap extends InputWidget
{
    private $fieldId;
    private $fieldName;

    private $fieldIdMap;

    private $fieldIdLat;
    private $fieldNameLat;

    private $fieldIdLng;
    private $fieldNameLng;

    public $style = [
        'input'  => ['class' => 'form-control'],
        'divMap' => [],
    ];

    /** @var string функция которая будет срабатывать после установки метки function(pos) {} */
    public $callback;

    /**
     * Initializes the widget.
     */
    public function init()
    {
        parent::init();

        $formName = $this->model->formName();
        $this->getId();

        $this->fieldId = strtolower($formName . '-' . $this->attribute);
        $this->fieldName = $formName . '[' . $this->attribute . ']';

        $this->fieldIdMap = strtolower($formName . '-' . $this->attribute . '-map');

        $this->fieldIdLng = strtolower($formName . '-' . $this->attribute . '-lng');
        $this->fieldNameLng = $formName . '[' . $this->attribute . '-lng' . ']';

        $this->fieldIdLat = strtolower($formName . '-' . $this->attribute . '-lat');
        $this->fieldNameLat = $formName . '[' . $this->attribute . '-lat' . ']';
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        $this->registerClientScript();
        $html = [];

        if ($this->hasModel()) {
            $lng = null;
            $lat = null;
            $attribute = $this->attribute;
            $place = '';
            if ($this->model->$attribute) {
                $lat = ArrayHelper::getValue($this->model->$attribute, 'lat');
                $lng = ArrayHelper::getValue($this->model->$attribute, 'lng');
                $place = ArrayHelper::getValue($this->model->$attribute, 'place');
            }

            // hidden
            $html[] = Html::input('hidden', $this->fieldNameLng, $lng, ['id' => $this->fieldIdLng]);
            $html[] = Html::input('hidden', $this->fieldNameLat, $lat, ['id' => $this->fieldIdLat]);

            // input
            $inputAttributes = ArrayHelper::getValue($this->style, 'input', []);
            $inputAttributes = ArrayHelper::merge($inputAttributes, ['id' => $this->fieldId]);
            $html[] = Html::input('hidden', $this->fieldName, $place, $inputAttributes);

            $html[] = Html::tag('div', null, [
                'id'    => 'map',
                'style' => 'width: 100%; height: 300px; border-radius:5px;margin-top: 10px;'
            ]);
        } else {

        }

        return join("\r\n", $html);
    }

    /**
     *
     * @param array $field
     *
     * @return array
     */
    public function onUpdate($field)
    {
        $fieldName = $this->attribute;
        $model = $this->model;
        $model->$fieldName = Json::encode($model->$fieldName);
    }


    /**
     * @param array           $field
     *
     * @return bool
     * @throws
     */
    public function onLoad($field)
    {
        $attribute = $this->attribute;
        $fieldName = $this->attribute;
        $modelName = $this->model->formName();
        $lat = ArrayHelper::getValue(Yii::$app->request->post(), $modelName . '.'  . $attribute . '-lat', '');
        $lng = ArrayHelper::getValue(Yii::$app->request->post(), $modelName . '.'  . $attribute . '-lng', '');
        $place = ArrayHelper::getValue(Yii::$app->request->post(), $modelName . '.'  . $attribute, '');
        $this->value = [
            'lat'     => $lat,
            'lng'     => $lng,
            'place'   => $place,
        ];
        $this->model->$fieldName = $this->value;

        return true;
    }

    /**
     * @param array             $field
     *
     * @return bool
     */
    public function onLoadDb($field)
    {
        return true;
    }

    /**
     * Registers the needed JavaScript.
     */
    public function registerClientScript()
    {
        Asset::register($this->getView());
        $bandle = Yii::$app->assetManager->getBundle('\common\assets\YandexMaps');

        $id = Html::getInputId($this->model, $this->attribute);

        $string = '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $string = str_shuffle($string);
        $string = substr($string, 0, 10);

        $hash = 'init'.$string;
        $lat = '';
        $lng = '';

        $attribute = $this->attribute;
        if ($this->model->$attribute) {
            $lat = ArrayHelper::getValue($this->model->$attribute, 'lat');
            $lng = ArrayHelper::getValue($this->model->$attribute, 'lng');
        }
        if ($lat == '') $lat = "''";
        if ($lng == '') $lng = "''";

        if (self::isEmpty($this->callback)) {
            $call = "PlaceMapYandex2.init('{$id}', '{$bandle->key}', {$lat}, {$lng});";
        } else {
            $call = "PlaceMapYandex2.init('{$id}', '{$bandle->key}', {$lat}, {$lng}, {$this->callback});";
        }

        $this->getView()->registerJs(<<<JS
var {$hash} = function() {
    {$call} 
};
ymaps.ready({$hash});
JS
);
    }

    /**
     * Returns the options for the captcha JS widget.
     *
     * @return array the options
     */
    protected function getClientOptions()
    {
        return [];
    }

    /**
     * Возвращает значение поля формы из поста
     *
     * @param string $fieldName
     * @param \yii\base\Model $model
     *
     * @return string
     */
    public static function getParam($fieldName, $model)
    {
        $formName = $model->formName();
        $query = $formName . '.' . $fieldName;

        return ArrayHelper::getValue(\Yii::$app->request->post(), $query, '');
    }

    /**
     * Если null => true
     * Если is_string => Если длина == 0 ? true : false
     * Если is_object => false
     * Если is_array => Если длина == 0 ? true : false
     *
     * @param $val
     *
     * @return bool
     */
    public static function isEmpty($val)
    {
        if (is_null($val)) return true;
        if (is_string($val)) {
            if (strlen($val) == 0) return true;
            return false;
        }
        if (is_object($val)) return false;
        if (is_array($val)) {
            if (count($val) == 0) return true;
            return false;
        }

        return false;
    }

}
