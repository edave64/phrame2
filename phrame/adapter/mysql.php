<?php
// MySQL query builder for phrame MVC Framework.
// by EDave

// Database adapter for MySQL
class DB {
  // Simple field types, and there mysql counterpart
  static $types = array(
    'string'     => 'VARCHAR(32)',
    'longstring' => 'VARCHAR(255)',
    'text'       => 'TEXT',
    'int'        => 'INT(10)',
    'shortint'   => 'INT(5)',
    'longint'    => 'INT(20)',
    'timestamp'  => 'TIMESTAMP',
    'bool'       => 'BOOLEAN'
  );
  static $log = array();
  static $connection;

  // { delegators
  static function select   () { return new SelectBuilder (); }
  static function insert   () { return new InsertBuilder (); }
  static function update   () { return new UpdateBuilder (); }
  static function delete   () { return new DeleteBuilder (); }
  static function create   () { return new CreateBuilder (); }
  static function drop     () { return new DropBuilder   (); }
  static function createDB () { return new CreateDBBuilder (); }
  static function dropDB   () { return new DropDBBuilder   (); }
  //   delegators }
  
  // Uses the informations inside $opt to establish a connection to the database
  static function connect ($opt) {
    self::$connection = new mysqli($opt['host'], $opt['user'], $opt['password'], $opt['database']);
    if (self::$connection->errno) {
      throw new Exception(
        'Database Error: Could not connect to database '.$opt['database'].".\n".self::$connection->error
      );
    }
  }
  
  // Tries to create a database and connect
  static function setup ($opt) {
    self::$connection = new mysqli($opt['host'], $opt['user'], $opt['password']);
    db::createDB()->name($opt['database'])->run();
    self::$connection->select_db($opt['database']);
    if (self::$connection->errno) {
      throw new Exception(
        'Database Error: Could not create database '.$opt['database'].".\n".self::$connection->error
      );
    }
  }
  
  function log ($string) {
    array_push(self::$log, $string);
    return $string;
  }

  // Executes a query and returns the DBResult
  function run () {
    $query = $this->build();
    $result = self::$connection->query($query);
    if (self::$connection->errno) {
      throw new Exception(
        'Database Error: Query failed! It was: '.$query."\n".mysql_error(self::$connection)
      );
    }
    return new DBResult($result);
  }
  
  // Converts a given value into a string that is safe SQL safe
  protected function secureValue ($val) {
  	if (is_numeric($val))
  	  return $val;
    elseif (is_bool($val))
      return ($val ? 1 : 0);
    elseif (is_null($val))
      return 'NULL';
    else
      return '\''.mysql_real_escape_string($val, self::$connection).'\'';
	}
}

// Represents the result of a DatabaseQuery.
class DBResult {
  var $mysqlResult;
  
  function __construct ($mysqlResult_) {
    $this->mysqlResult = $mysqlResult_;
  }

  // Executes the query and returns a single result
  function fetch () {
    return mysql_fetch_assoc($this->mysqlResult);
  }
}

class SelectBuilder extends DB {
  var $from_;
  var $which_;
  var $where_;
  var $limit_;
  var $conjunked_;
  var $order_;

  function __construct () {
    $this->conjunked = true;
    $this->fields_set = false;
  }
  
  // SQL: SELECT $fields
  function that ($fields) {
    if (is_array($fields)) {
      foreach ($fields as $field)
        $this->that($field);
    } else {
      if ($this->which_ == '')
        $this->which_ = $fields;
      else
        $this->which_ .= ', '.$fields;
    }
    return $this;
  }

  // SQL: FROM
  function from ($table, $db = false) {
    $this->from_ =
      ($this->from_ ? $this->from_.', ' : '').
      ($db ? '`'.$db.'`.' : '').
      ('`'.$table.'`');
    return $this;
  }
  
