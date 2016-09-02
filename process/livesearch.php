<?php
/**
 * 이 파일은 iModule Keyword 모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 전달된 키워드를 분리하여 분리된 키워드와 앞부분이 일치하는 자동검색어 목록을 반환합니다.
 *
 * @file /modules/keyword/ModuleKeyword.class.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0.160902
 *
 * @post string $keyword 검색어
 * @return object $results
 */
if (defined('__IM__') == false) exit;

$keyword = Request('keyword');

if ($keyword != null && strlen($keyword) > 0) {
	$keycode = $this->getKeycode($keyword);
	$engcode = $this->getEngcode($keycode);
	$keywords = $this->db()->select($this->table->keyword,'keyword')->where('keycode',$keycode.'%','LIKE')->orWhere('engcode',$engcode.'%','LIKE')->orderBy('hit','asc')->limit(50)->get();
	
	$results->success = true;
	$results->keywords = $keywords;
}
?>