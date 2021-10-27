<row>
    <column type="half">
        <row>
            <column type="half">
                <field name='image_id' hidelabel='true' profile_image='t'></field>
            </column>
            <column>
                <header field='full_name' />
            </column>
        </row>
        <row showif="editMode=false">
            <column>
                <fieldset name='User Info'>
                    <field name='job_title' />
                    <field name='city' />
                    <field name='state' />
                </fieldset>
            </column>
        </row>
        <row showif="editMode=1">
            <column>
                <fieldset name='User Password'>
                    <field name='password' />
                </fieldset>
            </column>
        </row>
        <row>
            <column>
                <fieldset name='Admin'>
                    <field name='team_id' />
                    <field name='active' />
                    <field name='groups' hidelabel='true' />
                    <field name='manager_id' />
                </fieldset>
            </column>
        </row>
        <row>
            <column>
                <fieldset name='Contact'>
                    <field label='Carier' name='phone_mobile_carrier' />
                    <field label='Mobile' name='phone_mobile' />
                    <field label='Office' name='phone_office' />
                    <field label='Ext' name='phone_ext' />
                </fieldset>
            </column>
        </row>

    </column>
    <column>
        <row>
            <column>
                <field name='name' validator='username' />
                <field name='full_name' />
                <field label='Email' name='email' />
            </column>
        </row>
        <row showif="editMode=1">
            <column>
                <field name='job_title' />
            </column>
        </row>
        <row showif="editMode=1">
            <column>
                <field name='city' />
            </column>
            <column>
                <field name='state' />
            </column>
        </row>
        <row>
            <column>
                <field name='notes' hidelabel='true' multiline='true' />
            </column>
        </row>
    </column>
    <column type="sidebar">
        <row>
            <field hidelabel='true' name='activity' />
        </row>
    </column>
</row>