  function join ($otherTable, $fieldA, $fieldB, $type = 'inner') {
  	if (!$this->from_) throw new Exception ('Database Error: join before from');
  	$this->from_ = '('.$this->from_.') ';
  	switch ($type) {
  	case 'right': $this->from_ .= 'RIGHT JOIN'; break;
  	case 'left':  $this->from_ .= 'LEFT JOIN';  break;
  	case 'cross': $this->from_ .= 'CROSS JOIN'; break;
  	default: $this->from_ .= 'JOIN';
  	};
  	$this->from_ .= ' '.$this->tableName($otherTable).' ON '.$fieldA.' = '.$fieldB.'';
  	return $this;
  }


  // SQL: WHERE ... OR ...
  function or_ () {
    $this->where_ .= ' OR ';
    $this->conjunked = true;
    return $this;
  }

  // SQL: WHERE ... AND ...
  function and_ () {
    $this->where_ .= ' AND ';
    $this->conjunked = true;
    return $this;
  }
  
  // where(key, value)
  // where(array of key-value-pairs)
  function where ($key, $value = false) {
    if (!$this->where_) $this->where_ = 'WHERE ';
    if (!is_array($key)) {
      if (!$this->conjunked) $this->and_();
      $this->where_ .= '`'.$key.'` = '.$this->val($value).' ';
      $this->conjunked = false;
    } else
      foreach ($key as $subkey => $value) $this->where($subkey, $value);
    return $this;
  }
  
  function whereNot ($key, $value = false) {
    if (!$this->where_) $this->where_ = 'WHERE ';
    if (!is_array($key)) {
      if (!$this->conjunked) $this->and_();
      $this->where_ .= '`'.$key.'` <> '.$this->val($value).' ';
      $this->conjunked = false;
    } else
      foreach ($key as $subkey => $value) $this->where($subkey, $value);
    return $this;
  }
  
  function whereIn ($key, $collection = array()) {
    if (!$this->where_) $this->where_ = 'WHERE ';
    if (!is_array($key)) {
      if (!$this->conjunked) $this->and_();
      $this->where_ .= $key.' IN (';
      $first = true;
      foreach ($collection as $item) {
        $first ? $first = false : $this->where_ .= ',';
        $this->where_ .= $this->val($item);
      }
      $this->where_ .= ') ';
      $this->conjunked = false;
    } else
      foreach ($key as $subkey => $collection) $this->whereIn($subkey, $collection);
        return $this;
  }
  
  function whereNotIn ($key, $collection = array()) {
    if (!$this->where_) $this->where_ = 'WHERE ';
    if (!is_array($key)) {
      if (!$this->conjunked) $this->and_();
      $this->where_ .= $key.' NOT IN (';
      $first = true;
      foreach ($collection as $item) {
    		$first ? $first = false : $this->where_ .= ',';
        $this->where_ .= $this->val($item);
      }
      $this->where_ .= ') ';
      $this->conjunked = false;
    } else
      foreach ($key as $subkey => $collection) $this->whereNotIn($subkey, $collection);
    return $this;
  }

  function limit ($max, $offset = 0) {
    $this->limit_ = 'LIMIT '.($offset > 0 ? (int)$offset.', ' : '').(int)$max.' ';
    return $this;
  }
  
  function orderBy ($name, $desc = false) {
    $this->order = 'ORDER BY `'.$name.'`'.($desc ? ' DESC ' : ' ASC ');
    return $this;
  }

  function build () {
    return $this->log('SELECT '.($this->which_ ? $this->which_ : '* ').'FROM '.
           $this->from_.' '.($this->where_ ? $this->where_ : '').($this->order ?
           $this->order : '').($this->limit_ ?
           $this->limit_ : '').';');
  }
  
  private function tableName ($otherTable) {
  	return '`'.$otherTable.'`';
	}
}

class DeleteBuilder extends DB {
  function build () {
    return $this->log('DELETE FROM '.$this->from_.' '.($this->where_ ? $this->where_ : '').
      ($this->limit_ ? $this->limit_ : ''));
  }
}

class InsertBuilder extends DB {
  var $into_   = '';
  var $rows_   = '';
  var $values_ = '';

  function __construct () {
    $this->keys = array();
    $this->values = array();
  }

