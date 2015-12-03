<?php 
class Curl{
	public $http_code = '';
    public $headers = '';
    public $headers_arr = array();
    protected $done_headers = false;
	
	private $_curl;
	
	public function Curl(){
		$this->_curl = curl_init();
		curl_setopt($this->_curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
		curl_setopt($this->_curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($this->_curl, CURLOPT_SSLVERSION, 6);
		curl_setopt($this->_curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
		
		curl_setopt($this->_curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.85 Safari/537.36');
        curl_setopt($this->_curl, CURLOPT_HEADER, 0);
        curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, 0);
		
		curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, 1);
		
		curl_setopt($this->_curl, CURLOPT_TIMEOUT, 10);
	}
	
	 public function get($url, $options = array()){
		curl_setopt($this->_curl, CURLOPT_URL, $url);
		curl_setopt($this->_curl, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($this->_curl, CURLOPT_COOKIE, $options['Cookie']);
		  
		$result = curl_exec($this->_curl);

        $this->http_code =  curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);

        if (curl_errno($this->_curl)) {
            throw new Exception('cURL error ' . curl_errno($this->_curl) . ': ' . curl_error($this->_curl));
            return;
        }
        curl_close($this->_curl);
		return $result;
	 }
	 
	protected function stream_headers($handle, $headers){
        if ($this->done_headers) {
            $this->headers = '';
            $this->done_headers = false;
        }
        $this->headers .= $headers;

        if ($headers === "\r\n") {
            $this->done_headers = true;
        }
        return strlen($headers);
    }
	
	private function hanle_header()
    {
        // Pretend CRLF = LF for compatibility (RFC 2616, section 19.3)
        $headers = str_replace("\r\n", "\n", $this->headers);
        // Unfold headers (replace [CRLF] 1*( SP | HT ) with SP) as per RFC 2616 (section 2.2)
        $headers = preg_replace('/\n[ \t]/', ' ', $headers);
        $headers = explode("\n", $headers);
        preg_match('#^HTTP/1\.\d[ \t]+(\d+)#i', array_shift($headers), $matches);
        if (empty($matches)) {
            throw new Requests_Exception('Response could not be parsed', 'noversion', $headers);
        }
        $this->headers_arr['status_code'] = (int)$matches[1];
        if ($this->headers_arr['status_code'] >= 200 && $this->headers_arr['status_code'] < 300) {
            $this->headers_arr['success'] = true;
        }

        foreach ($headers as $header) {
            list($key, $value) = explode(':', $header, 2);
            $value = trim($value);
            preg_replace('#(\s+)#i', ' ', $value);
            $this->headers_arr[$key] = $value;
        }
    }
}