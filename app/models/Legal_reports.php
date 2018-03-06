<?php

class Legal_reports extends \HXPHP\System\Model
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

		$cadastrar = self::create(array(
			'name_institution'=>$post['name_institution'],
			'cnpj'=>$post['cnpj'],
			'user_id'=>$user_id
		));

		if($cadastrar->is_valid()){
			$callbackObj->reports=$cadastrar;
			$callbackObj->status=true;
			array_push($callbackObj->msg, 'Relatório jurídico foi criado.');
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();

		foreach ($errors as $field=>$message ){
			array_push($callbackObj->errors, $message[0]);
		} 
		return $callbackObj;	
	}

	public static function enviar($file_img, $cnpj)
	{
		$legal = self::find_by_cnpj($cnpj);

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
			//var_dump($dir_path);

			$uploadUserImage->process($dir_path);
			//var_dump($uploadUserImage);

			if ($uploadUserImage->processed) {
				$uploadUserImage->clean();
				
				if (!is_null($legal->$file_img)) {
					unlink($dir_path . $legal->$file_img);
				}

				$ext = $uploadUserImage->file_src_name_ext;
				//var_dump($ext);

				$legal->$file_img = $image_name.'.'.$ext;
				
				if($legal->save(false)){
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
		}else 
		{
			array_push($callbackObj->errors, $uploadUserImage->error);
			return $callbackObj;
		}
	}
}