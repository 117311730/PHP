<?php

class GetTmallProduct{
	
	const ItemUrl = 'https://chaoshi.detail.tmall.com/item.htm?id=';
	const ItemDetailUrl = 'https://mdskip.taobao.com/core/initItemDetail.htm?offlineShop=false&addressLevel=3&tryBeforeBuy=false&isApparel=false&isSecKill=false&service3C=false&isUseInventoryCenter=true&isRegionLevel=true&household=false&sellerPreview=false&isForbidBuyItem=false&showShopProm=false&queryMemberRight=true&isAreaSell=true&cartEnable=true&progressiveSupport=false&tmallBuySupport=true&callback=&ref=&brandSiteId=0&itemId=%s&cachedTimestamp=%s';
	const MobileItemUrl = 'https://detail.m.tmall.com/item.htm?tbpm=3&id=';
	const SetCookiesUrl = 'https://chaoshi.tmall.com/nativeApp/getTodayCrazy.do';
	const ChangeLocationUrl = 'https://mdskip.taobao.com/core/changeLocation.htm?tmallBuySupport=true&addressLevel=3&offlineShop=false&notAllowOriginPrice=false&isAreaSell=true&isRegionLevel=true&isUseInventoryCenter=true&isSecKill=false&areaId=%s&itemId=%s';
	const SelectCityUrl = 'https://mdskip.taobao.com/core/selectCity.htm?callback=&isAreaSell=true&city=%s&itemId=%s';
	
	private $_id;
	private $_http_request;
	private $_headers = array();
	private $_product_info;
	private $_defaultModel;
	
	public function GetTmallProduct(){
		$this->_http_request = new HTTPRequest();
	}
	
	public function set_id($id){ //初始化当前ID信息
		$this->_id = $id;
		$this->_product_info = null;
		$this->_defaultModel = null;
	}
	
	public function set_area($area_id){
		$area_id = (int)$area_id;
		if($area_id <= 0)
			return ;
		$this->_headers['cookie'] .= 'sm4='.$area_id.';';
	}
	
	/**
	*	是否在售中
	*/
	public function is_area_sell(){
		$result = $this->get_data_detail();
		if(empty($result))
			return null;

		return (bool)$result['isAreaSell'];
	}
	
	public function get_price_quantity_sold_areas(){
		$result = $this->get_item_detail();
		if(empty($result))
			return null;
		return array(
			'price' => $this->get_price($result),
            'quantity' => $this->get_quantity($result),
            'sold_area' => $this->get_sold_area($result)
		);
	}
	
	public function get_market_price(){
		$result = $this->get_data_detail();
        if (!empty($result))
            return isset($result['itemDO']['reservePrice']) ? $result['itemDO']['reservePrice'] : null;
		return null;
	}
	
	/**获取商品的购买倍数
     * @return int
     */
    public function get_times_buy(){
        $result = $this->get_data_detail();
        if (!empty($result))
            return isset($result['itemDO']['timesBuy']) ? $result['itemDO']['timesBuy'] : 1;
        return 0;
    }
	
	/**获取商品的规格
     * @return Array
     */
	public function get_sku_list(){
		$result = $this->get_data_detail();
        if (empty($result))
			return null;
		
		$res['skuName'] = $result['valItemInfo']['skuName'] ? $result['valItemInfo']['skuName'] : null;
		$res['skuList'] = $result['valItemInfo']['skuList'] ? $result['valItemInfo']['skuList'] : null;
		return $res;
	}
	
	/**获取商品的主图
     * @return int
     */
	public function get_main_pic(){
		$result = $this->get_data_detail();
        if (empty($result))
            return null;
		return isset($result['itemDO']['mainPic']) ? $result['itemDO']['mainPic'] : null;
	}
	
    /**获取商品的描述
     * @return null
     */
    public function get_desc(){
        $result = $this->get_data_detail();
        if (empty($result))
            return null;

        $api_url = $result['api']['descUrl'];
        if (stripos($api_url, 'http') === false)
            $api_url = 'http:' . $api_url;
        $response = $this->_http_request->request($api_url);

        preg_match_all("/var desc='([\w\W]*)';/iU", $response['body'], $re);
        if (!empty($re[1][0])) {
            $data = iconv('GBK', 'utf-8//IGNORE', $re[1][0]);
			$re = null;
            return $this->remove_all_a_tag($data);
        }
        return null;
    }

    public function remove_all_a_tag($data){
        preg_match_all('/(<a.*?>).*?(<\/a>)/is', $data, $re);

        if (count($re[1]) > 0) {
            foreach ($re[1] as $v) {
                if (!empty($v)) {
                    $data = str_replace($v, '', $data);
                }
            }
        }
        if (count($re[2]) > 0) {
            foreach ($re[1] as $v) {
                if (!empty($v)) {
                    $data = str_replace($v, '', $data);
                }
            }
        }
		$re = null;
        return $data;
    }
	
	public function select_city($area_id){
		$url = sprintf(self::SelectCityUrl,$area_id,$this->_id);
		unset($this->_headers['cookie']);
		$this->_headers['referer'] = self::ItemUrl.$this->_id;
		$response = $this->_http_request->request($url,null,'GET',$this->_headers);
		return $this->handle_json_decode($response['body']);
	}
	
	public function change_location($area_id){
		$url = sprintf(self::ChangeLocationUrl,$area_id,$this->_id);
		unset($this->_headers['cookie']);
		$this->_headers['referer'] = self::ItemUrl.$this->_id;
		$response = $this->_http_request->request($url,null,'GET',$this->_headers);
		return $this->handle_json_decode($response['body']);
	}
	
