'use strict';

var BrowserView = require("../../../js/entity/BrowserView");
var netric = require("../../../js/main");

/**
 * Test the setting up of data for browser view
 */
describe("Setup browserView data", function() {
    
    var data = {
            name: 'browserViewTest',
            conditions: [],
            sort_order: [],
            view_fields: [],
    }
    
    data.conditions.push({
        blogic: 'and',
        field_name: 'id',
        operator: 'is_equal',
        value: -3,
    });
    
    data.sort_order.push({
        field_name: 'id',
        order: 'asc'
    });
    
    data.view_fields.push("id");
    
    var browserViewObject = new BrowserView("note");
    browserViewObject.fromData(data);
    
    it("Should have setup data for browserView", function() {
        expect(browserViewObject.name).toEqual("browserViewTest");
        expect(browserViewObject.getConditions().length).toEqual(1);
        expect(browserViewObject.getOrderBy().length).toEqual(1);
        expect(browserViewObject.getTableColumns().length).toEqual(1);
    }); 
    
    it("Should get data for browserView", function() {
        var browserViewData = browserViewObject.getData();        
        
        expect(browserViewData.name).toEqual("browserViewTest");
        expect(browserViewData.conditions.length).toEqual(1);
        expect(browserViewData.order_by.length).toEqual(1);
        expect(browserViewData.table_columns.length).toEqual(1);
    });
    
    it("Should add new condition", function() {
        browserViewObject.addCondition("id");
        
        expect(browserViewObject.getConditions().length).toEqual(2);
    });
    
    it("Should remove condition", function() {
        browserViewObject.removeCondition(0);
        
        expect(browserViewObject.getConditions().length).toEqual(1);
    });
    
    it("Should add order by", function() {
        browserViewObject.addOrderBy("id", "asc");
        
        expect(browserViewObject.getOrderBy().length).toEqual(2);
    });
    
    it("Should remove order by", function() {
        browserViewObject.removeOrderBy(0);
        
        expect(browserViewObject.getOrderBy().length).toEqual(1);
    });
    
    it("Should add table column", function() {
        browserViewObject.addTableColumn("id");
        
        expect(browserViewObject.getTableColumns().length).toEqual(2);
    });
    
    it("Should update table column", function() {
        browserViewObject.updateTableColumn('name', 0);
        var tableColumns = browserViewObject.getTableColumns();
        
        expect(tableColumns[0]).toEqual('name');
    });
    
    it("Should remove table column", function() {
        browserViewObject.removeTableColumn(0);
        
        expect(browserViewObject.getTableColumns().length).toEqual(1);
    });
    
    it("Should set browserView Id", function() {
        browserViewObject.setId(1);
        
        expect(browserViewObject.id).toEqual(1);
    });
});