<?php
class VisualizarController extends \HXPHP\System\Controller
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
			'administrator', 
			'user', 
			'institution'
		));
		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);
		$report = Reports::find_by_user_id($user_id);
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
				//'report' => $report,
				'reports' => Institution::all(),
				'ademadanreport'=> AdemadanReport::all()
			]);
	}

	public function visualizarAction($cnpj){

		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);

		$institution = Institution::find_by_cnpj($cnpj);

		$report = Reports::find_by_cnpj($cnpj);
		$oficina = WorkshopHearings::find_all_by_cnpj($cnpj);
		$apresentacao = ApresentationHearings::find_all_by_cnpj($cnpj);
		$definicao = DefinitionHearings::find_all_by_cnpj($cnpj);
		$encerramento = Closures::find_all_by_cnpj($cnpj);
		$financeiro = FinancialReports::find_all_by_cnpj($cnpj);
		$juridico = Legal_reports::find_by_cnpj($cnpj);
		$projetos = Projects::find_all_by_cnpj($cnpj);

		$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
			->setFile('geral')
			->setVars([
				//'institution' => $institution,
				//'reports' => Reports::all()
				'report' => $report,
				'oficina' => $oficina,
				'apresentacao' => $apresentacao,
				'definicao' => $definicao,
				'projetos' => $projetos,
				'financeiro' => $financeiro,
				'encerramento' => $encerramento,
				'juridico' => $juridico
			]);
	}

	public function geralrelatorioAction($cnpj){

		//setar uma view para visualizar as informaçoes gerais de cada instituiçao
		//var_dump($cnpj);
	
		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);
		
		$report = Reports::find_by_cnpj($cnpj);
		if(!is_null($report)){
			$oficina = WorkshopHearings::find_all_by_cnpj($cnpj);
			$apresentacao = ApresentationHearings::find_all_by_cnpj($cnpj);
			$definicao = DefinitionHearings::find_all_by_cnpj($cnpj);
			$encerramento = Closures::find_all_by_cnpj($cnpj);
			$financeiro = FinancialReports::find_all_by_cnpj($cnpj);
			$juridico = Legal_reports::find_by_cnpj($cnpj);
			$projetos = Projects::find_all_by_cnpj($cnpj);

			$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
				->setFile('geral')
				->setVars([
				'report' => $report,
				'oficina' => $oficina,
				'apresentacao' => $apresentacao,
				'definicao' => $definicao,
				'projetos' => $projetos,
				'financeiro' => $financeiro,
				'encerramento' => $encerramento,
				'juridico' => $juridico
				//'reports' => Reports::all()
			]);
		}
		else {
			$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
					->setFile('blank');
		}
	}

	public function visualizararquivoAction($cnpj){
		//setar uma view para visualizar todos os arquivos da instituiçao

		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);
		
		$report = Reports::find_by_cnpj($cnpj);
		$oficina = WorkshopHearings::find_all_by_cnpj($cnpj);
		$apresentacao = ApresentationHearings::find_all_by_cnpj($cnpj);
		$definicao = DefinitionHearings::find_all_by_cnpj($cnpj);
		$encerramento = Closures::find_all_by_cnpj($cnpj);
		$financeiro = FinancialReports::find_all_by_cnpj($cnpj);
		$juridico = Legal_reports::find_by_cnpj($cnpj);
		$projetos = Projects::find_all_by_cnpj($cnpj);

		$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
			->setFile('file')
			->setVars([
				'report' => $report,
				'oficina' => $oficina,
				'apresentacao' => $apresentacao,
				'definicao' => $definicao,
				'projetos' => $projetos,
				'financeiro' => $financeiro,
				'encerramento' => $encerramento,
				'juridico' => $juridico
				//'reports' => Reports::all()
			]);
	}

	public function ademadanrelatorioAction($id){

		//setar uma view para visualizar as informaçoes gerais de cada instituiçao

		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);
		
		$report = AdemadanReport::find_by_user_id($id);
		
		$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
			->setFile('ademadan')
			->setVars([
				'report' => $report
			]);

	}

	public function baixarAction($cnpj, $file){

		//$dir_path = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'users' . DS . $cnpj . DS . $file;
		$arquivo = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'users' . DS . $cnpj . DS . $file;
		//var_dump($arquivo);
		//var_dump($_FILES[$arquivo]);

		//$arquivo = $_GET[$dir_path]; 
		if(isset($arquivo) && file_exists($arquivo))
		{
			//var_dump($arquivo);
		      switch(strtolower(substr(strrchr(basename($arquivo),"."),1))){
		         case "pdf": $tipo="application/pdf"; break;
		         case "exe": $tipo="application/octet-stream"; break;
		         case "zip": $tipo="application/zip"; break;
		         case "doc": $tipo="application/msword"; break;
		         case "xls": $tipo="application/vnd.ms-excel"; break;
		         case "ppt": $tipo="application/vnd.ms-powerpoint"; break;
		         case "gif": $tipo="image/gif"; break;
		         case "png": $tipo="image/png"; break;
		         case "jpg": $tipo="image/jpg"; break;
		         case "mp3": $tipo="audio/mpeg"; break;
		         case "php": // deixar vazio por seurança
		         case "htm": // deixar vazio por seurança
		         case "html": // deixar vazio por seurança
		      }
		      header("Content-Type: ".$tipo); // informa o tipo do arquivo ao navegador
		      header("Content-Length: ".filesize($arquivo)); // informa o tamanho do arquivo ao navegador
		      //header('Content-Description: File Transfer');
		      header("Content-Disposition: inline; filename=".basename($arquivo)); // informa ao navegador que é tipo anexo e faz abrir a janela de download, tambem informa o nome do arquivo
		      header('Content-Transfer-Encoding: binary');
		      header('Accept-Ranges: bytes');
		      @readfile($arquivo); // lê o arquivo
		      exit; // aborta pós-ações   
		}
	}

	public function videoAction($cnpj, $file){
		$arquivo = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'users' . DS . $cnpj . DS . $file;
		//var_dump($arquivo);

		$this->view->setTitle('MIS - Monitoramento de Investimentos Socioambientais')
			->setFile('video')
			->setVars([
				'video' => $arquivo
			]);
	}
}