{if $oReport}
	<a href="#" class="profiler tree {if $sAction=='tree'}active{/if}" onclick="lsProfiler.toggleEntriesByClass('{$oReport->getId()}','tree',this); return false;">{$aLang.profiler_entries_show_tree}</a> 
	<a href="#" class="profiler all {if $sAction=='all'}active{/if}" onclick="lsProfiler.toggleEntriesByClass('{$oReport->getId()}','all',this); return false;">{$aLang.profiler_entries_show_all} ({$aStat.count})</a> 
	<a href="#" class="profiler query {if $sAction=='query'}active{/if}"  onclick="lsProfiler.toggleEntriesByClass('{$oReport->getId()}','query',this); return false;">{$aLang.profiler_entries_show_query} ({$aStat.query})</a>

	<div class="profiler-table">
		<table class="profiler entries">
			{foreach from=$oReport->getAllEntries() item=oEntry}
			<tr class="entry_{$oReport->getId()}_all entry_{$oReport->getId()}_{$oEntry->getName()}{if $oEntry->getChildCount()!=0} child{/if}">
				<td width="6%">{$oEntry->getId()}</td>
				<td width="15%">{$oEntry->getName()}</td>
				<td width="12%">{$oEntry->getTimeFull()}</td>
				<td>{$oEntry->getComment()}</td>
			</tr>
			{/foreach}
		</table>
	</div>
{else}
	 {$aLang.error}
{/if}