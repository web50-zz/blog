<?php
/**
*
* @author	Fedot B Pozdnyakov 9* <9@u9.ru>
* @package	SBIN Diesel
*/
class di_www_article_indexer extends data_interface
{
	public $title = 'www: Публикации - индексатор';

	/**
	* @var	string	$cfg	Имя конфигурации БД
	*/
	protected $cfg = 'localhost';
	
	/**
	* @var	string	$db	Имя БД
	*/
	protected $db = 'db1';
	
	/**
	* @var	string	$name	Имя таблицы
	*/
	protected $name = 'www_article_indexer';
	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => TRUE, 'readonly' => TRUE),
		'item_id' => array('type' => 'integer'),
		'creator_uid' => array('type' => 'integer'),
		'changer_uid' => array('type' => 'integer'),
		'item_id' => array('type' => 'integer'),
		'record_created_date' => array('type' => 'date'),
		'record_changed_date' => array('type' => 'date'),
		'changed_date' => array('type' => 'date'),
		'published_date' => array('type' => 'date'),
		'post_type' => array('type' => 'integer'),
		'title' => array('type' => 'string'),
		'release_date' => array('type' => 'date'),
		'content' => array('type' => 'string'),
		'brief' => array('type' => 'string'),
		'author' => array('type' => 'string'),
		'source' => array('type' => 'string'),
		'published' => array('type' => 'integer'),
		'uri' => array('type' => 'string'),
		'categories' => array('type' => 'string'),
		'comments' => array('type' => 'string'),
		'tags' => array('type' => 'string'),
		'images' => array('type' => 'string'),
		'unique_visitors' => array('type' => 'integer'),
		'total_visitors' => array('type' => 'integer'),
		'like' => array('type' => 'integer'),
		'dislike' => array('type' => 'integer'),
		'order' => array('type' => 'integer'),
	);

	/**
	* @var	array	$hash	Переменная для хранения ID при удалении
	*/
	protected $hash = array();
	
	public function __construct () {
		// Call Base Constructor
		parent::__construct(__CLASS__);
	}

	public function search_by_uri($uri, $columns = null,$args = array())
	{
		$this->_flush();
		$args_to['_suri'] = $uri;
		if($args['post_type'] >0)
		{
			$args_to['_spost_type'] = $args['post_type'];
		}
		$this->push_args($args_to);
		$this->what = $columns;

		$this->_get();
		$this->pop_args();
		return (array)$this->get_results(0);
	}

	public function search_id_by_uri($uri)
	{
		$this->_flush();
		$this->push_args(array('_suri' => $uri));
		$this->what = array('item_id');
		$this->_get();
		$this->pop_args();
		return (int)$this->get_results(0, 'item_id');
	}

	public function get_list_by_srch($srch)
	{
		$this->_flush();
		$this->push_args(array(
			'sort' => $srch['sort'],
			'dir' => $srch['dir'],
			'start' => $srch['start'],
			'limit' => $srch['limit'],
//			'_spublished' => 1,
		));
		$W = array();
		$where = array();
		//9* если задана категория, ищем по категории
		if (!empty($srch['category_id']))
		{
			$where[] = "MATCH (`categories`) AGAINST ('\"".$srch['category_id']."\"' IN BOOLEAN MODE)";
		}
		//9* если задан тэг, ищем по тэгу
		if (!empty($srch['tag']))
		{
			$where[] = "MATCH (`tags`) AGAINST ('\"".$srch['tag']."\"' IN BOOLEAN MODE)";
		}
		//9* если задана строка поиска, ищем по строке поиска в контенте или в  тайтле
		if (!empty($srch['s']))
		{
			$where[] = "MATCH (`content`) AGAINST ('\"".$srch['s']."\"' IN BOOLEAN MODE)";
			$where[] = "MATCH (`title`) AGAINST ('\"".$srch['s']."\"' IN BOOLEAN MODE)";
		}
		if($srch['year'])
		{
			$where[] = "(release_date > '".$srch['year']."-01-01' AND release_date < '".($srch['year']+1)."-01-01')";

		}
		if (!empty($where))
		{
			$W[] = join(' OR ', $where);
		}
		//9*  если задан тип поста, ищем по типу поста
		if (!empty($srch['post_type']))
		{
			$W[] = "`post_type` = {$srch['post_type']}";
		}

		if (!empty($W))
		{
			$this->where = "(" . join(') AND (', $W) . ")";
		}
		/* Старый вариант с дополительным  каунтом записей
		$data =	$this->extjs_grid_json(array(
			'*',
			'"' . data_interface::get_instance('www_article_files')->get_url() . '"' => 'url',
		), false);
		*/
		$data['records'] = $this->_get()->get_results();
		$data['files_url'] = data_interface::get_instance('www_article_files')->get_url();
	
		$this->pop_args();
		return $data;

	}
	public function get_record($id)
	{
		$this->push_args(array('_sitem_id' => $id));
		$this->_flush();
		$this->what = array(
			'*',
			'"' . data_interface::get_instance('www_article_files')->get_url() . '"' => 'url',
		);
		$this->_get();

		$this->pop_args();
		return $this->get_results(0);
	}


	/**
	*	Обновить запись по указанному ID
	*/
	protected function update_record($id)
	{
		// Собираем основные данные
		$di = data_interface::get_instance('www_article');
		$di->_flush(true);
		$di->what = array(
			'id' => 'item_id',
			'title',
			'post_type',
			'changed_date',
			'published_date',
			'creator_uid',
			'changer_uid',
			'record_created_date',
			'record_changed_date',
			'uri',
			'brief',
			'content',
			'release_date',
			'published',
			'source',
			'author',
			'order',
			'unique_visitors',
			'total_visitors',
			'like',
			'dislike',
		);
		$di->push_args(array('_sid' => $id));
		$di->_get();
		$data = (array)$di->get_results(0);
		$di->pop_args();

		// Обновляем данные
		$this->_flush();
		$this->push_args($data);
		$this->set_args(array('_sitem_id' => $id), true);
		$this->insert_on_empty = true;
		$this->_set();
		$this->pop_args();
	}

	protected function update_images($id)
	{
		// Собираем изображения
		$di = data_interface::get_instance('www_article_files');
		$di->_flush();
		$di->push_args(array('_sitem_id' => $id));
		$di2 = $di->join_with_di('www_article_file_types',array('file_type'=>'id'),array('prefix'=>'prefix','is_image'=>'is_image','not_available'=>'not_available','width'=>'width','height'=>'height','title'=>'type_title'));
		$di->what = array(
			'file_type' => 'file_type', 
			'real_name' => 'image',
			'title'=>'title',
			'comment'=>'comment',
			array('di'=>$di2,'name'=>'prefix'),
			array('di'=>$di2,'name'=>'not_available'),
			array('di'=>$di2,'name'=>'is_image'),
			array('di'=>$di2,'name'=>'width'),
			array('di'=>$di2,'name'=>'height'),
			array('di'=>$di2,'name'=>'title'),

			);
		$di->_get();
		$data = array('images' => $this->json_enc($di->get_results()));
		$di->pop_args();

		// Обновляем данные
		$this->_flush();
		$this->push_args($data);
		$this->set_args(array('_sitem_id' => $id), true);
		$this->insert_on_empty = true;
		$this->_set();
		$this->pop_args();
	}
	protected function update_categories($id)
	{
		// Собираем 
		$di = data_interface::get_instance('www_article_in_category');
		$di->_flush();
		$di->push_args(array('_sitem_id' => $id));
		$di2 = $di->join_with_di('www_article_type',array('category_id'=>'id'),array('title'=>'title','name'=>'name','published'=>'published'));
		$di->what = array(
			'category_id',
			array('di'=>$di2,'name'=>'id'),
			array('di'=>$di2,'name'=>'published'),
			array('di'=>$di2,'name'=>'title'),
			);
		$di->_get();
		$data1 = $di->get_results();

		$data = array('categories' =>  $this->json_enc($di->get_results()));
		$di->pop_args();

		// Обновляем данные
		$this->_flush();
		$this->push_args($data);
		$this->set_args(array('_sitem_id' => $id), true);
		$this->insert_on_empty = true;
		$this->_set();
		$this->pop_args();
	}
	protected function update_tags($id)
	{
		// Собираем изображения
		$di = data_interface::get_instance('www_article_tags');
		$di->_flush();
		$di->push_args(array('_sitem_id' => $id));
		$di2 = $di->join_with_di('www_article_tag_types',array('category_id'=>'id'),array('title'=>'title','uri'=>'uri','not_available'=>'not_available'));
		$di->what = array(
			array('di'=>$di2,'name'=>'id'),
			array('di'=>$di2,'name'=>'not_available'),
			array('di'=>$di2,'name'=>'title'),
			array('di'=>$di2,'name'=>'uri'),
			);
		$di->_get();
		$data1 = $di->get_results();

		$data = array('tags' => $this->json_enc($di->get_results()));
		$di->pop_args();

		// Обновляем данные
		$this->_flush();
		$this->push_args($data);
		$this->set_args(array('_sitem_id' => $id), true);
		$this->insert_on_empty = true;
		$this->_set();
		$this->pop_args();
	}

	protected function update_comments($id)
	{
		// Собираем изображения
		$di = data_interface::get_instance('www_article_comment');
		$di->_flush();
		$di->push_args(array('_sitem_id' => $id));
		$di->_get();
		$data1 = $di->get_results();

		$data = array('comments' => $this->json_enc($di->get_results()));
		$di->pop_args();

		// Обновляем данные
		$this->_flush();
		$this->push_args($data);
		$this->set_args(array('_sitem_id' => $id), true);
		$this->insert_on_empty = true;
		$this->_set();
		$this->pop_args();
	}

	/**
	*	Обработчик события "Изменения  компании"
	*
	* @access	public
	* @param	object		$eObj	DI y_comp_settlement
	* @param	array|integer	$ids	ID изменённых компаний
	* @param	array		$args	Массив ARGS который был актуален события
	*/
	public function article_set($eObj, $ids, $args)
	{
		if (!is_array($ids) && $ids > 0)
		{
			$this->update_record($ids);
			$this->update_images($ids);
			$this->update_categories($ids);
			$this->update_tags($ids);
			$this->update_comments($ids);
		}
		else if (is_array($ids))
		{
			foreach ($ids as $id)
			{
				$this->update_record($id);
				$this->update_images($id);
				$this->update_categories($id);
				$this->update_tags($id);
				$this->update_comments($ids);
			}
		}
		else
		{
			// Some error, because unknown settlement ID
		}
	}


	/**
	*	Обработчик события "Удаление компании"
	*
	* @access	public
	* @param	object		$eObj	DI y_comp_settlement
	* @param	array|integer	$ids	ID удалённых компаний
	* @param	array		$args	Массив ARGS который был актуален события
	*/
	public function article_unset($eObj, $ids, $args)
	{
		if (!empty($args['_sid']))
		{
			$this->_flush();
			$this->push_args(array(
				'_sitem_id' => $args['_sid'],
			));
			$this->_unset();
			$this->pop_args();
		}
	}

	/**
	*	Обработчик события "Изменения  компании"
	*
	* @access	public
	* @param	object		$eObj	DI y_comp_settlement_files
	* @param	array|integer	$ids	ID изменённых компаний
	* @param	array		$args	Массив ARGS который был актуален события
	*/
	public function article_files_set($eObj, $ids, $args)
	{
		if (($id = (int)$args['item_id']) == 0 && !empty($args['_sitem_id']))
			$id = (int)$args['_sitem_id'];

		$this->update_images($id);
	}

	public function article_files_unset($eObj, $ids, $args)
	{

		if (!empty($this->removeable_id))
			$this->update_images($this->removeable_id);
	}

	/**
	*	Обработчик события "Изменения  компании"
	*
	* @access	public
	* @param	object		$eObj	DI y_comp_settlement_files
	* @param	array		$args	Массив ARGS который был актуален события
	*/
	public function article_files_prepare_unset($eObj, $args)
	{
		if (($id = (int)$args['item_id']) == 0 && !empty($args['_sitem_id']))
			$id = (int)$args['_sitem_id'];
		
		$this->removeable_id = $id;
	}
	public function article_comment_set($eObj, $ids, $args)
	{
		if (($id = (int)$args['item_id']) == 0 && !empty($args['_sitem_id']))
			$id = (int)$args['_sitem_id'];

		$this->update_comments($id);
	}

	public function article_comment_prepare_unset($eObj, $args)
	{
		if (($id = (int)$args['item_id']) == 0 && !empty($args['_sitem_id']))
			$id = (int)$args['_sitem_id'];
		
		$this->removeable_id = $id;
	}

	public function article_comment_unset($eObj, $ids, $args)
	{
		if (!empty($this->removeable_id))
			$this->update_comments($this->removeable_id);
	}

	/**
	*	Обработчик события "Изменения вхождения  категорию"
	*
	* @access	public
	* @param	object		$eObj	DI www_article_in_category
	* @param	array|integer	$ids	ID изменённых публикаций 
	* @param	array		$args	Массив ARGS который был актуален события
	*/
	public function article_category_set($eObj, $ids, $args)
	{
		if (($id = (int)$args['item_id']) == 0 && !empty($args['_sitem_id']))
			$id = (int)$args['_sitem_id'];

		$this->update_categories($id);
	}

	/**
	*	Обработчик события "Изменения  вхождения в категорию"
	*
	* @access	public
	* @param	object		$eObj	DI www_article_in_category 
	* @param	array		$args	Массив ARGS который был актуален события
	*/
	public function article_category_prepare_unset($eObj, $args)
	{
		if (($id = (int)$args['item_id']) == 0 && !empty($args['_sitem_id']))
			$id = (int)$args['_sitem_id'];
		
		$this->removeable_id = $id;
	}

	/**
	*	Обработчик события "Удаление публикации из категории"
	*
	* @access	public
	* @param	object		$eObj	DI www_article_in_category 
	* @param	array|integer	$ids	ID удалённых записей
	* @param	array		$args	Массив ARGS который был актуален события
	*/
	public function article_category_unset($eObj, $ids, $args)
	{
		if (!empty($this->removeable_id))
			$this->update_categories($this->removeable_id);
	}

	/**
	*	Обработчик события "Изменения вхождения  тэга в публикацию"
	*
	* @access	public
	* @param	object		$eObj	DI www_article_tags
	* @param	array|integer	$ids	ID изменённых публикаций 
	* @param	array		$args	Массив ARGS который был актуален события
	*/
	public function article_tag_set($eObj, $ids, $args)
	{
		if (($id = (int)$args['item_id']) == 0 && !empty($args['_sitem_id']))
		{
			$id = (int)$args['_sitem_id'];
		}
		$this->update_tags($id);
	}

	/**
	*	Обработчик события "Изменения  вхождения тэга в публикацию"
	*
	* @access	public
	* @param	object		$eObj	DI www_article_tags 
	* @param	array		$args	Массив ARGS который был актуален события
	*/
	public function article_tag_prepare_unset($eObj, $args)
	{
		if (($id = (int)$args['article_id']) == 0 && !empty($args['_sarticle_id']))
		{
			$id = (int)$args['_sarticle_id'];
		}
		$this->removeable_id = $id;
	}

	/**
	*	Обработчик события "Удаление тэга из публикации"
	*
	* @access	public
	* @param	object		$eObj	DI www_article_tags 
	* @param	array|integer	$ids	ID удалённых записей
	* @param	array		$args	Массив ARGS который был актуален события
	*/
	public function article_tag_unset($eObj, $ids, $args)
	{
		if (!empty($this->removeable_id))
		{
			$this->update_tags($this->removeable_id);
		}
	}
	//9*  custom cyrillic fix. for json_encode
	public function json_enc($arr)
	{
		$result = preg_replace_callback(
			'/\\\u([0-9a-fA-F]{4})/', 
			create_function('$_m', 'return mb_convert_encoding("&#" . intval($_m[1], 16) . ";", "UTF-8", "HTML-ENTITIES");'),
                        str_replace('\n','',str_replace('\t','',str_replace('\r','',str_replace('"','\"',json_encode($arr)))))
		);
		/* 9* старый вариант не учитыввал замены переходов строк  и табов  на \n\t
		$result = preg_replace_callback(
			'/\\\u([0-9a-fA-F]{4})/', 
			create_function('$_m', 'return mb_convert_encoding("&#" . intval($_m[1], 16) . ";", "UTF-8", "HTML-ENTITIES");'),
			json_encode($arr)
		);
		*/
		return $result;
	}

	public function _listeners()
	{
		return array(
			array('di' => 'www_article', 'event' => 'onSet', 'handler' => 'article_set'),
			array('di' => 'www_article', 'event' => 'onUnset', 'handler' => 'article_unset'),
			array('di' => 'www_article_files', 'event' => 'onSet', 'handler' => 'article_files_set'),
			array('di' => 'www_article_files', 'event' => 'onBeforeUnset', 'handler' => 'article_files_prepare_unset'),
			array('di' => 'www_article_files', 'event' => 'onUnset', 'handler' => 'article_files_unset'),
			array('di' => 'www_article_in_category', 'event' => 'onSet', 'handler' => 'article_category_set'),
			array('di' => 'www_article_in_category', 'event' => 'onBeforeUnset', 'handler' => 'article_category_prepare_unset'),
			array('di' => 'www_article_in_category', 'event' => 'onUnset', 'handler' => 'article_category_unset'),
			array('di' => 'www_article_tags', 'event' => 'onSet', 'handler' => 'article_tag_set'),
			array('di' => 'www_article_tags', 'event' => 'onBeforeUnset', 'handler' => 'article_tag_prepare_unset'),
			array('di' => 'www_article_tags', 'event' => 'onUnset', 'handler' => 'article_tag_unset'),
			array('di' => 'www_article_comment', 'event' => 'onSet', 'handler' => 'article_comment_set'),
			array('di' => 'www_article_comment', 'event' => 'onBeforeUnset', 'handler' => 'article_comment_prepare_unset'),
			array('di' => 'www_article_comment', 'event' => 'onUnset', 'handler' => 'article_comment_unset'),

		);
	}
}
?>
