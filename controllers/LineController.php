<?php

namespace Instcar\Server\Controllers;

use Instcar\Server\Models\LinePoint as LinePointModel;
use Instcar\Server\Models\Line as LineModel;

class LineController extends ControllerBase
{
// 	public function __construct()
// 	{
// 		parent::__construct();		
// 		//进行登录验证
// 	}
	
	/***
	 * 新增线路基本信息
	 */
	public function addLineAction()
	{
		$name = trim($this->request->getPost('name'));
		if( empty($name) ) {
			$this->flashJson(500, array(), "线路名称不能为空");
		}
		
		$description = trim($this->request->getPost('description'));
		if( empty($description) ) {
			$this->flashJson(500, array(), "描述一下线路信息吧！");
		}
		
		$price = $this->request->getPost('price');
		
		if( is_null($price)|| floatval($price)<0.0 ) {
			$this->flashJson(500, array(), "客官，开个价钱吧！");
		}
		
		$line = new LineModel();
		$line->name = $name;
		$line->description = $description;
		$line->price = $price;
		$line->addtime = $line->modtime = date('Y-m-d H:i:s');
		
		if( $line->save() === false) {
			$errMsgs =  array();
			foreach($line->getMessages() as $message) {
				$errMsgs[] = $message->__toString();
			}
			$this->flashJson(500, array(), join("; ", $errMsgs));
		}
		$this->flashJson(200, array(), "恭喜您，新增路线成功！");
	}
	
	/***
	 * 修改线路基本信息
	 */
	public function editLineAction()
	{
		$line_id = intval( $this->request->getPost('lineid') );
		
		$lines = LineModel::findFirst("id='$line_id'");
		if ( $lines == false ) {
			$this->flashJson(404, array(), "该线路信息不存在");
		}
		
		$name = trim($this->request->getPost('name'));
		if( empty($name) ) {
			$this->flashJson(500, array(), "线路名称不能为空");
		}
	
		$description = trim($this->request->getPost('description'));
		if( empty($description) ) {
			$this->flashJson(500, array(), "描述一下线路信息吧！");
		}
	
		$price = $this->request->getPost('price');
		if( is_null($price) || floatval($price)<0.0 ) {
			$this->flashJson(500, array(), "客官，开个价钱吧！");
		}
	
		$lines->name = $name;
		$lines->description = $description;
		$lines->price = $price;
		$lines->modtime = date('Y-m-d H:i:s');
	
		if( $lines->save() === false ) {
			$errMsgs =  array();
			foreach($line->getMessages() as $message) {
				$errMsgs[] = $message->__toString();
			}
			$this->flashJson(500, array(), join("; ", $errMsgs));
		}
		
		$this->flashJson(200, array(), "恭喜您，修改路线成功！");
	}
	
	/***
	 * 删除线路
	 */
	public function delLineAction()
	{	
		try {
			//Start a transaction
			$connection = getDI()->get('db');
			$connection->begin();
				
			$line_id = intval( $this->request->getPost('lineid') );
			
			$lines = LineModel::findFirst("id='$line_id'");
			if ( $lines == false ) {
				$this->flashJson(404, array(), "该线路信息不存在");
			}
			
			//删除该线路下面的所有聚点
			$linePoints = LinePointModel::find("line_id='{$line_id}'");
				
			if( $linePoints ){
				foreach ($linePoints as $linePoint){
					
					if( $linePoint->delete() === false ) {
						$errMsgs =  array();
						foreach($line->getMessages() as $message) {
							$errMsgs[] = $message->__toString();
						}
						$this->flashJson(500, array(), join("; ", $errMsgs));
					}	
				}
			}
			
			if( $lines->delete() === false ) {
				$errMsgs =  array();
				foreach($line->getMessages() as $message) {
					$errMsgs[] = $message->__toString();
				}
				$this->flashJson(500, array(), join("; ", $errMsgs));
			}
			
			//Commit if everything goes well
			$connection->commit();
				
		} catch(Exception $e) {
		    //An exception has occurred rollback the transaction
		    $connection->rollback();
		    
		    $this->flashJson(500, array(), "Transactions fail");
		}
		
		$this->flashJson(200, array(), "恭喜您，删除路线成功！");		
	}
}