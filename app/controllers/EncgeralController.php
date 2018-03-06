<?php
class EncgeralController extends \HXPHP\System\Controller
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

		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$role->role
		);


		$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
			->setVars([
				'user' => $user,
				'users' => User::all(),
				'institution' => $institution,
				'institutions' => Institution::all()
				//'report' => $report,
				//'reports' => Reports::all()
			]);
	}

	public function registrarAction()
	{

		$callbackMsg = new \stdClass;
		$callbackMsg->msgm = array();
		$callbackMsg->err = array();
		
		$this->view->setFile('index');
		$user_id = $this->auth->getUserId();
		$post = $this->request->post();	
		//var_dump($post);
		//var_dump($user_id);


		$allowedExts = array("pdf", "png", "jpeg","jpg");
		$array_file = array('evt_enc_geral_img_1', 'evt_enc_geral_img_2', 'evt_enc_geral_img_3');
		$num_file = 3;	

		$str = explode(">> ", $post['name_institution']);

		//$cnpj = 0;
		$cnpj = trim($str[1]);

		$reports = Reports::find_by_cnpj($cnpj);

		if (!is_null($reports))
		{
			if (!empty($post))
			{
				$reports->evt_enc_geral_data = $post['evt_enc_geral_data'];
				$reports->participacao_com = $post['participacao_com'];

				$reports->save(false);

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
		}else{
			$this->load('Helpers\Alert', array(
				'error',
				'Relatorio não encontrado.',
				$reports->errors 
			));
		}		
	}
}