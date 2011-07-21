<?php
	/**
	* Extended db wrapper for php's native pdo library.
	*/

	class dbx extends PDO {
		const DEBUG_NONE = 0;
		const DEBUG_QUERY = 32;
		const DEBUG_ALL = 128;

		/** Default fetch mode, used by run(). */
		public $fetch_mode = PDO::FETCH_ASSOC;

		/** Debug level, used by run(). */
		public $debug = self::DEBUG_NONE;

		/** Default constructor override to catch connection exception. */
		function __construct($dsn, $user, $passwd) {
			try {
				parent::__construct($dsn, $user, $passwd);
			} catch (Exception $e) {
				die('
					<div style="font-size: 12px; border-top: 1px solid #666; border-left: 1px solid #666; border-right: 1px solid #666; padding: 1em 3em 1em 1em; font-weight: bold; background: #ddd; width: 450px; margin: 200px auto 0 auto; font-family: tahoma, arial, verdana, sans;">
						oBugger!
					</div>
					<div style="font-size: 12px; border-bottom: 1px solid #666; border-left: 1px solid #666; border-right: 1px solid #666; padding: 2em; background: #999; width: 450px; margin: 0 auto; text-align: center; font-family: tahoma, arial, verdana, sans;">
						<img src="' . IMG_PATH . 'down.png" /><br /><br />
						oBugger can&apos;t connect to the database server. Please contact the system administrator!
						<br /><br />
						<i>' . $dsn . '</i>
					</div>
				');
			}
		}
		/**
		* Helper method to quickly query and get access to db result set.
		*
		* Multiple usage patterns:
		*
		* <pre>
		*   $db->q($sql)
		*   $db->q($sql, $array_of_params)
		*   $db->q($sql, $p1, $p2, $p3, ...)
		* </pre>
		*/
		function run() {
			$params = func_get_args();
			$sql = array_shift($params);
			if (empty($params)) {
				return $this->query($sql);
			}
			if (is_array($params[0])) {
				$params = $params[0];
			}

			$st = $this->prepare($sql);
//print_r($params);
			foreach ($params as $key => &$param) {
				if (is_int($param)) $st->bindParam($key, intval($param), PDO::PARAM_INT);
				else $st->bindParam($key, $param);
				unset($params[$key]);
			}
			$st->setFetchMode($this->fetch_mode);
//print_r($params);
			if (!empty($params)) $ok = $st->execute($params);
			else $ok = $st->execute();
//print_r(array($st->errorInfo()));
			if (!$ok && $this->debug & self::DEBUG_QUERY) {
			$err = $st->errorInfo();
				throw new Exception("dbx: bad query [{$err[2]}][$sql]");
			} 
			return $st;
		}

		function last() {
			return $this->lastInsertId();
		}
	}
?>
