<column>
    <fieldset name='Details' editmodeonly='t'>
        <row>
            <column>
                <field name='name'></field>
            </column>
        </row>
        <row>
            <column>
                <field name='description' multiline='t'></field>
            </column>
        </row>
        <row>
            <column>
                <field name='owner_id'></field>
            </column>
        </row>
        <row>
            <column>
                <field name='scope'
                       tooltip="If set to User then only the Owner has access to this dashboard. It is private. Otherwise, if set to System/Everyone then everyone who has permission to view dashboards can access it."></field>
            </column>
        </row>
        <row>
            <column>
                <field name='num_columns'></field>
            </column>
        </row>
    </fieldset>
    <plugin name='DashboardWidgets'></plugin>
</column>