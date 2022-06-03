<form>
    <row>
        <column>
            <header image_field='image_id' />
        </column>
    </row>
    <row>
        <column>
            <row showif="editMode=true">
                <column>
                    <field name='name' hidelabel="true" class='headline'></field>
                </column>
            </row>
            <row showif="editMode=true">
                <column>
                    <field name='notes' multiline='true'></field>
                    <all_additional></all_additional>
                </column>
            </row>
            <row>
                <tabs>
                    <tab name='Tasks'>
                        <objectsref obj_type='task' ref_field='project' view_id='all_incomplete_tasks'></objectsref>
                    </tab>

                    <tab name='Discussions'>
                        <objectsref obj_type='discussion'></objectsref>
                    </tab>

                    <tab name='Milestones'>
                        <objectsref obj_type='project_milestone' ref_field='project_id'></objectsref>
                    </tab>

                    <tab name='Files'>
                        <attachments></attachments>
                    </tab>
                    <tab name='Members'>
                        <members name='Members' field='members'></members>
                    </tab>
                </tabs>
            </row>
        </column>
        <column type="sidebar">
            <row>
                <column>
                    <fieldset name='Details'>
                        <field name='priority'></field>
                        <field name='parent' tooltip='A project can be a child of much larger projects which allows for smaller teams working on massive projects. <br/>This is not a commonly used feature, few projects are of that scale; but if you find a project has too much noise from all the people <br/>and activity it might be helpful to split out subprojects and make smaller teams.'></field>
                        <field name='owner_id' tooltip='Each project must have one responisble owner even though many members may be working on the project.'></field>
                        <field name='customer_id'></field>
                        <field name='date_started'></field>
                        <field name='date_deadline' tooltip='If no deadline is set, this will be considered an ongoing project.'></field>
                        <field name='date_completed' tooltip='Once the project has been completed, enter the date here.'></field>
                        <field name='groups' hidelabel='true'></field>
                    </fieldset>
                </column>
            </row>
        </column>
    </row>
</form>