<row>
    <column>
        <field name='name' hidelabel="true" class='headline'></field>
    </column>
</row>
<row>
    <column>
        <row>
            <column>
                <fieldset name="Details">
                    <row>
                        <column>
                            <field name='priority_id'></field>
                            <field name='type_id'></field>
                            <field name='status_id'></field>
                            <field name='cost_estimated'></field>
                            <field name='cost_actual'></field>
                        </column>
                        <column>
                            <field name='project_id'></field>
                            <field name='milestone_id'></field>

                        </column>
                    </row>
                    <row>
                        <all_additional></all_additional>
                    </row>
                </fieldset>
            </column>
        </row>
        <row>
            <column>
                <fieldset name="Attachments">
                    <attachments></attachments>
                </fieldset>
            </column>
        </row>
        <row>
            <column>
                <fieldset name="Description">
                    <field name='notes' hidelabel='true' multiline='true'></field>
                </fieldset>
            </column>
        </row>
        <row>
            <column>
                <tabs>
                    <tab name='Comments'>
                        <column>
                            <field name='comments'></field>
                        </column>
                    </tab>
                    <tab name='Activity'>
                        <field name='activity'></field>
                    </tab>
                    <tab name='Tasks'>
                        <objectsref obj_type='task' ref_field='story_id'></objectsref>
                    </tab>
                </tabs>
            </column>
        </row>
    </column>
    <column type="sidebar">
        <fieldset name="People">
            <row>
                <column>
                    <field name='owner_id'></field>
                </column>
            </row>
        </fieldset>
        <fieldset name="Dates">
            <row>
                <column>
                    <field name='ts_updated'></field>
                    <field name='ts_entered'></field>
                </column>
            </row>
        </fieldset>
    </column>
</row>