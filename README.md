# PHP
PHP 
//是否是链接
 preg_match_all('/\b((?#protocol)https?|ftp):\/\/((?#domain)[-A-Z0-9.]+)((?#file)\/[-A-Z0-9+&@#\/%=~_|!:,.;]*)?((?#parameters)\?[A-Z0-9+&@#\/%=~_|!:,.;]*)?/i', $data, $url);
