<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/12/6
 * Time: 16:28
 */
class BuyerCustomModel extends PublicModel
{

    protected $tableName = 'buyer_custom';
    protected $dbName = 'erui_mall'; //数据库名称
    protected $g_table = 'erui_mall.buyer_custom';

    public function __construct()
    {
        parent::__construct();
    }
    //状态
    const STATUS_DRAFT= 'DRAFT'; //草稿
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETED = 'DELETED'; //删除；
    const DELETE_Y = 'Y';
    const DELETE_N = 'N';

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function getList($condition = []) {

        $where = $this->_getCondition($condition);
        $condition['current_no'] = $condition['currentPage'];

        list($start_no, $pagesize) = $this->_getPage($condition);
        $field = 'id,lang,buyer_id,service_no,title,cat_name,cat_no,item_no,content,remarks,add_desc';
        $field .= ',email,contact_name,company,country_bn,city,tel,status,created_by,created_at';
        return $this->field($field)
                     ->where($where)
                     ->limit($start_no, $pagesize)
                     ->order('id desc')
                     ->select();
    }

    /**
    *获取定制数量
    * @param array $condition
    * @author  klp
    */
    public function getCount($condition) {

        $where = $this->_getCondition($condition);

        return $this->where($where)->count();
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    /*
    public function getlist($condition = [],$limit, $order = " id desc") {

        $sql = 'SELECT `erui_mall`.`buyer_custom`.`id`, `erui_mall`.`buyer_custom`.`buyer_id`,
                 `erui_mall`.`buyer_custom`.`service_no`, `erui_mall`.`buyer_custom`.`title`,
                 `erui_mall`.`buyer_custom`.`cat_id`, `erui_mall`.`buyer_custom`.`item_id`,
                 `erui_mall`.`buyer_custom`.`content`, `erui_mall`.`buyer_custom`.`remarks`,
                 `erui_mall`.`buyer_custom`.`add_desc`, `erui_mall`.`buyer_custom`.`email`,
                 `erui_mall`.`buyer_custom`.`contact_name`, `erui_mall`.`buyer_custom`.`company`,
                 `erui_mall`.`buyer_custom`.`country_bn`, `erui_mall`.`buyer_custom`.`tel`,
                 `erui_mall`.`buyer_custom`.`status`, `erui_mall`.`buyer_custom`.`created_at`,
                 `erui_mall`.`buyer_custom`.`created_by`, `erui_mall`.`buyer_custom`.`updated_at`,
                 `erui_mall`.`buyer_custom`.`updated_by`,';
        $sql .= '`erui_mall`.`custom_cat`.`cat_name`,';
        $sql .= '`erui_buyer`.`buyer_agent`.`agent_id`,';
        $sql .= '`erui_sys`.`employee`.`name` as `agent_name`';
        $str = ' FROM ' . $this->g_table;
        $sql .= $str;

        $sql .= " LEFT JOIN `erui_buyer`.`buyer_agent` ON `erui_buyer`.`buyer_agent`.`buyer_id` = `erui_mall`.`buyer_custom`.`buyer_id` ";
        $sql .= " LEFT JOIN `erui_mall`.`custom_cat` ON `erui_mall`.`custom_cat`.`id` = `erui_mall`.`buyer_custom`.`cat_id`";
        $sql .= " LEFT JOIN `erui_sys`.`employee` ON `erui_buyer`.`buyer_agent`.`agent_id` = `erui_sys`.`employee`.`id` AND `erui_sys`.`employee`.`deleted_flag`='N'";

        $sql_count = 'SELECT count(`erui_mall`.`buyer_custom`.`id`) as num ';
        $sql_count .= $str;
        $where = " WHERE 1 = 1";
        if (isset($condition['buyer_id']) && !empty($condition['buyer_id'])) {
            $where .= ' And `erui_mall`.`buyer_custom`.`buyer_id` ="' . $condition['buyer_id'] . '"';
        }
        if (isset($condition['country_bn']) && !empty($condition['country_bn'])) {
            $where .= ' And `erui_mall`.`buyer_custom`.`country_bn` ="' . $condition['country_bn'] . '"';
        }
        if (isset($condition['company']) && !empty($condition['company'])) {
            $where .= " And `erui_mall`.`buyer_custom`.`company` like '%" . $condition['company'] . "%'";
        }
        if (isset($condition['cat_id']) && !empty($condition['cat_id'])) {
            $where .= ' And `erui_mall`.`buyer_custom`.`cat_id` = "' . $condition['cat_id'] .'"';
        }
        if (isset($condition['official_phone']) && !empty($condition['official_phone'])) {
            $where .= ' And `erui_mall`.`buyer_custom`.`official_phone`  = " ' . $condition['official_phone'] . '"';
        }
        if (isset($condition['email']) && !empty($condition['email'])) {
            $where .= ' And `erui_mall`.`buyer_custom`.`email` ="' . $condition['email'] . '"';
        }

        if ($where) {
            $sql .= $where;
            $sql_count .= $where;
        }
        $sql .= ' Order By ' . $order;
        if (!empty($limit['num'])) {
            $sql .= ' LIMIT ' . $limit['page'] . ',' . $limit['num'];
        }
        $count = $this->query($sql_count);
        $res['count'] = $count[0]['num'];
        $res['data'] = $this->query($sql);
        return $res;
    }*/

    /**
     * 根据条件获取查询条件.
     * @param Array $condition
     * @return mix
     * @author klp
     */
    protected function _getCondition($condition = []) {
        $where = [];
        if (isset($condition['cat_name']) && $condition['cat_name'] && $condition['lang']=='en') {
            switch ($condition['cat_name']) {
                case 'Technology':
                    $where['cat_name'] = 'Technology consulting and comprehensive solutions';
                    break;
                case 'Talent':
                    $where['cat_name'] = 'Talent training';
                    break;
                case 'Human':
                    $where['cat_name'] = 'Human resources';
                    break;
                default :
                    break;
            }
        }
        if (isset($condition['cat_no']) && $condition['cat_no']) {
            $where['cat_no'] = $condition['cat_no'];                  //服务类型编码
        }
        if (isset($condition['lang']) && $condition['lang']) {
            $where['lang'] = $condition['lang'];                  //语言
        }
        if (isset($condition['buyer_id']) && $condition['buyer_id']) {
            $where['buyer_id'] = $condition['buyer_id'];                  //客户ID
        }
        if (isset($condition['cat_no']) && $condition['cat_no']) {
            $where['cat_no'] = $condition['cat_no'];                 //服务类型名称
        }
        /*if (isset($condition['tel']) && $condition['tel']) {
            $where['tel'] = ['REGEXP','([\+]{0,1}\d*[-| ])*'.$condition['tel'].'$'];
        }*/
        if (!empty($condition['start_time']) && !empty($condition['end_time'])) {   //时间
            $where['created_at'] = array(
                array('egt', date('Y-m-d 0:0:0',strtotime($condition['start_time']))),
                array('elt', date('Y-m-d 23:59:59',strtotime($condition['end_time'])))
            );
        }
        $where['deleted_flag'] = !empty($condition['deleted_flag']) ? $condition['deleted_flag'] : 'N'; //删除状态
        return $where;
    }


    /**
     * 获取详情
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function info($custom_id, $lang) {
        if(isset($lang) && !empty($lang)) {
            $where["lang"] = $lang;
        }
        $where = [
            "id"           => $custom_id,
            "deleted_flag" => 'N',
        ];
        if ($where) {
            $customInfo = $this->where($where)->find();
//            $sql = "SELECT  `id`,  `service_id`,  `attach_type`,  `attach_name`,  `default_flag`,  `attach_url`,  `status`,  `created_by`,  `created_at` FROM  `erui_mall`.`service_attach` where deleted_flag ='N' and service_id = " . $customInfo['id'];
//            $row = $this->query($sql);     //==>>扩展加附件使用(后期加)
            $data = array();
            if($customInfo) {
//                $catModel = new CustomCatModel();
//                $catInfo = $catModel->info($lang, $customInfo['cat_id']);
//                $customInfo['cat_name'] = $catInfo[0]['cat_name'];
//                $itemModel = new CustomCatItemModel();
//                $item = json_decode($customInfo['item_id'], true);
//                foreach($item as $v) {
//                    $itemInfo = $itemModel->info($lang, $customInfo['cat_id'], $v);
//                    $customInfo['item_name'][] = $itemInfo[0][0]['item_name'];
//                }
                return $customInfo;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 新增
     */
    public function create_data($create) {
        if (isset($create['buyer_id']) && !empty($create['buyer_id'])) {
            $arr['buyer_id'] = trim($create['buyer_id']);
        } else {
            jsonReturn(null,-1,'用户ID不能为空!');
        }
        if (isset($create['lang']) && !empty($create['lang'])) {
            $arr['lang'] = strtolower(trim($create['lang']));
        }
        if (isset($create['service_no']) && !empty($create['service_no'])) {
            $arr['service_no'] = trim($create['service_no']);
        }
        if (isset($create['title']) && !empty($create['title'])) {
            $arr['title'] = $create['title'];
        }
        if (isset($create['cat_name']) && !empty($create['cat_name'])) {
            $arr['cat_name'] = trim($create['cat_name']);
        }
        if (isset($create['item_name']) && !empty($create['item_name'])) {
            $arr['item_name'] = trim($create['item_name']);
        }
//        if (isset($create['item_name']) && !empty($create['item_name'])) {
//            $arr['item_name'] = json_encode(trim($create['item_name']));
//        }
        if (isset($create['cat_no']) && !empty($create['cat_no'])) {
            $arr['cat_no'] = trim($create['cat_no']);
        }
//        if (isset($create['item_no']) && !empty($create['item_no'])) {
//            $arr['item_no'] = json_encode(trim($create['item_no']));
//        }
        if (isset($create['content']) && !empty($create['content'])) {
            $arr['content'] = trim($create['content']);
        }
        if (isset($create['remarks']) && !empty($create['remarks'])) {
            $arr['remarks'] = trim($create['remarks']);
        }
        if (isset($create['add_desc']) && !empty($create['add_desc'])) {
            $arr['add_desc'] = trim($create['add_desc']);
        }
        if (isset($create['email']) && !empty($create['email'])) {
            $arr['email'] = trim($create['email']);
        }
        if (isset($create['show_name']) && !empty($create['show_name'])) {
            $arr['contact_name'] = trim($create['show_name']);
        }
        if (isset($create['company']) && !empty($create['company'])) {
            $arr['company'] = trim($create['company']);
        }
        if (isset($create['country']) && !empty($create['country'])) {
            $arr['country_bn'] = trim($create['country']);
        }
        if (isset($create['city']) && !empty($create['city'])) {
            $arr['city'] = trim($create['city']);
        }
        if (isset($create['tel']) && !empty($create['tel'])) {
            $arr['tel'] = trim($create['tel']);
        }
        $arr['created_at'] = Date("Y-m-d H:i:s");
        try {
            $arr['created_by'] = $arr['buyer_id'];
            $data = $this->create($arr);
            $res = $this->add($data);
            return $res;
        } catch (Exception $e) {
            print_r($e);
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($e->getMessage(), LOG::ERR);
            return false;
        }
    }

    /**
     * 更新
     */
    public function update_data($data, $where) {
        $arr = [];
        if (isset($data['lang']) && !empty($where['lang'])) {
            $arr['lang'] = strtolower(trim($data['lang']));
        }
        if (isset($data['service_no']) && !empty($where['service_no'])) {
            $arr['service_no'] = trim($data['service_no']);
        }
        if (isset($data['title']) && !empty($where['title'])) {
            $arr['title'] = trim($data['title']);
        }
        if (isset($data['cat_no']) && !empty($where['cat_no'])) {
            $arr['cat_no'] = trim($data['cat_no']);
        }
        /*if (isset($data['item_no']) && !empty($where['item_no'])) {
            $arr['item_no'] = json_encode(trim($data['item_no']));
        }*/
        if (isset($data['content']) && !empty($where['content'])) {
            $arr['content'] = trim($data['content']);
        }
        if (isset($data['remarks']) && !empty($where['remarks'])) {
            $arr['remarks'] = trim($data['remarks']);
        }
        if (isset($data['add_desc']) && !empty($where['add_desc'])) {
            $arr['add_desc'] = trim($data['add_desc']);
        }
        if (isset($data['email']) && !empty($where['email'])) {
            $arr['email'] = trim($data['email']);
        }
        if (isset($data['contact_name']) && !empty($where['contact_name'])) {
            $arr['contact_name'] = trim($data['contact_name']);
        }
        if (isset($data['company']) && !empty($where['company'])) {
            $arr['company'] = trim($data['company']);
        }
        if (isset($data['country_bn']) && !empty($where['country_bn'])) {
            $arr['country_bn'] = trim($data['country_bn']);
        }
        if (isset($create['city']) && !empty($where['city'])) {
            $arr['city'] = trim($create['city']);
        }
        if (isset($data['tel']) && !empty($where['tel'])) {
            $arr['tel'] = trim($data['tel']);
        }
        if (isset($data['status']) && !empty($data['status'])) {
            switch (strtoupper($data['status'])) {
                case self::STATUS_VALID:
                    $arr['status'] = $data['status'];
                    break;
                case self::STATUS_DRAFT:
                    $arr['status'] = $data['status'];
                    break;
                case self::STATUS_DELETED:
                    $arr['status'] = $data['status'];
                    break;
            }
        }
        if (isset($data['updated_by']) && !empty($data['updated_by'])) {
            $arr['updated_by'] = trim($data['updated_by']);
        }
        $arr['updated_at'] = Date("Y-m-d H:i:s");
        if (!empty($where['buyer_id'])) {
            $data = $this->create($arr);
            $res = $this->where($where)->save($data);
        } else {
            return false;
        }
        if ($res !== false) {
            return true;
        }
        return false;
    }
    /**
    删除
     */
    public function delete_data($where) {
        return $this->where($where)->save(['deleted_flag'=> self::DELETE_Y]);
    }



}