<?php

namespace LaraJS\Core\Generators\Backend;

use Carbon\Carbon;
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
        $now = Carbon::now();
        $createFolderModel = '/'.$model['name'].'/';
        $pathTemplate = 'Repositories/';
        //template Repository
        $templateDataRepository = $this->serviceGenerator->get_template('Repository', $pathTemplate);
        $templateDataRepository = str_replace('{{DATE}}', $now->toDateTimeString(), $templateDataRepository);
        $templateDataRepository = str_replace('{{MODEL_CLASS}}', $model['name'], $templateDataRepository);
        $templateDataRepository = str_replace(
            '{{MODAL_CLASS_PARAM}}',
            $this->serviceGenerator->modelNameNotPluralFe($model['name']),
            $templateDataRepository,
        );
        $fileNameRepository = $model['name'].'Repository.php';
        $this->serviceFile->createFile($this->path.$createFolderModel, $fileNameRepository, $templateDataRepository);
        //template Interface
        $templateDataInterface = $this->serviceGenerator->get_template('Interface', $pathTemplate);
        $templateDataInterface = str_replace('{{DATE}}', $now->toDateTimeString(), $templateDataInterface);
        $templateDataInterface = str_replace('{{MODEL_CLASS}}', $model['name'], $templateDataInterface);
        $fileNameInterFace = $model['name'].'Interface.php';
        $this->serviceFile->createFile($this->path.$createFolderModel, $fileNameInterFace, $templateDataInterface);
        // add bind to RepositoryServiceProvider
        $fileName = 'RepositoryServiceProvider.php';
        $notDelete = config('generator.not_delete.laravel.repository.provider');
        $templateDataReal = $this->serviceGenerator->getFile('provider', 'laravel', $fileName);
        $templateDataReal = $this->serviceGenerator->replaceNotDelete(
            $notDelete['use_class'],
            "use App\Repositories\\{$model['name']}\\{$model['name']}Interface;",
            0,
            $templateDataReal,
        );
        $model['class'] = 'Repository';
        $templateDataReal = $this->serviceGenerator->replaceNotDelete(
            $notDelete['use_class'],
            $this->serviceGenerator->generateRepositoryProvider('use_class', $model),
            0,
            $templateDataReal,
        );
        $model['class'] = 'Interface';
        $templateDataReal = $this->serviceGenerator->replaceNotDelete(
            $notDelete['register'],
            $this->serviceGenerator->generateRepositoryProvider('register', $model),
            2,
            $templateDataReal,
        );
        $path = config('generator.path.laravel.provider');
        $this->serviceFile->createFileReal("$path/$fileName", $templateDataReal);
    }
}
