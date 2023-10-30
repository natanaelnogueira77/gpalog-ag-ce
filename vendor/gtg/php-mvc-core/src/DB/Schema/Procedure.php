<?php 

namespace GTG\MVC\DB\Schema;

use GTG\MVC\DB\Schema\ProcedureParamDefinition;

class Procedure 
{
    protected string $procedure;
    protected string $action;
    protected string $columnAction;
    protected array $columnParams = [];
    protected array $columns = [];
    protected string $statement;

    public function __construct(string $procedure) 
    {
        $this->procedure = $procedure;
    }

    protected function setAction(string $action): static 
    {
        $this->action = $action;
        return $this;
    }

    public function create(): static 
    {
        $this->setAction('create');
        return $this;
    }

    public function drop(): static 
    {
        $this->setAction('drop');
        return $this;
    }

    public function dropIfExists(): static 
    {
        $this->setAction('drop_if_exists');
        return $this;
    }

    public function integer(string $columnName, bool $autoIncrement = false): ProcedureParamDefinition
    {
        return $this->addColumn($columnName, 'integer', compact('autoIncrement'));
    }

    public function tinyInteger(string $columnName, bool $autoIncrement = false): ProcedureParamDefinition
    {
        return $this->addColumn($columnName, 'tinyInteger', compact('autoIncrement'));
    }

    public function bigInteger(string $columnName, bool $autoIncrement = false): ProcedureParamDefinition
    {
        return $this->addColumn($columnName, 'bigInteger', compact('autoIncrement'));
    }

    public function float(string $columnName, int $total = 8, int $places = 2): ProcedureParamDefinition 
    {
        return $this->addColumn($columnName, 'float', compact('total', 'places'));
    }

    public function char(string $columnName, int $length): ProcedureParamDefinition
    {
        return $this->addColumn($columnName, 'char', compact('length'));
    }

    public function string(string $columnName, int $length): ProcedureParamDefinition
    {
        return $this->addColumn($columnName, 'string', compact('length'));
    }

    public function tinyText(string $columnName): ProcedureParamDefinition
    {
        return $this->addColumn($columnName, 'tinyText');
    }

    public function text(string $columnName): ProcedureParamDefinition
    {
        return $this->addColumn($columnName, 'text');
    }

    public function mediumText(string $columnName): ProcedureParamDefinition
    {
        return $this->addColumn($columnName, 'mediumText');
    }

    public function longText(string $columnName): ProcedureParamDefinition
    {
        return $this->addColumn($columnName, 'longText');
    }

    public function date(string $columnName): ProcedureParamDefinition
    {
        return $this->addColumn($columnName, 'date');
    }

    public function time(string $columnName): ProcedureParamDefinition
    {
        return $this->addColumn($columnName, 'time');
    }

    public function dateTime(string $columnName): ProcedureParamDefinition
    {
        return $this->addColumn($columnName, 'dateTime');
    }

    public function timestamp(string $columnName): ProcedureParamDefinition
    {
        return $this->addColumn($columnName, 'timestamp');
    }

    public function statement(string $statement): static 
    {
        $this->statement = $statement;
        return $this;
    }

    private function addColumn(string $columnName, string $type = '', array $params = []): ProcedureParamDefinition 
    {
        $definition = new ProcedureParamDefinition($columnName, $type, $params);
        $this->columns[] = $definition;
        return $definition;
    }

    private function toMySQL(): string 
    {
        $sql = '';
        if($this->action == 'create') {
            $sql .= "CREATE PROCEDURE `{$this->procedure}`(";
            if($this->columns) {
                foreach($this->columns as $column) {
                    $sql .= $column->build() . ',';
                }
                $sql[strlen($sql) - 1] = ')'; 
            } else {
                $sql .= ')'; 
            }

            $sql .= ' BEGIN ' . $this->statement . ' END;';
        } elseif($this->action == 'drop') {
            $sql .= "DROP PROCEDURE `{$this->procedure}`";
        } elseif($this->action == 'drop_if_exists') {
            $sql .= "DROP PROCEDURE IF EXISTS `{$this->procedure}`";
        }

        return $sql;
    }

    public function build(): string 
    {
        return $this->toMySQL();
    }
}