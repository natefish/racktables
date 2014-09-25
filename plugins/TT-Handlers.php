<?php
registerTabHandler('object', 'edit', 'renderTtEditObjectForm','replace');

function renderTtEditObjectForm()
{
	global $pageno;
	$object_id = getBypassValue();
	$object = spotEntity ('object', $object_id);
	echo $object['type'];
	startPortlet ();
	printOpFormIntro ('update');

	// static attributes
	echo '<table border=0 cellspacing=0 cellpadding=3 align=center>';
	echo "<tr><td>&nbsp;</td><th colspan=2><h2> Attributes</h2></th></tr>";
	
	echo '<tr><td colspan=3>';
	echo '	<div class="pure-control-group">';
    echo '		<label for="object_type">Type:</label>';
	echo '		<input id="object_type" type="text" value="'.getRackObjectType(getObjectTypeChangeOptions ($object['id']), array ('name' => 'object_type_id'), $object['objtype_id']).'" readonly>';
	echo '	</div>';
	echo '	<div class="pure-control-group">';
    echo '		<label for="object_name">Common name:</label>';
	echo '		<input id="object_name" type="text" value="'.$object['name'].'">';
	echo '	</div>';
	echo '	<div class="pure-control-group">';
    echo '		<label for="object_label">Visible label:</label>';
	echo '		<input id="object_label" type="text" value="'.$object['label'].'">';
	echo '	</div>';
	echo '	<div class="pure-control-group">';
    echo '		<label for="object_asset_no">Asset tag:</label>';
	echo '		<input id="object_asset_no" type="text" value="'.$object['asset_no'].'">';
	echo '	</div>';
	echo '</td></tr>';
	
	/*
	echo '<tr><td>&nbsp;</td><th class=tdright>Type:</th><td class=tdleft>';
	printSelect (getObjectTypeChangeOptions ($object['id']), array ('name' => 'object_type_id'), $object['objtype_id']);
	echo '</td></tr>';
	// baseline info
	echo "<tr><td>&nbsp;</td><th class=tdright>Common name:</th><td class=tdleft><input type=text name=object_name value='${object['name']}'></td></tr>\n";
	echo "<tr><td>&nbsp;</td><th class=tdright>Visible label:</th><td class=tdleft><input type=text name=object_label value='${object['label']}'></td></tr>\n";
	echo "<tr><td>&nbsp;</td><th class=tdright>Asset tag:</th><td class=tdleft><input type=text name=object_asset_no value='${object['asset_no']}'></td></tr>\n";
	*/
	echo "<tr><td>&nbsp;</td><th class=tdright>Tags:</th><td class=tdleft>";
	printTagsPicker ();
	echo "</td></tr>\n";
	// parent selection
	if (objectTypeMayHaveParent ($object['objtype_id']))
	{
		$parents = getEntityRelatives ('parents', 'object', $object_id);
		foreach ($parents as $link_id => $parent_details)
		{
			if (!isset($label))
				$label = count($parents) > 1 ? 'Containers:' : 'Container:';
			echo "<tr><td>&nbsp;</td>";
			echo "<th class=tdright>${label}</th><td class=tdleft>";
			echo mkA ($parent_details['name'], 'object', $parent_details['entity_id']);
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			echo getOpLink (array('op'=>'unlinkObjects', 'link_id'=>$link_id), '', 'cut', 'Unlink container');
			echo "</td></tr>\n";
			$label = '&nbsp;';
		}
		echo "<tr><td>&nbsp;</td>";
		echo "<th class=tdright>Select container:</th><td class=tdleft>";
		echo "<span";
		$helper_args = array ('object_id' => $object_id);
		$popup_args = 'height=700, width=400, location=no, menubar=no, '.
			'resizable=yes, scrollbars=yes, status=no, titlebar=no, toolbar=no';
		echo " onclick='window.open(\"" . makeHrefForHelper ('objlist', $helper_args);
		echo "\",\"findlink\",\"${popup_args}\");'>";
		printImageHREF ('attach', 'Select a container');
		echo "</span></td></tr>\n";
	}
	// optional attributes
	$i = 0;
	$values = getAttrValuesSorted ($object_id);
	if (count($values) > 0)
	{
		foreach ($values as $record)
		{
			if (! permitted (NULL, NULL, NULL, array (
				array ('tag' => '$attr_' . $record['id']),
				array ('tag' => '$any_op'),
			)))
				continue;
			echo "<input type=hidden name=${i}_attr_id value=${record['id']}>";
			echo '<tr><td>';
			if (strlen ($record['value']))
				echo getOpLink (array('op'=>'clearSticker', 'attr_id'=>$record['id']), '', 'clear', 'Clear value', 'need-confirmation');
			else
				echo '&nbsp;';
			echo '</td>';
			echo "<th class=sticker>${record['name']}";
			if ($record['type'] == 'date')
				echo ' (' . datetimeFormatHint (getConfigVar ('DATETIME_FORMAT')) . ')';
			echo ':</th><td class=tdleft>';
			switch ($record['type'])
			{
				case 'uint':
				case 'float':
				case 'string':
					echo "<input type=text name=${i}_value value='${record['value']}'>";
					break;
				case 'dict':
					$chapter = readChapter ($record['chapter_id'], 'o');
					//tt branch modification
					$chapter[0] = array('value' => '-- NOT SET --','display' => 'yes');
					$chapter = cookOptgroups ($chapter, $object['objtype_id'], $record['key']);
					printNiftySelect ($chapter, array ('name' => "${i}_value"), $record['key']);
					break;
				case 'date':
					$date_value = $record['value'] ? datetimestrFromTimestamp ($record['value']) : '';
					echo "<input type=text name=${i}_value value='${date_value}'>";
					break;
			}
			echo "</td></tr>\n";
			$i++;
		}
	}
	echo '<input type=hidden name=num_attrs value=' . $i . ">\n";
	echo "<tr><td>&nbsp;</td><th class=tdright>Has problems:</th><td class=tdleft><input type=checkbox name=object_has_problems";
	if ($object['has_problems'] == 'yes')
		echo ' checked';
	echo "></td></tr>\n";
	echo "<tr><td>&nbsp;</td><th class=tdright>Actions:</th><td class=tdleft>";
	echo getOpLink (array ('op'=>'deleteObject', 'page'=>'depot', 'tab'=>'addmore', 'object_id'=>$object_id), '' ,'destroy', 'Delete object', 'need-confirmation');
	echo "&nbsp;";
	echo getOpLink (array ('op'=>'resetObject'), '' ,'clear', 'Reset (cleanup) object', 'need-confirmation');
	echo "</td></tr>\n";
	echo "<tr><td colspan=3><b>Comment:</b><br><textarea name=object_comment rows=10 cols=80>${object['comment']}</textarea></td></tr>";

	echo "<tr><th class=submit colspan=3>";
	printImageHREF ('SAVE', 'Save changes', TRUE);
	echo "</form></th></tr></table>\n";
	finishPortlet();

	echo '<table border=0 width=100%><tr><td>';
	startPortlet ('history');
	renderObjectHistory ($object_id);
	finishPortlet();
	echo '</td></tr></table>';
}

function getRackObjectType ($optionList, $select_attrs = array(), $selected_id = NULL)
{
	if (!array_key_exists ('name', $select_attrs))
		return 'missing';
	if (count ($optionList) == 0)
		return '(none)';
	foreach ($optionList as $key => $value)
		break;
	return $value;
}
?>