	public function get_item_detail(){
		if($this->_id <= 0)
			return null;
		
		if(empty($this->_defaultModel)){
			$url = sprintf(self::ItemDetailUrl,$this->_id,$this->get_timestamp());
			$this->_headers['referer'] = self::ItemUrl.$this->_id;
			$response = $this->_http_request->request($url,null,'GET',$this->_headers);

			$this->_defaultModel = $this->handle_json_decode($response['body']);
		}
		
		return $this->_defaultModel;
	}
	
	public function get_data_detail(){
		if($this->_id <= 0)
			return null;
		if(!empty($this->_product_info))
			return $this->_product_info;
		$url = self::MobileItemUrl.$this->_id;
		$response = $this->_http_request->request($url,null,'GET',$this->_headers);

		$response['body'] = str_replace(array("\n", "\r"), '', $response['body']);

		preg_match_all("/var _DATA_Mdskip =[\s]*{([\w\W]*)}[\s]*</iU", $response['body'], $res);
		if (!empty($res[1][0])) {
			$this->_defaultModel = $this->handle_json_decode('{' . $res[1][0] . '}');
		}
		
		preg_match_all("/var _DATA_Detail = {([\w\W]*)};/iU", $response['body'], $res);
		if (!empty($res[1][0])) {
			$this->_product_info = $this->handle_json_decode('{' . $res[1][0] . '}');
			$res = null;
			return $this->_product_info;
		}
		return null;
	}
	
	/**
	* 优惠价
	*/
    private function get_price($result){
		if(!is_array($result['defaultModel']['itemPriceResultDO']['priceInfo']))
			return null;
		
        foreach ($result['defaultModel']['itemPriceResultDO']['priceInfo'] as $result_price) {
            if (isset($result_price['promotionList'])) {
                $price = $result_price['promotionList'][0]['price'];
            } else {
                $price = $result_price['price'];
            }
            break;
        }
        return $price;
    }
	
	/**
	* 优惠价,限购多少件
	*/
	private function get_amount_restriction($result){
		if(!is_array($result['defaultModel']['itemPriceResultDO']['priceInfo']))
			return null;
		
        foreach ($result['defaultModel']['itemPriceResultDO']['priceInfo'] as $result_price) {
            if (isset($result_price['promotionList'])) {
                return $result_price['promotionList'][0]['amountRestriction'];
            }
        }
        return null;
	}
	
	/**
	* 天猫促销信息
	*/
	private function get_tmall_shop_prom($result){
		if(isset($result['defaultModel']['itemPriceResultDO']['tmallShopProm']))
			return $result['defaultModel']['itemPriceResultDO']['tmallShopProm'];
		return null;
	}
	
	/**
	*  商品限购多少件
	*/
	private function get_amount_can_buy($result){
		if(isset($result['defaultModel']['buyerRestrictInfoDO']['amountRestrictInfoMap']['def']['amountCanBuy']))
			return $result['defaultModel']['buyerRestrictInfoDO']['amountRestrictInfoMap']['def']['amountCanBuy'];
		return null;
	}
	
	/**
	* 库存
	*/
    private function get_quantity($result){
        if (!empty($result['defaultModel']['inventoryDO']['icTotalQuantity']))
            return $result['defaultModel']['inventoryDO']['icTotalQuantity'];
        else
            return $result['defaultModel']['inventoryDO']['totalQuantity'];
    }
	
	/**
	* 销售地区
	*/
    private function get_sold_area($result){
        $sold_areas = $result['defaultModel']['soldAreaDataDO'];

		if($sold_areas['success'] !== true)
			return null;

        if(empty($sold_areas['cityData'])){
            return array('offline' => true);     //天猫下架了
        }
      //  return $sold_areas['cityData'];
        return $sold_areas;
    }
	
	/** 处理天猫的json 数据
     * @param $data
     * @return array|bool
     */
    private function handle_json_decode($data){
		if(empty($data)){
			return null;
		}
		
		$result = str_replace(array("\r\n", "\n", "\r", "\t", chr(9), chr(13)), '', $data);  //去除回车、空格等
		$data = null;
        //将json数据中，以纯数字为key的字段加上双引号，例如28523678201:{"areaSold":1}转为："28523678201":{"areaSold":1}，否则json_decode会出现错误
        $mode = "#^[\{,]([0-9]+)\:#m";
        preg_match_all($mode, $result, $s);
        $s = $s[1];
        if (count($s) > 0) {
            foreach ($s as $v) {
                $result = str_replace($v . ':', '"' . $v . '":', $result);
            }
        }
		$s = null;		
        //将字符编码转为utf-8，并且将中文转译，否则json_decode会出现错误
        $result = iconv('GBK', 'utf-8//IGNORE', $result);

        $str = array();
        $mode = '/([\x80-\xff]*)/i';
        if (preg_match_all($mode, $result, $s)) {
            foreach ($s[0] as $v) {
                if (!empty($v)) {
                    $str[base64_encode($v)] = $v;
                    $result = str_replace('"' . $v . '"', '"' . base64_encode($v) . '"', $result);
                }
            }
        }
		$s = null;

        $result = json_decode($result, true);

        //这里得到的数据中，中文数据被转译，下面将中文数据解析
        $result = $this->arr_foreach($result, $str);

        return $result;
    }

    private function arr_foreach($arr, $str){
        if (!is_array($arr)) {
            return false;
        }
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $arr[$key] = $this->arr_foreach($val, $str);
            } else {
                if (!empty($val)) {
                    if ($str[$val]) {
                        $arr[$key] = $str[$val];
                    }
                }
            }
        }
        return $arr;
	}

	//	获取毫秒的时间戳
	private function get_timestamp(){
		$time = explode(" ", microtime());
		$time = $time[1] . ($time [0] * 1000);
		$time2 = explode( ".", $time);
		$time = $time2[0];
		return $time;	
	}
}
