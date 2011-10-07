<?php
abstract class Controller {
	protected $registry;	
	protected $id;
	protected $layout;
	protected $template;
	protected $children = array();
	protected $data = array();
	protected $output;
	
	public function __construct($registry) {
		$this->registry = $registry;
	}
	
	public function __get($key) {
		return $this->registry->get($key);
	}
	
	public function __set($key, $value) {
		$this->registry->set($key, $value);
	}
			
	protected function forward($route, $args = array()) {
		return new Action($route, $args);
	}

	protected function redirect($url, $status = 302) {
		header('Status: ' . $status);
		header('Location: ' . str_replace('&amp;', '&', $url));
		exit();
	}
	
	protected function getChild($child, $args = array()) {
		$action = new Action($child, $args);
		$file = $action->getFile();
		$class = $action->getClass();
		$method = $action->getMethod();
	
		if (file_exists($file)) {
			require_once($file);

			$controller = new $class($this->registry);
			
			$controller->$method($args);
			
			return $controller->output;
		} else {
			exit('Error: Could not load controller ' . $child . '!');
		}		
	}
	
	protected function render($ttl = 0) {
		foreach ($this->children as $child) {
			$this->data[basename($child)] = $this->getChild($child);
		}
		
		if ((CACHE_BACKEND == 'mc') && ($ttl != 0)) {
			$this->output=$this->cache->get(md5(DIR_TEMPLATE . $this->template)); 
			if($this->output) return $this->output;
		}
				
		if (file_exists(DIR_TEMPLATE . $this->template)) {
			
			extract($this->data);
			
			ob_start();
      
			require(DIR_TEMPLATE . $this->template);
      
			$this->output = ob_get_contents();

			ob_end_clean();
			
			if ((CACHE_BACKEND == 'mc') && ($this->output != "") && ($ttl != 0))
				 $this->cache->set(md5(DIR_TEMPLATE . $this->template),$this->output,$ttl);
			
			return $this->output;
			
		} else {
			exit('Error: Could not load template ' . DIR_TEMPLATE . $this->template . '!');
		}
	}
}
?>
