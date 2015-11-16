<?php

/*Common*/
function dbMysql(){
	$hostname = "localhost";
	$name = "app";
	$db = new PDO("mysql:host=$hostname;dbname=$name","root","Yuanxing2134");
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->query("SET NAMES utf8");
    return $db;
}

//return Error
function error($errMsg){
	$err = array("status"=>"error", "errmsg"=>$errMsg);
	output($err);
}

//output json
function output($arr){
	echo json_encode($arr);
}

/*Class_*/

//$lesson 课程, $teacher 老师id $user 用户名
function lessons($lesson, $teacher, $user, $is_public){
	$is_login = check_login();
	if(!$is_login){
		error("Please Login");
	}
	else{
		$sql = "SELECT uid from `teacher` WHERE `name`={$teacher} AND `user`={$user}";
		$db = dbMysql();
		$uid = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		if($uid){
			get_lesson_file($lesson, $uid[0], $is_public);
				}
		else{
			error("You are not teacher");
		}
		}
}

function get_lesson_file($lesson, $uid, $is_public){
	if($is_public){
		$lesson = trim($lesson);
		$uid = trim($uid);
		if(!is_numeric($lesson)||!is_numeric($uid)){
			error("invalid request");
		}
		$sql = "SELECT `uid` FROM `public_lesson` WHERE `lesson`={$lesson}";
		$db = dbMysql();
		$result = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		if($result){
			$info = $result[0];
			get_url($info);
		}
		else{
			error("invalid request");
		}
}
	else{
		$lesson = trim($lesson);
		$uid = trim($uid);
		if(!is_numeric($lesson)||!is_numeric($uid)){
			error("invaild request");
		}
		$sql = "SELECT `info` FROM `private_lesson` WHERE `id_teacher`={$uid}";
		$db = dbMysql();
		$result = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$info = array();
		if($result){
			for($i = 0; $i < count($result); $i++){
				$info[$i]=$result[$i];
			}
			output($info);
		}
		else{
			error("invalid request");
		}	}
	}

function createLesson(){
	$request = Slim::getInstance()->request();
	$lesson = trim($request->post('lesson'));
	$id_teacher = trim($request->post('id_teacher'));
	$id_school = trim($request->post('id_school'));
	$type = trim($request->post('type'));
	$info = trim($request->post('info'));
	if(!isset($lesson)||!isset($type)||!isset($info)){
		error("invalid request");
	}
	else{
		$sql = "INSERT INTO `private_lesson` (`lesson`,`id_teacher`,`id_school`,`type`,`info`) VALUES('{$lesson}','{$id_teacher}','{$id_school}','{$type}','{$info}')";
		$db = dbMysql();
		$result = $db->query($sql);
		if($result){
			output(array("status"=>"success","action"=>"upload"));
		}
		else{
			error("false");
		}
	}
}

function deleteLesson(){
	$request = Slim::getInstance()->request();
	$is_teacher = trim($request->post('is_teacher'));
	if($is_teacher){
		$id_teacher = trim($request->post('id_teacher'));
		$id_lesson = trim($request->post('id_lesson'));
		$if_lesson_exist = checklesson($id_lesson);
		if($if_lesson_exist){
			$sql = "DELETE FROM `private_lesson` WHERE `id_teacher`='{$id_teacher}' AND `id_lesson`='{$id_lesson}' LIMIT 1";
			$db = dbMysql();
			$result = $db->query($sql);
			if($result){
				output(array("status"=>"success","action"=>"delete"));
			}
			else{
				error("invalid request");
			}
		}
		else{
			error("No Such Lesson");
		}
	}
}

function checklesson($id_lesson){
	$id_lesson = trim($id_lesson);
	$sql = "SELECT `uid` FROM `private_lesson` WHERE `uid`='{$id_lesson}'";
	$db = dbMysql();
	$result = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	if($result){
		return 1;
	}
	else{
		return 0;
	}
}

