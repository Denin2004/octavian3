import React, {Component} from 'react';
import classNames from 'classnames';

import { VariableSizeGrid } from 'react-window';
import ResizeObserver from 'rc-resize-observer';
import { Table, Tag, Space, Checkbox, Button, Form, Input } from 'antd';

import { withTranslation } from 'react-i18next';

import MfwStringType from '@app/web/mfw/mfwTable/mfwStringType';
import MfwIntType from '@app/web/mfw/mfwTable/mfwIntType';
import MfwNativeIntType from '@app/web/mfw/mfwTable/mfwNativeIntType';
import MfwNumberType from '@app/web/mfw/mfwTable/mfwNumberType';
import MfwLinkType from '@app/web/mfw/mfwTable/mfwLinkType';
import MfwCheckboxType from '@app/web/mfw/mfwTable/mfwCheckboxType';
import MfwDateType from '@app/web/mfw/mfwTable/mfwDateType';
import MfwDateTimeType from '@app/web/mfw/mfwTable/mfwDateTimeType';
import MfwDateTimeMilliType from '@app/web/mfw/mfwTable/mfwDateTimeMilliType';
import MfwColVis from '@app/web/mfw/mfwTable/buttons/mfwColVis';
import MfwExcel from '@app/web/mfw/mfwTable/buttons/mfwExcel';

const mfwColumnTypes = {
    'mfw-string': MfwStringType,
    'mfw-int': MfwIntType,
    'mfw-native-int': MfwNativeIntType,
    'mfw-num': MfwNumberType,
    'mfw-link': MfwLinkType,
    'mfw-checkbox': MfwCheckboxType,
    'mfw-date': MfwDateType,
    'mfw-date-time': MfwDateTimeType,
    'mfw-date-time-milli': MfwDateTimeMilliType,
    custom: MfwStringType
};

class MfwTable extends Component {

    constructor(props){
        super(props);
        this.renderVirtualList = this.renderVirtualList.bind(this);
        this.setTableWidth = this.setTableWidth.bind(this);
        this.cellRender = this.cellRender.bind(this);
        this.onChange = this.onChange.bind(this);
        this.selectRowRender = this.selectRowRender.bind(this);
        this.selectRow = this.selectRow.bind(this);
        this.selectAllCheck = this.selectAllCheck.bind(this);
        this.groupCalcAll = this.groupCalcAll.bind(this);
        this.groupRowRender = this.groupRowRender.bind(this);
        this.groupDetails = this.groupDetails.bind(this);
        this.groupRowButton = this.groupRowButton.bind(this);
        this.buttons = this.buttons.bind(this);
        this.perform = this.perform.bind(this);
        this.setVisibleColumns = this.setVisibleColumns.bind(this);
        var visible = [],
            currentData = [...this.props.mfwData],
            colGroup = this.props.mfwConfig.extended && this.props.mfwConfig.extended.colGroup ? [this.props.mfwConfig.extended.colGroup] : [];
        if (colGroup.length != 0) {
            colGroup.map((col, index) => {
                if (!col.order) {
                    colGroup[index] = {
                        name: col,
                        order: 'ascend'
                    }
                }
            });
            currentData.sort((a, b) => {return this.multipleSort(a, b, this.state.groups.columns, res.table.head)});
        }
        this.state = {
            table: {
                width: 0,
                height: 0,
                head: [],
                currentData: currentData,
                dataSource: currentData
            },
            row: {
                height: 43,
                selected: {
                    keys: [],
                    allCount: this.props.mfwConfig.extended && this.props.mfwConfig.extended.colGroup ? this.props.mfwData.length : 0
                },
                columns: [],
                defaultWidth: 0
            },
            column: {
                filters: [],
                visible: [],
                list: this.props.mfwConfig.extended && this.props.mfwConfig.extended.thead != undefined ?
                    [...this.props.mfwConfig.extended.thead.react] : this.props.mfwConfig.tableInit.columns,
                selectColumn: {
                    title: () => <Checkbox
                           indeterminate={this.state.row.selected.keys.length != 0 && this.state.row.selected.keys.length != this.state.row.selected.allCount}
                           checked={this.state.row.selected.keys.length == this.state.row.selected.allCount && this.state.row.selected.allCount != 0}
                           onChange={this.selectAllCheck}
                           />,
                    dataIndex: 'mfwSelect',
                    render: this.selectRowRender,
                    width: 50,
                    className: 'mfwSelect mfw-noexcel',
                    mfw_noexcel: true
                },
                groupColumn: {
                    title: '',
                    dataIndex: 'mfwGroup',
                    render: this.groupRowButton,
                    width: 0,
                    className: 'mfwGroup  mfw-noexcel',
                    mfw_noexcel: true
                }
            },
            groups: {
               totals: [],
               data: [],
               columns: colGroup,
               grid: []
            },
            mount: false
        };
        this.state.column.list.map((column) => {
            var dataIndex = column.dataIndex ? column.dataIndex : column.data;
            if ((column.visible == undefined)||
                (column.visible && column.visible === true)||
                (colGroup.findIndex(col => col.name === dataIndex) != -1)) {
                visible.push(dataIndex);
            }
        });
        var conf = this.perform({
            visible: visible
        });
        this.state.table.head = conf.table.head;
        this.state.column.filters = conf.column.filters;
        this.state.column.visible = conf.column.visible;
        this.state.row.columns = conf.row.columns;
        this.state.row.defaultWidth = conf.row.defaultWidth;
        if (this.state.groups.columns.length != 0) {
            this.state.groups.totals = conf.groups.totals;
            currentData.sort((a, b) => {return this.multipleSort(a, b, this.state.groups.columns, this.state.table.head)});
            this.state.groups.data = this.groupCalcAll(currentData, this.state.groups.columns, this.state.groups.totals, []);
            this.state.groups.grid = this.groupGrid(this.state.groups.data);
            this.state.column.groupColumn.width = this.state.groups.columns.length*16+32; //32 - padding
        }
    }

