<?php
namespace Home\Controller;
use Think\Controller\RestController;
class BodyfatController extends RestController {

    protected $allowMethod = array('get', 'post', 'put', 'delete'); // REST允许的请求类型列表
    protected $defaultType = 'json';

    public function insert2Db($age, $gender, $height, $weight, $bodyfat, $bmi){
        $preDataArray = array(
            'age' => $age,
            'gender' => $gender,
            'height' => $height,
            'weight' => $weight,
            'bodyfat' => $bodyfat,
            'bmi' => $bmi,
            'createtime' => date('Y-m-d H:i:s')
        );
        $dataModel = D('Bodyfat');
        $result = $dataModel->data($preDataArray)->add();
        if($result) {
            echo json_encode(array(
                "status" => "success",
                "id" => $result
            ));
        } else {
            echo json_encode(array(
                "status" => "failure",
            ));
        }
    }

    public function read($id) {
        $dataModel = D('Bodyfat');
        $condition['id'] = $id;
        $result = $dataModel->where($condition)->find();
//        if(false === $result) {
//            echo json_encode(array(
//                "status" => "failure",
//                "error" => $dataModel->getDbError()
//            ));
//        } else if(null === $result) {
//            echo json_encode(array(
//                "status" => "failure",
//                "error" => "数据不存在"
//            ));
//        } else {
//            echo json_encode(array(
//                "status" => "success",
//                "data" => $result
//            ));
//        }
        $result['assessResult'] = $this -> calResult($result['gender'], $result['age'], $result['bodyfat'])['label'];
        echo $this->responseFactory("read", $dataModel, $result, "数据不存在");
    }

    /*新建数据记录*/
//    public function create($age, $gender, $height, $weight, $bodyfat, $bmi){
    public function create(){
        $inputs = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);//backbone使用application/json，必须使用$GLOBALS['HTTP_RAW_POST_DATA']来接收数据

        $name = $inputs['name'];
        $age = $inputs['age'];
        $gender = $inputs['gender'];
        $height = $inputs['height'];
        $weight = $inputs['weight'];
        $bodyfat = $inputs['bodyfat'];
        $bmi = $inputs['bmi'];

        $assessResult = $this -> calResult($gender, $age, $bodyfat);

        $preDataArray = array(
            'name' => $name,
            'age' => $age,
            'gender' => $gender,
            'height' => $height,
            'weight' => $weight,
            'bodyfat' => $bodyfat,
            'bmi' => $bmi,
            'grade' => $assessResult['grade'],
            'createtime' => date('Y-m-d H:i:s')
        );
        $dataModel = D('Bodyfat');
        if (!$dataModel->create($preDataArray)){
            echo json_encode(array(
                "status" => "failure",
                "error" => $dataModel->getError()
            ));
            return false;
        } else {
            $result = $dataModel->add();
        }
//        $result = $dataModel->data($preDataArray)->add();
        if($result) {
            echo json_encode(array(
                "status" => "success",
                "id" => $result,
                "result" => $assessResult['label']
            ));
        } else {
            echo json_encode(array(
                "status" => "failure",
                "error" => $dataModel->getDbError()
            ));
        }

