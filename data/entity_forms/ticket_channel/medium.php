<form>
    <field name='name' hidelabel="true" class='headline'></field>
    <maildrop name='email_account_id' create_type="ticket"></field>
    <members name='Members' field='members' />
    <objectsref obj_type='ticket' ref_field='channel_id' view_id='all_tickets'></objectsref>
</form>