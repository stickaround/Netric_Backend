<form>
    <row>
        <column>
            <header image_field='image_id' />
        </column>
    </row>
    <row>
        <column>
            <row showif='entity_id=null'>
                <column>
                    <field name='type_id'></field>
                </column>
            </row>
            <row showif='type_id=1'>
                <column>
                    <field name='salutation'></field>
                    <field name='first_name'></field>
                    <field name='last_name'></field>
                    <field name='spouse_name'></field>
                </column>
                <column>
                    <field ref_field='type_id' ref_value='2' name='primary_account'></field>
                    <field name='job_title'></field>
                </column>
            </row>
            <row showif='type_id=2'>
                <column>
                    <field name='name'></field>
                    <field ref_field='type_id' ref_value='1' name='primary_contact'></field>
                </column>
            </row>
            <row>
                <column>
                    <field name='notes' multiline='true'></field>
                </column>
            </row>

            <tabs>
                <tab name='Activity'>
                    <activity />
                </tab>

                <tab name='Tasks'>
                    <objectsref obj_type='task' ref_field='contact_id'></objectsref>
                </tab>

                <tab name='Tickets'>
                    <objectsref obj_type='ticket' ref_field='contact_id'></objectsref>
                </tab>

                <tab name='Opportunities'>
                    <objectsref obj_type='opportunity' ref_field='customer_id'></objectsref>
                </tab>

                <tab name='Files'>
                    <attachments></attachments>
                </tab>
            </tabs>
        </column>
        <column type="sidebar">
            <row>
                <column>
                    <fieldset name='Contact'>
                        <field label='Mobile' name='phone_cell'></field>
                        <field label='Home' name='phone_home'></field>
                        <field label='Work' name='phone_work'></field>
                        <field label='Ext' name='phone_ext'></field>
                        <field name='phone_fax'></field>
                        <field name='phone_pager'></field>
                        <field label='Home' name='email'></field>
                        <field label='Work' name='email2'></field>
                        <field label='Other' name='email3'></field>
                        <field label='Spouse' name='email_spouse'></field>
                        <field name='website'></field>
                        <field name='facebook'></field>
                    </fieldset>
                </column>
            </row>
            <row>
                <column>
                    <fieldset name='Details'>
                        <field name='status_id'></field>
                        <field name='stage_id'></field>
                        <field name='owner_id'></field>
                        <field name='is_nocall'></field>
                        <field name='is_noemailspam'></field>
                        <field name='is_nocontact'></field>
                    </fieldset>
                </column>
            </row>
            <row>
                <column>
                    <fieldset name='Groups'>
                        <field name='groups' hidelabel='true'></field>
                    </fieldset>
                </column>
            </row>
            <row>
                <column>
                    <fieldset name='Address'>
                        <field label='Street' name='street'></field>
                        <field label='Street 2' name='street2'></field>
                        <field label='Zip' name='postal_Code'></field>
                        <field label='City' name='city'></field>
                        <field label='State' name='district'></field>
                    </fieldset>
                </column>
            </row>
            <row>
                <column>
                    <fieldset name='Billing Address'>
                        <field name='billing_street'></field>
                        <field name='billing_street2'></field>
                        <field name='billing_postal_code'></field>
                        <field name='billing_city'></field>
                        <field name='billing_district'></field>
                    </fieldset>
                </column>
            </row>
            <row>
                <column>
                    <fieldset name='Important Dates'>
                        <field name='birthday'></field>
                        <field name='birthday_spouse'></field>
                        <field name='anniversary'></field>
                        <field name='last_contacted'></field>
                    </fieldset>
                </column>
            </row>

        </column>
    </row>
</form>