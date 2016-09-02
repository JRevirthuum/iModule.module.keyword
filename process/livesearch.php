<?php
/**
 * 전달된 키워드를 분리하여 분리된 키워드와 앞부분이 일치하는 자동검색어 목록을 반환합니다.
 * 
 * @post string $keyword 검색어
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