<?php
global $db_connection;
global $db;
global $db_conf;
global $config;

class model implements arrayaccess {
    var $position;
    var $table;

    function save () {
        foreach ($this->getIndex() as $needed) {
            if (!key_exists($needed, $this->data)) {
                print_r($this->data);
                throw new Exception ('Index key '.$needed.' not found.');
            }
        }
        if ($this->data['updatedAt']) $this->data['updatedAt'] = time();
        if (!$this->data['ID'])
            $this->create();
        else
            $this->update();
    }

    function delete () {
      query::delete()->from($this->table)
                     ->where('ID', $this->data['ID'])
                     ->limit(1)->run();
    }
    
    function getIndex () {
      $klass = get_class($this);
      return $klass::$index;
    }

    function update_attributes ($hash) {
        foreach ($this->forbidden as $forbidden)
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
        query::insert()->into($this->table)->that($this->data)->run();
        $this->data = query::select()->from($this->table)->where($this->data)->getOne();
    }

    private function update () {
        query::update()->from($this->table)->where('ID', $this->data['ID'])->that($this->data)->run();
    }

    # arrayaccess
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
    
    static function setup ($childModel) {
        db::create()
        ->name($childModel::$tableName)
        ->mayExist()
        ->row($childModel::$struct)
        ->key($childModel::$index)
        ->run();
    }
    
    static function drop ($childModel) {
        db::drop($childModel::$tableName);
    }
    
    function select () {
        return query::select()->from();
    }
}
?>