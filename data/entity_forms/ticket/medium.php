<tabs>
    <tab name='General'>
        <row>
            <column>
                <field name='name' hidelabel="true" class='headline'></field>
            </column>
        </row>
        <fieldset name='Details'>
            <row>
                <column>
                    <field name='status_id'></field>
                    <field name='priority_id'></field>
                    <field name='source_id'></field>
                    <recurrence></recurrence>
                </column>
                <column>
                    <field name='owner_id' tooltip='When creating a new task, this is by default assigned to you. However, you can assign new or existing tasks to someone else simply by changing the user. They will receive a notification when you first assign it to them, and you will be notified when the task is completed.'></field>
                </column>
                <column>
                    <field name='contact_id'></field>
                </column>
            </row>
        </fieldset>
        <row>
            <column>
                <attachments></attachments>
            </column>
        </row>
        <row>
            <column>
                <field name='description' hidelabel='true' multiline='true'></field>
            </column>
        </row>
        <row>
            <column>
                <comments allowpublic="true" />
            </column>
        </row>
    </tab>

    <tab name='Activity'>
        <field name='activity'></field>
    </tab>
</tabs>