    componentDidMount() {
        this.ref = React.createRef();
        this.setState({mount: true})
    }

    componentWillUnmount() {
        this.ref = null;
        this.setState({mount: false});
    }

    componentDidUpdate(prev) {
        if ((prev.loading === true)&&(this.props.loading === false)) {
            var dataSource = [...this.props.mfwData],
                groups = [];
            if (this.state.groups.columns.length != 0) {
                dataSource.sort((a, b) => {return this.multipleSort(a, b, this.state.groups.columns, this.state.table.head)});
                groups = this.groupCalcAll(dataSource, this.state.groups.columns, this.state.groups.totals, []);
            }
            if (this.state.column.filters.length != 0) {
                dataSource.map( (row, i) => {
                    row.rowKey = i;
                    this.state.column.filters.map(filterColumn => {
                        var index = filterColumn.filters.findIndex(x => x.value==row[filterColumn.dataIndex]);
                        if (index == -1) {
                            filterColumn.filters.push({text: mfwColumnTypes[filterColumn.types[0]].renderFilter(row, filterColumn), value: row[filterColumn.dataIndex]});
                        }
                   });
               });
            }
            this.setState( state => {
                state.groups.data = groups;
                state.groups.grid = this.groupGrid(groups);
                state.table.currentData = dataSource;
                state.table.dataSource = dataSource;
                state.row.selected.keys = [];
                state.row.selected.allCount = state.groups.columns.length == 0 ? dataSource.length : 0;
                return state;
            });
        }
    }

