<?php
class HomeController extends \HXPHP\System\Controller
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

		$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
			->setVar('user',  $user);
	}

	public function bloqueadaAction()
	{
		$this->auth->roleCheck(array(
			'administrator',
			'user',
		));
	}

}