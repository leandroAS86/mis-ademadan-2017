<?php
class JuridicoController extends \HXPHP\System\Controller
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
		$this->auth->redirectCheck();
	
		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);

		$institution = Institution::all();
		//$institution = Institution::find($user_id);
		//$report = Reports::find_by_user_id($user_id);
		
		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$role->role
		);

		$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
			->setFile('index')
			->setVars([
				'user' => $user,
				'users' => User::all(),
				'institution' => $institution,
				'institutions' => Institution::all()
				//'report' => $report,
				//'reports' => Reports::all()
			]);

		$this->view->setTitle('MAIA - Administrativo')
			->setVar('user',  $user);
	}

	public function registrarAction()
	{
		$callbackMsg = new \stdClass;
		$callbackMsg->msgm = array();
		
		$this->view->setFile('index');
		$user_id = $this->auth->getUserId();
		$post = $this->request->post();	
		//var_dump($post);
		//var_dump($user_id);


		$allowedExts = array("pdf", "png", "jpeg","jpg");
		$array_file = array('ata', 'estatuto');
		$num_file = 2;	

		$str = explode(">> ", $post['name_institution']);
		
		//$cnpj = '';
		$cnpj = trim($str[1]);
		//var_dump($cnpj);

		$post['cnpj'] = $cnpj; 
		$post['name_institution'] = $str[2];

		$legal = Legal_reports::find_by_cnpj($cnpj);

		$institution = Institution::find_by_cnpj($cnpj);
		
		if($institution->nivel >= 2){
			if (!empty($post))
			{
				if (is_null($legal))
				{ 
					$legalRegister = Legal_reports::registrar($user_id, $post);
					if($legalRegister->status === true)
					{
						$institution->nivel = 3;
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
									$reportsUpload = Legal_reports::enviar($array_file[$i], trim($post['cnpj']));

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
						array_push($callbackMsg->msgm, 'Relatório jurídico criado.');
						$this->load('Helpers\Alert', array(
								'success',
								'Sucesso',
								$callbackMsg->msgm
							));
					}else
					{
						$this->load('Helpers\Alert', array(
							'danger',
							'Não foi possível criar relatório. Verifique os motivos abaixo',
							$legalRegister->errors
						));
					}
				}else
				{
					$legal->cnpj = $post['cnpj'];
					$legal->name_institution = $post['name_institution'];

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
								$reportsUpload = Legal_reports::enviar($array_file[$i], trim($post['cnpj']));

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

					$legal->save(false);
					array_push($callbackMsg->msgm, 'Relatório jurídico atualizado');
					$this->load('Helpers\Alert', array(
						'success',
						'Sucesso',
						$callbackMsg->msgm
					));
				}
			}
		}else{
			$this->load('Helpers\Alert', array(
				'danger',
				'Não foi possível criar relatório. Verifique a sequência obrigatoria!',
				//$reportsRegister->errors
			));
		}			
	}
}