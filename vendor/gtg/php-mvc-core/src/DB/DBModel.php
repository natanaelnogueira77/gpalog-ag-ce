<?php

namespace GTG\MVC\DB;

use CoffeeCode\DataLayer\DataLayer;
use DateTime;
use GTG\MVC\Application;

abstract class DBModel extends DataLayer 
{
    public const RULE_RAW = 'raw';
    public const RULE_DATETIME = 'date';
    public const RULE_EMAIL = 'email';
    public const RULE_IN = 'in';
    public const RULE_INT = 'int';
    public const RULE_MATCH = 'match';
    public const RULE_MAX = 'max';
    public const RULE_MIN = 'min';
    public const RULE_EQUAL_TO = 'equal_to';
    public const RULE_SMALLER_THAN = 'smaller_than';
    public const RULE_LARGER_THAN = 'larger_than';
    public const RULE_REQUIRED = 'required';

    public array $errors = [];
    
    protected $values = [];
    protected ?string $joins = null;

    public function __construct() 
    {
        parent::__construct(static::tableName(), [], static::primaryKey(), false, Application::$DB_INFO);
    }

    public function __get($key) 
    {
        parent::__get($key);
        return isset($this->values[$key]) ? $this->values[$key] : null;
    }

    public function __set($key, $value) 
    {
        parent::__set($key, $value);
        $this->values[$key] = $value;
    }

    abstract public static function tableName(): string;

    abstract public static function attributes(): array;

    abstract public static function primaryKey(): string;

    abstract public function rules(): array;

    public function getData(): array 
    {
        $this->values[static::primaryKey()] = $this->data->{static::primaryKey()};
        foreach(static::attributes() as $attr) {
            $this->values[$attr] = $this->data->$attr;
        }
        return $this->values;
    }

    public function loadData(array $data): static 
    {
        foreach($data as $attr => $value) {
            if(in_array($attr, static::attributes())) {
                $this->{$attr} = $value;
                $this->values[$attr] = $value;
            }
        }
        return $this;
    }

    public static function getPropertyValues(array $objects, string $property = 'id'): array 
    {
        return array_map(
            function ($o) use ($property) { return $o->$property; }, 
            array_filter($objects, function ($e) use ($property) { return !is_null($e->$property); })
        );
    }

    public static function getGroupedBy(array $objects, string $attribute = 'id', bool $multiple = false): ?array 
    {
        $groups = [];
        if($multiple) {
            foreach($objects as $object) {
                $groups[$object->$attribute][] = $object;
            }
        } else {
            foreach($objects as $object) {
                $groups[$object->$attribute] = $object;
            }
        }
        return $groups ? $groups : null;
    }

    public function get(array $filters = [], string $columns = '*'): DataLayer 
    {
        $finders = static::getFilters($filters);
        return $this->find($finders[0], $finders[1], $columns);
    }

    public function getByIds(array $ids, string $columns = '*'): DataLayer 
    {
        return $this->find(static::primaryKey() . ' IN (' . implode(',', $ids) . ')', null, $columns);
    }
    
    public function join(string $entity, array $filters = []): static 
    {
        $sql = "JOIN {$entity} ON ";
        if(count($filters) > 0) {
            foreach($filters as $column => $value) {
                if($column == 'raw') {
                    $sql .= "{$value} AND ";
                }
            }
            $sql = substr($sql, 0, -4);
        }

        $this->joins .= $sql;
        return $this;
    }

    public function innerJoin(string $entity, array $filters = []): static 
    {
        $sql = "INNER JOIN {$entity} ON ";
        if(count($filters) > 0) {
            foreach($filters as $column => $value) {
                if($column == 'raw') {
                    $sql .= "{$value} AND ";
                }
            }
            $sql = substr($sql, 0, -4);
        }

        $this->joins .= $sql;
        return $this;
    }

    public function leftJoin(string $entity, array $filters = []): static 
    {
        $sql = "LEFT JOIN {$entity} ON ";
        if(count($filters) > 0) {
            foreach($filters as $column => $value) {
                if($column == 'raw') {
                    $sql .= "{$value} AND ";
                }
            }
            $sql = substr($sql, 0, -4);
        }

        $this->joins .= $sql;
        return $this;
    }

