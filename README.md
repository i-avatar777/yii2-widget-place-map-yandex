# yii2-widget-place-map-yandex
Виджет для YII2 для указания токчи на карте

```php
<?= $form->field($model, 'place')->widget('\iAvatar777\widgets\PlaceMapYandex\PlaceMap') ?>
```

```php
<?= $form->field($model, 'place')->widget('\iAvatar777\widgets\PlaceMapYandex\PlaceMap', [
                'callback' => <<<JS
function (pos) {
    console.log(pos);
    $('#countryregion-point').val(JSON.stringify(pos));
}
JS

])->label('Укажите примерную точку проживания') ?>
```

