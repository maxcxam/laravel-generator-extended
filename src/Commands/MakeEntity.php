<?php

namespace Maxcxam\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Maxcxam\Generators\Exception\FileExistsException;
use Maxcxam\Generators\Exception\TableExistsException;
use Maxcxam\Generators\Lib\MigrationGenerator;
use Symfony\Component\Console\Command\Command as CommandAlias;

class MakeEntity extends Command
{
    private array $fields = [];

    private array $types = [
        'string', 'array', 'text', 'translatable (for spatie)', 'relation', 'boolean'
    ];

    private array $relations = [
        'ManyToOne', 'ManyToMany' //TODO 'OneToMany', 'OneToOne'
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:entity {entity?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make entity command';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $entity = $this->argument('entity') ?? NULL;
        if(!$entity) {
            $entity = $this->ask("Model name");
        }
        $this->addField();
        $this->line(json_encode($this->fields));
        try {
            $result = (new MigrationGenerator($this->fields, $entity))->fire();
            $this->line($result);
            if($this->confirm('Migrate new migration?', FALSE)) {
                Artisan::call('migrate');
            }
        } catch (TableExistsException|FileExistsException|FileNotFoundException $e) {
            $this->line($e->getMessage());
            return CommandAlias::FAILURE;
        }

        return CommandAlias::SUCCESS;
    }

    private function addField()
    {
        $field = $this->ask("Add field");
        $type = $this->choice(
            'Type',
            $this->types,
            0,
            1
        );
        $relation = FALSE;
        $model = NULL;
        $relationType = NULL;
        if($type === 'relation') {
            $relationType = $this->choice('Relation type:', $this->relations,0,1);
            $models = array_map( fn(string $path) => basename($path, '.php'),
                (new Filesystem())->files(base_path() . '/app/Models'));
            $model = $this->choice('Related to:', $models,0,1);
            $relation = TRUE;
            $default = NULL;
            $nullable = $this->confirm('Can be null?');
        } elseif ($type !== 'boolean') {
            $nullable = $this->confirm('Can be null?');
            $default = $this->ask('Default');
        } else {

            $default = $this->choice('Default', ['true', 'false'],1,1);
            $default = $default === 'true';
            $nullable = NULL;
        }


        $this->fields[] = [
            'field' => $field,
            'type' => explode(' ', $type)[0],
            'nullable' => $nullable,
            'isRelation' => $relation,
            'relationType' => $relationType,
            'relationModel' => $model,
            'default' => $default
        ];
        if($this->confirm('Add new Field', TRUE)) $this->addField();
    }
}
