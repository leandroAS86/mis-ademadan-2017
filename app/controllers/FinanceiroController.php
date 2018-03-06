<?php
class FinanceiroController extends \HXPHP\System\Controller
{
	static $validates_presence_of = array(
		array(
			'acao', 
			'message' => 'O campo Ação é obrigatório'
		),
		array(
			'desembolso_data', 
			'message' => 'O campo Data é obrigatório'
		),
		array(
			'desembolso_valor', 
			'message' => 'O campo Valor é obrigatório'
		)
	);

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
			->setFile('index')
			->setVars([
				'institution' => $institution,
				'institutions' => Institution::all()
			]);

		$this->view->setTitle('MAIA - Administrativo')
			->setVar('user',  $user);
	}

	public function registrarAction()
	{
		$callbackMsg = new \stdClass;
		$callbackMsg->errors=array();
		$callbackMsg->msgm = array();

		$allowedExts = array("pdf", "png", "jpeg","jpg");
		$array_file = array('rel_financeiro');
		$num_file = 1;

		$this->view->setFile('index');
		$user_id = $this->auth->getUserId();
		$post = $this->request->post();

		$str = explode(">> ", $post['name_institution']);
		//$cnpj = 0;
		$cnpj = trim($str[1]);

		$institution = Institution::find_by_cnpj($cnpj);
		//if($institution->nivel >= 7)
		//{
			if (!empty($post))
			{
				$post['cnpj'] = $cnpj; 
				$post['name_institution'] = $str[2];

				$financial = FinancialReports::registrar($user_id, $post);
				if($financial->status === true)
				{
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
								$reportsUpload = FinancialReports::enviar($array_file[$i], $financial->reports->id);

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
					array_push($callbackMsg->msgm, 'Relatório financeiro criado.');
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
						$financial->errors
					));
				}
			}
	}

	public function atualizarAction()
	{
		$callbackMsg = new \stdClass;
		$callbackMsg->errors=array();
		$callbackMsg->msgm = array();

		$allowedExts = array("pdf", "png", "jpeg","jpg");
		$array_file = array('rel_financeiro');
		$num_file = 1;

		$this->view->setFile('index');
		$user_id = $this->auth->getUserId();
		$post = $this->request->post();

		$str = explode(">> ", $post['name_institution']);

		$id = 0 + $str[1];
		$cnpj = trim($str[2]);

		$financial = FinancialReports::find_by_id($id);

		

		//passar o id do relatorio financeiro se tiver arquivo
		if (!empty($post)){

			$financial->acao = $post['acao'];
			$financial->desembolso_valor = $post['desembolso_valor'];
			$financial->desembolso_data = $post['desembolso_data'];

			//$is_set = isset($_FILES);
			//$is_empty = !empty($_FILES);

			$financial->save(false);
			
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
						$reportsUpload = FinancialReports::enviar($array_file[$i], $id);

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

		$financial = FinancialReports::find_all_by_cnpj($cnpj);

		//var_dump($financial);

		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$role->role
		);

		if(empty($financial)){
			$this->load('Helpers\Alert', array(
					'danger',
					'Nenhum relatório encontrado.'
				));

		}else{
		$this->view->setTitle('MIS - Administrativo')
			->setFile('index')
			->setVars([
				'financial' => $financial,
				//'financials' => FinancialReports::all()
			]);}
	}
}