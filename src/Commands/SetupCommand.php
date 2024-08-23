<?php

namespace LaraJS\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SetupCommand extends Command
{
    protected string $env;

    protected string $envTesting;

    protected string $appUrlStub;

    protected string $dbHostStub;

    protected string $dbPortStub;

    protected string $dbDatabaseStub;

    protected string $dbUsernameStub;

    protected string $dbPasswordStub;

    protected string $appUrl;

    protected string $host;

    protected string $port;

    protected string $database;

    protected string $username;

    protected string $password;

    protected string $cacheConfig;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larajs:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup LaraJS';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->env = '.env';
        $this->envTesting = '.env.testing';
        $this->cacheConfig = base_path('bootstrap/cache/config.php');
    }

    /**
     * Execute the console command.
     *
     * @return string
     */
    public function handle()
    {
        try {
            $this->_createEnv();
            $this->_installMigrateSeed();
            $this->_installPackage();
            $this->_generateFile();
            $this->_deployStorage();
            //            $this->_copyEnvTesting();

            $this->_outputArtisan('config:clear');
            $this->info($this->_textSignature());
            $this->info('Powered by: LaraJS');
            $this->comment('SETUP SUCCESSFULLY!');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            $this->info('>>> Running: Remove env');
            $this->_outputArtisan('config:clear');
            File::delete(base_path($this->env));
            $this->comment('==========Stop setup==========');
        }
    }

    private function _installMigrateSeed()
    {
        $this->info('>>> Running: migrate and seed');
        $this->_outputArtisan('migrate:fresh --seed');
    }

    private function _installPackage()
    {
        $this->comment('INSTALL PACKAGE');
        $this->info('>>> Running: yarn install');
        exec('yarn install');
    }

    private function _generateFile()
    {
        $this->comment('GENERATE KEY');
        $this->_outputArtisan('key:generate');
        $this->comment('GENERATE LANG');
        $this->_outputArtisan('vue-i18n:generate');
        $this->info('Generate lang successfully.');
    }

    private function _deployStorage()
    {
        $this->comment('DEPLOY Storage');
        $this->info('>>> Running: deploy storage:link');
        exec('php artisan storage:link');
    }

    private function _copyEnvTesting()
    {
        $this->comment('COPY ENV TO TESTING');
        exec("cp {$this->env} {$this->envTesting}");
        $templateEnv = File::get(base_path($this->envTesting));
        $dbName = "DB_DATABASE={$this->database}";
        $templateEnv = str_replace($dbName, "{$dbName}_testing", $templateEnv);
        File::put(base_path($this->envTesting), $templateEnv);
        $this->warn("Created a {$this->envTesting} file. Please create a database name ({$this->database}_testing) if you run command: php artisan test");
    }

    private function _createEnv(): void
    {
        $this->comment('SETUP DATABASE');
        $this->appUrlStub = '{{APP_URL}}';
        $this->dbHostStub = '{{DB_HOST}}';
        $this->dbPortStub = '{{DB_PORT}}';
        $this->dbDatabaseStub = '{{DB_DATABASE}}';
        $this->dbUsernameStub = '{{DB_USERNAME}}';
        $this->dbPasswordStub = '{{DB_PASSWORD}}';
        $envExample = '.env.example';
        $parAppUrl = 'http://127.0.0.1:8000';
        $parHost = '127.0.0.1';
        $parPort = '3306';
        $parDatabase = 'larajs';
        $parUsername = 'root';
        $parPassword = '';
        $this->info('>>> Running: create env');
        $this->appUrl = $this->anticipate('What is the url of your app?', [$parAppUrl], $parAppUrl);
        $this->host = $this->anticipate('What is your database host?', [$parHost], $parHost);
        $this->port = $this->anticipate('What is your database port?', [$parPort], $parPort);
        $this->database = $this->anticipate('What is your database name?', [$parDatabase], $parDatabase);
        $this->username = $this->anticipate('What is your username?', [$parUsername], $parUsername);
        $this->password = $this->anticipate('What is your password?', [$parPassword], $parPassword);

        $fileEnvEx = File::get(base_path($envExample));
        $fileEnvEx = $this->_replaceEnvConfig($fileEnvEx);

        $fileConfig = File::get($this->cacheConfig);
        $fileConfig = $this->_replaceEnvConfig($fileConfig);

        File::put(base_path($this->env), $fileEnvEx);
        File::put($this->cacheConfig, $fileConfig);

        $this->_outputArtisan('config:cache');
        $this->_outputArtisan('key:generate');
    }

    private function _replaceEnvConfig($fileEnvEx): string
    {
        return str_replace([$this->appUrlStub, $this->dbHostStub, $this->dbPortStub, $this->dbDatabaseStub, $this->dbUsernameStub, $this->dbPasswordStub], [$this->appUrl, $this->host, $this->port, $this->database, $this->username, $this->password], $fileEnvEx);
    }

    private function _outputArtisan($command)
    {
        Artisan::call($command, [], $this->getOutput());
    }

    private function _textSignature(): string
    {
        // ANSI Shadow
        return <<<'SIGNATURE'
        ██╗      █████╗ ██████╗  █████╗      ██╗███████╗
        ██║     ██╔══██╗██╔══██╗██╔══██╗     ██║██╔════╝
        ██║     ███████║██████╔╝███████║     ██║███████╗
        ██║     ██╔══██║██╔══██╗██╔══██║██   ██║╚════██║
        ███████╗██║  ██║██║  ██║██║  ██║╚█████╔╝███████║
        ╚══════╝╚═╝  ╚═╝╚═╝  ╚═╝╚═╝  ╚═╝ ╚════╝ ╚══════╝
        SIGNATURE;
    }
}
