<?php
class HTTPRequest
{
	private $_ip = array(
		'120.24.156.31',
		'120.24.209.237',
		'120.24.156.11',
		'120.24.221.124',
		'120.24.156.78',
		'120.25.58.6',
		'120.24.209.27',
		'120.25.58.65',
		'120.24.221.47',
		'120.25.58.87',
		'120.25.158.16',
		'120.25.128.26',
		'120.25.108.36',
		'120.25.145.46',
		'120.25.165.56',
		'120.25.144.66',
		'120.25.198.76',
		'120.25.177.86'
	);
	
	private $_proxy_name; 
    private $_proxy_port;
	
	private $_host;
	private $_scheme;
	private $_cookies = array();
	private $_redirect_num = 0;

    public function HTTPRequest(){
		//$this->_ip = include __DIR__.'/../conf/ip.php';
	}

    /*
     * 使用http接口请求数据
     * 可以在$url按格式指定协议与端口 https://domain.com:443/path?querystring
     * 返回array
     */
    public function request($url = '', $post = array(), $method = 'GET', $header = array(), $timeout = 20, $http_version = 'HTTP/1.1'){
        if (empty($url)) return array('error' => 'url必须指定');
        $url = parse_url($url);
        $method = strtoupper(trim($method));
        $method = empty($method) ? 'GET' : $method;
        $this->_scheme = $scheme = strtolower($url['scheme']);
        $this->_host = $host = $url['host'];
        $path = $url['path'];
        empty($path) and ($path = '/');
        $query = $url['query'];
        $port = isset($url['port']) ? (int)$url['port'] : ('https' == $scheme ? 443 : 80);
        $protocol = 'https' == $scheme ? 'tlsv1.2://' : '';    //stream_get_transports();   tlsv1.2

        if (!$res = fsockopen($protocol . $host, (int)$port, $errno, $errstr, (int)$timeout)) {
            $res = array('error' => $errstr, 'errorno' => $errno);
			if(isset($_GET['debug']) && $_GET['debug'] == 1){
				echo '<pre>';
				print_r($res);
				exit;
			}
			return $res;
        } else {
			//stream_set_timeout($res,$timeout);		
			$ip = $this->_ip[array_rand($this->_ip)];
			
            $crlf = "\r\n";
            $commonHeader = array(
            'Host' => $host,
			'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.71 Safari/537.36',  //Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4           
			'Content-Type' => 'POST' == $method ? 'application/x-www-form-urlencoded' : 'text/html; charsert=UTF-8',
			'Accept' => '*/*', //text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
			//'X-Requested-With' => 'XMLHttpRequest',
			'Accept-Language' => 'zh-CN,zh;q=0.8',
			//'Client-IP' => $ip,
			//'X-Forwarded-For' => $ip,
			'Cache-Control' => 'max-age=0',
			'Pragma' => 'no-cache',
            'Connection' => 'Close'  //keep-alive
            );
			
			$cookies = $this->get_tmp_cookies();
			//$cookies = $this->import_cookies_json();
			if(isset($header['cookie']) && !empty($cookies)){
				$header['cookie'] = $cookies.';'.$header['cookie'];

			}else{
				if(!empty($cookies)){
					$header['cookie'] = $cookies;	
				}
			}
			 
            is_array($header) and ($commonHeader = array_merge($commonHeader, $header));

            foreach ($commonHeader as $key => & $val) {
                $val = str_replace(array("\n", "\r"), '', $val);
                $key = str_replace(array("\n", "\r", ':'), '', $key);
                $val = "{$key}: {$val}{$crlf}";
            }

            switch ($method) {
                case 'POST':
                    $post = http_build_query($post);
                    $query = empty($query) ? '' : '?' . $query;
                    $commonHeader[] = 'Content-Length: ' . strlen($post) . $crlf;
                    $post = empty($post) ? '' : $crlf . $post;
                    $commonHeader = implode('', $commonHeader);
                    $request = "{$method} {$path}{$query} {$http_version}{$crlf}"
                        . "{$commonHeader}"
                        . $post;//表示提交结束了
                    break;
                case 'GET':
                default:
                    empty($query) ? ($query = array()) : parse_str($query, $query);
					empty($post) ? ($post = array()) : $post;
                    $query = array_merge($query, $post);
                    $query = http_build_query($query);
                    $commonHeader = implode('', $commonHeader);
                    $query = empty($query) ? '' : '?' . $query;
                    $request = "{$method} {$path}{$query} {$http_version}{$crlf}"
                        . "{$commonHeader}"
                        . $crlf;//表示提交结束了
            }

            fwrite($res, $request);
            $reponse = '';

            while (!feof($res)) {
                $reponse .= fgets($res, 512);
            }

            fclose($res);
			
			if(isset($_GET['debug']) && $_GET['debug'] == 1){
				echo '<pre>';
				echo gethostbyname(gethostname());
				echo '<br/>';
				print_r($request);
				print_r($reponse);
			}
			
            $pos = strpos($reponse, $crlf . $crlf);//查找第一个分隔
            if ($pos === false) return array('reponse' => $reponse);
            $header = substr($reponse, 0, $pos);
            $body = substr($reponse, $pos + 2 * strlen($crlf));

            $result = $this->response(array('body' => $body, 'header' => $header));
			return $result;
        }
    }
	
