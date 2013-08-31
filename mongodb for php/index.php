<?php
/**
 * PHP操作MongoDB学习笔记
 * 2011年2月23日
 * 原作者：xiaocai
 */

//*************************
//**    连接MongoDB数据库服务器
//*************************

//格式=>("mongodb://用户名:密码@地址:端口/默认指定数据库",参数)
$conn = new Mongo();
//可以简写为
//$conn=new Mongo();                                            #连接本地主机,默认端口.
//$conn=new Mongo("172.21.15.69");                              #连接远程主机
//$conn=new Mongo("xiaocai.loc:10086");                         #连接指定端口远程主机
//$conn=new Mongo("xiaocai.loc",array("replicaSet"=>true));     #负载均衡
//$conn=new Mongo("xiaocai.loc",array("persist"=>"t"));         #持久连接
//$conn=new Mongo("mongodb://sa:123@localhost");                #带用户名密码
//$conn=new Mongo("mongodb://localhost:27017,localhost:27018"); #连接多个服务器
//$conn=new Mongo("mongodb:///tmp/mongo-27017.sock");           #域套接字
//$conn=new Mongo("mongodb://admin_miss:miss@localhost:27017/test",array('persist'=>'p',"replicaSet"=>true));   #完整
//详细资料:http://www.php.net/manual/en/mongo.connecting.php




//*************************
//**    选择数据库与表       
//*************************

$db=$conn->mydb;                                #选择mydb数据库
//$db=$conn->selectDB("mydb");                  #第二种写法

$collection=$db->column;                        #选择集合(选择'表')
//$collection=$db->selectCollection('column');  #第二种写法
//$collection=$conn->mydb->column;              #更简洁的写法

//注意:
// 1.数据库和集合不需要事先创建,若它们不存在则会自动创建它们.
// 2.注意错别字,你可能会无意间的创建一个新的数据库(与原先的数据库混乱).



    
//*************************
//**    插入文档         
//*************************

//**向集合中插入数据,返回bool判断是否插入成功. **/
$array=array('column_name'=>'col'.rand(100,999),'column_exp'=>'xiaocai');
$result=$collection->insert($array);        #简单插入
echo "新记录ID:".$array['_id'];              #MongoDB会返回一个记录标识
var_dump($result);                          #返回:bool(true) 
#插入结果:{ "_id" : ObjectId("4d63552ad549a02c01000009"), "column_name" : "col770", "column_exp" : "xiaocai" }
#'_id'为主键字段,在插入是MongoDB自动添加.

//**向集合中安全插入数据,返回插入状态(数组). **/
$array=array('column_name'=>'col'.rand(100,999),'column_exp'=>'xiaocai2');
$result=$collection->insert($array,true);   #用于等待MongoDB完成操作,以便确定是否成功.(当有大量记录插入时使用该参数会比较有用)
echo "新记录ID:".$array['_id'];              #MongoDB会返回一个记录标识
var_dump($result);                          #返回:array(3) { ["err"]=> NULL ["n"]=> int(0) ["ok"]=> float(1) }
    
//**插入的完整语法 **/
# insert(array $data,array('safe'=>false,'fsync'=>false,'timeout'=>10000))
# 参数说明:safe:默认false,是否安全写入;fsync:默认false,是否强制插入到同步到磁盘;timeout:超时时间(毫秒)

//**以下两次插入的为同一条记录(相同的_id),因为它们的值相同**/
$collection->insert(array('column_name'=>'xiaocai'));
$collection->insert(array('column_name'=>'xiaocai'));
#避免方法，安全插入
$collection->insert(array('column_name'=>'xiaocai'),true);
 try {
     $collection->insert(array('column_name'=>'xiaocai'),true);
}catch(MongoCursorException $e){
    echo "Can't save the same person twice!\n";
}
//详细资料:http://www.php.net/manual/zh/mongocollection.insert.php



    
//*************************
//**    更新文档            
//*************************

