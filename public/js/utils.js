(function(root) 
{
	//FUNCTION DECLARATION
	var utils = {
		
		//data table
		'createDataTable' : createDataTable,
		'prepareDataTableSortData' : prepareDataTableSortData,
		'prepareDataTablePagingData' : prepareDataTablePagingData,
		'getDataTableFieldIdx' : getDataTableFieldIdx,
		'getDataTableDetails' : getDataTableDetails,
		'resetDataTableDetails' : resetDataTableDetails,
		'createSumForDataTable' : createSumForDataTable,

		'addClass' : addClass,
		'removeClass' : removeClass,

		//submit button
		'startLoadingBtn' : startLoadingBtn,
		'stopLoadingBtn' : stopLoadingBtn,

		'showModal' : showModal,
		'createSpinner' : createSpinner, //spinner indicator

		'formatMoney' : formatMoney,
		'formatCurrencyInput' : formatCurrencyInput,
		'formatCurrencyInputWithoutDecimal' : formatCurrencyInputWithoutDecimal,
		'formatted_num' : formatted_num,

		'generateModalMessage' : generateModalMessage,

		//Logging
		'generateLogData' : generateLogData,

		//get qs
		'getParameterByName' : getParameterByName,

		//get today date in db format
		'getTodayDB' : getTodayDB,
		'formattedDbDate' : formattedDbDate,

		//get date in dd/mm/yy
		'getToday' : getToday,
		'getYesterday' : getYesterday,
		'getLastWeek' : getLastWeek,
		'getThisWeek' : getThisWeek,
		'getThisMonth': getThisMonth,
		'getLastMonth': getLastMonth,
		'getOneWeek' : getOneWeek,
		'getFifteenDays' : getFifteenDays,
		'getMonth' : getMonth,
		'formattedDate' : formattedDate,
		'datepickerStart' : datepickerStart,
		'datepickerEnd' : datepickerEnd,

		'getCurrentDateTime' : getCurrentDateTime,
		'padLeft' : padLeft,

		'getPreviousTier' : getPreviousTier,
		'getColumnRowspan' : getColumnRowspan,

		'initChart' : initChart,
	};
	
	root.utils = utils;

	function createDataTable(containerId,dataSet,fields,callbackSort,callbackPaging)
	{
		//fields structure
		// 0 - field name
		// 1 - field title
		// 2 - allow order
		// 3 - align right

		//container
	    var div = document.getElementById(containerId);
	    div.innerHTML = "";

	    //handle exception
	    if(dataSet.length == 0)
	    {
	    	div.innerHTML = locale['utils.datatable.invaliddata'];

			return null;
	    }

	    //table data
	    var data = dataSet.results;

	    if(data.length > 0)
	    {
		    //order by data
		    var orderBy = div.orderBy;
	    	var orderType = div.orderType;

	    	//paging data
		    var page = div.page;
		    var pageSize = dataSet.page_size;
		    var dataSize = dataSet.count;

		    if(page == undefined)
		    {
		    	page = 1;
		    	div.pagination = page;
		    }

		    var ttlPage = Math.ceil(dataSize / pageSize);

		    if(ttlPage < 1)
		    	ttlPage = 1;

		    var aryPage = [];

		    for(var i = -2 ; i < 3 ; i++)
		    {
		    	var validPage = false;

		  		var tmpPage = page + i;

		    	if(tmpPage >= 1 && tmpPage <= ttlPage)
		    	{
		    		validPage = true;
		    	}

		    	if(validPage)
		    		aryPage.push(tmpPage);
		    }

		    // console.log(aryPage);

		    //paging top
	    	var divRow = document.createElement("div");
	    	divRow.className = "row";
			div.appendChild(divRow);

			var divColLeft = document.createElement("div");
	    	divColLeft.className = "col-sm-6";
	    	divRow.appendChild(divColLeft);

	    	var navTop = document.createElement("nav");
			divColLeft.appendChild(navTop); 

			createDataTablePaging(containerId,navTop,page,aryPage,callbackPaging,ttlPage);

			var divColRight = document.createElement("div");
			divColRight.id = "total_record";
	    	divColRight.className = "col-sm-6 text-right";
	    	divColRight.innerHTML = locale['utils.datatable.totalrecords'] + " : " + dataSize;
	    	divRow.appendChild(divColRight);

	    	//table container
	    	var divTableContainer = document.createElement("div");
	    	divTableContainer.className = "table-responsive";
	    	div.appendChild(divTableContainer); 

		    //table
		    var table = document.createElement("table");
			table.className = "table-bordered table-striped table-sm mb-2";	
			divTableContainer.appendChild(table); 

			//table header
			var tHead = table.createTHead();
			var row = tHead.insertRow(0); 

			for (i = 0; i < fields.length; i++)
		    {
		    	var fieldName = fields[i][0];
		    	var fieldTitle = fields[i][1];
		    	var allowOrder = fields[i][2];

		    	var th = document.createElement('th');
				th.innerHTML = fieldTitle;

				if(allowOrder)
				{
					th.containerId = containerId;
					th.orderBy = fieldName;

					utils.addClass(th,'sorting');

					th.onclick = callbackSort;

					if(orderBy == fieldName)
					{
						if(orderType == "desc")
							utils.addClass(th,'sorting-desc');
						else
							utils.addClass(th,'sorting-asc');
					}
				}

				row.appendChild(th);
		    } 

		    //table data
			var tBody = table.createTBody();
			
		    for (i = 0; i < data.length; i++)
		    {
		        row = tBody.insertRow(i);
		        
		        for (j = 0; j < fields.length; j++)
		        {
		        	var alignRight = fields[j][3];

		            var cell = row.insertCell(j);

		            if(alignRight)
		            	cell.style.textAlign = "right";

		            cell.innerHTML = data[i][fields[j][0]];                          
		        }                   
		    }

		    //paging bottom
		    var navBottom = document.createElement("nav");
			div.appendChild(navBottom); 

			createDataTablePaging(containerId,navBottom,page,aryPage,callbackPaging,ttlPage);

			return table;
		}
		else
		{
			div.innerHTML = locale['utils.datatable.norecords'];

			return null;
		}
	}

	function createDataTablePaging(containerId,nav,page,aryPage,callbackPaging,ttlPage)
	{
		if(ttlPage == 1)
			return;

		var ul = document.createElement("ul");
		ul.className = "pagination pagination pagination-sm mb-2";
		nav.appendChild(ul);

		//for << and < 
		if(page > 1)
		{
			var li = document.createElement("li");
			li.className = "page-item";

			li.containerId = containerId;
			li.page = 1;
			li.onclick = callbackPaging;

			li.innerHTML = '<span class="page-link"><<</span>';
			ul.appendChild(li); 

			var li = document.createElement("li");
			li.className = "page-item";

			li.containerId = containerId;
			li.page = page - 1;
			li.onclick = callbackPaging;

			li.innerHTML = '<span class="page-link"><</span>';
			ul.appendChild(li); 
		}

		//for page number
		for(var i = 0 ; i < aryPage.length ; i++)
	    {
	    	var li = document.createElement("li");
			li.className = "page-item";

			li.containerId = containerId;
			li.page = aryPage[i];
			li.onclick = callbackPaging;

			li.innerHTML = '<span class="page-link">' + aryPage[i] + '</span>';

			if(aryPage[i] == page)
				utils.addClass(li,"active");

			ul.appendChild(li); 
	    }

	    //for > and >> 
	    if(page != ttlPage)
		{
			var li = document.createElement("li");
			li.className = "page-item";

			li.containerId = containerId;
			li.page = page + 1;
			li.onclick = callbackPaging;

			li.innerHTML = '<span class="page-link">></span>';
			ul.appendChild(li); 

			var li = document.createElement("li");
			li.className = "page-item";

			li.containerId = containerId;
			li.page = ttlPage;
			li.onclick = callbackPaging;

			li.innerHTML = '<span class="page-link">>></span>';
			ul.appendChild(li); 
		}

		//spacing
		var li = document.createElement('li');
		li.innerHTML = '&nbsp;&nbsp;';
		ul.appendChild(li); 

		//for dropdown
		var li = document.createElement("li");

		var dd = document.createElement("select");
		dd.className = "form-control";
		dd.style.height = "28px";
		dd.style.fontSize = "12px";

		dd.containerId = containerId;
		
		dd.onchange = function ()
			{ 
				this.page = parseInt(this.options[this.selectedIndex].value);
				
				callbackPaging.call(this);
			 };

		for(var i = 0 ; i < ttlPage ; i++)
	    {
	    	var option = document.createElement("option");
			option.text = i + 1;
			option.value = i + 1;
			dd.add(option);
	    }

		dd.selectedIndex = page - 1;

		li.appendChild(dd);
		ul.appendChild(li); 
	}

	function prepareDataTableSortData(containerId,orderBy)
	{
		var div = document.getElementById(containerId);

    	var prevOrderBy = div.orderBy;
    	var prevOrderType = div.orderType;

		if(orderBy == prevOrderBy)
	    {
	        if(prevOrderType == "desc")
	        {
	            div.orderType = "asc";
	        }
	        else
	        {
	            div.orderType = "desc";
	        }
	    }
	    else
	    {
	        div.orderType = "desc";
	    }

	    div.orderBy = orderBy; 
	}

	function prepareDataTablePagingData(containerId,pageNo)
	{
		var div = document.getElementById(containerId);

    	div.page = pageNo;
	}

	function getDataTableFieldIdx(name,fields)
	{
	    for (i = 0; i < fields.length; i++)
	    {
	        if(name == fields[i][0])
	            return i;
	    }

	    return 0;
	}

	function getDataTableDetails(containerId)
	{
		var div = document.getElementById(containerId);

	    var data = {
                page : div.page
                ,order_by : div.orderBy
                ,order_type : div.orderType
                };

        return data;
	}

	function resetDataTableDetails(containerId)
	{
		var div = document.getElementById(containerId);

		div.page = null;
    	div.orderBy = null;
    	div.order_type = null;
	}

	function createSumForDataTable(table,dataSet,dataSetTotal,fields,sumFields)
	{
		//1st field in table can't be sum, as reserved for title "Total"
		//if more than 1 page will have an extra row for current page total

		var isMultiPage = false;

		//check number of page
	    var data = dataSet.results;

	    if(data.length > 0)
	    {
		    var pageSize = dataSet.page_size;
		    var dataSize = dataSet.count;

		    if(dataSize > pageSize)
		    	isMultiPage = true;
		}
		else
		{
			return;
		}

		var footer = table.createTFoot();

		//1st row total
		var row = footer.insertRow(0); 
		var cell = row.insertCell(0);

		if(isMultiPage)
			cell.innerHTML = "<b>" + locale['utils.datatable.pagetotal'] + "</b>";
		else
			cell.innerHTML = "<b>" + locale['utils.datatable.total'] + "</b>";

		for(var i = 1 ; i < fields.length ; i++)
		{
			var cell = row.insertCell(i);

			var isSumField = false;
			var fieldName = "";

			//check whether the field need to sum
			for(var j = 0 ; j < sumFields.length ; j++)
			{
				if(utils.getDataTableFieldIdx(sumFields[j],fields) == i)
				{
					isSumField = true;
					fieldName = sumFields[j];
					break;
				}
			}

			if(isSumField)
			{
				var total = 0;

				cell.style.textAlign = "right";

				for(var h = 0 ; h < data.length ; h++)
				{	
					var figure = parseFloat(data[h][fieldName]);
					
					if(isNaN(figure))
						figure = 0;

					total += figure;
				}

				cell.innerHTML = total;
			}
		}

		//2nd row total
		if(isMultiPage)
		{
			var row = footer.insertRow(1); 
			var cell = row.insertCell(0);

			cell.innerHTML = "<b>" + locale['utils.datatable.total'] + "</b>";

			for(var i = 1 ; i < fields.length ; i++)
			{
				var cell = row.insertCell(i);

				var isSumField = false;
				var fieldName = "";

				//check whether the field need to sum
				for(var j = 0 ; j < sumFields.length ; j++)
				{
					if(utils.getDataTableFieldIdx(sumFields[j],fields) == i)
					{
						isSumField = true;
						fieldName = sumFields[j];
						break;
					}
				}

				if(isSumField)
				{
					var total = 0;

					cell.style.textAlign = "right";

					var figure = dataSetTotal[0][fieldName];

					if(isNaN(figure))
						figure = 0;
					
					cell.innerHTML = figure;
				}
			}
		}
	}
	
	function addClass(element,name) 
	{
	    var arr;
	    arr = element.className.split(" ");
	    if(arr.indexOf(name) == -1) 
	    {
	        element.className += " " + name;
	    }
	}

	function removeClass(element,name) 
	{
	    var arr;
	    arr = element.className.split(" ");

	    var idx = arr.indexOf(name);

	    if(idx >= 0) 
	    {
	        arr.splice(idx,1);
	    }

	    element.className = arr.join(" ");
	}

	function startLoadingBtn(element,overlayContainer) 
	{
		if (element != "") 
		{
			var btn = document.getElementById(element);

		    var ladda = Ladda.create(btn);
			ladda.start();
		}
			
		//create overlay
		if(overlayContainer)
		{
			var div = document.createElement('div');
			div.id = overlayContainer + "_overlay";
	    	div.style.backgroundColor = "black";
	    	div.style.width = "100%";
	    	div.style.height = "100%";
	    	div.style.top = "0";
	    	div.style.left = "0";
	    	div.style.opacity = "0.2";
	    	div.style.position = "absolute";
	    
	    	document.getElementById(overlayContainer).appendChild(div);
		}
	}

	function stopLoadingBtn(element,overlayContainer) 
	{
		if (element != "") 
		{
			var btn = document.getElementById(element);

		    var ladda = Ladda.create(btn);
			ladda.stop();
		}
			
		//remove overlay
		if(overlayContainer)
		{
			var overlay = document.getElementById(overlayContainer + "_overlay");
			overlay.parentNode.removeChild(overlay);
		}
	}

	function showModal(contentTitle,contentBody,type,callbackClose)
	{
	    var modal = document.createElement("div");
	    modal.className = "modal fade";
	    modal.setAttribute("role", "dialog");     

	    var dialog = document.createElement("div");

	    if(type == 1)
		{
			dialog.className = "modal-dialog modal-success";
		}
		else 
		{
			dialog.className = "modal-dialog modal-danger";
		}
	    
	    dialog.setAttribute("role", "document");   
	    modal.appendChild(dialog);              

	    var content = document.createElement("div");
	    content.className = "modal-content";
	    dialog.appendChild(content);   

	    var header = document.createElement("div");
	    header.className = "modal-header";
	    content.appendChild(header);   

	    var title = document.createElement("h4");
	    title.className = "modal-title";
	    title.innerHTML = contentTitle;
	    header.appendChild(title);

	    var btnX = document.createElement("button");
	    btnX.className = "close";
	    btnX.setAttribute("data-dismiss", "modal");
	    btnX.innerHTML = "×";
	    header.appendChild(btnX);

	    var body = document.createElement("div");
	    body.className = "modal-body";

	    if(Array.isArray(contentBody)) //is array
	    {
	    	var ul = document.createElement("ul");

	    	for(var i = 0 ; i < contentBody.length ; i++)
		    {
		    	var li = document.createElement("li");
		    	li.innerHTML = contentBody[i];
		    	ul.appendChild(li);
		    }

		    body.appendChild(ul);
	    }
	    else
	    {
	    	body.innerHTML = contentBody;
	    }

	    content.appendChild(body); 

	    var footer = document.createElement("div");
	    footer.className = "modal-footer";
	    content.appendChild(footer); 

	    var btnClose = document.createElement("button");
	    btnClose.className = "btn btn-secondary";
	    btnClose.setAttribute("data-dismiss", "modal");
	    btnClose.innerHTML = locale["utils.modal.ok"];
	    footer.appendChild(btnClose);

	    $(modal).modal('show');

	    if(callbackClose)
	    {
	    	$(modal).on('hidden.bs.modal', function () {
			    callbackClose();
			});
	    }
	    
	    // speed up focus on close btn
	    setTimeout(function (){
	        $(btnClose).focus();
	    }, 150);

	    //fail safe to focus
	    $(modal).on('shown.bs.modal', function() {
			$(btnClose).focus();
		});
	}

	function createSpinner(element) 
	{
		var spinner = document.getElementById(element);

		var div = document.createElement('div');
		div.className = "sk-wave";
		spinner.appendChild(div);

		var rect;

		rect = document.createElement('div');
		rect.className = "sk-rect sk-rect1";
		div.appendChild(rect);
		div.innerHTML += " ";

		rect = document.createElement('div');
		rect.className = "sk-rect sk-rect2";
		div.appendChild(rect);
		div.innerHTML += " ";

		rect = document.createElement('div');
		rect.className = "sk-rect sk-rect3";
		div.appendChild(rect);
		div.innerHTML += " ";

		rect = document.createElement('div');
		rect.className = "sk-rect sk-rect4";
		div.appendChild(rect);
		div.innerHTML += " ";

		rect = document.createElement('div');
		rect.className = "sk-rect sk-rect5";
		div.appendChild(rect);

	}

	function formatMoney(amount, decimalCount = 2, decimal = ".", thousands = ",") 
	{
	  try {
	    decimalCount = Math.abs(decimalCount);
	    decimalCount = isNaN(decimalCount) ? 2 : decimalCount;

	    const negativeSign = amount < 0 ? "-" : "";

	    let i = parseInt(amount = Math.abs(Number(amount) || 0).toFixed(decimalCount)).toString();
	    let j = (i.length > 3) ? i.length % 3 : 0;

	    return negativeSign 
	    	+ (j ? i.substr(0, j) + thousands : '') 
	    	+ i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" 
	    	+ thousands) 
	    	+ (decimalCount ? decimal 
	    	+ Math.abs(amount - i).toFixed(decimalCount).slice(2) : ""
	    	);
		  } 
		  catch (e) 
		  {
		    console.log(e)
		  }
	}

	function formatCurrencyInput(input)
	{        
	    $(input).on( "keyup", function( event )
	    {   
	        // When user select text in the document, also abort.
	        var selection = window.getSelection().toString();
	        if ( selection !== '' )
	        {
	            return;
	        }
	                
	        // When the arrow keys are pressed, abort.
	        if ( $.inArray( event.keyCode, [38,40,37,39] ) !== -1 )
	        {
	            return;
	        }
	                
	        var $this = $( this );
	                
	        // Get the value.
	        var input = $this.val();
	                
	        var input_length = input.length;

	        // check for decimal
			if (input.indexOf(".") >= 0) 
			{			  	
			  	// get position of first decimal to prevent multiple decimals from being entered
			    var decimal_pos = input.indexOf(".");

			    // split number by decimal point
			    var left_side = input.substring(0, decimal_pos);//before decimal point 
			    var right_side = input.substring(decimal_pos);//after decimal point

			    left_side = left_side.replace(/[^/\d/\.]+/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");

			    right_side = right_side.replace(/[^/\d/]+/g, "");  	    
			    
			    // Limit decimal to only 2 digits
			    right_side = right_side.substring(0, 2);

			    input = left_side + "." + right_side;
			} 
			else 
			{
			    input = input.replace(/[^\d\.]+/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");   
			}

	        $this.val( function()
	        {
	           //trimming leading zero and dot symbol
	           while(input.substring(0,1) === '0' || input.substring(0,1) === '.')
	           {
	           		input = input.substring(1);
	           }

	           return input;
	        });
	    });
	}

	function formatCurrencyInputWithoutDecimal(input)
	{
		$(input).on( "keyup", function( event )
	    {   
	        // When user select text in the document, also abort.
	        var selection = window.getSelection().toString();

	        if ( selection !== '' )
	        {
	            return;
	        }
	                
	        // When the arrow keys are pressed, abort.
	        if ( $.inArray( event.keyCode, [38,40,37,39] ) !== -1 )
	        {
	            return;
	        }
	                
	        var $this = $( this );
	                
	        // Get the value.
	        var input = $this.val();
	               
			input = input.replace(/[^\d]+/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");   

	        $this.val( function()
	        {
	           //trimming leading zero and dot symbol
	           while(input.substring(0,1) === '00')
	           {
	           		input = input.substring(1);
	           }

	           return input;
	        });
	    });

	}

	function generateModalMessage(container,type,contentBody)
	{
	    container = "#" + container;

	    $(container).html("");
	    $(container).removeClass("bg-success");
	    $(container).removeClass("bg-danger");

	    if(type == 1)
	    {
	        $(container).addClass("bg-success");

	        $(container).html(contentBody);
	    }
	    else
	    {
	        $(container).addClass("bg-danger");

	        if(Array.isArray(contentBody)) //is array
	        {
	            var ul = document.createElement("ul");

	            for(var i = 0 ; i < contentBody.length ; i++)
	            {
	                var li = document.createElement("li");
	                li.innerHTML = contentBody[i];
	                ul.appendChild(li);
	            }

	            $(container).append(ul);
	        }
	        else
	        {
	            $(container).html(contentBody);
	        }
	    }
	    
	    $(container).show();
	}

	function generateLogData(aryLogFields)
	{
		//aryLogFields - contains id of elements to be put into json
		
	    var obj = {};

	    for (i = 0; i < aryLogFields.length; i++)
	    {
	        var id = aryLogFields[i];

	        obj[id] = $("#" + id).val();
	    }

	    return JSON.stringify(obj);
	}

	function formatted_num(pad, trans, pad_pos)
	{
		if (typeof trans === 'undefined') 
		    return pad;
		if (pad_pos == 'l')
		{
		    return (pad + trans).slice(-pad.length);
		}
		else 
		{
		    return (trans + pad).substring(0, pad.length);
		}
	}

	function getParameterByName(name,url) 
	{
	    if (!url) url = window.location.href;
	    name = name.replace(/[\[\]]/g, '\\$&');
	    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
	        results = regex.exec(url);
	    if (!results) return null;
	    if (!results[2]) return '';
	    return decodeURIComponent(results[2].replace(/\+/g, ' '));
	} 

	function getTodayDB()
	{
	    var toGMT = +8;

	    var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var d = new Date(utc.getTime() + (toGMT * 60) * 60000);

	    var month = (1 + d.getMonth()).toString();
	    month = month.length > 1 ? month : '0' + month;

	    var day = d.getDate().toString();
	    day = day.length > 1 ? day : '0' + day;

	    var str =  d.getFullYear() + '-' + month + '-' + day;
	    return str;
	}

	function getCurrentDateTime()
    {
    	var toGMT = +8;

        var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var now = new Date(utc.getTime() + (toGMT * 60) * 60000);

        var currentHours = padLeft(now.getHours(),2,'0');
        var currentMinutes = padLeft(now.getMinutes(),2,'0');
        var currentSeconds = padLeft(now.getSeconds(),2,'0');

        var day = locale['utils.datetime.day.' + now.getDay()];

        var gmtSymbol = toGMT >= 0 ? '+' : '-';

        var str = now.getFullYear() 
            + '-' + padLeft(now.getMonth() + 1,2,'0')
            + '-' + padLeft(now.getDate(),2,'0') 
            + '&nbsp;' + day
            + '&nbsp;' + currentHours 
            + ':' + currentMinutes 
            + ':' +currentSeconds 
            +'&nbsp;' + 'GMT ' + gmtSymbol + Math.abs(toGMT) + ':00';

        return str;
    }
    function formattedDbDate(d)
	{
		var newdate = d.split("/").reverse().join("-");
					
		return newdate;
	}
	
	function formattedDate(d)
	{
		var d = new Date(d);

		var year = d.getFullYear();
		var month = ("00" + (d.getMonth() + 1).toString()).slice(-2);
		var day = ("00" + (d.getDate()).toString()).slice(-2);
					
		return day + '/' + month + '/' + year;
	}

	function getToday()
	{
	    var toGMT = +8;

	    var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var d = new Date(utc.getTime() + (toGMT * 60) * 60000);

	    var month = (1 + d.getMonth()).toString();
	    month = month.length > 1 ? month : '0' + month;

	    var day = d.getDate().toString();
	    day = day.length > 1 ? day : '0' + day;

	    var str =  day + '/' + month + '/' + d.getFullYear() 
	    return str;
	}

	function getLastWeek()
	{
		var toGMT = +8;
		var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var d = new Date(utc.getTime() + (toGMT * 60) * 60000);

		var end_week = new Date(d.setDate(d.getDate() - now.getDay()));
	    var end_week_date = ("00" + (end_week.getDate()).toString()).slice(-2) + '/' + ("00" + (end_week.getMonth() + 1).toString()).slice(-2) + '/' + end_week.getFullYear();

	   	var start_week = new Date(d.setDate(d.getDate() - 6));
	    var start_week_date = ("00" + (start_week.getDate()).toString()).slice(-2) + '/' + ("00" + (start_week.getMonth() + 1).toString()).slice(-2) + '/' + start_week.getFullYear();

	    return [start_week_date,end_week_date];

	}

	function getThisWeek()
	{
		var toGMT = +8;
		var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var d = new Date(utc.getTime() + (toGMT * 60) * 60000);

		var end_week = new Date(d.setDate(d.getDate()));
	    var end_week_date = ("00" + (end_week.getDate()).toString()).slice(-2) + '/' + ("00" + (end_week.getMonth() + 1).toString()).slice(-2) + '/' + end_week.getFullYear();

		var start_week = new Date(d.setDate(d.getDate() - now.getDay() + 1));
	    var start_week_date = ("00" + (start_week.getDate()).toString()).slice(-2) + '/' + ("00" + (start_week.getMonth() + 1).toString()).slice(-2) + '/' + start_week.getFullYear();



	    return [start_week_date,end_week_date];

	}


	function getThisMonth()
	{
		var toGMT = +8;
		var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var d = new Date(utc.getTime() + (toGMT * 60) * 60000);

		var end_month = new Date(d.setDate(d.getDate()));
		var start_month = new Date(d.setDate(d.getDate() - d.getDate() + 1));
		
	    var start_month_date = ("00" + (start_month.getDate()).toString()).slice(-2) + '/' + ("00" + (start_month.getMonth() + 1).toString()).slice(-2) + '/' + start_month.getFullYear();
	    var end_month_date = ("00" + (end_month.getDate()).toString()).slice(-2) + '/' + ("00" + (end_month.getMonth() + 1).toString()).slice(-2) + '/' + end_month.getFullYear();

	    return [start_month_date,end_month_date];

	}

	function getLastMonth()
	{
		var toGMT = +8;
		var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var d = new Date(utc.getTime() + (toGMT * 60) * 60000);

		var end_month = new Date(d.setDate(d.getDate() - now.getDate()));
		var start_month = new Date(d.setDate(d.getDate() - d.getDate() + 1));

	    var start_month_date = ("00" + (start_month.getDate()).toString()).slice(-2) + '/' + ("00" + (start_month.getMonth() + 1).toString()).slice(-2) + '/' + start_month.getFullYear();
	    var end_month_date = ("00" + (end_month.getDate()).toString()).slice(-2) + '/' + ("00" + (end_month.getMonth() + 1).toString()).slice(-2) + '/' + end_month.getFullYear();

	    return [start_month_date,end_month_date];

	}

	function getYesterday()
	{
	    var toGMT = +8;
		var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var d = new Date(utc.getTime() + (toGMT * 60) * 60000);

		var day = new Date(d.setDate(d.getDate() -  1));
	    var day = ("00" + (day.getDate()).toString()).slice(-2) + '/' + ("00" + (day.getMonth() + 1).toString()).slice(-2) + '/' + day.getFullYear();

	    return day;
	}

	function getOneWeek() 
	{
		var toGMT = +8;
		var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var d = new Date(utc.getTime() + (toGMT * 60) * 60000);

	    var one_week = new Date(d.setDate(d.getDate() - 7));
	    var one_week_date = ("00" + (one_week.getDate()).toString()).slice(-2) + '/' + ("00" + (one_week.getMonth() + 1).toString()).slice(-2) + '/' + one_week.getFullYear();

	    return one_week_date;
	}

	function getFifteenDays() 
	{
		var toGMT = +8;
		var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var d = new Date(utc.getTime() + (toGMT * 60) * 60000);

	    var fifteen = new Date(d.setDate(d.getDate() - 15));
	    var fifteen_date = ("00" + (fifteen.getDate()).toString()).slice(-2) + '/' + ("00" + (fifteen.getMonth() + 1).toString()).slice(-2) + '/' + fifteen.getFullYear();

	    return fifteen_date;
	}

	function getMonth(noOfMonths) 
	{
		var toGMT = +8;
		var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var d = new Date(utc.getTime() + (toGMT * 60) * 60000);

	    var month_date;
	    var checkYear = d.getFullYear();
	    var checkMonth = d.getMonth();
	    var checkDate = d.getDate();

	    if (checkMonth == 0) 
	    {
	        checkYear = checkYear - 1;
	        checkMonth = checkMonth - noOfMonths + 12 ;
	    } 
	    else 
	    {
	        checkMonth = checkMonth - noOfMonths; 
	    }

	    var isValidDateResult = isValidDate(checkYear, checkMonth, checkDate);

	    if (isValidDateResult) 
	    {
	        month_date = d.setMonth(d.getMonth() - noOfMonths);
	    } 
	    else 
	    {
		    if (checkMonth == 1) 
		    { 
		        month_date = d.setDate(getDateDay(checkYear, checkMonth, checkDate));
		    } 
		    else 
		    {
		        month_date = d.setDate(d.getDate() - 1);
		    }

	        month_date = d.setMonth(d.getMonth() - noOfMonths);
	    }

	    month_date = new Date(month_date);
	    var the_month_date = ("00" + (month_date.getDate().toString())).slice(-2) + '/' + ("00" + (month_date.getMonth() + 1).toString()).slice(-2) + '/' + month_date.getFullYear();

	    return the_month_date;
	}

	function isValidDate(year, month, day) 
	{	
	    var d = new Date(year, month, day);

	    if (d.getFullYear() == year && d.getMonth() == month && d.getDate() == day) 
	    {
	        return true;
	    }
	    return false;
	}

	function getDateDay(year, month, day) 
	{
	    var lastDayOfTheMonth = new Date(year, month + 1, 0).getDate();
	    if (day > lastDayOfTheMonth) 
	    {
	        return lastDayOfTheMonth;
	    }
	    return day;
	}

	//set the datepicker option
	function options()
	{
	   var opts = {
	        dateFormat: "dd/mm/yy", 
	        altFormat: "yy-mm-dd",
  			maxDate: 0,
	        changeMonth: true,
	        changeYear: true
	    };
	    
	    return opts; 
	}

	function datepickerStart(s_date,e_date,pass_date,set_date)
	{
	    var opts = options();

	    if(set_date == '')
	    {
	        $("#" + s_date).datepicker(
	        $.extend({
	            altField: "#" + pass_date, // the value pass to backend in db format
	            beforeShow: function() 
	            {
	                if($("#" + s_date).val() == '' || $("#" + e_date).val() == '' )
	                {
	                	if($("#" + e_date).val() == '')
	                	{
	                		$(this).datepicker('option', 'maxDate', getToday());
	                	}
	                	else
	                	{
	                		$(this).datepicker('option', 'maxDate', $('#' + e_date).val());
	                	}

	                }
	            	else
	            	{
	            		$(this).datepicker('option', 'maxDate', $('#' + e_date).val());
	            	}
	            }
	        }, opts));
	    }
	    else
	    {
	        $("#" + s_date).datepicker(
	        $.extend({
	            altField: "#" + pass_date, // the value pass to backend in db format
	            beforeShow: function() 
	            {

	                if($("#" + s_date).val() == '' || $("#" + e_date).val() == '' )
	                {
	                	if($("#" + e_date).val() == '')
	                	{
	                		$(this).datepicker('option', 'maxDate', getToday());
	                	}
	                	else
	                	{
	                		$(this).datepicker('option', 'maxDate', $('#' + e_date).val());
	                	}

	                }
	                else
	                {
	                    $(this).datepicker('option', 'maxDate', $('#' + e_date).val());
	                }
	            }
	        }, opts)).datepicker("setDate", set_date);
	    }
	}

	function datepickerEnd(s_date,e_date,pass_date,set_date,maxDate = 0)
	{
	    var opts = options();

	    if(set_date == '')
	    {
	        $("#" + e_date).datepicker(
	        $.extend({
	            altField: "#" + pass_date, // the value pass to backend in db format
	            beforeShow: function() 
	            {
	                $(this).datepicker('option', 'minDate', $('#' + s_date).val());
	                $(this).datepicker('option', 'maxDate', getToday());
	            }
	        }, opts));
	    }
	    else
	    {
	        $("#" + e_date).datepicker(
	        $.extend({
	            altField: "#" + pass_date, // the value pass to backend in db format
	            beforeShow: function() 
	            {
	            	if(maxDate == 0)
	            	{
	            		$(this).datepicker('option', 'minDate', $('#' + s_date).val());
	            		$(this).datepicker('option', 'maxDate', getToday());
	            	}
	            	else if(maxDate == 1)
	            	{
	            		$(this).datepicker('option', 'minDate', $('#' + s_date).val());
	            		$(this).datepicker('option', 'maxDate', '+24m');
	            	}
	            	else
	            	{
	            		$(this).datepicker('option', 'minDate', $('#' + s_date).val());
	            		$(this).datepicker('option', 'maxDate', '+2m');
	            	}
	            }
	        }, opts)).datepicker("setDate", set_date);
	    } 
	}



    function padLeft(str, len, prefix)
    {
        return Array(len-String(str).length+1).join(prefix||'0')+str;
    }

    function getPreviousTier(tierCode,previousTierCount)
	{
	    if(tierCode.length >= 7)
	    {
	        if(previousTierCount == 1)
	            return tierCode.slice(0,5);
	        else if(previousTierCount == 2)
	            return tierCode.slice(0,3); 
	    }
	    else if(tierCode.length >= 5)
	    {
	        if(previousTierCount == 1)
	            return tierCode.slice(0,3);
	    }

	    return '';
	}

	function getColumnRowspan(column) 
	{

	    var prevText = "";
	    var counter = 0;

	    column.each(function (index) {


	        var textValue = $(this).text();

	        if (index === 0) {
	            prevText = textValue; 
	        }
	        
	        if (textValue !== prevText || index === column.length - 1) {

	            var first = index - counter;

	            if (index === column.length - 1) {
	                counter = counter + 1;
	            }

	            column.eq(first).attr('rowspan', counter);


	            if (index === column.length - 1)
	            {
	                for (var j = index; j > first; j--) {
	                    column.eq(j).remove();
	                }
	            }

	            else {

	                for (var i = index - 1; i > first; i--) {
	                    column.eq(i).remove();
	                }
	            }

	            prevText = textValue;
	            counter = 0;
	        }

	        counter++;

	    });

    }

    function initChart(data,label,description)
	{
	  var ctx = document.getElementById('myChart').getContext('2d');
	  var myChart = new Chart(ctx, {
	      type: 'bar',
	      data: {
	          labels: label,
	          datasets: [{
	              label: description,
	              data: data,
	              backgroundColor: [
	                  'rgba(255, 99, 132, 0.2)',
	                  'rgba(54, 162, 235, 0.2)',
	                  'rgba(255, 206, 86, 0.2)',
	                  'rgba(75, 192, 192, 0.2)',
	              ],
	              borderColor: [
	                  'rgba(255, 99, 132, 1)',
	                  'rgba(54, 162, 235, 1)',
	                  'rgba(255, 206, 86, 1)',
	                  'rgba(75, 192, 192, 1)',
	              ],
	              borderWidth: 1
	          }]
	      },
	      options: {
	          scales: {
	              yAxes: [{
	                  ticks: {
	                      beginAtZero: true
	                  }
	              }]
	          }
	      }
	  });
	}
	

}(this));