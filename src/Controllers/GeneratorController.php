<?php

namespace LaraJS\Core\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaraJS\Core\Generators\Backend\ControllerGenerator;
use LaraJS\Core\Generators\Backend\FactoryGenerator;
use LaraJS\Core\Generators\Backend\LangGenerator;
use LaraJS\Core\Generators\Backend\MigrationGenerator;
use LaraJS\Core\Generators\Backend\ModelGenerator;
use LaraJS\Core\Generators\Backend\RelationshipGenerator;
use LaraJS\Core\Generators\Backend\RepositoryGenerator;
use LaraJS\Core\Generators\Backend\RequestGenerator;
use LaraJS\Core\Generators\Backend\RouteGenerator;
use LaraJS\Core\Generators\Backend\SeederGenerator;
use LaraJS\Core\Generators\Backend\TestsGenerator;
use LaraJS\Core\Generators\BackendUpdate\FactoryUpdateGenerator;
use LaraJS\Core\Generators\BackendUpdate\LangUpdateGenerator;
use LaraJS\Core\Generators\BackendUpdate\MigrationUpdateGenerator;
use LaraJS\Core\Generators\BackendUpdate\ModelUpdateGenerator;
use LaraJS\Core\Generators\BackendUpdate\RequestUpdateGenerator;
use LaraJS\Core\Generators\BackendUpdate\SeederUpdateGenerator;
use LaraJS\Core\Generators\BackendUpdate\TestsUpdateGenerator;
use LaraJS\Core\Generators\BaseGenerator;
use LaraJS\Core\Generators\Frontend\ApiGenerator;
use LaraJS\Core\Generators\Frontend\FormGenerator;
use LaraJS\Core\Generators\Frontend\InterfaceCommonGenerator;
use LaraJS\Core\Generators\Frontend\RouteGenerator as RouteGeneratorFe;
use LaraJS\Core\Generators\Frontend\UsesGenerator;
use LaraJS\Core\Generators\Frontend\ViewTableGenerator;
use LaraJS\Core\Generators\FrontendUpdate\InterfaceCommonUpdateGenerator;
use LaraJS\Core\Generators\FrontendUpdate\UsesUpdateGenerator;
use LaraJS\Core\Models\Generator;
use LaraJS\Core\Services\FileService;
use LaraJS\Core\Services\GeneratorService;
use LaraJS\Core\Services\QueryService;
use Symfony\Component\Process\Process;

class GeneratorController extends BaseLaraJSController
{
    /*@var service*/
    private GeneratorService $serviceGenerator;

    private BaseGenerator $baseGenerator;

