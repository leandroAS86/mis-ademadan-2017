<?php
class ComunicacaoController extends \HXPHP\System\Controller
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

		//$institution = Institution::find($user_id);
		$institution = Institution::all();
		//var_dump($institution);
		
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

		$str = explode(">> ", $post['name_institution']);

		$id = 0 + $str[1];
		$cnpj = trim($str[2]);
		$reports = Projects::find_by_id($id);

		$allowedExts = array("pdf", "pptx", "mp4", "avi", "wmv");
		$array_file = array('banner_projeto', 'video_projeto');
		$num_file = 2;	

		if (!is_null($reports))
		{ 
			if (!empty($post))
			{
				$is_url = filter_var($post['site_projeto'], FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED );
				
				if($is_url)
				{
					$reports->site_projeto = $post['site_projeto'];
					$reports->save(false);
					array_push($callbackMsg->msgm, 'Relatório atualizado.');
				}
				else{
					$this->load('Helpers\Alert', array(
						'danger',
						'URL inválida.',
					));
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
							$reportsUpload = Projects::enviar($array_file[$i], $id);

							if ($reportsUpload->status === false) {
									$this->load('Helpers\Alert', array(
									'danger',
									'Arquivos não foram enviados.',
									$reportsUpload->errors 
								));
							}

							$callbackMsg->msgm=array_merge($callbackMsg->msgm, $reportsUpload->msg);

						}else{
							$this->load('Helpers\Alert', array(
								'danger',
								'Arquivos não enviados. Formatos permitidos são "pdf", "pptx", "mp4", "avi", "wmv"',
							));
						}
					}
				}
				$this->load('Helpers\Alert', array(
						'success',
						'Sucesso',
						$callbackMsg->msgm
					));
			}
		}
		else{
		$this->load('Helpers\Alert', array(
				'danger',
				'Relatório não encontrado. Verifique se o relatório de monitoramento foi criado para esta instituição. Se tudo estiver correto contate o administrador.',
			));
		}
	}

	public function mostrarAction()
	{	
		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);

		//var_dump($_GET['name_institution']);

		$str = explode(">> ", $_GET['name_institution']);

		$cnpj = 0;
		$cnpj += $str[1];

		$projects = Projects::find_all_by_cnpj($cnpj);

		//var_dump($projects);

		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$role->role
		);

		if(empty($projects)){
			$this->load('Helpers\Alert', array(
					'danger',
					'Nenhum relatório de projeto encontrado.'
				));

		}else{
		$this->view->setTitle('MIS - Administrativo')
			->setFile('index')
			->setVars([
				'projects' => $projects,
				//'financials' => FinancialReports::all()
			]);}
	}
}