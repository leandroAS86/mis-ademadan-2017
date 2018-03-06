<?php
class AplicacaoController extends \HXPHP\System\Controller
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
				'user'=>$user,
				'institution' => $institution,
				'institutions' => Institution::all()
			]);
	}

	public function registrarAction()
	{
		$callbackMsg = new \stdClass;
		$callbackMsg->msgm = array();
		
		$this->view->setFile('index');
		$user_id = $this->auth->getUserId();
		$post = $this->request->post();
		
		$str = explode(">> ", $post['nome_instituicao']);

		if(!in_array('CNPJ', $str))
		{
			return $this->load('Helpers\Alert', array(
					'danger',
					'Selecione uma Instituição Cadastrada.',
				));
		};

		$cnpj = trim($str[1]);

		$post['cnpj'] = $cnpj; 
		$post['nome_instituicao'] = $str[2];

		$reports = Reports::find_by_cnpj($cnpj);

		$institution = Institution::find_by_cnpj($cnpj);		

		$allowedExts = array("pdf", "png", "jpeg","jpg");
		$array_file = array('ata_apr_1', 'ata_apr_2' , 'rel_diag', 'ata_apr_3','img_apr_1','img_apr_2','img_apr_3');
		$num_file = 6;
		
		if (!empty($post))
		{			
			if (is_null($reports))
			{ 				
				$reportsRegister = Reports::registrar($user_id, $post);
				
				if($reportsRegister->status === true)
				{
					$institution->nivel = 2;
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

							if(in_array($extension, $allowedExts)){
								$reportsUpload = Reports::enviar($array_file[$i], $cnpj);
								if ($reportsUpload->status === false) {
									$this->load('Helpers\Alert', array(
										'error',
										'Arquivos não foram enviados.',
										$reportsUpload->errors 
									));
								}
								$reportsRegister->msg=array_merge($reportsRegister->msg, $reportsUpload->msg);

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
					$this->load('Helpers\Alert', array(
							'success',
							'Sucesso',
							$reportsRegister->msg
						));
				}else{
					$this->load('Helpers\Alert', array(
						'danger',
						'Não foi possível criar relatório. Verifique os motivos abaixo',
						$reportsRegister->errors
					));
				}
			} else{
				$reports->cnpj = $post['cnpj'];
				$reports->nome_instituicao = $post['nome_instituicao'];
				
				$reports->apl_validacao = $post['apl_validacao'];

				if(!empty($post['aplicacao1'])){
					$reports->aplicacao= $post['aplicacao1'];	
				}

				if(!empty($post['aplicacao2'])){
					$reports->aplicacao= $post['aplicacao2'];	
				}

				if(!empty($post['data_apr_1']))
				{
					$reports->data_apr_1 = $post['data_apr_1'];
				}

				if(!empty($post['data_apr_2']))
				{
					$reports->data_apr_2= $post['data_apr_2'];
				}

				if(!empty($post['data_apr_3']))
				{
					$reports->data_apr_3 = $post['data_apr_3'];
				}

				for ($i = 0; $i < $num_file; $i++) 
				{
					$is_set = isset($_FILES[$array_file[$i]]);
					$is_empty = !empty($_FILES[$array_file[$i]]['tmp_name']);

					if ( $is_set && $is_empty) 	
					{	
						$extension = pathinfo($_FILES[$array_file[$i]]['name'], PATHINFO_EXTENSION);

						if(in_array($extension, $allowedExts))
						{
							$reportsUpload = Reports::enviar($array_file[$i], trim($post['cnpj']));

							if ($reportsUpload->status === false) 
							{								
								$this->load('Helpers\Alert', array(
									'danger',
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

				$reports->save(false);
				array_push($callbackMsg->msgm, 'Relatório atualizado');
				$this->load('Helpers\Alert', array(
					'success',
					'Sucesso',
					$callbackMsg->msgm
				));
			}

		}
	}
}