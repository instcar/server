<?php

namespace Instcar\Server\Controllers;

use Instcar\Server\Models\Point as PointModel;
use BullSoft\Geo as Geo;
use Instcar\Server\Models\LinePoint as LinePointModel;

class PointController extends ControllerBase {
	/**
	 * *
	 * 新增聚点
	 */
	public function addAction() {
		$name = trim ( $this->request->getPost ( 'name' ) );
		if (empty ( $name )) {
			$this->flashJson ( 500, array (), "聚点名称不能为空" );
		}
		$lat = trim ( $this->request->getPost ( 'lat' ) );
		$lng = trim ( $this->request->getPost ( 'lng' ) );
		if (empty ( $lat )) {
			$this->flashJson ( 500, array (), "纬度名称不能为空" );
		}
		if (empty ( $lng )) {
			$this->flashJson ( 500, array (), "经度不能为空" );
		}
		$district = trim ( $this->request->getPost ( 'district' ) );
		if (empty ( $district )) {
			$this->flashJson ( 500, array (), "地区不能为空" );
		}
		$city = trim ( $this->request->getPost ( 'city' ) );
		if (empty ( $city )) {
			$this->flashJson ( 500, array (), "城市不能为空" );
		}
		
		$geohash = Geo\Hash::encode ( $lng, $lat );
		
		$points = new PointModel ();
		$points->name = $name;
		$points->lat = $lat;
		$points->lng = $lng;
		$points->geohash = $geohash;
		$points->district = $district;
		$points->city = $city;
		$points->addtime = $points->modtime = date ( 'Y-m-d H:i:s' );
		
		if ($points->save () === false) {
			$errMsgs = array ();
			foreach ( $points->getMessages () as $message ) {
				$errMsgs [] = $message->__toString ();
			}
			$this->flashJson ( 500, array (), join ( "; ", $errMsgs ) );
		}
		$this->flashJson ( 200, array (
				'pointid' => $points->id 
		), "恭喜您，新增聚点成功！" );
	}
	
	/**
	 * *
	 * 编辑聚点
	 */
	public function editAction() {
		$point_id = intval ( $this->request->getPost ( 'pointid' ) );
		if (empty ( $point_id )) {
			$this->flashJson ( 500, array (), "聚点ID不能为空" );
		}
		
		$points = PointModel::findFirst ( "id={$point_id}" );
		if ($points == false) {
			$this->flashJson ( 404, array (), "该聚点信息不存在" );
		}
		
		$name = trim ( $this->request->getPost ( 'name' ) );
		if (empty ( $name )) {
			$this->flashJson ( 500, array (), "聚点名称不能为空" );
		}
		$lat = trim ( $this->request->getPost ( 'lat' ) );
		$lng = trim ( $this->request->getPost ( 'lng' ) );
		if (empty ( $lat )) {
			$this->flashJson ( 500, array (), "纬度名称不能为空" );
		}
		if (empty ( $lng )) {
			$this->flashJson ( 500, array (), "经度不能为空" );
		}
		$district = trim ( $this->request->getPost ( 'district' ) );
		if (empty ( $district )) {
			$this->flashJson ( 500, array (), "地区不能为空" );
		}
		$city = trim ( $this->request->getPost ( 'city' ) );
		if (empty ( $city )) {
			$this->flashJson ( 500, array (), "城市不能为空" );
		}
		
		$geohash = Geo\Hash::encode ( $lng, $lat );
		
		$points->name = $name;
		$points->lat = $lat;
		$points->lng = $lng;
		$points->geohash = $geohash;
		$points->district = $district;
		$points->city = $city;
		$points->addtime = $points->modtime = date ( 'Y-m-d H:i:s' );
		
		if ($points->save () === false) {
			$errMsgs = array ();
			foreach ( $points->getMessages () as $message ) {
				$errMsgs [] = $message->__toString ();
			}
			$this->flashJson ( 500, array (), join ( "; ", $errMsgs ) );
		}
		$this->flashJson ( 200, array (), "恭喜您，修改聚点成功！" );
	}
	
	/**
	 * *
	 * 删除聚点
	 */
	public function delAction() {
		$point_id = intval ( $this->request->getPost ( 'pointid' ) );
		if (empty ( $point_id )) {
			$this->flashJson ( 500, array (), "聚点ID不能为空" );
		}
		$points = PointModel::findFirst ( "id={$point_id}" );
		if ($points == false) {
			$this->flashJson ( 404, array (), "该聚点信息不存在" );
		}
		
		if ($points->delete () === false) {
			$errMsgs = array ();
			foreach ( $points->getMessages () as $message ) {
				$errMsgs [] = $message->__toString ();
			}
			$this->flashJson ( 500, array (), join ( "; ", $errMsgs ) );
		}
		$this->flashJson ( 200, array (), "删除聚点成功！" );
	}
	
