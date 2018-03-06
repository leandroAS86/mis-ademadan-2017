<?php
class QuestionarioController extends \HXPHP\System\Controller
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

		//$institution = Institution::all();

		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$role->role
		);

		$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
			->setFile('index');
	}

	public function registrarAction()
	{
		$callbackMsg = new \stdClass;
		$callbackMsg->errors=array();
		$callbackMsg->msg=array();

		$this->view->setFile('index');
		$user_id = $this->auth->getUserId();
		$post = $this->request->post();

		$this->view->setFile('index');
		$user_id = $this->auth->getUserId();
		$post = $this->request->post();

		$reports = AdemadanReport::find_by_user_id($user_id);

		if(!is_null($reports)){
			//a cada atualização o status é alterado para nova aprovação
			$reports->status = 0;
			if (!empty($post)){

				if(!empty($post['questao_1'])) {
					$reports->questao_1 = $post['questao_1'];
				}

				if(!empty($post['questao_2'])) {
					$reports->questao_2 = $post['questao_2'];
				}

				if(!empty($post['questao_3'])) { 
					$reports->questao_3 = $post['questao_3'];
				}

				if(!empty($post['questao_4'])) {
					$reports->questao_4 = $post['questao_4'];
				}

				if(!empty($post['questao_5'])) {
					$reports->questao_5 = $post['questao_5'];
				}

				if(!empty($post['questao_6'])) {
					$reports->questao_6 = $post['questao_6'];
				}

				if(!empty($post['questao_7'])) {
					$reports->questao_7 = $post['questao_7'];
				}

				if($reports->save(false)){
					array_push($callbackMsg->msg, 'Relatório atualizado.');
					$this->load('Helpers\Alert', array(
						'success',
						'Açães realizadas com sucesso.',
						$callbackMsg->msg
					));
				}else{
					$this->load('Helpers\Alert', array(
						'error',
						$reports->errors
					));
				}
			}
		}else{
			$this->load('Helpers\Alert', array(
				'error',
				'Relatório não encontrado. Por favor contate o administrador. ',
				$reports->errors
			));
		}
	}
}