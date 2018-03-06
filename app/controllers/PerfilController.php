<?php 
class PerfilController extends \HXPHP\System\Controller
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
		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$this->auth->getUserRole()
		);
		$user_id = $this->auth->getUserId();
		$this->view->setTitle('HXPHP - Editar perfil')
					->setVar('user', User::find($user_id));
	}
	public function editarAction()
	{
		$this->view->setFile('editar');
		$user_id = $this->auth->getUserId();
		$this->request->setCustomFilters(array(
			'email' => FILTER_VALIDATE_EMAIL
		));
		$post = $this->request->post();
		if (!empty($post)) {
			$atualizarUsuario = User::atualizar($user_id, $post);
			if ($atualizarUsuario->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Ops! Não foi possível atualizar seu perfil. <br> Verifique os erros abaixo:',
					$atualizarUsuario->errors
				));
			}
			else {
				if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {
					$uploadUserImage = new upload($_FILES['image']);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = 'png';
						$uploadUserImage->resize = true;
						$uploadUserImage->image_x = 500;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'users' . DS . $atualizarUsuario->user->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed) {
							$uploadUserImage->clean();
							$this->load('Helpers\Alert', array(
								'success',
								'Uhuul! Perfil atualizado com sucesso!'
							));
							if (!is_null($atualizarUsuario->user->image)) {
								unlink($dir_path . $atualizarUsuario->user->image);
							}
							$atualizarUsuario->user->image = $image_name . '.png';
							$atualizarUsuario->user->save(false);
						}
						else {
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível atualizar a sua imagem de perfil',
								$uploadUserImage->error
							));
						}
					}
				}
				else {
					$this->load('Helpers\Alert', array(
						'success',
						'Uhuul! Perfil atualizado com sucesso!'
					));
				}
				$this->view->setVar('user', $atualizarUsuario->user);
				
			}
		}
	}
}