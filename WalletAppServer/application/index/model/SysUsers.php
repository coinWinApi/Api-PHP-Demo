<?php
namespace app\index\model;

use think\Model;

class SysUsers extends Model {

    /**
     * 添加数据
     * @param array $data
     * @return int  id值
     */
    public function insert($data) {

        $result = $this->save($data);

        if ($result === false) {

            return false;
        } else {

            return $this->id;
        }
    }

    /**
     * 根据条件修改
     * @param array $where
     * @param array $data
     * @return id  id值
     */
    public function updateByWhere($where, $data) {


        $result = $this->where($where)->update($data);
        if ($result === false) {
            return false;
        } else {
            return true;
        }

    }

    /**
     * 根据条件删除
     * @param array $where
     * @return id  id值
     */
    public function deleteByWhere($where) {
        return $this->where($where)->delete();
    }

    /**
     * 根据条件统计
     * @param array $where
     * @return num  条数
     */
    public function countWhere($where) {

        return $this->where($where)->count();

    }


    /**
     * 根据属性获取一行记录
     * @param array  $where
     * @param string $fileds
     * @return array 返回一维数组，未找到记录则返回空数组
     */
    public function findByAttributes($where = array(), $fileds = "*") {

        $data= $this->field($fileds)->where($where)->find();
        if($data)
        {
            $data->toArray();
        }
        return $data;
    }



    public function findByAttributesOr($where = array(), $fileds = "*") {

        $data= $this->field($fileds)->whereOr($where)->find();
        if($data)
        {
            $data->toArray();
        }

        return $data;
    }

    /**
     * 根据条件查询获得数据
     * @param array  $where
     * @param string $fileds
     * @return array 返回二维数组，未找到记录则返回空数组
     */
    public function findAllByWhere($where = array(), $fileds = "*", $order = "id desc") {

        return $this->field($fileds)->where($where)->order($order)->select()->toArray();
    }

    /**
     * 查询全部数据有分页查询
     * @param array  $where
     * @param string $fileds
     * @param string $offset
     * @param string $num
     * @param string $order
     * @return array 返回二维数组，未找到记录则返回空数组
     */
    public function loadAllData($where, $offset = 0, $num = 1, $order = "id desc") {

        return $this->where($where)->order($order)->limit("$offset,$num")->select()->toArray();
    }







    public function sys_online_device_temp_save($user_id){
        $device_= equipmentSystem();
        $data=array(
            'userid'=>$user_id,
            'device_type'=>$device_['device_type'],
            'device_name'=>$device_['device_name'],
            'ip'=>ip()
        );

       $this->table('sys_online_device_temp');
        $result = $this->save($data);
        if ($result === false) {

            return false;
        } else {

            return $this->id;
        }
    }
}