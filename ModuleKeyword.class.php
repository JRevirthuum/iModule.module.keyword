<?php
/**
 * 이 파일은 iModule 키워드모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 홈페이지에서 검색되는 키워드와 관련된 전반적인 기능을 관리합니다.
 * 
 * @file /modules/keyword/ModuleKeyword.class.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2017. 11. 22.
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
		if ($this->DB == null || $this->DB->ping() === false) $this->DB = $this->IM->db($this->getModule()->getInstalled()->database);
		return $this->DB;
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
	 * 사이트 외부에서 현재 모듈의 API를 호출하였을 경우, API 요청을 처리하기 위한 함수로 API 실행결과를 반환한다.
	 * 소스코드 관리를 편하게 하기 위해 각 요쳥별로 별도의 PHP 파일로 관리한다.
	 *
	 * @param string $api API명
	 * @return object $datas API처리후 반환 데이터 (해당 데이터는 /api/index.php 를 통해 API호출자에게 전달된다.)
	 * @see /api/index.php
	 * @todo 최근키워드, 인기키워드 등 API 제공
	 */
	function getApi($api) {
		$data = new stdClass();
		$values = new stdClass();
		
		/**
		 * 모듈의 api 폴더에 $api 에 해당하는 파일이 있을 경우 불러온다.
		 */
		if (is_file($this->Module->getPath().'/api/'.$api.'.php') == true) {
			INCLUDE $this->Module->getPath().'/api/'.$api.'.php';
		}
		
		return $data;
	}
	
	/**
	 * 언어셋파일에 정의된 코드를 이용하여 사이트에 설정된 언어별로 텍스트를 반환한다.
	 * 코드에 해당하는 문자열이 없을 경우 1차적으로 package.json 에 정의된 기본언어셋의 텍스트를 반환하고, 기본언어셋 텍스트도 없을 경우에는 코드를 그대로 반환한다.
	 *
	 * @param string $code 언어코드
	 * @param string $replacement 일치하는 언어코드가 없을 경우 반환될 메세지 (기본값 : null, $code 반환)
	 * @return string $language 실제 언어셋 텍스트
	 */
	function getText($code,$replacement=null) {
		if ($this->lang == null) {
			if (is_file($this->getModule()->getPath().'/languages/'.$this->IM->language.'.json') == true) {
				$this->lang = json_decode(file_get_contents($this->getModule()->getPath().'/languages/'.$this->IM->language.'.json'));
				if ($this->IM->language != $this->getModule()->getPackage()->language && is_file($this->getModule()->getPath().'/languages/'.$this->getModule()->getPackage()->language.'.json') == true) {
					$this->oLang = json_decode(file_get_contents($this->getModule()->getPath().'/languages/'.$this->getModule()->getPackage()->language.'.json'));
				}
			} elseif (is_file($this->getModule()->getPath().'/languages/'.$this->getModule()->getPackage()->language.'.json') == true) {
				$this->lang = json_decode(file_get_contents($this->getModule()->getPath().'/languages/'.$this->getModule()->getPackage()->language.'.json'));
				$this->oLang = null;
			}
		}
		
		$returnString = null;
		$temp = explode('/',$code);
		
		$string = $this->lang;
		for ($i=0, $loop=count($temp);$i<$loop;$i++) {
			if (isset($string->{$temp[$i]}) == true) {
				$string = $string->{$temp[$i]};
			} else {
				$string = null;
				break;
			}
		}
		
		if ($string != null) {
			$returnString = $string;
		} elseif ($this->oLang != null) {
			if ($string == null && $this->oLang != null) {
				$string = $this->oLang;
				for ($i=0, $loop=count($temp);$i<$loop;$i++) {
					if (isset($string->{$temp[$i]}) == true) {
						$string = $string->{$temp[$i]};
					} else {
						$string = null;
						break;
					}
				}
			}
			
			if ($string != null) $returnString = $string;
		}
		
		$this->IM->fireEvent('afterGetText',$this->getModule()->getName(),$code,$returnString);
		
		/**
		 * 언어셋 텍스트가 없는경우 iModule 코어에서 불러온다.
		 */
		if ($returnString != null) return $returnString;
		elseif (in_array(reset($temp),array('text','button','action')) == true) return $this->IM->getText($code,$replacement);
		else return $replacement == null ? $code : $replacement;
	}
	
	/**
	 * 상황에 맞게 에러코드를 반환한다.
	 *
	 * @param string $code 에러코드
	 * @param object $value(옵션) 에러와 관련된 데이터
	 * @param boolean $isRawData(옵션) RAW 데이터 반환여부
	 * @return string $message 에러 메세지
	 */
	function getErrorText($code,$value=null,$isRawData=false) {
		$message = $this->getText('error/'.$code,$code);
		if ($message == $code) return $this->IM->getErrorText($code,$value,null,$isRawData);
		
		$description = null;
		switch ($code) {
			case 'NOT_ALLOWED_SIGNUP' :
				if ($value != null && is_object($value) == true) {
					$description = $value->title;
				}
				break;
				
			case 'DISABLED_LOGIN' :
				if ($value != null && is_numeric($value) == true) {
					$description = str_replace('{SECOND}',$value,$this->getText('text/remain_time_second'));
				}
				break;
			
			default :
				if (is_object($value) == false && $value) $description = $value;
		}
		
		$error = new stdClass();
		$error->message = $message;
		$error->description = $description;
		$error->type = 'BACK';
		
		if ($isRawData === true) return $error;
		else return $this->IM->getErrorText($error);
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
		foreach ($unicode as $key=>$code) {
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
		return strtolower($this->getEngcode($this->getKeycode($str)));
	}
	
	/**
	 * FULLTEXT 인덱스 검색을 위한 WHERE 절을 추가한다.
	 *
	 * @param &$DB 디비 객체
	 * @param string[] $fields 검색할 필드
	 * @param string $keyword 키워드
	 */
	function getWhere(&$DB,$fields,$keyword) {
		$db = $DB->db();
		
		if ($db->type == 'mysql') {
			$mysqli = $DB->mysqli();
			
			if (version_compare($mysqli->server_info,'5.7.6','>=') == true) {
				$DB->where("MATCH(".implode(',',$fields).") AGAINST (? IN BOOLEAN MODE)",array($keyword));
			} else {
				$keyword = explode(' ',$keyword);
				$keyword = array_unique($keyword);
			
				for ($i=0, $loop=count($keyword);$i<$loop;$i++) {
					$keyword[$i] = $keyword[$i].'*';
				}
				$keyword = implode(' ',$keyword);
				
				$DB->where("MATCH(".implode(',',$fields).") AGAINST (? IN BOOLEAN MODE)",array($keyword));
			}
		}
		
		return $DB;
	}
	
	/**
	 * 선택된 DOM 영역내에서 키워드를 강조하고, 키워드 검색기록을 기록한다.
	 *
	 * @param string $keyword 키워드
	 * @param string $dom DOM 셀렉터 (기본값 : body)
	 */
	function mark($keyword,$dom='body') {
		$this->IM->addHeadResource('script',$this->getModule()->getDir().'/scripts/mark.js');
		$this->IM->addBodyContent('<script>$(document).ready(function() { $("'.$dom.'").mark("'.$keyword.'",{acrossElements:true,caseSensitive:true}); });</script>');
		
		$storedKeywords = Request('IM_KEYWORDS','session') != null ? Request('IM_KEYWORDS','session') : array();
		if (in_array($keyword,$storedKeywords) == false) {
			$storedKeywords[] = $keyword;
			
			$keywords = explode(' ',$keyword);
			foreach ($keywords as $keyword) {
				$keyword = trim($keyword);
				if (strlen($keyword) == 0) continue;
				
				$stored = $this->db()->select($this->table->keyword)->where('keyword',$keyword)->getOne();
				$keycode = $stored == null ? $this->getEngCode($this->getKeycode($keyword)) : $stored->keycode;
				$hit = $stored == null ? 0 : $stored->hit;
				
				$this->db()->replace($this->table->keyword,array('keyword'=>$keyword,'keycode'=>$keycode,'hit'=>++$hit,'latest_search'=>time()))->execute();
			}
			
			$_SESSION['IM_KEYWORDS'] = $storedKeywords;
		}
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
		$results = new stdClass();
		
		$values = (object)get_defined_vars();
		$this->IM->fireEvent('beforeDoProcess',$this->getModule()->getName(),$action,$values);
		
		/**
		 * 모듈의 process 폴더에 $action 에 해당하는 파일이 있을 경우 불러온다.
		 */
		if (is_file($this->getModule()->getPath().'/process/'.$action.'.php') == true) {
			INCLUDE $this->getModule()->getPath().'/process/'.$action.'.php';
		}
		
		unset($values);
		$values = (object)get_defined_vars();
		$this->IM->fireEvent('afterDoProcess',$this->getModule()->getName(),$action,$values,$results);
		
		return $results;
	}
}
?>