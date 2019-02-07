<?php 

class GlnSupplyRequest
{
    public static function loadGlnData()
    {  
                                   
        $vals = GlnSupplyModules::getSupply();    // *** сформировать запрос GetSupply и получить ответ с биржи *****
        
        // проверить - полученны ли данные по Запросу (например нет интернет и т.д.)
        if (count($vals) > 1000) {
            Yii::app()->getDb()->createCommand (" INSERT INTO atest (a1, a2) VALUES ('****** Update Time ********', '$today') ; ")->execute(); 
        }
        else {
            Yii::app()->getDb()->createCommand (" INSERT INTO atest (a1, a2) VALUES ('****** GLN No Connection **', '$today') ; ")->execute(); 
            return;
        }

            $data [1] = microtime(true);        // *** отладка
            $r = $data [1] - $data [0];         // *** отладка
            $result = Yii::app()->getDb()->createCommand (" INSERT INTO atest (a1, a2) VALUES ('GetSupply and Unpacking', '$r') ; ")->execute();     // *** отладка
            
            GlnSupplyModules::statusUpdGlnSupply();    // *** изменить статус на обновление для GlnSupply *****

        // *** инициализация, т.к. в первой строке в ответе с биржи могут отсутсвовать некоторые из реквизитов
        $not_def = 'xx';       // *** реквизиты Страна, Плантация, ... в некоторых предложениях их значения не определены
        $schemename = '';
        $documenttype = '';
        $documenttype_listid = '';
        $documenttype_listversionid = '';
        $tradingterms_marketplace_schemeid = '';
        $tradingterms_marketplace_schemeagencyname = '';
        $tradingterms_marketformcode = '';
        $startdate_id = '';
        $tradingterms_condition_typecode = '';
        $tradingterms_condition_valuemeasure =  '';
        $referenceddocument_issuerassignedid_schemename = '';
        $referenceddocument_lineid = '';
        $supplierparty_primaryid_schemeid = '';
        $supplierparty_primaryid_schemeagencyname = '';                      
        $product_industryassignedid_schemeid = '';
        $product_industryassignedid_schemeagencyname = '';                      
        $product_id = '';
        $schemename = '';
        $documenttype = '';
        $documenttype_listid = '';
        $documenttype_listversionid = '';
        $linedatetime = '';
        $tradingterms_marketplace_schemeid = '';
        $tradingterms_marketplace_schemeagencyname = '';
        $tradingterms_marketformcode = '';
        $tradingterms_tradeperiod_startdatetime = '';
        $tradingterms_tradeperiod_enddatetime = '';
        $tradingterms_condition_typecode = '';
        $tradingterms_condition_valuemeasure = '';
        $referenceddocument_issuerassignedid_schemename = '';
        $referenceddocument_uriid = '';
        $referenceddocument_lineid = '';
        $supplierparty_primaryid_schemeid = '';
        $supplierparty_primaryid_schemeagencyname = '';
        $product_industryassignedid = '';
        $product_industryassignedid_schemeid = '';
        $product_industryassignedid_schemeagencyname = '';
        $product_supplierassignedid = '';
        $product_descriptiontext = '';
        $product_typecode = '';
        $quantity = '';
        $quantity_unitcode = '';
        $incrementalorderablequantity = '';
        $incrementalorderablequantity_unitcode = '';
        $price_typecode = '';
        $price_typecode_listid = '';
        $price_typecode_listversionid = '';
        $price_chargeamount = '';
        $price_variant = '';
        $price_basisquantity = '';
        $price_basisquantity_unitcode = '';
        $price_netpriceindicator_minimumquantity = '';
        $price_netpriceindicator_minimumquantity_unitcode = '';
        $price_netpriceindicator_maximumquantity = '';
        $price_netpriceindicator_maximumquantity_unitcode = '';
        $packing_package_typecode = '';
        $packing_package_typecode_listid = '';
        $packing_package_typecode_listagencyname = '';
        $packing_package_quantity = '';
        $packing_package_quantity_unitcode = '';
        $packing_package_lineardimension_widthmeasure = '';
        $packing_package_lineardimension_widthmeasure_unitcode = '';
        $packing_package_lineardimension_lengthmeasure = '';
        $packing_package_lineardimension_lengthmeasure_unitcode = '';
        $packing_package_lineardimension_heightmeasure = '';
        $packing_package_lineardimension_heightmeasure_unitcode = '';
        $packing_innerpackagequantity = '';
        $packing_innerpackagequantity_unitcode = '';
        $delivery_deliveryterms_deliverytypedode = '';
        $delivery_deliveryterms_deliverytypedode_listid = '';
        $delivery_deliveryterms_deliverytypedode_listversionid = '';
        $delivery_latestorderdatetime = '';
        $status_conditioncode = '';
        $status_conditioncode_descriptiontext = '';
        $status_product = '';
        $category_name = '';
        $country = '';
        $quality_group = '';
        $maturity_stage = '';
        $color = '';
        $minimum_length = '';
        $Product_ManufacturerParty_Name ='';
        $key_id ='';
        $num ='';
        $typecode ='';
        $typecode_listid ='';
        $typecode_listagencyname ='';
        $valuecode ='';
        $valuecode_listid ='';
        $valuecode_listagencyname ='';
        $subjectcode ='';
        $content ='';
        $contentcode ='';
        $price_typecode ='';
        $price_typecode_listversionid ='';
        $price_chargeamount ='';
        $price_variant ='';
        $price_basisquantity ='';
        $price_basisquantity_unitcode ='';
        $price_netpriceindicator_minimumquantity ='';
        $price_netpriceindicator_minimumquantity_unitcode ='';
        $price_netpriceindicator_maximumquantity ='';
        $price_netpriceindicator_maximumquantity_unitcode ='';
        $feram_flag = '';
        $APPLICACHARACTERISTICS = [];
        $PRICE_ARRAY = [];          
        $ADDITIONALINFORMATIONTRADENOTE = [];

        $IND_ADDITIONALINFORMATIONTRADENOTE = -1;
        $IND_PRICE_ARRAY = -1;
        $IND_APPLICABLEGOODSCHARACTERISTICS = -1;

$i = -1;
foreach ($vals as $str) {   // *** цикл по разбору данных в $vals **********************    
    $fl_err = '0';
    if ($str['tag'] == 'ASM:SUPPLYTRADELINEITEM') {
        if ($str['type'] == 'open') {
            $i++;
            $res = [];
            continue;
        }
        else {continue;}
    }
    if (substr($str['tag'],0,5) == 'FERAM') {   // *** 'FERAM' - идентификатор подстрок верхних уровней  
        
        if ($str['tag'] == 'FERAM:STATUS' and $str['type'] == 'close') {    // *** считана последняя строка описания позиции, записать данные в БД     
            
            if (strlen($Product_ManufacturerParty_Name) == 0) $Product_ManufacturerParty_Name = $not_def; 
            
            // *** записать в БД строки из ADDITIONALINFORMATIONTRADENOTE *****
            $category_name = GlnSupplyModules::additionalTradeNote($ADDITIONALINFORMATIONTRADENOTE, $product_id, $startdate_id);
            if ($category_name  == -999)  $fl_err = '1';  // отбраковать всю позицию                                                            
            
            // *** записать в БД строки из APPLICABLEGOODSCHARACTERISTICS *****
            if ($fl_err == '0') {
                $goodscharacteristics = GlnSupplyModules::applicableGoodsCharacteristics($APPLICABLEGOODSCHARACTERISTICS, $product_id, $startdate_id);
                $country = $goodscharacteristics['country'];
                $minimum_length = $goodscharacteristics['minimum_length'];
                $quality_group = $goodscharacteristics['quality_group'];
                $maturity_stage = $goodscharacteristics['maturity_stage'];
            }
                         
            // *** записать в БД строки из PRICE_ARRAY *****
            if ($fl_err == '0') {
                $price_array = GlnSupplyModules::glnPrice($PRICE_ARRAY, $IND_PRICE_ARRAY, $product_id, $startdate_id);
                
                $price_chargeamount = $price_array['price_chargeamount'];
                $price_typecode = $price_array['price_typecode'];
                $price_basisquantity = $price_array['price_basisquantity'];
                $price_basisquantity_unitcode = $price_array['price_basisquantity_unitcode'];
                $price_netpriceindicator_minimumquantity = $price_array['price_netpriceindicator_minimumquantity'];
                $price_netpriceindicator_minimumquantity_unitcode = $price_array['price_netpriceindicator_minimumquantity_unitcode'];
                $price_netpriceindicator_maximumquantity = $price_array['price_netpriceindicator_maximumquantity'];
                $price_netpriceindicator_maximumquantity_unitcode = $price_array['price_netpriceindicator_maximumquantity_unitcode'];
                $price_typecode_listversionid = $price_array['price_typecode_listversionid'];
            }
            
           
    // ***  записать (или обновить) новую строку в GLN_SUPPLY
            if ($fl_err == '0') {
                GlnSupplyModules::glnSupply($startdate_id, $product_id, $schemename, $documenttype, $documenttype_listid, $documenttype_listversionid, $linedatetime,
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
                    $quality_group, $maturity_stage, $color, $minimum_length, $Product_ManufacturerParty_Name);
            }
                        
    $feram_flag = '';
    $APPLICABLEGOODSCHARACTERISTICS = [];
    $IND_APPLICABLEGOODSCHARACTERISTICS = -1;           
    $PRICE_ARRAY = [];
    $IND_PRICE_ARRAY = -1;                       
    $ADDITIONALINFORMATIONTRADENOTE = [];
    $IND_ADDITIONALINFORMATIONTRADENOTE = -1;            
    continue;
    }
    
    //  *** блок разбора входного сообщения с биржи  *********************************************************************************      
        if ($str['tag'] == 'FERAM:ID') {
            if (isset($str['value'])) $product_id = $str['value'];
            continue;
        }        
        if ($str['tag'] == 'FERAM:LINEDATETIME') {
            if (isset($str['value'])) $linedatetime = $str['value'];
            continue;
        }        
        if ($str['tag'] == 'FERAM:STARTDATETIME') {
            if (isset($str['value'])) $tradingterms_tradeperiod_startdatetime = $str['value'];
            continue;
        }        
        if ($str['tag'] == 'FERAM:ENDDATETIME') {
            if (isset($str['value'])) $tradingterms_tradeperiod_enddatetime = $str['value'];
            continue;
        }        
        if ($str['tag'] == 'FERAM:INDUSTRYASSIGNEDID') {
            if (isset($str['value'])) $product_industryassignedid = $str['value'];
            continue;
        }        
        if ($str['tag'] == 'FERAM:SUPPLIERASSIGNEDID') {
            if (isset($str['value'])) $product_supplierassignedid = $str['value'];
            continue;
        }        
        if ($str['tag'] == 'FERAM:DESCRIPTIONTEXT' and $feram_flag == 'FERAM:DELIVERYTERMS_open') {
            if (isset($str['value'])) $status_conditioncode_descriptiontext = $str['value'];
            continue;
        }        
        if ($str['tag'] == 'FERAM:DESCRIPTIONTEXT') {
            if (isset($str['value'])) {
                $product_descriptiontext = $str['value'];
                $string = array(";","'",'"',"^","|","\n","\r","\p","<",">");
                $product_descriptiontext = trim(htmlspecialchars(strip_tags(str_replace($string,"",$product_descriptiontext))));
            }
            continue;
        }        
        if ($str['tag'] == 'FERAM:ADDITIONALINFORMATIONTRADENOTE') {
            $feram_flag = 'FERAM:ADDITIONALINFORMATIONTRADENOTE';
            continue;
        }        
        if ($str['tag'] == 'FERAM:MANUFACTURERPARTY') {
            $feram_flag = 'FERAM:MANUFACTURERPARTY';
            continue;
        }     
        if ($feram_flag ==  'FERAM:PACKAGE') {
            if ($str['tag'] == 'FERAM:TYPECODE') {
                if (isset($str['value'])) $packing_package_typecode = $str['value'];
            }
            if ($str['tag'] == 'FERAM:QUANTITY') {
                if (isset($str['value'])) {
                    $packing_package_quantity = $str['value'];
                }
                if (isset($str['attributes'])) {
                    $str1 = $str['attributes'];
                    $packing_package_quantity_unitcode = $str1['UNITCODE'];
                }
                continue;
            }
            if ($str['tag'] == 'FERAM:WIDTHMEASURE') {
                if (isset($str['value'])) {
                    $packing_package_lineardimension_widthmeasure = $str['value'];
                }
                if (isset($str['attributes'])) {
                    $str1 = $str['attributes'];
                    $packing_package_lineardimension_widthmeasure_unitcode = $str1['UNITCODE'];
                    continue;
                }
            }            
            if ($str['tag'] == 'FERAM:LENGTHMEASURE') {
                if (isset($str['value'])) {
                    $packing_package_lineardimension_lengthmeasure = $str['value'];
                }
                if (isset($str['attributes'])) {
                    $str1 = $str['attributes'];
                    $packing_package_lineardimension_lengthmeasure_unitcode = $str1['UNITCODE'];
                }
                continue;
            }
            
            if ($str['tag'] == 'FERAM:HEIGHTMEASURE') {
                if (isset($str['value'])) {
                    $packing_package_lineardimension_heightmeasure = $str['value'];
                }
                if (isset($str['attributes'])) {
                    $str1 = $str['attributes'];
                    $packing_package_lineardimension_heightmeasure_unitcode = $str1['UNITCODE'];
                }
                continue;
            }
        }        
        if ($str['tag'] == 'FERAM:INNERPACKAGEQUANTITY') {
            if (isset($str['value'])) {
                $packing_innerpackagequantity = $str['value'];
            }
            if (isset($str['attributes'])) {
                $str1 = $str['attributes'];
                $packing_innerpackagequantity_unitcode = $str1['UNITCODE'];
                continue;
            }            
        }        
        if ($str['tag'] == 'FERAM:DELIVERYTERMS') {
            $feram_flag = 'FERAM:DELIVERYTERMS';
            continue;
        }        
        if ($str['tag'] == 'FERAM:STATUS' and $str['type'] == 'close') {
            $feram_flag = 'FERAM:DELIVERYTERMS_close';
            continue;
        }        
        if ($str['tag'] == 'FERAM:STATUS' and $str['type'] == 'open') {
            $feram_flag = 'FERAM:DELIVERYTERMS_open';
            continue;
        }        
        if ($str['tag'] == 'FERAM:LATESTORDERDATETIME') {
            if (isset($str['value'])) $delivery_latestorderdatetime = $str['value'];
            continue;
        }        
        if ($str['tag'] == 'FERAM:CONDITIONCODE' and $feram_flag == 'FERAM:DELIVERYTERMS_open') {
            if (isset($str['value'])) $status_conditioncode = $str['value'];
            continue;
        }        
        if ($str['tag'] == 'FERAM:QUANTITY') {
            if (isset($str['value'])) $quantity = $str['value'];
            if (isset($str['attributes'])) {
                $str1 = $str['attributes'];
                $quantity_unitcode = $str1['UNITCODE'];
            }
            continue;
        }        
        if ($str['tag'] == 'FERAM:INCREMENTALORDERABLEQUANTITY') {
            if (isset($str['value'])) $incrementalorderablequantity = $str['value'];
            if (isset($str['attributes'])) {
                $str1 = $str['attributes'];
                $incrementalorderablequantity_unitcode = $str1['UNITCODE'];
            }
            continue;
        }        
        if ($str['tag'] == 'FERAM:VALUEMEASURE') {
            if (isset($str['value'])) $tradingterms_condition_valuemeasure = $str['value'];
            continue;
        }       
        if ($str['tag'] == 'FERAM:TYPECODE' and $feram_flag == 'FERAM:CONDITION') {
            if (isset($str['value'])) $tradingterms_condition_typecode = $str['value'];
            continue;
        }        
        if ($str['tag'] == 'FERAM:CONDITION' and $str['type'] == 'open') {
            $feram_flag = 'FERAM:CONDITION';
            continue;
        }        
        if ($str['tag'] == 'FERAM:CONDITION' and $str['type'] == 'close') {
            $feram_flag = '';
            continue;
        }        
        if ($str['tag'] == 'FERAM:APPLICABLEGOODSCHARACTERISTICS' and $str['type'] == 'open') {
            $feram_flag = 'FERAM:APPLICABLEGOODSCHARACTERISTICS';
            continue;
        }        
        if ($str['tag'] == 'FERAM:APPLICABLEGOODSCHARACTERISTICS' and $str['type'] == 'close') {
            $feram_flag = '';
            continue;
        }       
        if ($str['tag'] == 'FERAM:PACKAGE' and $str['type'] == 'open') {
            $feram_flag = 'FERAM:PACKAGE';
            continue;
        }        
        if ($str['tag'] == 'FERAM:PACKAGE' and $str['type'] == 'close') {
            $feram_flag = '';
            continue;
        }        
        if ($str['tag'] == 'FERAM:APPLICABLEGOODSCHARACTERISTICS' and $str['type'] == 'close') {
            $feram_flag = '';
            continue;
        }        
        if ($feram_flag == 'FERAM:APPLICABLEGOODSCHARACTERISTICS') {            
            if ($str['tag'] == 'FERAM:TYPECODE') {
                $IND_APPLICABLEGOODSCHARACTERISTICS++;
                $APPLICABLEGOODSCHARACTERISTICS [$IND_APPLICABLEGOODSCHARACTERISTICS]['typecode'] = $str['value'];
            }
            if ($str['tag'] == 'FERAM:VALUECODE') {
                $APPLICABLEGOODSCHARACTERISTICS [$IND_APPLICABLEGOODSCHARACTERISTICS]['valuecode'] = $str['value'];
            }
            continue;
        }        
        if ($str['tag'] == 'FERAM:PRICE' and $str['type'] == 'open') {
            $feram_flag = 'FERAM:PRICE';
            continue;
        }        
        if ($str['tag'] == 'FERAM:PRICE' and $str['type'] == 'close') {
            $feram_flag = '';
            continue;
        }        
        if ($feram_flag == 'FERAM:PRICE') {            
            if ($str['tag'] == 'FERAM:TYPECODE') {
                $IND_PRICE_ARRAY++;
                $PRICE_ARRAY [$IND_PRICE_ARRAY]['price_typecode'] = $str['value'];
            }            
            if ($str['tag'] == 'FERAM:CHARGEAMOUNT') {
                $PRICE_ARRAY [$IND_PRICE_ARRAY]['price_chargeamount'] = $str['value'];
            }            
            if ($str['tag'] == 'FERAM:BASISQUANTITY') {
                $PRICE_ARRAY [$IND_PRICE_ARRAY]['price_basisquantity'] = $str['value'];
                $str1 = $str['attributes'];
                $PRICE_ARRAY [$IND_PRICE_ARRAY]['price_basisquantity_unitcode'] = $str1['UNITCODE'];
            }            
            if ($str['tag'] == 'FERAM:MINIMUMQUANTITY') {
                $PRICE_ARRAY [$IND_PRICE_ARRAY]['price_netpriceindicator_minimumquantity'] = $str['value'];
                $str1 = $str['attributes'];
                $PRICE_ARRAY [$IND_PRICE_ARRAY]['price_netpriceindicator_minimumquantity_unitcode'] = $str1['UNITCODE'];
            }           
            if ($str['tag'] == 'FERAM:MAXIMUMQUANTITY') {
                $PRICE_ARRAY [$IND_PRICE_ARRAY]['price_netpriceindicator_maximumquantity'] = $str['value'];
                $str1 = $str['attributes'];
                $PRICE_ARRAY [$IND_PRICE_ARRAY]['price_netpriceindicator_maximumquantity_unitcode'] = $str1['UNITCODE'];
            }
            continue;
        }    // *** конец блока обработки 'FERAM' 
}
else {  //  *** строки с заголовком RAM     - идентификатор подстрок второго уровня
    if ($str['tag'] == 'RAM:URIID') {
        if (isset($str['value'])) {
            $referenceddocument_uriid = $str['value'];
            if (substr($referenceddocument_uriid,0,2) == '..') {
                $strlen = strlen($referenceddocument_uriid) - 1;
                $referenceddocument_uriid = 'http://185.34.168.10' . substr($referenceddocument_uriid,5, $strlen);
            }
        }
        else {
            $referenceddocument_uriid = '';
            $referenceddocument_lineid = '';
            $referenceddocument_issuerassignedid_schemename = '';
        }
        continue;
    }    
    if ($feram_flag == 'FERAM:ADDITIONALINFORMATIONTRADENOTE') {
        if ($str['tag'] == 'RAM:SUBJECTCODE') {
            $IND_ADDITIONALINFORMATIONTRADENOTE++;
            $ADDITIONALINFORMATIONTRADENOTE [$IND_ADDITIONALINFORMATIONTRADENOTE]['subjectcode'] = $str['value'];
        }
        if ($str['tag'] == 'RAM:CONTENT') {
            if (isset($str['value'])) {
                $ADDITIONALINFORMATIONTRADENOTE [$IND_ADDITIONALINFORMATIONTRADENOTE]['content'] = $str['value'];
            }
        }
        if ($str['tag'] == 'RAM:CONTENTCODE') {
            if (isset($str['value'])) {
                $ADDITIONALINFORMATIONTRADENOTE [$IND_ADDITIONALINFORMATIONTRADENOTE]['contentcode'] = $str['value'];
            }
        }
        continue;
    }
    if ($feram_flag == 'FERAM:MANUFACTURERPARTY') {
        if ($str['tag'] == 'RAM:NAME') {
            $Product_ManufacturerParty_Name = $str['value'];
            $string = array(";","'",'"',"^","|","\n","\r","\p","<",">");
            $Product_ManufacturerParty_Name = trim(htmlspecialchars(strip_tags(str_replace($string,"",$Product_ManufacturerParty_Name))));
        }        
        if ($str['tag'] == 'RAM:PRIMARYID') {
            $Product_ManufacturerParty_Id = $str['value'];
        }
        continue;
    }    
    if ($feram_flag == 'FERAM:DELIVERYTERMS') {
        if ($str['tag'] == 'RAM:DELIVERYTYPECODE') {
            $delivery_deliveryterms_deliverytypedode = $str['value'];
            $srting = array(";","'",'"',"^","|","\n","\r","\p","<",">");
            $delivery_deliveryterms_deliverytypedode = trim(htmlspecialchars(strip_tags(str_replace($srting,"",$delivery_deliveryterms_deliverytypedode))));
        }
    }
    continue;
}
}   //  ***  Конец блока разбора входного потока XML  ***********
        

$data [2] = microtime(true);    // *** отладка
$r = $data [2] - $data [1];     // *** отладка
$result = Yii::app()->getDb()->createCommand (" INSERT INTO atest (a1, a2) VALUES ('Load table GLN_SUPPLY:', '$r') ; ")->execute();    // *** отладка

//  ***     Формирование GLN записей в Price List и Справочниках  ******************************************************      
$today = date("Y-m-d H:i:s");
$update_date = substr ($today, 0, 10) . ' '  . substr ($today, 11, 8);

//  ***     при неоходимости обновляем таблицу Types    
GlnSupplyModules::glnUpdTypes($update_date);

$data [3] = microtime(true);  // *** отладка
$r = $data [3] - $data [2];     // *** отладка
$result = Yii::app()->getDb()->createCommand (" INSERT INTO atest (a1, a2) VALUES ('Load table Types:', '$r') ; ")->execute();  // *** отладка

//  при неоходимости обновляем таблицу Sorts ****************************************************************************
GlnSupplyModules::glnUpdSorts($update_date);   

$data [4] = microtime(true);  // *** отладка
$r = $data [4] - $data [3];     // *** отладка
$result = Yii::app()->getDb()->createCommand (" INSERT INTO atest (a1, a2) VALUES ('Load table Sorts:', '$r') ; ")->execute();  // *** отладка
                      
//  ***     при неоходимости обновляем таблицу Plantations ***************************************************************
GlnSupplyModules::glnUpdPlantations($update_date);  

$data [5] = microtime(true);      // *** отладка
$r = $data [5] - $data [4];         // *** отладка
$result = Yii::app()->getDb()->createCommand (" INSERT INTO atest (a1, a2) VALUES ('Load table Plantations:', '$r') ; ")->execute();    // *** отладка


// Обновить PriceList ******************************************************************************************************
$result_upd = GlnSupplyModules::glnUpdPriceList($update_date);

return;                       
    }
}
    
