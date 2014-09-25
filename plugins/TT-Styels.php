<?php
addCSS ('css/pure-min.css');
addCSS ('css/style.css');
	
function renderTabBar ($pageno, $tabno)
{
	global $tab, $page, $trigger;
	if (!isset ($tab[$pageno]['default']))
		return;
	
//	echo "<div class=greynavbar><ul id=foldertab style='margin-bottom: 0px; padding-top: 45px;'>";
	echo '<div class="pure-menu pure-menu-open pure-menu-horizontal tabbar"><ul>';
	foreach ($tab[$pageno] as $tabidx => $tabtitle)
	{
		// Hide forbidden tabs.
		if (!permitted ($pageno, $tabidx))
			continue;
		// Dynamic tabs should only be shown in certain cases (trigger exists and returns true).
		if (!isset ($trigger[$pageno][$tabidx]))
			$tabclass = 'std';
		elseif (!strlen ($tabclass = call_user_func ($trigger[$pageno][$tabidx])))
			continue;
		if ($tabidx == $tabno)
			$tabclass = 'selected'; // override any class for an active selection
		echo '<li class="tab '.$tabclass.'"><a';
		echo " href='index.php?page=${pageno}&tab=${tabidx}";
		$args = array();
		fillBypassValues ($pageno, $args);
		foreach ($args as $param_name => $param_value)
			echo "&" . urlencode ($param_name) . '=' . urlencode ($param_value);

		echo "'>${tabtitle}</a></li>\n";
	}
	echo "</ul></div>";
}

?>