<column>
    <row showif="id=*">
        <column>
            <field name='name'></field>
            <field name='file_size'></field>
        </column>
        <column>
            <field name='owner_id'></field>
            <field name='ts_updated'></field>
        </column>
    </row>
    <row>
        <column>
            <field name='folder_id'></field>
        </column>
    </row>
    <row>
        <column>
            <plugin name='DisplayFile'></plugin>
        </column>
    </row>
    <row showif="id=*">
        <column>
            <fieldset name='Comments'>
                <field name='comments'></field>
            </fieldset>
        </column>
    </row>
</column>
