<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Nuna_Loader extends CI_Loader {
    protected $Nuna;
    
    public function __construct() {
        parent::__construct();
        
        $this->Nuna =& get_instance();
    }
    
	/**
	 * Model Loader
	 *
	 * This function lets users load and instantiate models.
	 *
	 * @param	string	the name of the class
	 * @param	string	name for the model
	 * @param	bool	database connection
	 * @return	void
	 */
	public function model($model, $name = '', $db_conn = FALSE)
	{
		if (is_array($model))
		{
			foreach ($model as $babe)
			{
				$this->model($babe);
			}
			return;
		}

		if ($model == '')
		{
			return;
		}

		$path = '';

		// Is the model in a sub-folder? If so, parse out the filename and path.
		if (($last_slash = strrpos($model, '/')) !== FALSE)
		{
			// The path is in front of the last slash
			$path = substr($model, 0, $last_slash + 1);

			// And the model name behind it
			$model = substr($model, $last_slash + 1);
		}

		if ($name == '')
		{
			$name = $model;
		}

		if (in_array($name, $this->_ci_models, TRUE))
		{
			return;
		}

		if (isset($this->Nuna->$name))
		{
			show_error('The model name you are loading is the name of a resource that is already being used: '.$name);
		}

		$model = ucfirst($model);

		foreach ($this->_ci_model_paths as $mod_path)
		{
			if ( ! file_exists($mod_path.'models/'.$path.$model.'.php'))
			{
				continue;
			}

			if ($db_conn !== FALSE AND ! class_exists('CI_DB'))
			{
				if ($db_conn === TRUE)
				{
					$db_conn = '';
				}

				$this->Nuna->load->database($db_conn, FALSE, TRUE);
			}

			if ( ! class_exists('CI_Model'))
			{
				load_class('Model', 'core');
			}

			require_once($mod_path.'models/'.$path.$model.'.php');

			$model = ucfirst($model);

			$this->Nuna->$name = new $model();

			$this->_ci_models[] = $name;
			return;
		}

		// couldn't find the model
		show_error('Unable to locate the model you have specified: '.$model);
	}

    public function tpl($tpl, $vars = array(), $return = FALSE) {
        $this->Nuna->load->library('lib_tpl', false, 'tpl');
        
        $tplParse = explode('.', $tpl);
        $tplId = $tplParse[0];
        
        // make template id
        $tplId = str_replace('/', '_', $tplId);
        
        $this->Nuna->tpl->define($tplId, $tpl);

        if(!empty($vars)) {
            $this->Nuna->tpl->assign($vars);
        }

        if($return) {
            return $this->Nuna->tpl->fetch($tplId);
        } else {
            $this->Nuna->tpl->print_($tplId);
        }
    }
    
    public function import($resource, $params = false, $opt = false) {
        $this->Nuna->load->library('lib_tpl', false, 'tpl');

        /**
         * lib : lib.auth
         */
         
         $res_parse = explode('.', $resource);
         if(!empty($res_parse) && count($res_parse) >= 2) {
            $res_type = array_shift($res_parse);

            return $this->getResource($res_type, $res_parse, $params, $opt);
         } else {
            show_error('Resource is Empty!');
         }
    }
    
    protected function getResource($type, $res_params, $params, $opt) {
        
        switch($type) {
            case 'view': return $this->geView($res_params, $params, $opt); break;
            case 'tpl': return $this->getTpl($res_params, $params, $opt); break;
            case 'model': return $this->getModel($res_params, $params, $opt); break;
            case 'ci': return $this->getCiLib($res_params, $params, $opt); break;
            case 'lib': return $this->getLib($res_params, $params, $opt); break;
            default: return false; break;
        }
    }
    
    protected function geView($res_params, $params, $return) {
        $viewFileName = implode('/', $res_params);
        $customViewFileName = 'views/' . $viewFileName . '.php';
        
        if(file_exists($customViewFileName)) {
            $this->vars($params);
            
            return $this->file($customViewFileName, $return);
        } else {
            return $this->view($viewFileName, $params, $return);
        }
    }
    
    protected function getTpl($res_params, $params, $return) {
        $ext = array_pop($res_params);
        $tplFileName = implode('/', $res_params) . '.' . $ext;
        $customTplFileName = 'template/' . $tplFileName;
        
        if(file_exists($customTplFileName)) {
            $this->Nuna->tpl->setCustomTemplateDir(realpath('template'));
        }
        
        return $this->tpl($tplFileName, $params, $return);
    }
    
    protected function getModel($model, $name, $db_conn) {
        $orgModelPaths = $this->_ci_model_paths;
        
	    $this->_ci_model_paths = array_merge(array('./'), $this->_ci_model_paths);

        $modelName = 'model_' . strtolower(implode('_', $model));
        array_pop($model);
        $nunaModel = (!empty($model) ? strtolower(implode('/', $model)) . '/' : '') . $modelName;
        
        $this->model($nunaModel, $name, $db_conn);
        
        $this->_ci_model_paths = $orgModelPaths;
        
        return isset($this->Nuna->{$modelName}) ? $this->Nuna->{$modelName} : false;
    }
    
    protected function getCiLib($library, $params, $object_name) {
        $library = strtolower($library[0]);
        
        $params = empty($params) ? null : $params;
        $object_name = empty($object_name) ? null : $object_name;

        $this->library($library, $params, $object_name);
        
        return isset($this->Nuna->{$library}) ? $this->Nuna->{$library} : false;
    }

    protected function getLib($library, $params, $object_name) {
        $libraryName = 'lib_' . strtolower(implode('_', $library));
        array_pop($library);
        $nunaLibrary = (!empty($library) ? strtolower(implode('/', $library)) . '/' : '') . $libraryName;
        
        $params = empty($params) ? null : $params;
        $object_name = empty($object_name) ? null : $object_name;

        $this->library($nunaLibrary, $params, $object_name);
        
        return isset($this->Nuna->{$nunaLibrary}) ? $this->Nuna->{$nunaLibrary} : false;
    }

}