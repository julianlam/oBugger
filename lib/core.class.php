<?php

require_once ('dbx.php');

// if this is set on by your webserver, then client access doesn't work
ini_set('zend.ze1_compatibility_mode', 0);

function db() {
  $debug_mode = false;
  static $db = null;
  global $_DB_CONFIG;
	  if (!$debug_mode) {
		static $db;
		if (!$db) {
		  $db = new dbx($_DB_CONFIG['DB_DSN'], 
			$_DB_CONFIG['DB_USER'],
			$_DB_CONFIG['DB_PASSWD']);
	}
  }
  else {
    if (is_null($db)) {
    class dbx_debug extends dbx {
      private $log = array();
      function run($sql, $p = array()) {
        $begin_t = microtime();
        $ok = parent::run($sql, $p);
        $end_t = microtime();
        $this->log[] = array($sql, sprintf('%.4f', array_sum(explode(' ', $end_t)) - array_sum(explode(' ', $begin_t))));
        return $ok;
  }
      function __destruct() {
        echo '<pre>';
        foreach ($this->log as $i) {
          printf("<strong>%.4f</strong>\t<code>%s</code>\n",
            $i[1], $i[0]);
    }
        echo '</pre>';
  }
}
    
    $db = new dbx_debug($_DB_CONFIG['DB_DSN'], 
        $_DB_CONFIG['DB_USER'],
        $_DB_CONFIG['DB_PASSWD']);
}
  }
  return $db;
}

class web {
	public $middleware = array();
	public $result = null;
	
	function __construct($map = array(), $middleware = array()) {
		foreach ($middleware as $a) {
		$this->middleware[] = $a;
		}
		if (!empty($map)) {
		  $this->run($map);
		}
	}

	function run($map = array(), $q = null) {
		if (is_null($q)) {
	  		$q = $_SERVER['REQUEST_URI'];
		}
    		$ok = false;
   		foreach (array_reverse($map) as $k => $v) {
	      		if (preg_match("#$k#", $q, $matches)) {
				$ok = true;
				break;
	  		}
		}
	    	if (!$ok) {
	      		return false;
		}
		array_shift($matches);
		$client = new $v($this);
		$this->trigger('run_before');
		$this->result = $this->dispatch($client, $matches);
		$this->trigger('run_after');
		unset($client);
		return true;
	}

	function dispatch($client, $params = array()) {
		return call_user_func_array(array($client, $_SERVER['REQUEST_METHOD']),$params);
	}

	function trigger($name, $args = array()) {
		array_unshift($args, $this);
		foreach ($this->middleware as $i) {
			if (method_exists($i, $name)) {
				call_user_method_array($name, $i, $args);
			}
		}
	}
}

?>
