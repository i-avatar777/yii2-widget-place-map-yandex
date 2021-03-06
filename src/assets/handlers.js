/**
 * Виджет PlaceMap
 *
 * @type {{init: Function}}
 */

var PlaceMapYandex2 = {

    map: null,
    marker: null,
    fieldId: null,
    myPlacemarkCollection: null,
    apikey: null,
    lat: null,
    lng: null,

    /**
     * Функция инициализации виджета
     * @param fieldId - string
     * @param apikey - string
     * @param lat - string
     * @param lng - string
     * @param callback - function(pos)
     */
    init: function (fieldId, apikey, lat, lng, callback) {

        PlaceMapYandex2.apikey = apikey;
        PlaceMapYandex2.lat = lat;
        PlaceMapYandex2.lng = lng;

        if (PlaceMapYandex2.lat == '') PlaceMapYandex2.lat = 44.515933;
        if (PlaceMapYandex2.lng == '') PlaceMapYandex2.lng = 48.707787;

        // При возникновении событий, изменяющих состояние карты,
        // ее параметры передаются в адресную строку браузера (после символа #).
        // При загрузке страницы карта устанавливается в состояние,
        // соответствующее переданным параметрам.
        // http://.../savemap.html#type=hybrid&center=93.3218,60.0428&zoom=12
        var myMap = new ymaps.Map("map", {
                center: [PlaceMapYandex2.lng, PlaceMapYandex2.lat], // Волгоград
                zoom: 3
            }),
            // myPlacemark1 = new ymaps.Placemark([PlaceMapYandex2.lng, PlaceMapYandex2.lat], {
            //     balloonContent: 'Первый',
            //     myId: 'first'
            // }),
            myPlacemarkCollection = new ymaps.GeoObjectCollection(),
            lastOpenedBalloon = false;

        myMap.controls.remove('searchControl');

        PlaceMapYandex2.map = myMap;
        // PlaceMapYandex2.marker = myPlacemark1;
        PlaceMapYandex2.myPlacemarkCollection = myPlacemarkCollection;

        // myPlacemarkCollection
        //     .add(myPlacemark1)
        // ;

        myMap.geoObjects.add(myPlacemarkCollection);

        // myMap.geoObjects.remove(myPlacemark1);

        myMap.controls
            .add('typeSelector')
        ;

        // Обработка событий карты:
        // - boundschange - изменение границ области показа;
        // - type - изменение типа карты;
        // - balloonclose - закрытие балуна.
        myMap.events.add(['boundschange', 'typechange', 'balloonclose'], setLocationHash);

        // Обработка событий открытия балуна для любого элемента
        // коллекции.
        // В данном случае на карте находятся только метки одной коллекции.
        // Чтобы обработать события любых геообъектов карты можно использовать
        // myMap.geoObjects.events.add(['balloonopen'],function (e) { ...
        myPlacemarkCollection.events.add(['balloonopen'], function (e) {
            lastOpenedBalloon = e.get('target').properties.get('myId');
            setLocationHash();
        });

        setMapStateByHash();

        // Получение значение параметра name из адресной строки
        // браузера.
        function getParam (name, location) {
            location = location || window.location.hash;
            var res = location.match(new RegExp('[#&]' + name + '=([^&]*)', 'i'));
            return (res && res[1] ? res[1] : false);
        }

        // Передача параметров, описывающих состояние карты,
        // в адресную строку браузера.
        function setLocationHash () {
            var params = [
                'type=' + myMap.getType().split('#')[1],
                'center=' + myMap.getCenter(),
                'zoom=' + myMap.getZoom()
            ];
            if (myMap.balloon.isOpen()) {
                params.push('open=' + lastOpenedBalloon);
            }
            window.location.hash = params.join('&');
        }

        // Установка состояния карты в соответствии с переданными в адресной строке
        // браузера параметрами.
        function setMapStateByHash () {
            var hashType = getParam('type'),
                hashCenter = getParam('center'),
                hashZoom = getParam('zoom'),
                open = getParam('open');
            if (hashType) {
                myMap.setType('yandex#' + hashType);
            }
            if (hashCenter) {
                myMap.setCenter(hashCenter.split(','));
            }
            if (hashZoom) {
                myMap.setZoom(hashZoom);
            }
            if (open) {
                myPlacemarkCollection.each(function (geoObj) {
                    var id = geoObj.properties.get('myId');
                    if (id == open) {
                        geoObj.balloon.open();

                    }
                });
            }
        }


        myMap.events.add('click', function (e) {
            // Получение координат щелчка
            var coords = e.get('coords');
            $('#'+fieldId+'-lng').val(coords[0]);
            $('#'+fieldId+'-lat').val(coords[1]);

            if (PlaceMapYandex2.marker !== null) {
                PlaceMapYandex2.myPlacemarkCollection.remove(PlaceMapYandex2.marker);
            }

            myPlacemark1 = new ymaps.Placemark([coords[0], coords[1]], {
                balloonContent: 'Первый',
                myId: 'first'
            });
            PlaceMapYandex2.myPlacemarkCollection.add(myPlacemark1);
            PlaceMapYandex2.marker = myPlacemark1;

            var url = 'https://geocode-maps.yandex.ru/1.x/?apikey=' + PlaceMapYandex2.apikey + '&geocode=' + coords[1] + ',' + coords[0] + '&format=json&kind=house';
            var json1 = JSON.stringify({lat: coords[1], lng: coords[0]});

            $('#'+fieldId).attr('value', json1);

            if (typeof (callback) != "undefined") {
                $.ajax({
                    url: url,
                    success: function (ret) {
                        console.log(ret);
                        callback(ret);
                    }
                });
            }
        });
    }
};