<?php
final class Cache { 
	private $expire = 3600;
	private $pmc;
	private $pmc_zlib = 0;

  	public function __construct() {

		if (CACHE_BACKEND == 'mc') {
		    $this->pmc = new Memcache;
		    $this->pmc->connect(MC_HOST, MC_PORT) or die("Can't connect to memcached on ". MC_HOST .":".MC_PORT);
		} else {
			$files = glob(DIR_CACHE . 'cache.*');
		
			if ($files) {
				foreach ($files as $file) {
					$time = substr(strrchr($file, '.'), 1);

					if ($time < time()) {
						if (file_exists($file)) {
							unlink($file);
						}
					}
				}
			}
		}
  	}

	public function get($key) {
	
		if (CACHE_BACKEND == 'mc') {
			return $this->pmc->get($key	);
		} else {
			$files = glob(DIR_CACHE . 'cache.' . $key . '.*');

			if ($files) {
				$cache = file_get_contents($files[0]);
				return unserialize($cache);
			}
		}
	}

  	public function set($key, $value, $ttl = 3600) {
	
		if (CACHE_BACKEND == 'mc') {
			//var_dump($value);
			return $this->pmc->set($key,$value,$this->pmc_zlib,$ttl);
		} else {
		
			$this->delete($key);
		
			$file = DIR_CACHE . 'cache.' . $key . '.' . (time() + $this->expire);
			$handle = fopen($file, 'w');
			fwrite($handle, serialize($value));
			fclose($handle);
		}
  	}
	
  	public function delete($key) {
	
		if (CACHE_BACKEND == 'mc') {
			$this->pmc->delete($key);
		} else {
			$files = glob(DIR_CACHE . 'cache.' . $key . '.*');
		
			if ($files) {
				foreach ($files as $file) {
					if (file_exists($file)) {
						unlink($file);
						clearstatcache();
					}
				}
			}
		}
	}
}
?>