/* ********************************************************************************************************************************************************************

****** Пример Фрагмента разбираемой XML строки (по одной торговой позиции) Всего 25+ тыс. позиций
****** Запись "FERAM:STATUS [type] => close [level] => 7" идентифицирует конец описания позиции
****** Строки с идентификатором RAM - могут повторяться от 1 до N количества раз (N заранее не определено). Их значения записываются в промежуточные Массивы и в затем в специальные таблицы БД.
****** Строки с идентификатором группы FERAM:PRICE - могут повторяться от 1 до N количества раз (N заранее не определено). Значения записываются в промежуточные Массивы и в затем в специальные таблицы БД.
****** Уровень вложенности структуры - "9"


****** Заголовок Всего Сообщения (присутствует Один раз.
Array ( [tag] => SOAP:ENVELOPE [type] => open [level] => 1 [attributes] => Array ( [XMLNS:SOAP] => http://www.w3.org/2003/05/soap-envelope [XMLNS:XSI] => http://www.w3.org/2001/XMLSchema-instance [XMLNS:XSD] => http://www.w3.org/2001/XMLSchema ) )
Array ( [tag] => SOAP:BODY [type] => open [level] => 2 )
Array ( [tag] => ASM:SUPPLYRESPONSE [type] => open [level] => 3 [attributes] => Array ( [XMLNS:FEUDT] => urn:fc:florecom:xml:data:draft:UnqualifiedDataType:1 [XMLNS:FERAM] => urn:fec:florecom:xml:data:draft:ReusableAggregateBusinessInformationEntity:6 [XMLNS:QDT] => urn:un:unece:uncefact:data:standard:QualifiedDataType:7 [XMLNS:RAM] => urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:3 [XMLNS:UDT1] => urn:un:unece:uncefact:data:standard:UnqualifiedDataType:8 [XMLNS:UDT] => urn:un:unece:uncefact:data:standard:UnqualifiedDataType:4 [XMLNS:FEQDT] => urn:fec:florecom:xml:data:draft:QualifiedDataType:7 [XMLNS:QDT8] => urn:un:unece:uncefact:data:standard:QualifiedDataType:3 [XMLNS:ASM] => urn:fec:florecom:xml:data:draft:SupplyStandardMessage:7 ) )
Array ( [tag] => ASM:BODY [type] => open [level] => 4 )
Array ( [tag] => ASM:SUPPLYRESPONSEDETAILS [type] => open [level] => 5 )


******* Тело Сообщения. Присутствет (описывает) для каждой позиции на Торгах.
FERAM=Array ( [tag] => FERAM:ID [type] => complete [level] => 7 [attributes] => Array ( [SCHEMENAME] => AAG [SCHEMEDATAURI] => [SCHEMEURI] => ) [value] => 164105131 )
FERAM=Array ( [tag] => FERAM:DOCUMENTTYPE [type] => complete [level] => 7 [attributes] => Array ( [LISTID] => 1001 [LISTVERSIONID] => D09A ) [value] => 9 )
FERAM=Array ( [tag] => FERAM:LINEDATETIME [type] => complete [level] => 7 [value] => 2018-09-19T08:33:38.5378941+02:00 )
FERAM=Array ( [tag] => FERAM:TRADINGTERMS [type] => open [level] => 7 )
FERAM=Array ( [tag] => FERAM:MARKETPLACE [type] => open [level] => 8 )
FERAM=Array ( [tag] => FERAM:ID [type] => complete [level] => 9 [attributes] => Array ( [SCHEMEID] => 1 [SCHEMEAGENCYNAME] => FEC ) )
FERAM=Array ( [tag] => FERAM:MARKETPLACE [type] => close [level] => 8 )
FERAM=Array ( [tag] => FERAM:MARKETFORMCODE [type] => complete [level] => 8 [value] => 002 )
FERAM=Array ( [tag] => FERAM:TRADEPERIOD [type] => open [level] => 8 )
FERAM=Array ( [tag] => FERAM:STARTDATETIME [type] => complete [level] => 9 [value] => 2018-09-19T00:00:00 )
FERAM=Array ( [tag] => FERAM:ENDDATETIME [type] => complete [level] => 9 [value] => 2018-09-19T23:59:59+02:00 )
FERAM=Array ( [tag] => FERAM:TRADEPERIOD [type] => close [level] => 8 )
FERAM=Array ( [tag] => FERAM:CONDITION [type] => open [level] => 8 )
FERAM=Array ( [tag] => FERAM:TYPECODE [type] => complete [level] => 9 [value] => 303 )
FERAM=Array ( [tag] => FERAM:VALUEMEASURE [type] => complete [level] => 9 [value] => 1 )
FERAM=Array ( [tag] => FERAM:CONDITION [type] => close [level] => 8 )
FERAM=Array ( [tag] => FERAM:TRADINGTERMS [type] => close [level] => 7 )
FERAM=Array ( [tag] => FERAM:REFERENCEDDOCUMENT [type] => open [level] => 7 ) __RAM=Array ( [tag] => RAM:ISSUERASSIGNEDID [type] => complete [level] => 8 [attributes] => Array ( [SCHEMENAME] => IRN [SCHEMEDATAURI] => [SCHEMEURI] => ) ) __RAM=Array ( [tag] => RAM:URIID [type] => complete [level] => 8 [value] => http://185.34.168.54/pictures/X304221_H_1.jpg ) __RAM=Array ( [tag] => RAM:LINEID [type] => complete [level] => 8 [value] => 001 )
FERAM=Array ( [tag] => FERAM:REFERENCEDDOCUMENT [type] => close [level] => 7 )
FERAM=Array ( [tag] => FERAM:ADDITIONALINFORMATIONTRADENOTE [type] => open [level] => 7 ) __RAM=Array ( [tag] => RAM:SUBJECTCODE [type] => complete [level] => 8 [value] => AAI ) __RAM=Array ( [tag] => RAM:CONTENT [type] => complete [level] => 8 [value] => 1000 ) __RAM=Array ( [tag] => RAM:CONTENTCODE [type] => complete [level] => 8 [value] => prodcode )
FERAM=Array ( [tag] => FERAM:ADDITIONALINFORMATIONTRADENOTE [type] => close [level] => 7 )
FERAM=Array ( [tag] => FERAM:ADDITIONALINFORMATIONTRADENOTE [type] => open [level] => 7 ) __RAM=Array ( [tag] => RAM:SUBJECTCODE [type] => complete [level] => 8 [value] => AAI ) __RAM=Array ( [tag] => RAM:CONTENT [type] => complete [level] => 8 [value] => Срезаные цветы ) __RAM=Array ( [tag] => RAM:CONTENTCODE [type] => complete [level] => 8 [value] => productgroep naam )
FERAM=Array ( [tag] => FERAM:ADDITIONALINFORMATIONTRADENOTE [type] => close [level] => 7 )
FERAM=Array ( [tag] => FERAM:SUPPLIERPARTY [type] => open [level] => 7 ) __RAM=Array ( [tag] => RAM:PRIMARYID [type] => complete [level] => 8 [attributes] => Array ( [SCHEMEID] => 1 [SCHEMEAGENCYNAME] => FEC ) )
FERAM=Array ( [tag] => FERAM:SUPPLIERPARTY [type] => close [level] => 7 )
FERAM=Array ( [tag] => FERAM:PRODUCT [type] => open [level] => 7 )
FERAM=Array ( [tag] => FERAM:INDUSTRYASSIGNEDID [type] => complete [level] => 8 [attributes] => Array ( [SCHEMEID] => 1 [SCHEMEAGENCYNAME] => VBN ) [value] => 6325 )
FERAM=Array ( [tag] => FERAM:SUPPLIERASSIGNEDID [type] => complete [level] => 8 [value] => 6325 )
FERAM=Array ( [tag] => FERAM:DESCRIPTIONTEXT [type] => complete [level] => 8 [value] => Achil F Park Variety )
FERAM=Array ( [tag] => FERAM:TYPECODE [type] => complete [level] => 8 [value] => 57 )
FERAM=Array ( [tag] => FERAM:PRODUCTGROUPID [type] => complete [level] => 8 [attributes] => Array ( [SCHEMEID] => 16 [SCHEMEAGENCYNAME] => VBN ) [value] => 10103901 )
FERAM=Array ( [tag] => FERAM:APPLICABLEGOODSCHARACTERISTICS [type] => open [level] => 8 )
FERAM=Array ( [tag] => FERAM:TYPECODE [type] => complete [level] => 9 [attributes] => Array ( [LISTID] => 8 [LISTAGENCYNAME] => VBN ) [value] => S98 )
FERAM=Array ( [tag] => FERAM:VALUECODE [type] => complete [level] => 9 [attributes] => Array ( [LISTID] => 9 [LISTAGENCYNAME] => VBN ) [value] => A1 )
FERAM=Array ( [tag] => FERAM:APPLICABLEGOODSCHARACTERISTICS [type] => close [level] => 8 )
FERAM=Array ( [tag] => FERAM:APPLICABLEGOODSCHARACTERISTICS [type] => open [level] => 8 )
FERAM=Array ( [tag] => FERAM:TYPECODE [type] => complete [level] => 9 [attributes] => Array ( [LISTID] => 8 [LISTAGENCYNAME] => VBN ) [value] => S20 )
FERAM=Array ( [tag] => FERAM:VALUECODE [type] => complete [level] => 9 [attributes] => Array ( [LISTID] => 9 [LISTAGENCYNAME] => VBN ) [value] => 050 )
FERAM=Array ( [tag] => FERAM:APPLICABLEGOODSCHARACTERISTICS [type] => close [level] => 8 )
FERAM=Array ( [tag] => FERAM:APPLICABLEGOODSCHARACTERISTICS [type] => open [level] => 8 )
FERAM=Array ( [tag] => FERAM:TYPECODE [type] => complete [level] => 9 [attributes] => Array ( [LISTID] => 8 [LISTAGENCYNAME] => VBN ) [value] => S05 )
FERAM=Array ( [tag] => FERAM:VALUECODE [type] => complete [level] => 9 [attributes] => Array ( [LISTID] => 9 [LISTAGENCYNAME] => VBN ) [value] => 023 )
FERAM=Array ( [tag] => FERAM:APPLICABLEGOODSCHARACTERISTICS [type] => close [level] => 8 )
FERAM=Array ( [tag] => FERAM:APPLICABLEGOODSCHARACTERISTICS [type] => open [level] => 8 )
FERAM=Array ( [tag] => FERAM:TYPECODE [type] => complete [level] => 9 [attributes] => Array ( [LISTID] => 8 [LISTAGENCYNAME] => VBN ) [value] => S62 )
FERAM=Array ( [tag] => FERAM:VALUECODE [type] => complete [level] => 9 [attributes] => Array ( [LISTID] => 9 [LISTAGENCYNAME] => VBN ) [value] => NL )
FERAM=Array ( [tag] => FERAM:APPLICABLEGOODSCHARACTERISTICS [type] => close [level] => 8 )
FERAM=Array ( [tag] => FERAM:MANUFACTURERPARTY [type] => open [level] => 8 ) __RAM=Array ( [tag] => RAM:PRIMARYID [type] => complete [level] => 9 [attributes] => Array ( [SCHEMEID] => 1 [SCHEMEAGENCYNAME] => FEC ) [value] => 8714231147916 ) __RAM=Array ( [tag] => RAM:NAME [type] => complete [level] => 9 [value] => Akershoek & Zn )
FERAM=Array ( [tag] => FERAM:MANUFACTURERPARTY [type] => close [level] => 8 )
FERAM=Array ( [tag] => FERAM:PRODUCT [type] => close [level] => 7 )
FERAM=Array ( [tag] => FERAM:QUANTITY [type] => complete [level] => 7 [attributes] => Array ( [UNITCODE] => 1 ) [value] => 880 )
FERAM=Array ( [tag] => FERAM:INCREMENTALORDERABLEQUANTITY [type] => complete [level] => 7 [attributes] => Array ( [UNITCODE] => 1 ) [value] => 1 )
FERAM=Array ( [tag] => FERAM:PRICE [type] => open [level] => 7 )
FERAM=Array ( [tag] => FERAM:TYPECODE [type] => complete [level] => 8 [attributes] => Array ( [LISTID] => 5375 [LISTVERSIONID] => D09A ) [value] => AE )
FERAM=Array ( [tag] => FERAM:CHARGEAMOUNT [type] => complete [level] => 8 [value] => 0.35 )
FERAM=Array ( [tag] => FERAM:BASISQUANTITY [type] => complete [level] => 8 [attributes] => Array ( [UNITCODE] => 1 ) [value] => 1 )
FERAM=Array ( [tag] => FERAM:NETPRICEINDICATOR [type] => complete [level] => 8 [value] => false )
FERAM=Array ( [tag] => FERAM:MINIMUMQUANTITY [type] => complete [level] => 8 [attributes] => Array ( [UNITCODE] => 1 ) [value] => 1 )
FERAM=Array ( [tag] => FERAM:MAXIMUMQUANTITY [type] => complete [level] => 8 [attributes] => Array ( [UNITCODE] => 1 ) [value] => 880 )
FERAM=Array ( [tag] => FERAM:PRICE [type] => close [level] => 7 )
FERAM=Array ( [tag] => FERAM:PACKING [type] => open [level] => 7 )
FERAM=Array ( [tag] => FERAM:PACKAGE [type] => open [level] => 8 )
FERAM=Array ( [tag] => FERAM:TYPECODE [type] => complete [level] => 9 [attributes] => Array ( [LISTID] => 901 [LISTAGENCYNAME] => VBN ) [value] => 577 )
FERAM=Array ( [tag] => FERAM:QUANTITY [type] => complete [level] => 9 [attributes] => Array ( [UNITCODE] => 3 ) [value] => 1 )
FERAM=Array ( [tag] => FERAM:LINEARDIMENSION [type] => open [level] => 9 )
FERAM=Array ( [tag] => FERAM:WIDTHMEASURE [type] => complete [level] => 10 [attributes] => Array ( [UNITCODE] => CMT ) [value] => 0 )
FERAM=Array ( [tag] => FERAM:LENGTHMEASURE [type] => complete [level] => 10 [attributes] => Array ( [UNITCODE] => CMT ) [value] => 0 )
FERAM=Array ( [tag] => FERAM:HEIGHTMEASURE [type] => complete [level] => 10 [attributes] => Array ( [UNITCODE] => CMT ) [value] => 0 )
FERAM=Array ( [tag] => FERAM:LINEARDIMENSION [type] => close [level] => 9 )
FERAM=Array ( [tag] => FERAM:PACKAGE [type] => close [level] => 8 )
FERAM=Array ( [tag] => FERAM:INNERPACKAGEQUANTITY [type] => complete [level] => 8 [attributes] => Array ( [UNITCODE] => 1 ) [value] => 80 )
FERAM=Array ( [tag] => FERAM:PACKING [type] => close [level] => 7 )
FERAM=Array ( [tag] => FERAM:DELIVERY [type] => open [level] => 7 )
FERAM=Array ( [tag] => FERAM:DELIVERYTERMS [type] => open [level] => 8 ) __RAM=Array ( [tag] => RAM:DELIVERYTYPECODE [type] => complete [level] => 9 [attributes] => Array ( [LISTID] => 4053 [LISTVERSIONID] => D07A ) [value] => DDP )
FERAM=Array ( [tag] => FERAM:DELIVERYTERMS [type] => close [level] => 8 )
FERAM=Array ( [tag] => FERAM:LATESTORDERDATETIME [type] => complete [level] => 8 [value] => 2018-09-19T23:59:59+02:00 )
FERAM=Array ( [tag] => FERAM:LATESTDELIVERYDATETIME [type] => complete [level] => 8 [value] => 2018-09-20T08:00:00+02:00 )
FERAM=Array ( [tag] => FERAM:DELIVERY [type] => close [level] => 7 )
FERAM=Array ( [tag] => FERAM:STATUS [type] => open [level] => 7 )
FERAM=Array ( [tag] => FERAM:CONDITIONCODE [type] => complete [level] => 8 [value] => 71 )
FERAM=Array ( [tag] => FERAM:DESCRIPTIONTEXT [type] => complete [level] => 8 [value] => available for ordering )
FERAM=Array ( [tag] => FERAM:STATUS [type] => close [level] => 7 )
*/

    