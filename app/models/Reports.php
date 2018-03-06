<?php

class Reports extends \HXPHP\System\Model
{
	static $belongs_to = array(
		array('user')
	);

	public static function registrar($user_id, array $post)
	{	
		$callbackObj = new \stdClass;
		$callbackObj->reports=null;
		$callbackObj->status=false;
		$callbackObj->errors=array();
		$callbackObj->msg=array();

		if(!empty($post['aplicacao1'])){
			$apl = $post['aplicacao1'];	
		}

		if(!empty($post['aplicacao2'])){
			$apl = $post['aplicacao2'];	
		}

		$registrar = self::create(array(
			'cnpj' =>$post['cnpj'],
			'nome_instituicao'=>$post['nome_instituicao'],
			'aplicacao'=>$apl,
			'apl_validacao'=>$post['apl_validacao'],
			'data_apr_1'=>$post['data_apr_1'],
			'data_apr_2'=>$post['data_apr_2'],
			'data_apr_3'=>$post['data_apr_3'],
			'status'=>0,
			'livre'=>0,
			'nivel'=>2,
			'user_id'=>$user_id
		));

		if($registrar->is_valid())
		{
			$callbackObj->reports=$registrar;
			$callbackObj->status=true;
			array_push($callbackObj->msg, 'O Relatório foi criado.');
			return $callbackObj;
		}

		$errors = $registrar->errors->get_raw_errors();

		foreach ($errors as $field=>$message )
		{
			array_push($callbackObj->errors, $message[0]);
		} 

		return $callbackObj;	
	}

	public static function enviar($file_img, $cnpj)
	{
		$reports = self::find_by_cnpj($cnpj);
		//var_dump($reports);
		//var_dump($cnpj);

		$callbackObj = new \stdClass;
		$callbackObj->status=false;
		$callbackObj->errors=array();
		$callbackObj->msg=array();

		//var_dump($_FILES[$file_img]);

		$uploadUserImage = new upload($_FILES[$file_img]); 
		//var_dump($uploadUserImage);

		if ($uploadUserImage->uploaded) {
			$image_name = md5(uniqid());
			$uploadUserImage->file_new_name_body = $image_name;
			//$uploadUserImage->file_new_name_ext = 'pdf';
			$dir_path = ROOT_PATH.DS.'public'.DS.'uploads'.DS.'users'.DS.$cnpj.DS;

			$uploadUserImage->process($dir_path);
			//var_dump($uploadUserImage);

			if ($uploadUserImage->processed) {
				$uploadUserImage->clean();
				//var_dump($reports);
				if (!is_null($reports->$file_img)) {
					unlink($dir_path . $reports->$file_img);
				}

				$ext = $uploadUserImage->file_src_name_ext;
				//var_dump($ext);

				$reports->$file_img = $image_name.'.'.$ext;
				
				if($reports->save(false)){
					array_push($callbackObj->msg, 'Arquivo enviado.');
					$callbackObj->status=true;

					//array_push($callbackObj->errors, $uploadUserImage->error);
					return $callbackObj;
				}
			}
			else {
				array_push($callbackObj->errors, $uploadUserImage->error);
				return $callbackObj;
			}
		}else {
				array_push($callbackObj->errors, $uploadUserImage->error);
				return $callbackObj;
			}
	}
}

