<?php

namespace dobron\LaravelDatabaseEnum\Console\Commands;

use dobron\LaravelDatabaseEnum\EnumDefinition;
use dobron\LaravelDatabaseEnum\StubAssembler;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The Artisan command to generate Enum classes.
 *
 */
class EnumMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:enum {name : The name of the class}
                                      {--m|model= : Eloquent model class name}
                                      {--t|table= : (or) The database table name}
                                      {--p|path= : The path to generate enums in}
                                      {--id=id : ID column name}
                                      {--slug=slug : Slug column name}
                                      {--value= : Column(s) name for map separated by coma}
                                      {--f|force : Create the class even if the enum already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new enum class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Enum';

    /**
     * The Composer instance.
     *
     * @var Inflector
     */
    protected $inflector;

    /**
     * Create a new command instance.
     *
     * @param  Filesystem $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct($files);

        $this->inflector = InflectorFactory::create()->build();
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../../../stubs/enum.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        if ($path = $this->option('path')) {
            // Ensure the path starts with "app/"
            $path = Str::start(ltrim($path, '/'), 'app/');
            // Remove "app/" from the beginning of the path
            $path = preg_replace('#^app\/#', '', $path);
            // Convert the path into namespace
            $namespace = implode('\\', array_map('ucfirst', explode('/', $path)));
            // Prepend the root namespace
            return $rootNamespace . '\\' . $namespace;
        }

        return $rootNamespace . '\Enums';
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Exception
     */
    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);
        $enums = $this->loadEnums();

        return (new StubAssembler($stub, $enums))
            ->replaceCommand($this->compileCommand())
            ->replaceConstants()
            ->replaceMap()
            ->getStub();
    }

    /**
     * Retrieve enums from the database.
     *
     * @return array
     * @throws \Exception
     */
    private function loadEnums()
    {
        $id = $this->option('id');
        $slug = $this->option('slug');
        $values = explode(',', $this->option('value'));
        $multipleValues = count($values) > 1;

        if ($this->option('table')) {
            $data = DB::table($this->option('table'))->select(array_merge([
                $id,
                $slug,
            ], $values))->get();
        } elseif ($this->option('model')) {
            $name = $this->option('model');
            if (class_exists($name)) {
                $reflectionClass = new \ReflectionClass($name);

                if (! $reflectionClass->isSubclassOf('Illuminate\Database\Eloquent\Model')) {
                    throw new \Exception("$name is not subclass of Model");
                }

                if (! $reflectionClass->IsInstantiable()) {
                    throw new \Exception("$name is not instantiable");
                }

                $model = $this->laravel->make($name);

                $data = $model->get();
            } else {
                throw new \Exception("$name does not exist.");
            }
        } else {
            throw new \Exception("Command requires at least one of: '--table=' OR '--model=' to be defined.");
        }

        return $data->filter(function ($row) use ($slug) {
            return ! empty($row->{$slug});
        })->map(function ($row) use ($id, $slug, $values, $multipleValues) {
            return $this->hydrateEnumDefinition([
              $row->{$slug},
              $row->{$id},
              $multipleValues
                  ? collect($values)->mapWithKeys(function ($column) use ($row) {
                      return [
                          $column => $row->{$column},
                      ];
                  })->toArray()
                  : $row->{$values[0]},
            ]);
        })->toArray();
    }

    /**
     * Retrieve the hydrated enum definition.
     *
     * @param array $parts
     * @return EnumDefinition
     */
    private function hydrateEnumDefinition(array $parts)
    {
        $enum = new EnumDefinition;
        $enum->name = $this->prepareConstantName($parts[0]);
        $enum->key = $parts[1];
        $enum->value = $parts[2];

        return $enum;
    }

    /**
     * Prepare constant name.
     *
     * @return string
     */
    private function prepareConstantName($name)
    {
        $name = strtoupper(str_replace('-', '_', $this->inflector->urlize($name)));

        if (is_numeric($name[0])) {
            $name = 'NUMBER_' . $name;
        }

        return $name;
    }

    /**
     * Retrieve current command.
     *
     * @return string
     */
    private function compileCommand()
    {
        $arguments = $this->arguments();
        unset($arguments[0]);

        $arguments = collect($arguments)->implode(' ');

        $options = collect($this->options())->except('force')->filter(function ($item) {
            return $item;
        })->map(function ($item, $key) {
            $return = '--'.$key;
            if ($item !== true) {
                $return .= '='.$item;
            }

            return $return;
        })->implode(' ');

        return 'php artisan ' . $arguments . ' ' . $options;
    }
}