    public function rightJoin(string $entity, array $filters = []): static 
    {
        $sql = "RIGHT JOIN {$entity} ON ";
        if(count($filters) > 0) {
            foreach($filters as $column => $value) {
                if($column == 'raw') {
                    $sql .= "{$value} AND ";
                }
            }
            $sql = substr($sql, 0, -4);
        }

        $this->joins .= $sql;
        return $this;
    }

    public function find(?string $terms = null, ?string $params = null, string $columns = '*'): DataLayer
    {
        if(!$this->joins) {
            return parent::find($terms, $params, $columns);
        }

        if ($terms) {
            $this->statement = "SELECT {$columns} FROM " . static::tableName() . " {$this->joins} WHERE {$terms}";
            parse_str($params ? $params : '', $this->params);
            return $this;
        }

        $this->statement = "SELECT {$columns} FROM " . static::tableName() . " {$this->joins}";
        return $this;
    }

    public function statement(string $sql): static 
    {
        $this->statement = $sql;
        return $this;
    }

    public function paginate(int $limit = 10, int $page = 1, array $sorting = []): static
    {
        $this->limit($limit)->offset(($page - 1) * $limit);
        return $this;
    }

    public function sort(array $sorting = []): static 
    {
        $this->order(static::getSorting($sorting));
        return $this;
    }

    public function fetch(bool $all = false): array|static|null
    {
        if($result = parent::fetch($all)) {
            if($all) {
                foreach($result as $object) {
                    $object->decode();
                }
            } else {
                $result->decode();
            }
        }

        return $result;
    }

    public function encode(): static 
    {
        return $this;
    }
    
    public function decode(): static 
    {
        return $this;
    }

    public static function metaTableData(): ?array 
    {
        return null;
    }

    public function save(): bool 
    {
        if(!$this->validate()) {
            return false;
        }

        $this->encode();
        
        foreach(static::attributes() as $attr) {
            if(!is_null($this->$attr)) {
                $this->$attr = html_entity_decode($this->$attr);
            } else {
                unset($this->$attr);
            }
        }

        $result = parent::save();
        if($this->fail()) {
            return false;
        }

        $this->decode();
        $this->{static::primaryKey()} = $this->data->{static::primaryKey()};
        return $result;
    }

    public static function insertMany(array $objects): array|false 
    {
        if(count($objects) === 0) {
            return false;
        }

        $vars = [];
        $sql = 'INSERT INTO ' . static::tableName() . ' (' . implode(',', static::attributes()) . ') VALUES ';

        $hasErrors = false;
        foreach($objects as $index => $object) {
            if(is_array($objects[$index])) {
                $objects[$index] = (new static())->loadData($objects[$index]);
            }

            if($objects[$index]->validate()) {
                $objects[$index]->encode();
                $sql .= '(';
                foreach(static::attributes() as $attr) {
                    $vars[] = $objects[$index]->$attr;
                    $sql .= '?,';
                }
                $objects[$index]->decode();

                $sql[strlen($sql) - 1] = ' ';
                $sql .= '),';
            } else {
                $hasErrors = true;
            }
        }

        if($hasErrors) {
            return $objects;
        }

        $sql[strlen($sql) - 1] = ' ';
        $stmt = Application::$app->db->prepare($sql);
        if(!$stmt->execute($vars)) {
            return false;
        }

        return $objects;
    }

    public static function saveMany(array $objects): array|false 
    {
        if(count($objects) === 0) {
            return false;
        }

        $vars = [];
        $sql = 'INSERT INTO ' . static::tableName() . ' (' . static::primaryKey() . ',' 
            . implode(',', static::attributes()) . ') VALUES ';

        $hasErrors = false;
        foreach($objects as $index => $object) {
            if(is_array($objects[$index])) {
                $objects[$index] = (new static())->loadData($objects[$index]);
            }

            if($objects[$index]->validate()) {
                $objects[$index]->encode();
                $sql .= '(' . static::getFormatedValue($objects[$index]->{static::primaryKey()} ?? null) . ',';
                foreach(static::attributes() as $attr) {
                    $vars[] = $objects[$index]->$attr;
                    $sql .= '?,';
                }
                $objects[$index]->decode();

                $sql[strlen($sql) - 1] = ' ';
                $sql .= '),';
            } else {
                $hasErrors = true;
            }
        }

        if($hasErrors) {
            return $objects;
        }

        $sql[strlen($sql) - 1] = ' ';
        $sql .= ' ON DUPLICATE KEY UPDATE ';
        $sql .= static::primaryKey() . ' = VALUES (' . static::primaryKey() . '),';    

        foreach(static::attributes() as $attr) {
            $sql .= " ${attr} = VALUES (${attr}),";
        }

        $sql[strlen($sql) - 1] = ' ';
        $stmt = Application::$app->db->prepare($sql);
        if(!$stmt->execute($vars)) {
            return false;
        }

        return $objects;
    }

