<?php


class db
{
	public static $con = null;
	private static $curdb = null;
	public static $last_sql;

	public static function connect($server, $db_name, $user, $pass)
	{
		self::$con = mysqli_connect($server, $user, $pass, $db_name);
		self::$curdb = $db_name;

		if (!self::$con) {
			echo ('err : ' . mysqli_error(self::$con));
		}

		self::query('set names "utf8mb4"');

		if (!mysqli_select_db(self::$con, $db_name)) {
			echo ('err : ' . mysqli_error(self::$con));
		}
	}

	public static function query($sql)
	{
		// if(isset($_GET['dbdbdbdb'])) {
		//     echo(mysqli_error(self::$con) .'<bR><br>' . $sql."\r\n");
		// }

		$rs = @mysqli_query(self::$con, $sql);
		$errno = @mysqli_errno(self::$con);
		self::$last_sql = $sql;
		return array((int) $errno, &$rs);
	}

	public static function val($tb_name, $field_name, $cond, $order_by = '')
	{
		$cond = 'WHERE ' . $cond;

		if ($order_by !== '') {
			$order_by = 'ORDER BY ' . $order_by;
		}

		$result = self::query('SELECT ' . $field_name . ' FROM ' . $tb_name . ' ' . $cond . ' ' . $order_by . ' LIMIT 1;');

		if ($result[0] == 0) {
			if (mysqli_num_rows($result[1]) > 0) {
				if ($row = mysqli_fetch_row($result[1])) {
					return $row[0];
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {

			return false;
		}

	}

	public static function exists($tb_name, $conds, $global_conds = '')
	{

		if (is_array($conds)) {
			if ($global_conds === '') {
				$where_str = implode(' OR ', $conds) . '';
			} else {
				$where_str = '(' . implode(' OR ', $conds) . ') AND ' . $global_conds;
			}

			$result = self::query('SELECT COUNT(1) FROM ' . $tb_name . ' WHERE ' . $where_str . ';');

			if ($result[0] === 0) {
				$assoc = mysqli_fetch_row($result[1]);
				return $assoc[0];
			} else {
				//echo('Database Error (exists-multiple): '.mysqli_error(self::$con));
				return false;
			}
		} else {

			if ($global_conds !== '') {
				$conds = '(' . $conds . ') AND ' . $global_conds;
			}

			$result = self::query('SELECT COUNT(1) as cnt FROM ' . $tb_name . ' WHERE ' . $conds . ' LIMIT 1;');

			if ($result[0] === 0) {
				$row = mysqli_fetch_array($result[1]);
				if ($row['cnt'] > 0) {
					return true;
				} else {
					return false;
				}
			} else {
				return $result;
			}
		}

	}

	public static function last_count()
	{
		$rs = self::query('SELECT FOUND_ROWS() as total');
		$row = mysqli_fetch_array($rs[1]);
		return $row['total'];
	}

	public static function count($tb_name, $conds, $global_conds = '')
	{
		if (is_array($conds)) {
			if ($global_conds === '') {
				$where_str = implode(' OR ', $conds);
			} else {
				$where_str = '(' . implode(' OR ', $conds) . ') AND ' . $global_conds;
			}

			$result = self::query('SELECT COUNT(1) FROM ' . $tb_name . ' WHERE ' . $where_str . ';');

			if ($result[0] === 0) {
				$assoc = mysqli_fetch_row($result[1]);
				return $assoc[0];
			} else {
				//misc::raise('Database Error (count-multiple): '.mysqli_error());
				return false;
			}
		} else {

			if ($global_conds !== '') {
				$conds = '(' . $conds . ') AND ' . $global_conds;
			}

			if ($conds != '') {
				$conds = ' WHERE ' . $conds;
			}

			$result = self::query('SELECT COUNT(1) FROM ' . $tb_name . ' ' . $conds . ';');

			if ($result[0] == 0) {
				$assoc = mysqli_fetch_row($result[1]);
				return $assoc[0];
			} else {
				return $result;
			}
		}

	}

	public static function sum($tb_name, $conds, $field, $global_conds = '')
	{
		if (is_array($conds)) {
			if ($global_conds === '') {
				$where_str = implode(' OR ', $conds);
			} else {
				$where_str = '(' . implode(' OR ', $conds) . ') AND ' . $global_conds;
			}

			$result = self::query('SELECT SUM(' . $field . ') FROM ' . $tb_name . ' WHERE ' . $where_str . ';');

			if ($result[0] === 0) {
				$assoc = mysqli_fetch_row($result[1]);
				return $assoc[0];
			} else {
				//misc::raise('Database Error (count-multiple): '.mysqli_error());
				return false;
			}
		} else {

			if ($global_conds !== '') {
				$conds = '(' . $conds . ') AND ' . $global_conds;
			}

			if ($conds != '') {
				$conds = ' WHERE ' . $conds;
			}

			$result = self::query('SELECT SUM(' . $field . ') FROM ' . $tb_name . ' ' . $conds . ';');

			if ($result[0] == 0) {
				$assoc = mysqli_fetch_row($result[1]);
				return $assoc[0];
			} else {
				return $result;
			}
		}

	}

	public static function row($tb_name, $cond = '', $flds = '', $order_by = '')
	{
		if ($order_by !== '') {
			$order_by = 'ORDER BY ' . $order_by;
		}

		if ($cond !== '') {
			$cond = 'WHERE ' . $cond;
		}

		if ($flds === '') {
			$flds = '*';
		}

		$result = self::query('SELECT ' . $flds . ' FROM ' . $tb_name . ' ' . $cond . ' ' . $order_by . ' LIMIT 1');

		if ($result[0] === 0) {
			if ($row = mysqli_fetch_assoc($result[1])) {
				return $row;
			} else {
				return false;
			}
		} else {
			return $result;
		}
	}

	public static function rs($tb_name, $cond = '', $flds = '', $order_by = '', $limit = '')
	{
		if ($order_by !== '') {
			$order_by = 'ORDER BY ' . $order_by;
		}

		if ($cond !== '') {
			if (strpos($cond, 'WHERE') === FALSE) {
				$cond = 'WHERE ' . $cond;
			}
		}
		if ($limit !== '') {
			$limit = 'LIMIT ' . $limit;
		}
		if ($flds === '') {
			$flds = '*';
		}

		$result = self::query('SELECT ' . $flds . ' FROM ' . $tb_name . ' ' . $cond . ' ' . $order_by . ' ' . $limit);

		if ($result[0] === 0) {
			$result_array = array();
			while ($row = mysqli_fetch_assoc($result[1])) {
				$result_array[] = $row;
			}
			return $result_array;
		} else {
			return $result;
		}
	}

	public static function rs_flat($tb_name, $cond, $fld, $order_by = '', $limit = '')
	{
		if ($order_by !== '') {
			$order_by = 'ORDER BY ' . $order_by;
		}

		if ($cond !== '') {
			$cond = 'WHERE ' . $cond;
		}
		if ($limit !== '') {
			$limit = 'LIMIT ' . $limit;
		}

		$result = self::query('SELECT ' . $fld . ' FROM ' . $tb_name . ' ' . $cond . ' ' . $order_by . ' ' . $limit);

		if ($result[0] === 0) {
			$result_array = array();
			while ($row = mysqli_fetch_row($result[1])) {
				$result_array[] = $row[0];
			}
			return $result_array;
		} else {
			return $result;
		}
	}


	public static function update($tb_name, $upd_str, $cond, $limit = '')
	{
		if ($cond !== '') {
			$cond = 'WHERE ' . $cond;
		}
		if ($limit !== '') {
			$limit = 'LIMIT ' . $limit;
		}

		self::query('UPDATE ' . $tb_name . ' SET ' . $upd_str . ' ' . $cond . ' ' . $limit);

		return mysqli_affected_rows(self::$con);
	}

	public static function insert($tb_name, $fields, $vals, $has_ai = false, $on_dup = '')
	{
		if ($on_dup !== '') {
			$on_dup = 'ON DUPLICATE KEY UPDATE ' . $on_dup;
		}

		if (substr($vals, 0, 1) !== '(') {
			$vals = '(' . $vals . ')';
		}

		$result = self::query('INSERT INTO ' . $tb_name . ' (' . $fields . ') VALUES ' . $vals . ' ' . $on_dup);

		if ($result[0] === 0) {
			if ($has_ai) {
				return mysqli_insert_id(self::$con);
			} else {
				return mysqli_affected_rows(self::$con);
			}
		} else {
			return $result;
		}

	}

	public static function insert_ignore($tb_name, $fields, $vals, $has_ai = false, $on_dup = '')
	{
		if ($on_dup !== '') {
			$on_dup = 'ON DUPLICATE KEY UPDATE ' . $on_dup;
		}

		if (substr($vals, 0, 1) !== '(') {
			$vals = '(' . $vals . ')';
		}

		$result = self::query('INSERT IGNORE INTO ' . $tb_name . ' (' . $fields . ') VALUES ' . $vals . ' ' . $on_dup);

		if ($result[0] === 0) {
			if ($has_ai) {
				return mysqli_insert_id(self::$con);
			} else {
				return mysqli_affected_rows(self::$con);
			}
		} else {
			return $result;
		}

	}

	public static function _insert($tb_name, $fields, $vals, $key, $rand)
	{

		$result = self::query('INSERT INTO ' . $tb_name . ' (`' . $key . '` , ' . $fields . ') SELECT MAX(' . $key . ')+' . $rand . ' , ' . $vals . ' FROM ' . $tb_name);

		if ($result[0] === 0) {

			return mysqli_insert_id(self::$con);

		} else {
			return $result;
		}

	}


	public static function delete($tb_name, $cond, $limit = '')
	{
		if ($limit !== '') {
			$limit = 'LIMIT ' . $limit;
		}

		$result = self::query('DELETE FROM ' . $tb_name . ' WHERE ' . $cond . ' ' . $limit);

		if ($result[0] === 0) {
			return mysqli_affected_rows(self::$con);
		} else {
			return $result;
		}


	}

	public static function escape(&$text)
	{
		return mysqli_real_escape_string(self::$con, $text);
	}
}

db::connect(DB_HOST, DB_NAME, DB_USER, DB_PASS);
?>