	/**
	 * *
	 * 获取聚点分页数据
	 */
	public function listAction() {
		$page = intval ( $this->request->getPost ( 'page' ) );
		$page = $page < 1 ? 1 : $page;
		$rows = intval ( $this->request->getPost ( 'rows' ) );
		$rows = $rows < 1 ? 10 : $rows;
		$offset = ($page - 1) * $rows;
		
		$search_wd = trim ( $this->request->getPost ( 'wd' ) );
		
		$points = new PointModel ();
		
		$where = array (
				"limit" => array (
						"number" => $rows,
						"offset" => $offset 
				),
				"order" => "id ASC" 
		);
		if ($search_wd) {
			$count_where = $where ['conditions'] = "name like '%{$search_wd}%' ";
		}
		
		$rs = $points->find ( $where );
		if( $count_where ){
			$count = $points->count ( $count_where );
		}else{
			$count = $points->count ( );
		}
		
		$data = array ();
		if ($rs) {
			foreach ( $rs as $item ) {
				$data [] = $item->toArray ();
			}
		}
		$this->flashJson ( 200, array (
				"total" => $count,
				"list" => $data 
		), "" );
	}
	
	/**
	 * 根据经纬度获取最近的聚点分页数据
	 */
	public function nearestlistAction() {
		$lat = trim ( $this->request->getPost ( 'lat' ) );
		$lng = trim ( $this->request->getPost ( 'lng' ) );
		if (empty ( $lat )) {
			$this->flashJson ( 500, array (), "纬度不能为空" );
		}
		if (empty ( $lng )) {
			$this->flashJson ( 500, array (), "经度不能为空" );
		}
		$geohash = Geo\Hash::encode ( $lng, $lat );
		
		$page = intval ( $this->request->getPost ( 'page' ) );
		$page = $page < 1 ? 1 : $page;
		$rows = intval ( $this->request->getPost ( 'rows' ) );
		$rows = $rows < 1 ? 10 : $rows;
		$offset = ($page - 1) * $rows;
		
		$points = new PointModel ();
		$search_hash = substr ( $geohash, 0, strlen ( $geohash ) - 2 );
		$where = array (
				"limit" => array (
						"number" => $rows,
						"offset" => $offset 
				),
				"conditions" => "geohash like '{$search_hash}%'",
				"order" => "id ASC" 
		);
		$rs = $points->find ( $where );
		
		$data = array ();
		if ($rs) {
			foreach ( $rs as $item ) {
				$data [] = $item->toArray ();
			}
		}
		
		$count_where = array (
				"conditions" => "geohash like '{$search_hash}%'" 
		);
		$count = $points->count ( $count_where );
		
		$this->flashJson ( 200, array (
				"total" => $count,
				"list" => $data 
		), "" );
	}
	
	/**
	 * 根据聚点ID获取聚点详情
	 */
	public function detailAction(){
		$point_id = intval ( $this->request->getPost ( 'pointid' ) ,10 );
		if ( empty ( $point_id ) ) {
			$this->flashJson ( 500, array (), "聚点ID不能为空" );
		}
	
		$points = new PointModel ();
	
		$rs = $points->findFirst ( "id='{$point_id}'" );
		$data = array();
		if ( $rs ){
			$data = $rs->toArray();
		}
	
		$this->flashJson ( 200,$data, "" );
	}	
	
	/**
	 * 根据聚点关键词
	 */
	public function searchAction() {
		$page = intval ( $this->request->getPost ( 'page' ) );
		$page = $page < 1 ? 1 : $page;
		$rows = intval ( $this->request->getPost ( 'rows' ) );
		$rows = $rows < 1 ? 10 : $rows;
		$offset = ($page - 1) * $rows;
	
		$search_wd =  $this->request->getPost ( 'wd' ) ;
		
		//相关线路ID
		$point_id_arr = $this->request->getPost ( 'pointids' );
		$ids = array();
		foreach ( $point_id_arr as $id ){
			$tmp = intval($id,10);
			if( $tmp ){
				$ids[] = $tmp;
			}
		}
		
		$pointids = array();
		if( !empty($ids) ){			
			$lines = new LinePointModel();
			$ids = implode(",", $ids);
			
			$where['conditions'] = "point_id in ({$ids})";

			$line_infos = $lines->find( $where  );
			$lineids = array();
			if ( $line_infos ) {
				foreach ( $line_infos as $item ) {
					$tmp = $item->toArray ();
					$lineids[$tmp['line_id']] = $tmp['line_id'];
				}
			}

			if( $lineids ){
				$lineids = implode(",", $lineids);
				$where['conditions'] = "line_id in ({$lineids})";
	
				$line_infos = $lines->find( $where  );
	
				if ( $line_infos ) {
					foreach ( $line_infos as $item ) {
						$tmp = $item->toArray ();
						$pointids[$tmp['point_id']] = $tmp['point_id'];
					}
				}
					
	
				$pointids = array_values( $pointids );
			}
		}	
		
		$where = array (
			"limit" => array (
					"number" => $rows,
					"offset" => $offset
			),
			"order" => "id ASC"
		);
	
		if( $search_wd ){
			$where['conditions'][] = "name like '%{$search_wd}%'";
		}
		
		if( $pointids ){
			$pointids = implode(",",$pointids);
			$where['conditions'][] =  " id  in ({$pointids}) ";
		}
		if( $where['conditions'] ){
			$where['conditions'] =  implode(" and ", $where['conditions']);
		}
	
		$points = new PointModel ();
		$rs = $points->find (  $where );
		$data = array ();
		if ( $rs ) {
			foreach ( $rs as $item ) {
				$tmp = $item->toArray ();
				$data[] = $tmp;
			}
		}
	
		$count_where = array (
			"conditions" => $where['conditions']
		);
		$count = $points->count ( $count_where );
	
		$this->flashJson ( 200, array (
				"total" => $count,
				"list" => $data
		), "" );
	}
}