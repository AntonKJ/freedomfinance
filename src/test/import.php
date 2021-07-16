<?php

include 'DB.php';

use Database\DB;

    parse_str($argv[1]);
    parse_str($argv[2]);

    $dbClass = new DB();
    $db = $dbClass->getDb();

    $count_files_offers = ($file > 0) ? (int)$file : 7;               // Количество файлов предложений отсканировать
    $count_offers_in_to_file = ($cnt > 0) ? (int)$cnt : 50000000;     // Количество предложений в файле отсканировать
    $steps = 1000;                                                    // Шаг товаров для лоадера загрузки (/\) один файл

    $status_city = 1;
    $strtotime = date('Y-m-d H:i:s');
    $LIMIT = $count_offers_in_to_file;

    // OFFERS/IMPORTS XMLS
    for($io=0; $io<=$count_files_offers; $io++) {

        $city_id_1c_xml = '';
        $status = $status_city;

        if (!file_exists("data/offers".$io."_1.xml")){
            echo "data/offers".$io."_1.xml - File dos not exist!" ;
            break;
        }

        //OPEN
        $xml = simplexml_load_file("data/offers".$io."_1.xml");
        //READ
        $i = 0;

        // Сначала город
        foreach ($xml->ПакетПредложений as $city) {

            $strtotime = date('Y-m-d H:i:s');

            $id = (string)$city->ИдКлассификатора ?? '';
            preg_match( '/.*\((.*)\).*/i', $city->Наименование, $matches);
            $reg_name = trim($matches[1]);
            $name = htmlspecialchars(stripcslashes((string)$reg_name)) ?? '';
            $id1c = (string)$city->ИдКлассификатора ?? '';
            $status = $status;
            $city_id = $id ?? '';
/*                  var_dump($name);
                  var_dump($id1c);*/

            $city_id_1c_xml = $city_id;

            $idpt = $db->prepare("INSERT INTO `city` (id,name,id1c,status,datetime) 
VALUES (:id,:name,:id1c,:status,:datetime)
ON DUPLICATE KEY UPDATE name=:name,id1c=:id1c,status=:status,datetime=:datetime");

            $idpt->execute([ 'id' => $id,
                'name' => $name,
                'id1c' => $id1c,
                'status' => $status,
                'datetime' => $strtotime
                ]);
        }

        // Товары данного города
        foreach ($xml->ПакетПредложений->Предложения->Предложение as $item) {

            $strtotime = date('Y-m-d H:i:s');

            $id_1c = (string)$item->Ид ?? '';
            $name_item = (string)$item->Наименование  ?? '';
            $articul = (string)$item->Артикул  ?? '';
            $base_id = json_encode($item->БазоваяЕдиница)  ?? '';
            $city_id = $city_id_1c_xml  ?? '';
            $code_item = (string)$item->Код ?? '' ;
            $quantity = (string)$item->Количество ?? '' ;
            $datetime = $strtotime;

            //UPDATE `offers` as ofs SET key_map=SHA1( CONCAT(ofs.code, ofs.city_id)) Where key_map=''

                $query = "INSERT INTO offers (key_map, id1c, articul, naimenovanie, base_id, city_id, code, quantity, datetime) 
        VALUES ( :key_map, :id1c, :articul, :naimenovanie, :base_id, :city_id, :code, :quantity, :datetime)
        ON DUPLICATE KEY UPDATE 
     id1c=:id1c, 
     articul=:articul,
     naimenovanie=:naimenovanie,
     base_id=:base_id,
     city_id=:city_id,
     code=:code,
     quantity=:quantity,
     datetime=:datetime
        ";
                $idpo = $db->prepare($query);

            $id_1c_price_offer = $id_1c;
            $idpo->execute([
                'id1c' => $id_1c,
                'articul' => $articul,
                'naimenovanie' => $name_item,
                'base_id' => $base_id,
                'city_id' => $city_id,
                'code' => $code_item,
                'quantity' => $quantity,
                'key_map' => sha1($code_item.$city_id),
                'datetime' => $datetime,
            ]);

            foreach ($xml->ПакетПредложений->Предложения->Предложение->Цены->Цена as $prices) {

                $strtotime = date('Y-m-d H:i:s');

                $price_type_id = (string)$prices->ИдТипаЦены  ?? '';
                $unit_price = (string)$prices->ЦенаЗаЕдиницу  ?? '';
                $currency = (string)$prices->Валюта  ?? '';
                $performance = (string)$prices->Представление  ?? '';
                $city_id = $city_id_1c_xml  ?? '';
                $с_price = (string)$prices->Коэффициент  ?? '';
                $id_offer = (string)$id_1c_price_offer ?? '';


                //UPDATE `prices` as prs SET key_map=SHA1( CONCAT(prs.city_id, prs.id_offer, prs.price_type_id, prs.unit_price)) Where 1
            /*  DELETE p1 FROM prices p1
                JOIN prices p2
                WHERE
                 p1.id<p2.id
                 AND
                 p1.key_map = p2.key_map*/

                    $query = "INSERT INTO prices (key_map, price_type_id, unit_price, currency, performance, city_id, coffee, id_offer, datetime) 
VALUES ( :key_map, :price_type_id, :unit_price, :currency, :performance, :city_id, :coffee, :id_offer, :datetime)
ON DUPLICATE KEY UPDATE price_type_id=:price_type_id, unit_price=:unit_price,
     currency=:currency,
     performance=:performance,
     city_id=:city_id,
     coffee=:coffee,
     id_offer=:id_offer,
     datetime=:datetime
";
                    $idpp = $db->prepare($query);

                $idpp->execute([
                    'price_type_id' => $price_type_id,
                    'unit_price' => $unit_price,
                    'currency' => $currency,
                    'performance' => $performance,
                    'city_id' => $city_id,
                    'coffee' => $с_price,
                    'id_offer' => $id_offer,
                    'key_map' => sha1($city_id.$id_offer.$price_type_id.$unit_price),
                    'datetime' => $strtotime,
                ]);

            }

            if ( $i%$steps == 0 ){
                echo '/';
            }

            $i++;
            if ($i == $LIMIT) {
                break;
            }
        }

    // IMPORT XMLS

        $city_id_1c_xml = '';
        $status = $status_city;

        if (!file_exists("data/import".$io."_1.xml")){
            echo "data/import".$io."_1.xml - File dos not exist!" ;
            break;
        }

        //OPEN
        $xml = simplexml_load_file("data/import".$io."_1.xml");
        //READ
        $i = 0;

        // Сначало город
        foreach ($xml->Каталог as $city) {

            $strtotime = date('Y-m-d H:i:s');

            $id = (string)$city->Ид ?? '';
            $name = htmlspecialchars(stripcslashes((string)$city->Наименование)) ?? '';
            $id1c = (string)$city->ИдКлассификатора ?? '';
            $status = $status;

            $city_id = $id ?? '';
            /*        var_dump($id);
                    var_dump($name);
                    var_dump($id1c);*/

            $city_id_1c_xml = $city_id;

            $idpt = $db->prepare("INSERT INTO `city` (id,name,id1c,status,datetime) 
VALUES (:id,:name,:id1c,:status,:datetime)
ON DUPLICATE KEY UPDATE name=:name,id1c=:id1c,status=:status,datetime=:datetime");

            $idpt->execute([ 'id' => $id,
                'name' => $name,
                'id1c' => $id1c,
                'status' => $status,
                'datetime' => $strtotime,
            ]);

            //var_dump($idpt->execute());
        }

        // Товары данного города
        foreach ($xml->Каталог->Товары->Товар as $item) {

            $strtotime = date('Y-m-d H:i:s');

            $id_1c = (string)$item->Ид ?? '';
            $name_item = (string)$item->Наименование  ?? '';
            $shortcode = (string)$item->Штрихкод  ?? '';
            $articul = (string)$item->Артикул  ?? '';
            $base_id = json_encode($item->БазоваяЕдиница)  ?? '';
            $groups_import = json_encode($item->Группы)  ?? '';
            $description  = (string)$item->Описание  ?? '';
            $valuepropertys = json_encode($item->ЗначенияСвойств)  ?? '';
            $tax_rates  = json_encode($item->СтавкиНалогов)  ?? '';
            $value_of_requisites = json_encode($item->ЗначенияРеквизитов) ?? '' ;
            $code_item = (int)$item->Код ?? '' ;
            $city_id = $city_id_1c_xml ?? '';
            $weight_item = (string)$item->Вес ?? '';
            $stamps = (string)$item->Марки ?? '';
            $expected_arrival = (string)$item->ОжидаемыйПриход ?? '';
            $english_title  = (string)$item->АнглийскоеНазвание ?? '';
            $chinese_title = (string)$item->КитайскоеНазвание ?? '';
            $additional_articules = json_encode($item->ДополнительныеАртикулы) ?? '';
            $date_of_expected_arrival = json_encode($item->ДатаОжидаемогоПрихода) ?? '';
            $comment = (string)$item->Комментарий ?? '';
            $datetime = $strtotime;


            // UPDATE `import` as ipt SET key_map=SHA1( CONCAT(ipt.code, ipt.city_id)) Where key_map=''

                $idpi = $db->prepare("
INSERT INTO import ( key_map, id1c, shortcode, articul, naimenovanie, base_id, groups_import, description, valuepropertys, tax_rates, value_of_requisites, code, city_id, weight,  stamps , expected_arrival, english_title, chinese_title, additional_articules, date_of_expected_arrival, comment, datetime) 
    VALUES ( :key_map, :id1c, :shortcode, :articul, :naimenovanie, :base_id, :groups_import, :description, :valuepropertys, :tax_rates, :value_of_requisites, :code, :city_id, :weight, :stamps , :expected_arrival, :english_title, :chinese_title, :additional_articules, :date_of_expected_arrival, :comment, :datetime)
ON DUPLICATE KEY UPDATE id1c=:id1c, shortcode=:shortcode,
 articul=:articul,
 naimenovanie=:naimenovanie,
 base_id=:base_id,
 groups_import=:groups_import,
 description=:description,
 valuepropertys=:valuepropertys,
 tax_rates=:tax_rates,
 value_of_requisites=:value_of_requisites,
 code=:code,
 city_id=:city_id,
 weight=:weight,
 stamps=:stamps,
 expected_arrival=:expected_arrival,
 english_title=:english_title,
 chinese_title=:chinese_title,
 additional_articules=:additional_articules,
 date_of_expected_arrival=:date_of_expected_arrival,
 comment=:comment,
 datetime=:datetime");

            $idpi->execute([
                'id1c' => $id_1c,
                'shortcode' => $shortcode,
                'articul' => $articul,
                'naimenovanie' => $name_item,
                'base_id' => $base_id,
                'groups_import' => $groups_import,
                'description' => $description,
                'valuepropertys' => $valuepropertys,
                'tax_rates' => $tax_rates,
                'value_of_requisites' => $value_of_requisites,
                'code' => $code_item,
                'city_id' => $city_id,
                'weight' => $weight_item,
                'stamps' => $stamps,
                'expected_arrival' => $expected_arrival,
                'english_title' => $english_title,
                'chinese_title' => $chinese_title,
                'additional_articules' => $additional_articules,
                'date_of_expected_arrival' => $date_of_expected_arrival,
                'comment' => $comment,
                'key_map' => sha1($code_item.$city_id),
                'datetime' => $datetime,
            ]);

            if ($i%$steps == 0){
                echo '\\';
            }

            $i++;
            if ($i == $LIMIT) {
                break;
            }
        }
    }

    include 'view.php';
    $status_view = new ViewSource\CView( false );

if ($status_view) {
    echo "Data import completed successfully!
";
}
