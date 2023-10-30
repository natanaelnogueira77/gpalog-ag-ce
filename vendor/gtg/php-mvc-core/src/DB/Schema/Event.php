<?php 

namespace GTG\MVC\DB\Schema;

use GTG\MVC\DB\Schema\ColumnDefinition;

class Event 
{
    protected string $event;
    protected string $schedule;
    protected string $statement;

    public function __construct(string $event) 
    {
        $this->event = $event;
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

    public function schedule(string $schedule): static 
    {
        $this->schedule = $schedule;
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
            $sql .= "CREATE EVENT `{$this->event}` ON SCHEDULE " . $this->schedule . ' DO ' . $this->statement . ';';
        } elseif($this->action == 'drop') {
            $sql .= "DROP EVENT `{$this->event}`";
        } elseif($this->action == 'drop_if_exists') {
            $sql .= "DROP EVENT IF EXISTS `{$this->event}`";
        }

        return $sql;
    }

    public function build(): string 
    {
        return $this->toMySQL();
    }
}