	/**
	* 通过代理访问
	*/
	public function get_url_via_proxy($url) { 

		$proxy_fp = fsockopen($this->get_proxy_name(), $this->get_proxy_port()); 

		if (!$proxy_fp) { 
			return false; 
		} 
		fputs($proxy_fp, "GET " . $url . " HTTP/1.0\r\nHost: " . $this->get_proxy_name() . "\r\n\r\n"); 
		$proxy_cont = '';
		while (!feof($proxy_fp)) { 
			$proxy_cont .= fread($proxy_fp, 4096); 
		} 
		fclose($proxy_fp); 
		
		$proxy_cont = substr($proxy_cont, strpos($proxy_cont, "\r\n\r\n") + 4); 
		return $proxy_cont; 
    } 
	
	public function get_proxy_name() { 
        return $this->_proxy_name; 
    } 

    public function set_proxy_name($n) { 
        $this->_proxy_name = $n; 
    } 

    public function get_proxy_port() { 
        return $this->_proxy_port; 
    } 

    public function set_proxy_port($p) { 
        $this->_proxy_port = $p; 
    } 
	
    public function http_parse_headers($raw_headers){
        $headers = array();
        $key = '';

        foreach (explode("\n", $raw_headers) as $i => $h) {
            $h = explode(':', $h, 2);

            if (isset($h[1])) {
                if (!isset($headers[$h[0]]))
                    $headers[$h[0]] = trim($h[1]);
                elseif (is_array($headers[$h[0]])) {
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1])));
                } else {
                    $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1])));
                }

                $key = $h[0];
            } else {
                if (substr($h[0], 0, 1) == "\t")
                    $headers[$key] .= "\r\n\t" . trim($h[0]);
                elseif (!$key)
                    $headers[0] = trim($h[0]);
            }
        }

        if (!isset($headers['Status'])) {
            preg_match('#^HTTP/1\.\d[ \t]+(\d+)#i', $headers[0], $matches);
            if (!empty($matches))
                $headers['Status'] = (int)$matches[1];
        }

        return $headers;
    }
	
	/**
	 * 解码chunked数据
	 * @param string $data
	 * @return string
	 */
	public function http_chunked_decode($data){
		$pos=0;
		$temp='';
		while($pos<strlen($data))
		{
			// chunk部分(不包含CRLF)的长度,即"chunk-size [ chunk-extension ]"
			$len=strpos($data,"\r\n",$pos)-$pos;
			// 截取"chunk-size [ chunk-extension ]"
			$str=substr($data,$pos,$len);
			// 移动游标
			$pos+=$len+2;
			// 按;分割,得到的数组中的第一个元素为chunk-size的十六进制字符串
			$arr=explode(';',$str,2);
			// 将十六进制字符串转换为十进制数值
			$len=hexdec($arr[0]);
			// 截取chunk-data
			$temp.=substr($data,$pos,$len);
			// 移动游标
			$pos+=$len+2;
		}
		return $temp;
	}

	public function unchunk($data) {
		return preg_replace_callback(
			'/(?:(?:\r\n|\n)|^)([0-9A-F]+)(?:\r\n|\n){1,2}(.*?)'.
			'((?:\r\n|\n)(?:[0-9A-F]+(?:\r\n|\n))|$)/si',
			create_function(
				'$matches',
				'return hexdec($matches[1]) == strlen($matches[2]) ? $matches[2] : $matches[0];'
			),
			$data
		);
	}
	
    private function response($response){
		$headers = $this->http_parse_headers($response['header']);
		
		$this->get_response_cookies($headers);

        if ($headers['Status'] >= 200 && $headers['Status'] < 300) {
			$this->_redirect_num = 0;
			$this->save_tmp_cookies();
			$this->_cookies = array();
			if(isset($headers['Transfer-Encoding']) && $headers['Transfer-Encoding'] == 'chunked'){
				$response['body'] = $this->http_chunked_decode($response['body']);
			}
			$response['header'] = $headers;
			return $response;
        } elseif ($headers['Status'] >= 300 && $headers['Status'] < 400 && $this->_redirect_num < 20) {
			$this->_redirect_num = $this->_redirect_num + 1;
			$url = $headers['Location']?$headers['Location']:$headers['location'];
			$tmp_url = parse_url($url);
			if(empty($tmp_url['host'])){
				$url = $this->_scheme.'://'.$this->_host.'/'.$url;
			}
		
            return $this->request($url);
        } else {
            return null;
        }
    }

	private function get_response_cookies($headers){
		$key = $this->get_domain($this->_host);
		if(is_array($headers['Set-Cookie'])){
			foreach($headers['Set-Cookie'] as $v){
				$temp = explode(';',trim($v));
				if($temp[0])
					$this->_cookies[$key][] = $temp[0];
			}
		}else{
			$temp = explode(';',trim($headers['Set-Cookie']));
			if($temp[0])
				$this->_cookies[$key][] = $temp[0];
		}
	}
 	
	private function get_tmp_cookies(){
		$data = array();
		$path = $this->get_tmp_cookies_path();
		if(file_exists($path)){
			$data = file_get_contents($path);
		}

		$key = $this->get_domain();
		if(!empty($this->_cookies[$key]))
			$data .= ';'.implode(';',$this->_cookies[$key]);
		
		return $data;
	}
 
	public function import_cookies_json(){
		$key = $this->get_domain();
		$path = __DIR__.'/../conf/'.$key.'_cookies.json';		
		if(!file_exists($path)){
			return null;
		}

		$data = file_get_contents($path);
		$data = json_decode($data,true);
		
		if(empty($data))
			return null;
		
		$arr = array();
		foreach($data as $v){
			$arr[] = $v['name'].'='.$v['value'];
		}
		
		return empty($arr) ? null : implode(';',$arr);
	}
 
	public function save_tmp_cookies($force = false){
		foreach($this->_cookies as $k => $v){
			
			if(empty($v))
				continue;

			$path = $this->get_tmp_cookies_path($k);
			if($force){
				file_put_contents($path, implode(';',$v));
				continue;
			}

			if(!file_exists($path) || date("Ymd", filemtime($path)) != date('Ymd')){
				file_put_contents($path, implode(';',$v));
				continue;
			}

			$data = file($path);
			if(empty($data)){
				file_put_contents($path, implode(';',$v));
			}
		}
	}

	public function get_tmp_cookies_path($host = null){
		return '/tmp/'.$this->get_domain($host).'.cookies';	
	}

	public function get_domain($host = null){
		if(empty($host))
			$host = $this->_host;
		
		if($host == 'taobao')
			return $host;
		
		if(strpos($host,'.taobao.com') !== false){
			return 'taobao';
		}
		
		if($host == 'tmall')
			return $host;
		
		if(strpos($host,'.tmall.com') !== false){
			return 'tmall';
		}

		return $host;	
	}

	public function get($url,$header = array()){
		 $crlf = "\r\n";
		$commonHeader = array(        
			'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
			'Accept-Language' => 'zh-CN,zh;q=0.8',
			'Connection' => 'Close' 
		);
		
		is_array($header) and ($commonHeader = array_merge($commonHeader, $header));
					
		foreach ($commonHeader as $key => & $val) {
                $val = str_replace(array("\n", "\r"), '', $val);
                $key = str_replace(array("\n", "\r", ':'), '', $key);
                $val = "{$key}: {$val}{$crlf}";
		}
	
		$url_arr = parse_url($url);
		$scheme = strtolower($url_arr['scheme']);

		$opt = array(
			$scheme => array(
				'method' => 'GET',
				'timeout' => 10,
				'header' => implode('',$commonHeader)
			),
		);
		$context = stream_context_create($opt);

		$fp = fopen($url,'b',false,$context);
		if (!$fp) { 
			return false;
		}
		
		$response = '';
		stream_set_blocking($fp, 1);
		stream_set_timeout($fp, 10);
		
		//$status = stream_get_meta_data($fp);

		while (!feof($fp)) { 
			$data = fgets($fp, 512);
			$response .= $data;
		}  
		
		fclose($fp); 

		return $response;
	}
	
	public function get_cookies($url){
		return get_headers($url, 1);
	}
}
