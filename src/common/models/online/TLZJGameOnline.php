<?php
namespace common\models\online;

use yii\helpers\Json;

/**
 * Class TLZJGameOnline
 * @property string $onLine
 * @package console\models\online
 */
class TLZJGameOnline extends GameOnline
{
    const SUCCESS = 200;
    protected $pid;

    public function init()
    {
        $this->gKey = 'tlzj';
        parent::init();
    }

    public function query()
    {
        return  [
            'gkey' => $this->gKey,
            'from' => $this->from,
            'to' => $this->to,
            'pid' => $this->pid,
            'sign' => $this->sign(),
        ];
    }

    public function getOnLine()
    {
        $platformIdArr = (array_keys($this->param['cp_platform']));
        $data = [];
        foreach ($platformIdArr as $platformId) {
            $this->pid = $platformId;
            $this->query();
            $data[] = Json::decode($this->onLine());
        }
        $avg_online = $max_online = 0;
        foreach ($data as $d) {
            if ($d['code'] == self::SUCCESS) {
                $avg_online += $d['data']['avg_online'];
                $max_online += $d['data']['max_online'];
            }
        }

        return ['avg_online' => $avg_online, 'max_online' => $max_online, 'gKey' => $this->gKey];
    }
}