<?php

namespace liuxy\admin\components;

use yii\helpers\Url;
use liuxy\admin\models\AdminUser;
use liuxy\admin\Module;
use yii\liuxy\WebController;
use liuxy\admin\models\Permission;

/**
 * Main backend controller.
 */
abstract class Controller extends WebController {

    /**
     * @inheritdoc
     */
    public function beforeAction($action) {
        if (!AdminUser::isLoged()) {
            if (!in_array($action->id,['login'])) {
                if ($this->request->getIsAjax()) {
                    $this->setError('login.failed', 401);
                    $this->setResponseData('data', Url::toRoute('/admin/default/login'));
                } else {
                    $this->redirect(Url::toRoute('/admin/default/login'));
                }
            }
        } else {
            /**
             * @var $this->user \liuxy\admin\models\AdminUser
             */
            $this->user = AdminUser::getUser();
            /**
             * 校验权限
             */
            if (!AdminUser::hasPermission($this->user->id, $action->controller->route)) {
                if (!in_array($action->id,['deny', 'login', 'logout', ''])) {
                    if ($this->request->getIsAjax()) {
                        $this->setError('deny', 403);
                    } else {
                        $this->redirect(Url::toRoute('/admin/default/deny'));
                    }
                }
            }
        }

        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }


    /**
     * 返回内部错误信息
     * @param array $errors 错误队列
     * @param int $code 错误码
     */
    protected function setError($errors = [], $code = 500) {
        $this->setResponseData(['code'=>$code]);
        $errorMesage = '';
        if ($errors) {
            if (is_array($errors)) {
                foreach ($errors as $key=>$value) {
                    $errorMesage.=$key.':';
                    if (is_array($value)) {
                        foreach ($value as $err) {
                            $errorMesage.=$err.'<br/>';
                        }
                    } else {
                        $errorMesage.=$value.'<br/>';
                    }
                }
            } else {
                $errorMesage = $errors;
            }
        }
        $this->setResponseData(['msg'=>$errorMesage]);
    }

    /**
     * @param \yii\base\Action $action
     * @param mixed $result
     * @return mixed|void
     * @throws \yii\db\Exception
     */
    public function afterAction($action, $result) {
        /**
         * 设置导航
         */
        if (($this->format == '' || $this->format == 'html') && !$this->request->getIsAjax()) {
            if ($action->id !== 'login') {
                $myPermission = AdminUser::getPermission($this->user->id);
                $this->setResponseData('topMenu', $this->getTopMenu($myPermission));

                $path = $action->controller->route;
                if(!empty($path) && $path != \Yii::$app->defaultRoute && $path != \Yii::$app->errorHandler->errorAction) {
                    /**
                     * @var $perm \liuxy\admin\models\Permission
                     */
                    $perm = Permission::find()->where(['link'=>$path])->one();
                    if ($perm) {
                        $this->setResponseData('current', $perm);
                        /**
                         * @var $top \liuxy\admin\models\Permission
                         */
                        $top = Permission::findTop($perm);
                        $this->setResponseData('topItem', $top);
                        $this->setResponseData('subMenu', $this->getSubMenu($top->id, $myPermission));
                    } else {
                        $this->setDefaultMenu();
                    }
                } else {
                    $this->setDefaultMenu();
                }
            }
        }
        return parent::afterAction($action, $result);
    }

    /**
     * 设置默认菜单
     */
    private function setDefaultMenu() {
        $this->setResponseData('topItem', false);
        $this->setResponseData('subMenu', false);
        $this->setResponseData('current', false);
    }

    /**
     * 获取顶级菜单
     * @param $myPermission 登录用户的权限列表
     */
    private function getTopMenu($myPermission) {
        $permission = Permission::find()->where(['level'=>2,'status'=>Permission::STATUS_OK])
            ->asArray()->orderBy(['seq'=>SORT_ASC])->all();
        if ($myPermission) {
            $ret = [];
            foreach($permission as $item) {
                if (in_array($item['id'], $myPermission)) {
                    $ret[] = $item;
                }
            }
            return $ret;
        }
        return false;
    }

    /**
     * 获取子级菜单
     * @param $topPermId    上级权限ID
     * @param $myPermission 登录用户的权限列表
     */
    private function getSubMenu($topPermId, $myPermission) {
        if ($myPermission) {
            $subItems = Permission::getAllSub($topPermId, $myPermission);
            return isset($subItems['sub']) ? $subItems['sub'] : false;
        }
        return false;
    }

}