    perform(data) {
        var res = {
                table: {
                    head: []
                },
                row: {
                    columns: [],
                    defaultWidth: 0
                },
                column: {
                    filters: [],
                    visible: data.visible
                },
                groups: {
                   totals: []
                }
            },
            rowColumn = (column, parent) => {
                if (column.children) {
                    column.width = 0;
                    column.children.map(child => {
                        rowColumn(child, column);
                        column.width += child.width;
                    });
                    if (column.title != undefined) {
                        column.title = this.props.t(column.title);
                    }
                    res.table.head.push(column);
                    return;
                }
                column.types = column.mfw_type ? column.mfw_type.split(',') : ['mfw-string'];
                column.types.map(type => {
                    column.width = column.width ? column.width : (mfwColumnTypes[type].width ? mfwColumnTypes[type].width : undefined);
                    column.align = column.align ? column.align : (mfwColumnTypes[type].align ? mfwColumnTypes[type].align : undefined);
                    if ((column.orderable == undefined || column.orderable === true)&&(mfwColumnTypes[type].sorter != undefined)) {
                        column.sorter = (a, b) => {return mfwColumnTypes[type].sorter(a, b, column)};
                    }
                    if ((type == 'mfw-link')&&(column.action.ajax != undefined)) {
                        column.response = this.props.ajaxResponse;
                    }
                });
                if ((column.orderable == undefined || column.orderable === true)&&(column.sorter == undefined)) {
                    column.sorter = (a, b) => {return mfwColumnTypes['mfw-string'].sorter(a, b, column)};
                }
                column.width = column.width ? column.width : mfwColumnTypes['mfw-string'].width;
                column.align = column.align ? column.align : mfwColumnTypes['mfw-string'].align;
                res.row.defaultWidth = res.row.defaultWidth + column.width;
                column.render = (text, record, index) => {return this.cellRender(text, record, index, column)};
                if (column.mfw_filter) {
                    column.filters = [];
                    column.onFilter = (value, record) => record[column.dataIndex] != null ? record[column.dataIndex].indexOf(value) === 0 : false;
                    res.column.filters.push(column);
                }
                if (column.mfw_total_group) {
                    res.groups.totals.push(column);
                }
                column.dataIndex = column.dataIndex ? column.dataIndex : column.data;
                if (column.title != undefined) {
                    column.title = this.props.t(column.title);
                }
                if (column.dataIndex) {
                    column.className = column.dataIndex;
                }
                if (parent === null) {
                    res.table.head.push(column);
                }
                if (column.mfw_noexcel != undefined) {
                    column.className = column.className ? column.className+' mfw-noexcel' : 'mfw-noexcel';
                }
                res.row.columns.push(column);
            };
        this.state.column.list.map((column) => {
            if ((column.children)||
               (data.visible.findIndex(x => x === (column.dataIndex ? column.dataIndex : column.data)) != -1)) {
                rowColumn(column, null);
            }
        });
        if (this.props.mfwConfig.tableInit.select) {
            res.table.head.unshift(this.state.column.selectColumn);
            res.row.columns.unshift(this.state.column.selectColumn);
        }
        if (this.state.groups.columns.length != 0) {
            res.table.head.unshift(this.state.column.groupColumn);
            res.row.columns.unshift(this.state.column.groupColumn);
        }
        if (res.column.filters.length != 0) {
            this.state.table.currentData.map( row => {
                res.column.filters.map(filterColumn => {
                    var index = filterColumn.filters.findIndex(x => x.value==row[filterColumn.dataIndex]);
                    if (index == -1) {
                        filterColumn.filters.push({text: mfwColumnTypes[filterColumn.types[0]].renderFilter(row, filterColumn), value: row[filterColumn.dataIndex]});
                    }
               });
           });
        }
        return res;
    }

    selectRowRender(text, record, index) {
        return <Checkbox checked={this.state.row.selected.keys.indexOf(record.rowKey) != -1} onChange={() => this.selectRow(record.rowKey)}/>
    }

    selectRow(key) {
        var isSelected = this.state.row.selected.keys.indexOf(key);
        if (isSelected == -1) {
            this.setState(state => {state.row.selected.keys.push(key);return state;});
        } else {
            this.setState(state => {state.row.selected.keys.splice(isSelected, 1);return state;})
        }
    }

