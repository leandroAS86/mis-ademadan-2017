<?php

class CadastroController extends \HXPHP\System\Controller
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

		$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
			->setFile('index');
			
		$this->auth->redirectCheck(true);
	}

	public function cadastrarAction()
	{
		$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
			->setFile('index');

		$this->request->setCustomFilters(array(
			'email'=>FILTER_VALIDATE_EMAIL
		));

		$post = $this->request->post();

		if (!empty($post)){
			$cadastrarUsuario = User::cadastrar($post);	
			if($cadastrarUsuario->status===false){
				$this->load('Helpers\Alert', array(
					'danger',
					'Cadastro não realizado. Verifiqe os motivos abaixo',
					$cadastrarUsuario->errors
				));
			}
			else{
					$this->load('Helpers\Alert', array(
						'success',
						'Cadastro realizadoizado com sucesso. Por favor aguarde a liberação do seu cadastro para fazer loguin no sistema.'
					));
			}
		}
	}
}	