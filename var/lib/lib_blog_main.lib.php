<?php
/**
*	Library Glob основная помойка 
*
* @author	Fedot B Pozdnyakov <9@u9.ru> 18.02.2018
* @package	SBIN Diesel
*/
class lib_blog_main 
{
	private static $post_type_storage = array();

	public static function get_post_root_by_type($post_type)
	{
		if(count(self::$post_type_storage) == 0)
		{
			$di  = data_interface::get_instance('www_article_post_types');
			$res = $di->_get()->get_results();
			foreach($res as $k=>$v)
			{	
				if($v->root_page_id > 0)
				{
					$sql = "select uri from structure where id = {$v->root_page_id}";
					$r = $di->_get($sql)->get_results();
					self::$post_type_storage[$v->id] = $r[0]->uri;
				}
			}
		}
		return self::$post_type_storage[$post_type];
	}
}
?>
