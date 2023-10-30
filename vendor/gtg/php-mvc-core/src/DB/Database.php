<?php 

namespace GTG\MVC\DB;

use CoffeeCode\DataLayer\Connect;
use GTG\MVC\Application;
use GTG\MVC\DB\Schema\Event;
use GTG\MVC\DB\Schema\Procedure;
use GTG\MVC\DB\Schema\Table;
use GTG\MVC\DB\Schema\Trigger;
use PDO;
use PDOStatement;

class Database 
{
    private array $dbInfo = [];
    private array $migrations;
    private array $seeders;

    public function __construct(array $config) 
    {
        $this->dbInfo = $config['pdo'];
        $this->migrations = $config['migrations'];
        $this->seeders = $config['seeders'];
    }

    public function exec(string $sql): int 
    {
        $pdo = Connect::getInstance($this->dbInfo);
        return $pdo->exec($sql);
    }

    public function applyMigrations(?int $number = null): void 
    {
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();

        $newMigrations = [];
        $files = array_map(
            fn($m) => pathinfo($m, PATHINFO_FILENAME), 
            array_filter(scandir(Application::$ROOT_DIR . '/' . $this->migrations['path']), fn($m) => $m !== '.' && $m !== '..')
        );
        $toApplyMigrations = array_diff($files, array_map(fn ($o) => $o->migration, $appliedMigrations));
        foreach($toApplyMigrations as $migration) {
            $className = $this->migrations['namespace'] . "\\{$migration}";
            $instance = new $className();
            $this->log("Applying migration {$migration}...");
            $instance->up();
            $this->log("Migration {$migration} applied!");
            $newMigrations[] = $migration;
            if($number && count($newMigrations) >= $number) {
                break;
            }
        }

        if(!empty($newMigrations)) {
            $this->saveMigrations($newMigrations);
        }
        $this->log('All migrations were applied!');
    }

    public function reverseMigrations(?int $number = null): void 
    {
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();

        $files = array_map(
            fn($m) => pathinfo($m, PATHINFO_FILENAME), 
            array_filter(scandir(Application::$ROOT_DIR . '/' . $this->migrations['path']), fn($m) => $m !== '.' && $m !== '..')
        );
        $toReverseMigrations = array_reverse($appliedMigrations);
        foreach($toReverseMigrations as $migration) {
            $className = $this->migrations['namespace'] . "\\{$migration->migration}";
            $instance = new $className();
            $this->log("Reversing migration {$migration->migration}...");
            $instance->down();
            $this->log("Migration {$migration->migration} reversed!");
            $newMigrationIds[] = $migration->id;
            if($number && count($newMigrationIds) >= $number) {
                break;
            }
        }

        if(!empty($newMigrationIds)) {
            if($number) {
                $this->deleteMigrations($newMigrationIds);
            } else {
                $this->deleteMigrations();
            }
        }
        $this->log('All migrations were reversed!');
    }

    public function createMigrationsTable(): void 
    {
        $this->exec('
            CREATE TABLE IF NOT EXISTS migrations (
                id INT(1) AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=INNODB;
        ');
    }

    public function getAppliedMigrations(): array 
    {
        $statement = $this->prepare("SELECT id, migration FROM migrations");
        $statement->execute();
        return $statement->fetchAll();
    }

    public function saveMigrations(array $migrations): void 
    {
        $str = implode(',', array_map(fn($m) => "('{$m}')", $migrations));
        $statement = $this->prepare("INSERT INTO migrations (migration) VALUES {$str}");
        $statement->execute();
    }

    public function deleteMigrations(?array $migrationIds = null): void 
    {
        if($migrationIds) {
            $statement = $this->prepare("DELETE FROM migrations WHERE id IN (" . implode(',', $migrationIds) . ")");
        } else {
            $statement = $this->prepare("DELETE FROM migrations WHERE id >= 1");
        }
        $statement->execute();
    }

    public function applySeeders(): void 
    {
        $files = array_map(
            fn($m) => pathinfo($m, PATHINFO_FILENAME), 
            array_filter(scandir(Application::$ROOT_DIR . '/' . $this->seeders['path']), fn($m) => $m !== '.' && $m !== '..')
        );
        foreach($files as $seeder) {
            $className = $this->seeders['namespace'] . "\\{$seeder}";
            $instance = new $className();
            $this->log("Applying seeder {$seeder}...");
            $instance->run();
            $this->log("Seeder {$seeder} applied!");
        }

        $this->log('All seeders were applied!');
    }

    public function createTable(string $tableName, callable $callback): int 
    {
        $table = new Table($tableName);
        $table->create();
        $callback($table);
        return $this->exec($table->build());
    }

    public function alterTable(string $tableName, callable $callback): int 
    {
        $table = new Table($tableName);
        $table->alter();
        $callback($table);
        return $this->exec($table->build());
    }

    public function dropTable(string $tableName): int 
    {
        $table = new Table($tableName);
        $table->drop();
        return $this->exec($table->build());
    }

    public function dropTableIfExists(string $tableName): int 
    {
        $table = new Table($tableName);
        $table->dropIfExists();
        return $this->exec($table->build());
    }

    public function createProcedure(string $procedureName, callable $callback): int 
    {
        $procedure = new Procedure($procedureName);
        $procedure->create();
        $callback($procedure);
        return $this->exec($procedure->build());
    }

    public function dropProcedure(string $procedureName): int 
    {
        $procedure = new Procedure($procedureName);
        $procedure->drop();
        return $this->exec($procedure->build());
    }

    public function dropProcedureIfExists(string $procedureName): int 
    {
        $procedure = new Procedure($procedureName);
        $procedure->dropIfExists();
        return $this->exec($procedure->build());
    }

    public function createEvent(string $eventName, callable $callback): int 
    {
        $event = new Event($eventName);
        $event->create();
        $callback($event);
        return $this->exec($event->build());
    }

    public function dropEvent(string $eventName): int 
    {
        $event = new Event($eventName);
        $event->drop();
        return $this->exec($event->build());
    }

    public function dropEventIfExists(string $eventName): int 
    {
        $event = new Event($eventName);
        $event->dropIfExists();
        return $this->exec($event->build());
    }

    public function createTrigger(string $triggerName, callable $callback): int 
    {
        $trigger = new Trigger($triggerName);
        $trigger->create();
        $callback($trigger);
        return $this->exec($trigger->build());
    }

    public function dropTrigger(string $triggerName): int 
    {
        $trigger = new Trigger($triggerName);
        $trigger->drop();
        return $this->exec($trigger->build());
    }

    public function dropTriggerIfExists(string $triggerName): int 
    {
        $trigger = new Trigger($triggerName);
        $trigger->dropIfExists();
        return $this->exec($trigger->build());
    }

    public function getConnection(): ?PDO 
    {
        return Connect::getInstance($this->dbInfo);
    }

    public function prepare(string $sql): PDOStatement|false
    {
        $pdo = Connect::getInstance($this->dbInfo);
        return $pdo->prepare($sql);
    }

    protected function log(string $message): void 
    {
        echo '[' . date('Y-m-d H:i:s') . '] - ' . $message . PHP_EOL;
    }
}