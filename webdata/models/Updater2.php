<?php

class Updater2
{
    protected static $_last_fetch = null;

    public static function parseBussinessFile($content)
    {
        $doc = new DOMDocument;
        $content = str_replace('<head>', '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">', $content);

        $info = new StdClass;

        @$doc->loadHTML($content);
        if ($doc->getElementById('tabBusmContent')) {
            foreach ($doc->getElementById('tabBusmContent')->getElementsByTagName('tbody')->item(0)->childNodes as $tr_dom) {
                if ($tr_dom->nodeName != 'tr') {
                    continue;
                }
                $td_doms = $tr_dom->getElementsByTagName('td');
                if ($td_doms->length < 2) {
                    continue;
                }
                $key = trim($td_doms->item(0)->nodeValue);
                $value = trim($td_doms->item(1)->childNodes->item(0)->nodeValue);

                if (preg_match("#^(\d+)年(\d+)月(\d+)日$#", $value, $matches) or in_array($key, array(
                    '核准許可報備日期', '最後核准變更日期', '核准許可日期', '停業日期(起)', '停業日期(迄)',
                    '核准登記日期', '核准設立日期', '最後核准變更日期', '核准報備日期', '核准認許日期', '停業日期(起)', '停業日期(迄)',
                    '核准設立日期', '最後核准變更日期', '停業日期(起)', '停業日期(迄)', '延展開業日期(迄)',
                    '最近異動日期',
                ))) {
                    $value = array(
                        'year' => intval($matches[1]) + 1911,
                        'month' => intval($matches[2]),
                        'day' => intval($matches[3]),
                    );
                } else if (in_array($key, array('負責人姓名', '合夥人姓名'))) {
                    foreach ($td_doms->item(1)->getElementsByTagName('tr') as $name_tr_dom) {
                        if (!$name = $name_tr_dom->getElementsByTagName('td')->item(0)->nodeValue) {
                            continue;
                        }
                        $amount = explode(':', $name_tr_dom->getElementsByTagName('td')->item(1)->nodeValue)[1];

                        if (!property_exists($info, '出資額(元)')) {
                            $info->{'出資額(元)'} = new StdClass;
                        }
                        $info->{'出資額(元)'}->{$name} = str_replace(',', '', $amount);

                        if (property_exists($info, $key)) {
                            if (is_scalar($info->{$key})) {
                                $info->{$key} = array($info->{$key});
                            }
                            $info->{$key}[] = $name;
                        } else {
                            $info->{$key} = $name;
                        }
                    }
                    continue;
                } elseif (in_array($key, array('營業項目'))) {
                    $value = trim($td_doms->item(1)->nodeValue);
                }

                $info->{$key} = $value;
            }
        }
        $info->{'商業統一編號'} = str_replace(html_entity_decode('&nbsp;'), '', $info->{'商業統一編號'});

        return $info;
    }

    public static function parseBranchFile($content)
    {
        $doc = new DOMDocument;
        $content = str_replace('<head>', '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">', $content);

        $info = new StdClass;

        @$doc->loadHTML($content);
        if ($doc->getElementById('tabCmpyContent')) {
            foreach ($doc->getElementById('tabCmpyContent')->getElementsByTagName('tbody')->item(0)->childNodes as $tr_dom) {
                if ($tr_dom->nodeName != 'tr') {
                    continue;
                }
                $td_doms = $tr_dom->getElementsByTagName('td');
                if ($td_doms->length < 2) {
                    continue;
                }
                $key = trim($td_doms->item(0)->nodeValue);
                $value = trim($td_doms->item(1)->childNodes->item(0)->nodeValue);

                if (preg_match("#^(\d+)年(\d+)月(\d+)日$#", $value, $matches) or in_array($key, array(
                    '核准許可報備日期', '最後核准變更日期', '核准許可日期', '停業日期(起)', '停業日期(迄)',
                    '核准登記日期', '核准設立日期', '最後核准變更日期', '核准報備日期', '核准認許日期', '停業日期(起)', '停業日期(迄)',
                    '核准設立日期', '最後核准變更日期', '停業日期(起)', '停業日期(迄)', '延展開業日期(迄)'))) {
                    $value = array(
                        'year' => intval($matches[1]) + 1911,
                        'month' => intval($matches[2]),
                        'day' => intval($matches[3]),
                    );
                } elseif ($key == '總(本)公司統一編號') {
                    $value = trim($td_doms->item(1)->getElementsByTagName('a')->item(0)->nodeValue);
                }

                $info->{$key} = $value;
            }
        }
        unset($info->{'總(本)公司名稱'});
        $info->{'分公司統一編號'} = str_replace(html_entity_decode('&nbsp;'), '', $info->{'分公司統一編號'});

        return $info;
    }