    public static function deleteByIds(array $ids): bool 
    {
        if(count($ids) === 0) {
            return false;
        }

        $sql = 'DELETE FROM ' . static::tableName() . ' WHERE ' . static::primaryKey() . ' IN (';

        foreach($ids as $id) {
            $sql .= static::getFormatedValue($id) . ',';
        }
        
        $sql[strlen($sql) - 1] = ')';

        $stmt = Application::$app->db->prepare($sql);
        return $stmt->execute($vars);
    }

    public static function deleteByObjects(array $objects): bool 
    {
        if(count($objects) === 0) {
            return false;
        }

        $sql = 'DELETE FROM ' . static::tableName() . ' WHERE ' . static::primaryKey() . ' IN (';
        
        $ids = array_map(fn($o) => $o->{static::primaryKey()}, array_filter($objects, fn($o) => $o instanceof static));
        foreach($ids as $id) {
            $sql .= static::getFormatedValue($id) . ',';
        }
        
        $sql[strlen($sql) - 1] = ')';

        $stmt = Application::$app->db->prepare($sql);
        return $stmt->execute($vars);
    }

    public static function deleteAll(): bool 
    {
        return Application::$app->db->exec('DELETE FROM ' . static::tableName());
    }

    private static function getSearch(string $terms = '', array $attributes = []): string 
    {
        $query = '';
        if($terms && $attributes) {
            $words = explode(' ', $terms);
            $conds = array();
            $searches = array();
            $numCols = count($attributes);
    
            foreach($words as $word) {
                $col = 1;
                foreach($attributes as $attr) {
                    $open = $col == 1 ? ' ( ' : '';
                    $close = $col == $numCols ? ' ) ' : '';
                    $conds[] = "{$open} {$attr} LIKE '%" . $word . "%' {$close}";
                    $col++;
                }
                $searches[] = implode(' OR ', $conds);
                $conds = [];
                $col = 1;
            }
    
            $query .= implode(' AND ', $searches);
        }
        return $query;
    }

    private static function getSorting(array $sorting = []): string 
    {
        $sql = '';
        if(count($sorting) > 0) {
            foreach($sorting as $column => $value) {
                if($column == 'raw') {
                    $sql .= "{$value}";
                } else {
                    $sql .= "{$column} {$value},";
                }
            }

            if($sql) $sql[strlen($sql) - 1] = ' ';
        }
        return $sql;
    }

    private static function getFilters(array $filters = []): array 
    {
        $terms = '';
        $params = '';
        $count = 0;

        if(count($filters) > 0) {
            foreach($filters as $column => $value) {
                $count++;
                if($column == 'raw') {
                    $terms .= "{$value} AND ";
                } elseif($column == 'search') {
                    if($value['term'] && $value['columns']) {
                        $terms .= static::getSearch($value['term'], $value['columns']) . ' AND ';
                    }
                } elseif(in_array($column, ['>=', '<=', '<', '>', '!=', '<>'])) {
                    if($value) {
                        foreach($value as $col => $val) {
                            $terms .= "{$col} {$column} " . static::getFormatedValue($val) . ' AND ';
                        }
                    }
                } elseif($column == 'is_null' || $column == 'is_not_null') {
                    if($value) {
                        foreach($value as $col) {
                            if($column == 'is_null') {
                                $terms .= "{$col} IS NULL AND ";
                            } elseif($column == 'is_not_null') {
                                $terms .= "{$col} IS NOT NULL AND ";
                            }
                        }
                    }
                } elseif($column == 'between') {
                    if($value) {
                        foreach($value as $col => $val) {
                            if(is_array($val)) {
                                $terms .= "{$col} BETWEEN " . static::getFormatedValue($val[0]) 
                                    . " AND " . static::getFormatedValue($val[1]) . " AND ";
                            }
                        }
                    }
                } elseif($column == 'in') {
                    if($value) {
                        foreach($value as $col => $values) {
                            $in = implode(',', array_map(function ($e) { return static::getFormatedValue($e); }, $values));
                            $terms .= "{$col} IN ({$in}) AND ";
                        }
                    }
                } else {
                    $terms .= "{$column} = :param{$count} AND ";
                    $params .= "param{$count}={$value}&";
                }
            }

            if($terms) $terms = substr($terms, 0, -4);
            if($params) $params = substr($params, 0, -1);
        }

        return [$terms, $params];
    }

