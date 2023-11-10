<?php

namespace LaraJS\Core\Generators\Backend;

use LaraJS\Core\Generators\BaseGenerator;

class RepositoryGenerator extends BaseGenerator
{
    public function __construct($model)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.repository');

        $this->_generate($model);
    }

    private function _generate($model)
    {
        $createFolderModel = '/'.$model['name'].'/';
        $pathTemplate = 'Repositories/';
        //template Repository
        $templateDataRepository = $this->serviceGenerator->get_template('Repository', $pathTemplate);
        $templateDataRepository = str_replace('{{MODEL_CLASS}}', $model['name'], $templateDataRepository);
        $templateDataRepository = str_replace(
            '{{MODEL_CLASS_PARAM}}',
            $this->serviceGenerator->modelNameNotPluralFe($model['name']),
            $templateDataRepository,
        );
        $fileNameRepository = $model['name'].'Repository.php';
        $this->serviceFile->createFile($this->path.$createFolderModel, $fileNameRepository, $templateDataRepository);
        //template Interface
        $templateDataInterface = $this->serviceGenerator->get_template('Interface', $pathTemplate);
        $templateDataInterface = str_replace('{{MODEL_CLASS}}', $model['name'], $templateDataInterface);
        $fileNameInterFace = $model['name'].'RepositoryInterface.php';
        $this->serviceFile->createFile($this->path.$createFolderModel, $fileNameInterFace, $templateDataInterface);
        // add bind to RepositoryServiceProvider
        //        $fileName = 'RepositoryServiceProvider.php';
        //        $templateDataReal = $this->serviceGenerator->getFile('provider', 'laravel', $fileName);
        //        $templateDataReal = $this->phpParserService->usePackage($templateDataReal, "App\Repositories\\{$model['name']}\\{$model['name']}Interface");
        //        $model['class'] = 'Repository';
        //        $templateDataReal = $this->phpParserService->usePackage($templateDataReal, $this->serviceGenerator->generateRepositoryProvider('ast_use_class', $model));
        //        $model['class'] = 'Interface';
        //        $templateDataReal = $this->phpParserService->addCodeToFunction($templateDataReal, $this->serviceGenerator->generateRepositoryProvider('register', $model), 'register');
        //        $path = config('generator.path.laravel.provider');
        //        $this->serviceFile->createFileReal("$path/$fileName", $templateDataReal);
    }
}
