<?php
/*
|--------------------------------------------------------------------------
| Prettus Repository Config
|--------------------------------------------------------------------------
|
|
*/
return [

    /*
    |--------------------------------------------------------------------------
    | Repository Pagination Limit Default
    |--------------------------------------------------------------------------
    |
    */
    'pagination'               => [
        'rows'  => 20,         //默认每页显示多少条
        'start' => 0,          //默认第几条开始查找
        'rules' => [
            'rows'  => 'integer|min:1|digits_between: 1,20',
            'start' => 'integer|min:0',
            'with'  => 'string',
            'sort'  => 'string|check_relation:with',
            'fl'    => 'string|check_relation:with',
            'q'     => 'string',
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Cache Config
    |--------------------------------------------------------------------------
    |
    */
    'cache'                    => [
        /*
         |--------------------------------------------------------------------------
         | Cache Status
         |--------------------------------------------------------------------------
         |
         | Enable or disable cache
         |
         */
        'enabled'    => true,

        /*
         |--------------------------------------------------------------------------
         | Cache Minutes
         |--------------------------------------------------------------------------
         |
         | Time of expiration cache
         |
         */
        'minutes'    => 30,

        /*
         |--------------------------------------------------------------------------
         | Cache Repository
         |--------------------------------------------------------------------------
         |
         | Instance of Illuminate\Contracts\Cache\Repository
         |
         */
        'repository' => 'cache',

        /*
          |--------------------------------------------------------------------------
          | Cache Clean Listener
          |--------------------------------------------------------------------------
          |
          |
          |
          */
        'clean'      => [

            /*
              |--------------------------------------------------------------------------
              | Enable clear cache on repository changes
              |--------------------------------------------------------------------------
              |
              */
            'enabled' => true,

            /*
              |--------------------------------------------------------------------------
              | Actions in Repository
              |--------------------------------------------------------------------------
              |
              | create : Clear Cache on create Entry in repository
              | update : Clear Cache on update Entry in repository
              | delete : Clear Cache on delete Entry in repository
              |
              */
            'on'      => [
                'create' => true,
                'update' => true,
                'delete' => true,
            ],
        ],

        'params'  => [
            /*
            |--------------------------------------------------------------------------
            | Skip Cache Params
            |--------------------------------------------------------------------------
            |
            |
            | Ex: http://prettus.local/?search=lorem&skipCache=true
            |
            */
            'skipCache' => 'skipCache',
        ],

        /*
       |--------------------------------------------------------------------------
       | Methods Allowed
       |--------------------------------------------------------------------------
       |
       | methods cacheable : all, paginate, find, findByField, findWhere, getByCriteria
       |
       | Ex:
       |
       | 'only'  =>['all','paginate'],
       |
       | or
       |
       | 'except'  =>['find'],
       */
        'allowed' => [
            'only'   => ['all', 'paginate', 'find', 'findWhere'],
            'except' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Criteria Config
    |--------------------------------------------------------------------------
    |
    | Settings of request parameters names that will be used by Criteria
    |
    */
    'criteria'                 => [
        /*
        |--------------------------------------------------------------------------
        | Accepted Conditions
        |--------------------------------------------------------------------------
        |
        | Conditions accepted in consultations where the Criteria
        |
        | Ex:
        |
        | 'acceptedConditions'=>['=','like']
        |
        | $query->where('foo','=','bar')
        | $query->where('foo','like','bar')
        |
        */
        'acceptedConditions' => [
            '=',
            'like',
        ],
        /*
        |--------------------------------------------------------------------------
        | Request Params
        |--------------------------------------------------------------------------
        |
        | Request parameters that will be used to filter the query in the repository
        |
        | Params :
        |
        | - search : Searched value
        |   Ex: http://prettus.local/?search=lorem
        |
        | - searchFields : Fields in which research should be carried out
        |   Ex:
        |    http://prettus.local/?search=lorem&searchFields=name;email
        |    http://prettus.local/?search=lorem&searchFields=name:like;email
        |    http://prettus.local/?search=lorem&searchFields=name:like
        |
        | - filter : Fields that must be returned to the response object
        |   Ex:
        |   http://prettus.local/?search=lorem&filter=id,name
        |
        | - orderBy : Order By
        |   Ex:
        |   http://prettus.local/?search=lorem&orderBy=id
        |
        | - sortedBy : Sort
        |   Ex:
        |   http://prettus.local/?search=lorem&orderBy=id&sortedBy=asc
        |   http://prettus.local/?search=lorem&orderBy=id&sortedBy=desc
        |
        */
        'params'             => [
            'simpleSearch'   => 'sq',
            'advancedSearch' => 'q',
            'filter'         => 'fl',
            'sort'           => 'sort',
            'start'          => 'start',
            'rows'           => 'rows',
            'with'           => 'with',
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Generator Config
    |--------------------------------------------------------------------------
    |
    */
    'generator'                => [
        'basePath'      => app_path(),
        'rootNamespace' => 'App\\',
        'paths'         => [
            'models'                => 'Models',
            'repositories'          => 'Repositories',
            'repository_interface'  => 'Repositories/Contracts',
            'transformers'          => 'Transformers',
            'transformer_interface' => 'Transformers/Contracts',
            'presenters'            => 'Presenters',
            'validators'            => 'Validators',
            'controllers'           => 'Http/Controllers/Api/V1',
            'provider'              => 'RepositoryServiceProvider',
            'criteria'              => 'Criteria',
            'services'              => 'Services',
            'requests'              => 'Http/Requests',
            'routes'                => 'routes',
            'stubsOverridePath'     => app_path(),
        ],
    ],


    //是否自动记录sql
    'dumpSqlLog'               => true,



    // capture release as git sha
    // 'release' => trim(exec('git log --pretty="%h" -n1 HEAD')),

    // Capture bindings on SQL queries
    'breadcrumbs.sql_bindings' => true,

    // Capture default user context
    'user_context'             => true,

    //version
    'app_version'              => env('APP_VERSION'),

    /*
    |--------------------------------------------------------------------------
    | 队列切库或者curl请求的默认配置
    |--------------------------------------------------------------------------
    */
    //传递header头
    'passQueueHeader'          => true,

    //log type
    'log'                      => env('APP_LOG'),

    /*
    |--------------------------------------------------------------------------
    | Sign global service jwt
    |--------------------------------------------------------------------------
    */

    'global_jwt' => [
        'exp'         => env('GLOBAL_JWT_EXP_SEC'),
        'iss'         => env('GLOBAL_JWT_ISS'),
        'private_key' => env('GLOBAL_JWT_PRIVATE_KEY_PKCS8'),
        'service'     => [
        ]
    ],

    'web_url' => env('WEB_URL', 'http://localhost'),
];
