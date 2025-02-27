/**
 * Class BX.CustomOrderList.List
 */
if (!BX.CustomOrderList)
    BX.CustomOrderList = {};

BX.CustomOrderList.List = {
    selectFilter: {
        dateStart: '',
        dateEnd: '',
        statusOrder: '',
    },
    offset: 10,

    getSortData: function () {
        const selected = BX('table').querySelector('.select');
        return {
            by: selected.id,
            order: selected.querySelector('i').className === 'up' ? 'asc' : 'desc'
        }
    },
    sortTable: function (by) {
        const element = BX(by);
        const selector = BX(by).querySelector('i');
        let order = '';
        if (selector) {
            if (selector.className === 'up') {
                selector.className = 'down';
                order = 'desc';
            } else {
                selector.className = 'up';
                order = 'asc';
            }
        } else {
            BX('table').querySelector('.select').className = '';
            BX('table').querySelector('i').remove();
            element.className = 'select';
            BX.append(BX.create({tag:'i', props: {className: 'up'} }), element)
            order = 'asc';
        }
        BX.ajax({
            url: '/bitrix/admin/list.php?AJAX=true' + '&statusOrder=' + this.selectFilter.statusOrder + '&dateStart='+ this.selectFilter.dateStart + '&dateEnd=' + this.selectFilter.dateEnd + '&by=' + by + '&order=' + order,
            method: 'GET',
            dataType: 'json',
            timeout: 30,
            async: true,
            processData: true,
            start: true,
            cache: false,
            onsuccess: (data) => {
                if (data.type === 'error') {
                    alert(data.message);
                } else {
                    BX.cleanNode(BX('body-list-orders'));
                    data.orders.forEach(element => {
                        let elements = [];
                        for (const [key, value] of Object.entries(element)) {
                            elements[elements.length] = BX.create({tag:'td', text: value})
                        }
                        BX.append(BX.create({tag:'tr', children: elements}), BX('body-list-orders'))
                    });
                }
            },
            onfailure: () => {
                alert('Внутренняя ошибка');
            }
        });
    },
    filterTable: function (e) {
        BX.PreventDefault(e);
        this.selectFilter.dateStart = BX('dateStart').value;
        this.selectFilter.dateEnd = BX('dateEnd').value;
        this.selectFilter.statusOrder = BX('statusOrder').value;
        const sort = this.getSortData();
        BX.ajax({
            url: '/bitrix/admin/list.php?AJAX=true' + '&statusOrder=' + this.selectFilter.statusOrder + '&dateStart='+ this.selectFilter.dateStart + '&dateEnd=' + this.selectFilter.dateEnd + '&by=' + sort.by + '&order=' + sort.order,
            method: 'GET',
            dataType: 'json',
            timeout: 30,
            async: true,
            processData: true,
            start: true,
            cache: false,
            onsuccess: (data) => {
                if (data.type === 'error') {
                    alert(data.message);
                } else {
                    BX.cleanNode(BX('body-list-orders'));
                    data.orders.forEach(element => {
                        let elements = [];
                        for (const [key, value] of Object.entries(element)) {
                            elements[elements.length] = BX.create({tag:'td', text: value})
                        }
                        BX.append(BX.create({tag:'tr', children: elements}), BX('body-list-orders'))
                    });
                }
            },
            onfailure: (data) => {
                alert('Внутренняя ошибка');
            }
        });
    },
    loadOrders: function () {
        const sort = this.getSortData();
        BX.ajax({
            url: '/bitrix/admin/list.php?AJAX=true' + '&statusOrder=' + this.selectFilter.statusOrder + '&dateStart='+ this.selectFilter.dateStart + '&dateEnd=' + this.selectFilter.dateEnd + '&offset=' + this.offset + '&by=' + sort.by + '&order=' + sort.order,
            method: 'GET',
            dataType: 'json',
            timeout: 30,
            async: true,
            processData: true,
            start: true,
            cache: false,
            onsuccess: (data) => {
                if (data.type === 'error') {
                    alert(data.message);
                } else {
                    data.orders.forEach(element => {
                        let elements = [];
                        for (const [key, value] of Object.entries(element)) {
                            elements[elements.length] = BX.create({tag:'td', text: value})
                        }
                        BX.append(BX.create({tag:'tr', children: elements}), BX('table'))
                    });
                    this.offset += 10;
                }
            },
            onfailure: (data) => {
                console.log(data);
            }
        });
    }
}