<?php
/**
*
* @author   9* <9@u9.ru>  refactored at  26072013
* @package	SBIN Diesel
*/
class ui_www_article_front extends user_interface
{
	public $title = 'www: Публикации - front';

	protected $deps = array(
	);
	
	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/';
		$this->unique_marker = 's';
		$this->default_root = 'articles';
	}
 	/**
	*	Интерпретатор входящих параметров
	*/
	public function pub_content()
	{
		if(SRCH_URI == '')
		{
			if($this->args['nolists'] == true)
			{
				header( 'Location: '.$this->args['redirect_404'].'', true, 301 );
				return '';
			}
			return $this->get_post_list();
		}
		if(preg_match('/tag\//',SRCH_URI))
		{
			return $this->list_by_tag();
		}
		if(!$this->location)
		{
			$di = data_interface::get_instance('www_article_url_indexer');
			$res = $di->search_by_uri('/'.SRCH_URI);
		}
		else
		{
			$res = $this->location;
		}
		if($res['item_id']>0)
		{
			return $this->get_item($res['item_id']);
		}
		if($res['item_id']==0 && $res['category_id'] >0)
		{
			$this->detected_category = $res['category_id'];
			return $this->get_post_list($res['category_id']);
		}
		if($res['id'] == 0 && SRCH_URI == '')
		{
			$possible_records =  $this->get_post_list();
			if($possible_records != false)
			{
				return $possible_records;
			}
		}
		return 'Ничего не найдено';
	}

	public function pub_locator()
	{
		$di = data_interface::get_instance('www_article_url_indexer');
		$args = $this->get_args();
		$res = $di->search_by_uri('/'.SRCH_URI,false,$args);
		$this->location = $res;
	}

	//9*  вывод списка постов  по входному   тэгу
	public function list_by_tag()
	{
		$parts = explode('/',SRCH_URI);
		$tag = $parts[1];
		$page = request::get('page', 1);
		$limit = $this->get_args('limit',5);
		$post_type = $this->get_args('post_type',1);
		$template = $this->get_args('list_template','list.html');
		$di =  data_interface::get_instance('www_article_indexer');
		$this->args['srch'] = array(
			'tag'=>$tag,
			'sort'=>'release_date',
			'dir'=>'DESC',
			'start' => ($page - 1) * $limit,
			'post_type'=>$post_type,
			'limit'=>$limit,
		);
		$this->prepare_search();
		$data = $di->get_list_by_srch($this->args['srch']);
		$pager = user_interface::get_instance('pager');
		$st=user_interface::get_instance('structure');
		$st->collect_resources($pager,'pager');
		$data['pager'] =$pager->get_pager(array('page' => $page, 'total' => $data['total'], 'limit' => $limit, 'prefix' => $_SERVER['QUERY_STRING']));
		return $this->parse_tmpl($template,$data);
	}

	//9* вывод  списка постов с типом поста  - Пост 
	public function get_post_list($id = '')
	{
		$di =  data_interface::get_instance('www_article_indexer');
		$limit = $this->get_args('limit',5);
		$post_type = $this->get_args('post_type',0);
		$post_tmpl = $this->get_args('list_template','list.html');
		$enable_pager = $this->get_args('enable_pager',false);
		$dir = $this->get_args('dir','DESC');
		$sort = $this->get_args('sort','release_date');
		$page = request::get('page', 1);
		$category_id = $this->get_args('category_id',0);
		$this->args['srch'] = array(
			'sort'=>$sort,
			'dir'=>$dir,
			'start' => ($page - 1) * $limit,
			'limit'=>$limit,
		);
		if($post_type>0)
		{
			$this->args['srch']['post_type'] = $post_type;
		}
		if($id>0)
		{
			$this->args['srch']['category_id'] = $id;
		}
		if($category_id > 0)
		{
			$this->args['srch']['category_id'] = $category_id;
		}	
		$this->prepare_search();
		$data = $di->get_list_by_srch($this->args['srch']);
		if(count($data['records']) == 0)
		{
			$enable_pager = false;
		}
		if($enable_pager == true)
		{
			$pager = user_interface::get_instance('pager');
			$st=user_interface::get_instance('structure');
			$st->collect_resources($pager,'pager');
			$data['pager'] =$pager->get_pager(array('page' => $page, 'total' => $data['total'], 'limit' => $limit, 'prefix' => $_SERVER['QUERY_STRING']));
		}
		return $this->parse_tmpl($post_tmpl,$data);

	}
	//9*  метод для вывода на морду  списка постов  по задаваемымы  в вьюпоинте  параметрам вплоть до шаблона вывода
	public function pub_get_list_parametric()
	{
		$di =  data_interface::get_instance('www_article_indexer');
		$limit = $this->get_args('limit',5);
		$post_type = $this->get_args('post_type');
		$template = $this->get_args('template','list.html');
		$page = request::get('page', 1);
		$category_id = $this->get_args('category_id','');
		$enable_pager = $this->get_args('pager',false);
		$sort = $this->get_args('sort','release_date');
		$dir = $this->get_args('dir','DESC');
		$this->args['srch'] = array(
			'sort'=>$sort,
			'dir'=>$dir,
			'start' => ($page - 1) * $limit,
			'post_type'=>$post_type,
			'limit'=>$limit,
		);
		if($post_type >0)
		{
			$this->args['srch']['post_type'] = $post_type;
		}
		if($category_id>0)
		{
			$this->args['srch']['category_id'] = $category_id;
			$di2 = data_interface::get_instance('www_article_type');
			$di2->_flush();
			$di2->set_args(array('_sid'=>$category_id));
			$cdata = $di2->_get()->get_results(0);
		}
		$this->prepare_search();
		$data = $di->get_list_by_srch($this->args['srch']);
		if($enable_pager == true)
		{
			$pager = user_interface::get_instance('pager');
			$st=user_interface::get_instance('structure');
			$st->collect_resources($pager,'pager');
			$data['pager'] =$pager->get_pager(array('page' => $page, 'total' => $data['total'], 'limit' => $limit, 'prefix' => $_SERVER['QUERY_STRING']));
		}
		$data['args'] = $this->args;
		$data['PAGE_URI'] = PAGE_URI;
		$data['SRHC_URI'] = SRCH_URI;
		if($category_id > 0)
		{
			$data['category'] = $cdata;
		}
		return $this->parse_tmpl($template,$data);
	}

	//9* 01052014 получаем публикацию по ID
	public function pub_get_item()
	{
		$template = $this->get_args('template','item.html');
		$args = $this->get_args();
		$di =  data_interface::get_instance('www_article_indexer');
		$id = $this->get_args('_sid',0);
		$data = $di->get_record($id);
		$data->args = $args;
		if($template == 'empty')
		{
			return $data->content;
		}
		return $this->parse_tmpl($template,$data);
	}

	public function get_item($id)
	{
		$template = $this->get_args('post_template','item.html');
		$di =  data_interface::get_instance('www_article_indexer');
		$data = $di->get_record($id);
		if($this->args['prev_next_post'])
		{
			$this->set_args(array('current'=>$id),true);
			$prev_next = $this->pub_get_prev_next();
			$data->prev_uri = $prev_next[0];
			$data->next_uri = $prev_next[1];
		}
		$st = user_interface::get_instance('structure');
		$st->add_title(strip_tags($data->title));
		$st->add_description(strip_tags($data->brief));
		return $this->parse_tmpl($template,$data);
	}

	// 9* 30072013 список категорий  в шаблон для разных нужд навигации например
	public function pub_categories()
	{
		$di =  data_interface::get_instance('www_article_type');
		$data = $di->get_counted_list();//9* если что, есть проще метод без подсчета вхождений - $di->get_simple_list()
		$data['current_category'] = $this->detected_category; //9* это работает только, если на странице ранее уже вызывался метод pub_content где детект происходит
		return $this->parse_tmpl('categories.html',$data);
	}
		/* 9* 29022015 выводит подкатегории по заданнйо ноде */
	public function pub_get_subcategories()
	{
		$template = $this->get_args('template','get_subcategories.html');
		$parent = $this->get_args('parent','1');
		$di =  data_interface::get_instance('www_article_type');
		$res = $di -> get_all_descendants($parent);
		$parent = $di->get_node_info($parent);
		$data['parent'] = $parent;
		$data['records'] =  $res;
		return $this->parse_tmpl($template,$data);
	}
	// 9*  30072013 список тагов
	public function pub_tags()
	{
		$di =  data_interface::get_instance('www_article_tags');
		$data = $di->get_assigned_list();
		return $this->parse_tmpl('tags.html',$data);
	}

	/* 9*  небольшой хук для интерпретации входных параметров и добавлению их в массив параметров для дальнейшей передачи в  DI
	*  исполльзуется  в  get_post_list  list_by_tag
	*/
	public function prepare_search()
	{
		$string = request::get('s','');
		if($string != '')
		{
			$this->args['srch']['s'] = $string;
		}
	}

	public function pub_get_prev_next()
	{
		$post_type = $this->get_args('post_type',1);
		$current_post = $this->get_args('current','0');
		$di = data_interface::get_instance('www_article_indexer');
		$di->push_args(array('_spost_type'=>$post_type));
		$di->set_order('release_date','desc');
		$res = $di->_get()->get_results();
		$di->pop_args();
		foreach($res as $key=>$value)
		{
			if($value->item_id == $current_post)
			{
				$prev_o = $res[$key+1];
				$next_o = $res[$key-1];
				if($prev_o)
				{
					$prev = $prev_o->uri;
				}
				if($next_o)
				{
					$next = $next_o->uri;
				}

			}
		}
		return array($prev,$next);
	}

	public function pub_comment()
	{
		$di = data_interface::get_instance('www_article_comment');
		$di->_flush();
		$di->set_args(request::get(array()));
		$req = request::get();
		$headers = getallheaders();
		if($headers['X-Requested-With'] == 'XMLHttpRequest')
		{
			if($req['item_id'] >0 && $req['email'] != '' && $req['author_name'] != '')
			{
				$di->sys_set(true);
			}
			response::send('Спасибо за ваше обращение.','text');
		}
		return false;
	}
	public function pub_trunc()
	{
		$st = data_interface::get_instance('structure');
		$data = $st->get_trunc_menu();
		if($this->location['item_id']>0)
		{
			$di = data_interface::get_instance('www_article_indexer');
			$di->_flush();
			$di->push_args(array(
				'_sitem_id'=>$this->location['item_id'],
			));
			$res = $di->_get()->get_results(0);
			$data[] = array('title'=>$res->title,'name'=>$res->uri,'uri'=>'/'.$res->uri.'/');
		}
		return $this->parse_tmpl('trunc.html',$data);
	}
	/* 9* 12022016 Выводит список  публикаций входящих в подкатеггории указанной категории  сгруппированный по подкатегориям */
	public function pub_sub_list_by_category()
	{
		$template = $this->get_args('template','sub_list_by_category.html');
		$data = array();
		$parent = $this->get_args('parent','1');
		$di = data_interface::get_instance('www_article_type');
		$cat =  $di->get_all_descendants($parent);
		$parent = $di->get_node_info($parent);
		foreach($cat as $key=>$value)
		{
			$ids[] =  $value['id'];
		}
		$di = data_interface::get_instance('www_article_in_category');
		$di->set_args(array('_scategory_id'=>$ids));
		$d2 = $di->join_with_di('www_article_indexer',array('item_id'=>'item_id'),array('title'=>'title','uri'=>'uri','published'=>'published'));
		$flds = array(
			'category_id',
			array('di'=>$d2,'name'=>'title'),
			array('di'=>$d2,'name'=>'uri'),
			array('di'=>$d2,'name'=>'published'),
		);
		$res = $di->extjs_grid_json($flds,false);
		foreach($res['records'] as $key=>$value)
		{
			foreach($cat as $key2=>$value2)
			{
				if(!array_key_exists('articles',$value2))
				{
					$cat[$key2]['articles'] = array();
				}
				if($value['category_id'] == $value2['id'])
				{
					$cat[$key2]['articles'][] = $value;
				}
			}
		}
		$data['records'] = $cat;
		$data['parent'] = $parent;
		$data['args'] = $this->get_args();
		return $this->parse_tmpl($template,$data);
	}
}
?>
