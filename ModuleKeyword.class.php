<?php
/**
 * 이 파일은 iModule Keyword 모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 키워드 모듈은 iModule 상에서 검색되어지는 모든 키워드 데이터를 관리하고, 자동검색어를 제공합니다.
 * 
 * @file /modules/keyword/ModuleKeyword.class.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0.160902
 */
class ModuleKeyword {
	/**
	 * iModule core 와 Module core 클래스
	 */
	private $IM;
	private $Module;
	
	/**
	 * DB 관련 변수정의
	 *
	 * @private DB $DB DB에 접속하고 데이터를 처리하기 위한 DB class (@see /classes/DB.class.php)
	 * @private string[] $table DB 테이블 별칭 및 원 테이블명을 정의하기 위한 변수
	 */
	private $DB;
	private $table;
	
	/**
	 * 언어셋을 정의한다.
	 * 
	 * @private object $lang 현재 사이트주소에서 설정된 언어셋
	 * @private object $oLang package.json 에 의해 정의된 기본 언어셋
	 */
	private $lang = null;
	private $oLang = null;
	
	/**
	 * class 선언
	 *
	 * @param iModule $IM iModule core class
	 * @param Module $Module Module core class
	 * @see /classes/iModule.class.php
	 * @see /classes/Module.class.php
	 */
	function __construct($IM,$Module) {
		$this->IM = $IM;
		$this->Module = $Module;
		
		/**
		 * 모듈에서 사용하는 DB 테이블 별칭 정의
		 * @see 모듈폴더의 package.json 의 databases 참고
		 */
		$this->table = new stdClass();
		$this->table->keyword = 'keyword_table';
		
		/**
		 * 자동검색어를 제공하기 위한 자바스크립트 및 스타일시트를 로딩한다.
		 * 키워드모듈은 글로벌모듈이기 때문에 모듈클래스 선언부에서 선언해주어야 사이트 레이아웃에 반영된다.
		 */
		$this->IM->addHeadResource('style',$this->Module->getDir().'/styles/style.css');
		$this->IM->addHeadResource('script',$this->Module->getDir().'/scripts/keyword.js');
	}
	
	/**
	 * 모듈 코어 클래스를 반환한다.
	 * 현재 모듈의 각종 설정값이나 모듈의 package.json 설정값을 모듈 코어 클래스를 통해 확인할 수 있다.
	 *
	 * @return Module $Module
	 */
	function getModule() {
		return $this->Module;
	}
	
	/**
	 * 모듈 설치시 정의된 DB코드를 사용하여 모듈에서 사용할 전용 DB클래스를 반환한다.
	 *
	 * @return DB $DB
	 */
	function db() {
		return $this->IM->db($this->Module->getInstalled()->database);
	}
	
	/**
	 * 모듈에서 사용중인 DB테이블 별칭을 이용하여 실제 DB테이블 명을 반환한다.
	 *
	 * @param string $table DB테이블 별칭
	 * @return string $table 실제 DB테이블 명
	 */
	function getTable($table) {
		return $this->table->$table;
	}
	
	/**
	 * 사이트 외부에서 현재 모듈의 API를 호출하였을 경우, API 요청을 처리하기 위한 함수
	 *
	 * @param string $api API명
	 * @return object $datas API처리후 반환 데이터 (해당 데이터는 /api/index.php 를 통해 API호출자에게 전달된다.)
	 * @see /api/index.php
	 */
	function getApi($api) {
		// @todo 최근키워드, 인기키워드 등 API 제공
	}
	
