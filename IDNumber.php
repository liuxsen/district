<?php
/**
 * Created by PhpStorm.
 * User: zhezhao
 * Date: 2017/9/5
 * Time: 上午10:41
 * 身份证号解析类
 */

class IDNumber
{
    private $id;
    private $error;
    public function __construct($id)
    {
        $this->id = $id;
    }
    public function setID($id){
        $this->id = $id;
    }
    public function lastError(){
        return $this->error;
    }
    public function parseID(){
        $pattern = '/^([1-3]\d{5})(\d{8})(\d{3})(\d|X)$/';
        if(preg_match($pattern,$this->id,$matches)){
            return $matches;
        }else{
            return false;
        }
    }
    public function checkVcode(){
        if(strlen($this->id) != 18){
            return false;
        }
        $nums = str_split($this->id);
        $params = [7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2];
        $map = ['1','0','X','9','8','7','6','5','4','3','2'];
        $sum = 0;
        for($i=0;$i<17;$i++){
            $sum += $nums[$i]*$params[$i];
        }
        $vcode = $sum%11;
        if($map[$vcode] == $nums[17]){
            return true;
        }else{
            return false;
        }
    }
    public function getDistrict($code){
        $province_code = substr($code,0,2).str_repeat("0",4);
        $city_code = substr($code,0,4).str_repeat("0",2);
        $district_code = $code;
        $mysqli = new mysqli("127.0.0.1","root","root","test");
        $mysqli->set_charset('utf8');
        $result = $mysqli->query("SELECT name FROM district WHERE code IN ('{$province_code}','$city_code','$district_code')");
        $ret = [];
        while($row=$result->fetch_row()){
            array_push($ret,$row[0]);
        }
        $mysqli->close();
        return $ret;
    }
    public function getDate($birth){
        $year = substr($birth,0,4);
        $month = substr($birth,5,2);
        $day = substr($birth,7,2);
        return [$year,$month,$day];
    }
    public function getSex($order){
        return $order%2;
    }
    public function main()
    {
        if (!$this->checkVcode()) {
            $this->error = "校验位错误";
            return false;
        }
        $matches = $this->parseID();
        if (!$matches) {
            $this->error = "格式错误";
            return false;
        }
        $ret = [];
        $ret['district'] = $this->getDistrict($matches[1]);
        $ret['birth'] = $this->getDate($matches[2]);
        $ret['sex'] = $this->getSex($matches[3]);
        return $ret;
    }
}

$obj = new IDNumber('XXXXXXXXXXXXXXXXXX');
$ret = $obj->main();
if($ret){
    print_r($ret);
}else{
    echo $obj->lastError();
}
