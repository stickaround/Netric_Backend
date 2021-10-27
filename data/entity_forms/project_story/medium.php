<tabs>
    <tab name='General'>
        <row>
            <column>
                <field class='headline' hidelabel='t' name='name'></field>
            </column>
        </row>
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
                <field name='owner_id'></field>
                <field name='ts_updated'></field>
                <field name='ts_entered'></field>
            </column>
        </row>
        <row>
            <all_additional></all_additional>
        </row>
        <row>
            <column>
                <attachments></attachments>
            </column>
        </row>
        <row>
            <column>
                <field name='notes' hidelabel='t' multiline='true'></field>
            </column>
        </row>
        <row>
            <column>
                <field name='comments'></field>
            </column>
        </row>
    </tab>
    <tab name='Activity'>
        <field name='activity'></field>
    </tab>
</tabs>