<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['db']['host']   = "localhost";
$config['db']['user']   = "root";
$config['db']['pass']   = "";
$config['db']['dbname'] = "homewater";

function DBConnection()
{	
	return new PDO('mysql:dbhost=localhost;dbname=homewater', 'root', '');
}

$app = new \Slim\App(["settings" => $config]);
	$container = $app->getContainer();
	$container['view'] = new \Slim\Views\PhpRenderer("../templates/");
	$container['logger'] = function($c) {
	$logger = new \Monolog\Logger('my_logger');
				$file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
				$logger->pushHandler($file_handler);
				return $logger;
	};

	$container['db'] = function ($c) {
		$db = $c['settings']['db'];
		$pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
		$db['user'], $db['pass']);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		return $pdo;
	};
			
	//setting tampilan awal
	$app->get('/', function (Request $request, Response $response) {
		$this->logger->addInfo("HomeWater Application");
		$response = $this->view->render($response, "login.php");
		return $response;
	});
		
	//home aplikasi admin
	$app->get("/home_admin", function (Request $request, Response $response, $args) {
		$response = $this->view->render($response, "home_admin.phtml");
		return $response;
	});
		
	//home aplikasi user
	$app->get("/home_user", function (Request $request, Response $response, $args) {
		$response = $this->view->render($response, "home_user.phtml");
		return $response;
	});
	
	//login
	$app->post('/login', function ($request, $response) {
		$username = $request->getParsedBody()['username'];
		$password = $request->getParsedBody()['password'];
		$ps = (DBConnection()->query("select password from user where username = '".$username."' LIMIT 1")->fetch());
		$ut = (DBConnection()->query("select usertype from user where username = '".$username."' LIMIT 1")->fetch());
		$db_ps = $ps['password'];
		$db_ut = $ut['usertype'];
		if($password === $db_ps && $db_ut === "admin")
		{
			$_SESSION['isLoggedIn'] = 'admin';
			session_regenerate_id();
			$response = $response->withRedirect("/home_admin");
			return $response;
		} else if
		(
			$password === $db_ps && $db_ut === "user"){
			$_SESSION['isLoggedIn'] = 'user';
			$_SESSION['username'] = $username;
			session_regenerate_id();
			$response = $response->withRedirect("/home_user");
			return $response;
		} else 
		{
			$message = "Username atau Password Anda Salah !";
			echo "<script type='text/javascript'>alert('$message');</script>";
			$response = $this->view->render($response, "login.php");
			return $response;
		}
		});

	//logout
	$app->get('/logout', function ($request, $response, $args) {
			unset($_SESSION['isLoggedIn']);
			unset($_SESSION['username']);
			session_regenerate_id();
			$response = $response->withRedirect("/");
			return $response;
	});
	
	//tabel user
	$app->get('/user', function (Request $request, Response $response) {
			$this->logger->addInfo("user list");
			$mapper = new UserMapper($this->db);
			$user = $mapper->getUser();
			$response = $this->view->render($response, "user.phtml", ["user" => $user, "router" => $this->router]);
			return $response;
	});
		
	//insert user
	$app->get('/user/new', function (Request $request, Response $response) {
			$user_mapper = new UserMapper($this->db);
			$user = $user_mapper->getUser();
			$response = $this->view->render($response, "useradd.phtml", ["user" => $user]);
			return $response;
	});
	$app->post('/user/new', function (Request $request, Response $response) {
			$data = $request->getParsedBody();
			$user_data = [];
			$user_data['username'] = filter_var($data['username'], FILTER_SANITIZE_STRING);
			$user_data['password'] = filter_var($data['password'], FILTER_SANITIZE_STRING);
			$user_data['nama'] 	   = filter_var($data['nama'], FILTER_SANITIZE_STRING);
			$user_data['usertype'] 	   = filter_var($data['usertype'], FILTER_SANITIZE_STRING);
			$user = new UserEntity($user_data);
			$user_mapper = new UserMapper($this->db);
			$user_mapper->save($user);
			$response = $response->withRedirect("/user");
			return $response;
	});
	
	//view user
	$app->get('/user/{username}', function (Request $request, Response $response, $args) {
			$username_id = (String)$args['username'];
			$mapper = new UserMapper($this->db);
			$user = $mapper->getUserByUsername($username_id);
			$response = $this->view->render($response, "userdetail.phtml", ["user" => $user]);
			return $response;
	})->setName('user-detail');
		
	//update user
	$app->get('/user/{username}/update', function (Request $request, Response $response, $args) {
			$username_id = (String)$args['username'];
			$mapper = new UserMapper($this->db);
			$user = $mapper->getUserByUsername($username_id);
			$response = $this->view->render($response, "userupdate.phtml", ["user" => $user]);
			return $response;
	})->setName('user-update');
	$app->post('/user/{username}/update', function (Request $request, Response $response, $args) {
			$username_id = (String)$args['username'];
			$mapper = new UserMapper($this->db);
			$user = $mapper->getUserByUsername($username_id);
			$nama = $request->getParam('nm');
			$password = $request->getParam('password');
			$usertype = $request->getParam('usertype');
			$username = $request->getParam('usrnm');
			DBConnection()->exec("update user set nama = '".$nama."' , password = '".$password."' , usertype = '".$usertype."' where username = '".$username."' ;");
			echo('Data berhasil diupdate !');
			$response = $response->withRedirect("/user");
			return $response;
			})->setName('user-update');
					
	//delete user
	$app->get('/user/{username}/delete', function (Request $request, Response $response, $args ) {
			$username_id = (String)$args['username'];
			$mapper = new UserMapper($this->db);
			$user = $mapper->getUserByUsername($username_id);
			$user_mapper = new UserMapper($this->db);
			$user_mapper->delete($user);
			$response = $response->withRedirect("/user");
			return $response;
	});
	
	//tabel depot 
	$app->get('/depot', function (Request $request, Response $response) {
			$this->logger->addInfo("depot list");
			$mapper = new DepotMapper($this->db);
			$depot = $mapper->getDepot();
			$response = $this->view->render($response, "depot.phtml", ["depot" => $depot, "router" => $this->router]);
			return $response;
	});
		
	//tabel delivery
	$app->get('/delivery', function (Request $request, Response $response) {
			$this->logger->addInfo("depot list");
			$mapper = new DepotMapper($this->db);
			$depot = $mapper->getDepotDelivery();
			$response = $this->view->render($response, "depot_delivery.phtml", ["depot" => $depot, "router" => $this->router]);
			return $response;
	});
		
	//insert depot
	$app->get('/depot/new', function (Request $request, Response $response) {
			$depot_mapper = new DepotMapper($this->db);
			$depot = $depot_mapper->getDepot();
			$response = $this->view->render($response, "depotadd.phtml", ["depot" => $depot]);
			return $response;
	});
	$app->post('/depot/new', function (Request $request, Response $response) {
			$data = $request->getParsedBody();
			$depot_data = [];
			$depot_data['id']			= filter_var($data['id'], FILTER_SANITIZE_STRING);
			$depot_data['nama'] 		= filter_var($data['nama'], FILTER_SANITIZE_STRING);
			$depot_data['depot'] 		= filter_var($data['depot'], FILTER_SANITIZE_STRING);
			$depot = new DepotEntity($depot_data);
			$depot_mapper = new DepotMapper($this->db);
			$depot_mapper->save($depot);
			$response = $response->withRedirect("/depot");
			return $response;
	});

	//depot detail
	$app->get('/depot/{id}/detail', function (Request $request, Response $response, $args) {
			$depot_id	 = (int)$args['id'];
			$mapper 	 = new DepotMapper($this->db);
			$depot		 = $mapper->getDepotById($depot_id);
			$response = $this->view->render($response, "depotdetail.phtml", ["depot" => $depot]);
			return $response;
	})->setName('depot-detail');		
			
	//depot update
	$app->get('/depot/{id}/update', function (Request $request, Response $response, $args) {
			$depot_id	 = (int)$args['id'];
			$mapper 	 = new DepotMapper($this->db);
			$depot		 = $mapper->getDepotById($depot_id);
			$response = $this->view->render($response, "depotupdate.phtml", ["depot" => $depot]);
			return $response;
	})->setName('depot-update');	
	$app->get('/depot/{id}/updat', function (Request $request, Response $response, $args) {
			$depot_id = (int)$args['id'];
			$mapper = new DepotMapper($this->db);
			$depot = $mapper->getDepotById($depot_id);
			$depot = $request->getParam('depot');
			$id = $request->getParam('id');	
			$nama = $request->getParam('nama');
			DBConnection()->exec("update depot set  nama = '".$nama."', depot = '".$depot."' where id = ".$args['id'].";");
			echo('Data berhasil diupdate !');
			$response = $response->withRedirect("/depot");
			return $response;
	})->setName('depot-update');
			
	//delete depot
	$app->get('/depot/{id}/delete', function (Request $request, Response $response, $args ) {
			$depot_id = (int)$args['id'];
			$mapper = new DepotMapper($this->db);
			$depot = $mapper->getDepotById($depot_id);
			$depot_mapper = new DepotMapper($this->db);
			$depot_mapper->delete($depot);
			$response = $response->withRedirect("/depot");
			return $response;
	});
		
	//tabel deposit
	$app->get('/deposit', function (Request $request, Response $response) {
			$this->logger->addInfo("Deposit list");
			$mapper = new DepositMapper($this->db);
			$deposit = $mapper->getDeposit();
			$response = $this->view->render($response, "deposit.phtml", ["deposit" => $deposit, "router" => $this->router]);
			return $response;
	});
			
	//insert deporit
	$app->get('/deposit/new', function (Request $request, Response $response) {
			$depot_mapper = new DepotMapper($this->db);
			$depot = $depot_mapper->getDepot();
			$response = $this->view->render($response, "depositadd.phtml", ["depot" => $depot]);
			return $response;
	});
	$app->post('/deposit/new', function (Request $request, Response $response) {
			$data = $request->getParsedBody();
			$deposit_data = [];
			$deposit_data['waktu']		 = filter_var($data['waktu'], FILTER_SANITIZE_STRING);
			$deposit_data['nilai']		 = filter_var($data['nilai'], FILTER_SANITIZE_STRING);
			//setting atribut depot pada tabel depot
			$depot_id = (int)$data['depot'];
			$depot_mapper = new DepotMapper($this->db);
			$depot = $depot_mapper->getDepotById($depot_id);
			$deposit_data['depot'] = $depot->getDepot();
			$deposit = new DepositEntity($deposit_data);
			$deposit_mapper = new DepositMapper($this->db);
			$deposit_mapper->save($deposit);
			$response = $response->withRedirect("/deposit");
			return $response;
	});
		
	//view Deposit
	$app->get('/deposit/{id}', function (Request $request, Response $response, $args) {
			$deposit_id = (int)$args['id'];
			$mapper = new DepositMapper($this->db);
			$deposit = $mapper->getDepositById($deposit_id);
			$response = $this->view->render($response, "depositdetail.phtml", ["deposit" => $deposit]);
			return $response;
	})->setName('deposit-detail');
			
	//delete deposit
	$app->get('/deposit/{id}/delete', function (Request $request, Response $response, $args ) {
			$deposit_id = (int)$args['id'];
			$mapper = new DepositMapper($this->db);
			$deposit = $mapper->getDepositById($deposit_id);
			$deposit_mapper = new DepositMapper($this->db);
			$deposit_mapper->delete($deposit);
			$response = $response->withRedirect("/deposit");
			return $response;
	});

$app->run();
