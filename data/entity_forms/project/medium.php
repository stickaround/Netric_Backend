<tabs>
    <tab name='General'>
        <row>
            <column>
                <field name='image_id' profile_image='t'></field>
            </column>
        </row>
        <row>
            <column>
                <field name='name' hidelabel="true" class='headline'></field>
            </column>
        </row>
        <fieldset name='Details'>
            <row>
                <column>
                    <field name='priority'></field>
                    <field name='parent' tooltip='A project can be a child of much larger projects which allows for smaller teams working on massive projects. <br/>This is not a commonly used feature, few projects are of that scale; but if you find a project has too much noise from all the people and <br/>activity it might be helpful to split out subprojects and make smaller teams.'></field>
                    <field name='owner_id' tooltip='Each project must have one responisble owner even though many members may be working on the project.'></field>
                    <field name='customer_id'></field>
                </column>
                <column>
                    <field name='date_started'></field>
                    <field name='date_deadline' tooltip='If no deadline is set, this will be considered an ongoing project.'></field>
                    <field name='date_completed' tooltip='Once the project has been completed, enter the date here.'></field>
                    <field name='groups' hidelabel='true'></field>
                </column>
            </row>
        </fieldset>
        <fieldset name='Description'>
            <row>
                <column>
                    <field name='notes' multiline='true'></field>
                </column>
            </row>
        </fieldset>
        <fieldset name='Tasks'>
            <objectsref obj_type='task' ref_field='project'></objectsref>
        </fieldset>
    </tab>

    <tab name='Status Update'>
        <row>
            <column>
                <wall />
            </column>
        </row>
        <row>
            <column>
                <field name='activity'></field>
            </column>
        </row>
    </tab>

    <tab name='Files'>
        <attachments></attachments>
    </tab>
</tabs>