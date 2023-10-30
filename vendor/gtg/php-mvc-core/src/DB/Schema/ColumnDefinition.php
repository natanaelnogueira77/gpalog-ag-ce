<?php 

namespace GTG\MVC\DB\Schema;

class ColumnDefinition 
{
    private string $columnName;
    private string $type;
    private array $params;

    public function __construct(string $columnName, string $type, array $params = []) 
    {
        $this->columnName = $columnName;
        $this->type = $type;
        $this->params = $params;
    }

    public function primaryKey(): static 
    {
        $this->params['primaryKey'] = true;
        return $this;
    }

    public function nullable(): static 
    {
        $this->params['nullable'] = true;
        return $this;
    }

    public function default(mixed $value): static 
    {
        $this->params['default'] = $value;
        return $this;
    }

    private function getParam(string $key): mixed 
    {
        return isset($this->params[$key]) ? $this->params[$key] : null;
    }

    public function toMySQL(): string 
    {
        $sql = '';
        if($this->getParam('command') == 'add_column') {
            $sql .= ' ADD COLUMN ';
        } elseif($this->getParam('command') == 'modify_column') {
            $sql .= ' MODIFY COLUMN ';
        } elseif($this->getParam('command') == 'change_column') {
            $sql .= " CHANGE COLUMN `{$this->getParam('from')}` ";
        } elseif($this->getParam('command') == 'drop_column') {
            $sql .= ' DROP COLUMN ';
        } elseif($this->getParam('command') == 'rename_table') {
            $sql .= ' RENAME ';
        }

        $sql .= "`{$this->columnName}`";
        if(!in_array($this->getParam('command'), ['drop_column', 'rename_table'])) {
            if($this->type == 'integer') {
                $sql .= ' INT(1)';
            } elseif($this->type == 'tinyInteger') {
                $sql .= ' BOOLEAN';
            } elseif($this->type == 'bigInteger') {
                $sql .= ' BIGINT';
            } elseif($this->type == 'float') {
                $sql .= " FLOAT({$this->getParam('total')}, {$this->getParam('places')})";
            } elseif($this->type == 'char') {
                $sql .= " CHAR({$this->getParam('length')})";
            } elseif($this->type == 'string') {
                $sql .= " VARCHAR({$this->getParam('length')})";
            } elseif($this->type == 'tinyText') {
                $sql .= ' TINYTEXT';
            } elseif($this->type == 'text') {
                $sql .= ' TEXT';
            } elseif($this->type == 'mediumText') {
                $sql .= ' MEDIUMTEXT';
            } elseif($this->type == 'longText') {
                $sql .= ' LONGTEXT';
            } elseif($this->type == 'date') {
                $sql .= ' DATE';
            } elseif($this->type == 'time') {
                $sql .= ' TIME';
            } elseif($this->type == 'dateTime') {
                $sql .= ' DATETIME';
            } elseif($this->type == 'timestamp') {
                $sql .= ' TIMESTAMP';
            }
    
            $sql .= $this->getParam('autoIncrement') ? ' AUTO_INCREMENT' : '';
            $sql .= $this->getParam('primaryKey') ? ' PRIMARY KEY' : '';
            $sql .= $this->getParam('nullable') ? ' NULL' : ' NOT NULL';
            $sql .= $this->getParam('default') ? " DEFAULT {$this->getParam('default')}" : '';
        }
        return $sql;
    }

    public function build(): string 
    {
        return $this->toMySQL();
    }
}