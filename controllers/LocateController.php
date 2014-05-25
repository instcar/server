<?php
namespace Instcar\Server\Controllers;
use Instcar\Server\Models\Position as PositionModel;


class LocateController extends ControllerBase
{
    public function addAction()
    {

    }

    public function getLastAction()
    {

    }

    public function getSingleAction()
    {
        
    }

    public function getMultiAction()
    {
        if(!$this->user) {
            $this->flashJson(401);
        }
        $userIds = (array) $this->request->getPost("user_ids");
        $userIds = array_filter(array_map("intval", $userIds));

        if(empty($userIds)) {
            $this->flashJson(500, array(), "参数错误！");
        }

        $isAllowed = $this->acl->isAllowed();

        if(!$isAllowed) {
            $this->flashJson(403, array(), "非法访问：您没有权限");
        }

        $positionCollection = PositionModel::find("is_last = 1 AND user_id IN (". join(", ", $userIds) . ")");

        if(empty($positionCollection)) {
            $this->flashJson(500, array(), "你所请求的用户没有痕迹");
        }

        $ret = array();
        $ret['total'] = $positionCollection->count();
        $ret['list'] = $positionCollection->toArray();

        $this->flashJson(200, $ret);
    }

}