function uploadPic(){
	$base_path = "./upload/"; // 接收文件目录
	$target_path = $base_path . basename ( $_FILES ['uploadfile'] ['name'] );
	if(move_uploaded_file ( $_FILES ['uploadfile'] ['tmp_name'], $target_path)){
	$name = $_FILES['uploadfile']['name'];
	$url = "http://101.200.177.122/Android_HT/upload/" . $name;
	$lessonName = $_POST['lessonName'];
	$domainID = $_POST['domainId'];
	$subDomainID = $_POST['subDomainId'];
	$level = $_POST['level'];
	$exerciseIndex = $_POST['exerciseIndex'];
	$exerciseName = $_POST['exerciseName'];
	$exerciseType = $_POST['exerciseType'];
	$unitIndex = $_POST['unitIndex'];
	$schoolId = $_POST['schoolId'];
	$userId = $_POST['userId'];
	$optionIndex = $_POST['optionIndex'];
	$resourceType = $_POST['resourceType'];
	$interIndex = $_POST['interIndex'];

	$db = dbMysql();
	$sql = "INSERT INTO `picture` (`name`, `userId`, `schoolId`, `lessonName`,`domainID`,`subDomainID`,`level`,`exerciseIndex`,`exerciseName`,`exerciseType`,`unitIndex`,`optionIndex`,`responseIndex`,`interIndex`,`url`) VALUES('{$name}','{$userId}','{$schoolId}','{$lessonName}','{$domainID}','{$subDomainID}','{$level}','{$exerciseIndex}','{$exerciseName}','{$exerciseType}','{$unitIndex}','{$optionIndex}','{$resourceType}','{$interIndex}','{$url}')";
	$is_insert = $db->query($sql);
	if ($is_insert) {
		$sql_get_id = "SELECT `uid` FROM `picture` WHERE `name` = '{$name}' LIMIT 1";
		$result = $db->query($sql_get_id)->fetchAll(PDO::FETCH_ASSOC);
		if ($result) {
			output(array("uid" => $result[0]['uid']));
		} else {
			output(array("action" => "get_id", "status" => "failed"));
		}
	} else {
		output(array("action" => "insert", "status" => "failed"));
	}
}
	else{
		output(array("action"=>"upload", "status"=>"failed"));
	}
}

function downloadPic(){
	$request = Slim::getInstance()->request();
	$userId = trim($request->post('userId'));
	$lessonName = trim($request->post('lessonName'));
	$is_public = trim($request->post('is_public'));
	if(!isset($lessonName) || !isset($is_public)){
		error("invalid Request");
	}
	else {
		$db = dbMysql();
		if ($is_public) {
			$get_lesson_id = "SELECT `uid` FROM `public_lesson` WHERE `lesson` = '{$lessonName}' LIMIT 1";
			$id_lesson = $db->query($get_lesson_id)->fetchAll(PDO::FETCH_ASSOC);
			if($id_lesson) {
				$lessonId = $id_lesson[0]['uid'];
				$sql = "SELECT * FROM `picture` WHERE `lessonId` = '{$lessonId}' AND `lessonName` = '{$lessonName}'";
				$result = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
				if ($result) {
					$urls = array();
					for ($i = 0; $i < count($result); $i++) {
						//$urls[$i] = array("url" => $result[$i]['url'],"lessonName"=>$lessonName,"domainId"=>$result[$i]['domainID'],"subDomainId"=>"$result[$i]['subDomainId]","exerciseName"=>$result[$i]['exerciseName'],"exerciseIndex"=>$result[$i]['exerciseIndex'],"exerciseType"=>$result[$i]['exerciseType'],"unitIndex"=>$result[$i]['unitIndex'],"optionIndex"=>$result[$i]['optionIndex'],"resourceType"=>$result[$i]['responseIndex'],"interIndex"=>$result[$i]['interIndex'],"level"=>$result[$i]['level'],"lessonId"=>$result[$i]['lessonId']);
						$urls[$i]=$result[$i];
					}
					output($urls);
				} else {
					error(array("action" => "get url", "status" => "failed"));
				}
			}
			else{
				error("no lesson");
			}
		} else {
			$get_lesson_id = "SELECT `uid` FROM `private_lesson` WHERE `user_id` = '{$userId}' AND `lessonName` = '{$lessonName}' LIMIT 1";
			$id_lesson = $db->query($get_lesson_id)->fetchAll(PDO::FETCH_ASSOC);
			if ($id_lesson) {
				$lessonId = $id_lesson[0]['uid'];
				$sql = "SELECT * FROM `picture` WHERE `lessonId` = '{$lessonId}' AND `userId` = '{$userId}'";
				$result = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
				if ($result) {
					$urls = array();
					for ($i = 0; $i < count($result); $i++) {
						$urls[$i] = array("url" => $result[$i]['url'],"lessonName"=>$lessonName);
					}
					output($urls);
				} else {
					error("no lesson");
				}
			}
			else{
					error(array("action" => "get url", "status" => "failed"));
				}
			}
		}
}

