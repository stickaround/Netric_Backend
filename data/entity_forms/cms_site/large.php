<row>
    <column>
        <field name='name'></field>
    </column>
</row>
<row>
    <column>
        <row>
            <column width='50%'>
                <field name='url'></field>
                <field name='url_test'></field>
            </column>
            <column width='50%'>
                <field name='owner_id'></field>
                <field name='ts_entered'></field>
            </column>
        </row>
        <row>
            <column>
                <tabs>
                    <tab name='Feeds &amp; Blogs'>
                        <objectsref obj_type='content_feed' ref_field='site_id' />
                    </tab>
                    <tab name='Pages'>
                        <objectsref obj_type='cms_page' ref_field='site_id'></objectsref>
                    </tab>
                    <tab name='Page Templates'>
                        <objectsref obj_type='cms_page_template' ref_field='site_id'></objectsref>
                    </tab>
                    <tab name='Snippets'>
                        <objectsref obj_type='cms_snippet' ref_field='site_id'></objectsref>
                    </tab>
                    <tab name='Comments'>
                        <field name='comments'></field>
                    </tab>
                    <tab name='Media'>
                        <field hidelabel='true' name='folder_id'></field>
                    </tab>
                </tabs>
            </column>
        </row>
    </column>
</row>