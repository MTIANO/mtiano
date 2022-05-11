<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Base extends Model
{
    
    public $timestamps = true;
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    //自定义删除标记字段
    public const DELETED_AT = 'status';

    //默认时间的存储格式，改为 Unix 时间戳
    protected $dateFormat = 'U';
    //字段黑名单，不允许添加进表的字段
    protected $guarded = [];

    // 状态
    const STATUS_ENABLE = 1;  // 启用
    const STATUS_DISABLE = 0;  // 禁用
    const STATUS_DELETED = 2;  // 已删除

    public const STATUS_ARR = [self::STATUS_DISABLE, self::STATUS_ENABLE, self::STATUS_DELETED];//状态数组

    //添加全局的条件
    protected static function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub

        static::addGlobalScope('status',function (Builder $builder){
            $builder->where(self::getModel()->getTable().'.status','!=',self::STATUS_DISABLE);
        });
    }

    /**
     * @param Builder $query
     * @return Builder
     *
     * 局部作用域
     */
    public function scopeStatus(Builder $query){
        return $query->where(self::getModel()->getTable().'.status','=', self::STATUS_ENABLE);
    }

    /**
     * 已启用
     *
     * @param $query
     * @return mixed
     */
    public function scopeEnabled(Builder $query)
    {
        return $query->where(self::getModel()->getTable().'.status', self::STATUS_ENABLE);
    }

    /**
     * 已禁用
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDisabled(Builder $query): Builder
    {
       return $query->where(self::getModel()->getTable().'.status', '=', self::STATUS_DISABLE);
    }

    /**
     * 已删除
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDeleted(Builder $query): Builder
    {
        return $query->where(self::getModel()->getTable().'.status', self::STATUS_DELETED);
    }

    /**
     * 过滤状态
     *
     * @param Builder $query
     * @param int $status
     * @return Builder
     */
    public function scopeWithStatus(Builder $query, int $status): Builder
    {
        return $query->where(self::getModel()->getTable().'.status', $status);
    }
    
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
    
    function getTree($data, $pId)
    {
        $tree = array();
        foreach ($data as $v) {
            if ($v['pid'] == $pId) {
                $children = $this->getTree($data, $v['id']);
                if($children){
                    $v['children'] = $children;
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }

}
