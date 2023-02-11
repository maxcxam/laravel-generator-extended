<?php

namespace Maxcxam\Generators\Traits;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Pluralizer;
use Maxcxam\Generators\Exception\FileExistsException;
use Maxcxam\Generators\Exception\TableExistsException;
use Maxcxam\Generators\Lib\PivotSchemaGenerator;

trait WithRelations
{
    public function createManyToOneRelation($field): array
    {
        $result = [];
        $relationModel = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $field['relationModel']));
        $nullable = $field['nullable'] ? 'TRUE' : 'FALSE';
        $relationField = $relationModel . '_id';
        $relationTable = Pluralizer::plural($relationModel);
        $fk = $relationModel.'_fk_'.date('YmdHis');
        $result[] = "\$table->unsignedBigInteger('$relationField')->nullable($nullable);";
        $result[] = "\$table->foreign('$relationField', '$fk')->references('id')->on('$relationTable');";
        return $result;
    }

    /**
     * @throws TableExistsException
     * @throws FileExistsException
     * @throws FileNotFoundException
     */
    public function createManyToManyRelation($field, $model): array
    {
        $pivotGenerator = new PivotSchemaGenerator($model, $field['relationModel']);
        $pivotGenerator->generate();
        return [];
    }

    public function createOneToManyRelation($field): array
    {
        return ['createOneToManyRelation'];
    }

    public function createOneToOneRelation($field): array
    {
        return ['createOneToOneRelation'];
    }
}
