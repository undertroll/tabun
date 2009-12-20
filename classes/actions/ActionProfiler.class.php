<?php
/*-------------------------------------------------------
*
*   LiveStreet Engine Social Networking
*   Copyright © 2008 Mzhelskiy Maxim
*
*--------------------------------------------------------
*
*   Official site: www.livestreet.ru
*   Contact e-mail: rus.engine@gmail.com
*
*   GNU General Public License, version 2:
*   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*
---------------------------------------------------------
*/

/**
 * Обрабатывает вывод отчетов профилирования
 *
 */
class ActionProfiler extends Action {
	/**
	 * Текущий юзер
	 *
	 * @var unknown_type
	 */
	protected $oUserCurrent=null;
		
	/**
	 * Инициализация 
	 *
	 * @return unknown
	 */
	public function Init() {	
		/**
		 * Проверяем авторизован ли юзер
		 */
		if (!$this->User_IsAuthorization()) {
			$this->Message_AddErrorSingle($this->Lang_Get('not_access'));
			return Router::Action('error'); 
		}
		/**
		 * Получаем текущего юзера
		 */
		$this->oUserCurrent=$this->User_GetUserCurrent();
		/**
		 * Проверяем является ли юзер администратором
		 */
		if (!$this->oUserCurrent->isAdministrator()) {
			$this->Message_AddErrorSingle($this->Lang_Get('not_access'));
			return Router::Action('error');
		}
		
		$this->SetDefaultEvent('report');	
	}
	
	protected function RegisterEvent() {		
		$this->AddEvent('report','EventReport');
		$this->AddEvent('ajaxloadreport','EventAjaxLoadReport');
		$this->AddEvent('ajaxloadentriesbyfilter','EventAjaxLoadEntriesByFilter');
	}
		
	
	/**********************************************************************************
	 ************************ РЕАЛИЗАЦИЯ ЭКШЕНА ***************************************
	 **********************************************************************************
	 */
	
	protected function EventReport() {				
		/**
		 * Обработка удаления отчетов профайлера
		 */
		if (isPost('submit_report_delete')) {
			$this->Security_ValidateSendForm();
			
			$aReportsId=getRequest('report_del');
			if (is_array($aReportsId)) {
				if($this->Profiler_DeleteEntryByRequestId(array_keys($aReportsId))) {
					$this->Message_AddNotice($this->Lang_Get('profiler_report_delete_success'), $this->Lang_Get('attention'));
				} else {
					$this->Message_AddError($this->Lang_Get('profiler_report_delete_error'), $this->Lang_Get('error'));
				}
			}
		}
		
		/**
		 * Если вызвана обработка upload`а логов в базу данных
		 */
		if(getRequest('submit_profiler_import') and getRequest('profiler_date_import')) {			
			$iCount = @$this->Profiler_UploadLog(date('Y-m-d H:i:s',strtotime(getRequest('profiler_date_import'))));
			if(!is_null($iCount)) {
				$this->Message_AddNotice($this->Lang_Get('profiler_import_report_success',array('count'=>$iCount)), $this->Lang_Get('attention'));
			} else {
				$this->Message_AddError($this->Lang_Get('profiler_import_report_error'), $this->Lang_Get('error'));
			}
		}
		
		/**
		 * Составляем фильтр для просмотра отчетов
		 */
		$aFilter=$this->BuildFilter();
		
		/**
		 * Передан ли номер страницы
		 */
		$iPage=preg_match("/^page(\d+)$/i",$this->getParam(0),$aMatch) ? $aMatch[1] : 1;				
		/**
		 * Получаем список отчетов
		 */		
		$aResult=$this->Profiler_GetReportsByFilter($aFilter,$iPage,Config::Get('module.profiler.per_page'));		
		$aReports=$aResult['collection'];
		
		/**
		 * Формируем постраничность
		 */
		$aPaging=$this->Viewer_MakePaging(
			$aResult['count'],$iPage,Config::Get('module.profiler.per_page'),4,
			Router::GetPath('profiler').$this->sCurrentEvent,
			array_intersect_key(
				$_REQUEST,
				array_fill_keys(
					array('start','end','request_id','time_full'),
					''
				)
			)
		);
		
		/**
		 * Загружаем переменные в шаблон
		 */
		$this->Viewer_Assign('aPaging',$aPaging);
		$this->Viewer_Assign('aReports',$aReports);
		$this->Viewer_Assign('aDatabaseStat',($aData=$this->Profiler_GetDatabaseStat())?$aData:array('max_date'=>'','count'=>''));		
		$this->Viewer_AddBlock('right','actions/ActionProfiler/sidebar.tpl');
		$this->Viewer_AddHtmlTitle($this->Lang_Get('profiler_report_page_title'));
	}
	
	/**
	 * Формирует из REQUEST массива фильтр для отбора отчетов
	 *
	 * @return array
	 */
	protected function BuildFilter() {
		return array();
	}
	
	/**
	 * Подгрузка данных одного профиля по ajax-запросу
	 *
	 * @return 
	 */
	protected function EventAjaxLoadReport() {
		$this->Viewer_SetResponseAjax();
		
		$sReportId=str_replace('report_','',getRequest('reportId',null,'post'));
		$bTreeView=getRequest('bTreeView',false,'post');
		$sParentId=getRequest('parentId',null,'post');
		
		$oViewerLocal=$this->Viewer_GetLocalViewer();
		$oViewerLocal->Assign('oReport',$this->Profiler_GetReportById($sReportId,$sParentId));
		if(!$sParentId) $oViewerLocal->Assign('aStat',$this->Profiler_GetReportStatById($sReportId));
		if(!$sParentId) $oViewerLocal->Assign('sAction','tree');
		
		$sTemplateName = ($bTreeView)
			? (($sParentId) 
				? 'level' 
				: 'tree')
			:'report';
		$this->Viewer_AssignAjax('sReportText',$oViewerLocal->Fetch("actions/ActionProfiler/ajax/{$sTemplateName}.tpl"));
	}

	/**
	 * Подгрузка данных одного профиля по ajax-запросу
	 *
	 * @return 
	 */	
	protected function EventAjaxLoadEntriesByFilter() {
		$this->Viewer_SetResponseAjax();
		
		$sAction = $this->GetParam(0);
		$sReportId=str_replace('report_','',getRequest('reportId',null,'post'));

		$oViewerLocal=$this->Viewer_GetLocalViewer();
		$oViewerLocal->Assign('aStat',$this->Profiler_GetReportStatById($sReportId));
		$oViewerLocal->Assign('sAction',$sAction);
		
		$oReport = $this->Profiler_GetReportById($sReportId,($sAction=='tree')?0:null);
		
		/**
		 * Преобразуем report взависимости от выбранного фильтра
		 */
		switch ($sAction) {
			case 'query':
				$oReport->setAllEntries($oReport->getEntriesByName('query'));
				break;
		}
				var_dump($oReport);
		$oViewerLocal->Assign('oReport',$oReport);
		
		$sTemplateName=($sAction=='tree')?'tree':'report';
		$this->Viewer_AssignAjax('sReportText',$oViewerLocal->Fetch("actions/ActionProfiler/ajax/{$sTemplateName}.tpl"));
	}
	
	/**
	 * Завершение работы Action`a
	 *
	 */
	public function EventShutdown() {

	}
}
?>