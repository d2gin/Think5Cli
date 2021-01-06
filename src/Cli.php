<?php
namespace Think5Cli;

use think\App;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\Db;
use think\facade\Config;
use think\Loader;

class Cli extends Command
{
    use \app\common\traits\Cli;
    protected $cli_name    = '';
    protected $lock_dir    = './data/lock/';
    protected $lock_prefix = 'lock_';

    public function configure()
    {
        $this->setName($this->cli_name)
            ->addArgument(
                'proccess',
                Argument::OPTIONAL,
                '执行器')
            /*->addOption(
                'param',
                'p',
                Option::VALUE_OPTIONAL | Option::VALUE_IS_ARRAY,
                '参数',
                []
            )*/
            ->addArgument(
                'param',
                Argument::IS_ARRAY,
                '参数'
            );
    }

    protected function load_config($name, $module = 'spider')
    {
        return Config::load(rtrim(App::getInstance()->getAppPath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $name . App::getInstance()->getConfigExt(), basename($name));
    }

    protected function execute(Input $input, Output $output)
    {
        $proccess = $input->getArgument('proccess');
        empty($proccess) && $proccess = 'exec';
        $method = '_' . $proccess;
        $param  = $input->getArgument('param');
        if (!method_exists($this, $method)) {
            throw new \InvalidArgumentException('Proccess not found');
        }
        call_user_func_array([$this, $method], $param);
    }

    /**
     * 文件锁
     * @param $id
     */
    protected function lock($id)
    {
        is_dir($this->lock_dir) or mkdir($this->lock_dir, 0777, true);
        file_put_contents($this->lock_dir . $this->lock_prefix . $id, 'locked');
    }

    /**
     * 解除文件锁
     * @param $id
     */
    protected function unlock($id)
    {
        @unlink($this->lock_dir . $this->lock_prefix . $id);
    }

    /**
     * 是否有锁
     * @param $id
     * @return bool
     */
    protected function islock($id)
    {
        return is_file($this->lock_dir . $this->lock_prefix . $id);
    }

    /**
     * 清除锁 避免死锁
     * @param int $expire 锁过期时间
     */
    protected function clearlock($expire = 60)
    {
        $files = glob($this->lock_dir . $this->lock_prefix . '*');
        foreach ($files as $file) {
            $filetime = filectime($file);
            if ((time() - $filetime) >= $expire) {
                @unlink($file);
            }
        }
    }

    protected function set_data($table, $data, $map = [], $replace = true)
    {
        if ($map && $info = Db::name($table)->where($map)->find()) {
            if (!$replace) return $info['id'];
            if (is_callable($replace)) {
                return call_user_func_array($replace, [$data, $map]);
            }
            return false === Db::name($table)->where('id', $info['id'])->field(true)->update($data) ? false : $info['id'];
        }
        return Db::name($table)->field(true)->insertGetId($data);
    }

    protected function get_data($table, $map)
    {
        return Db::name($table)->where($map)->find();
    }

}
