<?php

namespace DagaSmart\Scheduling\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use DagaSmart\Scheduling\Services\SchedulingService;
use DagaSmart\Scheduling\Support\Scheduling;
use DagaSmart\BizAdmin\Controllers\AdminController;
use DagaSmart\BizAdmin\Renderers\Action;
use DagaSmart\BizAdmin\Renderers\Button;
use DagaSmart\BizAdmin\Renderers\Code;
use DagaSmart\BizAdmin\Renderers\CRUDTable;
use DagaSmart\BizAdmin\Renderers\Dialog;
use DagaSmart\BizAdmin\Renderers\Html;
use DagaSmart\BizAdmin\Renderers\Operation;
use DagaSmart\BizAdmin\Renderers\TableColumn;
use DagaSmart\BizAdmin\Renderers\Tag;
use DagaSmart\BizAdmin\Renderers\TagControl;
use Symfony\Component\Process\Process;

class SchedulingController extends AdminController
{

    protected $serviceName;

    public function __construct()
    {
        $this->pageTitle   = '任务调度';
        $this->serviceName = SchedulingService::class;

        parent::__construct();
    }
//    public function form(){
//
//    }
    public function list()
    {
        $crud=CRUDTable::make()->perPage(20)
            ->affixHeader(false)
            ->filterTogglable(false)
            ->filterDefaultVisible(false)
//            ->title('任务调度')
            ->api($this->getListGetDataPath())->columns(
                [
                    TableColumn::make()->name('index')->label('Index')->tpl('${index+1}'),
                    TableColumn::make()->name('task.name')->label('Task'),
                    TableColumn::make()->name('task.name')->label('Task')->type('tpl')->tpl('<span class="label label-info">${task.type}</span>'),
                    TableColumn::make()->name('expression')->label('Expression'),
                    TableColumn::make()->name('readable')->label('说明'),
                    TableColumn::make()->name('nextRunDate')->label('下次运行时间'),
                    TableColumn::make()->name('description')->label('介绍'),
                    Operation::make()->label('运行')->buttons([
                            Button::make()->label('运行')->actionType('ajax')->confirmText('是否运行这个任务')
                            ->api('/scheduling/run?id=${index+1}')
                            ->feedback(
                                Dialog::make()->title('操作提示')->size('xl')->closeOnOutside()->body('<code>${LINEBREAK(outval)}</code>')
                            )
                            ->messages(['failed'=>'运行失败了\${event.data.responseResult|json}']),
                        ]
                    )
                ]);
        return $this->baseList($crud);
    }

    public function runEvent(Request $request)
    {
        try {
            $scheduling = new Scheduling();
            $output = $scheduling->runTask($request->get('id'));
//            return $this->autoResponse(true,$output);
//            $uuid = Str::uuid()->toString();
//            Cache::set($uuid, $output, 30);
//            $outval=str_replace(['\r\n'],['<br>'],$output??'');
            return $this->response()->success(['outval'=>$output??''],'请求成功');
        } catch (\Exception $e) {
            return $this->response()->fail('运行出错了',$e->getMessage());
        }
    }
}