    selectAllCheck() {
        if ((this.state.row.selected.keys.length >= 0)&&
            (this.state.row.selected.keys.length < this.state.row.selected.allCount)) {
            this.setState(state => {
                state.row.selected.keys = [];
                if (state.groups.columns.length == 0) {
                    state.table.currentData.map(row => {
                        state.row.selected.keys.push(row.rowKey);
                    });
                } else {
                    state.groups.grid.map(row => {
                        if (row.rowIndex) {
                            state.row.selected.keys.push(state.table.currentData[row.rowIndex].rowKey);
                        }
                    });
                }
                return state;
            });
        } else {
            this.setState(state => {
                state.row.selected.keys = [];
                return state;
            });
        }
    }

    multipleSort(a, b, orderColumns, rowColumns) {
        var comp = 0;
        orderColumns.map(orderColumn => {
            const colIndex = rowColumns.map(col => col.dataIndex).indexOf(orderColumn.name);
            const compCol = orderColumn.order == 'ascend' ? rowColumns[colIndex].sorter(a, b) : 0-rowColumns[colIndex].sorter(a, b);
            comp = comp || compCol;
        });
        return comp;
    }

    groupCalcAll(dataSource, colGroup, totals, openGroups) {
        var groups = [];
        dataSource.map((row, index) => {
            this.groupCalc(dataSource, colGroup, totals, 0, index, groups, openGroups);
        });
        return groups;
    }

    groupCalc(dataSource, colGroup, totals, level, index, groups, openGroups) {
        var grp = groups.map(group => group.value).indexOf(dataSource[index][colGroup[level].name]);
        if (grp == -1) {
            const detailed = openGroups.reduce((detailed, group) => {
                return detailed === true ? detailed : (group.level == level)&&(group.dataIndex==colGroup[level].name)&&(group.value == dataSource[index][colGroup[level].name]);
            }, false);
            groups.push({
                level: level,
                dataIndex: colGroup[level].name,
                value: dataSource[index][colGroup[level].name],
                indexes: [],
                firstIndex: index,
                subGroups: [],
                totals: [],
                detailed: detailed
            });
            grp = groups.length -1;
            totals.map(total => {
                groups[grp].totals.push({
                    column: total,
                    results: {...mfwColumnTypes[total.types[0]].aggregatorInitValues(total.mfw_total_group)}
                });
            })
        }
        groups[grp].totals.map(total => {
            mfwColumnTypes[total.column.types[0]].aggregatorCalc(total.column.mfw_total_group, total.results, dataSource[index], total.column.dataIndex);
        });
        if (level == (colGroup.length-1)) {
            groups[grp].indexes.push(index);
        } else {
             this.groupCalc(dataSource, colGroup, totals, level+1, index, groups[grp].subGroups, openGroups);
        }
    }

    groupGrid(groups) {
        var grid = [];
        groups.map(group => {
            grid.push({
                group: group
            });
            if (group.detailed) {
                this.groupGridDetails(group, grid);
            }
        });
        return grid;
    }

    groupGridDetails(group, grid) {
        group.subGroups.map(subGroup => {
            grid.push({
                group: subGroup
            });
            if (subGroup.detailed) {
                this.groupGridDetails(subGroup, grid);
            }
        });
        if (group.detailed) {
            group.indexes.map(rowIndex => {
                grid.push({
                    rowIndex: rowIndex
                });
            });
        }
    }

