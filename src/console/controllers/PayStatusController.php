<?php
namespace console\controllers;

use common\models\Payment;
use yii\console\Controller;

class PayStatusController extends Controller
{
    const FILE_PREFIX = 'tlzj_';

    public function actionRun($from = null, $to = null)
    {
        if ($from == null || $to == null) {
            $from = date('Y-m-d', strtotime('yesterday'));
            $to = date('Y-m-d', strtotime('now'));
        } else {
            $from = date('Y-m-d', strtotime($from));
            $to = date('Y-m-d', strtotime($to));
        }
        $this->operateByDay($from, $to);
    }

    protected function operateByDay($from, $to)
    {
        $day = (strtotime($to) - strtotime($from)) / 86400;

        for ($i = 0; $i < $day; $i++) {
            $f = date('Y-m-d', strtotime($from.$i.' day'));
            $t = date('Y-m-d', strtotime($from.($i + 1).' day'));
            $this->urlMix($f, $t);
            Payment::updateAllFlagByPlatform('2144', $f, $t);
            Payment::updateAllFlagByPlatform('6255', $f, $t);
        }
    }

    protected function urlMix($from, $to)
    {
        $data = $this->getDataFromLogFile($from);
        $list = explode(PHP_EOL, $data);
        foreach ($list as $line) {
            if (empty($line)) {
                continue;
            }
            $obj = json_decode($line);
            $result = Payment::updateFlag($obj->data->platfrom, $obj->data->order_id, $obj->errno);
            if ($result) {
                $this->stdout('update ID: '.$result.PHP_EOL);
            }
        }

    }

    protected function getDataFromLogFile($date)
    {
        $fileName = self::FILE_PREFIX.date('Ymd', strtotime($date)).'.log';
        $filePath = 'http://tulong.gateway.2144.cn/log/';
        $file = $filePath.$fileName;
        $data = @file_get_contents($file);
        if (empty($data)) {
            $this->stdout($file.' is no exist'.PHP_EOL);

            return null;
        } else {
            return $data;
        }
    }

    protected function update_2144()
    {

    }
}
