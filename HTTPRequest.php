<?php
class HTTPRequest
{
    /*
     * 使用http接口请求数据
     * 可以在$url按格式指定协议与端口 https://domain.com:443/path?querystring
     * 返回array
     * $method = 'PROXY'时,$post=" GET url  HTTP/1.0\r\n",也就是代理请求的方法自己设置
     */
    public function request($url = '', $post = null /* 数组*/, $method = 'GET', $header = null, $timeout = 20, $http_version = 'HTTP/1.0')
    {
        if (empty($url)) return array('error' => 'url必须指定');
        $url = parse_url($url);
        $method = strtoupper(trim($method));
        $method = empty($method) ? 'GET' : $method;
        $scheme = strtolower($url['scheme']);
        $host = $url['host'];
        $path = $url['path'];
        empty($path) and ($path = '/');
        $query = $url['query'];
        $port = isset($url['port']) ? (int)$url['port'] : ('https' == $scheme ? 443 : 80);
        $protocol = 'https' == $scheme ? 'ssl://' : '';

        if (!$res = fsockopen($protocol . $host, (int)$port, $errno, $errstr, (int)$timeout)) {
            return array('error' => mb_convert_encoding($errstr, 'UTF-8', 'UTF-8,GB2312'), 'errorno' => $errno);
        } else {
            $crlf = "\r\n";
            $commonHeader = $method == 'PROXY' ? array() : array(
            'Host' => $host,
            'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.85 Safari/537.36',  //Mozilla/5.0 (X11; Linux x86_64; rv:31.0) Gecko/20100101 Firefox/31.0
            'Content-Type' => 'POST' == $method ? 'application/x-www-form-urlencoded' : 'text/html; charsert=UTF-8',
			'Accept-Language' => 'zh-CN,zh;q=0.8',
			//'X-Forwarded-For' => '8.8.8.8',
			'Cache-Control' => 'max-age=0',
			'Pragma' => 'no-cache',
            'Connection' => 'Close'  //keep-alive
            );
            is_array($header) and ($commonHeader = array_merge($commonHeader, $header));

            foreach ($commonHeader as $key => & $val) {
                $val = str_replace(array("\n", "\r"), '', $val);
                $key = str_replace(array("\n", "\r", ':'), '', $key);
                $val = "{$key}: {$val}{$crlf}";
            }

            if ($method == 'PROXY') {
                $post = trim(str_replace(array("\n", "\r"), '', $post)) . $crlf;

                if (empty($post)) return array('error' => '使用代理时,必须指定代理请求方法($post参数)');
            } else if (!is_array($post)) {
                $post = array();
            }

            switch ($method) {
                case 'POST':
                    $post = http_build_query($post);
                    $query = empty($query) ? '' : '?' . $query;
                    $commonHeader[] = 'Content-Length: ' . strlen($post) . $crlf;
                    $post = empty($post) ? '' : $crlf . $post . $crlf;
                    $commonHeader = implode('', $commonHeader);
                    $request = "{$method} {$path}{$query} {$http_version}{$crlf}"
                        . "{$commonHeader}"
                        . $post
                        . $crlf;//表示提交结束了
                    break;
                case 'PROXY'://代理
                    $commonHeader = implode('', $commonHeader);
                    $request = $post
                        . $commonHeader
                        . $crlf;//表示提交结束了
                    break;
                case 'GET':
                default:
                    empty($query) ? ($query = array()) : parse_str($query, $query);
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
            $pos = strpos($reponse, $crlf . $crlf);//查找第一个分隔
            if ($pos === false) return array('reponse' => $reponse);
            $header = substr($reponse, 0, $pos);
            $body = substr($reponse, $pos + 2 * strlen($crlf));

            $result = $this->response(array('body' => $body, 'header' => $header));
			return $result;
        }
    }

    public function http_parse_headers($raw_headers)
    {
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
	public function http_chunked_decode($data)
	{
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

	
    private function response($response)
    {
        $headers = $this->http_parse_headers($response['header']);
        if ($headers['Status'] >= 200 && $headers['Status'] < 300) {
			if(isset($headers['Transfer-Encoding']) && $headers['Transfer-Encoding'] == 'chunked'){
				$response['body'] = $this->http_chunked_decode($response['body']);
			}
			$response['header'] = $headers;
            return $response;
        } elseif ($headers['Status'] >= 300 && $headers['Status'] < 400) {
			$temp = explode(';',trim($headers['Set-Cookie']));
			$cookie = $temp[0];
            $response = $this->request($headers['Location'], null, 'GET', $cookie);
            return $this->response($response);
        } else {
            return null;
        }
    }
	
	public function get($url){
		$url_arr = parse_url($url);
		$scheme = strtolower($url_arr['scheme']);
		$header = '';
		$opt = array(
			$scheme => array(
				'method' => 'GET',
				'timeout' => 10,
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
}
