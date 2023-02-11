<?php

namespace Maxcxam\Generators\Lib;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Maxcxam\Generators\Exception\FileExistsException;
use Maxcxam\Generators\Exception\TableExistsException;

class PivotSchemaGenerator extends MigrationGenerator
{
    public function __construct($firstModel, $secondModel = NULL)
    {
        $model = $firstModel.$secondModel;
        $fields = [];
        $fields[] = [
            'field' => 'pivot',
            'type' => 'relation',
            'nullable' => FALSE,
            'isRelation' => TRUE,
            'relationType' => 'ManyToOne',
            'relationModel' => $firstModel
        ];
        $fields[] = [
            'field' => 'pivot',
            'type' => 'relation',
            'nullable' => FALSE,
            'isRelation' => TRUE,
            'relationType' => 'ManyToOne',
            'relationModel' => $secondModel
        ];
        parent::__construct($fields, $model, TRUE);
    }

    /**
     * @throws TableExistsException
     * @throws FileExistsException
     * @throws FileNotFoundException
     */
    public function generate():void
    {
        echo $this->fire();
    }
}