    protected function hasOne(string $model, string $foreign, string $key = 'id', string $columns = '*'): DataLayer 
    {
        return (new $model())->get([$foreign => $this->$key], $columns);
    }

    protected function hasMany(string $model, string $foreign, string $key = 'id', array $filters = [], string $columns = '*'): DataLayer
    {
        return (new $model())->get([$foreign => $this->$key] + $filters, $columns);
    }

    protected function belongsTo(string $model, string $foreign, string $key = 'id', string $columns = '*'): DataLayer 
    {
        return (new $model())->get([$key => $this->$foreign], $columns);
    }

    protected function belongsToMany(
        string $model, 
        string $pivotModel, 
        string $foreign1, 
        string $foreign2, 
        string $pivotProperty, 
        string $key1 = 'id',
        string $key2 = 'id',
        array $filters = [],
        string $columns = '*', 
        string $pivotColumns = '*'
    ): ?array
    {
        if($pivots = (new $pivotModel())->get([$foreign1 => $this->$key1], $pivotColumns)->fetch(true)) {
            $pivots = $pivotModel::getGroupedBy($pivots, $foreign2);
            $ids = $pivotModel::getPropertyValues($pivots, $foreign2);
            $objects = (new $model())->get(['in' => [$key2 => $ids]] + $filters, $columns)->fetch(true);
        }

        return $objects 
            ? array_map(function ($o) use ($pivots, $pivotProperty, $key2) { $o->$pivotProperty = $pivots[$o->$key2]; return $o; }, $objects) 
            : null;
    }

    protected static function withHasOne(
        array $objects, 
        string $model, 
        string $foreign, 
        string $property, 
        string $key = 'id', 
        array $filters = [], 
        string $columns = '*'
    ): array 
    {
        $ids = static::getPropertyValues($objects, $key);
        if($registries = (new $model())->get(['in' => [$foreign => $ids]] + $filters, $columns)->fetch(true)) {
            $registries = $model::getGroupedBy($registries, $foreign);
            foreach($objects as $index => $object) {
                $objects[$index]->$property = $registries[$object->$key];
            }
        }

        return $objects;
    }

    protected static function withHasMany(
        array $objects, 
        string $model, 
        string $foreign, 
        string $property, 
        string $key = 'id', 
        array $filters = [], 
        string $columns = '*'
    ): array 
    {
        $ids = static::getPropertyValues($objects, $key);
        if($registries = (new $model())->get(['in' => [$foreign => $ids]] + $filters, $columns)->fetch(true)) {
            $registries = $model::getGroupedBy($registries, $foreign, true);
            foreach($objects as $index => $object) {
                $objects[$index]->$property = $registries[$object->$key];
            }
        }

        return $objects;
    }

    protected static function withBelongsTo(
        array $objects, 
        string $model, 
        string $foreign, 
        string $property, 
        string $key = 'id', 
        array $filters = [], 
        string $columns = '*'
    ): array 
    {
        $ids = static::getPropertyValues($objects, $foreign);
        if($registries = (new $model())->get(['in' => [$key => $ids]] + $filters, $columns)->fetch(true)) {
            $registries = $model::getGroupedBy($registries);
            foreach($objects as $index => $object) {
                $objects[$index]->$property = $registries[$object->$foreign];
            }
        }

        return $objects;
    }

