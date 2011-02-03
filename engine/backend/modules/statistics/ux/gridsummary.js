Ext.ns("Ext.ux.grid.GridSummary");

Ext.ux.grid.GridSummary = function(config) {
	Ext.apply(this, config);
};

Ext.extend(Ext.ux.grid.GridSummary, Ext.util.Observable, {
	init : function(grid) {
		this.grid = grid;
		this.cm = grid.getColumnModel();
		this.view = grid.getView();

		var v = this.view;

		v.onLayout = this.onLayout; // override GridView's onLayout() method

		v.afterMethod('render', this.refreshSummary, this);
		v.afterMethod('refresh', this.refreshSummary, this);
		v.afterMethod('syncScroll', this.syncSummaryScroll, this);
		v.afterMethod('onColumnWidthUpdated', this.doWidth, this);
		v.afterMethod('onAllColumnWidthsUpdated', this.doAllWidths, this);
		v.afterMethod('onColumnHiddenUpdated', this.doHidden, this);
		v.afterMethod('onUpdate', this.refreshSummary, this);
		v.afterMethod('onRemove', this.refreshSummary, this);

		// update summary row on store's add / remove / clear events
		grid.store.on('add', this.refreshSummary, this);
		grid.store.on('remove', this.refreshSummary, this);
		grid.store.on('clear', this.refreshSummary, this);

		if (!this.rowTpl) {
			this.rowTpl = new Ext.Template(
			'<div class="x-grid3-summary-row x-grid3-gridsummary-row-offset">',
			'<table class="x-grid3-summary-table" border="0" cellspacing="0" cellpadding="0" style="{tstyle}">',
			'<tbody><tr>{cells}</tr></tbody>',
			'</table>',
			'</div>'
			);
			this.rowTpl.disableFormats = true;
		}
		this.rowTpl.compile();

		if (!this.cellTpl) {
			this.cellTpl = new Ext.Template(
			'<td class="x-grid3-col x-grid3-cell x-grid3-td-{id} {css}" style="{style}">',
			'<div class="x-grid3-cell-inner x-grid3-col-{id}" unselectable="on" {attr}>{value}</div>',
			"</td>"
			);
			this.cellTpl.disableFormats = true;
		}
		this.cellTpl.compile();
	},

	calculate : function(rs, cm) {
		var data = {}, cfg = cm.config;
		for (var i = 0, len = cfg.length; i < len; i++) { // loop through all columns in ColumnModel
			var cf = cfg[i], // get column's configuration
			cname = cf.dataIndex; // get column dataIndex

			// initialise grid summary row data for
			// the current column being worked on
			data[cname] = 0;

			if (cf.summaryType) {
				for (var j = 0, jlen = rs.length; j < jlen; j++) {
					var r = rs[j]; // get a single Record
					data[cname] = Ext.ux.grid.GridSummary.Calculations[cf.summaryType](r.get(cname), r, cname, data, j);
				}
			}
		}

		return data;
	},

	onLayout : function(vw, vh) {
		if (Ext.type(vh) != 'number') { // handles grid's height:'auto' config
			return;
		}
		// note: this method is scoped to the GridView
		if (!this.grid.getGridEl().hasClass('x-grid-hide-gridsummary')) {
			// readjust gridview's height only if grid summary row is visible
			this.scroller.setHeight(vh - this.summary.getHeight());
		}
	},

	syncSummaryScroll : function() {
		var mb = this.view.scroller.dom;
		this.view.summaryWrap.dom.scrollLeft = mb.scrollLeft;
		this.view.summaryWrap.dom.scrollLeft = mb.scrollLeft; // second time for IE (1/2 time first fails, other browsers ignore)
	},

	doWidth : function(col, w, tw) {
		var s = this.view.summary.dom;
		s.firstChild.style.width = tw;
		s.firstChild.rows[0].childNodes[col].style.width = w;
	},

	doAllWidths : function(ws, tw) {
		var s = this.view.summary.dom, wlen = ws.length;
		s.firstChild.style.width = tw;
		var cells = s.firstChild.rows[0].childNodes;
		for (var j = 0; j < wlen; j++) {
			cells[j].style.width = ws[j];
		}
	},

	doHidden : function(col, hidden, tw) {
		var s = this.view.summary.dom,
		display = hidden ? 'none' : '';
		s.firstChild.style.width = tw;
		s.firstChild.rows[0].childNodes[col].style.display = display;
	},
	roundVal: function (val){
		var dec = 2;
		var result = Math.round(val*Math.pow(10,dec))/Math.pow(10,dec);
		return result;
	},
	renderSummary : function(o, cs, cm) {
		cs = cs || this.view.getColumnData();
		var cfg = cm.config,
		buf = [],
		last = cs.length - 1;

		for (var i = 0, len = cs.length; i < len; i++) {
			var c = cs[i], cf = cfg[i], p = {};
			p.id = c.id;
			p.style = c.style;
			p.css = i == 0 ? 'x-grid3-cell-first ' : (i == last ? 'x-grid3-cell-last ' : '');
			if (cf.summaryType || cf.summaryRenderer) {
				p.value = (cf.summaryRenderer || c.renderer)(o.data[c.name], p, o);
				p.value = this.roundVal(p.value);
			} else {
				p.value = '';
			}
			if (p.value == undefined || p.value === "") p.value = "&#160;";
			buf[buf.length] = this.cellTpl.apply(p);
		}

		return this.rowTpl.apply({
			tstyle: 'width:' + this.view.getTotalWidth() + ';',
			cells: buf.join('')
		});
	},

	refreshSummary : function() {
		var g = this.grid, ds = g.store,
		cs = this.view.getColumnData(),
		cm = this.cm,
		rs = ds.getRange(),
		data = this.calculate(rs, cm),
		buf = this.renderSummary({data: data}, cs, cm);

		if (!this.view.summaryWrap) {
			this.view.summaryWrap = Ext.DomHelper.insertAfter(this.view.scroller, {
				tag: 'div',
				cls: 'x-grid3-gridsummary-row-inner'
			}, true);
		} else {
			this.view.summary.remove();
		}
		this.view.summary = this.view.summaryWrap.insertHtml('afterbegin', buf, true);
	},

	toggleSummary : function(visible) { // true to display summary row
		var el = this.grid.getGridEl();
		if (el) {
			if (visible === undefined) {
				visible = el.hasClass('x-grid-hide-gridsummary');
			}
			el[visible ? 'removeClass' : 'addClass']('x-grid-hide-gridsummary');

			this.view.layout(); // readjust gridview height
		}
	},

	getSummaryNode : function() {
		return this.view.summary
	}
});

