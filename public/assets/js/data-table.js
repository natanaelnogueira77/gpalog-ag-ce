class DataTable 
{
    constructor(table, urlBase) 
    {
        this.table = table;
        this.urlBase = urlBase;
        this.msg = {};
        this.defaultUrlParams = {};
        this.urlParams = {};
        this.functions = [];
        this.form;

        this.msgFunc;
    }

    load() 
    {
        const object = this;

        $.ajax({
            url: this.urlBase,
            type: 'get',
            data: this.urlParams,
            dataType: "json",
            beforeSend: function () {
                object.table.css('opacity', '0.1');
            },
            success: function(response) {
                object.msg = {};
                if(response.message) {
                    object.msg = response.message;
                    object.outputMsg();
                }

                if(response.content) {
                    object.table.css('opacity', '0.1');
                    object.table.children().remove();
                    
                    if(response.content.pagination) {
                        const pagination = $(response.content.pagination);
                        object.setPagination(pagination);
                        object.table.append(pagination);
                    }

                    const table = $(response.content.table);
                    object.setOrdenation(table);
                    object.table.append(table);
                    object.loadActions();
                }
            },
            complete: function () {
                object.table.animate({opacity: '1'}, 'fast');
            }
        });

        return object;
    }

    setBaseUrl(url) 
    {
        this.urlBase = url;
        return this;
    }

    setPagination(elem) 
    {
        const object = this;
        elem.find("[data-page]").click(function () {
            object.urlParams.page = $(this).data('page');
            object.load();
        });

        return object;
    }

    setOrdenation(elem) 
    {
        const object = this;
        elem.find("[data-order]").click(function () {
            object.urlParams.order = $(this).data('order');
            object.urlParams.orderType = $(this).data('orderType');
            object.load();
        });

        return object;
    }

    params(params = {}) 
    {
        this.urlParams = params;
        this.changeFilterValues();
        return this;
    }

    defaultParams(params = {}) 
    {
        this.defaultUrlParams = params;
        return this;
    }

    filtersForm(elem) 
    {
        const object = this;

        object.form = elem;
        object.form.submit(function (e) {
            e.preventDefault();

            var returnArray = {};
            var formArray = $(this).serializeArray();

            if(formArray) {
                for(var i = 0; i < formArray.length; i++){
                    returnArray[formArray[i]["name"]] = formArray[i]["value"];
                }
            }
            
            object.params(returnArray);
            object.load();
        });

        return object;
    }

    clearButton(elem) 
    {
        const object = this;
        elem.click(function () {
            object.clear();
        });

        return object;
    }

    addAction(func) 
    {
        this.functions.push(func);
        return this;
    }

    loadActions() 
    {
        if(this.functions) {
            for(var i = 0; i < this.functions.length; i++) {
                this.functions[i](this.table);
            }
        }

        return this;
    }

    clear() 
    {
        this.urlParams = this.defaultUrlParams;
        this.changeFilterValues();
        this.load();

        return this;
    }

    changeFilterValues() 
    {
        if(this.form) {
            for(const [index, value] of Object.entries(this.urlParams)) {
                this.form.find(`[name='${index}']`).val(value);
            }
        }

        return this;
    }

    setMsgFunc(func) 
    {
        this.msgFunc = func;
        return this;
    }

    loadOnChange() 
    {
        const object = this;
        if(object.form) {
            object.form.find("[name]").change(function () {
                var returnArray = {};
                var formArray = object.form.serializeArray();
    
                if(formArray) {
                    for(var i = 0; i < formArray.length; i++){
                        returnArray[formArray[i]["name"]] = formArray[i]["value"];
                    }
                }
                
                object.params(returnArray);
                object.load();
            });
        }

        return object;
    }

    outputMsg() 
    {
        this.msgFunc(this.msg);
    }
}