    protected static function withBelongsToMany(
        array $objects, 
        string $model, 
        string $pivotModel, 
        string $foreign1, 
        string $foreign2, 
        string $property, 
        string $pivotProperty, 
        string $key1 = 'id',
        string $key2 = 'id',
        array $filters = [],
        string $columns = '*', 
        string $pivotColumns = '*'
    ): array 
    {
        if($pivots = (new $pivotModel())->get(['in' => [$foreign1 => static::getPropertyValues($objects, $key1)]], $pivotColumns)->fetch(true)) {
            $groupedPivots = $pivotModel::getGroupedBy($pivots, $foreign1, true);

            $registries = (new $model())->get([
                'in' => [$key2 => $pivotModel::getPropertyValues($pivots, $foreign2)]
            ] + $filters, $columns)->fetch(true);
            $registries = $model::getGroupedBy($registries);

            $groupedRegistries = [];
            foreach($groupedPivots as $groupedPivot) {
                foreach($groupedPivot as $pivot) {
                    if(isset($registries[$pivot->$foreign2])) {
                        $registries[$pivot->$foreign2]->$pivotProperty = $pivot;
                        $groupedRegistries[$pivot->$foreign1][] = $registries[$pivot->$foreign2];
                    }
                }
            }

            foreach($objects as $index => $object) {
                $objects[$index]->$property = $groupedRegistries[$object->$key2];
            }
        }

        return $objects;
    }

    public function getMeta(string $meta): mixed
    {
        $metaInfo = static::metaTableData();
        if(!$metaInfo || !$metaInfo['class'] || !$metaInfo['meta'] || !$metaInfo['value'] || !$meta) {
            return null;
        }

        $filters = [];
        if($metaInfo['entity']) {
            $filters[$metaInfo['entity']] = $this->{static::primaryKey()};
        }
        $filters[$metaInfo['meta']] = $meta;

        $object = (new $metaInfo['class']())->get($filters)->fetch(false);
        return $object ? $object->{$metaInfo['value']} : null;
    }

    public function getGroupedMetas(array $metas): ?array
    {
        $metaInfo = static::metaTableData();
        if(!$metaInfo || !$metaInfo['class'] || !$metaInfo['meta'] || !$metaInfo['value'] || !$metas) {
            return null;
        }

        $filters = [];
        if($metaInfo['entity']) {
            $filters[$metaInfo['entity']] = $this->{static::primaryKey()};
        }
        $filters['in'] = [$metaInfo['meta'] => $metas];
        
        if($objects = (new $metaInfo['class']())->get($filters)->fetch(true)) {
            $metas = [];
            foreach($objects as $object) {
                $metas[$object->{$metaInfo['meta']}] = $object->{$metaInfo['value']};
            }
            return $metas;
        }

        return null;
    }

    public function saveMeta(string $meta, mixed $value): bool 
    {
        $metaInfo = static::metaTableData();
        if(!$metaInfo || !$metaInfo['class'] || !$metaInfo['meta'] || !$metaInfo['value'] || !$meta) {
            return false;
        }

        $filters = [];
        if($metaInfo['entity']) {
            $filters[$metaInfo['entity']] = $this->{static::primaryKey()};
        }
        $filters[$metaInfo['meta']] = $meta;

        if(!$object = (new $metaInfo['class']())->get($filters)->fetch(false)) {
            $object = (new $metaInfo['class']());
            if($metaInfo['entity']) {
                $object->{$metaInfo['entity']} = $this->{static::primaryKey()};
            }
            $object->{$metaInfo['meta']} = $meta;
        }

        $object->{$metaInfo['value']} = $value;
        return $object->save();
    }

    public function saveManyMetas(array $data): array|false 
    {
        $metaInfo = static::metaTableData();
        if(!$metaInfo || !$metaInfo['class'] || !$metaInfo['meta'] || !$metaInfo['value'] || !$data) {
            return false;
        }

        $filters = [];
        if($metaInfo['entity']) {
            $filters[$metaInfo['entity']] = $this->{static::primaryKey()};
        }
        $filters['in'] = [$metaInfo['meta'] => array_keys($data)];

        if($objects = (new $metaInfo['class']())->get($filters)->fetch(true)) {
            $objects = $metaInfo['class']::getGroupedBy($objects, $metaInfo['meta']);
        }

        foreach($data as $meta => $value) {
            if(isset($objects[$meta])) {
                $objects[$meta]->{$metaInfo['value']} = $value;
            } else {
                $objects[$meta] = (new $metaInfo['class']())->loadData([
                    $metaInfo['entity'] => $this->{static::primaryKey()},
                    $metaInfo['meta'] => $meta,
                    $metaInfo['value'] => $value
                ]);
            }
        }

        return $metaInfo['class']::saveMany($objects);
    }

