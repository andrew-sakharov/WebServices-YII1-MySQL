<?php 

class GlnSupplyModules
{
    public static function getSupply()
    {  
        // *** сформировать запрос GetSupply на основе отлаженного в SoapUI запроса и получить ответ с биржи *****
               
        ini_set('memory_limit', '2048M');
        $row_res = yii::app()->getDb()->createCommand("SELECT linedatetime FROM gln_supply LIMIT 1")->queryRow();
        $date_id = substr ($row_res ['linedatetime'], 0, 10);
        $date_id = substr ($date_id, 0, 10);
        $today = date("Y-m-d H:i:s");
        $startdate_id = substr ($today, 0, 10);
        
        if ($date_id != $startdate_id) {    // Новый Торговый День
            // *** предполагается удание всех старых данных в начале нового торгового дня
            // *** в таблицах requests, deals автоматически сохраняются записи из gln_supply задействованне в сделках в json формате           
            $result = Yii::app()->getDb()->createCommand (" TRUNCATE TABLE gln_applicablegoodscharacteristics; ")->execute();
            $result = Yii::app()->getDb()->createCommand (" TRUNCATE TABLE gln_additionalinformationtradenote; ")->execute();
            $result = Yii::app()->getDb()->createCommand (" TRUNCATE TABLE gln_price; ")->execute();
            $result = Yii::app()->getDb()->createCommand (" TRUNCATE TABLE gln_supply; ")->execute();
            yii::app()->getDb()->createCommand("DELETE FROM price_list WHERE flag = 'NL' or nl_id > 0 ;")->execute();
        }
        
        $status_product = 1;  // Флаг - Позиция Актуальна. Используется для выявления позиций полностью проданных в ходе торгов на Бирже
                
        $i = 0;
        // *** Задать входные параметры для SuppLyRequest
        $MessageDateTime = substr ($today, 0, 10) . 'T' . substr ($today, 11, 8);
        $LineDateTime = $MessageDateTime  . '+02:00';
        
        // ***  xml post structure ***   шаблон запроса (берётся из SoapUI) на торговые позиции с
        // ***  передаётся всего один праметр - "$LineDateTime"
        
        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:urn="urn:fec:florecom:xml:data:draft:SupplyStandardMessage:7" xmlns:urn1="urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:3" xmlns:urn2="urn:fec:florecom:xml:data:draft:ReusableAggregateBusinessInformationEntity:6">
           <soap:Header/>
           <soap:Body>
              <urn:SupplyRequest>
                 <urn:Header>
                    <urn:UserName>************</urn:UserName>
                    <urn:Password>************</urn:Password>
                    <urn:MessageID>1</urn:MessageID>
                    <urn:MessageDateTime>' . $LineDateTime . '</urn:MessageDateTime>
                    <urn:MessageSerial>1</urn:MessageSerial>
                 </urn:Header>
                 <urn:Body></urn:Body>
              </urn:SupplyRequest>
           </soap:Body>
        </soap:Envelope>
        ';
        
        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "http://**************************************************",
            "Content-length: ".strlen($xml_post_string),
        );
        
