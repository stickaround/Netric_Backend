<fieldset name='Details'>
	<field name='name'></field>
	<field name='done'></field>
	<field name='deadline'></field>
	<recurrence></recurrence>
	<field name='user_id'></field>
	<field name='priority'></field>
	<field name='project'></field>
	<field name='milestone_id' ref_field='project_id' ref_this='project' ref_required='t'></field>
	<field name='story_id' ref_field='project_id' ref_this='project' ref_required='t'></field>
	<field name='depends_task_id'></field>
	<field name='cost_estimated'></field>
	<field name='cost_actual'></field>
	<all_additional></all_additional>
</fieldset>
<fieldset name='Description'>
	<field name='notes' hidelabel='t' multiline='t'></field>
</fieldset>
<fieldset name='Comments'>
	<field name='comments'></field>
</fieldset>
<plugin name='logtime'></plugin>
