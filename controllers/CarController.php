<?php

/**
 *Author : Shi oujun
 *Time   : 2014-04-20 15:40:24
 *
 *Brief  : api for car
 */

namespace Instcar\Server\Controllers;

use Instcar\Server\Models\Car as CarModel;

class CarController extends ControllerBase
{
	public function listAction()
	{
		$aliasname = trim ( $this->request->getPost ( 'aliasname' ) );
		if( empty($aliasname) ){
			$this->flashJson(500, array(), '参数错误');
		}

		
		$data[] = array(
			'name'=>"一汽奥迪",
			'list'=>array(
				array(
					'id'=>5041,
					'name'=>"一汽奥迪100",
					'picture'=>'http://m1.auto.itc.cn/car/LOGO/BRAND/J_L_191.jpg?518789',
					'series'=>"100"
				),
				array(
						'id'=>5042,
						'name'=>"一汽奥迪200",
						'picture'=>'http://m1.auto.itc.cn/car/LOGO/BRAND/J_L_191.jpg?518789',
						'series'=>"100"
				),
			)
		);
		$data[] = array(
				'name'=>"进口奥迪",
				'list'=>array(
						array(
								'id'=>5051,
								'name'=>"进口奥迪A1",
								'picture'=>'http://m1.auto.itc.cn/car/LOGO/BRAND/J_L_191.jpg?518789',
								'series'=>"A1"
						),
						array(
								'id'=>5055,
								'name'=>"进口奥迪A3 Allroad",
								'picture'=>'http://m1.auto.itc.cn/car/LOGO/BRAND/J_L_191.jpg?518789',
								'series'=>"A3 Allroad"
						),
				)
		);
		$this->flashJson ( 200, $data, '' );
	}
}