    groupRowRender(columnIndex, rowIndex, style) {
        if (this.state.groups.grid[rowIndex].group != undefined) {
            if (this.state.row.columns[columnIndex].dataIndex == this.state.groups.grid[rowIndex].group.dataIndex) {
                return <div className={classNames('virtual-table-cell', {
                        'virtual-table-cell-last': columnIndex === this.state.table.head.length - 1,
                      }, 'mfw-align-'+this.state.row.columns[columnIndex].align)} style={style}>
                          {this.state.row.columns[columnIndex].render(
                              this.state.table.currentData[this.state.groups.grid[rowIndex].group.firstIndex][this.state.row.columns[columnIndex].dataIndex],
                              this.state.table.currentData[this.state.groups.grid[rowIndex].group.firstIndex],
                              this.state.groups.grid[rowIndex].firstIndex)}
                    </div>;
            }
            if (this.state.row.columns[columnIndex].mfw_total_group) {
                const totalIndex = this.state.groups.grid[rowIndex].group.totals.map(total => total.column.dataIndex).indexOf(this.state.row.columns[columnIndex].dataIndex);
                return <div className={classNames('virtual-table-cell', {
                    'virtual-table-cell-last': columnIndex === this.state.row.columns.length - 1,
                     }, 'mfw-font-bold mfw-align-'+this.state.row.columns[columnIndex].align)} style={style}>
                    { mfwColumnTypes[this.state.row.columns[columnIndex].types[0]].renderTotal(
                        this.state.row.columns[columnIndex].mfw_total_group,
                        this.state.groups.grid[rowIndex].group.totals[totalIndex].results
                    )}
                    </div>
            }
            if (columnIndex == 0) {
                return <div className={classNames('virtual-table-cell', {
                    'virtual-table-cell-last': columnIndex === this.state.row.columns.length - 1,
                     }, 'mfw-align-'+this.state.row.columns[columnIndex].align)} style={style}>
                    {this.state.row.columns[columnIndex].render('', this.state.groups.grid[rowIndex], rowIndex)}
                </div>
            }
            return <div className={classNames('virtual-table-cell', {
                'virtual-table-cell-last': columnIndex === this.state.row.columns.length - 1,
                 }, 'mfw-align-'+this.state.row.columns[columnIndex].align)} style={style}></div>
        }
        if (this.state.groups.grid[rowIndex].rowIndex != undefined) {
            return <div className={classNames('virtual-table-cell', {
                      'virtual-table-cell-last': columnIndex === this.state.row.columns.length - 1,
                    }, 'mfw-align-'+this.state.row.columns[columnIndex].align)} style={style}>
                        {this.state.row.columns[columnIndex].render(
                            this.state.table.currentData[this.state.groups.grid[rowIndex].rowIndex][this.state.row.columns[columnIndex].dataIndex],
                            this.state.table.currentData[this.state.groups.grid[rowIndex].rowIndex],
                            this.state.groups.grid[rowIndex].rowIndex)}
                    </div>
        }
    }

    groupRowButton(text, record, index) {
        if (record.group) {
            return <button type="button"
                 style={{marginLeft: record.group.level*16+'px'}}
                 className={classNames('ant-table-row-expand-icon mfw-ant-table-row-expand-icon', record.group.detailed ? 'ant-table-row-expand-icon-expanded' : 'ant-table-row-expand-icon-collapsed')}
                 onClick={() => {this.groupDetails(index)}}></button>;
        }
        return '';
    }

    groupDetails(index) {
        this.setState(state => {
            state.groups.grid[index].group.detailed = !state.groups.grid[index].group.detailed;
            state.groups.grid = this.groupGrid(state.groups.data);
            state.row.selected.allCount = state.groups.grid.reduce((acc, row) => {
                return acc + (row.rowIndex == undefined ? 0 : 1);
            }, 0);
            return state;
        })
    }

    groupSort(a, b, totalIndex, order) {
        return order == 'ascend' ?
            mfwColumnTypes[a.totals[totalIndex].column.types[0]].aggregatorValue(a.totals[totalIndex].column.mfw_total_group, a.totals[totalIndex].results)
              - mfwColumnTypes[b.totals[totalIndex].column.types[0]].aggregatorValue(b.totals[totalIndex].column.mfw_total_group, b.totals[totalIndex].results) :
            mfwColumnTypes[b.totals[totalIndex].column.types[0]].aggregatorValue(a.totals[totalIndex].column.mfw_total_group, b.totals[totalIndex].results)
              - mfwColumnTypes[a.totals[totalIndex].column.types[0]].aggregatorValue(b.totals[totalIndex].column.mfw_total_group, a.totals[totalIndex].results);
    }

    groupSortSub(group, totalIndex, order) {
        group.subGroups.sort((a, b) => {return this.groupSort(a, b, totalIndex, order);});
        group.subGroups.map(subGroup => {this.groupSortSub(subGroup, totalIndex, order)});
    }