    public static function parseFile($content)
    {
        $doc = new DOMDocument;
        $content = str_replace('<head>', '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">', $content);


        $info = new StdClass;

        @$doc->loadHTML($content);
        if ($doc->getElementById('tabCmpyContent')) {
            foreach ($doc->getElementById('tabCmpyContent')->getElementsByTagName('tbody')->item(0)->childNodes as $tr_dom) {
                if ($tr_dom->nodeName != 'tr') {
                    continue;
                }
                $td_doms = $tr_dom->getElementsByTagName('td');
                if ($td_doms->length < 2) {
                    continue;
                }
                $key = trim($td_doms->item(0)->nodeValue);
                $value = trim($td_doms->item(1)->childNodes->item(0)->nodeValue);

                if (preg_match("#^(\d+)年(\d+)月(\d+)日$#", $value, $matches) or in_array($key, array(
                    '核准許可報備日期', '最後核准變更日期', '核准許可日期', '停業日期(起)', '停業日期(迄)',
                    '核准登記日期', '核准設立日期', '最後核准變更日期', '核准報備日期', '核准認許日期', '停業日期(起)', '停業日期(迄)',
                    '核准設立日期', '最後核准變更日期', '停業日期(起)', '停業日期(迄)', '延展開業日期(迄)'))) {
                    $value = array(
                        'year' => intval($matches[1]) + 1911,
                        'month' => intval($matches[2]),
                        'day' => intval($matches[3]),
                    );
                } elseif ($key == '所營事業資料') {
                    $list = array();
                    foreach ($td_doms->item(1)->childNodes as $node) {
                        if ($node->nodeValue == 'br') {
                            continue;
                        }
                        $lines = explode("\n", trim($node->wholeText));
                        if (!preg_match('#^([A-Z0-9]*)#', trim($lines[0]), $matches)) {
                            throw new Exception('事業代號不正確');
                        }
                        if (trim($lines[1]) == '') {
                            continue;
                        }
                        $list[] = array($matches[1], trim($lines[1]));
                    }
                    $value = $list;
                } elseif (in_array($key, array('在中華民國境內負責人', '在中華民國境內代表人', '訴訟及非訴訟代理人姓名'))) {
                    $lines = explode("\n", trim($value));
                    if (count($lines) > 1) {
                        $value = array(
                            trim($lines[0]),
                            trim($lines[count($lines) - 1]),
                        );
                    }
                } elseif ($key == '公司名稱') {
                    $dom = $doc->getElementById('linkGoogleSearch');
                    while ($dom = $dom->nextSibling) {
                        if ($dom->nodeName == 'span' and $dom->getAttribute('id') == 'linkMoea') {
                            break;
                        }
                        if ($dom->nodeName == 'br') {
                            if (preg_match('#^(.*)\((.*)\)$#', trim($value), $matches1) and 
                                preg_match('#^(.*)\((.*)\)$#', trim($dom->nextSibling->nodeValue), $matches2)) {
                                $value = array(
                                    array(trim($matches1[1]), trim($matches1[2])),
                                    array(trim($matches2[1]), trim($matches2[2])),
                                );
                            } else {
                                $value = array($value, trim($dom->nextSibling->nodeValue));
                            }
                            break;
                        }
                    }
                }

                $info->{$key} = $value;
            }
        }

        if ($doc->getElementById('tabShareHolderContent')) {
            $list = array();
            foreach ($doc->getElementById('tabShareHolderContent')->getElementsByTagName('tbody')->item(0)->childNodes as $tr_dom) {
                if ($tr_dom->nodeName != 'tr') {
                    continue;
                }
                $td_doms = $tr_dom->getElementsByTagName('td');
                if ($td_doms->length != 5) {
                    continue;
                }
                $row = new StdClass;
                $row->{'序號'} = trim($td_doms->item(0)->nodeValue);
                $row->{'職稱'} = trim($td_doms->item(1)->nodeValue);
                $row->{'姓名'} = trim($td_doms->item(2)->nodeValue);
                if (trim($td_doms->item(3)->nodeValue) != '') {
                    $a_dom = $td_doms->item(3)->getElementsByTagName('a')->item(0);
                    if (!$a_dom) {
                        $row->{'所代表法人'} = array(0, trim($td_doms->item(3)->nodeValue));
                    } else {
                        $link = $a_dom->getAttribute('onclick');
                        if (!preg_match('#queryCmpy\(\'[^\']*\',\'([^\']*)#', $link, $matches)) {
                            throw new Exception('請處理法人');
                        }
                        $row->{'所代表法人'} = array($matches[1], trim($a_dom->nodeValue));
                    }
                } else {
                    $row->{'所代表法人'} = '';
                }
                $row->{'出資額'} = trim($td_doms->item(4)->nodeValue);
                $list[] = $row;
            }
            $info->{'董監事名單'} = $list;
        }

        if ($doc->getElementById('tabBrCmpyContent')) {
            if ($doc->getElementById('tabBrCmpyContent')->getElementsByTagName('a')->length) {
                $info->_has_branch = true;
            }
        }

        if ($doc->getElementById('tabMgrContent')) {
            $list = array();
            foreach ($doc->getElementById('tabMgrContent')->getElementsByTagName('tbody')->item(0)->childNodes as $tr_dom) {
                if ($tr_dom->nodeName != 'tr') {
                    continue;
                }
                $td_doms = $tr_dom->getElementsByTagName('td');
                if ($td_doms->length != 3) {
                    continue;
                }
                $row = new StdClass;
                $row->{'序號'} = trim($td_doms->item(0)->nodeValue);
                $row->{'姓名'} = trim($td_doms->item(1)->nodeValue);
                if (!preg_match('#(.*)年(.*)月(.*)日#', trim($td_doms->item(2)->nodeValue), $matches)) {
                    $row->{'到職日期'} = null;
                } else {
                    $value = new stdClass;
                    $value->year = 1911 + intval($matches[1]);
                    $value->month = intval($matches[2]);
                    $value->day = intval($matches[3]);
                    $row->{'到職日期'} = $value;
                }
                $list[] = $row;
            }
            $info->{'經理人名單'} = $list;
        }

        $info->{'統一編號'} = str_replace(html_entity_decode('&nbsp;'), '', $info->{'統一編號'});
        return $info;
    }

