<?php
/*
 * Author: Gerald <gera2ld@163.com>
 */
class Fetcher {
	protected $encoding='utf-8';	// encoding used in the module
	private $cookiefile=null;
	public $status=0;
	function __construct($cookiefile=null) {
		if($cookiefile)
			$this->cookiefile=$cookiefile;
		else if($cookiefile==='')
			$this->cookiefile=tempnam('.','COOKIE');
	}
	public function save($fd, $data, $charset=null) {
		if($charset)
			$data=iconv($this->encoding,$charset.'//ignore',$data);
		$f=fopen($fd,'w');
		if(!$f) throw new Exception('Error opening file: '.$fd);
		fwrite($f,$data);
		fclose($f);
	}
	public function load_binary($url,$data=null,$kw=null) {
		// $data can be array or string
		// headers can be customized using $kw['headers']
		$ch=curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_HEADER,false);	// do not return header
		if($this->cookiefile) {
			curl_setopt($ch,CURLOPT_COOKIEJAR,$this->cookiefile);
			curl_setopt($ch,CURLOPT_COOKIEFILE,$this->cookiefile);
		}
		curl_setopt($ch,CURLOPT_POST,$data?1:0);
		if($data)
			curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		if(isset($kw['useragent']))
			curl_setopt($ch,CURLOPT_USERAGENT,$kw['useragent']);
		else
			curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.115 Safari/537.36 OPR/27.0.1689.76');
		if(isset($kw['timeout']))
			curl_setopt($ch,CURLOPT_TIMEOUT,$kw['timeout']);
		elseif(isset($kw['timeout_ms']))
			curl_setopt($ch,CURLOPT_TIMEOUT_MS,$kw['timeout_ms']);
		if(isset($kw['headers']))
			curl_setopt($ch,CURLOPT_HTTPHEADER,$kw['headers']);
		$g=curl_exec($ch);
		$this->status=curl_getinfo($ch,CURLINFO_HTTP_CODE);
		return $g;
	}
	public function load($url,$data=null,$kw=null) {
		// if $kw['charset'] is set, $g is decoded using $kw['charset']
		$g=$this->load_binary($url,$data,$kw);
		if($kw&&isset($kw['charset']))
			$g=iconv($kw['charset'],$this->encoding.'//ignore',$g);
		return $g;
	}
	public function load_json($url,$data=null,$kw=null) {
		$g=$this->load($url,$data,$kw);
		return json_decode($g,true);
	}
}
