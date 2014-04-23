<?php

/**
 *Author : Shi oujun
 *Time   : 2014-04-20 15:40:24
 *
 *Brief  : api for car
 */

namespace Instcar\Server\Controllers;

use Instcar\Server\Models\Car as CarModel;
use Instcar\Server\Models\Brand as BrandModel;

class CarController extends ControllerBase
{
	public function listAction()
	{
		$aliasname = trim ( $this->request->getPost ( 'aliasname' ) );
		if( empty($aliasname) ){
			$this->flashJson(500, array(), '参数错误');
		}
		
		$car_brand = new BrandModel();
		$brand = $car_brand->findFirst("iconname='{$aliasname}'");
		if ( $brand===FALSE ){
			$this->flashJson(500, array(), '汽车品牌不存在');
		}
		$brand = $brand->toArray();
		
		
		$car = new CarModel();
		$brands = $car->find("parent_brand='{$brand['name']}'");
		if (! $brands ) {
			$this->flashJson ( 404, array (), 'Not Found car brand' );
		}
		$datas = $brands->toArray();
		
		foreach($datas  as $item){
			$data[$item['brand']][] = $item;
		};
		$this->flashJson ( 200, $data, '' );
	}
}