	/**
	 * 언어셋파일에 정의된 코드를 이용하여 사이트에 설정된 언어별로 텍스트를 반환한다.
	 * 코드에 해당하는 문자열이 없을 경우 1차적으로 package.json 에 정의된 기본언어셋의 텍스트를 반환하고, 기본언어셋 텍스트도 없을 경우에는 코드를 그대로 반환한다.
	 *
	 * @param string $code 언어코드
	 * @return string $language 실제 언어셋 텍스트
	 */
	function getLanguage($code) {
		if ($this->lang == null) {
			if (file_exists($this->Module->getPath().'/languages/'.$this->IM->language.'.json') == true) {
				$this->lang = json_decode(file_get_contents($this->Module->getPath().'/languages/'.$this->IM->language.'.json'));
				if ($this->IM->language != $this->Module->getPackage()->language) {
					$this->oLang = json_decode(file_get_contents($this->Module->getPath().'/languages/'.$this->Module->getPackage()->language.'.json'));
				}
			} else {
				$this->lang = json_decode(file_get_contents($this->Module->getPath().'/languages/'.$this->Module->getPackage()->language.'.json'));
				$this->oLang = null;
			}
		}
		
		$temp = explode('/',$code);
		if (count($temp) == 1) {
			return isset($this->lang->$code) == true ? $this->lang->$code : ($this->oLang != null && isset($this->oLang->$code) == true ? $this->oLang->$code : '');
		} else {
			$string = $this->lang;
			for ($i=0, $loop=count($temp);$i<$loop;$i++) {
				if (isset($string->{$temp[$i]}) == true) $string = $string->{$temp[$i]};
				else $string = null;
			}
			
			if ($string == null && $this->oLang != null) {
				$string = $this->oLang;
				for ($i=0, $loop=count($temp);$i<$loop;$i++) {
					if (isset($string->{$temp[$i]}) == true) $string = $string->{$temp[$i]};
					else $string = null;
				}
			}
			return $string == null ? '' : $string;
		}
	}
	
	/**
	 * 자동검색어를 위해 각 언어별 키워드위치에 해당하는 영문코드를 반환한다.
	 *
	 * @param string $str
	 * @return string $englishStr
	 * @todo 한글 외 다른 언어 지원
	 */
	function getEngcode($str) {
		$arr_kor = array('ㄱ','ㄲ','ㄴ','ㄷ','ㄸ','ㄹ','ㅁ','ㅂ','ㅃ','ㅅ','ㅆ','ㅇ','ㅈ','ㅉ','ㅊ','ㅋ','ㅌ','ㅍ','ㅎ','ㄳ','ㄵ','ㄶ','ㄺ','ㄻ','ㄼ','ㄽ','ㄾ','ㄿ','ㅀ','ㅄ','ㅏ','ㅐ','ㅑ','ㅒ','ㅓ','ㅔ','ㅕ','ㅖ','ㅗ','ㅘ','ㅙ','ㅚ','ㅛ','ㅜ','ㅝ','ㅞ','ㅟ','ㅠ','ㅡ','ㅢ','ㅣ');

		$arr_eng = array('r','R','s','e','E','f','a','q','Q','t','T','d','w','W','c','z','x','v','g','rt','sw','sg','fr','fa','fq','ft','fx','fv','fg','qt','k','o','i','O','j','p','u','P','h','hk','ho','hl','y','n','nj','np','nl','u','m','ml','l');

		$engcode = str_replace($arr_kor,$arr_eng,$str);

		return $engcode;
	}
	
