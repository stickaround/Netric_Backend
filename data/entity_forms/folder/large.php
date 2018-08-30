<column>
    <row>
        <column>
            <field name='name'></field>
        </column>
    </row>
    <row>
        <column>
            <field name='parent_id'></field>
        </column>
    </row>
    <row showif="id=*">
        <column>
            <fieldset name='Comments'>
                <field name='comments'></field>
            </fieldset>
        </column>
    </row>
    <row>
        <tabs>
            <tab name='Files'>
                <row>
                    <column>
                        <objectsref obj_type='file' ref_field='folder_id'></objectsref>
                    </column>
                </row>
            </tab>
        </tabs>
    </row>
</column>
