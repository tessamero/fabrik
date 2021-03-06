<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.calendar
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * Fabrik Calendar HTML View
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.calendar
 */

class fabrikViewCalendar extends JView
{

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */

	public function display($tpl = 'default')
	{
		$app = JFactory::getApplication();
		$Itemid = (int) @$app->getMenu('site')->getActive()->id;
		$pluginManager = FabrikWorker::getPluginManager();

		// Needed to load the language file!
		$plugin = $pluginManager->getPlugIn('calendar', 'visualization');
		$model = $this->getModel();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$id = JRequest::getVar('id', $usersConfig->get('visualizationid', JRequest::getInt('visualizationid', 0)));
		$model->setId($id);
		$this->row = $model->getVisualization();
		$params = $model->getParams();
		$this->assign('params', $params);
		$this->assign('containerId', $model->getJSRenderContext());
		$this->assignRef('filters', $this->get('Filters'));
		$this->assign('showFilters', JRequest::getInt('showfilters', $params->get('show_filters')) === 1 ? 1 : 0);
		$this->assign('showTitle', JRequest::getInt('show-title', 1));
		$this->assign('filterFormURL', $this->get('FilterFormURL'));

		$calendar = $model->_row;

		$fbConfig = JComponentHelper::getParams('com_fabrik');
		JHTML::stylesheet('media/com_fabrik/css/list.css');
		$params = $model->getParams();

		$canAdd = $params->get('calendar-read-only', 0) == 1 ? 0 : $this->get('CanAdd');

		$this->assign('requiredFiltersFound', $this->get('RequiredFiltersFound'));
		if ($canAdd && $this->requiredFiltersFound)
		{
			$app->enqueueMessage(JText::_('PLG_VISUALIZATION_CALENDAR_DOUBLE_CLICK_TO_ADD'));
		}
		$this->assign('canAdd', $canAdd);

		$fbConfig = JComponentHelper::getParams('com_fabrik');
		JHTML::stylesheet('media/com_fabrik/css/list.css');
		$params = $model->getParams();

		// Get the active menu item
		$urlfilters = JRequest::get('get');
		unset($urlfilters['option']);
		unset($urlfilters['view']);
		unset($urlfilters['controller']);
		unset($urlfilters['Itemid']);
		unset($urlfilters['visualizationid']);
		unset($urlfilters['format']);
		if (empty($urlfilters))
		{
			$urlfilters = new stdClass;
		}
		$urls = new stdClass;

		// Don't JRoute as its wont load with sef?
		$urls->del = 'index.php?option=com_fabrik&controller=visualization.calendar&view=visualization&task=deleteEvent&format=raw&Itemid=' . $Itemid
			. '&id=' . $id;
		$urls->add = 'index.php?option=com_fabrik&view=visualization&format=raw&Itemid=' . $Itemid . '&id=' . $id;
		$user = JFactory::getUser();
		$legend = $params->get('show_calendar_legend', 0) ? $model->getLegend() : '';
		$tpl = $params->get('calendar_layout', 'default');
		$options = new stdClass;
		$options->url = $urls;
		$options->deleteables = $this->get('DeleteAccess');
		$options->eventLists = $this->get('eventLists');
		$options->calendarId = $calendar->id;
		$options->popwiny = $params->get('yoffset', 0);
		$options->urlfilters = $urlfilters;
		$options->canAdd = $canAdd;

		$options->restFilterStart = FabrikWorker::getMenuOrRequestVar('resetfilters', 0, false, 'request');
		$options->tmpl = $tpl;

		$o = $model->getAddStandardEventFormInfo();

		if ($o != null)
		{
			$options->listid = $o->id;
		}

		// $$$rob @TODO not sure this is need - it isnt in the timeline viz
		$model->setRequestFilters();
		$options->filters = $model->filters;

		// End not sure
		$options->Itemid = $Itemid;
		$options->show_day = (bool) $params->get('show_day', true);
		$options->show_week = (bool) $params->get('show_week', true);
		$options->days = array(JText::_('SUNDAY'), JText::_('MONDAY'), JText::_('TUESDAY'), JText::_('WEDNESDAY'), JText::_('THURSDAY'),
			JText::_('FRIDAY'), JText::_('SATURDAY'));
		$options->shortDays = array(JText::_('SUN'), JText::_('MON'), JText::_('TUE'), JText::_('WED'), JText::_('THU'), JText::_('FRI'),
			JText::_('SAT'));
		$options->months = array(JText::_('JANUARY'), JText::_('FEBRUARY'), JText::_('MARCH'), JText::_('APRIL'), JText::_('MAY'), JText::_('JUNE'),
			JText::_('JULY'), JText::_('AUGUST'), JText::_('SEPTEMBER'), JText::_('OCTOBER'), JText::_('NOVEMBER'), JText::_('DECEMBER'));
		$options->shortMonths = array(JText::_('JANUARY_SHORT'), JText::_('FEBRUARY_SHORT'), JText::_('MARCH_SHORT'), JText::_('APRIL_SHORT'),
			JText::_('MAY_SHORT'), JText::_('JUNE_SHORT'), JText::_('JULY_SHORT'), JText::_('AUGUST_SHORT'), JText::_('SEPTEMBER_SHORT'),
			JText::_('OCTOBER_SHORT'), JText::_('NOVEMBER_SHORT'), JText::_('DECEMBER_SHORT'));
		$options->first_week_day = (int) $params->get('first_week_day', 0);

		$options->monthday = new stdClass;
		$options->monthday->width = (int) $params->get('calendar-monthday-width', 90);
		$options->monthday->height = (int) $params->get('calendar-monthday-height', 80);
		$options->greyscaledweekend = $params->get('greyscaled-week-end', 0);
		$options->viewType = $params->get('calendar_default_view', 'monthView');

		$options->weekday = new stdClass;
		$options->weekday->width = (int) $params->get('calendar-weekday-width', 90);
		$options->weekday->height = (int) $params->get('calendar-weekday-height', 10);
		$options->open = (int) $params->get('open-hour', 0);
		$options->close = (int) $params->get('close-hour', 24);
		$options->showweekends = (bool) $params->get('calendar-show-weekends', true);
		$options->readonly = (bool) $params->get('calendar-read-only', false);

		$json = json_encode($options);

		JText::script('PLG_VISUALIZATION_CALENDAR_NEXT');
		JText::script('PLG_VISUALIZATION_CALENDAR_PREVIOUS');
		JText::script('PLG_VISUALIZATION_CALENDAR_DAY');
		JText::script('PLG_VISUALIZATION_CALENDAR_WEEK');
		JText::script('PLG_VISUALIZATION_CALENDAR_MONTH');
		JText::script('PLG_VISUALIZATION_CALENDAR_KEY');
		JText::script('PLG_VISUALIZATION_CALENDAR_TODAY');
		JText::script('PLG_VISUALIZATION_CALENDAR_CONF_DELETE');
		JText::script('PLG_VISUALIZATION_CALENDAR_DELETE');
		JText::script('PLG_VISUALIZATION_CALENDAR_VIEW');
		JText::script('PLG_VISUALIZATION_CALENDAR_EDIT');
		JText::script('PLG_VISUALIZATION_CALENDAR_ADD_EDIT_EVENT');

		$ref = $model->getJSRenderContext();
		$js = " $ref = new fabrikCalendar('$ref');\n";
		$js .= " $ref.render($json);\n";
		$js .= "  Fabrik.addBlock('" . $ref . "', $ref);\n";
		$js .= $legend . "\n";
		$js .= $model->getFilterJs();

		$srcs = FabrikHelperHTML::framework();
		$srcs[] = 'media/com_fabrik/js/listfilter.js';
		$srcs[] = 'plugins/fabrik_visualization/calendar/calendar.js';
		FabrikHelperHTML::script($srcs, $js);

		$viewName = $this->getName();

		$pluginParams = $model->getPluginParams();
		$this->assignRef('params', $model->getParams());
		$tpl = $pluginParams->get('calendar_layout', $tpl);
		$tmplpath = JPATH_ROOT . '/plugins/fabrik_visualization/calendar/views/calendar/tmpl/' . $tpl;
		$this->_setPath('template', $tmplpath);

		$ab_css_file = $tmplpath . '/template.css';

		if (JFile::exists($ab_css_file))
		{
			JHTML::stylesheet('plugins/fabrik_visualization/calendar/views/calendar/tmpl/' . $tpl . '/template.css');
		}
		return parent::display();
	}

