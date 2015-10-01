<?php
namespace acl;

use Graphene\controllers\Action;

class GroupRead extends Action
{
    public function run(){
       $groupName=$this->request->getPar('group');
        $group = new Group();
        $group->setName($groupName);
        $rGroup=$group->read();
        $this->sendModel($rGroup);
    }
}