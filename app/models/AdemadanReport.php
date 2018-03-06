<?php

class AdemadanReport extends \HXPHP\System\Model
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

		$registrar = self::create(array(
			'ademadan_data_1'=>$post['ademadan_data_1'],
			'ademadan_data_2'=>$post['ademadan_data_2'],
			'ademadan_data_3'=>$post['ademadan_data_3'],
			'ademadan_data_4'=>$post['ademadan_data_4'],
			'ademadan_data_5'=>$post['ademadan_data_5'],
			'ademadan_data_6'=>$post['ademadan_data_6'],
			'ademadan_data_7'=>$post['ademadan_data_7'],
			'user_id'=>$user_id,
			'status' => 0
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

	public static function enviar($file_img, $user_id)
	{
		$reports = self::find_by_user_id($user_id);

		$callbackObj = new \stdClass;
		$callbackObj->status=false;
		$callbackObj->errors=array();
		$callbackObj->msg=array();

		$uploadUserImage = new upload($_FILES[$file_img]); 
		//var_dump($uploadUserImage);

		if ($uploadUserImage->uploaded) {
			$image_name = md5(uniqid());
			$uploadUserImage->file_new_name_body = $image_name;
			//$uploadUserImage->file_new_name_ext = 'pdf';
			$dir_path = ROOT_PATH.DS.'public'.DS.'uploads'.DS.'users'.DS.$user_id.DS;

			$uploadUserImage->process($dir_path);
			//var_dump($uploadUserImage);

			if ($uploadUserImage->processed) {
				$uploadUserImage->clean();
				if (!is_null($reports->$file_img)) {
					unlink($dir_path . $reports->$file_img);
				}

				$ext = $uploadUserImage->file_src_name_ext;
	
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