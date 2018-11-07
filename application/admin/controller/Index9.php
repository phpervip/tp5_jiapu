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

class Index9 extends Controller
{
    public function tree(){
        return $this->fetch();
    }
    public function mydata(){
        set_time_limit(0);
        $member = new Member;
        $level_max = $member->max('level');

        for($al=1;$al<$level_max;$al+=4){
            $map = "level=".$al." and childs<>''";
            $list = $member->field('id,pid,name')->where($map)->select();
            foreach($list as $k=>$v){
                $tree[$v['id']] = $this->data($al,$v['id']);
            }
         }
        if(!is_array($tree)){
            $tree = json_decode($tree,true);
        };
        // 对第一层重新书写
        $i=0;
        foreach($tree as $k=>$v){
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

    public function jiapu(){
        $user = new Member;
        $list = $user->select();
        foreach($list as $k=>$v){
            $v = $v->toArray();
            $id = $v['id'];
            $fathers = $this->fathers($id);
            $childs = $this->childs($id);
            $v['childs'] = $childs;
            $v['fathers'] = $fathers;
            if($fathers==0){
                $v['level'] = 1;
            }else{
                $fathers_arr = explode(',',$fathers);
                $v['level'] = count($fathers_arr)+1;
            }
            $family[$k]['id'] = $v['id'];
            $family[$k]['childs'] = $v['childs'];
            $family[$k]['fathers'] = $v['fathers'];
            $family[$k]['level'] = $v['level'];
            echo "<pre>";print_r($v);echo "<pre>";
        }
        $user->saveAll($family);
    }

    function childs($id)
    {
        $User = new Member;
        if ($id) {
            $ids = $User->where('pid', '=', $id)->column('id');
            $ids_str = implode(',', $ids);
        }
        return $ids_str;
    }

    public function fathers($id){
        $User = new Member;
        if ($id) {
            $pid = $User->where('id',$id)->value('pid');
            $ids_str = 0;
            if ($pid) {
                $ids_str = $pid;
                if ($fathers_ids = $this->fathers($pid)) {
                    $ids_str = $fathers_ids . ',' . $ids_str;
                }
            }
        } else {
            return 0;
        }
        return $ids_str;
    }
    public function demo_data(){
        $data1 = [
            'name'=>'flare',
            'children'=>[
               [ 'name'=>'analytics',
                'children'=>[
                    [ 'name'=>'analytics',
                        'children'=>[
                            [ 'name'=>'analytics',
                                'children'=>[
                                    [ 'name'=>'analytics',
                                        'children'=>[
                                            ['name'=>'Agglom',
                                                'value'=> 3938],
                                            ['name'=>'Communi',
                                                'value'=> 3812],
                                            ['name'=>'Hierar',
                                                'value'=> 6714],
                                            ['name'=>'Mergee',
                                                'value'=> 743],
                                        ],
                                    ],
                                    [ 'name'=>'graph',
                                        'children'=>[
                                            ['name'=>'Agglo',
                                                'value'=> 3938],
                                            ['name'=>'Commure',
                                                'value'=> 3812],
                                            ['name'=>'Hierr',
                                                'value'=> 6714],
                                            ['name'=>'Mere',
                                                'value'=> 743],
                                        ],
                                    ],

                                ],
                            ],
                            [ 'name'=>'graph',
                                'children'=>[
                                    ['name'=>'Agglo',
                                        'value'=> 3938],
                                    ['name'=>'Commure',
                                        'value'=> 3812],
                                    ['name'=>'Hierr',
                                        'value'=> 6714],
                                    ['name'=>'Mere',
                                        'value'=> 743],
                                ],
                            ],

                        ],
                    ],
                    [ 'name'=>'graph',
                        'children'=>[
                            ['name'=>'Agglo',
                                'value'=> 3938],
                            ['name'=>'Commure',
                                'value'=> 3812],
                            ['name'=>'Hierr',
                                'value'=> 6714],
                            ['name'=>'Mere',
                                'value'=> 743],
                        ],
                    ],

                ],
               ],
                [ 'name'=>'graph',
                    'children'=>[
                        ['name'=>'Agglo',
                            'value'=> 3938],
                        ['name'=>'Commure',
                            'value'=> 3812],
                        ['name'=>'Hierr',
                            'value'=> 6714],
                        ['name'=>'Mere',
                            'value'=> 743],
                    ],
                ],

            ]
        ];

        $data2 = [
            'name'=>'flare',
            'children'=>[
                [ 'name'=>'analytics',
                    'children'=>[
                        ['name'=>'AgglomerativeCluster',
                            'value'=> 3938],
                        ['name'=>'CommunityStructure',
                            'value'=> 3812],
                        ['name'=>'HierarchicalCluster',
                            'value'=> 6714],
                        ['name'=>'MergeEdge',
                            'value'=> 743],
                    ],
                ],
                [ 'name'=>'graph',
                    'children'=>[
                        ['name'=>'AgglomerativeCluster',
                            'value'=> 3938],
                        ['name'=>'CommunityStructure',
                            'value'=> 3812],
                        ['name'=>'HierarchicalCluster',
                            'value'=> 6714],
                        ['name'=>'MergeEdge',
                            'value'=> 743],
                    ],
                ],

            ]
        ];

        $data = [$data1,$data2];

        return json($data);
    }


}