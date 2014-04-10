<?php

namespace Instcar\Server\Controllers;

use Instcar\Server\Models\LinePoint as LinePointModel;
use Instcar\Server\Models\Line as LineModel;
use Instcar\Server\Models\Point as PointModel;
use Instcar\Server\Models\LinePoint;

class LineController extends ControllerBase {
	// public function __construct()
	// {
	// parent::__construct();
	// //进行登录验证
	// }
	
	/**
	 * *
	 * 新增线路基本信息
	 */
	public function addLineAction() {
		$name = trim ( $this->request->getPost ( 'name' ) );
		if (empty ( $name )) {
			$this->flashJson ( 500, array (), "线路名称不能为空" );
		}
		
		$description = trim ( $this->request->getPost ( 'description' ) );
		if (empty ( $description )) {
			$this->flashJson ( 500, array (), "描述一下线路信息吧！" );
		}
		
		$price = $this->request->getPost ( 'price' );
		
		if (is_null ( $price ) || floatval ( $price ) < 0.0) {
			$this->flashJson ( 500, array (), "客官，开个价钱吧！" );
		}
		
		$line = new LineModel ();
		$line->name = $name;
		$line->description = $description;
		$line->price = $price;
		$line->addtime = $line->modtime = date ( 'Y-m-d H:i:s' );
		
		if ($line->save () === false) {
			$errMsgs = array ();
			foreach ( $line->getMessages () as $message ) {
				$errMsgs [] = $message->__toString ();
			}
			$this->flashJson ( 500, array (), join ( "; ", $errMsgs ) );
		}
		$this->flashJson ( 200, array ('lineid'=>$line->id), "恭喜您，新增路线成功！" );
	}
	
	/**
	 * *
	 * 修改线路基本信息
	 */
	public function editLineAction() {
		$line_id = intval ( $this->request->getPost ( 'lineid' ) );
		
		$lines = LineModel::findFirst ( "id='$line_id'" );
		if ($lines == false) {
			$this->flashJson ( 404, array (), "该线路信息不存在" );
		}
		
		$name = trim ( $this->request->getPost ( 'name' ) );
		if (empty ( $name )) {
			$this->flashJson ( 500, array (), "线路名称不能为空" );
		}
		
		$description = trim ( $this->request->getPost ( 'description' ) );
		if (empty ( $description )) {
			$this->flashJson ( 500, array (), "描述一下线路信息吧！" );
		}
		
		$price = $this->request->getPost ( 'price' );
		if (is_null ( $price ) || floatval ( $price ) < 0.0) {
			$this->flashJson ( 500, array (), "客官，开个价钱吧！" );
		}
		
		$lines->name = $name;
		$lines->description = $description;
		$lines->price = $price;
		$lines->modtime = date ( 'Y-m-d H:i:s' );
		
		if ($lines->save () === false) {
			$errMsgs = array ();
			foreach ( $line->getMessages () as $message ) {
				$errMsgs [] = $message->__toString ();
			}
			$this->flashJson ( 500, array (), join ( "; ", $errMsgs ) );
		}
		
		$this->flashJson ( 200, array (), "恭喜您，修改路线成功！" );
	}
	
	/**
	 * *
	 * 删除线路
	 */
	public function delLineAction() {
		try {
			// Start a transaction
			$connection = getDI ()->get ( 'db' );
			$connection->begin ();
			
			$line_id = intval ( $this->request->getPost ( 'lineid' ) );
			
			$lines = LineModel::findFirst ( "id='$line_id'" );
			if ($lines == false) {
				$this->flashJson ( 404, array (), "该线路信息不存在" );
			}
			
			// 删除该线路下面的所有聚点
			$linePoints = LinePointModel::find ( "line_id='{$line_id}'" );
			
			if ($linePoints) {
				foreach ( $linePoints as $linePoint ) {
					
					if ($linePoint->delete () === false) {
						$errMsgs = array ();
						foreach ( $line->getMessages () as $message ) {
							$errMsgs [] = $message->__toString ();
						}
						$this->flashJson ( 500, array (), join ( "; ", $errMsgs ) );
					}
				}
			}
			
			if ($lines->delete () === false) {
				$errMsgs = array ();
				foreach ( $line->getMessages () as $message ) {
					$errMsgs [] = $message->__toString ();
				}
				$this->flashJson ( 500, array (), join ( "; ", $errMsgs ) );
			}
			
			// Commit if everything goes well
			$connection->commit ();
		} catch ( Exception $e ) {
			// An exception has occurred rollback the transaction
			$connection->rollback ();
			
			$this->flashJson ( 500, array (), "Transactions fail" );
		}
		
		$this->flashJson ( 200, array (), "恭喜您，删除路线成功！" );
	}
	