function downloadPic_test($userId,$lessonName,$is_public){
	$userId = trim($userId);
	$lessonName = trim($lessonName);
	$is_public = trim($is_public);
	if(!isset($lessonName) || !isset($is_public)){
		error("invalid Request");
	}
	else{
		$db = dbMysql();
		if($is_public){
			$get_lesson_id = "SELECT `uid` FROM `public_lesson` WHERE `lesson` = '{$lessonName}' LIMIT 1";
			$id_lesson = $db->query($get_lesson_id)->fetchAll(PDO::FETCH_ASSOC);
			$lessonId = $id_lesson[0]['uid'];
			$sql = "SELECT `url` FROM `picture` WHERE `lessonId` = '{$lessonId}' AND `lessonName` = '{$lessonName}'";
			$result = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			if($result) {
				$urls = array();
				for($i = 0;$i<count($result);$i++){
					$urls[$i] = array("url"=>$result[$i]['url']);
				}
				output($urls);
			}
			else{
				error(array("action"=>"get url","status"=>"failed"));
			}
		}
		else{
			$get_lesson_id = "SELECT `uid` FROM `private_lesson` WHERE `user_id` = '{$userId}' AND `lessonName` = '{$lessonName}' LIMIT 1";
			$id_lesson = $db->query($get_lesson_id)->fetchAll(PDO::FETCH_ASSOC);
			$lessonId = $id_lesson[0]['uid'];
			$sql = "SELECT `url` FROM `picture` WHERE `lessonId` = '{$lessonId}' AND `userId` = '{$userId}'";
			$result = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			if($result){
				$urls = array();
				for($i = 0;$i < count($result); $i++){
					$urls[$i] = array("url"=>$result[$i]['url']);
				}
				output($urls);
			}
			else{
				error(array("action"=>"get url", "status"=>"failed"));
			}
		}
	}

}

function get_info()
{
	$request_get = Slim::getInstance()->request();
	$request = trim($request_get->post('json'));
	$para1 = trim($request_get->post('para1'));
	$para2 = trim($request_get->post('para2'));
	$para3 = trim($request_get->post('para3'));
	$is_voice = trim($request_get->post('is_voice'));
	$request = str_replace('"','"',$request);
	$json_string = json_decode($request, True);
	if($is_voice){
		output(array("url"=>$json_string['Exercises'][$para1]['Units'][$para2]['Voice'][$para3]));
	}
	else{
		output(array("url"=>$json_string['Exercises'][$para1]['Units'][$para2]['Answer']['Image'][$para3]));
}
}

//grade
function newgrade($id_student, $id_lesson, $id_teacher, $grade){
	$db = dbMysql();
	$sql = "INSERT INTO `grade` (`id_student`, `id_lesson`, `id_teacher`, `grade`) VALUES('{$id_student}', '{$id_lesson}', '{$id_teacher}','{$grade}')";
	$result = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	if($result){
		output(array("action"=>"newgrade", "status"=>"success"));
	}
	else{
		error("newgrade failed");
	}
}

function updategrade()
{
	$request = Slim::getInstance()->request();
	$db = dbMysql();
	$id_student = trim($request->post('id_student'));
	$id_lesson = trim($request->post('id_lesson'));
	$id_teacher = trim($request->post('id_teacher'));
	$grade = trim($request->post('grade'));
	$sql_query = "SELECT `uid` FROM `grade` WHERE `id_student`='{$id_student}' AND `id_lesson`='{$id_lesson}'";
	$is_exist = dbMysql($sql_query)->query()->fetchAll(PDO::FETCH_ASSOC);
	if ($is_exist) {
		$sql = "UPDATE `grade` SET `grade` = '{$grade}' WHERE `id_student`='{$id_student}' AND `id_lesson`='{$id_lesson}'";
		$sql_check = "SELECT `uid` from `user` WHERE `id_teacher` = '{$id_teacher}' AND `uid`='{$id_student}'";
		$result_check = $db->query($sql_check)->fetchAll(PDO::FETCH_ASSOC);
		if ($result_check) {
			$result = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			if ($result) {
				output(array("action" => "updategrade", "status" => "success"));
			} else {
				error("failed");
			}
		} else {
			error("can't updategrade");
		}
	}
	else{
		newgrade($id_student, $id_lesson, $id_teacher, $grade);
	}
}

