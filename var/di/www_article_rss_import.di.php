<?php
/**
*
* @author       9*  9@u9.ru 
* @package	SBIN Diesel
* 9* if in registry exists key - 'www_article_thumb_size' for example  90x20,  then this size will be applied on thumb over current www_article defaults 
*/
class di_www_article_rss_import extends data_interface
{
	public $title = 'www_article: RSS импорт';
	
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
	protected $name = 'www_article_rss_import';

	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => 1),
		'order' => array('type' => 'integer'),
		'post_type' => array('type' => 'integer'),
		'creator_uid' => array('type' => 'integer'),
		'changer_uid' => array('type' => 'integer'),
		'changed_date' => array('type' => 'date'),
		'created_date' => array('type' => 'date'),
		'changed_date' => array('type' => 'date'),
		'title' => array('type' => 'string'),
		'source' => array('type' => 'string'),
	);
	
        public function __construct () {
            // Call Base Constructor
            parent::__construct(__CLASS__);
        }

	/**
	*	Получить JSON-пакет данных для ExtJS-грида
	* @access protected
	*/
	protected function sys_list()
	{
		$this->_flush();
		$di = $this->join_with_di('www_article_post_types',array('post_type'=>'id'),array('title'=>'post_type_title'));
		$what = array('id', 
				'order', 
				'title',
				'post_type',
				'source',
				array('di'=>$di,'name'=>'title'),
				);
		$this->extjs_grid_json($what);

	}
	
	/**
	*	Получить данные для ExtJS-формы
	* @access protected
	*/
	protected function sys_get()
	{
		$this->_flush();
		$this->extjs_form_json();
	}


	protected function sys_set()
	{
		$fid = $this->get_args('_sid');
		$silent = $this->get_args('silent',false);
		try{
			$this->check_input();
			if ($fid > 0)
			{
				$this->_flush();
				$this->_get();
				$file = $this->get_results(0);
			}
			$file = array();
			$args =  $this->get_args();
			if (!($fid > 0))
			{
				$args['order'] = $this->get_new_order();
				$args['changer_uid'] = UID;
				$args['creator_uid'] = UID;
				$args['created_date'] = date('Y-m-d H:i:s',time());
				$args['changed_date'] = date('Y-m-d H:i:s',time());
			}
			else
			{
				$args['changer_uid'] = UID;
				$args['changed_date'] = date('Y-m-d H:i:s',time());
			}
			$this->set_args($args);
			$this->_flush();
			$this->insert_on_empty = true;
			$result = $this->extjs_set_json(false);
			if($silent == true)
			{
				$this->args['result'] = $result;
				return;
			}
			response::send($result, 'json');
		}
		catch(Exception $e)
		{
			$data['success'] = false;
			$data['errors'] =  $e->getMessage();
			if($silent == true)
			{
				$this->args['result'] = $data;
				return;
			}
			response::send($data,'json');
		}

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
	*	Удалить 
	* @access protected
	*/
	protected function sys_unset()
	{
		if ($this->args['records'] && !$this->args['_sid'])
		{
			$this->args['_sid'] = request::json2int($this->args['records']);
		}
		$this->_flush();
		$data = $this->extjs_unset_json(false);
		response::send($data, 'json');
	}

	/**
	* получаем новое значение  order
	*/
	private function get_new_order()
	{
		$this->_flush();
		$this->_get("SELECT MAX(`order`) + 1 AS `order` FROM `{$this->name}`");
		return $this->get_results(0, 'order');
	}
	/**
	*	Реорганизация порядка вывода
	*/
	protected function sys_reorder()
	{
		list($npos, $opos) = array_values($this->get_args(array('npos', 'opos')));
		$values = $this->get_args(array('opos', 'npos', 'id', 'pid'));

		if ($opos < $npos)
			$query = "UPDATE `{$this->name}` SET `order` = IF(`id` = :id, :npos, `order` - 1) WHERE `order` >= :opos AND `order` <= :npos";
		else
			$query = "UPDATE `{$this->name}` SET `order` = IF(`id` = :id, :npos, `order` + 1) WHERE `order` >= :npos AND `order` <= :opos";

		$this->_flush();
		$this->connector->exec($query, $values);
		response::send(array('success' => true), 'json');
	}

	//9* 23072013  проверяем  инпут для uri
	public function check_input()
	{
		$args = $this->args;
		if($args['uri'] != '')
		{
			if(!preg_match('/^[a-zA-Z1-90\-_]+$/',$args['uri']))
			{
			//			throw new Exception('URI может содержать только латинские буквы, цифры  и символы _ -. Пробелы не допустимы');
			}
		}
	}

	public function sys_import()
	{
		try{
			$id = $this->get_args('id');
			if(!($id >0))
			{
				throw new Exception('ID missed');
			}
			$this->_flush();
			$this->set_args(array('_sid'=>$fid));
			$res = $this->_get()->get_results(0);
			$src = $res->source;
			$post_type = $res->post_type;
			if($src == '')
			{
			}
			$data = file_get_contents($src);
			$this->parser = xml_parser_create('UTF-8');
			xml_set_object($this->parser, $this);
			xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 1);
			xml_set_element_handler($this->parser, 'tag_open', 'tag_close');
			xml_set_character_data_handler($this->parser, 'cdata');
			if (!xml_parse($this->parser, $data))
			{
				$this->data = array();
				$this->error_code = xml_get_error_code($this->parser);
				$this->error_string = xml_error_string($this->error_code);
				$this->current_line = xml_get_current_line_number($this->parser);
				$this->current_column = xml_get_current_column_number($this->parser);
			}
			else
			{
				$this->data = $this->data['child'];
			}
			xml_parser_free($this->parser);
			if(!is_array($this->data['RSS']))
			{
				throw new Exception('Не удалось прочесть канал');
			}
			$items = $this->data['RSS'][0]['child']['CHANNEL'][0]['child']['ITEM'];
			foreach($items as $k=>$v)
			{
				$in = array();
				$in['title'] = trim($v['child']['TITLE'][0]['data']);
				$in['brief'] = trim(strip_tags($v['child']['DESCRIPTION'][0]['data']));
				$in['release_date'] = $v['child']['PUBDATE'][0]['data'];
				$in['source'] = $v['child']['LINK'][0]['data'];
				$in['silent'] = true;
				$in['post_type'] = $post_type;
				$in['release_date'] = date('Y-m-d H:i:s',strtotime($in['release_date']));
				$di = data_interface::get_instance('www_article');
				$sql = "SELECT count(*) as cnt from www_article where title = '".$in['title']."' and source ='".$in['source']."'";
				$di->_flush();
				$res = $di->_get($sql)->get_results(0);
				if($res->cnt == 0)
				{
					$di = data_interface::get_instance('www_article');
					$di->_flush();
					$di->set_args($in);
					$res2 = $di->sys_set(true);
				}
			}
		}
		catch(Exception $e)
		{
			response::send(array('success' => false,'msg'=>$e->getMessage()), 'json');
		}
		response::send(array('success' => true,'msg'=>'Сделано'), 'json');
	}

	private function tag_open($parser, $tag, $attribs)
	{
		$this->data['child'][$tag][] = array('data' => '', 'attribs' => $attribs, 'child' => array());
		$this->datas[] =& $this->data;
		$this->data =& $this->data['child'][$tag][count($this->data['child'][$tag])-1];
	}

	private function cdata($parser, $cdata)
	{
		$this->data['data'] .= $cdata;
	}

	private function tag_close($parser, $tag)
	{
		$this->data =& $this->datas[count($this->datas)-1];
		array_pop($this->datas);
	}

}
?>
