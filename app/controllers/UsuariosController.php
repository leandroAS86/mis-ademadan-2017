<?php 
class UsuariosController extends \HXPHP\System\Controller
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
		$this->auth->roleCheck(array(
			'administrator'
		));
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
					->setFile('index')
					->setVars([
						'user' => $user,
						'users' => User::all()
					]);
	}

	public function roleAction($user_id = null){
		$post = $this->request->post();	
		$user = User::find_by_email($post['email']);
		if (!is_null($user)) {
			if ($post['role'] == 'adm'){
				//var_dump($post);
				$user->role_id = 1;
				if ($user->status === 0){
					$user->status = 1;
				}
				else {
					$user->status = 0;
				}
			}
			if ($post['role'] == 'user'){
				$user->role_id = 2;
				if ($user->status === 0){
					$user->status = 1;
				}
				else {
					$user->status = 0;
				}
			}
			if ($post['role'] == 'inst'){
				$user->role_id = 3;
				if ($user->status === 0){
					$user->status = 1;
				}
				else {
					$user->status = 0;
				}
			}	
			
			$user->save(false);
			$this->view->setVar('users', User::all());
		}
	}
	
	public function bloquearAction($user_id = null)
	{
		if (is_numeric($user_id)) {
			$user = User::find_by_id($user_id);
			if (!is_null($user)) {
				$user->status = 0;
				$user->save(false);
				$this->view->setVar('users', User::all());
			}
		}
	}
	public function desbloquearAction($user_id = null)
	{
		if (is_numeric($user_id)) {
			$user = User::find_by_id($user_id);
			if (!is_null($user)) {
				$user->status = 1;
				$user->save(false);
				$this->view->setVar('users', User::all());
			}
		}
	}
	public function excluirAction($user_id = null)
	{
		if (is_numeric($user_id)) {
			$user = User::find_by_id($user_id);
			if (!is_null($user)) {
				$user->delete();
				$this->view->setVar('users', User::all());
			}
		}
	}
}