        $url =  "http://***********************************************";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, 'bifint:bifint');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml; charset=utf-8", "Content-Length: " . strlen($xml_post_string)));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        
        // Преобразовать полученное с биржи сообщение  "$response" в формат массива -> $vals
        $p = xml_parser_create();
        xml_parse_into_struct($p, $response, $vals, $index);
        xml_parser_free($p);
        curl_close($ch);

        return $vals;        
    }
    
    
    
    public static function statusUpdGlnSupply() {
        Yii::app()->getDb()->createCommand (" UPDATE gln_supply SET status_conditioncode_descriptiontext = 'updating'; ")->execute();           
        return;
    }
    
    public static function additionalTradeNote($ADDITIONALINFORMATIONTRADENOTE, $product_id, $startdate_id)
    {       
        // ***  записать в gln_additionalinformationtradenote
        $j = 0;
        $category_name = -999;
        foreach ($ADDITIONALINFORMATIONTRADENOTE as $str3) {    
            $num = $j;
            $subjectcode = $str3['subjectcode'];
            if (isset($str3['content'])) {
                $content = $str3['content'];
                $contentcode = $str3['contentcode'];
                $key_id = $product_id . $num;
                $result = Yii::app()->getDb()->createCommand (" INSERT INTO gln_additionalinformationtradenote
                    (key_id, startdate_id, product_id, num, subjectcode, content, contentcode) VALUES
                    ('$key_id', '$startdate_id', '$product_id', '$num', '$subjectcode', '$content', '$contentcode')
                    ON DUPLICATE KEY UPDATE
                    key_id = '$key_id', startdate_id = '$startdate_id', product_id = '$product_id',
                    num = '$num', subjectcode = '$subjectcode', contentcode = '$contentcode'
                    ; ")->execute();
                if ($contentcode == 'productgroep naam') {
                    $category_name = $content;
                }
            }
            $j++;
        }        
        return $category_name; 
    }
    
    public static function applicableGoodsCharacteristics($APPLICABLEGOODSCHARACTERISTICS, $product_id, $startdate_id)       
    { 
      
        // ***  записать новвые строки в gln_applicablegoodscharacteristics
            $j = 0;
            $country = '';
            $valuecode_listid = '';
            $typecode_listagencyname = '';
            $typecode_listid = '';
            $valuecode_listagencyname = '';            
            $goodscharacteristics['country'] =  '';
            $goodscharacteristics['minimum_length'] =  '';
            $goodscharacteristics['quality_group'] =  '';
            $goodscharacteristics['maturity_stage'] =  '';
                        
            foreach ($APPLICABLEGOODSCHARACTERISTICS as $str1) {
                $num = $j;
                if (!isset($str1['typecode'])) {
                    $j++;
                    continue;
                }
                $typecode = $str1['typecode'];
                $valuecode = $str1['valuecode'];
                $key_id = $product_id . $num;
                
                $result = Yii::app()->getDb()->createCommand (" INSERT INTO gln_applicablegoodscharacteristics
                    (key_id, startdate_id, product_id, num, typecode, typecode_listid, typecode_listagencyname, valuecode, valuecode_listid, valuecode_listagencyname)
                    VALUES
                    ('$key_id', '$startdate_id', '$product_id', '$num', '$typecode', '$typecode_listid',
                   '$typecode_listagencyname', '$valuecode', '$valuecode_listid', '$valuecode_listagencyname')
                    ON DUPLICATE KEY UPDATE
                    key_id = '$key_id',
                    startdate_id = '$startdate_id', product_id ='$product_id', num = '$num', typecode = '$typecode', typecode_listid = '$typecode_listid',
                    typecode_listagencyname = '$typecode_listagencyname', valuecode = '$valuecode', valuecode_listid = '$valuecode_listid',
                    valuecode_listagencyname = '$valuecode_listagencyname'
                    ; ")->execute();
                
                if ($typecode =='S62') {    // *** 'S62' - страна происхождения
                    $country = $valuecode;
                    if (strlen($country) == 0) $country = $not_def;
                    $goodscharacteristics['country'] = $country;
                }
                if ($typecode =='S20') {    // *** 'S20' - размер
                    $minimum_length = $valuecode;
                    $goodscharacteristics['minimum_length'] = $minimum_length;
                }
                if ($typecode =='S98') {    // *** 'S98' - стандарт качества
                    $quality_group = $valuecode;
                    $goodscharacteristics['quality_group'] = $quality_group;
                }
                if ($typecode =='S05') {    // *** 'S05' - стандарт зрелости
                    $maturity_stage = $valuecode;
                    $goodscharacteristics['maturity_stage'] = $maturity_stage;
                }
                $j++;
            }       
            return $goodscharacteristics;           
    }
    
    public static function glnPrice($PRICE_ARRAY, $IND_PRICE_ARRAY, $product_id, $startdate_id)
    { 
        // ***  записать новую строку в gln_price
                
            $price_variant = 'one';
            if ($IND_PRICE_ARRAY > 0)  $price_variant = 'many';
            $j = 0;
            foreach ($PRICE_ARRAY as $str3) {   
                $num = $j;
                $key_id = $product_id . $num;
                $price_typecode_listversionid = '';
                $price_chargeamount = $str3 ['price_chargeamount'];
                $price_typecode = $str3 ['price_typecode'];
                $price_basisquantity = $str3 ['price_basisquantity'];
                $price_basisquantity_unitcode = $str3 ['price_basisquantity_unitcode'];
                $price_netpriceindicator_minimumquantity = $str3 ['price_netpriceindicator_minimumquantity'];
                $price_netpriceindicator_minimumquantity_unitcode = $str3 ['price_netpriceindicator_minimumquantity_unitcode'];
                $price_netpriceindicator_maximumquantity = $str3 ['price_netpriceindicator_maximumquantity'];
                $price_netpriceindicator_maximumquantity_unitcode = $str3 ['price_netpriceindicator_maximumquantity_unitcode'];
                $result = Yii::app()->getDb()->createCommand (" INSERT INTO gln_price
                    (
                    key_id, startdate_id, product_id, num, price_typecode,
                    price_typecode_listversionid, price_chargeamount, price_variant, price_basisquantity,
                    price_basisquantity_unitcode, price_netpriceindicator_minimumquantity,
                    price_netpriceindicator_minimumquantity_unitcode, price_netpriceindicator_maximumquantity,
                    price_netpriceindicator_maximumquantity_unitcode
                    ) VALUES
                    (
                    '$key_id', '$startdate_id', '$product_id', '$num', '$price_typecode',
                    '$price_typecode_listversionid', '$price_chargeamount', '$price_variant', '$price_basisquantity',
                    '$price_basisquantity_unitcode', '$price_netpriceindicator_minimumquantity',
                    '$price_netpriceindicator_minimumquantity_unitcode', '$price_netpriceindicator_maximumquantity',
                    '$price_netpriceindicator_maximumquantity_unitcode'
                    )
                    ON DUPLICATE KEY UPDATE
                    key_id='$key_id', startdate_id='$startdate_id', product_id='$product_id',
                    num='$num', price_typecode='$price_typecode',
                    price_typecode_listversionid='$price_typecode_listversionid', price_chargeamount='$price_chargeamount',
                    price_variant='$price_variant', price_basisquantity='$price_basisquantity',
                    price_basisquantity_unitcode='$price_basisquantity_unitcode', price_netpriceindicator_minimumquantity='$price_netpriceindicator_minimumquantity',
                    price_netpriceindicator_minimumquantity_unitcode='$price_netpriceindicator_minimumquantity_unitcode',
                    price_netpriceindicator_maximumquantity='$price_netpriceindicator_maximumquantity',
                    price_netpriceindicator_maximumquantity_unitcode='$price_netpriceindicator_maximumquantity_unitcode'
                    ; ")->execute();
                $j++;
            }

            $price_array['price_chargeamount'] = $price_chargeamount;
            $price_array['price_typecode'] = $price_typecode;
            $price_array['price_basisquantity'] = $price_basisquantity;
            $price_array['price_basisquantity_unitcode'] = $price_basisquantity_unitcode;
            $price_array['price_netpriceindicator_minimumquantity'] = $price_netpriceindicator_minimumquantity;
            $price_array['price_netpriceindicator_minimumquantity_unitcode'] = $price_netpriceindicator_minimumquantity_unitcode;
            $price_array['price_netpriceindicator_maximumquantity'] = $price_netpriceindicator_maximumquantity;
            $price_array['price_netpriceindicator_maximumquantity_unitcode'] = $price_netpriceindicator_maximumquantity_unitcode;
            $price_array['price_typecode_listversionid'] = $price_typecode_listversionid;
            
            return $price_array;
    }
    
    
    
    // ***  записать (или обновить) новую строку в GLN_SUPPLY 
    public static function glnSupply($startdate_id, $product_id, $schemename, $documenttype, $documenttype_listid, $documenttype_listversionid, $linedatetime,
        $tradingterms_marketplace_schemeid, $tradingterms_marketplace_schemeagencyname, $tradingterms_marketformcode,
        $tradingterms_tradeperiod_startdatetime, $tradingterms_tradeperiod_enddatetime, $tradingterms_condition_typecode, $tradingterms_condition_valuemeasure,
        $referenceddocument_issuerassignedid_schemename, $referenceddocument_uriid,
        $referenceddocument_lineid, $supplierparty_primaryid_schemeid, $supplierparty_primaryid_schemeagencyname, $product_industryassignedid,
        $product_industryassignedid_schemeid, $product_industryassignedid_schemeagencyname, $product_supplierassignedid, $product_descriptiontext,
        $product_typecode, $quantity, $quantity_unitcode, $incrementalorderablequantity, $incrementalorderablequantity_unitcode, $price_typecode,
        $price_typecode_listid, $price_typecode_listversionid, $price_chargeamount, $price_variant, $price_basisquantity, $price_basisquantity_unitcode,
        $price_netpriceindicator_minimumquantity, $price_netpriceindicator_minimumquantity_unitcode, $price_netpriceindicator_maximumquantity,
        $price_netpriceindicator_maximumquantity_unitcode, $packing_package_typecode, $packing_package_typecode_listid, $packing_package_typecode_listagencyname,
        $packing_package_quantity, $packing_package_quantity_unitcode, $packing_package_lineardimension_widthmeasure, $packing_package_lineardimension_widthmeasure_unitcode,
        $packing_package_lineardimension_lengthmeasure, $packing_package_lineardimension_lengthmeasure_unitcode, $packing_package_lineardimension_heightmeasure,
        $packing_package_lineardimension_heightmeasure_unitcode, $packing_innerpackagequantity, $packing_innerpackagequantity_unitcode,
        $delivery_deliveryterms_deliverytypedode, $delivery_deliveryterms_deliverytypedode_listid, $delivery_deliveryterms_deliverytypedode_listversionid,
        $delivery_latestorderdatetime, $status_conditioncode, $status_conditioncode_descriptiontext, $status_product, $category_name, $country,
        $quality_group, $maturity_stage, $color, $minimum_length, $Product_ManufacturerParty_Name)  {
        
    
        $result = Yii::app()->getDb()->createCommand (" INSERT INTO gln_supply
    (
    startdate_id,
    product_id,
    schemename,
    documenttype,
    documenttype_listid,
    documenttype_listversionid,
    linedatetime,
    tradingterms_marketplace_schemeid,
    tradingterms_marketplace_schemeagencyname,
    tradingterms_marketformcode,
    tradingterms_tradeperiod_startdatetime,
    tradingterms_tradeperiod_enddatetime,
    tradingterms_condition_typecode,
    tradingterms_condition_valuemeasure,
    referenceddocument_issuerassignedid_schemename,
    referenceddocument_uriid,
    referenceddocument_lineid,
    supplierparty_primaryid_schemeid,
    supplierparty_primaryid_schemeagencyname,
    product_industryassignedid,
    product_industryassignedid_schemeid,
    product_industryassignedid_schemeagencyname,
    product_supplierassignedid,
    product_descriptiontext,
    product_typecode,
    quantity,
    quantity_unitcode,
    incrementalorderablequantity,
    incrementalorderablequantity_unitcode,
    price_typecode,
    price_typecode_listid,
    price_typecode_listversionid,
    price_chargeamount,
    price_variant,
    price_basisquantity,
    price_basisquantity_unitcode,
    price_netpriceindicator_minimumquantity,
    price_netpriceindicator_minimumquantity_unitcode,
    price_netpriceindicator_maximumquantity,
    price_netpriceindicator_maximumquantity_unitcode,
    packing_package_typecode,
    packing_package_typecode_listid,
    packing_package_typecode_listagencyname,
    packing_package_quantity,
    packing_package_quantity_unitcode,
    packing_package_lineardimension_widthmeasure,
    packing_package_lineardimension_widthmeasure_unitcode,
    packing_package_lineardimension_lengthmeasure,
    packing_package_lineardimension_lengthmeasure_unitcode,
    packing_package_lineardimension_heightmeasure,
    packing_package_lineardimension_heightmeasure_unitcode,
    packing_innerpackagequantity,
    packing_innerpackagequantity_unitcode,
    delivery_deliveryterms_deliverytypedode,
    delivery_deliveryterms_deliverytypedode_listid,
    delivery_deliveryterms_deliverytypedode_listversionid,
    delivery_latestorderdatetime,
    status_conditioncode,
    status_conditioncode_descriptiontext,
    status_product,
    category_name,
    country,
    quality_group,
    maturity_stage,
    color,
    minimum_length,
    product_manufacturerparty_name
    ) VALUES
    (
    '$startdate_id',
    '$product_id',
    '$schemename',
    '$documenttype',
    '$documenttype_listid',
    '$documenttype_listversionid',
    '$linedatetime',
    '$tradingterms_marketplace_schemeid',
    '$tradingterms_marketplace_schemeagencyname',
    '$tradingterms_marketformcode',
    '$tradingterms_tradeperiod_startdatetime',
    '$tradingterms_tradeperiod_enddatetime',
    '$tradingterms_condition_typecode',
    '$tradingterms_condition_valuemeasure',
    '$referenceddocument_issuerassignedid_schemename',
    '$referenceddocument_uriid',
    '$referenceddocument_lineid',
    '$supplierparty_primaryid_schemeid',
    '$supplierparty_primaryid_schemeagencyname',
    '$product_industryassignedid',
    '$product_industryassignedid_schemeid',
    '$product_industryassignedid_schemeagencyname',
    '$product_supplierassignedid',
    '$product_descriptiontext',
    '$product_typecode',
    '$quantity',
    '$quantity_unitcode',
    '$incrementalorderablequantity',
    '$incrementalorderablequantity_unitcode',
    '$price_typecode',
    '$price_typecode_listid',
    '$price_typecode_listversionid',
    '$price_chargeamount',
    '$price_variant',
    '$price_basisquantity',
    '$price_basisquantity_unitcode',
    '$price_netpriceindicator_minimumquantity',
    '$price_netpriceindicator_minimumquantity_unitcode',
    '$price_netpriceindicator_maximumquantity',
    '$price_netpriceindicator_maximumquantity_unitcode',
    '$packing_package_typecode',
    '$packing_package_typecode_listid',
    '$packing_package_typecode_listagencyname',
    '$packing_package_quantity',
    '$packing_package_quantity_unitcode',
    '$packing_package_lineardimension_widthmeasure',
    '$packing_package_lineardimension_widthmeasure_unitcode',
    '$packing_package_lineardimension_lengthmeasure',
    '$packing_package_lineardimension_lengthmeasure_unitcode',
    '$packing_package_lineardimension_heightmeasure',
    '$packing_package_lineardimension_heightmeasure_unitcode',
    '$packing_innerpackagequantity',
    '$packing_innerpackagequantity_unitcode',
    '$delivery_deliveryterms_deliverytypedode',
    '$delivery_deliveryterms_deliverytypedode_listid',
    '$delivery_deliveryterms_deliverytypedode_listversionid',
    '$delivery_latestorderdatetime',
    '$status_conditioncode',
    '$status_conditioncode_descriptiontext',
    '$status_product',
    '$category_name',
    '$country',
    '$quality_group',
    '$maturity_stage',
    '$color',
    '$minimum_length',
    '$Product_ManufacturerParty_Name'
    )
    ON DUPLICATE KEY UPDATE
    startdate_id = '$startdate_id', product_id ='$product_id', schemename = '$schemename', documenttype = '$documenttype', documenttype_listid = '$documenttype_listid',
    documenttype_listversionid = '$documenttype_listversionid', linedatetime = '$linedatetime', tradingterms_marketplace_schemeid = '$tradingterms_marketplace_schemeid',
    tradingterms_marketplace_schemeagencyname = '$tradingterms_marketplace_schemeagencyname', tradingterms_marketformcode = '$tradingterms_marketformcode',
    tradingterms_tradeperiod_startdatetime = '$tradingterms_tradeperiod_startdatetime', tradingterms_tradeperiod_enddatetime = '$tradingterms_tradeperiod_enddatetime',
    tradingterms_condition_typecode = '$tradingterms_condition_typecode', tradingterms_condition_valuemeasure = '$tradingterms_condition_valuemeasure',
    referenceddocument_issuerassignedid_schemename = '$referenceddocument_issuerassignedid_schemename', referenceddocument_uriid ='$referenceddocument_uriid',
    referenceddocument_lineid = '$referenceddocument_lineid', supplierparty_primaryid_schemeid = '$supplierparty_primaryid_schemeid',
    supplierparty_primaryid_schemeagencyname = '$supplierparty_primaryid_schemeagencyname', product_industryassignedid = '$product_industryassignedid',
    product_industryassignedid_schemeid = '$product_industryassignedid_schemeid', product_industryassignedid_schemeagencyname = '$product_industryassignedid_schemeagencyname',
    product_supplierassignedid = '$product_supplierassignedid', product_industryassignedid_schemeid = '$product_industryassignedid_schemeid',
    product_industryassignedid_schemeagencyname = '$product_industryassignedid_schemeagencyname', product_supplierassignedid = '$product_supplierassignedid',
    product_industryassignedid_schemeagencyname = '$product_industryassignedid_schemeagencyname', product_supplierassignedid = '$product_supplierassignedid',
    product_descriptiontext = '$product_descriptiontext', product_typecode = '$product_typecode', quantity = '$quantity',
    quantity_unitcode = '$quantity_unitcode', incrementalorderablequantity = '$incrementalorderablequantity',
    incrementalorderablequantity = '$incrementalorderablequantity', incrementalorderablequantity_unitcode = '$incrementalorderablequantity_unitcode',
    price_typecode = '$price_typecode', price_typecode_listid = '$price_typecode_listid', price_typecode_listversionid = '$price_typecode_listversionid',
    price_chargeamount = '$price_chargeamount', price_variant = '$price_variant', price_basisquantity = '$price_basisquantity', price_basisquantity_unitcode = '$price_basisquantity_unitcode',
    price_netpriceindicator_minimumquantity = '$price_netpriceindicator_minimumquantity', price_netpriceindicator_maximumquantity_unitcode = '$price_netpriceindicator_maximumquantity_unitcode',
    packing_package_typecode = '$packing_package_typecode', packing_package_typecode_listid = '$packing_package_typecode_listid',
    packing_package_typecode_listagencyname = '$packing_package_typecode_listagencyname', packing_package_quantity = '$packing_package_quantity',
    packing_package_quantity_unitcode = '$packing_package_quantity_unitcode', packing_package_lineardimension_widthmeasure = '$packing_package_lineardimension_widthmeasure',
    packing_package_lineardimension_widthmeasure_unitcode = '$packing_package_lineardimension_widthmeasure_unitcode', packing_package_lineardimension_lengthmeasure = '$packing_package_lineardimension_lengthmeasure',
    packing_package_lineardimension_heightmeasure = '$packing_package_lineardimension_heightmeasure', packing_package_lineardimension_heightmeasure_unitcode = '$packing_package_lineardimension_heightmeasure_unitcode',
    packing_innerpackagequantity = '$packing_innerpackagequantity', packing_innerpackagequantity_unitcode = '$packing_innerpackagequantity_unitcode',
    delivery_deliveryterms_deliverytypedode = '$delivery_deliveryterms_deliverytypedode', delivery_deliveryterms_deliverytypedode_listid ='$delivery_deliveryterms_deliverytypedode_listid',
    delivery_deliveryterms_deliverytypedode_listid = '$delivery_deliveryterms_deliverytypedode_listid',
    delivery_deliveryterms_deliverytypedode_listversionid = '$delivery_deliveryterms_deliverytypedode_listversionid',
    delivery_latestorderdatetime = '$delivery_latestorderdatetime', status_conditioncode = '$status_conditioncode',
    status_conditioncode_descriptiontext = '$status_conditioncode_descriptiontext', status_product = '3',  category_name = '$category_name',
    country = '$country', quality_group = '$quality_group', maturity_stage = '$maturity_stage', color = '$color', minimum_length = '$minimum_length',
    product_manufacturerparty_name = '$Product_ManufacturerParty_Name';
    ; ")->execute();
            
    }
    
    public static function glnUpdTypes()
    { 
        //  ***     при неоходимости обновляем таблицу Types  
        //  $result получить список Типов в  GLN
        $result = yii::app()->getDb()->createCommand("SELECT DISTINCT (category_name) from gln_supply ORDER BY category_name ASC; ")->queryAll();
        $i = 0;
        $category_list_l = count($result);
        while ($i < $category_list_l) {
            $category_list [$i] = strip_tags($result[$i]['category_name']);
            $i++;
        }        
        $i= 0;
        while ($i < $category_list_l) {
            if ( $category_list[$i] == '') { $i++; continue;}   // могут быть Торговые Позиции с пустыми Типами, их пропускаем
            $name_nl = $category_list[$i];            
            $res = yii::app()->getDb()->createCommand("select * from types where name_nl = '$name_nl' and flag = 'NL'")->queryAll();            
            if (count($res) == 0) {          // Если не найдено нужно добавить новый Тип в Таблицу.
                $name = 'GL' . $i;
                $name_short = $name_nl;
                $result = Yii::app()->getDb()->createCommand (" INSERT INTO types (name, name_en, name_es, name_ru, name_nl, flag)
        VALUES ('$name', '$name_short', '$name_short', '$name_short', '$name_nl', 'NL');
        ")->execute();
            }
            $i++;
        }       
    }
    
    public static function glnUpdSorts($update_date)
    { 
        //  при неоходимости обновляем таблицу Sorts ****************************************************************************
                
        $gln = yii::app()->getDb()->createCommand("select product_id, product_industryassignedid, product_descriptiontext,
                                                   category_name, country, referenceddocument_uriid from gln_supply;")->queryAll();
        $a = count($gln);       
        $i = 0;
        while ($i < $a) {
            $product_id = $gln[$i]['product_id'];
            $product_industryassignedid = $gln[$i]['product_industryassignedid'];   // код сорта по справочнику в Голландии
            $product_descriptiontext = $gln[$i]['product_descriptiontext'];         // имя сорта
            $category_name = $gln[$i]['category_name'];
            $referenceddocument_uriid = $gln[$i]['referenceddocument_uriid'];
            
            $res = yii::app()->getDb()->createCommand("select * from sorts where nl_product_id = '$product_industryassignedid'")->queryAll();
            
            if (count($res) == 0) {          // Если не найдено нужно добавить новый Сорт в Таблицу.
                $types = yii::app()->getDb()->createCommand("select * from types where name_nl = '$category_name' and flag = 'NL'")->queryAll();
                if (count($types) != 0) {
                    $max_id = Yii::app()->getDb()->createCommand ("SELECT MAX(id) as m FROM sorts;") ->queryAll();
                    $id = $max_id[0]['m'] + 1;          //  берётся максимальный ID из  users.
                    $types_id = $types ['0']['id'] ;
                    $result = Yii::app()->getDb()->createCommand (" INSERT INTO sorts (
	        id, name, types_id, create_date, nl_product_id, flag, color_id)
 	        VALUES ('$id', '$product_descriptiontext', '$types_id', '$update_date', '$product_industryassignedid','NL',  '0'
            ); ")->execute();
                }
            }
            else {
                $id = $res [0]['id'];
            }
            $i++;
        }    
        
    }
    
    public static function glnUpdPlantations($update_date)
    {
        //  ***     при неоходимости обновляем таблицу Plantations ***************************************************************
        
        $platform_id =  Platforms::NETHERLANDS_W_PLATFORM;
        $result = yii::app()->getDb()->createCommand("SELECT DISTINCT (product_manufacturerparty_name) from gln_supply ORDER BY product_manufacturerparty_name ASC;")->queryAll();
        
        $i = 0;
        $plantation_name_list_l = count($result);
        while ($i < $plantation_name_list_l) {
            $plantation_name_list [$i] = strip_tags($result[$i]['product_manufacturerparty_name']);
            $i++;
        }
        
        //$user_nl = yii::app()->getDb()->createCommand("select id from users where name = 'BiFlorica_Netherlands' and role = 'agent'")->queryRow();
        //$user_id = $user_nl['id'];
        
        $user_id = Platforms::NETHERLANDS_W_PLATFORM_AGENT;       
        $i= 0;
        while ($i < $plantation_name_list_l) {
            if ( $plantation_name_list[$i] == '') { $i++;continue;}    // могут быть Торговые Позиции с пустой Плантацией, их пропускаем
            $name = $plantation_name_list[$i];       ;
            $res = yii::app()->getDb()->createCommand("select * from plantations where trade_mark = '$name' and m_users_id = '$user_id'")->queryAll();
            if (count($res) == 0) {             // Если не найдено нужно добавить новую Плантацию в Таблицу.
                // Формировать список плантаций
                $result = Yii::app()->getDb()->createCommand (" INSERT INTO plantations (
		name, m_users_id, trade_mark, comment, create_date, updated)
        VALUES ('$name', '$user_id', '$name', 'NL', '$update_date', '$update_date'
        ); ")->execute();
            }
            $i++;
        }                
    }
    
    public static function glnUpdPriceList($update_date)
    {
                       
        $result = Yii::app()->getDb()->createCommand (" UPDATE price_list SET count_40_qb = '12345'  WHERE flag = 'NL'; ")->execute();
        
        // *** *** *** удаление из "price_list" неактуальных позиций
        // *** в ответе на запрос GetSupply поступают только актуальные позиции
        // *** необходимо уалить из "price_list" позиции ставшие неактуальными с момента последней итерации обновления
        // *** перед обновлением все строки в "gln_supply" получают статус status_conditioncode_descriptiontext = 'updating'
        // *** и в "price_list" устанавливается count_40_qb = '12345'
        // *** при обновлении "price_list" используются строки из "gln_supply" где status_conditioncode_descriptiontext != 'updating'
        // *** после обновления из "price_list" удалются не обновлённые строки "WHERE flag = 'NL' and count_40_qb = '12345'"
        // *** поле "count_40_qb" = '12345' здесь используется как флаг предварительного логического удаления записи
        
        $gln = yii::app()->getDb()->createCommand("select product_id, product_industryassignedid, product_descriptiontext,
        category_name, country, minimum_length, price_chargeamount, quantity, quality_group, packing_innerpackagequantity,
        quantity_unitcode, product_manufacturerparty_name, maturity_stage, startdate_id,  linedatetime
        from gln_supply where status_conditioncode_descriptiontext != 'updating';")->queryAll();
        
        $a = count($gln);        
        $new = 0;
        $upd = 0;
        $del = 0;
        $i = 0;
        
        while ($i < $a) {
            $product_id = $gln[$i]['product_id'];
            $product_industryassignedid = $gln[$i]['product_industryassignedid'];
            $product_descriptiontext = $gln[$i]['product_descriptiontext'];
            $category_name = $gln[$i]['category_name'];
            $country = $gln[$i]['country'];
            $minimum_length = $gln[$i]['minimum_length'];
                if ($minimum_length > 0) {}
                else {$minimum_length = 0;}            
            $price_chargeamount = $gln[$i]['price_chargeamount'] * 10000;
            $quantity = $gln[$i]['quantity'];
            $quality_group = $gln[$i]['quality_group'];
            $packing_innerpackagequantity = $gln[$i]['packing_innerpackagequantity'];
            $quantity_unitcode = $gln[$i]['quantity_unitcode'];
            $plantation_name = $gln[$i]['product_manufacturerparty_name'];
            $maturity_stage = $gln[$i]['maturity_stage'];
            $startdate_id = $gln[$i]['startdate_id'];
            $linedatetime = $gln[$i]['linedatetime'];
            $create_date = substr ($startdate_id, 0, 10) . ' ' .  substr ($startdate_id, 11, 8);
                
            // *** дополнительно удалять (не обрабатывать) позиции "price_list" где  $quantity < $packing_innerpackagequantity 
            // *** проверка - "Остаток меньше Объёма минимально допустимого Заказа"
            if ($quantity >= $packing_innerpackagequantity) {                
                $size_40 = '0';
                $size_50 = '0';
                $size_60 = '0';
                $size_70 = '0';
                $size_80 = '0';
                $size_90 = '0';
                $size_100 = '0';
                $size_100p = '0';
                
                $count_40 = '0';
                $count_50 = '0';
                $count_60 = '0';
                $count_70 = '0';
                $count_80 = '0';
                $count_90 = '0';
                $count_100 = '0';
                $count_100p = '0';
                
                if ($minimum_length >= 30 and $minimum_length <= 45) {
                    $size_40 = $price_chargeamount;
                    $count_40 = $quantity;
                }
                if ($minimum_length > 45 and $minimum_length <= 55) {
                    $size_50 = $price_chargeamount;
                    $count_50 = $quantity;
                }
                if ($minimum_length > 55 and $minimum_length <= 65) {
                    $size_60 = $price_chargeamount;
                    $count_60 = $quantity;
                }
                if ($minimum_length > 65 and $minimum_length <= 75) {
                    $size_70 = $price_chargeamount;
                    $count_70 = $quantity;
                }
                if ($minimum_length > 75 and $minimum_length <= 85) {
                    $size_80 = $price_chargeamount;
                    $count_80 = $quantity;
                }
                if ($minimum_length > 85 and $minimum_length <= 95) {
                    $size_90 = $price_chargeamount;
                    $count_90 = $quantity;
                }
                if ($minimum_length > 95 and $minimum_length <= 105) {
                    $size_100 = $price_chargeamount;
                    $count_100 = $quantity;
                }
                if ($minimum_length > 100) {
                    $size_100p = $price_chargeamount;
                    $count_100p = $quantity;
                }
                
                $result = Yii::app()->getDb()->createCommand (" UPDATE price_list SET
                create_date = '$update_date', count_40_qb = '0',
                update_date = '$update_date', is_deleted = 'N',
                nl_product_id = '$product_industryassignedid', price_chargeamount = '$price_chargeamount', minimum_length = '$minimum_length', 
                quantity = '$quantity', flag = 'NL',
                quality_group = '$quality_group', packing_innerpackagequantity = '$packing_innerpackagequantity', quantity_unitcode = '$quantity_unitcode',
                size_40 = '$size_40', size_50 = '$size_50', size_60 = '$size_60',  size_70 = '$size_70', size_80 = '$size_80', size_90 = '$size_90', 
                size_100 = '$size_100', size_100p = '$size_100p',
                count_40 = '$count_40', count_50 = '$count_50', count_60 = '$count_60', count_70 = '$count_70', count_80 = '$count_80', count_90 = '$count_90', 
                count_100 = '$count_100', count_100p = '$count_100p'
                WHERE nl_id = '$product_id'
                ; ")->execute();
                
                if (true == $result) {
                    $upd++;
                }
                else {
                    $sorts = yii::app()->getDb()->createCommand("select * from sorts where nl_product_id = '$product_industryassignedid'")->queryAll();
                    $sorts_id= $sorts['0']['id'];
                    $types_id = $sorts ['0']['types_id'];
                    $plantations = yii::app()->getDb()->createCommand("select * from plantations where trade_mark = '$plantation_name' and  comment = 'NL'")->queryAll();
                    $m_users_id = $plantations ['0']['m_users_id'];
                    $plantations_id = $plantations ['0']['id'];
                    $types = yii::app()->getDb()->createCommand("select * from types where name_nl = '$category_name' and flag = 'NL'")->queryAll();
                    
                    $result = Yii::app()->getDb()->createCommand (" INSERT INTO price_list (
                    users_id, plantations_id, types_id, sorts_id, create_date, update_date, is_deleted,
                    nl_id, nl_product_id, price_chargeamount, minimum_length, quantity, flag,
                    quality_group, packing_innerpackagequantity, quantity_unitcode, grower, maturity_stage,
                    size_40, size_50, size_60,  size_70, size_80, size_90, size_100, size_100p,
                    count_40, count_50, count_60, count_70, count_80, count_90, count_100, count_100p, count_40_qb
                    )
                    VALUES (
                    '$m_users_id', '$plantations_id', '$types_id', '$sorts_id', '$update_date', '$update_date', 'N',
                    '$product_id', '$product_industryassignedid', '$price_chargeamount', '$minimum_length', '$quantity', 'NL',
                    '$quality_group', '$packing_innerpackagequantity', '$quantity_unitcode', '$country', '$maturity_stage',
                    '$size_40', '$size_50', '$size_60',  '$size_70', '$size_80', '$size_90', '$size_100', '$size_100p',
                    '$count_40', '$count_50', '$count_60', '$count_70', '$count_80', '$count_90', '$count_100', '$count_100p', '0'
                    ); ")->execute();
                    $new++;
                }
            }
            $i++;
        }
        
        // *** Удалить записи с флагом count_40_qb = '12345'        
        $del = Yii::app()->getDb()->createCommand (" delete from price_list WHERE flag = 'NL' and count_40_qb = '12345' ; ")->execute();
               
        
        return $result_upd;
    }       
}

