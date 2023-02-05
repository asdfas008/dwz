<?php
/**
 * User: Hufeng
 * Date: 2015/12/15 17:45
 * Desc: 配置文件
 */
return array(
    /*数据库*/
    'DB_SERVER' => array(
        'server' => '127.0.0.1', // 服务器地址
        'database' => 'dwz', // 数据库名
        'dbname' => 'dwz', // 用户名
         'dbpwd' => 'JaF6hjHaWSt3SwL8', // 密码
        'port' => 3306, // 端口
        'charset'=> 'utf8', // 字符集
    ),
    'REDIS'=> array(
        'REDISSENTINELS'=>[
            'scheme' => 'tcp',
            'host'   => '127.0.0.1',
            'port'   => 6379
        ],
        'REDISOPTIONS'=>[
            'replication' => 'sentinel',
            'service' => 'mymaster',
            'parameters' => [
                'password' => 'redis9',
                'database' => 2,
                'read_write_timeout'=>0//超时设置
            ],
        ]
    )
);
