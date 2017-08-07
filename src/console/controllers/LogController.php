<?php
namespace console\controllers;

use console\models\LogTable;
use Kassner\LogParser\LogParser;
use Yii;
use yii\console\Controller;

class LogController extends Controller
{
    const FILE_NAME = 'tulong.gateway.2144.cn';
    const DIVIDING = '2017-07-22';
    const HISTORY = '2017-06-01';
    const OLDER = 1;
    const OLD = 2;
    const NEW = 3;

    //per 5 min run
    public function actionRun($from = null, $to = null)
    {
        if ($from == null || $to == null) {
            $f = strtotime('-5 minute');
            $t = strtotime('now');
            $from = date('Y-m', strtotime('now'));
            $to = date('Y-m-01', strtotime('+1 month'));
        } else {
            $f = strtotime($from);
            $t = strtotime($to);
            $from = date('Y-m', strtotime($from));
            $to = date('Y-m-01', strtotime($to.' +1 month'));
        }

      //记录日志
        $this->saveLog($from, $to, $f, $t);
    }

    protected function getLogUrl($old = self::NEW, $time = null, $i = 1)
    {
        if ($old == self::OLD) {
            $time = $time ?: strtotime('-1 days');
            $lastPoint = date('Ymd', $time - ($time + 28800) % 86400);
            $fileName = Yii::$app->params['logUrl'].self::FILE_NAME.'.log'.$lastPoint;
        } elseif ($old == self::NEW) {
            $time = $time ?: strtotime('-5 minutes');
            $lastPoint = date('YmdHi', $time - ($time + 28800) % 300);
            $fileName = Yii::$app->params['logUrl'].self::FILE_NAME.'.'.$lastPoint.'.log';
        } else {
            $fileName = Yii::$app->params['logUrl'].self::FILE_NAME.'_'.$i.'.log';
        }

        return $fileName;
    }

    //时间拆分多少间距
    protected function getSlave($f, $t, $dividing = 300) {
        $dividing = $dividing ?: 86400;
        $slave = ceil(($t - $f) / $dividing);

        return $slave;
    }

    protected function parserLog($from, $to)
    {
        $parser = new LogParser();
        $parser->addPattern('%_hv', '(?P<httpVersion>HTTP/(?:1|2).(?:0|1)|-|)');
        $parser->addPattern('%_pd', '(?P<postData>.*)');
//        $parser->addPattern('%_p', '(?P<XFF>(\d+\.\d+\.\d+\.\d+(, )?(,)?(, unknown)?)+|\-|(unknown(, )?(,)?)+)');
        $parser->addPattern('%_p', '(?P<XFF>.*)');
//        $parser->addPattern('%_p', '(?P<XFF>'.$ipPatterns.'|\-|unknown)');
        if (strtotime($to) <= strtotime(self::HISTORY)) {
            $parser->setFormat('%h %l %u %t "%m %U %_hv" %>s %O "%{Referer}i" \"%{User-Agent}i" "%_p"');
        } elseif(strtotime($from) < strtotime(self::HISTORY)) {
            $this->stdout('请在2017-06-01前后分段输入日期'.PHP_EOL);
            Yii::$app->end();
        } else {
            $parser->setFormat('%h %l %u %t "%m %U %_hv"%>s %O "%{Referer}i" \"%{User-Agent}i" "%_p" "%_pd"');
        }

        return $parser;
    }

    protected function saveData($from, $to, $lines)
    {
        foreach ($lines as $line) {
            $entry = $this->parserLog($from, $to)->parse($line);
            if ($entry->stamp >= strtotime($from) && $entry->stamp < strtotime($to)) {
                //添加表
                LogTable::newTable($entry->stamp);
                try {
                    $log = new LogTable();
                    LogTable::$month = date('Ym', $entry->stamp);
                    if ($obj = $log->findUnique($entry)) {
                        $this->stdout('ID: '.$obj->id.' 已存在'.PHP_EOL);
                        continue;
                    }
                    if ($id = $log->saveData($entry)) {
                        $this->stdout(LogTable::$month.'添加成功 ID: '.$id.PHP_EOL);
                    }
                } catch (\Exception $e) {
                    $this->stderr($e->getMessage().PHP_EOL);
                }
            }
        }
    }

    protected function saveLog($from, $to, $f, $t)
    {
        //3,4月
        if ($f <= strtotime(self::HISTORY)){
            for ($i = 1; $i< 400; $i++) {
                $logUrl = $this->getLogUrl(self::OLDER, '', $i);
                $data = $this->findFile($logUrl);
                if(!$data) {
                    continue;
                }
                $this->saveData($from, $to, $data);
                unset($data);
            }
        }
        //6,7月
        $count = $this->getSlave($f, $t, 86400);
        for ($i = 1; $i <= $count; $i++) {
            $date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s', $f).$i.' day'));
            if (strtotime($date) <= strtotime(self::DIVIDING)) {
                $logUrl = $this->getLogUrl(self::OLD, strtotime($date));
                $data = $this->findFile($logUrl);
                if(!$data) {
                    continue;
                }
                $this->saveData($from, $to, $data);
                unset($data);
            }
        }
        //7月以后
        $count = $this->getSlave($f, $t, 300);
        for ($i = 1; $i <= $count; $i++) {
            $date = date('Y-m-d H:i', strtotime(date('Y-m-d H:i', $f).($i * 5)." minute"));
            if (strtotime($date) >= strtotime(self::DIVIDING)) {
                $logUrl = $this->getLogUrl(self::NEW, strtotime($date));
                $data = $this->findFile($logUrl);
                if(!$data) {
                    continue;
                }
                $this->saveData($from, $to, $data);
                unset($data);
            }
        }
    }

    protected function findFile($logUrl)
    {
        $lines = @file($logUrl, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            $this->stderr('not found: '.$logUrl.PHP_EOL);

            return false;
        }
        $this->stdout($logUrl.PHP_EOL);

        return $lines;
    }
}