	function chooseaddevent()
	{
		$view->_layout = 'chooseaddevent';
		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');
		$plugin = $pluginManager->getPlugIn('calendar', 'visualization');
		$model = $this->getModel();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model->setId(JRequest::getVar('id', $usersConfig->get('visualizationid', JRequest::getInt('visualizationid', 0))));
		$rows = $model->getEventLists();
		$o = $model->getAddStandardEventFormInfo();
		$options = array();
		$options[] = JHTML::_('select.option', '', JText::_('PLG_VISUALIZATION_CALENDAR_PLEASE_SELECT'));
		if ($o != null)
		{
			$listid = $o->id;
			$options[] = JHTML::_('select.option', $listid, JText::_('PLG_VISUALIZATION_CALENDAR_STANDARD_EVENT'));
		}
		$model->getEvents();
		$config = JFactory::getConfig();
		$prefix = $config->getValue('config.dbprefix');
		$this->_eventTypeDd = JHTML::_('select.genericlist', array_merge($options, $rows), 'event_type', 'class="inputbox" size="1" ', 'value',
			'text', '', 'fabrik_event_type');

		/*tried loading in iframe and as an ajax request directly - however
		 *in the end decided to set a call back to the main calendar object (via the package manager)
		 * to load up the new add event form
		 */
		$script = "head.ready(function() {
			//oCalendar" . $model->getId()
			. ".addListenTo('chooseeventwin');
		$('fabrik_event_type').addEvent('change', function(e) {
		var fid = $(e.target).get('value');
		var o = ({'d':'','listid':fid,'rowid':0});
		o.datefield = '{$prefix}fabrik_calendar_events___start_date';
		o.datefield2 = '{$prefix}fabrik_calendar_events___end_date';
		o.labelfield = '{$prefix}fabrik_calendar_events___label';
		";
		foreach ($model->_events as $tid => $arr)
		{
			foreach ($arr as $ar)
			{
				$script .= "if(" . $ar['formid'] . " == fid)	{\n";
				$script .= "o.datefield = '" . $ar['startdate'] . "'\n";
				$script .= "o.datefield2 = '" . $ar['enddate'] . "'\n";
				$script .= "o.labelfield = '" . $ar['label'] . "'\n";
				$script .= "}\n";
			}
		}
		$script .= "var o = JSON.encode(o);
		// @TODO deal with this via window.fireEvent()
			//oPackage.sendMessage('chooseeventwin', 'addEvForm' , true, o);

	});
	});
	";
		FabrikHelperHTML::addScriptDeclaration($script);
		echo '<h2>' . JText::_('PLG_VISUALIZATION_CALENDAR_PLEASE_CHOOSE_AN_EVENT_TYPE') . ':</h2>';
		echo $this->_eventTypeDd;
	}
}
