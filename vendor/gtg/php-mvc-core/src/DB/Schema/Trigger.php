<?php 

namespace GTG\MVC\DB\Schema;

use GTG\MVC\DB\Schema\ColumnDefinition;

class Trigger 
{
    protected string $event;
    protected string $statement;
    protected string $trigger;

    public function __construct(string $trigger) 
    {
        $this->trigger = $trigger;
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

    public function event(string $event): static 
    {
        $this->event = $event;
        return $this;
    }

    public function statement(string $statement): static 
    {
        $this->statement = $statement;
        return $this;
    }

    private function toMySQL(): string 
    {
        $sql = '';
        if($this->action == 'create') {
            $sql .= "CREATE TRIGGER `{$this->trigger}` " . $this->event . " BEGIN " . $this->statement . " END;";
        } elseif($this->action == 'drop') {
            $sql .= "DROP TRIGGER `{$this->trigger}`";
        } elseif($this->action == 'drop_if_exists') {
            $sql .= "DROP TRIGGER IF EXISTS `{$this->trigger}`";
        }

        return $sql;
    }

    public function build(): string 
    {
        return $this->toMySQL();
    }
}