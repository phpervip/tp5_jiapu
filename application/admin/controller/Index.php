<?php
/**
 * Created by mac.
 * User: mac
 * Date: 2018/11/5
 * Time: 下午7:23
 */

namespace app\admin\controller;
use app\common\model\Member;
use think\cache\driver\Redis;
use think\Controller;

class Index extends Controller
{

    public function demo_redis(){
        $redis = new Redis();
        $redis->set('key','111');
        echo $redis->get('key');
    }
    public function tree(){
        return $this->fetch();
    }

    public function mydata(){
        set_time_limit(0);
        $member = new Member;
        $level_max = $member->max('level');
        $redis = new Redis();
        if($redis->get('tree')){
            $tree = $redis->get('tree');
        }else{
            for($al=1;$al<$level_max;$al+=4){
                $map = "level=".$al." and childs<>''";
                $list = $member->field('id,pid,name')->where($map)->select();
                foreach($list as $k=>$v){
                    $tree[$v['id']] = $this->data($al,$v['id']);
                }
            }
            $redis->set('tree',$tree);
        }
        // 对第一层重新书写
        $i=0;
        foreach(json_decode($tree,true) as $k=>$v){
            $name = $member->where('id',$k)->value('name');
            $data[$i]['name'] = $name;
            $data[$i]['children'] = $v;
            $i++;
        }
        return json($data);
    }

    public function data($level,$pid){
        set_time_limit(0);
        $member = new Member;
        // 找到所有level=5,childs有值的。
        $map = 'level<'.($level+6).' and level >'.$level ;
        $data = $member->field('id,pid,name')->where($map)->select();
        $res = $this->getTree($data,$pid);
        return $res;
    }

    public function getTree($data, $pId)
    {
        set_time_limit(0);
        $member = new Member;
        $tree = '';
        foreach($data as $k => $v)
        {
            if($v['pid'] == $pId)
            {        //父亲找到儿子
                $v['children'] = $this->getTree($data, $v['id']);
                $level = $member->where('id',$v['id'])->value('level');
                $v['level'] = $level;
                $tree[] = $v;
                //unset($data[$k]);
            }
        }
        return $tree;
    }

}