    public function validate(): bool
    {
        foreach($this->rules() as $attribute => $rules) {
            if($attribute === self::RULE_RAW) {
                foreach($rules as $function) {
                    if(is_callable($function)) {
                        $function($this);
                    }
                }
            } else {
                $value = $this->{$attribute};
                foreach($rules as $rule) {
                    $ruleName = $rule;
                    if(!is_string($ruleName)) {
                        $ruleName = $rule[0];
                    }

                    if($ruleName === self::RULE_REQUIRED && !$value) {
                        $this->addErrorForRule($attribute, self::RULE_REQUIRED, $rule);
                    }

                    if($ruleName === self::RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->addErrorForRule($attribute, self::RULE_EMAIL, $rule);
                    }

                    if($ruleName === self::RULE_INT && !filter_var($value, FILTER_VALIDATE_INT)) {
                        $this->addErrorForRule($attribute, self::RULE_INT, $rule);
                    }
                    
                    if($ruleName === self::RULE_MIN && $value && strlen($value) < $rule['min']) {
                        $this->addErrorForRule($attribute, self::RULE_MIN, $rule);
                    }

                    if($ruleName === self::RULE_MAX && $value && strlen($value) > $rule['max']) {
                        $this->addErrorForRule($attribute, self::RULE_MAX, $rule);
                    }

                    if($ruleName === self::RULE_MATCH && $value !== $this->{$rule['match']}) {
                        $this->addErrorForRule($attribute, self::RULE_MATCH, $rule);
                    }

                    if($ruleName === self::RULE_EQUAL_TO && $value != $rule['value']) {
                        $this->addErrorForRule($attribute, self::RULE_EQUAL_TO, $rule);
                    }

                    if($ruleName === self::RULE_SMALLER_THAN && $value >= $rule['value']) {
                        $this->addErrorForRule($attribute, self::RULE_SMALLER_THAN, $rule);
                    }

                    if($ruleName === self::RULE_LARGER_THAN && $value <= $rule['value']) {
                        $this->addErrorForRule($attribute, self::RULE_LARGER_THAN, $rule);
                    }

                    if($ruleName === self::RULE_IN && $value && !in_array($value, $rule['values'])) {
                        $this->addErrorForRule($attribute, self::RULE_IN, $rule);
                    }

                    if($ruleName === self::RULE_DATETIME && $value && !DateTime::createFromFormat($rule['pattern'], $value)) {
                        $this->addErrorForRule($attribute, self::RULE_DATETIME, $rule);
                    }
                }
            }
        }

        return empty($this->errors);
    }

    private function addErrorForRule(string $attribute, string $rule, array $params = []): void
    {
        $message = $params['message'] ?? '';
        foreach($params as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }
        $this->errors[$attribute][] = $message;
    }

    public function addError(string $attribute, string $message): static 
    {
        $this->errors[$attribute][] = $message;
        return $this;
    }

    public function hasError(string $attribute): array|false
    {
        return $this->errors[$attribute] ?? false;
    }

    public function hasErrors(): bool 
    {
        return $this->errors ? true : false;
    }

    public function getFirstError(string $attribute): string|false
    {
        return $this->errors[$attribute][0] ?? false;
    }

    public function getFirstErrors(): array
    {
        $errors = [];
        foreach($this->errors as $attr => $messages) {
            $errors[$attr] = $messages[0];
        }
        return $errors;
    }

    public static function getErrorsFromMany(array $objects, bool $isMeta = false): ?array 
    {
        $errors = null;
        if($isMeta) {
            foreach($objects as $object) {
                if($object->hasErrors()) {
                    foreach($object->getFirstErrors() as $attr => $message) {
                        $errors[$attr] = $message;
                    }
                }
            }
        } else {
            foreach($objects as $index => $object) {
                if($object->hasErrors()) {
                    foreach($object->getFirstErrors() as $attr => $message) {
                        $errors["{$attr}_{$index}"] = $message;
                    }
                }
            }
        }

        return $errors;
    }

    private static function getFormatedValue(mixed $value): mixed 
    {
        if(is_null($value)) {
            return 'null';
        } elseif(gettype($value) === 'string') {
            $value = html_entity_decode($value);
            return "'${value}'";
        } else {
            return $value;
        }
    }
}