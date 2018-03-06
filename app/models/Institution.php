<?php

class Institution extends \HXPHP\System\Model
{
	static $belongs_to = array(
		array('user')
	);

	static $validates_uniqueness_of = array(
 	    	array(
 	    		'name_institution',
 	    		'message' => 'Instituição com mesmo nome ja cadastrado.'
 	    	),
 	    	array(
 	    		'cnpj',
 	    		'message' => 'CNPJ ja cadastrado.'
 	    	)
 		);

	public static function registrar($user_id, array $post)
	{
		$callbackObj = new \stdClass;
		$callbackObj->reports=null;
		$callbackObj->status=false;
		$callbackObj->errors=array();
		$callbackObj->msg=array();

		//$user_exists = User::find_by_id($user_id);
		$nivel = array(
			'nivel' => 0,
			'status' => 0,
			'situation' => 0
		);
		$post = array_merge($post, $nivel);

		$exists_cnpj = self::find_by_cnpj($post['cnpj']);

		if (!is_null($exists_cnpj)) {
			array_push($callbackObj->errors, 'Já existe uma instituição com esse CNPJ cadastrado.');
			return $callbackObj;
		}

		$exists_nome = self::find_by_name_institution($post['name_institution']);
		
		if (!is_null($exists_nome)) {
			array_push($callbackObj->errors, 'Já existe uma instituição com esse NOME cadastrado.');
			return $callbackObj;
		}

		$cadastrar = self::create(array(
			'name_institution'=>$post['name_institution'],
			'cnpj'=>$post['cnpj'],
			'user_id'=>$user_id,
			'nivel'=>$post['nivel'],
			'status' =>$post['status'],
			'situation' =>$post['situation']
		));

		if($cadastrar->is_valid()){
			//var_dump($cadastrar);
			$callbackObj->reports=$cadastrar;
			$callbackObj->status=true;
			array_push($callbackObj->msg, 'Instituição cadastrada.');
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();

		foreach ($errors as $field=>$message ){
			array_push($callbackObj->errors, $message[0]);
		} 

		return $callbackObj;	
	}
}