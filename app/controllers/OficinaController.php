<?php
class OficinaController extends \HXPHP\System\Controller
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
		$array_file = array("ata", "img_1", "img_2", "img_3");
		$num_file = 4;

		$str = explode(">> ", $post['name_institution']);
		//$cnpj = 0;
		$cnpj = trim($str[1]);

		$post['cnpj'] = $cnpj; 
		$post['name_institution'] = $str[2];

		$institution = Institution::find_by_cnpj($cnpj);
	
		if($institution->nivel >= 3)
		{
			if (!empty($post))
			{
				$evt = WorkshopHearings::registrar($user_id, $post);
				//var_dump($evt);
				if($evt->status === true)
				{
					$institution->nivel = 4;
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
								$reportsUpload = WorkshopHearings::enviar($array_file[$i], $evt->reports->id);

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
					array_push($callbackMsg->msgm, 'Relatório de oficina criado.');
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
				'alerts',
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
		$array_file = array("ata", "img_1", "img_2", "img_3");
		$num_file = 4;

		$str = explode(">> ", $post['name_institution']);

		$id = 0 + $str[1];
		$cnpj = trim($str[2]);

		$post['cnpj'] = $cnpj; 
		$post['name_institution'] = $str[2];

		$evt = WorkshopHearings::find_by_id($id);

		$evt->data = $post['data'];
		
		//passar o id do relatorio financeiro se tiver arquivo
		if (!empty($post)){

			//$is_set = isset($_FILES);
			//$is_empty = !empty($_FILES);

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
						$reportsUpload = WorkshopHearings::enviar($array_file[$i], $id);

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

	public function mostrarAction()
	{	
		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);

		//var_dump($_GET['name_institution']);

		$str = explode(">> ", $_GET['name_institution']);

		//$cnpj = 0;
		$cnpj = trim($str[1]);

		$evt = WorkshopHearings::find_all_by_cnpj($cnpj);

		//var_dump($evt);

		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$role->role
		);

		if(empty($evt)){
			$this->load('Helpers\Alert', array(
					'danger',
					'Nenhum relatório de oficina encontrado.'
				));

		}else{
		$this->view->setTitle('MIS - Administrativo')
			->setFile('index')
			->setVars([
				'audiencia' => $evt,
				//'evts' => WorkshopHearings::all()
			]);}
	}
}