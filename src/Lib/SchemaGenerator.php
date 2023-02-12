<?php

namespace Maxcxam\Generators\Lib;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Maxcxam\Generators\Exception\FileExistsException;
use Maxcxam\Generators\Exception\TableExistsException;
use Maxcxam\Generators\Traits\WithRelations;

class SchemaGenerator
{
    use WithRelations;

    public function __construct(private string $table, private array $fields, private string $model)
    {
    }

    public function create(): array
    {
        $up = $this->createSchemaForUpMethod();
        $down = $this->createSchemaForDownMethod();

        return compact('up', 'down');
    }

    /**
     * @return string
     */
    private function createSchemaForUpMethod(): string
    {
        $fields = implode(PHP_EOL."\t\t\t", $this->generateFields());
        return
<<<SCHEMA
Schema::create('$this->table', function (Blueprint \$table) {
\t        $fields
\t\t});
SCHEMA;

    }

    private function createSchemaForDownMethod(): string
    {
        return sprintf("Schema::dropIfExists('%s');", $this->table);
    }

    /**
     * @throws TableExistsException
     * @throws FileExistsException
     * @throws FileNotFoundException
     */
    private function generateFields():array
    {
        $result = [];
        $result[] = '$table->bigIncrements("id");';
        foreach ($this->fields as $field) {
            if ($field['isRelation']) {
                $relations = match ($field['relationType']) {
                    'ManyToOne' => $this->createManyToOneRelation($field),
                    'ManyToMany' => $this->createManyToManyRelation($field, $this->model),
                    'OneToMany' => $this->createOneToManyRelation($field),
                    default => $this->createOneToOneRelation($field),
                };
                $result = array_merge($result, $relations);
            } else {
                $type = match ($field['type']) {
                    default => 'string',
                    'text' => 'longText',
                    'array', 'translatable' => 'json',
                    'boolean' => 'boolean'
                };
                if($type === 'boolean') {
                    $result[] = '$table->boolean("' . $field['field'] . '")->default(1);';
                } else {
                    $result[] = '$table->' . $type . '("' . $field['field'] . '")->nullable(' . ($field['nullable'] ? 'TRUE' : 'FALSE') . ');';
                }
                //$result[] = '$table->' . $type . '("' . $field['field'] . '")->nullable(' . ($field['nullable'] ? 'TRUE' : 'FALSE') . ');';
            }
        }
        return $result;
    }
}