//** 修改更新 **/
$where=array('column_name'=>'col123');
$newdata=array('column_exp'=>'GGGGGGG','column_fid'=>444);
$result=$collection->update($where,array('$set'=>$newdata));  #$set:让某节点等于给定值
/* 
 * 原数据
 * {"_id":ObjectId("4d635ba2d549a02801000003"),"column_name":"col123","column_exp":"xiaocai"}
 * 被替换成了
 * {"_id":ObjectId("4d635ba2d549a02801000003"),"column_name":"col123","column_exp":"GGGGGGG","column_fid":444}
 */

//** 替换更新 **/
$where=array('column_name'=>'col709');
$newdata=array('column_exp'=>'HHHHHHHHH','column_fid'=>123);
$result=$collection->update($where,$newdata);
/* 
 * 原数据
 * {"_id":ObjectId("4d635ba2d549a02801000003"),"column_name":"col709","column_exp":"xiaocai"}
 * 被替换成了
 * {"_id":ObjectId("4d635ba2d549a02801000003"),"column_exp":"HHHHHHHHH","column_fid":123}
 */

//** 批量更新 **/
$where=array('column_name'=>'col');
$newdata=array('column_exp'=>'multiple','91u'=>684435);
$result=$collection->update($where,array('$set'=>$newdata),array('multiple'=>true));
/**
 * 所有'column_name'='col'都被修改
 */

//** 自动累加 **/
$where=array('91u'=>684435);
$newdata=array('column_exp'=>'edit');
$result=$collection->update($where,array('$set'=>$newdata,'$inc'=>array('91u'=>-5)));
/**
 * 更新91u=684435的数据,并且91u自减5
 * 注意：91u存在时加上-5，不存在时就设置91u=-5
 */

//**  匹配到就更新，否则新增  **/
 $c->update(
    array("name" => "joe"),
    array("username" => "joe312", "createdAt" => new MongoDate()), 
    array("upsert" => true) #up(date)(in)sert
);

/** 删除节点 **/
$where=array('column_name'=>'col685');
$result=$collection->update($where,array('$unset'=>'column_exp'));
/**
 * 删除节点column_exp
 */

/** 附加新数据到节点 **/
$coll->update(
    array('b'=>1),
    array('$push'=>array('a'=>'wow')) #附加新数据到节点a
);
# 如果对应节点是个数组，就附加一个新的值上去；不存在，就创建这个数组，并附加一个值在这个数组上；
# 如果该节点不是数组，返回错误。
# 原纪录：array('a'=>array(0=>'haha'),'b'=>1)
# 新记录为：array('a'=>array(0=>'haha',1=>'wow'),'b'=>1)
# $pushAll与$push类似，只是会一次附加多个数值到某节点

/** 判断更新 **/
$coll->update(
    array('b'=>1),
    array('$addToSet'=>array('a'=>'wow'))
);
# 如果该阶段的数组中没有某值，就添加之
# 设记录结构为array('a'=>array(0=>'haha'),'b'=>1)
# 如果在a节点中已经有了wow,那么就不会再添加新的，
# 如果没有，就会为该节点添加新的item——wow。

/** 删除某数组节点的最后一个元素 **/
$coll->update(
    array('b'=>1),
    array('$pop'=>array('a'=>1)) #删除a数组节点的最后一个元素
);

/** 删除某数组节点的第一个元素 **/ 
$coll->update(
    array('b'=>1),
    array('$pop'=>array('a'=>-1))  #删除a数组节点的第一个元素
);

/** 删除某数组节点的元素 **/
$coll->update(
    array('b'=>1),
    array('$pull'=>array('a'=>'haha'))
)
# 如果该节点是个数组，那么删除其值为value的子项，如果不是数组，会返回一个错误。
# 原记录为：array('a'=>array(0=>'haha',1=>'wow'),'b'=>1)，
# 删除a中value为haha的子项
# 结果为： array('a'=>array(0=>'wow'),'b'=>1)
# $pullAll与$pull类似，只是可以删除一组符合条件的记录。

