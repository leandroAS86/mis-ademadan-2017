<?php
class InstituicaoController extends \HXPHP\System\Controller
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
		$callbackMsg->msgm = array();	

		$this->view->setFile('index');
		$user_id = $this->auth->getUserId();
		$post = $this->request->post();

		if (!empty($post)){

			$cad_instituicao = Institution::registrar($user_id, $post);
			
			if($cad_instituicao->status===true)
			{
				$this->load('Helpers\Alert', array(
				'success',
				'Sucesso:',
				$cad_instituicao->msg
			));
			}else{
				$this->load('Helpers\Alert', array(
					'danger',
					'Cadastro da instituição não foi realizado. Verifiqe os motivos abaixo:',
					$cad_instituicao->errors
				));
			}
		}
	}
}