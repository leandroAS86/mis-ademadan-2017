<?php
class LoginAttempts extends \HXPHP\System\Model
{
	public static function totalTentativas($user_id)
	{
		return count(self::find_all_by_user_id($user_id));
	}

	public static function tentativasRestantes($user_id)
	{
		return intval(5-self::totalTentativas($user_id));
	}

	public static function registrarTentativa($user_id){
		self::create(array(
			'user_id'=>$user_id
		)); 
	}

	public static function limpartentativas($user_id)
	{
		self::delete_all(array(
			'conditions'=>array(
				'user_id = ?',
				$user_id
			)
		));
	}

	public static function existemTentativas($user_id)
	{
		return self::totalTentativas($user_id) < 5 ? true : false;
	}
}