/*
* all Calculation methods are called on each Record in the Store
* with the following 5 parameters:
*
* v - cell value
* record - reference to the current Record
* colName - column name (i.e. the ColumnModel's dataIndex)
* data - the cumulative data for the current column + summaryType up to the current Record
* rowIdx - current row index
*/
Ext.ux.grid.GridSummary.Calculations = {
	sum : function(v, record, colName, data, rowIdx) {
		//console.log();
		return data[colName] + parseFloat(v);
	},

	count : function(v, record, colName, data, rowIdx) {
		return rowIdx + 1;
	},

	max : function(v, record, colName, data, rowIdx) {
		return Math.max(Ext.num(v, 0), data[colName]);
	},

	min : function(v, record, colName, data, rowIdx) {
		return Math.min(Ext.num(v, 0), data[colName]);
	},

	average : function(v, record, colName, data, rowIdx) {
		var t = data[colName] + Ext.num(v, 0), count = record.store.getCount();
		return rowIdx == count - 1 ? (t / count) : t;
	}
}
/*
Building grid metadata dynamicly
*/
Ext.ux.AutoGridPanel = Ext.extend(Ext.grid.GridPanel, {

	initComponent : function(){
		
		if(this.columns && (this.columns instanceof Array)){
			this.colModel = new Ext.grid.ColumnModel(this.columns);
			delete this.columns;
		}

		// Create a empty colModel if none given
		if(!this.colModel) {
			this.colModel = new Ext.grid.ColumnModel([]);
		}

		Ext.ux.AutoGridPanel.superclass.initComponent.call(this);

		// register to the store's metachange event
		if(this.store){
			this.store.on("metachange", this.onMetaChange, this);
		}

		// Store the column model to the server on change
		if(this.autoSave) {
			this.colModel.on("widthchange", this.saveColumModel, this);
			this.colModel.on("hiddenchange", this.saveColumModel, this);
			this.colModel.on("columnmoved", this.saveColumModel, this);
			this.colModel.on("columnlockchange", this.saveColumModel, this);
		}
	},

	onMetaChange : function(store, meta) {
		//this.initComponent();

		// loop for every field, only add fields with a header property (modified copy from ColumnModel constructor)
		var c;
		var config = [];
		var lookup = {};
		for(var i = 0, len = meta.fields.length; i < len; i++)
		{

			c = meta.fields[i];
			if(c.header !== undefined){

				if(typeof c.dataIndex == "undefined"){
					c.dataIndex = c.name;
				}
				if(typeof c.renderer == "string"){
					c.renderer = Ext.util.Format[c.renderer];
				}
				if(typeof c.id == "undefined"){
					c.id = 'c' + i;
				}

				if(c.editor && c.editor.isFormField){
					c.editor = new Ext.grid.GridEditor(c.editor);
				}
				c.sortable = "true";
				//delete c.name;
				
				
				config[config.length] = c;
				lookup[c.id] = c;
			}
		}

		// Store new configuration
		this.colModel.config = config;
		this.colModel.lookup = lookup;
		this.colModel.defaultSortable = true;
		this.view.refresh(true);


		this.view.hmenu.add(
		{id:"reset", text: "Reset Columns", cls: "xg-hmenu-reset-columns"}
		);
	},

	saveColumModel : function() {

		// Get Id, width and hidden propery from every column
		var c, config = this.colModel.config;
		var fields = [];
		for(var i = 0, len = config.length; i < len; i++)
		{
			c = config[i];
			fields[i] = {name: c.name, width: c.width};
			if(c.hidden) {
				fields[i].hidden = true;
			}
		}

		var sortState = this.store.getSortState();

		// Send it to server
		//console.log("save config", fields);
		Ext.Ajax.request({
			url: this.saveUrl,
			params : {fields: Ext.encode(fields), sort: Ext.encode(sortState)}
		});
	}

});