<?php

namespace Maxcxam\Generators\Lib;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Composer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Pluralizer;
use Maxcxam\Generators\Exception\FileExistsException;
use Maxcxam\Generators\Exception\TableExistsException;
use Illuminate\Filesystem\Filesystem;

class MigrationGenerator
{
    private Filesystem $fs;

    private Composer $composer;

    public function __construct(
        private array $fields, private string $model,
        private bool $isPivot = FALSE,
        private ?string $table = NULL, private ?string $fileName = NULL)
    {
        $this->fs = new Filesystem();
        $this->composer = new Composer($this->fs);
    }

    /**
     * @throws TableExistsException
     * @throws FileExistsException
     * @throws FileNotFoundException
     */
    public function fire(): string
    {
        $this->table = $this->createTableName();
        $this->fileName = $this->createFileName();
        if($this->tableExists()) {
            throw new TableExistsException(
                message: sprintf("Table %s for model %s you tying to generate already exists",
                    $this->table, $this->model)
            );
        }

        if($this->fileExists()) {
            throw new FileExistsException(
                message: sprintf("File %s for model migration %s you tying to generate already exists",
                    $this->fileName, $this->model)
            );
        }
        $stub = $this->fs->get(__DIR__ . '/../stubs/migration.stub');
        $this->replaceSchema($stub);

        $path = base_path().'/database/migrations/'.$this->fileName;
        $this->fs->put($path, $stub);

        $this->composer->dumpAutoloads();
        return sprintf('Migration created successfully. \r\n [%s]', $path);
    }

    private function fileExists(): bool
    {
        return $this->fs->exists(base_path().'/database/migrations/'.$this->fileName);
    }



    private function tableExists(): bool
    {
        return Schema::hasTable($this->table);
    }

    private function createTableName(): string
    {
        $singular = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $this->model));
        return $this->isPivot ? $singular : Pluralizer::plural($singular);
    }

    private function createFileName(): string
    {
        return date('Y_m_d_His').'_'.$this->table.'_create_'.($this->isPivot ? 'pivot_' : '').'table.php';
    }

    private function replaceClassName(&$stub): static
    {
        $className = $this->model.'Migration'.date('Y_m_d');

        $stub = str_replace('{{class}}', $className, $stub);

        return $this;
    }

    private function replaceSchema(&$stub): void
    {
        $schema = (new SchemaGenerator($this->table, $this->fields, $this->model))->create();
        $stub = str_replace(['{{schema_up}}', '{{schema_down}}'], $schema, $stub);
    }

}