	/**
	 * 글자를 조합해서 사용하는 언어의 경우(예 : 한국어) 조합된 언어 분리한 문자열을 얻는다.
	 *
	 * @param string $str
	 * @return string $splitStr
	 * @todo 한글 외 다른 언어 지원
	 */
	function getKeycode($str) {
		$arr_cho = array('ㄱ','ㄲ','ㄴ','ㄷ','ㄸ','ㄹ','ㅁ','ㅂ','ㅃ','ㅅ','ㅆ','ㅇ','ㅈ','ㅉ','ㅊ','ㅋ','ㅌ','ㅍ','ㅎ');
		$arr_jung = array('ㅏ','ㅐ','ㅑ','ㅒ','ㅓ','ㅔ','ㅕ','ㅖ','ㅗ','ㅘ','ㅙ','ㅚ','ㅛ','ㅜ','ㅝ','ㅞ','ㅟ','ㅠ','ㅡ','ㅢ','ㅣ');
		$arr_jong = array('','ㄱ','ㄲ','ㄳ','ㄴ','ㄵ','ㄶ','ㄷ','ㄹ','ㄺ','ㄻ','ㄼ','ㄽ','ㄾ','ㄿ','ㅀ','ㅁ','ㅂ','ㅄ','ㅅ','ㅆ','ㅇ','ㅈ','ㅊ','ㅋ','ㅌ','ㅍ','ㅎ');

		$unicode = array();
		$values = array();
		$lookingFor = 1;

		for ($i=0, $loop=strlen($str);$i<$loop;$i++) {
			$thisValue = ord($str[$i]);

			if ($thisValue < 128) {
				$unicode[] = $thisValue;
			} else {
				if (count($values)==0) $lookingFor = $thisValue < 224 ? 2 : 3;
				$values[] = $thisValue;
				if (count($values) == $lookingFor) {
					$number = $lookingFor == 3 ? (($values[0]%16)*4096)+(($values[1]%64)*64)+($values[2]%64) : (($values[0]%32)*64)+($values[1]%64);
					$unicode[] = $number;
					$values = array();
					$lookingFor = 1;
				}
			}
		}

		$splitStr = '';
		while (list($key,$code) = each($unicode)) {
			if ($code >= 44032 && $code <= 55203) {
				$temp = $code-44032;
				$cho = (int)($temp/21/28);
				$jung = (int)(($temp%(21*28)/28));
				$jong = (int)($temp%28);

				$splitStr.= $arr_cho[$cho].$arr_jung[$jung].$arr_jong[$jong];
			} else {
				$temp = array($unicode[$key]);

				foreach ($temp as $ununicode) {
					if ($ununicode < 128) {
						$splitStr.= chr($ununicode);
					} elseif ($ununicode < 2048) {
						$splitStr.= chr(192+(($ununicode-($ununicode%64))/64));
						$splitStr.= chr(128+($ununicode%64));
					} else {
						$splitStr.= chr(224+(($ununicode-($ununicode%4096))/4096));
						$splitStr.= chr(128+((($ununicode%4096)-($ununicode%64))/64));
						$splitStr.= chr(128+($ununicode%64));
					}
				}
			}
		}

		$splitStr = str_replace(' ','',$splitStr);
		return $splitStr;
	}
	
	/**
	 * 자동검색어에 사용하기 위한 문자열을 얻는다.
	 * 조합형 언어의 경우 getKeycode 를 통해 글자를 분리한다음 getEngcode 를 통해 영문으로 대치한다.
	 * 실질적으로 영문 문자열만으로 자동검색어 매칭을 하게 된다.
	 *
	 * @param string $str
	 * @return string $liveStr
	 */
	function getLivecode($str) {
		return $this->getEngcode($this->getKeycode($str));
	}
	
	/**
	 * 현재 모듈에서 처리해야하는 요청이 들어왔을 경우 처리하여 결과를 반환한다.
	 * 소스코드 관리를 편하게 하기 위해 각 요쳥별로 별도의 PHP 파일로 관리한다.
	 * 작업코드가 '@' 로 시작할 경우 사이트관리자를 위한 작업으로 최고관리자 권한이 필요하다.
	 *
	 * @param string $action 작업코드
	 * @return object $results 수행결과
	 * @see /process/index.php
	 */
	function doProcess($action) {
		$values = new stdClass();
		$results = new stdClass();
		
		/**
		 * 모듈의 process 파일에 $action 에 해당하는 파일이 있을 경우 불러온다.
		 */
		if (is_file($this->Module->getPath().'/process/'.$action.'.php') == true) {
			INCLUDE $this->Module->getPath().'/process/'.$action.'.php';
		}
		if ($action == 'livesearch') {
			
		}
		
		return $results;
	}
}
?>