        // echo $this->responseFactory("create", $dataModel, $assessResult['label'], "");
    }

    public function update() {
//        echo json_encode(array(
//            "status" => "success",
//            "data" => I('put.'),//使用I('put.')获取put来的数据
//            "type" => $this->_type
//        ));
    }

    public function delete($id) {
        $dataModel = D('Bodyfat');
        $condition['id'] = $id;
        $result = $dataModel->where($condition)->delete();
//        if(false === $result) {
//            echo json_encode(array(
//                "status" => "failure",
//                "error" => $dataModel->getDbError()
//            ));
//        } else if(0 === $result) {
//            echo json_encode(array(
//                "status" => "failure",
//                "error" => "数据不存在"
//            ));
//        } else {
//            echo json_encode(array(
//                "status" => "success"
//            ));
//        }
        echo $this->responseFactory("delete", $dataModel, $result, "数据不存在");
    }

    public function responseFactory($action, $dataModel, $result, $error) {
        switch($action) {
            case 'create':
                if($result) {
                    return json_encode(array(
                        "status" => "success",
                        "result" => $result
                    ));
                } else {
                    return json_encode(array(
                        "status" => "failure",
                        "error" => $dataModel->getDbError()
                    ));
                }
                break;
            case 'read':
                if(false === $result) {
                    return json_encode(array(
                        "status" => "failure",
                        "error" => $dataModel->getDbError()
                    ));
                } else if(null === $result) {
                    return json_encode(array(
                        "status" => "failure",
                        "error" => $error
                    ));
                } else {
                    return json_encode(array(
                        "status" => "success",
                        "data" => $result
                    ));
                }
                break;
            case "delete":
                if(false === $result) {
                    return json_encode(array(
                        "status" => "failure",
                        "error" => $dataModel->getDbError()
                    ));
                } else if(0 === $result) {
                    return json_encode(array(
                        "status" => "failure",
                        "error" => $error
                    ));
                } else {
                    return json_encode(array(
                        "status" => "success"
                    ));
                }
                break;
        }
    }

    public function calResult($gender, $age, $fat) {

//        $resultMale = array(
//            "A" => "瘦猴，不过肯定能看到腹肌，赶紧去多吃一点吧！",
//            "B" => "男神啊！标准的六块腹肌了吧！说不定人鱼线也有啊！",
//            "C" => "还不错，只是腹肌还是只有那么一整块吧，还需继续努力！",
//            "D" =>"裤子还能穿下吗？皮带扣还够用吗？赶紧运动去吧！",
//            "E" => "还吃那么多？！再不运动，你就没救了啊！"
//        );
//
//        $resultFemale = array(
//            "A" => "弱不禁风小女子一枚吧！多运动，多补充营养！",
//            "B" => "女神就是你了！赶紧亮出你的马甲线和人鱼线吧！",
//            "C" => "软妹子一个，别总宅家里看韩剧了，多去运动运动！",
//            "D" => "放下你手里的零食，你对镜子里的自己还满意吗？加油吧！",
//            "E" => "你确认你是打算放弃治疗了吗？"
//        );
        $resultMale = array(
            "A" => array(
                'grade' => 'A',
                'label' => "有思想，有理想，有抱负，可惜哥们，你就比男神缺了点肌肉，快快点击圣诞大礼包查看专属于你的"
            ),
            "B" => array(
                'grade' => 'B',
                'label' => "自带男神光环，既拥有健康的身体又拥有过人的智慧，哥们，你和真正男神只差了完美的肌肉线条，快快点击圣诞大礼包查看专属于你的"
            ),
            "C" => array(
                'grade' => 'C',
                'label' => "略带文艺气质，看上去帅帅哒，但捏上去软软的，哥们，你只比男神少了点肌肉，快快点击圣诞大礼包查看专属于你的"
            ),
            "D" => array(
                'grade' => 'D',
                'label' => "眉宇间透着霸气，同时拥有一颗柔软的心，可惜哥们，你比男神还多了几两肉，快快点击圣诞大礼包查看专属于你的"
            ),
            "E" => array(
                'grade' => 'E',
                'label' => "拥有阳光般的笑容和强大内心，哥们，你比男神只多了一层脂肪，男人就要对自己狠一点，快快点击圣诞大礼包查看专属于你的"
            )
        );

        $resultFemale = array(
            "A" => array(
                'grade' => 'A',
                'label' => "具有柔美气质，在瘦弱的身躯下有一颗强大的心，亲，你和女神之间只差了一点肌肉，快快点击圣诞大礼包查看专属于你的"
            ),
            "B" => array(
                'grade' => 'B',
                'label' => "自带女神光环，既拥有姣好的身材又拥有智慧的大脑，亲，你和女神只差了马甲线和蜜桃臀，快快点击圣诞大礼包查看专属于你的"
            ),
            "C" => array(
                'grade' => 'C',
                'label' => "拥有甜美迷人的微笑，看上去萌萌哒，捏上去软软的，亲，你只比女神少了点肌肉，快快点击圣诞大礼包查看专属于你的"
            ),
            "D" => array(
                'grade' => 'D',
                'label' => "甜美可人，又聪明伶俐就是圆嘟嘟的脸和肚子给你拖了后腿，亲，你只比女神多了几两肉，快快点击圣诞大礼包查看专属于你的"
            ),
            "E" => array(
                'grade' => 'E',
                'label' => "拥有俏皮可爱的笑容和坚强无比的内心，亲，你比女神只多了一层脂肪，快快点击圣诞大礼包查看专属于你的"
            )
        );

        if($gender == 1) { //male
            if($age < 40) {
                if($fat < 11) {//A
                    return $resultMale['A'];
                } else if($fat >= 11 && $fat < 17) {// B
                    return $resultMale['B'];
                } else if($fat >= 17 && $fat < 22) {// C
                    return $resultMale['C'];
                } else if($fat >= 22 && $fat < 27) {// D
                    return $resultMale['D'];
                } else if($fat >= 27) {// E
                    return $resultMale['E'];
                }
            } else if($age >= 40 && $age <60) {
                if($fat < 12) {//A
                    return $resultMale['A'];
                } else if($fat >= 12 && $fat < 18) {// B
                    return $resultMale['B'];
                } else if($fat >= 18 && $fat < 23) {// C
                    return $resultMale['C'];
                } else if($fat >= 23 && $fat < 28) {// D
                    return $resultMale['D'];
                } else if($fat >= 28) {// E
                    return $resultMale['E'];
                }
            } else if($age >= 60 ) {
                if($fat < 14) {//A
                    return $resultMale['A'];
                } else if($fat >= 14 && $fat < 20) {// B
                    return $resultMale['B'];
                } else if($fat >= 20 && $fat < 25) {// C
                    return $resultMale['C'];
                } else if($fat >= 25 && $fat < 30) {// D
                    return $resultMale['D'];
                } else if($fat >= 30) {// E
                    return $resultMale['E'];
                }
            }
        } else { // female
            if($age < 40) {
                if($fat < 21) {//A
                    return $resultFemale['A'];
                } else if($fat >= 21 && $fat < 28) {// B
                    return $resultFemale['B'];
                } else if($fat >= 28 && $fat < 35) {// C
                    return $resultFemale['C'];
                } else if($fat >= 35 && $fat < 40) {// D
                    return $resultFemale['D'];
                } else if($fat >= 40) {// E
                    return $resultFemale['E'];
                }
            } else if($age >= 40 && $age <60) {
                if($fat < 22) {//A
                    return $resultFemale['A'];
                } else if($fat >= 22 && $fat < 29) {// B
                    return $resultFemale['B'];
                } else if($fat >= 29 && $fat < 36) {// C
                    return $resultFemale['C'];
                } else if($fat >= 36 && $fat < 41) {// D
                    return $resultFemale['D'];
                } else if($fat >= 41) {// E
                    return $resultFemale['E'];
                }
            } else if($age >= 60 ) {
                if($fat < 23) {//A
                    return $resultFemale['A'];
                } else if($fat >= 23 && $fat < 30) {// B
                    return $resultFemale['B'];
                } else if($fat >= 30 && $fat < 37) {// C
                    return $resultFemale['C'];
                } else if($fat >= 37 && $fat < 42) {// D
                    return $resultFemale['D'];
                } else if($fat >= 42) {// E
                    return $resultFemale['E'];
                }
            }
        }
    }
}