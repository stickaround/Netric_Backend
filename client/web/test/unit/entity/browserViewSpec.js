'use strict';

var BrowserView = require("../../../js/entity/BrowserView");
var Where = require("../../../js/entity/Where");
var netric = require("../../../js/main");

/**
 * Test the setting up of data for browser view
 */
describe("Setup browserView data", function() {
    
    var data = {
            name: 'browserViewTest',
            conditions: [],
            sort_order: [],
            table_columns: ["id"],
    }

    // Setup where object
    var where = new Where('id');
    where.bLogic = 'and';
    where.operator = Where.operators.EQUALTO;
    where.value = -3;

    data.conditions.push(where);
    
    data.sort_order.push({
        field_name: 'id',
        order: 'asc'
    });

    it("Should have setup data for browserView", function() {
        var browserViewObject = new BrowserView("note");
        browserViewObject.fromData(data);

        expect(browserViewObject.name).toEqual("browserViewTest");
        expect(browserViewObject.getConditions().length).toEqual(1);
        expect(browserViewObject.getOrderBy().length).toEqual(1);
        expect(browserViewObject.getTableColumns().length).toEqual(1);
    }); 
    
    it("Should get data for browserView", function() {

        var browserViewObject = new BrowserView("note");

        browserViewObject.fromData(data);
        var browserViewData = browserViewObject.getData();
        
        expect(browserViewData.name).toEqual("browserViewTest");
        expect(browserViewData.conditions.length).toEqual(1);
        expect(browserViewData.order_by.length).toEqual(1);
        expect(browserViewData.table_columns.length).toEqual(1);
    });
    
    it("Should add/remove condition", function() {

        var browserViewObject = new BrowserView("note");
        browserViewObject.addCondition("id");
        browserViewObject.addCondition("name");
        browserViewObject.addCondition("website");
        expect(browserViewObject.getConditions().length).toEqual(3);

        // Remove Condition
        browserViewObject.removeCondition(0);
        expect(browserViewObject.getConditions().length).toEqual(2);
    });
    
    it("Should add/remove order by", function() {
        var browserViewObject = new BrowserView("note");
        browserViewObject.addOrderBy("id", "asc");
        browserViewObject.addOrderBy("name", "asc");
        browserViewObject.addOrderBy("website", "asc");
        expect(browserViewObject.getOrderBy().length).toEqual(3);

        // Remove Order By
        browserViewObject.removeOrderBy(0);
        expect(browserViewObject.getOrderBy().length).toEqual(2);
    });

    it("Should add/update/remove table column", function() {
        var browserViewObject = new BrowserView("note");
        browserViewObject.addTableColumn("id");
        browserViewObject.addTableColumn("name");
        browserViewObject.addTableColumn("website");
        expect(browserViewObject.getTableColumns().length).toEqual(3);

        // Update table column
        browserViewObject.updateTableColumn('note', 0);
        var tableColumns = browserViewObject.getTableColumns();
        expect(tableColumns[0]).toEqual('note');

        // Remove table column
        browserViewObject.removeTableColumn(0);
        expect(browserViewObject.getTableColumns().length).toEqual(2);
    });
    
    it("Should set browserView Id", function() {
        var browserViewObject = new BrowserView("note");
        browserViewObject.setId(1);
        
        expect(browserViewObject.id).toEqual(1);
    });
});