  function into ($table, $db = false) {
    $this->into_ = ($db ? '`'.$db.'`.' : '').'`'.$table.'` ';
    return $this;
  }

  function that ($row, $content = null) {
    if (!is_null($content)) {
      $this->rows_ = ($this->rows_ != '' ? $this->rows_.',' : '').'`'.$row.'`';
      $this->values_ = ($this->values_ != '' ? $this->values_.',' : '').
          (is_int($content) || is_bool($content) ?
              $content : '\''.mysql_real_escape_string($content).'\'');
    } else {
      foreach ($row as $key => $value) $this->that($key, $value);
    }
    return $this;
  }

  function build () {
    $string = 'INSERT INTO '.$this->into_.' ('.$this->rows_.') VALUES ('.
              $this->values_.')';
    return $this->log($string);
  }
}

class UpdateBuilder extends DB {
  var $that_ = '';

  function that ($row, $content = null) {
    if (is_array($row))
      foreach ($row as $name => $value) $this->that($name, $value);
    else {
      if ($this->that_ != '') $this->that_ .= ',';
      $this->that_ .= '`'.$row.'` = '.(is_int($content) || is_bool($content)?
          $content : '\''.mysql_real_escape_string($content).'\'').' ';
    }
    return $this;
  }

  function build () {
    $string = 'UPDATE '.$this->from_.' SET '.$this->that_.
              ' '.$this->where_.($this->limit_ ? $this->limit_ : '').';';
    return $this->log($string);
  }
}

class CreateBuilder extends DB {
  var $rows = '';
  var $indices_ = '';
  var $id = true;
  var $name;
  var $mayExist_ = false;
  var $charset_ = 'utf8';
  
  function row ($name, $type = null) {
    if (is_array($name))
      foreach ($name as $row => $type) $this->row($row, $type);
    else {
      if ($this->rows) $this->rows .= ',';
      $this->rows .= '`'.$name.'` '.DB::$types[$type];
    }
    return $this;
  }
  
  function key ($name) {
    if (is_array($name))
      foreach ($name as $key) $this->key($key);
    else
      $this->indices .= ($this->indices ? ',' : '').'KEY('.$name.')';
    return $this;
  }

  function name ($name) {
    $this->name = $name;
    return $this;
  }
  
  function mayExist ($val = true) {
    $this->mayExist_ = $val;
    return $this;
  }
  
  function noID ($val = true) {
    $this->id = !$val;
    return $this;
  }
  
  function charset ($val) {
    $this->charset_ = $val;
    return $this;
  }

  function build () {
    $string = 'CREATE TABLE '.($this->mayExist_? 'IF NOT EXISTS ' : '').'`'.$this->name.'` (';
    if ($this->id) {
      $rows = $this->rows;
      $this->rows = '';
      $this->row('ID', 'int');
      $this->rows .= ' AUTO_INCREMENT';
      if ($rows) $this->rows .= ','.$rows;
    }
    $string .= $this->rows;
    
    if ($this->id) $string .= ',PRIMARY KEY(`ID`)';
    
    $string .= $this->indices_.') DEFAULT CHARSET='.$this->charset_.';';
    return $this->log($string);
  }
}

class DropBuilder extends DB {
  var $table = '';
  var $haveToExist_ = false;
  
  function __construct () {}
  
  function table ($name) {
    $this->table = $name;
    return $this;
  }
  
  function haveToExist ($val = true) {
    $this->haveToExist_ = $val;
    return $this;
  }
  
  function build () {
    return $this->log('DROP TABLE '.($this->haveToExist_ ? 'IF EXISTS ' : '').$this->table.';');
  }
}

class CreateDBBuilder extends DB {
  var $name;
  
  function name ($name) {
    $this->name = $name;
    return $this;
  }
  
  function build () {
    return $this->log('CREATE DATABASE IF NOT EXISTS `'.$this->name.'`;');
  }
}

class DropDBBuilder extends DB {
  var $name;
  
  function name ($name) {
    $this->name = $name;
    return $this;
  }
  
  function build () {
    return $this->log('DROP DATABASE '.$this->name.';');
  }
}
?>