    public function __construct()
    {
        $this->serviceGenerator = new GeneratorService();
        $this->baseGenerator = new BaseGenerator();
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $queryService = new QueryService(new Generator());
            $queryService->filters([
                'select' => [],
                'columnSearch' => ['table'],
                'withRelationship' => [],
                'search' => $request->get('search'),
                'betweenDate' => $request->get('between_date'),
                'direction' => $request->get('direction'),
                'orderBy' => $request->get('orderBy'),
                'limit' => $request->get('limit'),
            ]);
            $query = $queryService->query();
            $generators = $query->paginate($queryService->limit);

            return $this->jsonTable($generators);
        } catch (\Exception $e) {
            return $this->jsonError($e);
        }
    }

    public function show(Generator $generator): JsonResponse
    {
        try {
            return $this->jsonData($generator);
        } catch (\Exception $e) {
            return $this->jsonError($e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $fields = $request->get('fields', []);
            $model = $request->get('model', []);
            // git commit
            $this->_gitCommit($model['name']);
            if (
                $this->serviceGenerator->getOptions(config('generator.model.options.only_migrate'), $model['options'])
            ) {
                $migrationGenerator = new MigrationGenerator($fields, $model);
                new ModelGenerator($fields, $model);
                $files['migration']['file'] = $migrationGenerator->file;
            } else {
                $generateBackend = $this->_generateBackend($fields, $model);
                $this->_generateFrontend($fields, $model);
                $files = $this->_generateFile($model, $generateBackend);
            }
            Generator::create([
                'field' => json_encode($fields),
                'model' => json_encode($model),
                'table' => $this->serviceGenerator->tableName($model['name']),
                'files' => json_encode($files),
            ]);
            $this->_exportDataGenerator();
            $this->_runCommand($model);

            return $this->jsonMessage(trans('messages.success'));
        } catch (\Exception $e) {
            return $this->jsonError($e);
        }
    }

    public function update(Request $request, Generator $generator): JsonResponse
    {
        try {
            $fields = $request->get('fields', []);
            $updateFields = $request->get('fields_update', []);
            $model = $request->get('model', []);
            $renameFields = $request->get('rename', []);
            $changeFields = $request->get('change', []);
            $dropFields = $request->get('drop', []);
            $updateFields = [
                'updateFields' => $updateFields,
                'renameFields' => $renameFields,
                'changeFields' => $changeFields,
                'dropFields' => $dropFields,
            ];
            // git commit
            $this->_gitCommit($model['name']);
            if (
                $this->serviceGenerator->getOptions(config('generator.model.options.only_migrate'), $model['options'])
            ) {
                new MigrationUpdateGenerator($generator, $model, $updateFields);
                new ModelUpdateGenerator($model, $updateFields);
            } else {
                $this->_generateBackendUpdate($generator, $model, $updateFields);
                $this->_generateFrontendUpdate($model, $updateFields);
            }
            $generator->update([
                'field' => json_encode($fields),
            ]);
            $this->_exportDataGenerator();
            $this->_runCommand();

            return $this->jsonMessage(trans('messages.success'));
        } catch (\Exception $e) {
            return $this->jsonError($e);
        }
    }

    public function destroy(Generator $generator): JsonResponse
    {
        try {
            $model = json_decode($generator->model, true);
            $files = json_decode($generator->files, true);
            $generatorService = new GeneratorService();
            $fileService = new FileService();

            $this->_gitCommit($model['name']);
            // START - Remove File
            foreach ($files as $file) {
                if (is_array($file)) {
                    foreach ($file as $fileInside) {
                        if (file_exists(base_path($fileInside))) {
                            unlink(base_path($fileInside));
                        }
                    }
                } else {
                    if (file_exists(base_path($file))) {
                        unlink(base_path($file));
                    }
                }
            }
            // END - Remove File
            // START - search route
            $templateDataRouteReal = $this->serviceGenerator->getFile('api_routes', 'laravel');
            $startRoute = "/*<==> {$model['name']} Route -";
            $endRoute = "'{$model['name']}Controller');";
            $templateDataRoute = $generatorService->searchTemplate(
                $startRoute,
                $endRoute,
                0,
                strlen($endRoute),
                $templateDataRouteReal,
            );
            $templateDataRouteReal = str_replace($templateDataRoute, '', $templateDataRouteReal);
            $fileService->createFileReal(config('generator.path.laravel.api_routes'), $templateDataRouteReal);
            // END - search route
            // START - search lang
            $nameLanguages = ['route', 'table'];
            $languages = config('generator.not_delete.laravel.lang');
            $tableName = $this->serviceGenerator->tableNameNotPlural($model['name']);
            foreach ($languages as $key => $langComment) {
                foreach ($nameLanguages as $nameLang) {
                    $templateDataLangReal = $this->serviceGenerator->getFile(
                        'lang',
                        'laravel',
                        $key.'/'.$nameLang.'.php',
                    );
                    if ($nameLang === 'route') {
                        $startRouteTable = "// START - {$generatorService->tableNameNotPlural($model['name'])}\n";
                        $endRouteTable = "// END - {$generatorService->tableNameNotPlural($model['name'])}\n";
                        $templateDataLangRoute = $generatorService->searchTemplate(
                            $startRouteTable,
                            $endRouteTable,
                            0,
                            strlen($endRouteTable),
                            $templateDataLangReal,
                        );
                        $templateDataLangReal = str_replace($templateDataLangRoute, '', $templateDataLangReal);
                    }

                    if ($nameLang === 'table') {
                        $quoteTable = "'".$tableName."' => [";
                        $templateDataLangTable = $this->serviceGenerator->searchTemplate(
                            $quoteTable,
                            '],',
                            0,
                            4,
                            $templateDataLangReal,
                        );
                        $templateDataLangReal = str_replace($templateDataLangTable, '', $templateDataLangReal);
                    }
                    $fileService->createFileReal(
                        config('generator.path.laravel.lang').$key.'/'.$nameLang.'.php',
                        $templateDataLangReal,
                    );
                }
            }
            // END - search lang
            // START - repository provider
            $fileName = 'RepositoryServiceProvider.php';
            $templateRepositoryProviderDataReal = $this->serviceGenerator->getFile('provider', 'laravel', $fileName);
            $model['class'] = 'Repository';
            $templateRepositoryProviderDataReal = str_replace(
                $this->serviceGenerator->generateRepositoryProvider('use_class', $model),
                '',
                $templateRepositoryProviderDataReal,
            );
            $model['class'] = 'Interface';
            $templateRepositoryProviderDataReal = str_replace(
                $this->serviceGenerator->generateRepositoryProvider('use_class', $model),
                '',
                $templateRepositoryProviderDataReal,
            );
            $templateRepositoryProviderDataReal = str_replace(
                $this->serviceGenerator->generateRepositoryProvider('register', $model),
                '',
                $templateRepositoryProviderDataReal,
            );
            $path = config('generator.path.laravel.provider');
            $fileService->createFileReal("$path/$fileName", $templateRepositoryProviderDataReal);
            // END - repository provider
            // START - event provider
            $fileName = 'EventServiceProvider.php';
            $templateRepositoryProviderDataReal = $this->serviceGenerator->getFile('provider', 'laravel', $fileName);
            $templateRepositoryProviderDataReal = str_replace(
                $this->serviceGenerator->generateObserverProvider('use_class_model', $model),
                '',
                $templateRepositoryProviderDataReal,
            );
            $templateRepositoryProviderDataReal = str_replace(
                $this->serviceGenerator->generateObserverProvider('use_class_observer', $model),
                '',
                $templateRepositoryProviderDataReal,
            );
            $templateRepositoryProviderDataReal = str_replace(
                $this->serviceGenerator->generateObserverProvider('register', $model),
                '',
                $templateRepositoryProviderDataReal,
            );
            $path = config('generator.path.laravel.provider');
            $fileService->createFileReal("$path/$fileName", $templateRepositoryProviderDataReal);
            // END - event provider
            // START - api VueJS
            $pathApiVueJSReal = config('generator.path.vue.api').'index.ts';
            $templateDataApiVueJSReal = $this->serviceGenerator->getFile('api', 'vue', 'index.ts');
            $templateDataApiVueJSReal = str_replace(
                "export { default as {$model['name']}Resource } from './{$generatorService->nameAttribute($model['name'])}';\n",
                '',
                $templateDataApiVueJSReal,
            );
            $fileService->createFileReal($pathApiVueJSReal, $templateDataApiVueJSReal);
            // END - api VueJS
            // START - route VueJS
            $pathRouteVueJSReal = config('generator.path.vue.router').'index.ts';
            $templateDataRouteVueJSReal = $this->serviceGenerator->getFile('router', 'vue', 'index.ts');
            $templateDataRouteVueJSReal = str_replace(
                "import {$generatorService->modelNameNotPluralFe(
                    $model['name'],
                )} from '{$this->baseGenerator->getImportJsOrTs()}/router/modules/{$generatorService->nameAttribute($model['name'])}';\n",
                '',
                $templateDataRouteVueJSReal,
            );
            $templateDataRouteVueJSReal = str_replace(
                "{$generatorService->modelNameNotPluralFe($model['name'])},\n",
                '',
                $templateDataRouteVueJSReal,
            );
            $fileService->createFileReal($pathRouteVueJSReal, $templateDataRouteVueJSReal);
            // END - route VueJS
            // START - USES
            $templateDataUsesIndex = $this->serviceGenerator->getFile('uses', 'vue', 'index.ts');
            $fileNameUses = $this->serviceGenerator->folderPages($model['name']);
            $templateDataUsesIndex = str_replace("export * from './$fileNameUses';", '', $templateDataUsesIndex);
            $pathUses = config('generator.path.vue.uses');
            $fileService->createFileReal("{$pathUses}index.ts", $templateDataUsesIndex);
            // END - USES
            // START - package common
            if (config('generator.js_language') === 'ts') {
                $pathPackageModelReal = config('generator.path.package.model').'index.ts';
                $templateDataPackageModelReal = $this->serviceGenerator->getFile('model', 'package', 'index.ts');
                $templateDataPackageModelReal = str_replace(
                    "export * from './{$generatorService->nameAttribute($model['name'])}';\n",
                    '',
                    $templateDataPackageModelReal,
                );
                $fileService->createFileReal($pathPackageModelReal, $templateDataPackageModelReal);
            }
            // END - package common
            $generator->delete();
            $this->__runPrettier();

            return $this->jsonMessage(trans('messages.success'));
        } catch (\Exception $e) {
            return $this->jsonError($e);
        }
    }

    public function checkModel(Request $request): JsonResponse
    {
        $serviceGenerator = new GeneratorService();
        $name = $request->get('name', '');
        try {
            if ($name) {
                $name = $serviceGenerator->tableName($name);
                if (Schema::hasTable($name)) {
                    //table exist
                    return $this->jsonData(1);
                }
                // table not exist
                return $this->jsonData(2);
            }
            //name null
            return $this->jsonData(3);
        } catch (\Exception $e) {
            return $this->jsonError($e);
        }
    }

    public function generateRelationship(Request $request): JsonResponse
    {
        $request->validate([
            'relationship' => 'required',
            'model' => 'required',
            'model_name' => $request->get('relationship') === 'belongsToMany' ? 'required' : '',
            'column' => 'required',
            'column2' => $request->get('relationship') === 'belongsToMany' ? 'required' : '',
        ]);
        try {
            $relationship = $request->get('relationship');
            $model = $request->get('model');
            $modelName = $request->get('model_name');
            $modelCurrent = $request->get('model_current');
            $column = $request->get('column');
            $column2 = $request->get('column2');
            $options = $request->get('options', []);
            // git commit
            $this->_gitCommit($model);
            new RelationshipGenerator($relationship, $model, $modelCurrent, $column, $column2, $options, $modelName);
            $this->_runCommand();

            return $this->jsonMessage(trans('messages.success'));
        } catch (\Exception $e) {
            return $this->jsonError($e);
        }
    }

    public function generateDiagram(Request $request): JsonResponse
    {
        try {
            $model = $request->get('model');
            $diagram = $this->serviceGenerator->getDiagram($model);

            return $this->jsonData($diagram);
        } catch (\Exception $e) {
            return $this->jsonError($e);
        }
    }

    public function getModels(Request $request): JsonResponse
    {
        try {
            $table = $request->get('model', []);
            $table = json_decode($table, true);
            $modelTable = $table['name'];
            $pathModel = config('generator.path.laravel.model');
            $ignoreModel = config('generator.relationship.ignore_model');
            $models = File::files($pathModel);
            $modelData = [];
            foreach ($models as $model) {
                $modelName = $model->getBasename('.php');
                if ($modelName !== $modelTable) {
                    if (!in_array($modelName, $ignoreModel)) {
                        $modelData[] = $modelName;
                    }
                }
            }

            return $this->jsonData($modelData);
        } catch (\Exception $e) {
            return $this->jsonError($e);
        }
    }

    public function getAllModels(): JsonResponse
    {
        try {
            $whiteList = ['BaseModel', 'Generator'];
            $allFiles = File::allFiles(app_path('Models'));
            $files = [];
            foreach ($allFiles as $file) {
                $model = basename($file->getFilename(), '.php');
                !in_array($model, $whiteList) && ($files[] = $model);
            }

            return $this->jsonData($files);
        } catch (\Exception $e) {
            return $this->jsonError($e);
        }
    }

    public function getColumns(Request $request): JsonResponse
    {
        try {
            $table = $request->get('table');
            $table = Str::snake(Str::plural($table));
            $columns = Schema::getColumnListing($table);

            return $this->jsonData($columns);
        } catch (\Exception $e) {
            return $this->jsonError($e);
        }
    }

    private function _generateBackend($fields, $model): array
    {
        $migrationGenerator = new MigrationGenerator($fields, $model);
        new ControllerGenerator($model);
        new SeederGenerator($model);
        new FactoryGenerator($fields, $model);
        new ModelGenerator($fields, $model);
        new RepositoryGenerator($model);
        new LangGenerator($fields, $model);
        new RouteGenerator($model);
        new RequestGenerator($fields, $model);
        if ($this->serviceGenerator->getOptions(config('generator.model.options.test_cases'), $model['options'])) {
            new TestsGenerator($fields, $model);
        }

        return [
            'migration' => [
                'file' => $migrationGenerator->file,
            ],
        ];
    }

    private function _generateFrontend($fields, $model): void
    {
        new RouteGeneratorFe($model);
        new ApiGenerator($model);
        new UsesGenerator($fields, $model);
        new ViewTableGenerator($model);
        new FormGenerator($model);
        if (config('generator.js_language') === 'ts') {
            new InterfaceCommonGenerator($fields, $model);
        }
    }

    private function _generateFile($model, $generateBackend): array
    {
        $files = [];
        $configGeneratorLaravel = config('generator')['path']['delete_files']['laravel'];
        $configGeneratorVueJS = config('generator')['path']['delete_files']['vue'];
        $configGeneratorPackage = config('generator')['path']['delete_files']['package'];

        $files['api_controller'] = $configGeneratorLaravel['api_controller'].$model['name'].'Controller.php';
        $files['request'] = $configGeneratorLaravel['request'].'Store'.$model['name'].'Request.php';
        $files['model'] = $configGeneratorLaravel['model'].$model['name'].'.php';
        $files['repositories']['interface'] =
            $configGeneratorLaravel['repository'].$model['name'].'/'.$model['name'].'Interface.php';
        $files['repositories']['repository'] =
            $configGeneratorLaravel['repository'].$model['name'].'/'.$model['name'].'Repository.php';
        $files['observer'] = $configGeneratorLaravel['observer'].$model['name'].'Observer.php';
        $files['migration'] = $configGeneratorLaravel['migration'].$generateBackend['migration']['file'];
        $files['seeder'] = $configGeneratorLaravel['seeder'].$model['name'].'Seeder.php';
        $files['factory'] = $configGeneratorLaravel['factory'].$model['name'].'Factory.php';
        $files['tests'] = $configGeneratorLaravel['tests']['feature'].$model['name'].'Test.php';

        $files['api'] = $configGeneratorVueJS['api'].$this->serviceGenerator->folderPages($model['name']).'.ts';
        $files['router_modules'] =
            $configGeneratorVueJS['router_modules'].$this->serviceGenerator->folderPages($model['name']).'.ts';
        $files['views']['form'] = $configGeneratorVueJS['views'].lcfirst(Str::kebab($model['name'])).'/Form.vue';
        $files['views']['index'] = $configGeneratorVueJS['views'].lcfirst(Str::kebab($model['name'])).'/index.vue';
        $files['uses']['index'] =
            $configGeneratorVueJS['uses'].$this->serviceGenerator->folderPages($model['name']).'/index.ts';
        $files['uses']['table'] =
            $configGeneratorVueJS['uses'].$this->serviceGenerator->folderPages($model['name']).'/table.tsx';
        $files['uses']['form'] =
            $configGeneratorVueJS['uses'].$this->serviceGenerator->folderPages($model['name']).'/form.tsx';

        $files['common']['model'] =
            $configGeneratorPackage['model'].$this->serviceGenerator->folderPages($model['name']).'.ts';

        return $files;
    }

    private function _generateBackendUpdate($generator, $model, $updateFields): void
    {
        new MigrationUpdateGenerator($generator, $model, $updateFields);
        new ModelUpdateGenerator($model, $updateFields);
        new SeederUpdateGenerator($generator, $model, $updateFields);
        new FactoryUpdateGenerator($model, $updateFields);
        new LangUpdateGenerator($model, $updateFields);
        new RequestUpdateGenerator($generator, $model, $updateFields);
        if ($this->serviceGenerator->getOptions(config('generator.model.options.test_cases'), $model['options'])) {
            new TestsUpdateGenerator($model, $updateFields);
        }
    }

    private function _generateFrontendUpdate($model, $updateFields): void
    {
        new UsesUpdateGenerator($model, $updateFields);
        if (config('generator.js_language') === 'ts') {
            new InterfaceCommonUpdateGenerator($updateFields, $model);
        }
    }

    private function _runCommand(array $model = []): void
    {
        if (!isset($model['options'])) {
            $model['options'] = [];
        }
        if (!$this->serviceGenerator->getOptions(config('generator.model.options.ignore_migrate'), $model['options'])) {
            Artisan::call('migrate');
        }
        Artisan::call('vue-i18n:generate');
        $this->__runPrettier();
    }

    private function __runPrettier(): void
    {
        $basePath = apps_path();
        if (config('generator.js_language') === 'js') {
            $basePath = base_path();
        }
        exec_in_background("(sleep 1 && cd $basePath && node ./node_modules/.bin/pretty-quick)");
    }

    private function _exportDataGenerator(): void
    {
        $generators = Generator::withTrashed()
            ->get()
            ->toArray();
        $template = 'INSERT INTO `generators` VALUES ';
        foreach ($generators as $index => $generator) {
            $sql = implode(
                ', ',
                array_map(function ($value) {
                    return $value === null ? 'NULL' : "'$value'";
                }, $generator),
            );
            $template .= '('.$sql.')';
            if ($index !== count($generators) - 1) {
                $template .= ',';
            }
        }
        $disk = Storage::disk('local');
        $fileName = env('DB_DATABASE').'-'.date('YmdHis').'.sql';
        $disk->put("/backup/generators/$fileName", $template);
        $files = $disk->files('/backup/generators');
        $numberFileDeletes = count($files) - Generator::NUMBER_FILE_DELETES;
        if ($numberFileDeletes > 0) {
            for ($i = 0; $i < $numberFileDeletes; $i++) {
                $disk->delete($files[$i]);
            }
        }
    }

    private function _gitCommit($model): void
    {
        if (env('GENERATOR_DEBUG')) {
            return;
        }
        $basePath = base_path();
        $now = \Carbon\Carbon::now()->toDateTimeString();
        $commit = '"'.$model.' - '.$now.'"';

        $gitAdd = new Process(['git', 'add', '.'], $basePath);
        $gitAdd->run();
        $gitCommit = new Process(['git', 'commit', '-m', $commit, '--no-verify'], $basePath);
        $gitCommit->run();
    }
}
