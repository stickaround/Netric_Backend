<form>
    <row>
        <column>
            <field name='name' hidelabel="true" class='headline'></field>
        </column>
    </row>    
    <row>
        <column>
            <row>
                <column>
                    <objectsref obj_type='ticket' ref_field='channel_id' view_id='all_tickets' />
                </column>
            </row>
        </column>
        <column type="sidebar">
            <maildrop name='email_account_id' create_type="ticket" ref_field='channel_id'></field>
            <members name='Members' field='members' />
        </column>
    </row>
</form>