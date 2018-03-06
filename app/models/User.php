<?php

class User extends \HXPHP\System\Model
{
	static $belongs_to=array(
		array('role')
	);
	
	static $validates_presence_of = array(
		
		array(
			'email', 
			'message' => 'O campo e-mail é obrigatório'
		),
		
		
		array(
			'nome', 
			'message' => 'O campo nome é obrigatório'
		),

		array(
			'username', 
			'message' => 'O campo nome de usuário é obrigatório'
		),
		array(
			'password', 
			'message' => 'O campo senha é obrigatório'
		)
	);

	static $validates_uniqueness_of = array(
 	    	array(
 	    		'username',
 	    		'message' => 'Usuário com mesmo nome de usuário ja cadastrado.'
 	    	),
 	    	array(
 	    		'email',
 	    		'message' => 'Usuário com mesmo e-mail ja cadastrado.'
 	    	)
 		);

	public static function cadastrar(array $post)
	{	
		$callbackObj = new \stdClass;
		$callbackObj->user=null;
		$callbackObj->status=false;
		$callbackObj->errors=array();
		$role = Role::find_by_role('user');
		//$cadastrar = self::create($post);

		if(is_null($role))
		{
			array_push($callbackObj->errors, 'A Role não existe. Contate o Administrador');
			//var_dump($cadastrar->errors->get_raw_errors());
			return $callbackObj;

		}

		$user_data = array(
			'role_id'=> $role->id, 
			'status' => 0
		);
		$password = \HXPHP\System\Tools::hashHX($post['password']);
		$post = array_merge($post, $user_data, $password);

		$cadastrar = self::create($post); 

		if($cadastrar->is_valid()){
			$callbackObj->user=$cadastrar;
			$callbackObj->status=true;
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();
			
		foreach ($errors as $field=>$message )
		{
			array_push($callbackObj->errors, $message[0]);
		} 

		return $callbackObj;
		//var_dump($cadastrar->errors->get_raw_errors());
	}

	public static function atualizar($user_id, array $post)
	{
		$callbackObj = new \stdClass;
		$callbackObj->user = null;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		if (isset($post['password']) && !empty($post['password'])) {
			$password = \HXPHP\System\Tools::hashHX($post['password']);
			$post = array_merge($post, $password);
		}

		$user = self::find($user_id);

		$user->name = $post['name'];
		$user->email = $post['email'];
		$user->username = $post['username'];

		if (isset($post['salt'])) {
			$user->password = $post['password'];
			$user->salt = $post['salt'];
		}

		$exists_mail = self::find_by_email($post['email']);
		if (!is_null($exists_mail) && intval($user_id) !== intval($exists_mail->id)) {
			array_push($callbackObj->errors, 'Oops! Já existe um usuário com este e-mail cadastrado. Por favor, escolha outro e tente novamente');
			return $callbackObj;
		}

		$exists_username = self::find_by_username($post['username']);
		if (!is_null($exists_username) && intval($user_id) !== intval($exists_username->id)) {
			array_push($callbackObj->errors, 'Oops! Já existe um usuário com o login <strong>' . $post['username'] . '</strong> cadastrado. Por favor, escolha outro nome de usuário e tente novamente');
			return $callbackObj;
		}

		$atualizar = $user->save(false);
		if ($atualizar) {
			$callbackObj->user = $user;
			$callbackObj->status = true;
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();
		
		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}
		return $callbackObj;
	}

	public static function login(array $post)
	{
		$callbackObj = new \stdClass;
		$callbackObj->user=null;
		$callbackObj->status=false;
		$callbackObj->code=null; 
		$callbackObj->tentativas_restantes=null;

		$user = self::find_by_username($post['username']);

		if(!is_null($user)){
			$password = \HXPHP\System\Tools::hashHX($post['password'], $user->salt);

			//Verifica a quantidade maxima de tentativas
			if($user->status === 1){
				if($password['password'] === $user->password){	
					//var_dump('logado');
					$callbackObj->user=$user; 
					$callbackObj->status=true;
				}
				else{
					$callbackObj->code='dados-incorretos';	
				}
			}
			else{
				$callbackObj->code='usuario-bloqueado';
			}
		}
		else{
			$callbackObj->code='usuario-inexistente';
		}
		return $callbackObj;
	}
}