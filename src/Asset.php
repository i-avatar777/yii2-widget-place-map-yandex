<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace common\widgets\PlaceMapYandex;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Asset extends AssetBundle
{
    public $sourcePath = '@vendor/i-avatar777/yii2-widget-place-map-yandex/src/assets';

    public $css = [];

    public $js = [
        'handlers.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'common\assets\YandexMaps',
    ];
}
