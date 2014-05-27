<?php
/**
 * Created by PhpStorm.
 * User: guweigang
 * Date: 14-5-27
 * Time: 20:46
 */

namespace Instcar\Server\Controllers;

use Instcar\Server\Models\SysConf as SysConfModel;

class AppController extends ControllerBase
{
    public function checkUpdateAction()
    {
        $version = trim($this->request->getPost("version", "string"));
        // a version must be a "1.1.0", "0.0.9" pattern
        if(empty($version)) {
            $this->flashJson(500, array(), "参数错误");
        }
        $oldVersionNumber = $this->getVersionNumber($version);

        $newVersionInfo = SysConfModel::findFirst("name='app'");
        if(empty($newVersionInfo)){
            $this->flashJson(200, array("need_update" => false, "force_update" => false));
        }

        $newVersionValue = $newVersionInfo->value;
        $newVersionArray = json_decode($newVersionValue, true);

        $newVersion = $newVersionArray['version'];

        $newVersionNumber = $this->getVersionNumber($newVersion);

        $forceUpdate = $newVersionArray['force_update'] == 'yes' ? true : false;

        if($newVersionNumber > $oldVersionNumber) {
            $this->flashJson(
                200,
                array(
                    "need_update"  => true,
                    "force_update" => $forceUpdate,
                    "download_url" => $this->url->get("server/app/apkdownload/?version=".$newVersion)
                )
            );
        } else {
            $this->flashJson(
                200,
                array(
                    "need_update" => false,
                    "force_update" => $forceUpdate,
                    "download_url" => "",
                )
            );
        }
    }

    protected function getVersionNumber($version)
    {
        $version = strval($version);
        $versionNumbers = explode(".", $version);
        if(count($versionNumbers) != 3) {
            $this->flashJson(500, array(), "参数错误");
        }
        $majorVersion = array_shift($versionNumbers);
        $minorVersion = array_shift($versionNumbers);
        $patchVersion = array_shift($versionNumbers);
        return 10000 * $majorVersion + 100 * $minorVersion + $patchVersion;
    }

    public function apkDownloadAction()
    {
        $version = $this->request->getQuery("version");
        echo $version;
        exit;
    }
}