    cellRender(text, record, index, column) {
        column.types.map(type => {
            text = mfwColumnTypes[type] ? mfwColumnTypes[type].render(text, record, index, column) : type + ' undefined';
        });
        return text;
    }

    onChange(pagination, filters, sorter, extra) {
        this.setState(state => {
            if (extra.action == 'filter') {
                state.row.selected.keys = [];
                state.column.filters.map(filterColumn => {
                    filterColumn.filters = [];
                });
                extra.currentDataSource.map( row => {
                    state.column.filters.map(filterColumn => {
                        var index = filterColumn.filters.findIndex(x => x.value==row[filterColumn.dataIndex]);
                        if (index == -1) {
                            filterColumn.filters.push({text: mfwColumnTypes[filterColumn.types[0]].renderFilter(row, filterColumn), value: row[filterColumn.dataIndex]});
                        }
                   });
               });
            }
            state.table.currentData = extra.currentDataSource;
            if (state.groups.columns.length != 0) {
                if (extra.action == 'sort') {
                    const grpColumn = state.groups.columns.map(col => col.name).indexOf(sorter.field);
                    var sortColumns = [...state.groups.columns];
                    if (grpColumn != -1) {
                        state.groups.columns[grpColumn].order = sorter.order ? sorter.order : 'ascend';
                    } else {
                        if (sorter.order) {
                            sortColumns.push({
                                name: sorter.field,
                                order: sorter.order
                            });
                        }
                    }
                    state.table.currentData.sort((a, b) => {return this.multipleSort(a, b, sortColumns, state.table.head)});
                }
                var openGroups = [];
                state.groups.grid.map(row => {
                    if (row.group && row.group.detailed === true) {
                        openGroups.push({
                            level: row.group.level,
                            value: row.group.value,
                            dataIndex: row.group.dataIndex
                        });
                    }
                });
                state.groups.data = this.groupCalcAll(extra.currentDataSource, state.groups.columns, state.groups.totals, openGroups);
                if ((extra.action == 'sort')&&(sorter.order != undefined)) {
                    const totalSort = state.groups.totals.map(total => total.dataIndex).indexOf(sorter.field);
                    if (totalSort != -1) {
                        state.groups.data.sort((a, b) => {return this.groupSort(a, b, totalSort, sorter.order);});
                        state.groups.data.map(group => {
                            this.groupSortSub(group, totalSort, sorter.order);
                        });
                    }
                }
                state.groups.grid = this.groupGrid(state.groups.data);
                state.row.selected.allCount = state.groups.grid.reduce((acc, row) => {
                    return acc + (row.rowIndex ? 1 : 0);
                }, 0);
            } else {
                state.row.selected.allCount = extra.currentDataSource.length;
            }
            return state;
        });
    }

    renderVirtualList(rawData, { scrollbarSize, ref, onScroll }) {
        if (this.state.groups.columns.length != 0) {
            const totalHeight = this.state.groups.grid.length * this.state.row.height;
            return this.state.table.width == 0 ? (<React.Fragment/>) : (
                <VariableSizeGrid
                    className="virtual-grid"
                    columnCount={this.state.row.columns.length}
                    columnWidth={(index) => {
                        const { width } = this.state.row.columns[index];
                        return totalHeight > this.props.scroll.y && index === this.state.row.columns.length - 1
                             ? width - scrollbarSize - 1
                      : width;
                    }}
                  height={this.state.table.height-55}
                  rowCount={this.state.groups.grid.length}
                  rowHeight={() => this.state.row.height}
                  width={this.state.table.width}
                  onScroll={({ scrollLeft }) => {
                    onScroll({
                      scrollLeft,
                    });
                  }}
                >
                    {({ columnIndex, rowIndex, style }) => {return this.groupRowRender(columnIndex, rowIndex, style);}}
                </VariableSizeGrid>
            );
        }
        const totalHeight = rawData.length * this.state.row.height;
        return this.state.table.width == 0 ? (<React.Fragment/>) : (
            <VariableSizeGrid
                className="virtual-grid"
                columnCount={this.state.row.columns.length}
                columnWidth={(index) => {
                    const { width } = this.state.row.columns[index];
                    return totalHeight > this.props.scroll.y && index === this.state.row.columns.length - 1
                         ? width - scrollbarSize - 1
                  : width;
                }}
              height={this.state.table.height-55}
              rowCount={rawData.length}
              rowHeight={() => this.state.row.height}
              width={this.state.table.width}
              onScroll={({ scrollLeft }) => {
                onScroll({
                  scrollLeft,
                });
              }}
            >
                {({ columnIndex, rowIndex, style }) => {
                    return (
                    <div className={classNames('virtual-table-cell', {
                      'virtual-table-cell-last': columnIndex === this.state.row.columns.length - 1,
                    }, 'mfw-align-'+this.state.row.columns[columnIndex].align)} style={style}>
                        {this.state.row.columns[columnIndex].render(
                            rawData[rowIndex][this.state.row.columns[columnIndex].dataIndex],
                            rawData[rowIndex],
                            rowIndex)}
                    </div>
                )}}
            </VariableSizeGrid>
        );
    }

