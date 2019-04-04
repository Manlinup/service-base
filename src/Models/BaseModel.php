<?php

namespace Sak\Core\Models;

use Faker\Provider\DateTime;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseModel
 * @package Sak\Core\Models
 */
class BaseModel extends Model
{

    /**
     * 默认使用时间戳戳功能
     *
     * @var bool
     */
    public $timestamps = true;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['created_at', 'updated_at', 'id'];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['deleted_at'];


    /**
     * 获取当前时间
     *
     * @return int
     */
    public function freshTimestamp()
    {
        return time();
    }

    /**
     * 避免转换时间戳为时间字符串
     *
     * @param DateTime|int $value
     * @return DateTime|int
     */
    public function fromDateTime($value)
    {
        return $value;
    }


    /**
     * 从数据库获取的为获取时间戳格式
     *
     * @return string
     */
    public function getDateFormat()
    {
        return 'U';
    }

    /**
     * @param $date
     * @return mixed
     */
    public function getCreatedAtAttribute($date)
    {
        return $date;
    }

    /**
     * @param $date
     * @return mixed
     */
    public function getUpdatedAtAttribute($date)
    {
        return $date;
    }


    /**
     * select的时候避免转换时间为Carbon
     *
     * @param mixed $value
     * @return mixed
     */
//    protected function asDateTime($value) {
//        return $value;
//    }
}
