<?php
defined('IN_PHPCMS') or exit('No permission resources.'); 

#加载缓存中的栏目名称,注 category_content_站点ID
$CATEGORYS = getcache('category_content_1','commons');

//print_r($CATEGORYS);
//获取参数
$typeid = isset($_GET['typeid']) ? $CATEGORYS[$_GET['typeid']]['arrchildid']: 0;//传递过来的分类ID

$page = isset($_GET['page']) ? intval($_GET['page']): 0;//页码
$pagesize = isset($_GET['pagesize']) ? intval($_GET['pagesize']): 10;//每页多少条，也就是一次加载多少条数据
$start = $page>0 ? ($page-1)*$pagesize : 0;//数据获取的起始位置。即limit条件的第一个参数。

$db = '';
#加载内容模型
$db = pc_base::load_model('content_model');
#重定义加载的表名
$db->table_name = 'v9_news';


$typesql = $typeid ? " catid in($typeid) and " : '';//
$sqlwhere = $typesql . " status = 99 ";

//符合条件的数据总量
$totaldata  =$db->get_one($sqlwhere,'COUNT(id) as num');
$total = $totaldata['num'];

#查询表数据
$limit = " ORDER BY `id` DESC LIMIT {$start},{$pagesize}";
$data = $db->select($sqlwhere#根据实际情况调整where语句
	,'id,catid,title,style,status,thumb,description,url,updatetime,inputtime,username' #需要调取的字段,如需要全部字段则删除本段
	,"{$start},{$pagesize}"
	,'`id` DESC'
);


//echo $sqlwhere;

#定义数组容器
$array=array();
#遍历查询到的数组，注：如不需要对 图片、时间、栏目进行转换可跳过遍历直接将$data输出
foreach ($data as $key => $value) {
		$array[]=array(
			"id"=>$value['id'],
			"title"=>$value['title'],
			"style"=>$value['style'],
			"thumb"=>$value['thumb']?thumb($value['thumb'],520,160):"",
			"description"=>str_cut($value['description'],200),
			"url"=>$value['url'],
			"updatetime"=>date('m/d',$value['updatetime']),
			"inputtime"=>date('m/d',$value['inputtime']),
			"username"=>$value['username'],
			"catname"=>$CATEGORYS[$value['catid']]['catname'],
			'status'=>$value['status']
		);
}


#将数组转换为json数据并输出

$result =array('status'=>200,'list'=>$array,'total'=>$total,'pagetotal'=>ceil($total/$pagesize));

echo json_encode($result); 
?>
