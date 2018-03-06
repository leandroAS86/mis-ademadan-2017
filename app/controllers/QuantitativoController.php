<?php
class QuantitativoController extends \HXPHP\System\Controller
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
		$callbackMsg->errors=array();
		$callbackMsg->msg=array();

		$this->view->setFile('index');
		$user_id = $this->auth->getUserId();
		$post = $this->request->post();	

		$str = explode(">> ", $post['name_institution']);

		$id = 0 + $str[1];
		$cnpj = trim($str[2]);

		$projects = Projects::find_by_id($id);

		if(!is_null($projects)){
			if (!empty($post)){
				$projects->p_contempladas_diretamente = $post['p_contempladas_diretamente'];
				$projects->p_contempladas_indiretamente = $post['p_contempladas_indiretamente'];
				if($projects->save(false)){
					array_push($callbackMsg->msg, 'Relatório atualizado.');
					$this->load('Helpers\Alert', array(
						'success',
						'Sucesso.',
						$callbackMsg->msg
					));
				}else{
					//var_dump($projects);
					$this->load('Helpers\Alert', array(
						'danger',
						$projects->errors
					));
				}
			}
		}
		else{
			//var_dump($projects);
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
		$cnpj = trim($str[1]);

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