function get_grade($id_student, $id_lesson){
	$db = dbMysql();
	$sql = "SELECT `grade` from `grade` WHERE `id_student`='{$id_student}' AND `id_lesson`='{$id_lesson}' LIMIT 1";
	$result = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	if($result){
		output($result);
	}
	else{
		error("no grade");
	}
}

//class
function query_class(){

}

//admin
function login(){
		$request = Slim::getInstance()->request();
		$user = trim($request->post('user'));
		$pwd = trim($request->post('pwd'));
		$pwd = md5(md5($pwd));
		$is_teacher = trim($request->post('is_teacher'));
		if($is_teacher){
			$sql = "SELECT `uid`, `nickname` FROM `teacher` WHERE `teacher`='{$user}' AND `pwd`='{$pwd}' LIMIT 1";
			$db = dbMysql();
			$reslut = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			if($reslut){
				$_SESSION['admin'] = true;
				$_SESSION['nickname']=$reslut[0]['nickname'];
				output(array("status"=>"success","action"=>"login"));
			}
			else{
				error("You are not a teacher");
			}
	}
		else{
			$sql = "SELECT `uid`, `nickname` FROM `user` WHERE `name`='{$user}' AND `pwd`='{$pwd}' LIMIT 1";
			$db = dbMysql();
			$reslut = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			if($reslut){
				$_SESSION['admin'] = true;
				$_SESSION['nickname']=$reslut[0]['nickname'];
				output(array("status"=>"success","action"=>"login"));
			}
			else{
				error("You are not a student");
			}
		}
}

function check_login(){
		if(isset($_SESSION['admin']) && $_SESSION['admin']){
		return 1;
	}
	else{
		return 0;
	}
}

function logout(){
	if(!isset($_SESSION['admin']) || !$_SESSION['admin']){
		error("not login");
	}
	else{
		unset($_SESSION['admin']);
		unset($_SESSION['nickname']);
		output(array("action"=>"logout"));
	}
}
function get_id_teacher($teacher,$id_school){
	$sql = "SELECT `uid` FROM `teacher` WHERE `teacher`='{$teacher}' AND `id_school`='{$id_school}'";
	$db = dbMysql();
	$id_teacher =  $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	if($id_teacher){
		return $id_teacher;
	}
	else{
		error("No Such Teacher");
	}
}

function get_id_school($school){
	$sql = "SELECT `uid` FROM `school` WHERE `school` = '{$school}'";
	$db = dbMysql();
	$id_school = array();
	$result = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	if($result){
		$id_school['uid']=$result[0]['uid'];
		return $id_school['uid'];
	}
	else{
		error("No Such School");
	}
}

function register(){
	$request = Slim::getInstance()->request();
	$user = trim($request->post('user'));
	$pwd = trim($request->post('pwd'));
	$pwd = md5(md5($pwd));
	$school = trim($request->post('school'));
	$id_school = get_id_school($school);
	$nickname = trim($request->post('nickname'));
	$is_teacher = trim($request->post('is_teacher'));
	$db = dbMysql();
	if(!$is_teacher){
		$sql_check = "SELECT `name` FROM `user` WHERE `name`='{$user}'";
		$result = $db->query($sql_check)->fetchAll(PDO::FETCH_ASSOC);
		if($result){
			error("already registered");
		}
		else{
			$teacher = trim($request->post('teacher'));
			$id_teacher = get_id_teacher($teacher, $id_school);
			$sql_insert = "INSERT INTO `user` (`name`,`pwd`,`nickname`,`school`, `teacher`, `id_teacher`, `id_school`) VALUES('{$user}','{$pwd}','{$nickname}','{$school}','{$teacher}','{$id_teacher}','{$id_school}')";
			$is_insert = $db->query($sql_insert);
			if($is_insert){
				output(array("action"=>"register","status"=>"success"));
			}
			else{
				error("false");
			}
		}
	}
	else{
		$sql_check = "SELECT `teacher` FROM `teacher` WHERE `teacher`='{$user}'";
		$result = $db->query($sql_check)->fetchAll(PDO::FETCH_ASSOC);
		if($result){
			error("already sign in");
		}
		else{
			$sql_insert = "INSERT INTO `teacher` (`teacher`,`pwd`,`nickname`,`school`,`id_school`) VALUES('{$user}','{$pwd}','{$nickname}','{$school}','{$id_school}')";
			$is_insert = $db->query($sql_insert);
			if($is_insert){
				output(array("action"=>"register","status"=>"success"));
			}
			else{
				error("false");
			}
		}
	}
}
?>
