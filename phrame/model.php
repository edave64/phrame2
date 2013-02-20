<?php
abstract class Model implements ArrayAccess {
  // This variable should be overwritten by your model
  // It defines the table name the model uses to store and load data
  static $table_name = 'table';
  
  // This defines the structure of the table. This is used to create the table.
  // The attribute 'ID' exists per default. 
  // Example:
  //   $struct = array(
  //     'name' => 'string',
  //     'body' => 'text',
  //     'category_id' => 'int'
  //   )
  static $struct    = array();
  
  // This defines attributes of the table that should be indexed, to access it
  // faster. The model expects this attributes to be set when saving.
  static $index     = array();
  
  // These attributes are not allowed to be assigned 
  static $forbidden = array();
  
  // Simple id based caching to reduce database load
  static $id_cache  = array();
  
  // If true, records will be cached by there id.
  static $caching   = true;
  
  // Initilizes a new instant.
  // $data contains the record data.
  function __construct ($data) {
    $this->data = $data;
    
    if ($data['ID'] && $caching) {
      static::$id_cache[$result[$id]] = $result;
    }
  }
  
  // A simple query-builder fragment that describes a select query on this
  // models table. This can be overwriten to define basic filtering or sorting
  // behavior. For example:
  //
  //   static function select () {
  //     return parent::select()->orderBy('updated_at');
  //   }
  //
  static function select () {
    return db::select()->from(static::$table_name);
  }
  
  // Performs a very simple database query for the first object that matches
  // $key = $value and returns a new instance of the model
  //
  //    $record = ExampleModel::sselect('name', 'example');
  //
  static function sselect ($key, $value = null) {
    $result = new static (static::select()->where($key, $value)->limit(1)->run()->fetch());
    return $result;
  }
  
  // Returns a DBTable abstraction to help with join requests
  static function table () {
    return new DBTable (static::$table_name);
  }
  
  // Performs a query on the database for the first object that matches ID = $id
  // and returns a new instance of the model.
  static function find ($id) {
    if (static::$id_cache[$id]) return static::$id_cache[$id];
    $result = new static (
      static::select()->where('ID', $id)->limit(1)->run()->fetch()
    );
    return $result;
  }
  
  // Returns the first record of the table
  static function first () {
    $result = new static (
      static::select()->limit(1)->run()->fetch()
    );
    return $result;
  }
  
  // returns an object for each dataset in table. This can be limited by
  // additional conditions in $where. It is used like this:
  //
  //    while ($item = ExampleModel::each($iter)) {
  //      // some code
  //    }
  //
  // $iter is used only by each to remember where it is. It should by
  // empty and you should do nothing with it. If you use multiple
  // each loops inside each other, remember to use different
  // $iter variables!
  static function each (&$iter, $where = array()) {
    if (!$iter) $iter = static::select()->where($where)->run();
    if ($result = $iter->fetch()) {
      return new static ($result);
    }
    unset($iter);
    return null;
  }

  // saves the changes of a record inside the database
  function save () {
    foreach (static::$index as $needed) {
      if (!key_exists($needed, $this->data))
        throw new Exception ('Index key '.$needed.' not found.');
    }
    if ($this->data['updatedAt']) $this->data['updatedAt'] = time();
    if (!$this->data['ID'])
      $this->create();
    else
      $this->update();
  }
  
  // deletes the record from the database
  function delete () {
    db::delete()->from(static::$table_name)
                ->where('ID', $this['ID'])
                ->limit(1)->run();
  }
  
  // Merges the record data with $hash
  // Only attributes that are not contained inside $forbidden will be allowed.
  function update_attributes ($hash) {
    if ($this->data['updatedAt']) $this->data['updatedAt'] = time();
    foreach (static::$forbidden as $forbidden)
      if ($hash[$forbidden]) {
        global $config;
        if ($config['state'] == 'production')
          unset($hash[$forbidden]);
        else
          throw new Exception ('Forbidden key '.$forbidden.' in update_attributes');
      }
    $this->data = array_merge($this->data, $hash);
    $this->save();
  }

  private function create () {
    if ($this->data['createdAt']) $this->data['createdAt'] = time();
    $id = db::insert()
      ->into(static::$table_name)
      ->that($this->data)
      ->run();
    
    $this['ID'] = $id;
    static::$id_cache[$id] = $this;
  }

  private function update () {
    db::update()
      ->from(static::$table_name)
      ->where('ID', $this['ID'])
      ->that($this->data)
      ->run();
  }

  # ArrayAccess
  public function offsetSet($offset, $value) {
    $this->data[$offset] = $value;
  }
  public function offsetExists($offset) {
    return isset($this->data[$offset]);
  }
  public function offsetUnset($offset) {
    unset($this->data[$offset]);
  }
  public function offsetGet($offset) {
    return isset($this->data[$offset]) ? $this->data[$offset] : null;
  }
  
  // Trys to create the models table
  static function setup () {
    db::create()
      ->name(static::$tableName)
      ->mayExist()
      ->row(static::$struct)
      ->key(static::$index)
      ->run();
  }
  
  // Trys to delete the models table
  static function drop () {
    db::drop(static::$tableName);
  }
}
?>