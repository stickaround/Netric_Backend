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
                            <field name='type_id'></field>
                            <field name='stage_id'></field>
                            <field name='lead_source_id'></field>
                            <field name='campaign_id'></field>
                        </column>
                        <column>
                            <field name='amount'></field>
                            <field name='probability_per'></field>
                            <field name='objection_id'></field>
                        </column>
                    </row>
                </fieldset>
            </column>
        </row>

        <row>
            <column>
                <fieldset name="Notes">
                    <field name='notes' hidelabel='true' multiline='true'></field>
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
            <tabs>
                <tab name='Comments'>
                    <field name='comments'></field>
                </tab>

                <tab name='Activity'>
                    <field name='activity'></field>
                </tab>

                <tab name='Task'>
                    <objectsref obj_type='task' ref_field='customer_id'></objectsref>
                </tab>

                <tab name='Events'>
                    <objectsref obj_type='calendar_event'></objectsref>
                </tab>
            </tabs>
        </row>
    </column>
    <column type="sidebar">
        <fieldset name="People">
            <row>
                <column>
                    <field name='customer_id'></field>

                    <field name='owner_id'></field>
                    <field name='creator_id'></field>
                </column>
            </row>
        </fieldset>
        <fieldset name="Dates">
            <row>
                <column>
                    <field name='expected_close_date'></field>
                    <field name='ts_entered'></field>
                    <field name='ts_updated'></field>
                </column>
            </row>
        </fieldset>
    </column>
</row>