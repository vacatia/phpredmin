<?php

class Beanstalkd_Controller extends Controller
{
    public function indexAction()
    {
        $config = App::instance()->config;
        $pheanstalk = new Pheanstalk_Pheanstalk($config['beanstalkd']['host'], $config['beanstalkd']['port']);

        $job = $pheanstalk
            ->watch('phpredmin')
            ->ignore('default')
            ->reserve();

        $data = $job->getData();
        $data = explode(' ', $data);
        $func = $data[0];

        if (method_exists($this, $func)) {
            $this->$func(urldecode($data[1]));
        }
        $pheanstalk->delete($job);
    }

    public function deleteKeys($data)
    {

        Log::factory()->write(Log::INFO, "Try to delete: {$data}", 'Beanstalk');

        $keys  = $this->db->keys($data);
        $count = count($keys);

        $this->db->set("phpredmin:deletecount:{$data}", $count);
        $this->db->del("phpredmin:deleted:{$data}");
        $this->db->del("phpredmin:requests:{$data}");

        foreach ($keys as $key) {
            if ($this->db->delete($key) !== False) {
                $this->db->incrBy("phpredmin:deleted:{$data}", 1);
                $this->db->expireAt("phpredmin:deleted:{$data}", strtotime('+10 minutes'));
            } else
                Log::factory()->write(Log::INFO, "Unable to delete {$key}", 'Beanstalkd');
        }

        $this->db->del("phpredmin:deletecount:{$data}");
    }

    public function moveKeys($job)
    {
    }
}
