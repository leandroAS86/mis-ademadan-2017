<?php
class RelatorioController extends \HXPHP\System\Controller
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
		//$report = Reports::find_by_user_id($user_id);
		$ademadanreport = AdemadanReport::find_by_user_id($user_id);
		
		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$role->role
		);
		$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
			->setFile('index')
			->setVars([
				//'rep' => Reports::all(),
				'reports' => Institution::all(),
				'ademadanreport'=> AdemadanReport::all()
			]);
	}

	public function aprovarAction($cnpj = null)
	{
		//var_dump($cnpj);
		//exit();
		if (is_numeric($cnpj)) {
			$report = Institution::find_by_cnpj($cnpj);
			//var_dump($report);
			if (!is_null($report)) {
				$report->status = 1;
				$report->save(false);
				$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
				->setFile('index')
				->setVars([
					//'rep' => Reports::all(),
					'reports' => Institution::all(),
					'ademadanreport'=> AdemadanReport::all()
				]);
			}
		}
	}
	
	public function reprovarAction($cnpj = null)
	{
		if (is_numeric($cnpj)) {
			$report = Institution::find_by_cnpj($cnpj);
			//var_dump($report);
			if (!is_null($report)) {
				$report->status = 0;
				$report->save(false);
				$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
				->setFile('index')
				->setVars([
					//'rep' => Reports::all(),
					'reports' => Institution::all(),
					'ademadanreport'=> AdemadanReport::all()
				]);
			}
		}
	}

		public function abrirAction($cnpj = null)
	{
		//var_dump($cnpj);
		//exit();
		if (is_numeric($cnpj)) {
			$report = Institution::find_by_cnpj($cnpj);
			//var_dump($report);
			if (!is_null($report)) {
				$report->situation = 1;
				$report->save(false);
				$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
				->setFile('index')
				->setVars([
					//'rep' => Reports::all(),
					'reports' => Institution::all(),
					'ademadanreport'=> AdemadanReport::all()
				]);
			}
		}
	}
	
	public function fecharAction($cnpj = null)
	{
		if (is_numeric($cnpj)) {
			$report = Institution::find_by_cnpj($cnpj);
			//var_dump($report);
			if (!is_null($report)) {
				$report->situation = 0;
				$report->save(false);
				$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
				->setFile('index')
				->setVars([
					//'rep' => Reports::all(),
					'reports' => Institution::all(),
					'ademadanreport'=> AdemadanReport::all()
				]);
			}
		}
	}

	public function excluirAction($cnpj = null)
	{
		if (is_numeric($cnpj)) {
			//$report = AdemadanReport::find_by_cnpj($cnpj);

			$institution = Institution::find_by_cnpj($cnpj);

			if(!is_null($institution)){
				$institution->delete();
			}

			$report = Reports::find_by_cnpj($cnpj);
			if(!is_null($report)){
				$report->delete();
			}

			$legal_reports = Legal_reports::find_by_cnpj($cnpj);
			if(!is_null($legal_reports)){
				$legal_reports->delete();
			}

			WorkshopHearings::delete_all(array('conditions' => array('cnpj = ?', $cnpj)));
			ApresentationHearings::delete_all(array('conditions' => array('cnpj = ?', $cnpj)));
			DefinitionHearings::delete_all(array('conditions' => array('cnpj = ?', $cnpj)));
			Closures::delete_all(array('conditions' => array('cnpj = ?', $cnpj)));
			FinancialReports::delete_all(array('conditions' => array('cnpj = ?', $cnpj)));
			Projects::delete_all(array('conditions' => array('cnpj = ?', $cnpj)));
			Projects::delete_all(array('conditions' => array('cnpj = ?', $cnpj)));			

			$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
				->setFile('index')
				->setVars([
					//'rep' => Reports::all(),
					'reports' => Institution::all(),
					'ademadanreport'=> AdemadanReport::all()
				]);
		}
	}

	public function aprovarademadanAction($id = null)
	{
		if (is_numeric($id)) {
			$report = AdemadanReport::find_by_id($id);
			if (!is_null($report)) {
				$report->status = 1;
				$report->save(false);
				$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
				->setFile('index')
				->setVars([
					'reports' => Reports::all(),
					'ademadanreport'=> AdemadanReport::all()
				]);
			}
		}
	}
	public function reprovarademadanAction($id = null)
	{
		if (is_numeric($id)) {
			$report = AdemadanReport::find_by_id($id);
			if (!is_null($report)) {
				$report->status = 0;
				$report->save(false);
				$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
				->setFile('index')
				->setVars([
					'reports' => Reports::all(),
					'ademadanreport'=> AdemadanReport::all()
				]);
			}
		}
	}

	public function excluirademadanAction($id = null)
	{
		$report = AdemadanReport::find_by_id($id);
		if (!is_null($report)) {
			$report->delete();
		}
		$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
		->setFile('index')
		->setVars([
			//'rep' => Reports::all(),
			'reports' => Institution::all(),
			'ademadanreport'=> AdemadanReport::all()
		]);
	}
}