<?php
declare (strict_types=1);

/**
 * Author:
 *
 *   ┏┛ ┻━━━━━┛ ┻┓
 *   ┃　　　━　　  ┃
 *   ┃　┳┛　  ┗┳  ┃
 *   ┃　　　-　　  ┃
 *   ┗━┓　　　┏━━━┛
 *     ┃　　　┗━━━━━━━━━┓
 *     ┗━┓ ┓ ┏━━━┳ ┓ ┏━┛
 *       ┗━┻━┛   ┗━┻━┛
 * DateTime: 2022-02-26 11:09:53
 */

namespace rocket_cart;

use rocket_cart\contracts\Driver;
use rocket_cart\exception\CartException;
use think\facade\Config;

/**
 * Cart
 * Class Cart
 * @method static Cart cartList($uid, $zone = null) 获取购物车数据
 * @method static Cart cartOper($gid, $gnum, $uid, $zone = null) 购物车添加/减少商品
 * @method static Cart cartUpdateSku($gid, $old_gid, $uid, $zone = null) 更新购物车中单件商品的SKU
 * @method static Cart cartDelSingle($gid, $uid, $zone = null) 从购物车中删除单种商品
 * @method static Cart cartDelMuiti($gid, $uid, $zone = null) 从购物车中删除多件商品
 * @method static Cart cartClearAll($uid, $zone = null) 清空购物车
 * @method static Cart cartExistsGoods($gid, $uid, $zone = null) 购物车是否存在某商品
 * @package rocket
 * create_at: 2022-02-26 11:10:08
 * update_at: 2022-02-26 11:10:08
 */
class Cart
{
    /**
     * @var object 存储介质
     */
    protected static $driver = null;

    /**
     * @var string 类后缀
     */
    protected static $suffix = 'Driver';

    /**
     * ShoppingCart constructor.
     */
    protected function __construct(...$params)
    {
    }

    /**
     * Magic static call.
     * @param $method
     * @param $params
     */
    public static function __callStatic($method, $params)
    {
        $instance = new self($params);
        $driver = Config::get('cart.driver') ?: 'redis';
        $class = $instance->create($driver);

        if (method_exists($class, $method)) {
            return call_user_func_array([$class, $method], $params);
        } else {
            throw new CartException("[{$method}] 方法不存在！");
        }
    }

    /**
     * 创建实例
     * @param string $driver
     * @return Driver
     */
    protected function create($driver): Driver
    {
        $driver = __NAMESPACE__ . '\\drivers\\' . ucfirst($driver . self::$suffix);

        if (self::$driver) {
            return self::$driver;
        } else {
            if (class_exists($driver)) {
                return $this->make($driver);
            } else {
                throw new CartException("Driver [{$driver}] Not Exists");
            }
        }
    }

    /**
     * make
     * @param string $driver
     * @return Driver
     */
    protected function make($driver): Driver
    {
        $app = new $driver();

        if ($app instanceof Driver) {
            return $app;
        }

        throw new CartException("[{$driver}] Must Be An Instance Of Driver");
    }
}
