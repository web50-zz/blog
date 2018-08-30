<?php
/**
*
* @author	9@u9.ru  30.08.2018
* @package	SBIN Diesel
*/
class di_www_article_text_blocks extends data_interface
{
	public $title = 'Статьи: Блок текста в статье';

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
	protected $name = 'www_article_text_blocks';
	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => TRUE, 'readonly' => TRUE),
		'created_datetime' => array('type' => 'datetime'),
		'published' => array('type' => 'integer'),
		'item_id' => array('type' => 'integer'),
		'block_type' => array('type' => 'string'),
		'content' => array('type' => 'string'),
	);
	
	public function __construct ()
	{
	    // Call Base Constructor
	    parent::__construct(__CLASS__);
	}

	
	/**
	*	Список записей
	*/
	protected function sys_list()
	{
		$this->_flush(true);
		$di =  $this->join_with_di('www_article_text_blocks_types',array('block_type'=>'id'),array('title'=>'text_blocks_type_str'));
		if (!empty($this->args['query']) && !empty($this->args['field']))
		{
			$this->args["_s{$this->args['field']}"] = "%{$this->args['query']}%";
		}

		$where = array();

		$this->extjs_grid_json(array(
			'id',
			'created_datetime',
			'published',
			'item_id',
			'block_type',
			array('di'=>$di,'name'=>'title'),
			'content',
		));
	}
	
	/**
	*	Получить данные элемента в виде JSON
	* @access protected
	*/
	protected function sys_get()
	{
		$this->_flush();
		$this->extjs_form_json();
	}
	
	/**
	*	Сохранить данные и вернуть JSON-пакет для ExtJS
	* @access protected
	*/
	public function sys_set($silent = false)
	{
		$this->_flush();
		if(!$this->args['_sid'])
		{
			$this->args['created_datetime'] = date('Y-m-d H:i:s');
		}
		$this->insert_on_empty = true;
		$res =	$this->extjs_set_json(false,false);
		if($silent == true)
		{
			return $res;
		}
		response::send($res,'json');
	}

	/**
	*	Сохранить данные и вернуть JSON-пакет для ExtJS
	* @access protected
	*/
	protected function sys_mset()
	{
		$records = (array)json_decode($this->get_args('records'), true);

		foreach ($records as $record)
		{
			$record['_sid'] = $record['id'];
			unset($record['id']);
			$this->_flush();
			$this->push_args($record);
			$this->insert_on_empty = true;
			$data = $this->extjs_set_json(false);
			$this->pop_args();
		}

		response::send(array('success' => true), 'json');
	}
	
	/**
	*	Удалить данные и вернуть JSON-пакет для ExtJS
	* @access protected
	*/
	protected function sys_unset()
	{
		$this->_flush();
		$this->extjs_unset_json();
	}

	public function unset_for_article($eObj, $ids, $args)
	{
		$this->push_args(array());
		if (!is_array($ids) && $ids > 0)
		{
			$this->set_args(array(
				'_sitem_id' => $ids,
			));
			$this->_flush();
			$this->_unset();
		}
		else if (is_array($ids))
		{
			foreach ($ids as $id)
			{
				$this->set_args(array(
					'_sitem_id' => $id,
				));
				$this->_flush();
				$this->insert_on_empty = true;
				$this->_unset();
			}
		}
		else
		{
			// Some error, because unknown project ID
		}
		$this->pop_args();
	}


	public function _listeners()
	{
		return array(
			array('di' => 'www_article', 'event' => 'onUnset', 'handler' => 'unset_for_article'),
		);
	}

}
?>
