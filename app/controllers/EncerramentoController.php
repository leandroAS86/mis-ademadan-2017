<?php
class EncerramentoController extends \HXPHP\System\Controller
{
	public function __construct($configs)
	{
		parent::__construct($configs);
		$this->load(
			'Services\Auth',
			$configs->auth->after_login,
			$configs->auth->after_logout,
			true
		);
		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);
		$institution = Institution::all();

		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$role->role
		);

		$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
			->setFile('index')
			->setVars([
				'institution' => $institution,
				'institutions' => Institution::all()
			]);

		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$role->role
		);

		$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
					->setVar('user', $user);
	}

	public function registrarAction()
	{
		$callbackMsg = new \stdClass;
		$callbackMsg->errors=array();
		$callbackMsg->msgm = array();

		$this->view->setFile('index');
		$user_id = $this->auth->getUserId();
		$post = $this->request->post();

		$allowedExts = array("pdf", "png", "jpeg","jpg");
		$array_file = array("img_1", "img_2", "img_3");
		$num_file = 3;

		$str = explode(">> ", $post['projeto']);

		$id = $str[1];

		$projects = Projects::find_by_id($id);

		$institution = Institution::find_by_cnpj($projects->cnpj);

		$cnpj = $institution->cnpj;
		$nome = $institution->name_institution;

		$array_post = array('cnpj'=>$institution->cnpj, 'name_institution'=>$institution->name_institution);
		
		$post['projeto'] = $str[2];
		$post = array_merge($post, $array_post);
	
		if($institution->nivel >= 7)
		{
			if (!empty($post))
			{
				$evt = Closures::registrar($user_id, $post);
				if($evt->status === true)
				{
					$institution->nivel = 8;
					$institution->save(false);

					//$is_set = isset($_FILES);
					//$is_empty = !empty($_FILES);
					for ($i = 0; $i < $num_file; $i++) 
					{
						$is_set = isset($_FILES[$array_file[$i]]);
						$is_empty = !empty($_FILES[$array_file[$i]]['tmp_name']);

						if ( $is_set && $is_empty) 
						{	
							$extension = pathinfo($_FILES[$array_file[$i]]['name'], PATHINFO_EXTENSION);

							if(in_array($extension, $allowedExts))
							{
								//deve passar o id do relatorio financieiro
								$reportsUpload = Closures::enviar($array_file[$i], $evt->reports->id);

								if ($reportsUpload->status === false) 
								{								
									$this->load('Helpers\Alert', array(
										'error',
										'Arquivos não foram enviados.',
										$reportsUpload->errors 
									));
								}
								$callbackMsg->msgm=array_merge($callbackMsg->msgm, $reportsUpload->msg);
							}else
							{
								$this->load('Helpers\Alert', array(
									'danger',
									'Arquivos não enviados. Formatos permitidos são "pdf", "png", "jpeg","jpg"',
									//$reportsRegister->errors
								));
							}
						}
					}
					array_push($callbackMsg->msgm, 'Relatório de encerramento de projeto criado.');
					$this->load('Helpers\Alert', array(
							'success',
							'Sucesso',
							$callbackMsg->msgm
						));
				}
				else
				{
					$this->load('Helpers\Alert', array(
						'danger',
						'Não foi possível criar relatório. Verifique os motivos abaixo',
						$evt->errors
					));
				}
			}
		}else{
			$this->load('Helpers\Alert', array(
				'danger',
				'Não foi possível criar relatório. Verifique a sequência obrigatória!',
			));
		}
	}

	public function atualizarAction()
	{
		$callbackMsg = new \stdClass;
		$callbackMsg->errors=array();
		$callbackMsg->msgm = array();

		$this->view->setFile('index');
		$user_id = $this->auth->getUserId();
		$post = $this->request->post();

		$allowedExts = array("pdf", "png", "jpeg","jpg");
		$array_file = array("img_1", "img_2", "img_3");
		$num_file = 3;

		$str = explode(">> ", $post['projeto']);
	
		$projeto = $str[1];
		
		$evt = Closures::find_by_id($projeto);
		
		//passar o id do relatorio financeiro se tiver arquivo
		if (!empty($post)){

			//$is_set = isset($_FILES);
			//$is_empty = !empty($_FILES);

			$evt->data = $post['data'];

			$evt->save(false);
			
			for ($i = 0; $i < $num_file; $i++) 
			{
				$is_set = isset($_FILES[$array_file[$i]]);
				$is_empty = !empty($_FILES[$array_file[$i]]['tmp_name']);

				if ( $is_set && $is_empty) 
				{		
					$extension = pathinfo($_FILES[$array_file[$i]]['name'], PATHINFO_EXTENSION);

					if(in_array($extension, $allowedExts))
					{
						//deve passar o id do relatorio financieiro
						$reportsUpload = Closures::enviar($array_file[$i], $evt->id);

						if ($reportsUpload->status === false) 
						{								
							$this->load('Helpers\Alert', array(
								'error',
								'Arquivos não foram enviados.',
								$reportsUpload->errors 
							));
						}
						$callbackMsg->msgm=array_merge($callbackMsg->msgm, $reportsUpload->msg);
					}else
					{
						$this->load('Helpers\Alert', array(
							'danger',
							'Arquivos não enviados. Formatos permitidos são "pdf", "png", "jpeg","jpg"',
							//$reportsRegister->errors
						));
					}
				}
			}
			array_push($callbackMsg->msgm, 'Relatório atualizado.');
			$this->load('Helpers\Alert', array(
					'success',
					'Sucesso',
					$callbackMsg->msgm
				));
		}
	}

	public function cadastrarAction()
	{	
		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);

		//$institution = Institution::find($user_id);
		$institution = Institution::all();

		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$role->role
		);

		$this->view->setTitle('MIS - Administrativo')
			->setFile('index_novo')
			->setVars([
				'institution' => $institution,
				'institutions' => Institution::all()
			]);
	}

	public function mostrarnovoAction()
	{	
		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);

		$str = explode(">> ", $_GET['name_institution']);

		//$cnpj = 0;
		$cnpj = trim($str[1]);

		$projects = Projects::find_all_by_cnpj($cnpj);

		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$role->role
		);

		if(empty($projects)){
			$this->view->setTitle('MIS - Administrativo')
				->setFile('index_novo');

			$this->load('Helpers\Alert', array(
					'danger',
					'Nenhum relatório de projeto encontrado.'
				));

		}else{
			$this->view->setTitle('MIS - Administrativo')
				->setFile('index_novo')
				->setVars([
					'projects' => $projects
					//'evt' => $evt,
					//'evts' => Closures::all()
			]);
		}
	}

	public function mostraratualizarAction()
	{	
		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);

		$str = explode(">> ", $_GET['name_institution']);

		//$cnpj = 0;
		$cnpj = trim($str[1]);

		$projects = Closures::find_all_by_cnpj($cnpj);

		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$role->role
		);

		if(empty($projects)){
			$this->load('Helpers\Alert', array(
					'danger',
					'Nenhum relatório de evento de encerramento encontrado.'
				));

		}else{
			$this->view->setTitle('MIS - Administrativo')
				->setFile('index')
				->setVars([
					'projects' => $projects
					//'evt' => $evt,
					//'evts' => Closures::all()
			]);
		}
	}
}