<?php

namespace BBC;

/**
 * Tiny ORM to quickly save / return data from the db
 */
class TinyORM
{
    public static $app;
    protected $table;
    protected $id = 'pid';
    protected $original = array();
    protected $changed = array();

    public function __construct($table, array $data = array())
    {
        $this->table = $table;
        $this->changed = $data;
    }

    public function __set($name, $value)
    {
        $this->changed[$name] = $value;
    }

    public function __get($name)
    {
        if(array_key_exists($name, $this->changed))
        {
            return $this->changed[$name];
        }
        elseif(array_key_exists($name, $this->original))
        {
            return $this->original[$name];
        }

        return null;
    }

    public function save()
    {
        if(empty($this->changed)) return true;

        if(empty($this->original)) {
            return $this->create();
        } else {
            return $this->update();
        }

        return false;
    }

    protected function create()
    {
        $sql = 'INSERT INTO ' . $this->table . ' SET ';
        $sql .= implode(' = ?, ', array_keys($this->changed)) . ' = ?';

        $stmt = self::$app['db']->prepare($sql);
        foreach(array_values($this->changed) as $idx => $value) {
            $stmt->bindValue($idx + 1, $value);
        }

        return $stmt->execute();
    }

    protected function update()
    {
        $sql = 'UPDATE ' . $this->table . ' SET ';
        $sql .= implode(' = ?, ', array_keys($this->changed)) . ' = ?';
        $sql .= ' WHERE ' . $this->id . ' = :id';

        $stmt = self::$app['db']->prepare($sql);
        foreach(array_values($this->changed) as $idx => $value) {
            $stmt->bindValue($idx + 1, $value);
        }
        $stmt->bindValue('id', $this->__get($this->id));

        return $stmt->execute();
    }

}