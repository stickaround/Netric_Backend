<field name='name' class='headline'></field>
<field name='status_id'></field>
<field name='deadline' tooltip='Optional due date for the completion of this task.'></field>
<recurrence></recurrence>
<field name='owner_id' tooltip='When creating a new task, this is by default assigned to you. However, you can assign new or existing tasks to someone else simply by changing the user. They will receive a notification when you first assign it to them, and you will be notified when the task is completed.'></field>
<field name='priority_id'></field>
<field name='type_id'></field>
<field name='project'></field>
<field name='milestone_id' ref_field='project_id' ref_this='project' ref_required='t' tooltip='Milestones can be added to projects to split subsets of tasks into shorter periods. The task must first be a member of a project before being assigned to a milestone.'></field>
<field name='depends_task_id' tooltip='Optional task that needs to be completed before this task can be worked on.'></field>
<field name='cost_estimated' tooltip='Use this to track your effeciency. Enter the amount of time you estimate this task to take in hours. Use decimals to divide smaller intervals. For instance ".5" would be 30 minutes or 1/2 hour.'></field>
<field name='cost_actual' tooltip='This is automatically calculated as you work on the task. After saving changes, click "Log Time Spent" in the toolbar for this task to record how much actual time you spent on the task.'></field>
<field name='notes' hidelabel='true' multiline='true'></field>
<comments />