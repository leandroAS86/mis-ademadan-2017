<?php
class ComademadanController extends \HXPHP\System\Controller
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
		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$role->role
		);

		//$institution = Institution::all();

		$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
			->setVars([
				'user' => $user,
				'users' => User::all(),
				//'institution' => $institution,
				//'institutions' => Institution::all()
				//'report' => $report,
				//'reports' => Reports::all()
			]);
	}

	public function registrarAction()
	{
		$callbackMsg = new \stdClass;
		$callbackMsg->msgm = array();
		
		$this->view->setFile('index');
		$user_id = $this->auth->getUserId();
		$post = $this->request->post();	

		$reports = AdemadanReport::find_by_user_id($user_id);

		$allowedExts = array("pdf", "png", "jpeg","jpg");
		$array_file = array('ademadan_img_1', 'ademadan_img_2' , 'ademadan_img_3','ademadan_img_4','ademadan_img_5','ademadan_img_6', 'ademadan_img_7');
		$num_file = 7;
		
		if (!empty($post))
		{			
			if (is_null($reports))
			{ 
				$reportsRegister = AdemadanReport::registrar($user_id, $post);

				if($reportsRegister->status === true)
				{
					for ($i = 0; $i < $num_file; $i++) 
					{
						$is_set = isset($_FILES[$array_file[$i]]);
						$is_empty = !empty($_FILES[$array_file[$i]]['tmp_name']);

						if ( $is_set && $is_empty) 	
						{
							$extension = pathinfo($_FILES[$array_file[$i]]['name'], PATHINFO_EXTENSION);

							if(in_array($extension, $allowedExts)){
								$reportsUpload = AdemadanReport::enviar($array_file[$i], $user_id);
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
			}else
			{
				if(!empty($post['ademadan_data_1']))
				{
					$reports->ademadan_data_1 = $post['ademadan_data_1'];
				}
				if(!empty($post['ademadan_data_2']))
				{
					$reports->ademadan_data_2 = $post['ademadan_data_2'];
				}
				if(!empty($post['ademadan_data_3']))
				{
					$reports->ademadan_data_3 = $post['ademadan_data_3'];
				}
				if(!empty($post['ademadan_data_4']))
				{
					$reports->ademadan_data_4 = $post['ademadan_data_4'];
				}
				if(!empty($post['ademadan_data_5']))
				{
					$reports->ademadan_data_5 = $post['ademadan_data_5'];
				}
				if(!empty($post['ademadan_data_6']))
				{
					$reports->ademadan_data_6 = $post['ademadan_data_6'];
				}
				if(!empty($post['ademadan_data_7']))
				{
					$reports->ademadan_data_7 = $post['ademadan_data_7'];
				}

				//a cada atualização o status é alterado para nova aprovação
				$reports->status = 0;

				$reports->save(false);

				for ($i = 0; $i < $num_file; $i++) 
				{
					$is_set = isset($_FILES[$array_file[$i]]);
					$is_empty = !empty($_FILES[$array_file[$i]]['tmp_name']);

					if ( $is_set && $is_empty) 
					{
						$extension = pathinfo($_FILES[$array_file[$i]]['name'], PATHINFO_EXTENSION);

						if(in_array($extension, $allowedExts)){
							$reportsUpload = AdemadanReport::enviar($array_file[$i], $user_id);
							if ($reportsUpload->status === false) {
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
				array_push($callbackMsg->msgm, 'Relatório atualizado.');
				$this->load('Helpers\Alert', array(
						'success',
						'Sucesso',
						$callbackMsg->msgm
					));
			}
		}
	}
}