# 注意:
# 1.注意区分替换更新与修改更新
# 2.注意区分数据类型如 array('91u'=>'684435')与array('91u'=>684435)
# 详细资料:http://www.mongodb.org/display/DOCS/Updating#Updating-%24bit



    
//*************************
//**    删除文档         
//*************************

/** 删除 **/
$collection->remove(array('column_name'=>'col399'));
//$collection->remove();                #清空集合
//$collection->drop();                  #清空，效率高于remove()


/** 删除指定MongoId **/
$id = new MongoId("4d638ea1d549a02801000011");
$collection->remove(array('_id'=>(object)$id));
/*
 * *
 *  使用下面的方法来匹配{"_id":ObjectId("4d638ea1d549a02801000011")},查询、更新也一样
 *  $id = new MongoId("4d638ea1d549a02801000011");
 *  array('_id'=>(object)$id) 
 * *
 */




//*************************
//**    查询文档         
//*************************

/** 查询文档中的记录数 **/
echo 'count:'.$collection->count()."<br>";                                          #全部
echo 'count:'.$collection->count(array('type'=>'user'))."<br>";                     #可以加上条件
echo 'count:'.$collection->count(array('age'=>array('$gt'=>50,'$lte'=>74)))."<br>"; #大于50小于等于74
echo 'count:'.$collection->find()->limit(5)->skip(0)->count(true)."<br>";           #获得实际返回的结果数

/**
 * 注:$gt为大于、$gte为大于等于、$lt为小于、$lte为小于等于、$ne为不等于、$exists不存在
 */

/** 集合中所有文档 **/
$cursor = $collection->find()->snapshot();
foreach ($cursor as $id => $value) {
    echo "$id: "; var_dump($value); echo "<br>";     
}
/**
 * 注意:
 *      在我们做了find()操作，获得$cursor游标之后，这个游标还是动态的.
 *      换句话说,在我find()之后,到我的游标循环完成这段时间,如果再有符合条件的记录被插入到collection,那么这些记录也会被$cursor获得.
 *      如果你想在获得$cursor之后的结果集不变化,需要这样做：
 *      $cursor = $collection->find();
 *      $cursor->snapshot();#获得快照！
 *      详见http://www.bumao.com/index.php/2010/08/mongo_php_cursor.html
 */

/** 查询一条数据 **/
$cursor = $collection->findOne();
/**
 *  注意:findOne()获得结果集后不能使用snapshot(),fields()等函数;
 */

/** age,type 列不显示 **/
$cursor = $collection->find()->fields(array("age"=>false,"type"=>false));

/** 只显示user 列 **/
$cursor = $collection->find()->fields(array("user"=>true));
/**
 * 我这样写会出错:$cursor->fields(array("age"=>true,"type"=>false));
 */

/** (存在type,age节点) and age!=0 and age<50 **/
$where=array('type'=>array('$exists'=>true),'age'=>array('$ne'=>0,'$lt'=>50,'$exists'=>true));
$cursor = $collection->find($where);

/** 分页获取结果集  **/
$cursor = $collection->find()->limit(5)->skip(0);

/** 排序  **/
$cursor = $collection->find()->sort(array('age'=>-1,'type'=>1));                    #1表示降序 -1表示升序,参数的先后影响排序顺序

/** 创建索引  **/
$collection->ensureIndex(array('age' => 1,'type'=>-1));                             #1表示降序 -1表示升序
$collection->ensureIndex(array('age' => 1,'type'=>-1),array('background'=>true));   #索引的创建放在后台运行(默认是同步运行)
$collection->ensureIndex(array('age' => 1,'type'=>-1),array('unique'=>true));       #该索引是唯一的

/** 取得查询结果 **/
$cursor = $collection->find();
$array=array();
foreach ($cursor as $id => $value) {
   $array[]=$value;
}