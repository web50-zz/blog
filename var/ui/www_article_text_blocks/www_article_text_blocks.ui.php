<?php
/**
*
* @author 9@u9.ru	30.08.2018
* @package	SBIN Diesel
*/
class ui_www_article_text_blocks extends user_interface
{
	public $title = 'www: Публикации - блоки текста';

	protected $deps = array(
		'main' => array(
			'www_article_text_blocks.grid',
		),
		'grid' => array(
		),
	);
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

	/**
	*       Page configure form
	*/
	protected function sys_configure_form()
	{
		$tmpl = new tmpl($this->pwd() . 'configure_form.js');
		response::send($tmpl->parse($this), 'js');
	}

	protected function sys_main()
	{
		$tmpl = new tmpl($this->pwd() . 'main.js');
		response::send($tmpl->parse($this), 'js');
	}

	protected function sys_main_grid()
	{
		$tmpl = new tmpl($this->pwd() . 'main_grid.js');
		response::send($tmpl->parse($this), 'js');
	}

	protected function sys_main_filter()
	{
		$tmpl = new tmpl($this->pwd() . 'main_filter.js');
		response::send($tmpl->parse($this), 'js');
	}

	protected function sys_grid()
	{
		$tmpl = new tmpl($this->pwd() . 'grid.js');
		response::send($tmpl->parse($this), 'js');
	}

	protected function sys_item_form()
	{
		$tmpl = new tmpl($this->pwd() . 'item_form.js');
		response::send($tmpl->parse($this), 'js');
	}
}
?>
