<?php

namespace Pravin\Crud\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MvcCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:crud
                            {name : The name of the CRUD.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new CRUD';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return 
     */
    public function handle()
    {
        $this->line('Starting CRUD Generation...');
        $name = $this->argument('name');
        $this->line('Creating model...');
        sleep(1);
        $this->model($name);
        $this->line('Creating view...');
        sleep(1);
        $this->view($name,'create');
        $this->view($name,'edit');
        $this->view($name,'form');
        $this->view($name,'index');
        $this->view($name,'show');
        $this->line('Creating controller...');
        sleep(1);
        $this->controller($name);
        $this->line('Creating migration...');
        sleep(1);
        $this->migration($name);
        $this->line('Creating request...');
        sleep(1);
        $this->request($name);
        sleep(1);
        $this->line('Creating data grid...');
        sleep(1);
        $this->datagrid($name);
        $this->line('Creating form...');
        sleep(1);
        $this->form($name);
        $this->line('Creating repository...');
        $this->call('make:repository', ['name' => $name]);
        $this->line('Creating route...');
        sleep(1);
        $this->route($name);
        $this->line('Creating language...');
        sleep(1);
        $this->language($name);
        $this->line('Creating sidebar...');
        sleep(1);
        $this->sidebar($name);
        $this->line('
            #######  ##        #  ##########  #######  #########  ##        #  #######  ########
               #     # #       #  #        #     #     #       #  # #       #     #     #
               #     #  #      #  #        #     #     #       #  #  #      #     #     #
               #     #   #     #  #        #     #     #       #  #   #     #     #     #
               #     #    #    #  #        #     #     #########  #    #    #     #     #
               #     #     #   #  #        #     #     #       #  #     #   #     #     #
               #     #      #  #  #        #     #     #       #  #      #  #     #     #
               #     #       # #  #        #     #     #       #  #       # #     #     #
               #     #        ##  #        #     #     #       #  #        ##     #     #
            #######  #         #  ##########  #######  #       #  #         #  #######  ########
        ');
    }

    /**
     * This function will get template content
     * @param  [string] $type 
     * @return [string]       
     */
    protected function getTemplate($type)
    {
        return file_get_contents(base_path("vendor/indianic/crud/src/templates/$type.temp"));
    }

    /**
     * This function will create model
     * @param  [string] $name 
     * @return [type]       
     */
    protected function model($name)
    {
        $modelTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getTemplate('model')
        );
        $path = app_path("/Models/{$name}.php");
        if (File::exists($path)) {
            $this->error("Model already exists.");
        }else{
            file_put_contents($path, $modelTemplate);
            $this->info('Model created successfully.');
        }
    }
    /**
     * This function will get view template content
     * @param  [string] $type 
     * @return [string]       
     */
    protected function getViewTemplate($type)
    {
        return file_get_contents(base_path("vendor/indianic/crud/src/templates/views/$type.temp"));
    }
    /**
     * This function will create view
     * @param  [type] $name [string]
     * @return [type]       [description]
     */
    protected function view($name,$type){
        $slug = strtolower($name);
        $slug = Str::plural($slug);
        $viewTemplate = str_replace(
            ['{{modelName}}','{{modelPluralSlug}}','{{modelLower}}'],
            [$name,$slug,strtolower($name)],
            $this->getViewTemplate($type)
        );
        $lower = strtolower($name);
        $path = resource_path("views/admin/".$lower);
        if (!File::isDirectory($path)) {
            mkdir($path, 0777 , true);
        }
        $path = $path."/".$type.".blade.php";
        if (File::exists($path)) {
            $this->error("View already exists.");
        }else{
            file_put_contents($path, $viewTemplate);
            $this->info('View created successfully.');
        }
    }

    /**
     * This function will create controller
     * @param  [string] $name 
     * @return [type]       
     */
    protected function controller($name)
    {
        $slug = Str::snake($name);
        $slug = Str::plural($slug);
        $controllerTemplate = str_replace(
            ['{{modelName}}','{{modelNameLower}}','{{modelPluralSlug}}'],
            [$name,strtolower($name),$slug],
            $this->getTemplate('controller')
        );
        $path = app_path("/Http/Controllers/Admin/{$name}Controller.php");
        if (File::exists($path)) {
            $this->error("Controller already exists.");
        }else{
            file_put_contents($path, $controllerTemplate);
            $this->info('Controller created successfully.');
        }
    }

    /**
     * This function will create request
     * @param  [string] $name
     * @return [type]       
     */
    protected function request($name)
    {
        $requestTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getTemplate('request')
        );
        if(!file_exists($path = app_path('/Http/Requests'))) {
            mkdir($path, 0777, true);
        }
        $path = app_path("/Http/Requests/{$name}Request.php");
        if (File::exists($path)) {
            $this->error("Request already exists.");
        }else{
            file_put_contents($path, $requestTemplate);
            $this->info('Request created successfully.');
        }
    }

    /**
     * This function will create migration
     * @param  [string] $name 
     * @return [type]       
     */
    protected function migration($name)
    {
        $files = scandir(database_path("migrations/"));
        $files = array_diff(scandir(database_path("migrations/")), array('.', '..'));
        $status = true;
        foreach($files as $file){
          $content = file_get_contents(database_path("migrations/").$file);
          if (strpos($content,'Create'.Str::plural($name).'Table')) {
              $this->error("Cannot declare class Create".Str::plural($name)."Table, because the name is already in use.");
              $status = false;
              break;
          }
        }
        if ($status) {
            $slug = strtolower($name);
            $slug = Str::plural($slug);
            $requestTemplate = str_replace(
                ['{{modelName}}','{{modelPluralSlug}}'],
                [Str::plural($name),$slug],
                $this->getTemplate('migration')
            );
            $path = database_path("migrations/".date('Y_m_d_').time()."_create_".$slug."_table.php");
            file_put_contents($path, $requestTemplate);
            $this->info('Migration created successfully.');
        }
    }
    /**
     * This function will create datagrid file
     * @param  [type] $name [string]
     * @return [type]       [description]
     */
    protected function datagrid($name){
        $slug = Str::snake($name);
        $slug = Str::plural($slug);
        $datagridTemplate = str_replace(
            ['{{modelName}}','{{modelPluralSlug}}'],
            [$name,$slug],
            $this->getTemplate('datagrid')
        );
        if(!file_exists($path = app_path('/DataGrids'))) {
            mkdir($path, 0777, true);
        }
        $path = app_path("/DataGrids/{$name}DataGrid.php");
        if (File::exists($path)) {
            $this->error("DataGrid already exists.");
        }else{
            file_put_contents($path, $datagridTemplate);
            $this->info('DataGrid created successfully.');
        }
    }
    /**
     * This function will create form file
     * @param  [type] $name [string]
     * @return [type]       [description]
     */
    protected function form($name){
        $slug = strtolower($name);
        $slug = Str::plural($slug);
        $formTemplate = str_replace(
            ['{{modelName}}','{{modelNameLower}}','{{modelPluralSlug}}'],
            [$name,strtolower($name),$slug],
            $this->getTemplate('form')
        );
        $path = app_path("/Forms/{$name}Form.php");
        if (File::exists($path)) {
            $this->error("Form already exists.");
        }else{
            file_put_contents($path, $formTemplate);
            $this->info('Form created successfully.');
        }
    }

    /**
     * This function will create ruute
     * @param  [string] $name 
     * @return [type]       
     */
    protected function route($name){
        $slug = strtolower($name);
        $slug = Str::plural($slug);
        $routeTemplate = str_replace(
            ['{{modelName}}','{{modelPluralSlug}}'],
            [$name,$slug],
            $this->getTemplate('route')
        );
        File::append(base_path('routes/admin.php'), PHP_EOL.$routeTemplate.PHP_EOL);
        $this->info('Route created successfully.');
    }

    /**
     * This function will create lang
     * @param  [string] $name 
     * @return [type]       
     */
    protected function language($name)
    {
        $template = str_replace(
            ['{{modelName}}','{{modelNameLower}}'],
            [$name,strtolower($name)],
            $this->getTemplate('lang')
        );
        $modelNameLowerCase = strtolower($name);
        $path = resource_path("/lang/en/{$modelNameLowerCase}.php");
        if (File::exists($path)) {
            $this->error("File already exists.");
        }else{
            file_put_contents(resource_path("/lang/en/{$modelNameLowerCase}.php"), $template);
            $this->info('Lang created successfully.');
        }
    }

    /**
     * This function will create sidebar
     * @param  [string] $name
     * @return [type]      
     */
    protected function sidebar($name){
        if (!File::exists(resource_path('views/admin/layouts/includes/addon-left-sidebar.blade.php'))) {
            $this->error("addon-left-sidebar.blade.php file not found.");
        }else{
            $fp = fopen(resource_path('views/admin/layouts/includes/addon-left-sidebar.blade.php'), 'a');
                fwrite($fp, "\n @include('{$name}::sidebar') ");
                fclose($fp);
        }
    }
}
