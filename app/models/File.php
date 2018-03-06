<?php
class File extends \HXPHP\System\Model
{
	public static function enviar($file_img, $cnpj)
	{
		//$reports = self::find_by_cnpj($cnpj);

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