<?php

namespace Espo\Core;

class Container
{

	private $data = array();


	/**
     * Constructor
     */
    public function __construct()
    {

    }
    
    public function get($name)
    {
    	if (empty($this->data[$name])) {
    		$this->load($name);
    	}    	
    	return $this->data[$name];
    }

    private function load($name)
    {
    	$loadMethod = 'load' . ucfirst($name);
    	if (method_exists($this, $loadMethod)) {
    		$obj = $this->$loadMethod();
    		$this->data[$name] = $obj;
    	} else {
            //external loader class \Espo\Core\Loaders\<className> or \Custom\Espo\Core\Loaders\<className> with load() method
			$className = '\Espo\Custom\Core\Loaders\\'.ucfirst($name);
            if (!class_exists($className)) {
            	$className = '\Espo\Core\Loaders\\'.ucfirst($name);
            }

			if (class_exists($className)) {
            	 $loadClass = new $className($this);
				 $this->data[$name] = $loadClass->load();
			}
    	}

		// TODO throw an exception
    	return null;
    }


    private function loadSlim()
    {
        //return new \Slim\Slim();
        return new \Espo\Core\Utils\Api\Slim();
    }

	private function loadFileManager()
    {
    	return new \Espo\Core\Utils\File\Manager(
			(object) array(
				'defaultPermissions' => (object)  array (
				    'dir' => '0775',
				    'file' => '0664',
				    'user' => '',
				    'group' => '',
			  ),
			)
		);
    }

	private function loadConfig()
    {
    	return new \Espo\Core\Utils\Config(
			$this->get('fileManager')
		);
    }
    
	private function loadHookManager()
    {
    	return new \Espo\Core\HookManager(
			$this
		);
    }

	private function loadLog()
    {
    	return new \Espo\Core\Utils\Log(
			$this->get('fileManager'),
			$this->get('output'),
			(object) array(
				'options' => $this->get('config')->get('logger'),
			)
		);
    }

	private function loadOutput()
    {
    	return new \Espo\Core\Utils\Api\Output(
			$this->get('slim')
		);
    }
    
	private function loadMailSender()
    {
    	return new \Espo\Core\Mail\Sender(
			$this->get('config')
		);
    }
    
	private function loadServiceFactory()
    {
    	return new \Espo\Core\ServiceFactory(
			$this
		);
    }

	private function loadMetadata()
    {
    	return new \Espo\Core\Utils\Metadata(
			$this->get('config'),
			$this->get('fileManager'),
			$this->get('uniteFiles')
		);
    }


	private function loadLayout()
    {
    	return new \Espo\Core\Utils\Layout(
			$this->get('config'),
			$this->get('fileManager'),
			$this->get('metadata')
		);
    } 

	private function loadUniteFiles()
    {
       	return new \Espo\Core\Utils\File\UniteFiles(
			$this->get('fileManager'),
            (object) array(
				'unsetFileName' => $this->get('config')->get('unsetFileName'),
				'defaultsPath' => $this->get('config')->get('defaultsPath'),
			)
		);
    }
    
	private function loadAcl()
	{
		return new \Espo\Core\Acl(
			$this->get('user'),
			$this->get('config'),
			$this->get('fileManager')
		);
	}

	private function loadSchema()
	{
		return new \Espo\Core\Utils\Database\Schema\Schema(
			$this->get('config'),
			$this->get('metadata'),
			$this->get('fileManager')
		);
	}
	
	public function setUser($user)
	{
		$this->data['user'] = $user;
	} 

}

