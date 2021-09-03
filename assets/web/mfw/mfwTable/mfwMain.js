import React, {Component} from 'react';
import classNames from 'classnames';

import { VariableSizeGrid } from 'react-window';
import ResizeObserver from 'rc-resize-observer';
import { Table, Tag, Space, Checkbox } from 'antd';

import MfwStringType from '@app/web/mfw/mfwTable/mfwStringType';
import MfwIntType from '@app/web/mfw/mfwTable/mfwIntType';
import MfwNumberType from '@app/web/mfw/mfwTable/mfwNumberType';
import MfwLinkType from '@app/web/mfw/mfwTable/mfwLinkType';
import MfwCheckboxType from '@app/web/mfw/mfwTable/mfwCheckboxType';
import MfwDateType from '@app/web/mfw/mfwTable/mfwDateType';
import MfwDateTimeType from '@app/web/mfw/mfwTable/mfwDateTimeType';
import MfwDateTimeMilliType from '@app/web/mfw/mfwTable/mfwDateTimeMilliType';

const mfwColumnTypes = {
    'mfw-string': MfwStringType,
    'mfw-int': MfwIntType,
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
        var tableHead = [...this.props.mfwColumns],
            rowColumns = [],
            filters = [],
            groups = [],
            groupTotals = [],
            dataSource = [...this.props.mfwData],
            colGroup = this.props.colGroup ? this.props.colGroup : [],
            rowColumn = (column) => {
                if (column.children) {
                    column.width = 0;
                    column.children.map((child) => {
                        rowColumn(child);
                        column.width += child.width;
                    });
                    return;
                }
                column.types = column.type ? column.type.split(',') : ['mfw-string'];
                if (column.width == undefined) {
                    column.types.map(type => {
                        if (mfwColumnTypes[type].width) {
                            column.width = mfwColumnTypes[type].width;
                        }
                        if (mfwColumnTypes[type].className) {
                            column.className = column.className ? column.className+' '+mfwColumnTypes[type].className: mfwColumnTypes[type].className;
                        }
                        if ((column.orderable == undefined || column.orderable === true)&&(mfwColumnTypes[type].sorter != undefined)) {
                            column.sorter = (a, b) => {return mfwColumnTypes[type].sorter(a, b, column)};
                        }
                    });
                } else {
                    column.types.map(type => {
                        if (mfwColumnTypes[type].className) {
                            column.className = column.className ? column.className+' '+mfwColumnTypes[type].className: mfwColumnTypes[type].className;
                        }
                        if ((column.orderable == undefined || column.orderable === true)&&(mfwColumnTypes[type].sorter != undefined)) {
                            column.sorter = (a, b) => {return mfwColumnTypes[type].sorter(a, b, column)};
                        }
                    });
                }
                if ((column.orderable == undefined || column.orderable === true)&&(column.sorter == undefined)) {
                    column.sorter = (a, b) => {return mfwColumnTypes['mfw-string'].sorter(a, b, column)};
                }
                column.render = (text, record, index) => {return this.cellRender(text, record, index, column)};
                if (column.mfw_filter) {
                    column.filters = [];
                    column.onFilter = (value, record) => record[column.dataIndex] != null ? record[column.dataIndex].indexOf(value) === 0 : false;
                    filters.push(column);
                }
                column.width = column.width ? column.width : mfwColumnTypes['mfw-string'].width;
                if (column.mfw_total_group) {
                    groupTotals.push(column);
                }
                rowColumns.push(column);
            };
        tableHead.map((column) => {
            rowColumn(column);
        });
        if (this.props.multiSelect) {
            var selectColumn = {
                title: () => <Checkbox
                       indeterminate={this.state.row.selected.keys.length != 0 && this.state.row.selected.keys.length != this.state.row.selected.allCount}
                       checked={this.state.row.selected.keys.length == this.state.row.selected.allCount && this.state.row.selected.allCount != 0}
                       onChange={this.selectAllCheck}
                       />,
                dataIndex: 'mfwSelect',
                render: this.selectRowRender,
                width: 50
            };
            tableHead.unshift(selectColumn);
            rowColumns.unshift(selectColumn);
        }
        if (colGroup.length != 0) {
            colGroup.map((col, index) => {
                if (!col.order) {
                    colGroup[index] = {
                        name: col,
                        order: 'ascend'
                    }
                }
            });
            var groupColumn = {
                title: '',
                dataIndex: 'mfwGroup',
                render: this.groupRowButton,
                width: colGroup.length*16+32 //32 - padding
            };
            tableHead.unshift(groupColumn);
            rowColumns.unshift(groupColumn);
            dataSource.sort((a, b) => {return this.multipleSort(a, b, colGroup, rowColumns)});
            groups = this.groupCalcAll(dataSource, colGroup, groupTotals, []);
        }
        if (filters.lenght != 0) {
            dataSource.map( row => {
                filters.map(filterColumn => {
                    var index = filterColumn.filters.findIndex(x => x.value==row[filterColumn.dataIndex]);
                    if (index == -1) {
                        filterColumn.filters.push({text: mfwColumnTypes[filterColumn.types[0]].renderFilter(row, filterColumn), value: row[filterColumn.dataIndex]});
                    }
               });
           });
        }
        
        this.state = {
            table: {
                width: 0,
                height: 0,
                head: tableHead,
                currentData: dataSource,
                dataSource: dataSource
            },
            row: {
                height: 54,
                columns: rowColumns,
                selected: {
                    keys: [],
                    allCount: colGroup.length == 0 ? dataSource.length : 0
                }
            },
            column: {
                filters: filters
            },
            groups: {
               totals: groupTotals,
               data: groups,
               columns: colGroup ,
               grid: this.groupGrid(groups)
            },
            gridRef: React.createRef()
        };
    }

    selectRowRender(text, record, index) {
        return <Checkbox checked={this.state.row.selected.keys.indexOf(record[this.props.rowKey]) != -1} onChange={() => this.selectRow(record[this.props.rowKey])}/>
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
                        state.row.selected.keys.push(row[this.props.rowKey]);
                    });
                } else {
                    state.groups.grid.map(row => {
                        if (row.rowIndex) {
                            state.row.selected.keys.push(state.table.currentData[row.rowIndex][this.props.rowKey]);
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
                        'virtual-table-cell-last': columnIndex === this.state.row.columns.length - 1,
                      }, this.state.row.columns[columnIndex].className ? this.state.row.columns[columnIndex].className : '')} style={style}>
                          {this.state.row.columns[columnIndex].render(
                              this.state.table.currentData[this.state.groups.grid[rowIndex].group.firstIndex][this.state.row.columns[columnIndex].dataIndex],
                              this.state.table.currentData[this.state.groups.grid[rowIndex].group.firstIdex],
                              this.state.groups.grid[rowIndex].firstIndex)}
                    </div>;
            }
            if (this.state.row.columns[columnIndex].mfw_total_group) {
                const totalIndex = this.state.groups.grid[rowIndex].group.totals.map(total => total.column.dataIndex).indexOf(this.state.row.columns[columnIndex].dataIndex);
                return <div className={classNames('virtual-table-cell', {
                    'virtual-table-cell-last': columnIndex === this.state.row.columns.length - 1,
                     }, this.state.row.columns[columnIndex].className ? this.state.row.columns[columnIndex].className : '')} style={style}>
                    { mfwColumnTypes[this.state.row.columns[columnIndex].types[0]].renderTotal(
                        this.state.row.columns[columnIndex].mfw_total_group, 
                        this.state.groups.grid[rowIndex].group.totals[totalIndex].results
                    )}
                    </div>
            }
            if (columnIndex == 0) {
                return <div className={classNames('virtual-table-cell', {
                    'virtual-table-cell-last': columnIndex === this.state.row.columns.length - 1,
                     }, this.state.row.columns[columnIndex].className ? this.state.row.columns[columnIndex].className : '')} style={style}>
                    {this.state.row.columns[columnIndex].render('', this.state.groups.grid[rowIndex], rowIndex)}                     
                </div>                
            }
            return <div className={classNames('virtual-table-cell', {
                'virtual-table-cell-last': columnIndex === this.state.row.columns.length - 1,
                 }, this.state.row.columns[columnIndex].className ? this.state.row.columns[columnIndex].className : '')} style={style}></div>
        }
        if (this.state.groups.grid[rowIndex].rowIndex != undefined) {
            return <div className={classNames('virtual-table-cell', {
                      'virtual-table-cell-last': columnIndex === this.state.row.columns.length - 1,
                    }, this.state.row.columns[columnIndex].className ? this.state.row.columns[columnIndex].className : '')} style={style}>
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
                    state.table.currentData.sort((a, b) => {return this.multipleSort(a, b, sortColumns, state.row.columns)});
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
                    ref={this.state.gridRef}
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
                ref={this.state.gridRef}
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
                    }, this.state.row.columns[columnIndex].className ? this.state.row.columns[columnIndex].className : '')} style={style}>
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
        this.setState((state) => {
            state.table.width = params.width;
            state.table.height = params.height;
            return state;
        });
    }

    onSelectChange(selectedRowKeys) {
        this.setState({ selectedRowKeys });
    };

    render() {
        return (
            <ResizeObserver onResize={this.setTableWidth}>
              <Table
                {...this.props}
                dataSource={this.state.table.dataSource}
                className="virtual-table"
                columns={this.state.table.head}
                pagination={false}
                components={{
                  body: this.renderVirtualList
                }}
                onChange={this.onChange}
              />
            </ResizeObserver>
        );
    }
}

export default MfwTable;