    protected static $_curl = null;
    protected static $_agency_map = null;

    public static function getCURL($reconnect = false)
    {
        if (!$reconnect and !is_null(self::$_curl)) {
            return self::$_curl;
        }
        error_log("init curl");

        // 先取得 session
        $url = 'https://findbiz.nat.gov.tw/fts/query/QueryBar/queryInit.do';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
        curl_setopt($curl, CURLOPT_COOKIEFILE, '');
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Origin: https://findbiz.nat.gov.tw',
        ]);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:150.0) Gecko/20100101 Firefox/150.0');
        curl_setopt($curl, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
        if (getenv('PROXY_URL')) {
            curl_setopt($curl, CURLOPT_PROXY, getenv('PROXY_URL'));
        }
        //curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        // ignore certificate check
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($curl);
        $info = curl_getinfo($curl);

        // 再來跳轉到驗證頁面
        sleep(2);
        $url = "https://findbiz.nat.gov.tw/fts/query/QueryList/queryList.do";
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($curl);
        $info = curl_getinfo($curl);

        self::$_curl = $curl;
        return $curl;
    }

    public static function updateBussiness($id, $options = array(), $county = null)
    {
        $info = self::parseAPIBussiness($id, $county);
        if (!$info) {
            trigger_error("API 找不到商業登記: $id", E_USER_WARNING);
            return;
        }

        if (!$unit = Unit::find($id)) {
            $unit = Unit::insert(array(
                'id' => $id,
                'type' => 2,
            ));
        }
        $unit->updateData($info);
        return $unit;
    }

    public static function updateBranch($id, $options = array(), $parent_id = null)
    {
        if (!$parent_id) {
            $unit = Unit::find($id);
            if ($unit) {
                $db_data = $unit->getData();
                if (property_exists($db_data, '總(本)公司統一編號')) {
                    $parent_id = $db_data->{'總(本)公司統一編號'};
                }
            }
        }
        if (!$parent_id) {
            trigger_error("找不到分公司的總公司統一編號: $id", E_USER_WARNING);
            return;
        }

        $branch_data_list = self::fetchAPIRaw('FDB8D2C8-573D-4276-BFA4-8D3925ABE1CB', $parent_id);
        $branch_data = null;
        foreach ($branch_data_list as $branch) {
            $branch_id = str_pad($branch['Branch_Office_Business_Accounting_NO'], 8, '0', STR_PAD_LEFT);
            if ($branch_id === $id) {
                $branch_data = $branch;
                break;
            }
        }
        if (!$branch_data) {
            trigger_error("API 找不到分公司資料: $id (parent: $parent_id)", E_USER_WARNING);
            return;
        }

        $info = self::parseAPIBranch($branch_data, $parent_id);

        if (!$unit = Unit::find($id)) {
            $unit = Unit::insert(array('id' => $id, 'type' => 3));
        } else {
            $unit->update(array('type' => 3));
        }
        $unit->updateData($info);
        return $unit;
    }

    public static function update($id, $options = array())
    {
        $unit = Unit::find($id);
        if ($unit) {
            $modified_at = $unit->updated_at;
            if (array_key_exists('month', $options)) {
                $query_time = strtotime('+1 month', mktime(0, 0, 0, $options['month'], 1, $options['year']));
                if ($query_time < $modified_at) {
                    return;
                }
            }
        }

        $info = self::parseAPICompany($id);
        if (!$info) {
            trigger_error("API 找不到公司: $id", E_USER_WARNING);
            return;
        }

        if (!$unit = Unit::find($id)) {
            $unit = Unit::insert(array(
                'id' => $id,
                'type' => 1,
            ));
        } else {
            $unit->update(array('type' => 1));
        }

        // 一次 API 取得所有分公司資料
        $branch_data_list = self::fetchAPIRaw('FDB8D2C8-573D-4276-BFA4-8D3925ABE1CB', $id);
        $branches = array();
        foreach ($branch_data_list as $branch) {
            $branch_id = str_pad($branch['Branch_Office_Business_Accounting_NO'], 8, '0', STR_PAD_LEFT);
            $branches[$branch_id] = $branch;
        }

        if (!array_key_exists($unit->id(), $branches)) {
            $unit->updateData($info);
        }
        foreach ($branches as $branch_id => $branch) {
            if ($branch_id == $unit->id()) {
                $info->{'分公司名稱'} = $branch['Branch_Office_Name'];
                $unit->updateData($info);
                continue;
            }
            $branch_info = self::parseAPIBranch($branch, $unit->id());
            if (!$branch_unit = Unit::find($branch_id)) {
                $branch_unit = Unit::insert(array('id' => $branch_id, 'type' => 3));
            } else {
                $branch_unit->update(array('type' => 3));
            }
            $branch_unit->updateData($branch_info);
        }
        return $unit;
    }

    public static function http($url)
    {
        for ($i = 0; $i < 10; $i ++) {
            error_log('Fetching ' . $url . " time: {$i}");
            $curl = curl_init($url);
            if (getenv('PROXY_URL')) {
                curl_setopt($curl, CURLOPT_PROXY, getenv('PROXY_URL'));
            }
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 20);
            //curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:150.0) Gecko/20100101 Firefox/150.0');
            curl_setopt($curl, CURLOPT_REFERER, $url); //'https://gcis.nat.gov.tw/pub/cmpy/cmpyInfoListAction.do');
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $content = curl_exec($curl);
            $info = curl_getinfo($curl);
            curl_close($curl);
            if (200 == $info['http_code']) {
                return $content;
            }
            if ($i) {
                echo json_encode($info) . "\n";
                sleep($i);
            }
        }
        throw new Exception("fetch 3 times failed");
    }

    public static function searchBranch($id)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $ids = array();
        for ($page = 1; ; $page ++) {
            sleep(1);
            curl_setopt($curl, CURLOPT_URL, 'https://findbiz.nat.gov.tw/fts/query/QueryCmpyDetail/queryCmpyDetail.do');
            curl_setopt($curl, CURLOPT_REFERER, 'https://findbiz.nat.gov.tw/fts/query/QueryCmpyDetail/queryCmpyDetail.do');
            curl_setopt($curl, CURLOPT_POST, true);
            //curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:150.0) Gecko/20100101 Firefox/150.0');
            curl_setopt($curl, CURLOPT_POSTFIELDS, "banNo={$id}&brBanNo=&banKey=&estbId=&objectId=&CPage={$page}&brCmpyPage=Y&eng=false&CPageHistory=&historyPage=&chgAppDate=");
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $content = curl_exec($curl);
            $content = str_replace('<head>', '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">', $content);
            $doc = new DOMDocument;
            @$doc->loadHTML($content);
            if (!$dom = $doc->getElementById('tabBrCmpyContent')) {
                break;
            }
            $hit = false;
            foreach ($dom->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr') as $tr_dom) {
                $td_doms = $tr_dom->getElementsByTagName('td');
                if (!$td_doms->item(2)) {
                    continue;
                }
                if (!$a_dom = $td_doms->item(2)->getElementsByTagName('a')->item(0)) {
                    continue;
                }
                $hit = true;
                preg_match('#queryBranch\(\'([0-9]{8})#', $a_dom->getAttribute('onclick'), $matches);
                $ids[$matches[1]] = trim($a_dom->nodeValue);
            }
            if (!$hit) {
                break;
            }
        }

        return $ids;
    }

    // -------------------------------------------------------------------------
    // API-based methods (data.gcis.nat.gov.tw)
    // -------------------------------------------------------------------------

    private static function parseROCDate($str)
    {
        $str = trim($str);
        if ($str === '') {
            return null;
        }
        return [
            'year'  => intval(substr($str, 0, 3)) + 1911,
            'month' => intval(substr($str, 3, 2)),
            'day'   => intval(substr($str, 5, 2)),
        ];
    }

    public static function fetchAPIRaw($uuid, $id)
    {
        $url = "https://data.gcis.nat.gov.tw/od/data/api/{$uuid}?\$format=json&\$filter=Business_Accounting_NO%20eq%20{$id}";
        $content = self::http($url);
        return json_decode($content, true) ?: [];
    }

    public static function parseAPICompany($id)
    {
        $api1 = self::fetchAPIRaw('236EE382-4942-41A9-BD03-CA0709025E7C', $id); // 行業別
        $api3 = self::fetchAPIRaw('5F64D864-61CB-4D0D-8AD9-492047CC1EA6', $id); // 資本額/基本資料
        $api4 = self::fetchAPIRaw('4E5F7653-1B91-4DDC-99D5-468530FAE396', $id); // 董監事

        if (empty($api3)) {
            return null;
        }

        $d = $api3[0];
        $info = new StdClass;

        $info->{'公司名稱'}         = $d['Company_Name'];
        $info->{'登記現況'}         = $d['Company_Status_Desc'];
        $info->{'登記機關'}         = $d['Register_Organization_Desc'];
        $info->{'資本總額(元)'}     = number_format($d['Capital_Stock_Amount']);
        if ($d['Paid_In_Capital_Amount'] > 0) {
            $info->{'實收資本額(元)'} = number_format($d['Paid_In_Capital_Amount']);
        }
        $info->{'代表人姓名'}       = $d['Responsible_Name'];
        $info->{'公司所在地'}       = $d['Company_Location'];
        $info->{'核准設立日期'}     = self::parseROCDate($d['Company_Setup_Date']);
        $info->{'最後核准變更日期'} = self::parseROCDate($d['Change_Of_Approval_Data']);

        if ($date = self::parseROCDate($d['Revoke_App_Date'])) {
            $info->{'廢止日期'} = $date;
        }
        if ($date = self::parseROCDate($d['Sus_Beg_Date'])) {
            $info->{'停業日期(起)'} = $date;
        }
        if ($date = self::parseROCDate($d['Sus_End_Date'])) {
            $info->{'停業日期(迄)'} = $date;
        }

        $info->{'所營事業資料'} = [];
        if (!empty($api1[0]['Cmp_Business'])) {
            foreach ($api1[0]['Cmp_Business'] as $item) {
                $info->{'所營事業資料'}[] = [trim($item['Business_Item']), $item['Business_Item_Desc']];
            }
        }

        $info->{'經理人名單'} = [];

        $info->{'董監事名單'} = [];
        foreach ($api4 as $i => $dir) {
            $row = new StdClass;
            $row->{'序號'}       = str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            $row->{'職稱'}       = $dir['Person_Position_Name'];
            $row->{'姓名'}       = $dir['Person_Name'];
            $row->{'所代表法人'} = empty($dir['Juristic_Person_Name'])
                ? ''
                : [0, $dir['Juristic_Person_Name']];
            $row->{'出資額'}     = number_format($dir['Person_Shareholding']);
            $info->{'董監事名單'}[] = $row;
        }

        return $info;
    }

    private static function loadAgencyCodes()
    {
        if (!is_null(self::$_agency_map)) {
            return self::$_agency_map;
        }
        self::$_agency_map = ['by_code' => [], 'by_name' => [], 'by_county' => []];
        $fp = fopen(__DIR__ . '/../maps/agency.csv', 'r');
        fgetcsv($fp); // skip header
        while ($row = fgetcsv($fp)) {
            if (count($row) < 2) continue;
            [$code, $name] = $row;
            self::$_agency_map['by_code'][$code] = $name;
            self::$_agency_map['by_name'][$name]  = $code;

            // 縣市名稱：前3字，福建省例外取第4-6字
            $county = mb_substr($name, 0, 3, 'UTF-8') === '福建省'
                ? mb_substr($name, 3, 3, 'UTF-8')
                : mb_substr($name, 0, 3, 'UTF-8');
            self::$_agency_map['by_county'][$county] = $code;
        }
        fclose($fp);
        return self::$_agency_map;
    }

    private static function normalizeCounty($county)
    {
        return str_replace('台', '臺', trim($county));
    }

    private static function findAgencyCode($id, $county = null)
    {
        $map = self::loadAgencyCodes();

        // 有指定縣市就直接查，O(1)
        if ($county) {
            $county = self::normalizeCounty($county);
            if (isset($map['by_county'][$county])) {
                return $map['by_county'][$county];
            }
        }

        // 查 DB 已存的登記機關，反查 agency code
        $unit = Unit::find($id);
        if ($unit) {
            $db_data = $unit->getData();
            if (property_exists($db_data, '登記機關')) {
                $agency_name = $db_data->{'登記機關'};
                if (isset($map['by_name'][$agency_name])) {
                    return $map['by_name'][$agency_name];
                }
            }
        }

        // 最後才逐一嘗試所有 agency（最多 23 次）
        foreach ($map['by_code'] as $code => $name) {
            $url = "https://data.gcis.nat.gov.tw/od/data/api/7E6AFA72-AD6A-46D3-8681-ED77951D912D?\$format=json&\$filter=President_No%20eq%20{$id}%20and%20Agency%20eq%20{$code}&\$top=1";
            $content = self::http($url);
            $data = json_decode($content, true);
            if (!empty($data)) {
                return $code;
            }
            sleep(1);
        }

        return null;
    }

    public static function fetchAPIBussinessRaw($uuid, $id, $agency)
    {
        $filter = "President_No%20eq%20{$id}%20and%20Agency%20eq%20{$agency}";
        $url = "https://data.gcis.nat.gov.tw/od/data/api/{$uuid}?\$format=json&\$filter={$filter}&\$top=50";
        $content = self::http($url);
        return json_decode($content, true) ?: [];
    }

    public static function parseAPIBussiness($id, $county = null)
    {
        $agency = self::findAgencyCode($id, $county);
        if (!$agency) {
            error_log("找不到 agency for bussiness {$id}");
            return null;
        }

        $api1 = self::fetchAPIBussinessRaw('7E6AFA72-AD6A-46D3-8681-ED77951D912D', $id, $agency); // 基本資料
        $api2 = self::fetchAPIBussinessRaw('F570BC9A-DA4C-4813-8087-FB9CE95F9D38', $id, $agency); // 營業項目

        if (empty($api1)) {
            return null;
        }

        $d = $api1[0];
        $info = new StdClass;

        $info->{'商業名稱'}     = $d['Business_Name'];
        $info->{'登記現況'}     = $d['Business_Current_Status_Desc'];
        $info->{'組織類型'}     = $d['Business_Organization_Type_Desc'];
        $info->{'登記機關'}     = $d['Agency_Desc'];
        $info->{'資本額(元)'}   = number_format($d['Business_Register_Funds']);
        $info->{'地址'}         = $d['Business_Address'];
        $info->{'核准設立日期'} = self::parseROCDate($d['Business_Setup_Approve_Date']);
        $info->{'最近異動日期'} = self::parseROCDate($d['Business_Last_Change_Date']);

        // 負責人/合夥人姓名 & 出資額(元)
        if (!empty($d['Business_Director']) && is_array($d['Business_Director'])) {
            $info->{'出資額(元)'} = new StdClass;
            foreach ($d['Business_Director'] as $dir) {
                $duty  = $dir['Business_Duty_Desc'];
                $name  = $dir['Name'];
                $funds = strval($dir['Funds']); // 不加逗號，對應 parseBussinessFile() 的 str_replace(',', '')

                $field = ($duty === '合夥人') ? '合夥人姓名' : '負責人姓名';
                if (property_exists($info, $field)) {
                    if (is_string($info->{$field})) {
                        $info->{$field} = [$info->{$field}];
                    }
                    $info->{$field}[] = $name;
                } else {
                    $info->{$field} = $name;
                }
                $info->{'出資額(元)'}->{$name} = $funds;
            }
        }

        // 營業項目：重組成 "CODE 名稱\nCODE 名稱" 字串
        if (!empty($api2) && !empty($api2[0]['Business_Item_Old']) && is_array($api2[0]['Business_Item_Old'])) {
            $lines = [];
            foreach ($api2[0]['Business_Item_Old'] as $item) {
                $lines[] = $item['Business_Item'] . ' ' . $item['Business_Item_Desc'];
            }
            $info->{'營業項目'} = implode("\n", $lines);
        }

        return $info;
    }

    public static function searchBranchAPI($id)
    {
        $branches = self::fetchAPIRaw('FDB8D2C8-573D-4276-BFA4-8D3925ABE1CB', $id);
        $ids = [];
        foreach ($branches as $branch) {
            $branch_id = str_pad($branch['Branch_Office_Business_Accounting_NO'], 8, '0', STR_PAD_LEFT);
            $ids[$branch_id] = $branch['Branch_Office_Name'];
        }
        return $ids;
    }

    public static function parseAPIBranch($branch_data, $parent_id)
    {
        $info = new StdClass;
        $info->{'分公司名稱'}         = $branch_data['Branch_Office_Name'];
        $info->{'分公司所在地'}       = $branch_data['Branch_Office_Location'];
        $info->{'分公司經理姓名'}     = $branch_data['Branch_Office_Manager_Name'];
        $info->{'分公司狀況'}         = $branch_data['Branch_Office_Status_Desc'];
        $info->{'總(本)公司統一編號'} = $parent_id;

        if ($date = self::parseROCDate($branch_data['BR_ESTAB_DATE'])) {
            $info->{'核准設立日期'} = $date;
        }
        if ($date = self::parseROCDate($branch_data['CHG_APP_DATE'])) {
            $info->{'最後核准變更日期'} = $date;
        }
        if ($date = self::parseROCDate($branch_data['Revoke_App_Date'])) {
            $info->{'廢止日期'} = $date;
        }

        return $info;
    }
}
