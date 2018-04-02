<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],
        'qiniu' => [
            'driver'  => 'qiniu',
            'domains' => [
                'default'   => 'ooqid2far.bkt.clouddn.com', //你的七牛域名
                'https'     => 'https://outer-performance.oryxtc.top',         //你的HTTPS域名
                'custom'    => 'ooqid2far.bkt.clouddn.com',                //Useless 没啥用，请直接使用上面的 default 项
            ],
            'access_key'=> 'nB52GB7RllcK67CtHCvdr58UCWN_CSgum5On_GQM',  //AccessKey
            'secret_key'=> 'lV6_d8jbxQLDkdHzo1PYBeuvGnJ8LyEyI5zZN67F',  //SecretKey
            'bucket'    => 'images',  //Bucket名字
            'notify_url'=> 'https://outer-performance.oryxtc.top/qiniu/callback',  //持久化处理回调地址
            'access'    => 'public'  //空间访问控制 public 或 private
        ],

    ],

];