	/**
	 * *
	 * 新增线路里面的聚点
	 */
	public function addLinePointAction() {
		$line_id = intval ( $this->request->getPost ( 'lineid' ) );
		
		$lines = LineModel::findFirst ( "id='$line_id'" );
		if ($lines == false) {
			$this->flashJson ( 404, array (), "该线路信息不存在" );
		}
		
		$point_id = intval ( $this->request->getPost ( 'pointid' ) );
		$points = PointModel::findFirst ( "id='$point_id'" );
		if ($points == false) {
			$this->flashJson ( 404, array (), "该聚点信息不存在" );
		}
		
		$points_arr = array ();
		// 该线路是否已经有起始节点
		$is_have_pre_point = false;
		$line_points = LinePointModel::find ( "line_id='{$line_id}'" );
		foreach ( $line_points as $l ) {
			if (empty ( $l->point_id )) {
				$is_have_pre_point = true;
			} else {
				$points_arr [] = $l->point_id;
			}
		}
		
		if (in_array ( $point_id, $points_arr )) {
			$this->flashJson ( 500, array (), "聚点信息在该线路已经存在" );
		}
		
		// 前驱聚点ID
		$pre_point_id = intval ( $this->request->getPost ( 'pre_pointid' ) );
		
		if ($pre_point_id) {
			$points = PointModel::findFirst ( "id='$pre_point_id'" );
			if ($points == false) {
				$this->flashJson ( 404, array (), "该聚点信息不存在" );
			}
			if (! in_array ( $pre_point_id, $points_arr )) {
				$this->flashJson ( 500, array (), "前驱聚点信息在该线路不存在" );
			}
		} else if (empty ( $pre_point_id ) && $is_have_pre_point) {
			$this->flashJson ( 500, array (), "起始聚点信息已经存在" );
		}
		
		// 后继聚点ID
		$post_point_id = intval ( $this->request->getPost ( 'post_pointid' ) );
		if ($post_point_id) {
			$points = PointModel::findFirst ( "id='$post_point_id'" );
			if ($points == false) {
				$this->flashJson ( 404, array (), "该聚点信息不存在" );
			}
			if (! in_array ( $post_point_id, $points_arr )) {
				// $this->flashJson(500, array(), "后继聚点信息在该线路不存在");
			}
		} else if (empty ( $post_point_id ) && empty ( $pre_point_id )) {
			$this->flashJson ( 500, array (), "前置聚点和后置聚点不能全为空" );
		}
		
		if ($point_id == $pre_point_id || $pre_point_id == $post_point_id || $post_point_id == $point_id) {
			$this->flashJson ( 500, array (), "聚点信息错误！" );
		}
		
		$distance = intval ( $this->request->getPost ( 'distance' ) );
		$price = floatval ( $this->request->getPost ( 'price' ) );
		
		$line_point = new LinePointModel ();
		$line_point->line_id = $line_id;
		$line_point->point_id = $point_id;
		$line_point->pre_point_id = $pre_point_id;
		$line_point->post_point_id = $post_point_id;
		$line_point->distance = $distance;
		$line_point->price = $price;
		$line_point->addtime = $line_point->modtime = date ( 'Y-m-d H:i:s' );
		if ($line_point->create () === false) {
			$errMsgs = array ();
			foreach ( $line_point->getMessages () as $message ) {
				$errMsgs [] = $message->__toString ();
			}
			$this->flashJson ( 500, array (), join ( "; ", $errMsgs ) );
		}
		$this->flashJson ( 200, array (), "恭喜您，新增线路聚点成功！" );
	}
	
