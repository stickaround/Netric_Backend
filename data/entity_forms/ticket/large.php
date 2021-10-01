<row>
    <column>
        <field name='name' hidelabel="t" class='headline'></field>
    </column>
</row>
<row>
    <column>
        <row>
            <column>
                <fieldset name="Details">
                    <row>
                        <column>
                            <field name='status_id'></field>
                            <field name='priority_id'></field>
                            <field name='source_id'></field>
                        </column>
                        <column>
                            <field name='creator_id'></field>
                            <field name='contact_id' tooltip='Optional task that needs to be completed before this task can be worked on.'></field>
                        </column>
                    </row>
                </fieldset>
            </column>
        </row>
        <row>
            <column>
                <fieldset name="Notes">
                    <field name='description' hidelabel='t' multiline='t'></field>
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
                <column>
                    <comments />
                </column>
            </column>
        </row>
    </column>
    <column type="sidebar">
        <fieldset name="People">
            <row>
                <column>
                    <field name='owner_id' tooltip='When creating a new task, this is by default assigned to you. However, you can assign new or existing tasks to someone else simply by changing the user. They will receive a notification when you first assign it to them, and you will be notified when the task is completed.'></field>
                    <field name='contact_id'></field>
                    <field name='creator_id'></field>
                </column>
            </row>
        </fieldset>
    </column>
</row>