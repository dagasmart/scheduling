<?php

namespace DagaSmart\Scheduling\Services;

use DagaSmart\Scheduling\Support\Scheduling;
use DagaSmart\BizAdmin\Services\AdminService;
use DagaSmart\BizAdmin\Traits\ErrorTrait;

class SchedulingService extends AdminService
{

    use ErrorTrait;
    protected string $modelName;

    public function list()
    {
        $scheduling=new Scheduling();
        try {
            $items = $scheduling->getTasks();
        } catch (\Exception $e) {

        }
        $total = count($items);

        return compact('items', 'total');
    }


}