	/**
	 * *
	 * 根据聚点ID获取线路
	 */
	public function listLineByPointIdAction() {
		$point_id = intval ( $this->request->getPost ( 'pointid' ) );
		$page = intval ( $this->request->getPost ( 'page' ) );
		$page = $page < 1 ? 1 : $page;
		$rows = intval ( $this->request->getPost ( 'rows' ) );
		$rows = $rows < 1 ? 10 : $rows;
		$offset = ($page - 1) * $rows;
		$all = intval ( $this->request->getPost ( 'all' ) );
		
		$line_point = new LinePointModel ();
		$where = array (
				"limit" => array (
					"number" => $rows,
					"offset" => $offset 
				),
				"order" => "id DESC",
				"conditions" => "point_id='{$point_id}'" 
		);
		$rs = $line_point->find ( $where );
		
		$data = array ();
		if ($rs) {
			foreach ( $rs as $item ) {
				$data [] = $item->toArray ();
			}
			if (empty ( $data )) {
				$this->flashJson ( 404, array (), 'Not Found point info' );
			}
		} else {
			$this->flashJson ( 404, array (), 'Not Found point info' );
		}
		
		$line_id_arr = array ();
		foreach ( $data as $robot ) {
			// 排重线路ID
			if (! in_array ( $robot ['line_id'], $line_id_arr )) {
				$line_id_arr [] = $robot ['line_id'];
			}
		}
		$line_ids = implode ( ",", $line_id_arr );
		
		$line = new LineModel ();
		$lines = $line->find ( "id in ({$line_ids})" );
		if (! $lines) {
			$this->flashJson ( 404, array (), 'Not Found line info' );
		}
		
		$data = array ();
		foreach ( $lines as $i ) {
			$tmp = $i->toArray ();
			if ($all) {
				$line_point_info = $line_point->find ( "line_id='{$tmp['id']}'" );
				$list = array ();
				foreach ( $line_point_info as $ii ) {
					$list [] = $ii->toArray ();
				}
				$tmp ['list'] = $list;
			}
			$data [] = $tmp;
		}
		
		$return = array (
				'total' => 10,
				'list' => $data 
		);
		$this->flashJson ( 200, $return, '' );
	}
	
	/**
	 * *
	 * 获取线路列表
	 */
	public function listLineAction() {
		$page = intval ( $this->request->getPost ( 'page' ) );
		$page = $page < 1 ? 1 : $page;
		$rows = intval ( $this->request->getPost ( 'rows' ) );
		$rows = $rows < 1 ? 10 : $rows;
		$offset = ($page - 1) * $rows;
		$search_wd = trim ( $this->request->getPost ( 'wd' ) );
		$all = intval ( $this->request->getPost ( 'all' ) );
		
		$line = new LineModel ();
		$where = array (
				"limit" => array (
					"number" => $rows,
					"offset" => $offset 
				),
				"order" => "id DESC" 
		);
		
		if ($search_wd) {
			$count_where = $where ['conditions'] = "name like '%{$search_wd}%' ";
		}
		
		$lines = $line->find ( $where );
		if (! $lines) {
			$this->flashJson ( 404, array (), 'Not Found line info' );
		}
		
		$count = $line->count ( $count_where );
		
		$data = array ();
		$line_point = new LinePointModel ();
		foreach ( $lines as $i ) {
			$tmp = $i->toArray ();
			if ($all) {
				$line_point_info = $line_point->find ( "line_id='{$tmp['id']}'" );
				$list = array ();
				foreach ( $line_point_info as $ii ) {
					$list [] = $ii->toArray ();
				}
				$tmp ['list'] = $list;
			}
			$data[] = $tmp;
		}
		$return = array('total'=>$count,'list'=>$data);		
		$this->flashJson(200, $return ,'');
	}
	
	/**
	 * *
	 * 根据线路ID获取线路详情
	 */
	public function listLineByIdAction() {
		$line_id = intval ( $this->request->getPost ( 'lineid' ) );
		$all = intval ( $this->request->getPost ( 'all' ) );	
		$line = new LineModel ();
	
		$lines = $line->findFirst ( 'id='.$line_id );
		if (! $lines) {
			$this->flashJson ( 404, array (), 'Not Found line info' );
		}
		$data = $lines->toArray();
		
		$line_point = new LinePointModel ();
		if ($all) {
			$line_point_info = $line_point->find ( "line_id='{$data['id']}'" );
			$list = array ();
			foreach ( $line_point_info as $ii ) {
				$list [] = $ii->toArray ();
			}
			$data['list'] = $list;
		}		
		$this->flashJson(200, $data ,'');
	}
}