    setTableWidth(params) {
        if (!this.state.mount) {
            return;
        }
        this.setState((state) => {
            state.table.width = params.width;
            state.table.height = params.height;
            if (state.table.width > state.row.defaultWidth) {
                const head = this.ref.current.getElementsByClassName('ant-table-thead');
                state.row.columns.map(col => {
                    col.width = head[0].getElementsByClassName(col.dataIndex)[0].offsetWidth;
                });
            }
            return state;
        });
    }

    onSelectChange(selectedRowKeys) {
        this.setState({ selectedRowKeys });
    };

    setVisibleColumns(visible) {
        var conf = this.perform({
            visible: visible
        });
        this.setState( state => {
            state.table.head = conf.table.head;
            state.column.filters = conf.column.filters;
            state.column.visible = conf.column.visible;
            state.row.columns = conf.row.columns;
            state.row.defaultWidth = conf.row.defaultWidth;
            if (state.groups.columns.length != 0) {
                state.groups.totals = conf.groups.totals;
                state.groups.data = this.groupCalcAll(state.table.currentData, state.groups.columns, state.groups.totals, []);
                state.groups.grid = this.groupGrid(state.groups.data);
            }
            return state;
        })
    }

    buttons() {
        return <Space>{
        this.props.mfwConfig.tableInit.buttons.map((button, i) => {
            const btnName = typeof button === 'string' ? button : button.extend;
            switch(btnName) {
                case 'mfwColVis':
                    return <MfwColVis
                       key={i}
                       setColVis={this.setVisibleColumns}
                       all={this.props.mfwConfig.tableInit.columns}
                       visible={this.state.column.visible}
                       />;
                case 'mfwExcel':
                    return <MfwExcel key={i} data={() => {return {
                        groups: this.state.groups.columns.length != 0 ? this.state.groups.grid : false,
                        data: this.state.table.currentData,
                        columns: this.state.row.columns.filter(col => col.mfw_noexcel ? false : true),
                        head: this.props.mfwConfig.extended && this.props.mfwConfig.extended.thead ?
                             this.ref.current.getElementsByClassName('ant-table-thead')[0].outerHTML : false
                    }}}/>;
            }
        })
        }</Space>
    }

    render() {
        return (
            <div className="flex">
                <div className="header">{this.props.mfwConfig.tableInit.buttons ? this.buttons() : null}</div>
                <ResizeObserver onResize={this.setTableWidth}>
                    <Table
                      {...this.props}
                      dataSource={this.state.table.dataSource}
                      className="virtual-table"
                      columns={this.state.table.head}
                      pagination={false}
                      rowKey="rowKey"
                      components={{
                        body: this.renderVirtualList
                      }}
                      onChange={this.onChange}
                      ref={this.ref}
                    />
                </ResizeObserver>
            </div>
        );
    }
}

export default withTranslation()(MfwTable);